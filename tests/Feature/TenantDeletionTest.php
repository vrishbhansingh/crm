<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantDatabaseProvisioner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Exercises the real provision -> backup -> drop path for TenantController's
 * "Delete company" feature. Deliberately NOT wrapped in DatabaseTransactions:
 * CREATE DATABASE / DROP DATABASE are DDL statements that implicitly commit
 * on MySQL, which would silently break transactional rollback for the rest
 * of a transactional test — exactly why the rest of the suite runs with
 * TENANCY_MODE=shared and never touches a real per-tenant database. This
 * test cleans up everything it creates by hand in tearDown() instead.
 */
class TenantDeletionTest extends TestCase
{
    private ?Tenant $tenant = null;
    private ?User $superAdmin = null;
    private ?string $backupFile = null;

    protected function tearDown(): void
    {
        if ($this->backupFile && file_exists($this->backupFile)) {
            unlink($this->backupFile);
        }

        // The test flips tenancy.mode to 'database' but the CrmAuditObserver
        // on User was registered at boot against this suite's 'shared'
        // default (see the comment on the test method) — still true here,
        // after the test body's own withoutEvents() wrapper has exited.
        \Illuminate\Database\Eloquent\Model::withoutEvents(function () {
            if ($this->tenant) {
                User::where('tenant_id', $this->tenant->id)->delete();
                if ($this->tenant->database_name) {
                    DB::connection('mysql')->statement('DROP DATABASE IF EXISTS `'.$this->tenant->database_name.'`');
                }
                Tenant::whereKey($this->tenant->id)->delete();
            }

            $this->superAdmin?->delete();
        });

        parent::tearDown();
    }

    public function test_deleting_a_tenant_backs_up_and_drops_its_real_database_and_removes_its_users(): void
    {
        // CrmAuditObserver is only attached to User when the app boots with
        // tenancy.mode=shared (this test's default, from phpunit.xml) —
        // deliberately, since in real database mode no tenant connection is
        // active for central `users` table writes and auditing them would
        // throw (AppServiceProvider.php:41-43). Flipping to database mode
        // below (needed to provision a real per-tenant database) leaves
        // that already-registered observer stranded, so every User
        // create/delete for the rest of this test would hit exactly that
        // throw. It's unrelated to what this test verifies, so it's
        // suppressed for the whole scenario rather than worked around
        // piecemeal at each User touchpoint.
        \Illuminate\Database\Eloquent\Model::withoutEvents(fn () => $this->runScenario());
    }

    private function runScenario(): void
    {
        $this->superAdmin = User::create([
            'tenant_id' => null,
            'name' => 'Platform QA',
            'email' => 'platform-'.Str::lower(Str::random(10)).'@example.test',
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => 'session-'.Str::random(10),
        ]);
        Role::findOrCreate('Super Admin', 'web');
        foreach (['platform.manage-tenants', 'companies.view', 'companies.create'] as $permission) {
            $this->superAdmin->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        $this->superAdmin->assignRole('Super Admin');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        config(['tenancy.mode' => 'database']);

        $slug = 'real-delete-'.Str::lower(Str::random(8));
        $this->tenant = Tenant::create([
            'name' => 'Real Delete Co '.$slug,
            'slug' => $slug,
            'status' => 'Active',
            'plan' => 'trial',
            'timezone' => 'Asia/Kolkata',
            'locale' => 'en',
        ]);
        app(TenantDatabaseProvisioner::class)->provision($this->tenant);
        $this->tenant->refresh();
        $this->assertNotEmpty($this->tenant->database_name, 'Provisioning should have created a real tenant database.');

        $admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Real Delete Admin',
            'email' => $slug.'@example.test',
            'password' => Hash::make('password123'),
            'status' => 'Active',
            'session_token' => Str::random(60),
        ]);
        $this->tenant->update(['admin_user_id' => $admin->id]);
        $databaseName = $this->tenant->database_name;
        $tenantId = $this->tenant->id;

        $this->actingAs($this->superAdmin, 'web')
            ->withSession(['session_token' => $this->superAdmin->session_token])
            ->delete("/superadmin/companies/{$tenantId}", ['confirm_name' => $this->tenant->name])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('tenants', ['id' => $tenantId]);
        $this->assertDatabaseMissing('users', ['id' => $admin->id]);

        $schemaExists = DB::select('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?', [$databaseName]);
        $this->assertEmpty($schemaExists, 'The tenant database should have been dropped.');

        $backupFiles = glob(storage_path('app/tenant-backups/'.$slug.'-'.$tenantId.'-*.sql'));
        $this->assertNotEmpty($backupFiles, 'A backup file should have been written before dropping the database.');
        $this->backupFile = $backupFiles[0];
        $this->assertStringContainsString('INSERT INTO', file_get_contents($this->backupFile));

        // destroy() already deleted the tenant row and dropped its database
        // for real — clear the reference so tearDown() doesn't try again.
        $this->tenant = null;
    }
}
