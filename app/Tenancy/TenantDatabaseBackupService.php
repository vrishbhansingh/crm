<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Plain-PHP SQL dump (no shelling out to the `mysqldump` binary, which may
 * not exist or be allowed on every host) used to snapshot a tenant's
 * database immediately before TenantController::destroy() drops it.
 */
class TenantDatabaseBackupService
{
    public function __construct(private readonly TenantConnectionManager $connections) {}

    public function backup(Tenant $tenant): string
    {
        $this->connections->activate($tenant);

        $directory = storage_path('app/tenant-backups');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/'.$tenant->slug.'-'.$tenant->id.'-'.now()->format('Y-m-d_His').'.sql';

        $handle = fopen($path, 'w');
        fwrite($handle, "-- Backup of \"{$tenant->name}\" ({$tenant->database_name}) taken ".now()->toDateTimeString()." before deletion\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $key = 'Tables_in_'.$tenant->database_name;
        foreach (DB::connection('tenant')->select('SHOW TABLES') as $row) {
            $this->dumpTable($row->$key, $handle);
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        $this->connections->deactivate();

        return $path;
    }

    public function dropDatabase(Tenant $tenant): void
    {
        $master = config('tenancy.master_connection', 'mysql');
        DB::connection($master)->statement('DROP DATABASE IF EXISTS `'.$tenant->database_name.'`');
    }

    /** @param resource $handle */
    private function dumpTable(string $table, $handle): void
    {
        $quoted = '`'.$table.'`';
        $createTable = DB::connection('tenant')->selectOne("SHOW CREATE TABLE {$quoted}")->{'Create Table'};
        fwrite($handle, "DROP TABLE IF EXISTS {$quoted};\n{$createTable};\n\n");

        $pdo = DB::connection('tenant')->getPdo();

        foreach (DB::connection('tenant')->table($table)->get() as $row) {
            $data = (array) $row;
            $columns = implode(',', array_map(fn ($c) => "`{$c}`", array_keys($data)));
            $values = implode(',', array_map(
                fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v),
                array_values($data)
            ));
            fwrite($handle, "INSERT INTO {$quoted} ({$columns}) VALUES ({$values});\n");
        }

        fwrite($handle, "\n");
    }
}
