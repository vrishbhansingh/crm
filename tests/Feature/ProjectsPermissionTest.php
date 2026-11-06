<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Support\DefaultTenantRoles;
use App\Support\PermissionTeam;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProjectsPermissionTest extends TestCase
{
    use DatabaseTransactions;

    private function tenant(): Tenant
    {
        $s = Str::lower(Str::random(8));
        $tenant = Tenant::create(['name' => 'Perm Co', 'slug' => 'perm-'.$s, 'status' => 'Active']);
        $admin = User::create(['tenant_id' => $tenant->id, 'name' => 'Perm Admin', 'email' => "pa-{$s}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(30)]);
        $tenant->forceFill(['admin_user_id' => $admin->id])->save();

        return $tenant;
    }

    private function userWith(Tenant $tenant, array $permissions): User
    {
        $user = User::create(['tenant_id' => $tenant->id, 'name' => 'Person', 'email' => Str::random(8).'@example.test', 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(30)]);
        PermissionTeam::run($tenant->id, function () use ($user, $permissions) {
            $role = Role::create(['name' => 'R'.Str::random(5), 'guard_name' => 'web', 'tenant_id' => $user->tenant_id]);
            $role->syncPermissions(collect($permissions)->map(fn ($p) => Permission::findOrCreate($p, 'web')));
            $user->assignRole($role);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    public function test_projects_can_be_switched_on_or_off_independently_of_orders(): void
    {
        $tenant = $this->tenant();
        TenantContext::set($tenant->id);
        PermissionTeam::set($tenant->id);

        $ordersOnly = $this->userWith($tenant, ['orders.view']);
        $this->actingAs($ordersOnly, 'web')->withSession(['session_token' => $ordersOnly->session_token]);
        $this->get('/projects')->assertForbidden();
        $this->get('/orders')->assertOk();

        $projectsOnly = $this->userWith($tenant, ['projects.view']);
        $this->actingAs($projectsOnly, 'web')->withSession(['session_token' => $projectsOnly->session_token]);
        $this->get('/projects')->assertOk();
        $this->get('/orders')->assertForbidden();

        TenantContext::clear();
        PermissionTeam::set(null);
    }

    public function test_the_default_agent_role_covers_quotations_products_and_projects_but_nothing_admin_level(): void
    {
        $tenant = $this->tenant();
        foreach (DefaultTenantRoles::agentPermissions() as $name) {
            Permission::findOrCreate($name, 'web');
        }
        $role = PermissionTeam::run($tenant->id, fn () => DefaultTenantRoles::seedAgentRole());
        $granted = $role->permissions()->pluck('name')->all();

        foreach (['quotations.view', 'quotations.create', 'quotations.edit', 'products.view', 'projects.view', 'leads.view', 'tasks.create'] as $p) {
            $this->assertContains($p, $granted);
        }
        foreach (['users.view', 'roles.view', 'reports.view', 'campaigns.view', 'masters.view', 'company.manage-settings', 'leads.delete', 'projects.edit'] as $p) {
            $this->assertNotContains($p, $granted);
        }
    }

    public function test_the_upgrade_keeps_existing_projects_access_and_tops_up_or_creates_agent_roles_without_removing_anything(): void
    {
        $withAgent = $this->tenant();
        $withoutAgent = $this->tenant();

        PermissionTeam::run($withAgent->id, function () use ($withAgent) {
            $agent = Role::create(['name' => 'Agent', 'guard_name' => 'web', 'tenant_id' => $withAgent->id]);
            $agent->syncPermissions([Permission::findOrCreate('leads.view', 'web'), Permission::findOrCreate('contacts.delete', 'web')]);
            $custom = Role::create(['name' => 'Ops', 'guard_name' => 'web', 'tenant_id' => $withAgent->id]);
            $custom->syncPermissions([Permission::findOrCreate('orders.view', 'web'), Permission::findOrCreate('orders.edit', 'web')]);
        });

        (include database_path('migrations/2026_11_06_100001_split_projects_permission_and_upgrade_agent_role.php'))->up();

        PermissionTeam::run($withAgent->id, function () use ($withAgent) {
            $agent = Role::where('tenant_id', $withAgent->id)->where('name', 'Agent')->first();
            $names = $agent->permissions()->pluck('name')->all();
            $this->assertContains('quotations.view', $names);
            $this->assertContains('leads.view', $names);
            $this->assertContains('contacts.delete', $names, 'nothing the company granted is removed');

            $ops = Role::where('tenant_id', $withAgent->id)->where('name', 'Ops')->first()->permissions()->pluck('name')->all();
            $this->assertContains('projects.view', $ops);
            $this->assertContains('projects.edit', $ops);
        });

        PermissionTeam::run($withoutAgent->id, function () use ($withoutAgent) {
            $created = Role::where('tenant_id', $withoutAgent->id)->where('name', 'Agent')->first();
            $this->assertNotNull($created, 'a missing Agent role is created');
            $this->assertContains('quotations.create', $created->permissions()->pluck('name')->all());
        });
    }
}
