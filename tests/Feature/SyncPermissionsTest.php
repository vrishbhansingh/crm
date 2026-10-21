<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Support\PermissionTeam;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SyncPermissionsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sync_grants_new_module_permissions_to_company_admins_but_never_touches_custom_roles(): void
    {
        $s = Str::lower(Str::random(8));
        $tenant = Tenant::create(['name' => 'Old Server Co', 'slug' => 'sync-'.$s, 'status' => 'Active']);
        $admin = User::create(['tenant_id' => $tenant->id, 'name' => 'Old Admin', 'email' => "old-admin-{$s}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(30)]);
        $agent = User::create(['tenant_id' => $tenant->id, 'name' => 'Old Agent', 'email' => "old-agent-{$s}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(30)]);
        $tenant->forceFill(['admin_user_id' => $admin->id])->save();

        // An "old server": the Admin role only knows the permissions that existed back then.
        PermissionTeam::run($tenant->id, function () use ($admin, $agent) {
            $adminRole = Role::findOrCreate('Admin', 'web');
            $adminRole->syncPermissions([Permission::findOrCreate('leads.view', 'web')]);
            $admin->assignRole($adminRole);

            $agentRole = Role::findOrCreate('Agent', 'web');
            $agentRole->syncPermissions([Permission::findOrCreate('leads.view', 'web')]);
            $agent->assignRole($agentRole);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        PermissionTeam::set($tenant->id);
        $this->assertFalse($admin->fresh()->can('integrations.view'), 'precondition: menu would be hidden');

        $this->artisan('crm:sync-permissions')->assertSuccessful();

        PermissionTeam::set($tenant->id);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $admin = $admin->fresh();
        foreach (['integrations.view', 'leads.manage-settings', 'deals.manage-settings', 'deals.view', 'whatsapp.view', 'whatsapp.manage-settings'] as $permission) {
            $this->assertTrue($admin->can($permission), "Admin should hold {$permission} after the sync");
        }
        $this->assertFalse($agent->fresh()->can('integrations.view'), 'custom roles are left exactly as the company set them');
        PermissionTeam::set(null);
    }
}
