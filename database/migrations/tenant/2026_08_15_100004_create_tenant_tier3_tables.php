<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant business schema, tier 3: depends on `leads` (tier 2) and/or
 * `tags`/`project_info` (tier 0).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lead_follow_up')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `lead_follow_up` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
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
                  PRIMARY KEY (`id`),
                  KEY `lead_follow_up_tenant_id_foreign` (`tenant_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            SQL);
        }

        if (! Schema::hasTable('lead_activities')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `lead_activities` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `lead_id` int unsigned NOT NULL,
                  `user_id` bigint unsigned DEFAULT NULL,
                  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `description` text COLLATE utf8mb4_unicode_ci,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `lead_activities_tenant_id_foreign` (`tenant_id`),
                  KEY `lead_activities_user_id_foreign` (`user_id`),
                  KEY `lead_activities_lead_id_created_at_index` (`lead_id`,`created_at`),
                  CONSTRAINT `lead_activities_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (! Schema::hasTable('lead_attachments')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `lead_attachments` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `lead_id` int unsigned NOT NULL,
                  `user_id` bigint unsigned DEFAULT NULL,
                  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `stored_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `size` bigint unsigned NOT NULL,
                  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `lead_attachments_tenant_id_foreign` (`tenant_id`),
                  KEY `lead_attachments_lead_id_foreign` (`lead_id`),
                  KEY `lead_attachments_user_id_foreign` (`user_id`),
                  CONSTRAINT `lead_attachments_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (! Schema::hasTable('lead_tag')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `lead_tag` (
                  `lead_id` int unsigned NOT NULL,
                  `tag_id` bigint unsigned NOT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`lead_id`,`tag_id`),
                  KEY `lead_tag_tag_id_foreign` (`tag_id`),
                  CONSTRAINT `lead_tag_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
                  CONSTRAINT `lead_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (! Schema::hasTable('orders')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `orders` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `order_number` varchar(255) DEFAULT NULL,
                  `lead_id` int unsigned DEFAULT NULL,
                  `invoice_date` datetime DEFAULT NULL,
                  `invoice_id` varchar(10) DEFAULT NULL,
                  `project_id` int unsigned DEFAULT NULL,
                  `user_id` bigint unsigned DEFAULT NULL,
                  `sub_total` double(10,2) DEFAULT NULL,
                  `discount` double(10,2) DEFAULT NULL,
                  `gst` int DEFAULT NULL,
                  `total_amount` double(10,2) DEFAULT NULL,
                  `order_status` varchar(50) NOT NULL DEFAULT 'new',
                  `payment_terms` varchar(50) DEFAULT NULL,
                  `payment_status` varchar(50) DEFAULT NULL,
                  `currency` varchar(50) NOT NULL DEFAULT 'INR',
                  `due_amount` double(10,2) DEFAULT NULL,
                  `net_amount` double(10,2) DEFAULT NULL,
                  `paid_amount` double(10,2) DEFAULT NULL,
                  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `orders_order_number_unique` (`order_number`),
                  UNIQUE KEY `orders_invoice_id_unique` (`invoice_id`),
                  KEY `lead_id` (`lead_id`),
                  KEY `project_id` (`project_id`),
                  KEY `orders_tenant_id_foreign` (`tenant_id`),
                  KEY `orders_users_id_foreign` (`user_id`),
                  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `project_info` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('lead_tag');
        Schema::dropIfExists('lead_attachments');
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('lead_follow_up');
    }
};
