<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('model_has_roles')->whereNotIn('model_id', DB::table('users')->select('id'))->delete();
        DB::table('model_has_permissions')->whereNotIn('model_id', DB::table('users')->select('id'))->delete();

        if (! Schema::hasColumn('roles', 'tenant_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropUnique('roles_name_guard_name_unique');
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
                $table->unique(['tenant_id', 'name', 'guard_name'], 'roles_tenant_name_guard_unique');
            });
        }

        if (! Schema::hasColumn('model_has_roles', 'tenant_id')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->default(0)->after('role_id');
            });
        }
        if (! $this->hasIndex('model_has_roles', 'model_has_roles_role_id_index')) {
            DB::statement('ALTER TABLE `model_has_roles` ADD INDEX `model_has_roles_role_id_index` (`role_id`)');
        }
        if (! $this->primaryIncludesTenant('model_has_roles')) {
            DB::statement('ALTER TABLE `model_has_roles` DROP PRIMARY KEY, ADD PRIMARY KEY (`tenant_id`, `role_id`, `model_id`, `model_type`)');
        }
        if (! $this->hasIndex('model_has_roles', 'model_has_roles_tenant_id_index')) {
            DB::statement('ALTER TABLE `model_has_roles` ADD INDEX `model_has_roles_tenant_id_index` (`tenant_id`)');
        }

        if (! Schema::hasColumn('model_has_permissions', 'tenant_id')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->default(0)->after('permission_id');
            });
        }
        if (! $this->hasIndex('model_has_permissions', 'model_has_permissions_permission_id_index')) {
            DB::statement('ALTER TABLE `model_has_permissions` ADD INDEX `model_has_permissions_permission_id_index` (`permission_id`)');
        }
        if (! $this->primaryIncludesTenant('model_has_permissions')) {
            DB::statement('ALTER TABLE `model_has_permissions` DROP PRIMARY KEY, ADD PRIMARY KEY (`tenant_id`, `permission_id`, `model_id`, `model_type`)');
        }
        if (! $this->hasIndex('model_has_permissions', 'model_has_permissions_tenant_id_index')) {
            DB::statement('ALTER TABLE `model_has_permissions` ADD INDEX `model_has_permissions_tenant_id_index` (`tenant_id`)');
        }

        $superAdminUserIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->join('users', 'users.id', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.model_type', App\Models\User::class)
            ->where('roles.name', 'Super Admin')
            ->whereNull('users.tenant_id')
            ->pluck('users.id');

        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete();

        $superRoleId = DB::table('roles')->where('name', 'Super Admin')->value('id');
        DB::table('roles')->where('id', '!=', $superRoleId)->delete();
        DB::table('roles')->where('id', $superRoleId)->update(['tenant_id' => null]);

        foreach ($superAdminUserIds as $userId) {
            DB::table('model_has_roles')->insert([
                'role_id' => $superRoleId, 'tenant_id' => 0,
                'model_type' => App\Models\User::class, 'model_id' => $userId,
            ]);
        }

        $tenantPermissionIds = DB::table('permissions')->where('name', 'not like', 'platform.%')->pluck('id');
        foreach (DB::table('tenants')->whereNotNull('admin_user_id')->get(['id', 'admin_user_id']) as $tenant) {
            $roleId = DB::table('roles')->insertGetId([
                'tenant_id' => $tenant->id, 'name' => 'Admin', 'guard_name' => 'web',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($tenantPermissionIds as $permissionId) {
                DB::table('role_has_permissions')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
            DB::table('model_has_roles')->insert([
                'role_id' => $roleId, 'tenant_id' => $tenant->id,
                'model_type' => App\Models\User::class, 'model_id' => $tenant->admin_user_id,
            ]);
        }

        app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        throw new RuntimeException('Tenant-scoped role migration is intentionally irreversible. Restore a database backup to roll it back.');
    }

    private function hasIndex(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::connection()->getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }

    private function primaryIncludesTenant(string $table): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::connection()->getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', 'PRIMARY')
            ->where('column_name', 'tenant_id')
            ->exists();
    }
};
