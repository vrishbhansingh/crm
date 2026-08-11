<?php

return [
    'mode' => env('TENANCY_MODE', 'database'),
    'master_connection' => env('DB_CONNECTION', 'mysql'),
    'tenant_connection' => 'tenant',
    'database_prefix' => env('TENANT_DB_PREFIX', 'crm_tenant_'),
    'auto_provision' => env('TENANT_AUTO_PROVISION', true),
];
