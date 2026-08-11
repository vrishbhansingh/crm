<?php

namespace App\Models\Concerns;

use App\Tenancy\TenantConnectionManager;

trait UsesTenantConnection
{
    public function getConnectionName()
    {
        return app(TenantConnectionManager::class)->connectionName();
    }
}
