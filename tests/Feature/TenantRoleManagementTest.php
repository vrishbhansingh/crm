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
use Tests\TestCase;

class TenantRoleManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_create_a_tenant_scoped_custom_role(): void
    {
        [$tenant, $admin] = $this->tenantAdmin('one');
        $otherTenant = Tenant::create(['name' => 'Other Tenant', 'slug' => 'other-'.Str::random(8), 'status' => 'Active']);

        $this->actingAs($admin)->withSession(['session_token' => $admin->session_token])
            ->post('/roles', ['name' => 'Agent', 'permissions' => ['leads.view', 'leads.create']])
            ->assertRedirect()->assertSessionHas('success');

        $role = Role::where('tenant_id', $tenant->id)->where('name', 'Agent')->firstOrFail();
        $this->assertTrue($role->hasPermissionTo('leads.view'));
        $this->assertDatabaseMissing('roles', ['tenant_id' => $otherTenant->id, 'name' => 'Agent']);
    }

    public function test_non_admin_cannot_manage_roles_even_if_given_role_permissions(): void
    {
        [$tenant, $admin] = $this->tenantAdmin('two');
        PermissionTeam::set($tenant->id);
        $managerRole = Role::create(['tenant_id' => $tenant->id, 'name' => 'Manager', 'guard_name' => 'web']);
        $managerRole->syncPermissions(Permission::whereIn('name', ['roles.view', 'roles.create'])->get());
        $manager = User::create(['tenant_id' => $tenant->id, 'name' => 'Manager', 'email' => Str::random(10).'@example.test', 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60)]);
        $manager->assignRole($managerRole);

        $this->actingAs($manager)->withSession(['session_token' => $manager->session_token])
            ->post('/roles', ['name' => 'Forbidden Role', 'permissions' => ['leads.view']])
            ->assertForbidden();
    }

    private function tenantAdmin(string $label): array
    {
        $tenant = Tenant::create(['name' => "Tenant {$label}", 'slug' => $label.'-'.Str::random(8), 'status' => 'Active']);
        $admin = User::create(['tenant_id' => $tenant->id, 'name' => 'Admin', 'email' => Str::random(10).'@example.test', 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60)]);
        $tenant->update(['admin_user_id' => $admin->id]);
        PermissionTeam::set($tenant->id);
        $adminRole = Role::create(['tenant_id' => $tenant->id, 'name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::whereIn('name', ['roles.view', 'roles.create', 'roles.edit', 'roles.delete'])->get());
        $admin->assignRole($adminRole);

        return [$tenant, $admin];
    }
}
