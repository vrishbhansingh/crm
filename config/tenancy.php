<?php

return [
    'mode' => env('TENANCY_MODE', 'database'),
    'master_connection' => env('DB_CONNECTION', 'mysql'),
    'tenant_connection' => 'tenant',
    'database_prefix' => env('TENANT_DB_PREFIX', 'crm_tenant_'),
    'auto_provision' => env('TENANT_AUTO_PROVISION', true),

    // When false (current default), a self-service signup is activated
    // immediately after provisioning — no Super Admin approval step. The
    // tenant still lands in the Super Admin company list either way.
    'require_approval' => env('TENANT_REQUIRE_APPROVAL', false),
];
