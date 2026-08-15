<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant business schema, tier 0: tables with no foreign keys to other
 * tenant-scoped business tables. Column definitions are captured verbatim
 * from the central database's tables (the historical source of truth) so a
 * freshly provisioned company gets byte-for-byte identical columns/types.
 *
 * Foreign keys to `tenants` and `users` are intentionally omitted — those
 * tables live only in the central database, not in a tenant database.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('master_types')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `master_types` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `is_active` tinyint(1) NOT NULL DEFAULT '1',
                  `created_at` timestamp NULL DEFAULT NULL,
                  `updated_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `master_types_code_unique` (`code`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (! Schema::hasTable('company_details')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `company_details` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
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
                  PRIMARY KEY (`id`),
                  KEY `company_details_tenant_id_index` (`tenant_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            SQL);
        }

        if (! Schema::hasTable('companies')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `companies` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `owner_id` bigint unsigned DEFAULT NULL,
                  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `legal_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `industry` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `company_size` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `gst_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `pan_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `address` text COLLATE utf8mb4_unicode_ci,
                  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `pincode` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'prospect',
                  `notes` text COLLATE utf8mb4_unicode_ci,
                  `created_at` timestamp NULL DEFAULT NULL,
                  `updated_at` timestamp NULL DEFAULT NULL,
                  `deleted_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `companies_owner_id_foreign` (`owner_id`),
                  KEY `companies_tenant_id_name_index` (`tenant_id`,`name`),
                  KEY `companies_tenant_id_status_index` (`tenant_id`,`status`),
                  KEY `companies_tenant_id_owner_id_index` (`tenant_id`,`owner_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (! Schema::hasTable('pipelines')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `pipelines` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `is_default` tinyint(1) NOT NULL DEFAULT '0',
                  `is_active` tinyint(1) NOT NULL DEFAULT '1',
                  `sort_order` int unsigned NOT NULL DEFAULT '0',
                  `created_at` timestamp NULL DEFAULT NULL,
                  `updated_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `pipelines_tenant_id_foreign` (`tenant_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (! Schema::hasTable('tags')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `tags` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned NOT NULL,
                  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `created_at` timestamp NULL DEFAULT NULL,
                  `updated_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `tags_tenant_id_name_unique` (`tenant_id`,`name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (! Schema::hasTable('project_info')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `project_info` (
                  `id` int unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `project_name` varchar(255) DEFAULT NULL,
                  `tech_stack` varchar(255) DEFAULT NULL,
                  `expected_start_date` date DEFAULT NULL,
                  `expected_delivery_date` date DEFAULT NULL,
                  `actual_delivery_date` date DEFAULT NULL,
                  `priority` varchar(50) DEFAULT NULL,
                  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
                  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `project_info_tenant_id_foreign` (`tenant_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            SQL);
        }

        if (! Schema::hasTable('tasks')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `tasks` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned NOT NULL,
                  `assigned_to` bigint unsigned DEFAULT NULL,
                  `created_by` bigint unsigned DEFAULT NULL,
                  `related_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `related_id` bigint unsigned DEFAULT NULL,
                  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `description` text COLLATE utf8mb4_unicode_ci,
                  `priority` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
                  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'todo',
                  `due_at` timestamp NULL DEFAULT NULL,
                  `remind_at` timestamp NULL DEFAULT NULL,
                  `notification_sent_at` timestamp NULL DEFAULT NULL,
                  `completed_at` timestamp NULL DEFAULT NULL,
                  `created_at` timestamp NULL DEFAULT NULL,
                  `updated_at` timestamp NULL DEFAULT NULL,
                  `deleted_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `tasks_assigned_to_foreign` (`assigned_to`),
                  KEY `tasks_created_by_foreign` (`created_by`),
                  KEY `tasks_tenant_id_status_due_at_index` (`tenant_id`,`status`,`due_at`),
                  KEY `tasks_related_type_related_id_index` (`related_type`,`related_id`),
                  KEY `tasks_notification_sent_at_index` (`notification_sent_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (! Schema::hasTable('user_attendance')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `user_attendance` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `user_id` bigint unsigned DEFAULT NULL,
                  `date` datetime DEFAULT NULL,
                  `check_out` datetime DEFAULT NULL,
                  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `idx_user_attendance_user_id` (`user_id`),
                  KEY `user_attendance_tenant_id_foreign` (`tenant_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            SQL);
        }

        if (! Schema::hasTable('audit_logs')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `audit_logs` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `actor_id` bigint unsigned DEFAULT NULL,
                  `event` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `auditable_type` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `auditable_id` bigint unsigned NOT NULL,
                  `old_values` json DEFAULT NULL,
                  `new_values` json DEFAULT NULL,
                  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `created_at` timestamp NULL DEFAULT NULL,
                  `updated_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `audit_logs_tenant_id_created_at_index` (`tenant_id`,`created_at`),
                  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
                  KEY `audit_logs_actor_id_created_at_index` (`actor_id`,`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (! Schema::hasTable('customer_contact')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `customer_contact` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `name` varchar(255) NOT NULL,
                  `phone` varchar(50) NOT NULL,
                  `email` varchar(255) NOT NULL,
                  `designation` varchar(255) NOT NULL,
                  `budget` double(10,2) NOT NULL,
                  `city` varchar(255) NOT NULL,
                  `lead_id` int unsigned NOT NULL,
                  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `lead_id` (`lead_id`),
                  KEY `customer_contact_tenant_id_foreign` (`tenant_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_contact');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('user_attendance');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('project_info');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('pipelines');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('company_details');
        Schema::dropIfExists('master_types');
    }
};
