<?php

namespace App\Console\Commands;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Menu items and pages are hidden unless the user's role holds the matching
 * permission (e.g. integrations.view, deals.manage-settings). New modules add
 * new permissions in code, but nothing grants them to a tenant's existing
 * Admin role until RolePermissionSeeder runs — so a server updated by
 * `git pull` alone shows a menu without the new automation / funnel pages.
 * Run this after every deploy. It is idempotent: it creates missing
 * permissions and re-syncs every company's Admin role to all of them. Custom
 * roles (Agent, etc.) are never touched — tenants edit those themselves.
 */
class SyncPermissions extends Command
{
    protected $signature = 'crm:sync-permissions';

    protected $description = 'Create any missing permissions and grant them to every company Admin (run after each deploy)';

    public function handle(): int
    {
        $before = Permission::count();

        $this->call('db:seed', ['--class' => RolePermissionSeeder::class, '--force' => true]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Cache::flush();

        $added = Permission::count() - $before;
        $this->info("Permissions synced ({$added} new). Company Admins now have every permission; users may need to sign in again.");

        return self::SUCCESS;
    }
}
