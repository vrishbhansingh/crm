<?php

namespace App\Console\Commands;

use App\Models\TaxRate;
use App\Models\Tenant;
use App\Tenancy\TenantConnectionManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * A new global (tenant_id null) master type/value only ever reaches an
 * already-provisioned tenant's own database via a fresh provision — see
 * TenantDatabaseProvisioner::seedFreshTenant(), which only copies from
 * master when the tenant's own master_types table is still empty. Adding
 * a type to database/seeders/MasterDataSeeder.php (like uom/product_category
 * were) therefore does nothing for tenants that already existed before
 * the seeder ran — this command closes that gap by copying any global
 * type/value that's missing from each tenant's own copy, the same way a
 * fresh provision would have. It also backfills the default GST tax-rate
 * slabs (0/5/12/18/28%) for tenants provisioned before those defaults
 * existed, the same way TenantDatabaseProvisioner seeds them for new ones.
 */
class SyncMasterDataToTenants extends Command
{
    protected $signature = 'crm:sync-master-data {--tenant= : Sync only one tenant ID}';

    protected $description = 'Seed the master database with MasterDataSeeder, then copy any missing global master types/values and default tax rates into every already-provisioned tenant';

    public function handle(TenantConnectionManager $connections): int
    {
        $this->info('Seeding the master database...');
        Artisan::call('db:seed', ['--class' => 'MasterDataSeeder', '--force' => true], $this->getOutput());

        if (config('tenancy.mode') !== 'database') {
            $this->info('Tenancy mode is not "database" — every tenant already shares the master connection, nothing further to sync.');

            return self::SUCCESS;
        }

        $master = config('tenancy.master_connection', 'mysql');
        $masterDatabase = DB::connection($master)->getDatabaseName();

        $query = Tenant::where('provision_status', 'ready')->whereNotNull('database_name')->orderBy('id');
        if ($this->option('tenant')) {
            $query->whereKey((int) $this->option('tenant'));
        }

        $failed = [];
        foreach ($query->get() as $tenant) {
            try {
                $connections->activate($tenant);
                [$types, $values] = $this->syncTenant($tenant->database_name, $masterDatabase, $master);
                $rates = $this->syncDefaultTaxRates($tenant->id);
                $this->info("Tenant {$tenant->id} ({$tenant->name}): +{$types} type(s), +{$values} value(s), +{$rates} tax rate(s)");
            } catch (Throwable $exception) {
                $failed[] = $tenant->id;
                $this->error("Tenant {$tenant->id} failed: {$exception->getMessage()}");
            } finally {
                $connections->deactivate();
            }
        }

        return $failed === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return array{0: int, 1: int} [typesCopied, valuesCopied]
     */
    private function syncTenant(string $tenantDatabase, string $masterDatabase, string $master): array
    {
        $typesCopied = 0;
        $valuesCopied = 0;

        $masterTypes = DB::connection($master)->table("{$masterDatabase}.master_types")->get();

        foreach ($masterTypes as $masterType) {
            $tenantType = DB::connection($master)->table("{$tenantDatabase}.master_types")
                ->where('code', $masterType->code)->first();

            if (! $tenantType) {
                DB::connection($master)->table("{$tenantDatabase}.master_types")->insert([
                    'code' => $masterType->code,
                    'name' => $masterType->name,
                    'is_active' => $masterType->is_active,
                    'created_at' => $masterType->created_at,
                    'updated_at' => $masterType->updated_at,
                ]);
                $tenantTypeId = DB::connection($master)->table("{$tenantDatabase}.master_types")
                    ->where('code', $masterType->code)->value('id');
                $typesCopied++;
            } else {
                $tenantTypeId = $tenantType->id;
            }

            $masterValues = DB::connection($master)->table("{$masterDatabase}.master_values")
                ->where('master_type_id', $masterType->id)->whereNull('tenant_id')->get();

            foreach ($masterValues as $value) {
                $exists = DB::connection($master)->table("{$tenantDatabase}.master_values")
                    ->where('master_type_id', $tenantTypeId)->whereNull('tenant_id')
                    ->where('code', $value->code)->exists();

                if (! $exists) {
                    DB::connection($master)->table("{$tenantDatabase}.master_values")->insert([
                        'master_type_id' => $tenantTypeId,
                        'tenant_id' => null,
                        'code' => $value->code,
                        'label' => $value->label,
                        'color' => $value->color,
                        'sort_order' => $value->sort_order,
                        'is_active' => $value->is_active,
                        'created_at' => $value->created_at,
                        'updated_at' => $value->updated_at,
                    ]);
                    $valuesCopied++;
                }
            }
        }

        return [$typesCopied, $valuesCopied];
    }

    /**
     * Backfills the standard GST slabs for a tenant that has none of them
     * yet — additive only, so a tenant's own custom/renamed rates are never
     * touched, and running this command twice never creates duplicates.
     */
    private function syncDefaultTaxRates(int $tenantId): int
    {
        $existing = DB::connection('tenant')->table('tax_rates')
            ->where('tenant_id', $tenantId)
            ->pluck('rate_percent')
            ->map(fn ($rate) => (float) $rate)
            ->all();

        $now = now();
        $rows = [];
        foreach (TaxRate::defaultRatePercents() as $rate) {
            if (in_array((float) $rate, $existing, true)) {
                continue;
            }

            $rows[] = [
                'tenant_id' => $tenantId,
                'name' => TaxRate::labelFor((float) $rate),
                'rate_percent' => $rate,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::connection('tenant')->table('tax_rates')->insert($rows);
        }

        return count($rows);
    }
}
