<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Support\PermissionTeam;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    private const GUARD = 'web';

    private array $modules = [
        'leads', 'orders', 'companies', 'contacts', 'tasks', 'reports', 'audit',
        'users', 'roles', 'company', 'attendance', 'masters', 'deals',
    ];

    private array $actions = [
        'view', 'create', 'edit', 'delete', 'import', 'export', 'assign',
        'approve', 'reject', 'share', 'manage-settings',
    ];

    public function run(): void
    {
        foreach ($this->modules as $module) {
            foreach ($this->actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => self::GUARD]);
            }
        }
        foreach (['platform.manage-tenants', 'users.impersonate'] as $extra) {
            Permission::firstOrCreate(['name' => $extra, 'guard_name' => self::GUARD]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $all = Permission::where('guard_name', self::GUARD)->get();
        $tenantPermissions = $all->reject(fn (Permission $permission) => str_starts_with($permission->name, 'platform.'));

        PermissionTeam::run(null, function () use ($all) {
            Role::findOrCreate('Super Admin', self::GUARD)->syncPermissions($all);
        });

        foreach (Tenant::whereNotNull('admin_user_id')->get() as $tenant) {
            PermissionTeam::run($tenant->id, function () use ($tenant, $tenantPermissions) {
                $role = Role::findOrCreate('Admin', self::GUARD);
                $role->syncPermissions($tenantPermissions);
                $tenant->admin?->syncRoles([$role]);
            });
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
