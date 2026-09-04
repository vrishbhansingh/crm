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
            ->postJson('/roles', ['name' => 'Agent', 'permissions' => ['leads.view', 'leads.create']])
            ->assertOk()->assertJsonPath('status', true);

        $role = Role::where('tenant_id', $tenant->id)->where('name', 'Agent')->firstOrFail();
        $this->assertTrue($role->hasPermissionTo('leads.view'));
        $this->assertDatabaseMissing('roles', ['tenant_id' => $otherTenant->id, 'name' => 'Agent']);
    }

    public function test_edit_info_and_manage_access_are_independent_actions(): void
    {
        [$tenant, $admin] = $this->tenantAdmin('three');
        $this->actingAs($admin)->withSession(['session_token' => $admin->session_token]);

        $this->postJson('/roles', ['name' => 'Agent', 'description' => 'Front-line sales', 'permissions' => ['leads.view']])
            ->assertOk();
        $role = Role::where('tenant_id', $tenant->id)->where('name', 'Agent')->firstOrFail();

        // Edit Info changes name/description but leaves permissions alone.
        $this->putJson("/roles/{$role->id}", ['name' => 'Senior Agent', 'description' => 'Updated'])
            ->assertOk()->assertJsonPath('status', true);
        $role->refresh();
        $this->assertSame('Senior Agent', $role->name);
        $this->assertSame('Updated', $role->description);
        $this->assertTrue($role->hasPermissionTo('leads.view'));

        // Manage Access changes permissions but leaves the name alone.
        $this->putJson("/roles/{$role->id}/permissions", ['permissions' => ['leads.view', 'leads.edit']])
            ->assertOk()->assertJsonPath('status', true);
        $role->refresh();
        $this->assertSame('Senior Agent', $role->name);
        $this->assertTrue($role->hasPermissionTo('leads.edit'));
    }

    public function test_roles_data_and_permission_catalog_endpoints(): void
    {
        [$tenant, $admin] = $this->tenantAdmin('four');
        $this->actingAs($admin)->withSession(['session_token' => $admin->session_token]);

        $this->getJson('/roles/data')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Admin', 'is_protected' => true]);

        $this->getJson('/roles/permissions')
            ->assertOk()
            ->assertJsonStructure(['status', 'groups' => [['module', 'label', 'permissions']], 'sensitive', 'total'])
            ->assertJsonMissing(['module' => 'roles']);
    }

    public function test_permission_catalog_only_offers_actions_a_real_route_enforces(): void
    {
        [$tenant, $admin] = $this->tenantAdmin('five');
        $this->actingAs($admin)->withSession(['session_token' => $admin->session_token]);

        $response = $this->getJson('/roles/permissions')->assertOk()->json();
        $allNames = collect($response['groups'])->flatMap(fn ($g) => collect($g['permissions'])->pluck('name'));

        // Real: routes/app.php actually gates these with `permission:`.
        $this->assertTrue($allNames->contains('leads.assign'));
        $this->assertTrue($allNames->contains('deals.manage-settings'));

        // Not real: no route checks these, so they must not appear as
        // offerable checkboxes even though the seeder's blanket
        // module x action cross-product created rows for them.
        $this->assertFalse($allNames->contains('contacts.reject'));
        $this->assertFalse($allNames->contains('companies.approve'));
        $this->assertFalse($allNames->contains(fn ($name) => str_starts_with($name, 'projects.')));
        $this->assertFalse($allNames->contains(fn ($name) => str_starts_with($name, 'attendance.')));
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
