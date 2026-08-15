<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant business schema, tier 1: depends only on tier 0 tables
 * (master_types, companies, pipelines), all created within the same
 * database so these foreign keys are safe.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('master_values')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `master_values` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `master_type_id` bigint unsigned NOT NULL,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `sort_order` int unsigned NOT NULL DEFAULT '0',
                  `is_active` tinyint(1) NOT NULL DEFAULT '1',
                  `created_at` timestamp NULL DEFAULT NULL,
                  `updated_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `master_values_tenant_id_foreign` (`tenant_id`),
                  KEY `master_values_master_type_id_tenant_id_index` (`master_type_id`,`tenant_id`),
                  CONSTRAINT `master_values_master_type_id_foreign` FOREIGN KEY (`master_type_id`) REFERENCES `master_types` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (! Schema::hasTable('contacts')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `contacts` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `company_id` bigint unsigned DEFAULT NULL,
                  `owner_id` bigint unsigned DEFAULT NULL,
                  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `alternate_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
                  `source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
                  `notes` text COLLATE utf8mb4_unicode_ci,
                  `legacy_customer_contact_id` int unsigned DEFAULT NULL,
                  `created_at` timestamp NULL DEFAULT NULL,
                  `updated_at` timestamp NULL DEFAULT NULL,
                  `deleted_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `contacts_legacy_customer_contact_id_unique` (`legacy_customer_contact_id`),
                  KEY `contacts_company_id_foreign` (`company_id`),
                  KEY `contacts_owner_id_foreign` (`owner_id`),
                  KEY `contacts_tenant_id_company_id_index` (`tenant_id`,`company_id`),
                  KEY `contacts_tenant_id_owner_id_index` (`tenant_id`,`owner_id`),
                  KEY `contacts_tenant_id_email_index` (`tenant_id`,`email`),
                  CONSTRAINT `contacts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }

        if (! Schema::hasTable('pipeline_stages')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `pipeline_stages` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `pipeline_id` bigint unsigned NOT NULL,
                  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `sort_order` int unsigned NOT NULL DEFAULT '0',
                  `is_won` tinyint(1) NOT NULL DEFAULT '0',
                  `is_lost` tinyint(1) NOT NULL DEFAULT '0',
                  `probability` tinyint unsigned DEFAULT NULL,
                  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `created_at` timestamp NULL DEFAULT NULL,
                  `updated_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `pipeline_stages_tenant_id_foreign` (`tenant_id`),
                  KEY `pipeline_stages_pipeline_id_foreign` (`pipeline_id`),
                  CONSTRAINT `pipeline_stages_pipeline_id_foreign` FOREIGN KEY (`pipeline_id`) REFERENCES `pipelines` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('master_values');
    }
};
