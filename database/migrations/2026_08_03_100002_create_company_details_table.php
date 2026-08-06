<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Documents the live `company_details` table exactly as it exists in production.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('company_details')) {
            return;
        }

        DB::statement(<<<'SQL'
            CREATE TABLE `company_details` (
              `id` int NOT NULL AUTO_INCREMENT,
              `company_name` varchar(255) DEFAULT NULL,
              `company_logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
              `email` varchar(255) DEFAULT NULL,
              `phone` varchar(255) DEFAULT NULL,
              `address` varchar(255) DEFAULT NULL,
              `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
              `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
              `country` varchar(255) NOT NULL DEFAULT 'India',
              `pincode` int DEFAULT NULL,
              `gst_number` varchar(255) DEFAULT NULL,
              `pan_number` varchar(255) DEFAULT NULL,
              `bank_name` varchar(255) DEFAULT NULL,
              `account_name` varchar(255) DEFAULT NULL,
              `account_number` int DEFAULT NULL,
              `ifsc_code` varchar(50) DEFAULT NULL,
              `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_details');
    }
};
