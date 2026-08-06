<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-time Phase 1 migration: copies admin_details + user_list rows into the
 * unified `users` table (assigning the matching seeded role to each), then
 * remaps every foreign key that pointed at the old user_list.id, and finally
 * renames the two legacy tables so nothing can silently keep reading them.
 *
 * Safe to run with --dry-run first: prints exactly what would happen without
 * writing anything. Refuses to run twice (checks for existing legacy_type rows).
 */
class MigrateIdentities extends Command
{
    protected $signature = 'crm:migrate-identities {--dry-run : Preview without writing any changes}';

    protected $description = 'Migrate admin_details + user_list into the unified users table and remap dependent foreign keys';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (DB::table('users')->whereNotNull('legacy_type')->exists()) {
            $this->error('Identities already migrated — users.legacy_type rows already exist. Aborting.');

            return self::FAILURE;
        }

        $tenant = Tenant::first();

        if (! $tenant) {
            $this->error('No tenant found. Run the tenancy migrations (Phase 1 Step 2) first.');

            return self::FAILURE;
        }

        $admins = DB::table('admin_details')->orderBy('id')->get();
        $agents = DB::table('user_list')->orderBy('id')->get();

        $emailCollisions = $admins->pluck('email')->intersect($agents->pluck('email'))->filter();
        if ($emailCollisions->isNotEmpty()) {
            $this->error('admin_details and user_list share email(s): '.$emailCollisions->implode(', ').' — resolve manually before migrating.');

            return self::FAILURE;
        }

        $this->info("Found {$admins->count()} admin_details row(s) and {$agents->count()} user_list row(s).".($dryRun ? ' [DRY RUN]' : ''));

        if (! $dryRun) {
            // MySQL DDL auto-commits, so these FK drops must happen *before* the
            // data transaction below — otherwise they'd implicitly commit it
            // mid-way and break the all-or-nothing guarantee we rely on.
            $this->dropLegacyUserListForeignKeys();
        }

        $userListIdMap = [];

        DB::transaction(function () use ($admins, $agents, $tenant, $dryRun, &$userListIdMap) {
            foreach ($admins as $admin) {
                $this->line(($dryRun ? '[dry-run] ' : '')."admin_details#{$admin->id} ({$admin->email}) -> users, role: Super Admin, tenant: platform (null)");

                if ($dryRun) {
                    continue;
                }

                $newId = DB::table('users')->insertGetId([
                    'tenant_id' => null,
                    'name' => $admin->name ?: $admin->username,
                    'username' => $admin->username,
                    'email' => $admin->email,
                    'phone' => null,
                    'password' => $admin->password,
                    'status' => $admin->status,
                    'session_token' => null,
                    'legacy_type' => 'admin',
                    'legacy_id' => $admin->id,
                    'email_verified_at' => now(),
                    'created_at' => $admin->created_At,
                    'updated_at' => now(),
                ]);

                User::find($newId)->assignRole('Super Admin');
            }

            foreach ($agents as $agent) {
                $role = match ($agent->role) {
                    'agent' => 'Sales Executive',
                    default => 'Sales Executive',
                };

                $this->line(($dryRun ? '[dry-run] ' : '')."user_list#{$agent->id} ({$agent->email}) -> users, role: {$role}, tenant: {$tenant->name}");

                if ($dryRun) {
                    continue;
                }

                $newId = DB::table('users')->insertGetId([
                    'tenant_id' => $tenant->id,
                    'name' => $agent->name,
                    'username' => null,
                    'email' => $agent->email,
                    'phone' => $agent->phone,
                    'password' => $agent->password,
                    'status' => $agent->status,
                    'session_token' => null,
                    'legacy_type' => 'user_list',
                    'legacy_id' => $agent->id,
                    'email_verified_at' => now(),
                    'created_at' => $agent->created_at,
                    'updated_at' => now(),
                ]);

                $userListIdMap[$agent->id] = $newId;
                User::find($newId)->assignRole($role);
            }

            if ($dryRun) {
                return;
            }

            // assigned_to / assigned_by / last_contacted_by / user_id all reference
            // user_list.id today (never admin_details.id — confirmed via code read
            // of LeadController's assignment endpoints and getAssignUsers()).
            $fkTargets = [
                'leads' => ['assigned_to', 'assigned_by', 'last_contacted_by'],
                'orders' => ['user_id'],
                'user_attendance' => ['user_id'],
                'lead_follow_up' => ['user_id'],
            ];

            foreach ($fkTargets as $table => $columns) {
                foreach ($columns as $column) {
                    foreach ($userListIdMap as $oldId => $newId) {
                        DB::table($table)->where($column, $oldId)->update([$column => $newId]);
                    }
                }
            }

            $this->info('Foreign key remap complete: '.json_encode($userListIdMap));
        });

        if ($dryRun) {
            $this->warn('Dry run complete — no rows were written, no tables were renamed.');

            return self::SUCCESS;
        }

        DB::statement('RENAME TABLE admin_details TO admin_details_legacy, user_list TO user_list_legacy');
        $this->info('Renamed admin_details -> admin_details_legacy, user_list -> user_list_legacy.');

        $this->addUsersForeignKeys();
        $this->info('Added orders.user_id and user_attendance.user_id foreign keys pointing at the new users table.');

        return self::SUCCESS;
    }

    /**
     * Drops the two FK constraints that reference user_list.id, if present,
     * so the FK-remap UPDATEs below can point them at the new users.id values.
     */
    private function dropLegacyUserListForeignKeys(): void
    {
        $targets = [
            'orders' => 'orders_ibfk_3',
            'user_attendance' => 'user_attendance_ibfk_1',
        ];

        foreach ($targets as $table => $constraint) {
            $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', $table)
                ->where('CONSTRAINT_NAME', $constraint)
                ->exists();

            if ($exists) {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
            }
        }
    }

    /**
     * Restores referential integrity from orders/user_attendance to the new
     * unified users table, now that their user_id values have been remapped.
     */
    private function addUsersForeignKeys(): void
    {
        // orders.user_id / user_attendance.user_id were `int unsigned` (matching
        // the old user_list.id) — widen to bigint unsigned so the FK to the
        // unified users.id (bigint unsigned) is type-compatible.
        DB::statement('ALTER TABLE `orders` MODIFY `user_id` BIGINT UNSIGNED DEFAULT NULL');
        DB::statement('ALTER TABLE `user_attendance` MODIFY `user_id` BIGINT UNSIGNED DEFAULT NULL');

        DB::statement('ALTER TABLE `orders` ADD CONSTRAINT `orders_users_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        DB::statement('ALTER TABLE `user_attendance` ADD CONSTRAINT `user_attendance_users_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
    }
}
