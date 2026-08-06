<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Documents the live `user_attendance` table exactly as it exists in production.
 * Depends on `user_list` already existing (real FK constraint).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_attendance')) {
            return;
        }

        DB::statement(<<<'SQL'
            CREATE TABLE `user_attendance` (
              `id` int NOT NULL AUTO_INCREMENT,
              `user_id` int unsigned DEFAULT NULL,
              `date` datetime DEFAULT NULL,
              `check_out` datetime DEFAULT NULL,
              `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_user_attendance_user_id` (`user_id`),
              CONSTRAINT `user_attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_attendance');
    }
};
