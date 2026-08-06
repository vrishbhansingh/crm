<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Documents the live `project_info` table exactly as it exists in production.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_info')) {
            return;
        }

        DB::statement(<<<'SQL'
            CREATE TABLE `project_info` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `project_name` varchar(255) DEFAULT NULL,
              `tech_stack` varchar(255) DEFAULT NULL,
              `expected_start_date` date DEFAULT NULL,
              `expected_delivery_date` date DEFAULT NULL,
              `actual_delivery_date` date DEFAULT NULL,
              `priority` enum('low','medium','high','urgent') DEFAULT NULL,
              `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
              `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('project_info');
    }
};
