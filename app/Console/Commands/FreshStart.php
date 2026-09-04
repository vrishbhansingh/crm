<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Development-loop reset: wipes the master database (tenants, users, roles,
 * audit log, ...) back to a fresh seed, and drops every provisioned tenant
 * database along with it, so the next `/register` starts from a clean slate.
 */
class FreshStart extends Command
{
    protected $signature = 'crm:fresh-start
        {--force : Skip the confirmation prompt (required in production)}
        {--keep-tenant-dbs : Reset the master database but leave existing tenant databases in place}';

    protected $description = 'Wipe and re-seed the master database, dropping every tenant database with it';

    public function handle(): int
    {
        if ($this->laravel->environment('production') && ! $this->option('force')) {
            $this->error('Refusing to run in production without --force.');

            return self::FAILURE;
        }

        $master = config('tenancy.master_connection', 'mysql');
        $prefix = preg_replace('/[^a-zA-Z0-9_]/', '', config('tenancy.database_prefix', 'crm_tenant_'));

        $tenantDatabases = $this->option('keep-tenant-dbs')
            ? collect()
            : $this->tenantDatabaseNames($master);

        if (! $this->option('force')) {
            $this->warn('This will DROP every table in the master database and re-seed it from scratch.');

            if ($tenantDatabases->isNotEmpty()) {
                $this->warn("It will also DROP {$tenantDatabases->count()} tenant database(s):");
                $this->line($tenantDatabases->map(fn ($name) => "  - {$name}")->implode(PHP_EOL));
            }

            if (! $this->confirm('Are you sure you want to continue?')) {
                $this->line('Aborted.');

                return self::SUCCESS;
            }
        }

        $dropped = 0;
        foreach ($tenantDatabases as $database) {
            if (! preg_match('/^[a-zA-Z0-9_]+$/', $database) || ! str_starts_with($database, $prefix)) {
                $this->warn("Skipping database name that doesn't match the configured tenant prefix: {$database}");
                continue;
            }

            DB::connection($master)->statement("DROP DATABASE IF EXISTS `{$database}`");
            $this->line("Dropped tenant database: {$database}");
            $dropped++;
        }

        $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);

        $this->info("Master database reset and re-seeded. Tenant databases dropped: {$dropped}.");
        $this->line('Register a new company at /register to try the full flow again.');

        return self::SUCCESS;
    }

    private function tenantDatabaseNames(string $master): \Illuminate\Support\Collection
    {
        if (! Schema::connection($master)->hasTable('tenants')) {
            return collect();
        }

        return DB::connection($master)->table('tenants')
            ->whereNotNull('database_name')
            ->pluck('database_name');
    }
}
