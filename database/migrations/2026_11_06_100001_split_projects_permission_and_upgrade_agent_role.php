<?php

use App\Models\Tenant;
use App\Support\DefaultTenantRoles;
use App\Support\PermissionTeam;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * One-off, so a deliberate later change by a company admin is never undone:
 *
 * 1. Projects got their own switches (projects.view / projects.edit). Roles
 *    that could open Projects through orders.view / orders.edit keep that
 *    access by being granted the new permissions once.
 * 2. Every company gets the full default Agent role: missing Agent roles are
 *    created, existing ones are topped up with the permissions the defaults
 *    gained (quotations, products, projects). Nothing is ever removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['projects.view', 'projects.edit'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        foreach (DefaultTenantRoles::agentPermissions() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $pivot = config('permission.table_names.role_has_permissions', 'role_has_permissions');
        $ids = Permission::where('guard_name', 'web')->pluck('id', 'name');

        foreach (['orders.view' => 'projects.view', 'orders.edit' => 'projects.edit'] as $old => $new) {
            DB::table($pivot)->where('permission_id', $ids[$old] ?? 0)->pluck('role_id')->each(function ($roleId) use ($pivot, $ids, $new) {
                DB::table($pivot)->updateOrInsert(['permission_id' => $ids[$new], 'role_id' => $roleId]);
            });
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Tenant::query()->whereNotNull('admin_user_id')->orderBy('id')->each(function (Tenant $tenant) {
            PermissionTeam::run($tenant->id, function () use ($tenant) {
                $role = Role::where('tenant_id', $tenant->id)->where('name', 'Agent')->where('guard_name', 'web')->first();
                if ($role) {
                    $role->givePermissionTo(Permission::whereIn('name', DefaultTenantRoles::agentPermissions())->get());
                } else {
                    DefaultTenantRoles::seedAgentRole();
                }
            });
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Permissions stay; removing grants could lock people out.
    }
};
