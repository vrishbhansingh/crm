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

    public function test_admin_can_create_a_tenant_scoped_custom_role_with_no_permissions_yet(): void
    {
        [$tenant, $admin] = $this->tenantAdmin('one');
        $otherTenant = Tenant::create(['name' => 'Other Tenant', 'slug' => 'other-'.Str::random(8), 'status' => 'Active']);

        // Create asks only for a name — permissions are a separate step.
        $this->actingAs($admin)->withSession(['session_token' => $admin->session_token])
            ->postJson('/roles', ['name' => 'Agent'])
            ->assertOk()->assertJsonPath('status', true);

        $role = Role::where('tenant_id', $tenant->id)->where('name', 'Agent')->firstOrFail();
        $this->assertCount(0, $role->permissions);
        $this->assertDatabaseMissing('roles', ['tenant_id' => $otherTenant->id, 'name' => 'Agent']);

        // Manage Permissions is where access actually gets assigned.
        $this->putJson("/roles/{$role->id}/permissions", ['permissions' => ['leads.view', 'leads.create']])
            ->assertOk()->assertJsonPath('status', true);
        $this->assertTrue($role->fresh()->hasPermissionTo('leads.view'));
    }

    public function test_edit_info_and_manage_access_are_independent_actions(): void
    {
        [$tenant, $admin] = $this->tenantAdmin('three');
        $this->actingAs($admin)->withSession(['session_token' => $admin->session_token]);

        $this->postJson('/roles', ['name' => 'Agent', 'description' => 'Front-line sales'])
            ->assertOk();
        $role = Role::where('tenant_id', $tenant->id)->where('name', 'Agent')->firstOrFail();
        $role->syncPermissions(['leads.view']);

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

        // roles.* is now a real, delegable module — no longer hidden.
        $this->getJson('/roles/permissions')
            ->assertOk()
            ->assertJsonStructure(['status', 'groups' => [['module', 'label', 'permissions']], 'total'])
            ->assertJsonFragment(['module' => 'roles']);
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

        // Sensitive and not delegable — no longer offered at all, not even
        // in a separate "sensitive" section.
        $this->assertFalse($allNames->contains('users.impersonate'));
        $this->assertArrayNotHasKey('sensitive', $response);
    }

    public function test_roles_create_permission_is_now_genuinely_delegable_not_just_symbolic(): void
    {
        [$tenant] = $this->tenantAdmin('two');
        PermissionTeam::set($tenant->id);

        // Granted roles.create: this is the whole point of curating roles.*
        // into the picker — it must actually work for a non-Admin holder,
        // not just look like it does.
        $delegateRole = Role::create(['tenant_id' => $tenant->id, 'name' => 'Delegate', 'guard_name' => 'web']);
        $delegateRole->syncPermissions(Permission::whereIn('name', ['roles.view', 'roles.create'])->get());
        $delegate = User::create(['tenant_id' => $tenant->id, 'name' => 'Delegate', 'email' => Str::random(10).'@example.test', 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60)]);
        $delegate->assignRole($delegateRole);

        $this->actingAs($delegate)->withSession(['session_token' => $delegate->session_token])
            ->postJson('/roles', ['name' => 'Created By Delegate'])
            ->assertOk()->assertJsonPath('status', true);
        $this->assertDatabaseHas('roles', ['tenant_id' => $tenant->id, 'name' => 'Created By Delegate']);

        // No roles.create: still genuinely blocked, at the route/middleware
        // level, not by a hardcoded "must be the literal Admin" check.
        $bystanderRole = Role::create(['tenant_id' => $tenant->id, 'name' => 'Bystander', 'guard_name' => 'web']);
        $bystanderRole->syncPermissions(Permission::whereIn('name', ['leads.view'])->get());
        $bystander = User::create(['tenant_id' => $tenant->id, 'name' => 'Bystander', 'email' => Str::random(10).'@example.test', 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60)]);
        $bystander->assignRole($bystanderRole);

        $this->actingAs($bystander)->withSession(['session_token' => $bystander->session_token])
            ->post('/roles', ['name' => 'Forbidden Role'])
            ->assertForbidden();
    }

    public function test_admin_permissions_are_editable_except_roles_which_stays_locked_on(): void
    {
        [$tenant, $admin] = $this->tenantAdmin('six');
        $adminRole = $admin->roles()->where('roles.tenant_id', $tenant->id)->firstOrFail();
        $this->actingAs($admin)->withSession(['session_token' => $admin->session_token]);

        // Attempt to submit Admin's permissions with roles.* deliberately
        // left out, and nothing else granted either.
        $this->putJson("/roles/{$adminRole->id}/permissions", ['permissions' => ['leads.view']])
            ->assertOk()->assertJsonPath('status', true);

        $adminRole->refresh();
        // The one thing that survives regardless of what was submitted.
        $this->assertTrue($adminRole->hasPermissionTo('roles.view'));
        $this->assertTrue($adminRole->hasPermissionTo('roles.create'));
        $this->assertTrue($adminRole->hasPermissionTo('roles.edit'));
        $this->assertTrue($adminRole->hasPermissionTo('roles.delete'));
        // Genuinely editable — what was submitted is what it has, roles.*
        // aside. Not silently kept at "every permission" as it used to be.
        $this->assertTrue($adminRole->hasPermissionTo('leads.view'));
        $this->assertFalse($adminRole->hasPermissionTo('users.view'));
    }

    public function test_admin_role_name_and_deletion_stay_protected_even_though_permissions_do_not(): void
    {
        [$tenant, $admin] = $this->tenantAdmin('seven');
        $adminRole = $admin->roles()->where('roles.tenant_id', $tenant->id)->firstOrFail();
        $this->actingAs($admin)->withSession(['session_token' => $admin->session_token]);

        $this->putJson("/roles/{$adminRole->id}", ['name' => 'Renamed'])->assertForbidden();
        $this->deleteJson("/roles/{$adminRole->id}")->assertForbidden();
        $this->assertSame('Admin', $adminRole->fresh()->name);
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
