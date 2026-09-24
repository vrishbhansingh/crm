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
        // Deliberately targets a row that cannot exist: TenantConnectionManager
        // (via the shared `tenant`/`mysql` connection) still participates in
        // this test's own DatabaseTransactions wrapper for the master
        // connection, but exercising that isn't the point here — zero
        // affected rows is still full proof the write path is allowed
        // through, executes without error, and gets audited.
        $response = $this->postJson('/superadmin/query-runner/run', [
            'sql' => 'UPDATE tenants SET name = name WHERE id = 999999999;',
            'mode' => 'single',
            'allow_write' => true,
        ])->assertOk();

        $this->assertSame(0, $response->json('row_count'));

        $log = PlatformAuditLog::where('event', 'query_runner.executed')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertTrue($log->metadata['allow_write']);
        $this->assertTrue($log->metadata['success']);
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

    public function test_a_bad_query_returns_the_database_error_and_is_still_audited(): void
    {
        $this->postJson('/superadmin/query-runner/run', [
            'sql' => 'SELECT * FROM this_table_does_not_exist;',
            'mode' => 'single',
        ])->assertUnprocessable();

        $log = PlatformAuditLog::where('event', 'query_runner.executed')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertFalse($log->metadata['success']);
    }

    public function test_all_tenants_mode_returns_rows_tagged_with_tenant_name(): void
    {
        // Under the test suite's shared tenancy mode, TenantDatabaseProvisioner
        // never sets database_name and connectionFor() falls through to the
        // master connection for every tenant — point both test tenants at
        // real rows so the per-tenant loop provably executes the query
        // rather than just tolerating a connection failure per iteration.
        $tenantA = Tenant::create(['name' => 'QR Tenant A', 'slug' => 'qr-a-'.Str::lower(Str::random(8)), 'status' => 'Active', 'provision_status' => 'ready']);
        $tenantB = Tenant::create(['name' => 'QR Tenant B', 'slug' => 'qr-b-'.Str::lower(Str::random(8)), 'status' => 'Active', 'provision_status' => 'ready']);

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

    public function test_in_real_database_tenancy_mode_an_unprovisioned_tenant_fails_cleanly(): void
    {
        // Outside "shared" mode, a company needs a real provisioned
        // database to query — confirms connectionFor() actually calls
        // TenantConnectionManager::activate() for that mode, not just the
        // shared-mode shortcut exercised by the tests above.
        config(['tenancy.mode' => 'database']);
        $tenant = Tenant::create(['name' => 'Unprovisioned Co', 'slug' => 'unprov-'.Str::lower(Str::random(8)), 'status' => 'Active']);

        $this->postJson('/superadmin/query-runner/run', [
            'sql' => 'SELECT 1;',
            'mode' => 'single',
            'tenant_id' => $tenant->id,
        ])->assertUnprocessable();
    }

    public function test_listing_tables_against_master_returns_known_tables(): void
    {
        $response = $this->getJson('/superadmin/query-runner/tables?connection=master')->assertOk();

        $this->assertTrue($response->json('status'));
        $this->assertContains('tenants', $response->json('tables'));
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
