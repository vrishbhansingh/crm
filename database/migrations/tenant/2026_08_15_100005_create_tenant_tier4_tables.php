<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant business schema, tier 4: `deals` (depends on pipelines,
 * pipeline_stages, leads, companies, contacts, orders, master_values) and
 * `payment_details` (depends on orders).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('deals')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `deals` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `pipeline_id` bigint unsigned NOT NULL,
                  `stage_id` bigint unsigned NOT NULL,
                  `lead_id` int unsigned DEFAULT NULL,
                  `company_id` bigint unsigned DEFAULT NULL,
                  `contact_id` bigint unsigned DEFAULT NULL,
                  `order_id` int DEFAULT NULL,
                  `owner_id` bigint unsigned DEFAULT NULL,
                  `lost_reason_id` bigint unsigned DEFAULT NULL,
                  `created_by` bigint unsigned DEFAULT NULL,
                  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `amount` double(10,2) NOT NULL DEFAULT '0.00',
                  `currency` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `expected_close_date` date DEFAULT NULL,
                  `closed_at` timestamp NULL DEFAULT NULL,
                  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
                  `notes` text COLLATE utf8mb4_unicode_ci,
                  `created_at` timestamp NULL DEFAULT NULL,
                  `updated_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `deals_lead_id_unique` (`lead_id`),
                  UNIQUE KEY `deals_order_id_unique` (`order_id`),
                  KEY `deals_tenant_id_foreign` (`tenant_id`),
                  KEY `deals_pipeline_id_foreign` (`pipeline_id`),
                  KEY `deals_stage_id_foreign` (`stage_id`),
                  KEY `deals_owner_id_foreign` (`owner_id`),
                  KEY `deals_lost_reason_id_foreign` (`lost_reason_id`),
                  KEY `deals_created_by_foreign` (`created_by`),
                  KEY `deals_company_id_foreign` (`company_id`),
                  KEY `deals_contact_id_foreign` (`contact_id`),
                  CONSTRAINT `deals_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
                  CONSTRAINT `deals_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
                  CONSTRAINT `deals_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL,
                  CONSTRAINT `deals_lost_reason_id_foreign` FOREIGN KEY (`lost_reason_id`) REFERENCES `master_values` (`id`) ON DELETE SET NULL,
                  CONSTRAINT `deals_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
                  CONSTRAINT `deals_pipeline_id_foreign` FOREIGN KEY (`pipeline_id`) REFERENCES `pipelines` (`id`),
                  CONSTRAINT `deals_stage_id_foreign` FOREIGN KEY (`stage_id`) REFERENCES `pipeline_stages` (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (! Schema::hasTable('payment_details')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `payment_details` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `order_id` int DEFAULT NULL,
                  `payment_mode` varchar(50) DEFAULT NULL,
                  `payment_date` datetime DEFAULT NULL,
                  `paid_amount` float(12,2) DEFAULT NULL,
                  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `payment_details_tenant_id_index` (`tenant_id`),
                  KEY `payment_details_order_id_foreign` (`order_id`),
                  CONSTRAINT `payment_details_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_details');
        Schema::dropIfExists('deals');
    }
};
