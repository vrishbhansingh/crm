<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Every other registration test runs under TENANCY_MODE=shared (phpunit.xml),
 * where TenantDatabaseProvisioner::provision() short-circuits and never
 * creates a real per-tenant database — so those tests can't catch anything
 * that only breaks against a genuine database (the FK-violation-on-audit-log
 * bug that took down production self-service registration never surfaced in
 * them). This test forces real 'database' mode and drives the whole thing
 * through the actual HTTP endpoint: register -> real provisioning ->
 * auto-approval -> login -> a real tenant-scoped page, exactly as it happens
 * in production. Deliberately not wrapped in DatabaseTransactions, for the
 * same reason as TenantDeletionTest: CREATE/DROP DATABASE implicitly commits
 * on MySQL. Cleans up everything by hand in tearDown().
 */
class RegistrationRealProvisioningTest extends TestCase
{
    private ?Tenant $tenant = null;

    protected function tearDown(): void
    {
        \Illuminate\Database\Eloquent\Model::withoutEvents(function () {
            if ($this->tenant) {
                User::where('tenant_id', $this->tenant->id)->delete();
                if ($this->tenant->database_name) {
                    DB::connection('mysql')->statement('DROP DATABASE IF EXISTS `'.$this->tenant->database_name.'`');
                }
                Tenant::whereKey($this->tenant->id)->delete();
            }
        });

        parent::tearDown();
    }

    public function test_self_service_registration_provisions_a_real_database_and_the_admin_can_log_in_immediately(): void
    {
        \Illuminate\Database\Eloquent\Model::withoutEvents(fn () => $this->runScenario());
    }

    private function runScenario(): void
    {
        config(['tenancy.mode' => 'database', 'tenancy.require_approval' => false]);

        $email = Str::lower(Str::random(10)).'@example.test';

        $this->post('/register', [
            'organization_name' => 'Real Provision Co',
            'name' => 'New Owner',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertRedirect(route('register.success'))
            ->assertSessionHas('auto_approved', true);

        $this->tenant = Tenant::where('contact_email', $email)->firstOrFail();

        // No waiting on Super Admin approval: active immediately, with a
        // real isolated database actually provisioned (not just the
        // shared-mode shortcut's provision_status flag).
        $this->assertSame('Active', $this->tenant->status);
        $this->assertSame('approved', $this->tenant->approval_status);
        $this->assertSame('ready', $this->tenant->provision_status);
        $this->assertNotEmpty($this->tenant->database_name);

        $schemaExists = DB::select('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?', [$this->tenant->database_name]);
        $this->assertNotEmpty($schemaExists, 'A real per-tenant database should exist.');

        $admin = User::where('email', $email)->firstOrFail();
        $this->assertSame('Active', $admin->status);

        // Log in as the brand-new admin and load a real tenant-scoped page —
        // proves ActivateTenantDatabase can actually connect to the freshly
        // provisioned database, not just that the row says "ready".
        $this->actingAs($admin, 'web')
            ->withSession(['session_token' => $admin->session_token])
            ->get('/dashboard')
            ->assertOk();
    }
}
