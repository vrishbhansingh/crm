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

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'tenant_id' => null, 'name' => 'Query Runner QA',
            'email' => Str::lower(Str::random(10)).'@example.test', 'password' => Hash::make('password'),
            'status' => 'Active', 'session_token' => Str::random(60),
        ]);
        PermissionTeam::run(null, function () {
            Role::findOrCreate('Super Admin', 'web');
            $this->superAdmin->assignRole('Super Admin');
        });
        $this->actingAs($this->superAdmin, 'web')->withSession(['session_token' => $this->superAdmin->session_token]);
    }

    public function test_index_page_renders(): void
    {
        $this->get('/superadmin/query-runner')->assertOk();
    }

    public function test_a_read_statement_against_master_returns_rows(): void
    {
        $response = $this->postJson('/superadmin/query-runner/run', [
            'sql' => 'SELECT id, name FROM tenants LIMIT 5;',
            'mode' => 'single',
        ])->assertOk();

        $this->assertTrue($response->json('status'));
        $this->assertContains('id', $response->json('columns'));
    }

    public function test_a_write_statement_is_rejected_without_allow_write(): void
    {
        $this->postJson('/superadmin/query-runner/run', [
            'sql' => "UPDATE tenants SET name = 'x' WHERE id = 999999;",
            'mode' => 'single',
        ])->assertUnprocessable();
    }

    public function test_a_write_statement_succeeds_with_allow_write_and_is_audited(): void
    {
        // Deliberately targets a row that cannot exist: the query runner's
        // connection is a genuinely separate PDO connection (by design —
        // it must not join the current request's own transaction), so a
        // row this same test just inserted via Eloquent is invisible to it
        // until commit, and MySQL lock-waits on the update instead of
        // simply finding 0 matches. Zero affected rows is still a fully
        // valid proof that the write path is allowed through, executes
        // without error, and gets audited.
        $response = $this->postJson('/superadmin/query-runner/run', [
            'sql' => 'UPDATE tenants SET name = name WHERE id = 999999999;',
            'mode' => 'single',
            'allow_write' => true,
        ])->assertOk();

        $this->assertSame(0, $response->json('row_count'));

        $log = PlatformAuditLog::where('event', 'query_runner.execute')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertTrue($log->metadata['allow_write']);
    }

    public function test_a_multi_statement_query_is_rejected(): void
    {
        $this->postJson('/superadmin/query-runner/run', [
            'sql' => 'SELECT 1; SELECT 2;',
            'mode' => 'single',
        ])->assertUnprocessable();
    }

    public function test_ddl_is_rejected_without_allow_ddl_even_with_allow_write(): void
    {
        $this->postJson('/superadmin/query-runner/run', [
            'sql' => 'DROP TABLE IF EXISTS some_table_that_should_not_be_touched;',
            'mode' => 'single',
            'allow_write' => true,
        ])->assertUnprocessable();
    }

    public function test_all_tenants_mode_returns_rows_tagged_with_tenant_name(): void
    {
        // Under the test suite's shared tenancy mode, TenantDatabaseProvisioner
        // never sets database_name — point both test tenants at real,
        // always-present databases (database_name has a unique constraint,
        // so they can't share one) so connectTo() succeeds for both and this
        // proves the per-tenant loop actually executes the query, not just
        // that it tolerates a connection failure (the controller's
        // catch-and-continue path would otherwise mask a broken query
        // behind a superficially-passing assertion).
        $testDatabase = config('database.connections.'.config('tenancy.master_connection', 'mysql').'.database');
        $tenantA = Tenant::create(['name' => 'QR Tenant A', 'slug' => 'qr-a-'.Str::lower(Str::random(8)), 'status' => 'Active', 'provision_status' => 'ready', 'database_name' => $testDatabase]);
        $tenantB = Tenant::create(['name' => 'QR Tenant B', 'slug' => 'qr-b-'.Str::lower(Str::random(8)), 'status' => 'Active', 'provision_status' => 'ready', 'database_name' => 'information_schema']);

        $response = $this->postJson('/superadmin/query-runner/run', [
            'sql' => 'SELECT 1 AS one;',
            'mode' => 'all',
        ])->assertOk();

        $rows = collect($response->json('rows'));
        $rowForA = $rows->firstWhere('tenant_name', $tenantA->name);
        $rowForB = $rows->firstWhere('tenant_name', $tenantB->name);
        $this->assertNotNull($rowForA);
        $this->assertNotNull($rowForB);
        $this->assertSame(1, $rowForA['one']);
        $this->assertArrayNotHasKey('error', $rowForA);
    }

    public function test_a_tenant_user_cannot_reach_the_query_runner(): void
    {
        auth()->logout();
        $tenant = Tenant::create(['name' => 'QR Blocked Co', 'slug' => 'qr-blocked-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $tenantUser = User::create([
            'tenant_id' => $tenant->id, 'name' => 'Regular Admin',
            'email' => Str::lower(Str::random(10)).'@example.test', 'password' => Hash::make('password'),
            'status' => 'Active', 'session_token' => Str::random(60),
        ]);
        PermissionTeam::run($tenant->id, function () use ($tenantUser) {
            Role::findOrCreate('Admin', 'web');
            $tenantUser->assignRole('Admin');
        });

        $this->actingAs($tenantUser, 'web')->withSession(['session_token' => $tenantUser->session_token])
            ->get('/superadmin/query-runner')
            ->assertForbidden();
    }
}
