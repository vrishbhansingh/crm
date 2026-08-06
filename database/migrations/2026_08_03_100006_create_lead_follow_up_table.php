<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Documents the live `lead_follow_up` table exactly as it exists in production.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_follow_up')) {
            return;
        }

        DB::statement(<<<'SQL'
            CREATE TABLE `lead_follow_up` (
              `id` int NOT NULL AUTO_INCREMENT,
              `lead_id` int unsigned DEFAULT NULL,
              `user_id` int unsigned DEFAULT NULL,
              `lead_response` enum('interested','callback','not_interested','meeting_scheduled') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
              `follow_up_date` date DEFAULT NULL,
              `follow_up_time` time DEFAULT NULL,
              `call_status` enum('call_connected','not_reachable','switched_off','busy','wrong_number') DEFAULT NULL,
              `call_note` text,
              `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_follow_up');
    }
};
