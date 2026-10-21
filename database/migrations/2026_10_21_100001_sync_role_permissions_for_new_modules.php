<?php

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * One-time catch-up so a server that was updated by pulling code gets the
 * permissions for the Lead Integrations, Lead/Deal Assignment, Pipelines and
 * WhatsApp pages (and the Admin grants for them) as part of the normal
 * `php artisan migrate`. Future permission additions: run
 * `php artisan crm:sync-permissions` after deploying.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('tenants')) {
            return;
        }

        app(RolePermissionSeeder::class)->run();
    }

    public function down(): void
    {
        // Permissions are additive and harmless to keep.
    }
};
