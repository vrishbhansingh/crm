<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TenantConnectionManager
{
    private ?Tenant $activeTenant = null;

    public function activate(Tenant|int $tenant): Tenant
    {
        $tenant = $tenant instanceof Tenant ? $tenant : Tenant::findOrFail($tenant);

        if (! $tenant->database_name) {
            throw new RuntimeException("Tenant {$tenant->id} has no provisioned database.");
        }

        $connection = config('tenancy.tenant_connection', 'tenant');
        $master = config('tenancy.master_connection', 'mysql');
        $base = config("database.connections.{$master}");
        config([
            "database.connections.{$connection}.host" => $tenant->database_host ?: $base['host'],
            "database.connections.{$connection}.port" => $tenant->database_port ?: $base['port'],
            "database.connections.{$connection}.database" => $tenant->database_name,
            "database.connections.{$connection}.username" => $tenant->database_username ?: $base['username'],
            "database.connections.{$connection}.password" => $tenant->database_password ?? $base['password'],
        ]);

        DB::purge($connection);
        DB::connection($connection)->getPdo();
        $this->activeTenant = $tenant;

        return $tenant;
    }

    public function deactivate(): void
    {
        $connection = config('tenancy.tenant_connection', 'tenant');
        DB::purge($connection);
        config(["database.connections.{$connection}.database" => null]);
        $this->activeTenant = null;
    }

    public function active(): ?Tenant
    {
        return $this->activeTenant;
    }

    public function connectionName(): string
    {
        if (config('tenancy.mode') === 'shared') {
            return config('tenancy.master_connection', 'mysql');
        }

        if (! $this->activeTenant && ! config('database.connections.'.config('tenancy.tenant_connection', 'tenant').'.database')) {
            throw new RuntimeException('No tenant database is active for this request.');
        }

        return config('tenancy.tenant_connection', 'tenant');
    }
}
