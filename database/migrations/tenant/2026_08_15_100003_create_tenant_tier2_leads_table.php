<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant business schema, tier 2: `leads`, which depends on `companies` and
 * `contacts` (tier 0/1).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leads')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `leads` (
                  `id` int unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `company_id` bigint unsigned DEFAULT NULL,
                  `contact_id` bigint unsigned DEFAULT NULL,
                  `lead_number` int DEFAULT NULL,
                  `lead_type` varchar(50) DEFAULT 'inquiry',
                  `lead_source` varchar(50) DEFAULT NULL,
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
                  `lead_status` varchar(50) NOT NULL DEFAULT 'new',
                  `final_status` enum('won','lost') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
                  `priority` varchar(50) NOT NULL DEFAULT 'high',
                  `follow_up_date` date DEFAULT NULL,
                  `follow_up_time` time DEFAULT NULL,
                  `follow_up_note` varchar(255) DEFAULT NULL,
                  `assigned_to` bigint DEFAULT NULL,
                  `assigned_by` int unsigned DEFAULT NULL,
                  `assigned_at` timestamp NULL DEFAULT NULL,
                  `remarks` text,
                  `internal_note` text,
                  `is_converted` enum('Yes','No') DEFAULT 'No',
                  `converted_at` datetime DEFAULT NULL,
                  `conversion_value` decimal(10,2) DEFAULT NULL,
                  `score` tinyint unsigned DEFAULT NULL,
                  `status_reason` varchar(255) DEFAULT NULL,
                  `last_contacted_at` datetime DEFAULT NULL,
                  `last_contacted_by` bigint DEFAULT NULL,
                  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Active',
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `leads_tenant_number_unique` (`tenant_id`,`lead_number`),
                  KEY `idx_user_attendance_assigned_to` (`assigned_to`),
                  KEY `leads_company_id_foreign` (`company_id`),
                  KEY `leads_contact_id_foreign` (`contact_id`),
                  CONSTRAINT `leads_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
                  CONSTRAINT `leads_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
