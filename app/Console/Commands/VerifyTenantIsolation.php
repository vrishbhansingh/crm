<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class VerifyTenantIsolation extends Command
{
    protected $signature = 'crm:verify-tenant-isolation';

    protected $description = 'Create two temporary databases and prove records cannot cross tenant connections';

    public function handle(): int
    {
        $master = config('tenancy.master_connection', 'mysql');
        $suffix = Str::lower(Str::random(10));
        $prefix = preg_replace('/[^a-zA-Z0-9_]/', '', config('tenancy.database_prefix', 'crm_tenant_'));
        $databases = [$prefix.'isolation_a_'.$suffix, $prefix.'isolation_b_'.$suffix];

        foreach ($databases as $database) {
            if (! preg_match('/^'.preg_quote($prefix, '/').'isolation_[ab]_[a-z0-9]{10}$/', $database)) {
                throw new RuntimeException('Refusing to use an unsafe isolation-test database name.');
            }
        }

        try {
            foreach ($databases as $database) {
                DB::connection($master)->statement("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                DB::connection($master)->statement("CREATE TABLE `{$database}`.`isolation_probe` (`id` INT UNSIGNED PRIMARY KEY, `marker` VARCHAR(80) NOT NULL) ENGINE=InnoDB");
            }

            DB::connection($master)->table($databases[0].'.isolation_probe')->insert(['id' => 1, 'marker' => 'tenant-a']);
            DB::connection($master)->table($databases[1].'.isolation_probe')->insert(['id' => 1, 'marker' => 'tenant-b']);

            $a = DB::connection($master)->table($databases[0].'.isolation_probe')->value('marker');
            $b = DB::connection($master)->table($databases[1].'.isolation_probe')->value('marker');
            if ($a !== 'tenant-a' || $b !== 'tenant-b' || $a === $b) {
                throw new RuntimeException('Tenant isolation verification failed.');
            }

            $this->info('PASS: two tenant databases retained independent records with the same primary key.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            foreach ($databases as $database) {
                DB::connection($master)->statement("DROP DATABASE IF EXISTS `{$database}`");
            }
        }
    }
}
