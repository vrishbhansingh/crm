<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantDatabaseProvisioner;
use App\Services\CompanyAdminManager;
use Illuminate\Console\Command;

class ProvisionTenantDatabases extends Command
{
    protected $signature = 'crm:provision-tenants {--tenant= : Provision only this tenant ID} {--copy-existing : Copy this tenant data from the master database} {--admin-user= : Set the company administrator user ID first}';

    protected $description = 'Create and initialize isolated company databases';

    public function handle(TenantDatabaseProvisioner $provisioner, CompanyAdminManager $admins): int
    {
        $query = Tenant::query()->orderBy('id');
        if ($this->option('tenant')) {
            $query->whereKey((int) $this->option('tenant'));
        }

        $tenants = $query->get();
        if ($tenants->isEmpty()) {
            $this->error('No matching tenant was found.');

            return self::FAILURE;
        }

        foreach ($tenants as $tenant) {
            if ($this->option('admin-user')) {
                $admin = User::where('tenant_id', $tenant->id)->findOrFail((int) $this->option('admin-user'));
                $tenant = $admins->assign($tenant, $admin);
            }

            if (! $tenant->admin_user_id) {
                $this->error("Tenant {$tenant->id} has no company administrator. Use --admin-user=ID.");

                return self::FAILURE;
            }

            $this->line("Provisioning tenant {$tenant->id}: {$tenant->name}");
            $provisioner->provision($tenant, (bool) $this->option('copy-existing'));
            $this->info("Ready: {$tenant->fresh()->database_name}");
        }

        return self::SUCCESS;
    }
}
