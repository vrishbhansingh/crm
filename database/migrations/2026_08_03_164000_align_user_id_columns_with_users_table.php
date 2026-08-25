<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * orders.user_id and user_attendance.user_id were `int unsigned` (matching the
 * old user_list.id). The unified users.id is `bigint unsigned` (Laravel's
 * default). Widen both columns so the FK constraint that crm:migrate-identities
 * adds to `users` is type-compatible.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop FK constraints before modifying column types (MySQL 8 requires this).
        $fks = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE `orders` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        $fks = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_attendance' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE `user_attendance` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        DB::statement('ALTER TABLE `orders` MODIFY `user_id` BIGINT UNSIGNED DEFAULT NULL');
        DB::statement('ALTER TABLE `user_attendance` MODIFY `user_id` BIGINT UNSIGNED DEFAULT NULL');

        // Recreate FK constraints with correct types.
        DB::statement('ALTER TABLE `orders` ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL');
        DB::statement('ALTER TABLE `user_attendance` ADD CONSTRAINT `user_attendance_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL');
    }

    public function down(): void
    {
        $fks = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE `orders` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        $fks = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_attendance' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE `user_attendance` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        DB::statement('ALTER TABLE `orders` MODIFY `user_id` INT UNSIGNED DEFAULT NULL');
        DB::statement('ALTER TABLE `user_attendance` MODIFY `user_id` INT UNSIGNED DEFAULT NULL');

        DB::statement('ALTER TABLE `orders` ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL');
        DB::statement('ALTER TABLE `user_attendance` ADD CONSTRAINT `user_attendance_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL');
    }
};
