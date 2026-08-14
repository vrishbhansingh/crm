<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Tenancy\TenantDatabaseProvisioner;
use Illuminate\Console\Command;
use Throwable;

class MigrateTenantSchemas extends Command
{
    protected $signature = 'crm:migrate-tenant-schemas {--tenant= : Migrate only one tenant ID}';

    protected $description = 'Apply database/migrations/tenant migrations to provisioned tenant databases';

    public function handle(TenantDatabaseProvisioner $provisioner): int
    {
        $query = Tenant::where('provision_status', 'ready')->orderBy('id');
        if ($this->option('tenant')) {
            $query->whereKey((int) $this->option('tenant'));
        }

        $failed = [];
        foreach ($query->get() as $tenant) {
            try {
                $provisioner->migrateSchema($tenant);
                $this->info("Migrated tenant {$tenant->id}: {$tenant->name}");
            } catch (Throwable $exception) {
                $failed[] = $tenant->id;
                $this->error("Tenant {$tenant->id} failed: {$exception->getMessage()}");
            }
        }

        return $failed === [] ? self::SUCCESS : self::FAILURE;
    }
}
