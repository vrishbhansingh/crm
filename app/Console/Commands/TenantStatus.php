<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Tenancy\TenantDatabaseProvisioner;
use Illuminate\Console\Command;

class TenantStatus extends Command
{
    protected $signature = 'crm:tenant-status {--health : Connect to every ready tenant database}';

    protected $description = 'Show tenant approval, expiry, administrator, database, and health status';

    public function handle(TenantDatabaseProvisioner $provisioner): int
    {
        $tenants = Tenant::withCount('users')->with('admin')->orderBy('id')->get();
        $rows = [];

        foreach ($tenants as $tenant) {
            if ($this->option('health') && $tenant->database_name && $tenant->provision_status === 'ready') {
                $provisioner->healthCheck($tenant);
                $tenant->refresh();
                $tenant->loadCount('users');
            }

            $rows[] = [
                $tenant->id,
                $tenant->name,
                $tenant->approval_status,
                $tenant->status,
                $tenant->isExpired() ? 'expired' : ($tenant->trial_ends_at?->format('Y-m-d') ?? 'none'),
                max($tenant->users_count - 1, 0),
                $tenant->admin?->email ?? 'missing',
                $tenant->provision_status,
                $tenant->last_health_status ?? 'not checked',
                $tenant->database_name ?? 'not assigned',
            ];
        }

        $this->table(
            ['ID', 'Company', 'Approval', 'Status', 'Expires', 'Users', 'Admin', 'Provision', 'Health', 'Database'],
            $rows
        );

        $invalidAdmins = $tenants->filter(function (Tenant $tenant) {
            $companyAdmins = \App\Support\PermissionTeam::run($tenant->id, fn () => $tenant->users()->whereHas('roles', fn ($query) => $query->where('name', 'Admin'))->get());

            return $companyAdmins->count() !== 1
                || ! $tenant->admin
                || $companyAdmins->first()->id !== $tenant->admin->id;
        });
        if ($invalidAdmins->isNotEmpty()) {
            $this->error('Admin invariant failed for tenant IDs: '.$invalidAdmins->pluck('id')->join(', '));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
