<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Documents the live `leads` table exactly as it exists in production,
 * including the pre-existing `assigned_to`/`last_contacted_by` bigint vs.
 * `user_list.id` int-unsigned type mismatch (tracked, not fixed, in this migration).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads')) {
            return;
        }

        DB::statement(<<<'SQL'
            CREATE TABLE `leads` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `lead_number` int DEFAULT NULL,
              `lead_type` enum('cold','warm','hot','inquiry','existing_customer') DEFAULT 'inquiry',
              `lead_source` enum('website','facebook_ads','referral','cold_call','email','partner','linkedIn','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
              `name` varchar(255) DEFAULT NULL,
              `company_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
              `phone` varchar(20) DEFAULT NULL,
              `alternate_phone` varchar(20) DEFAULT NULL,
              `email` varchar(255) DEFAULT NULL,
              `gst_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
              `city` varchar(255) DEFAULT NULL,
              `state` varchar(255) DEFAULT NULL,
              `country` varchar(255) DEFAULT NULL,
              `product` varchar(255) DEFAULT NULL,
              `service` varchar(255) DEFAULT NULL,
              `budget` decimal(10,2) DEFAULT NULL,
              `requirement` text,
              `lead_status` enum('new','contacted','interested','follow_up','not_interested','converted','closed') NOT NULL DEFAULT 'new',
              `final_status` enum('won','lost') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
              `priority` enum('high','medium','low') NOT NULL DEFAULT 'high',
              `follow_up_date` date DEFAULT NULL,
              `follow_up_time` time DEFAULT NULL,
              `follow_up_note` varchar(255) DEFAULT NULL,
              `assigned_to` bigint DEFAULT NULL,
              `assigned_by` int unsigned DEFAULT NULL,
              `assigned_at` timestamp NULL DEFAULT NULL,
              `remarks` text,
              `internal_note` text,
              `is_converted` enum('Yes','No') DEFAULT 'Yes',
              `converted_at` datetime DEFAULT NULL,
              `conversion_value` decimal(10,2) DEFAULT NULL,
              `status_reason` varchar(255) DEFAULT NULL,
              `last_contacted_at` datetime DEFAULT NULL,
              `last_contacted_by` bigint DEFAULT NULL,
              `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Active',
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_user_attendance_assigned_to` (`assigned_to`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
