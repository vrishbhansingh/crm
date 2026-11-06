<?php

use App\Models\Tenant;
use App\Support\PermissionTeam;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Pages that used to share one switch now have their own, so each can be
 * turned on or off per role. Anyone who could open the page through the old
 * shared switch is granted the new one once, so nobody loses a page. Agent
 * roles also gain WhatsApp chat (which is now limited to their own chats).
 * Nothing is ever removed.
 */
return new class extends Migration
{
    private const SPLITS = [
        'deals.manage-settings' => ['pipelines.manage-settings'],
        'company.manage-settings' => ['ai.manage-settings', 'mail.manage-settings'],
        'whatsapp.view' => ['whatsapp_campaigns.view'],
        'whatsapp.create' => ['whatsapp_campaigns.create'],
        'whatsapp.send' => ['whatsapp_campaigns.send'],
        'whatsapp.delete' => ['whatsapp_campaigns.delete'],
        'reports.view' => ['reports.agents', 'reports.communications', 'reports.automation'],
    ];

    public function up(): void
    {
        foreach (self::SPLITS as $new) {
            foreach ($new as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }
        }
        foreach (['whatsapp.view', 'whatsapp.send'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $pivot = config('permission.table_names.role_has_permissions', 'role_has_permissions');
        $ids = Permission::where('guard_name', 'web')->pluck('id', 'name');

        foreach (self::SPLITS as $old => $news) {
            $roleIds = DB::table($pivot)->where('permission_id', $ids[$old] ?? 0)->pluck('role_id');
            foreach ($news as $new) {
                foreach ($roleIds as $roleId) {
                    DB::table($pivot)->updateOrInsert(['permission_id' => $ids[$new], 'role_id' => $roleId]);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Tenant::query()->whereNotNull('admin_user_id')->orderBy('id')->each(function (Tenant $tenant) {
            PermissionTeam::run($tenant->id, function () use ($tenant) {
                $agent = Role::where('tenant_id', $tenant->id)->where('name', 'Agent')->where('guard_name', 'web')->first();
                $agent?->givePermissionTo(Permission::whereIn('name', ['whatsapp.view', 'whatsapp.send'])->get());
            });
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Permissions stay; removing grants could lock people out.
    }
};
