<?php

namespace Tests\Feature;

use App\Models\PlatformAuditLog;
use App\Models\Tenant;
use App\Models\User;
use App\Support\PermissionTeam;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlatformQueryRunnerTest extends TestCase
{
    use DatabaseTransactions;

    private User $super;

    protected function setUp(): void
    {
        parent::setUp();

        $this->super = User::create([
            'tenant_id' => null, 'name' => 'Super Admin', 'email' => Str::random(10).'@example.test',
            'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60),
        ]);
        PermissionTeam::run(null, function () {
            Role::findOrCreate('Super Admin', 'web');
            $this->super->assignRole('Super Admin');
        });
        $this->post('/superadmin/login', ['email' => $this->super->email, 'password' => 'password'])
            ->assertRedirect('/superadmin');
    }

    public function test_a_select_against_master_returns_rows_and_columns(): void
    {
        $response = $this->postJson(route('superadmin.query_runner.run'), [
            'connection' => 'master',
            'sql' => "SELECT '".$this->super->email."' as email",
        ])->assertOk()->json();

        $this->assertTrue($response['success']);
        $this->assertSame('read', $response['type']);
        $this->assertSame(['email'], $response['columns']);
        $this->assertSame($this->super->email, $response['rows'][0][0]);
    }

    public function test_a_write_statement_is_refused_without_confirmation_first(): void
    {
        $response = $this->postJson(route('superadmin.query_runner.run'), [
            'connection' => 'master',
            'sql' => "UPDATE users SET name = 'x' WHERE id = 0",
        ])->assertOk()->json();

        $this->assertFalse($response['success'] ?? true);
        $this->assertTrue($response['needs_confirmation']);

        // Nothing was actually run — no audit entry either.
        $this->assertDatabaseMissing('platform_audit_logs', ['event' => 'query_runner.executed']);
    }

    public function test_a_write_statement_runs_once_confirmed_and_reports_affected_rows(): void
    {
        $response = $this->postJson(route('superadmin.query_runner.run'), [
            'connection' => 'master',
            'sql' => "UPDATE users SET name = 'x' WHERE id = 0",
            'confirm' => true,
        ])->assertOk()->json();

        $this->assertTrue($response['success']);
        $this->assertSame('write', $response['type']);
        $this->assertSame(0, $response['affected']); // no user has id 0
    }

    public function test_multiple_statements_in_one_submission_are_rejected(): void
    {
        $response = $this->postJson(route('superadmin.query_runner.run'), [
            'connection' => 'master',
            'sql' => "SELECT 1; DROP TABLE users",
        ])->assertOk()->json();

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('one statement', $response['error']);
    }

    public function test_a_bad_query_returns_the_database_error_and_is_still_audited(): void
    {
        $response = $this->postJson(route('superadmin.query_runner.run'), [
            'connection' => 'master',
            'sql' => 'SELECT * FROM this_table_does_not_exist',
        ])->assertOk()->json();

        $this->assertFalse($response['success']);
        $this->assertNotEmpty($response['error']);

        $log = PlatformAuditLog::where('event', 'query_runner.executed')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertFalse($log->metadata['success']);
    }

    public function test_every_successful_run_is_recorded_in_the_audit_log_with_the_target(): void
    {
        // TENANCY_MODE=shared in tests (phpunit.xml) — no per-tenant
        // database exists to switch to, so picking a company still queries
        // the one shared database rather than failing outright.
        $tenant = Tenant::create(['name' => 'QR Tenant', 'slug' => 'qr-'.Str::random(8), 'status' => 'Active']);

        $response = $this->postJson(route('superadmin.query_runner.run'), [
            'connection' => (string) $tenant->id,
            'sql' => 'SELECT 1',
        ])->assertOk()->json();

        $this->assertTrue($response['success']);

        $log = PlatformAuditLog::where('event', 'query_runner.executed')->latest('id')->first();
        $this->assertSame($tenant->id, $log->tenant_id);
        $this->assertSame($this->super->id, $log->actor_id);
        $this->assertSame('read', $log->metadata['type']);
        $this->assertTrue($log->metadata['success']);
    }

    public function test_in_real_database_tenancy_mode_an_unprovisioned_tenant_fails_cleanly(): void
    {
        // Outside "shared" mode, a company needs a real provisioned
        // database to query — confirms the controller actually calls
        // TenantConnectionManager::activate() for that mode, not just in
        // the shared-mode shortcut exercised by the test above.
        config(['tenancy.mode' => 'database']);
        $tenant = Tenant::create(['name' => 'Unprovisioned Co', 'slug' => 'unprov-'.Str::random(8), 'status' => 'Active']);

        $response = $this->postJson(route('superadmin.query_runner.run'), [
            'connection' => (string) $tenant->id,
            'sql' => 'SELECT 1',
        ])->assertOk()->json();

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('no provisioned database', $response['error']);
    }

    public function test_the_page_loads_with_the_company_picker_and_recent_history(): void
    {
        $this->postJson(route('superadmin.query_runner.run'), ['connection' => 'master', 'sql' => 'SELECT 1'])->assertOk();

        $this->get(route('superadmin.query_runner.index'))
            ->assertOk()
            ->assertSee('Master Database')
            ->assertSee('SELECT 1');
    }

    public function test_listing_tables_against_master_returns_known_tables(): void
    {
        $response = $this->getJson(route('superadmin.query_runner.tables', ['connection' => 'master']))
            ->assertOk()->json();

        $this->assertTrue($response['success']);
        $this->assertContains('users', $response['tables']);
        $this->assertContains('tenants', $response['tables']);
    }

    public function test_tenant_side_users_cannot_reach_the_query_runner(): void
    {
        auth()->logout();
        $tenant = Tenant::create(['name' => 'Blocked Tenant', 'slug' => 'blocked-'.Str::random(8), 'status' => 'Active']);
        $tenantUser = User::create([
            'tenant_id' => $tenant->id, 'name' => 'Regular', 'email' => Str::random(8).'@example.test',
            'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60),
        ]);

        $this->actingAs($tenantUser, 'web')->withSession(['session_token' => $tenantUser->session_token])
            ->get(route('superadmin.query_runner.index'))
            ->assertForbidden();
    }
}
