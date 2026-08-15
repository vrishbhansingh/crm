<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Support\PermissionTeam;
use Spatie\Permission\Models\Role;

class CompanyAdminManager
{
    public function assign(Tenant $tenant, User $newAdmin): Tenant
    {
        abort_unless((int) $newAdmin->tenant_id === $tenant->id, 422, 'The administrator must belong to this company.');

        return PermissionTeam::run($tenant->id, fn () => DB::connection(config('tenancy.master_connection', 'mysql'))->transaction(function () use ($tenant, $newAdmin) {
            $tenant = Tenant::whereKey($tenant->id)->lockForUpdate()->firstOrFail();
            $existingAdmins = $tenant->users()
                ->where('id', '!=', $newAdmin->id)
                ->whereHas('roles', fn ($query) => $query->where('name', 'Admin'))
                ->get();

            foreach ($existingAdmins as $existingAdmin) {
                $existingAdmin->removeRole('Admin');
                if ($existingAdmin->roles()->count() === 0) {
                    $existingAdmin->assignRole(Role::findOrCreate('Manager', 'web'));
                }
            }

            $newAdmin->syncRoles(['Admin']);
            $newAdmin->forceFill(['status' => 'Active'])->save();
            $tenant->update(['admin_user_id' => $newAdmin->id]);

            return $tenant->fresh();
        }));
    }
}
