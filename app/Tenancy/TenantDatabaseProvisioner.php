<?php

namespace App\Tenancy;

use App\Models\Pipeline;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class TenantDatabaseProvisioner
{
    public const SCHEMA_VERSION = 1;

    private const TENANT_SCOPED_TABLES = [
        'company_details', 'companies', 'contacts', 'customer_contact', 'leads',
        'lead_follow_up', 'lead_activities', 'lead_attachments', 'tags', 'pipelines',
        'pipeline_stages', 'deals', 'project_info', 'orders', 'payment_details',
        'tasks', 'user_attendance', 'audit_logs',
    ];

    public function __construct(private readonly TenantConnectionManager $connections) {}

    public function provision(Tenant $tenant, bool $copyExistingData = false): Tenant
    {
        if (config('tenancy.mode') === 'shared') {
            $tenant->update([
                'provision_status' => 'ready',
                'schema_version' => self::SCHEMA_VERSION,
                'provisioned_at' => now(),
                'last_health_check_at' => now(),
                'last_health_status' => 'shared-test-mode',
                'provision_error' => null,
            ]);

            return $tenant->fresh();
        }

        $master = config('tenancy.master_connection', 'mysql');
        $masterDatabase = DB::connection($master)->getDatabaseName();
        $databaseName = $tenant->database_name ?: $this->databaseName($tenant);

        $tenant->update([
            'database_name' => $databaseName,
            'database_host' => config("database.connections.{$master}.host"),
            'database_port' => config("database.connections.{$master}.port"),
            'database_username' => config("database.connections.{$master}.username"),
            'database_password' => config("database.connections.{$master}.password"),
            'provision_status' => 'provisioning',
            'provision_error' => null,
        ]);

        try {
            DB::connection($master)->statement(
                'CREATE DATABASE IF NOT EXISTS '.$this->quoteIdentifier($databaseName).' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );

            $this->connections->activate($tenant->fresh());
            DB::connection('tenant')->statement(
                'CREATE TABLE IF NOT EXISTS `tenant_schema_versions` (`version` INT UNSIGNED NOT NULL PRIMARY KEY, `applied_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB'
            );
            DB::connection('tenant')->statement(
                'CREATE TABLE IF NOT EXISTS `tenant_sequences` (`name` VARCHAR(80) NOT NULL PRIMARY KEY, `current_value` BIGINT UNSIGNED NOT NULL DEFAULT 0, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB'
            );
            // Business tables (leads, companies, deals, ...) are created by
            // the tracked migrations in database/migrations/tenant/, not by
            // copying the central database's tables.
            $this->runTenantMigrations();

            if ($copyExistingData) {
                $this->copyExistingTenantData($tenant, $masterDatabase, $databaseName, $master);
            } else {
                $this->seedFreshTenant($tenant, $masterDatabase, $databaseName, $master);
            }

            DB::connection('tenant')->table('tenant_schema_versions')->updateOrInsert(
                ['version' => self::SCHEMA_VERSION],
                ['applied_at' => now()]
            );
            DB::connection('tenant')->table('tenant_sequences')->updateOrInsert(
                ['name' => 'lead_number'],
                ['current_value' => (int) DB::connection('tenant')->table('leads')->max('lead_number')]
            );

            $tenant->update([
                'provision_status' => 'ready',
                'schema_version' => self::SCHEMA_VERSION,
                'provisioned_at' => now(),
                'last_health_check_at' => now(),
                'last_health_status' => 'healthy',
                'provision_error' => null,
            ]);

            return $tenant->fresh();
        } catch (Throwable $exception) {
            $tenant->update([
                'provision_status' => 'failed',
                'last_health_check_at' => now(),
                'last_health_status' => 'failed',
                'provision_error' => Str::limit($exception->getMessage(), 4000),
            ]);

            throw $exception;
        } finally {
            $this->connections->deactivate();
            $this->restoreMasterMigrationConnection();
        }
    }

    public function healthCheck(Tenant $tenant): bool
    {
        try {
            $this->connections->activate($tenant);
            DB::connection('tenant')->select('SELECT 1');
            $healthy = true;
        } catch (Throwable) {
            $healthy = false;
        } finally {
            $this->connections->deactivate();
        }

        $tenant->update([
            'last_health_check_at' => now(),
            'last_health_status' => $healthy ? 'healthy' : 'failed',
        ]);

        return $healthy;
    }

    public function migrateSchema(Tenant $tenant): void
    {
        $this->connections->activate($tenant);

        try {
            $this->runTenantMigrations();
            $tenant->update(['schema_version' => self::SCHEMA_VERSION]);
        } finally {
            $this->connections->deactivate();
            $this->restoreMasterMigrationConnection();
        }
    }

    private function runTenantMigrations(): void
    {
        $migrator = app('migrator');
        $migrator->setConnection(config('tenancy.tenant_connection', 'tenant'));
        if (! $migrator->repositoryExists()) {
            $migrator->getRepository()->createRepository();
        }
        $migrator->run(database_path('migrations/tenant'));
    }

    private function restoreMasterMigrationConnection(): void
    {
        app('migrator')->setConnection(config('tenancy.master_connection', 'mysql'));
    }

    private function seedFreshTenant(Tenant $tenant, string $masterDatabase, string $databaseName, string $master): void
    {
        if (DB::connection('tenant')->table('master_types')->count() === 0) {
            DB::connection($master)->statement(
                'INSERT INTO '.$this->qualified($databaseName, 'master_types').' SELECT * FROM '.$this->qualified($masterDatabase, 'master_types')
            );
            DB::connection($master)->statement(
                'INSERT INTO '.$this->qualified($databaseName, 'master_values').' SELECT * FROM '.$this->qualified($masterDatabase, 'master_values').' WHERE `tenant_id` IS NULL'
            );
        }

        if (DB::connection('tenant')->table('company_details')->count() === 0) {
            DB::connection('tenant')->table('company_details')->insert([
                'tenant_id' => $tenant->id,
                'company_name' => $tenant->name,
                'email' => $tenant->contact_email,
                'country' => 'India',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        TenantContext::run($tenant->id, fn () => Pipeline::ensureDefaultForTenant($tenant->id));
    }

    private function copyExistingTenantData(Tenant $tenant, string $masterDatabase, string $databaseName, string $master): void
    {
        if (DB::connection('tenant')->table('master_types')->count() === 0) {
            DB::connection($master)->statement(
                'INSERT INTO '.$this->qualified($databaseName, 'master_types').' SELECT * FROM '.$this->qualified($masterDatabase, 'master_types')
            );
            DB::connection($master)->insert(
                'INSERT INTO '.$this->qualified($databaseName, 'master_values').' SELECT * FROM '.$this->qualified($masterDatabase, 'master_values').' WHERE `tenant_id` IS NULL OR `tenant_id` = ?',
                [$tenant->id]
            );
        }

        foreach (self::TENANT_SCOPED_TABLES as $table) {
            if (DB::connection('tenant')->table($table)->count() > 0) {
                continue;
            }

            DB::connection($master)->insert(
                'INSERT INTO '.$this->qualified($databaseName, $table).' SELECT * FROM '.$this->qualified($masterDatabase, $table).' WHERE `tenant_id` = ?',
                [$tenant->id]
            );
        }

        if (DB::connection('tenant')->table('deal_stage_history')->count() === 0) {
            DB::connection($master)->insert(
                'INSERT INTO '.$this->qualified($databaseName, 'deal_stage_history').' SELECT h.* FROM '.$this->qualified($masterDatabase, 'deal_stage_history').' h INNER JOIN '.$this->qualified($masterDatabase, 'deals').' d ON d.id = h.deal_id WHERE d.tenant_id = ?',
                [$tenant->id]
            );
        }

        if (DB::connection('tenant')->table('lead_tag')->count() === 0) {
            DB::connection($master)->insert(
                'INSERT INTO '.$this->qualified($databaseName, 'lead_tag').' SELECT p.* FROM '.$this->qualified($masterDatabase, 'lead_tag').' p INNER JOIN '.$this->qualified($masterDatabase, 'leads').' l ON l.id = p.lead_id WHERE l.tenant_id = ?',
                [$tenant->id]
            );
        }
    }

    private function databaseName(Tenant $tenant): string
    {
        $prefix = preg_replace('/[^a-zA-Z0-9_]/', '', config('tenancy.database_prefix', 'crm_tenant_'));
        $slug = preg_replace('/[^a-zA-Z0-9_]/', '_', Str::lower($tenant->slug));

        return Str::limit($prefix.$tenant->id.'_'.$slug, 64, '');
    }

    private function qualified(string $database, string $table): string
    {
        return $this->quoteIdentifier($database).'.'.$this->quoteIdentifier($table);
    }

    private function quoteIdentifier(string $value): string
    {
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            throw new RuntimeException('Unsafe database identifier.');
        }

        return '`'.$value.'`';
    }
}
