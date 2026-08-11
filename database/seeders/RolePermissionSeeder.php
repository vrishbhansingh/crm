<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds the module.action permission catalog and the 10 default roles from
 * the modernization brief. Permissions are granted at the module level here
 * (e.g. "leads.view" for the whole tenant) — per-record scoping ("only my
 * own leads") is a Policy-level concern introduced when each module is
 * rebuilt in its own phase, not part of this RBAC foundation.
 */
class RolePermissionSeeder extends Seeder
{
    private const GUARD = 'web';

    private array $modules = [
        'leads', 'orders', 'companies', 'contacts', 'tasks', 'reports', 'audit', 'users', 'roles', 'company', 'attendance', 'masters', 'deals',
    ];

    private array $actions = [
        'view', 'create', 'edit', 'delete', 'import', 'export',
        'assign', 'approve', 'reject', 'share', 'manage-settings',
    ];

    public function run(): void
    {
        foreach ($this->modules as $module) {
            foreach ($this->actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => self::GUARD,
                ]);
            }
        }

        foreach (['platform.manage-tenants', 'users.impersonate'] as $extra) {
            Permission::firstOrCreate(['name' => $extra, 'guard_name' => self::GUARD]);
        }

        $allPermissions = Permission::where('guard_name', self::GUARD)->pluck('name')->all();
        $allExceptPlatform = array_values(array_diff($allPermissions, ['platform.manage-tenants']));

        foreach (array_keys($this->roleDefaults($allExceptPlatform)) as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => self::GUARD]);
        }

        foreach ($this->roleDefaults($allExceptPlatform) as $roleName => $permissions) {
            Role::findByName($roleName, self::GUARD)->syncPermissions($permissions);
        }
    }

    private function perms(array $modules, array $actions): array
    {
        $names = [];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $names[] = "{$module}.{$action}";
            }
        }

        return $names;
    }

    private function roleDefaults(array $allExceptPlatform): array
    {
        return [
            'Super Admin' => array_merge($allExceptPlatform, ['platform.manage-tenants']),

            'Company Admin' => $allExceptPlatform,

            'Manager' => array_merge(
                $this->perms(['leads', 'orders', 'companies', 'contacts', 'tasks', 'attendance', 'deals'], ['view', 'create', 'edit', 'assign', 'approve', 'reject', 'export']),
                $this->perms(['users'], ['view']),
                $this->perms(['audit', 'reports'], ['view']),
                $this->perms(['deals'], ['manage-settings'])
            ),

            'Sales Manager' => array_merge(
                $this->perms(['leads'], ['view', 'create', 'edit', 'assign', 'import', 'export']),
                $this->perms(['orders'], ['view', 'create', 'edit', 'approve']),
                $this->perms(['companies', 'contacts', 'tasks'], ['view', 'create', 'edit']),
                $this->perms(['deals'], ['view', 'create', 'edit', 'assign', 'export']),
                $this->perms(['reports'], ['view'])
            ),

            'Sales Executive' => array_merge(
                $this->perms(['leads'], ['view', 'create', 'edit']),
                $this->perms(['orders'], ['view', 'create']),
                $this->perms(['companies', 'contacts', 'tasks'], ['view', 'create', 'edit']),
                $this->perms(['attendance'], ['view', 'create']),
                $this->perms(['deals'], ['view', 'create', 'edit'])
            ),

            'Marketing' => array_merge(
                $this->perms(['leads'], ['view', 'create', 'import', 'export']),
                $this->perms(['companies', 'contacts'], ['view'])
            ),

            'HR' => array_merge(
                $this->perms(['attendance'], ['view', 'create', 'edit', 'export', 'approve']),
                $this->perms(['users'], ['view'])
            ),

            'Support' => array_merge(
                $this->perms(['leads'], ['view']),
                $this->perms(['companies'], ['view']),
                $this->perms(['contacts'], ['view', 'edit']),
                $this->perms(['orders'], ['view']),
                $this->perms(['deals'], ['view']),
                $this->perms(['tasks'], ['view', 'create', 'edit'])
            ),

            'Finance' => array_merge(
                $this->perms(['orders'], ['view', 'edit', 'approve', 'export']),
                $this->perms(['leads'], ['view']),
                $this->perms(['deals', 'reports'], ['view'])
            ),

            'Employee' => array_merge(
                $this->perms(['attendance'], ['view', 'create']),
                $this->perms(['leads'], ['view']),
                $this->perms(['tasks'], ['view', 'create', 'edit'])
            ),
        ];
    }
}
