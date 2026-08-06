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
        // MODIFY is idempotent-safe to re-run (no-op if already bigint).
        DB::statement('ALTER TABLE `orders` MODIFY `user_id` BIGINT UNSIGNED DEFAULT NULL');
        DB::statement('ALTER TABLE `user_attendance` MODIFY `user_id` BIGINT UNSIGNED DEFAULT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `orders` MODIFY `user_id` INT UNSIGNED DEFAULT NULL');
        DB::statement('ALTER TABLE `user_attendance` MODIFY `user_id` INT UNSIGNED DEFAULT NULL');
    }
};
