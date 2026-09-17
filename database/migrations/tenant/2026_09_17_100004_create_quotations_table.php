<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Depends on `leads` and `deals` — a quotation links to exactly one of
 * them (enforced in QuotationController, not at the DB level).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quotations')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `quotations` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `quotation_number` varchar(50) NOT NULL,
                  `lead_id` int unsigned DEFAULT NULL,
                  `deal_id` bigint unsigned DEFAULT NULL,
                  `status` varchar(20) NOT NULL DEFAULT 'draft',
                  `version` int unsigned NOT NULL DEFAULT 1,
                  `root_quotation_id` bigint unsigned DEFAULT NULL,
                  `valid_until` date DEFAULT NULL,
                  `currency` varchar(10) NOT NULL DEFAULT 'INR',
                  `sub_total` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `terms_conditions` text,
                  `notes` text,
                  `owner_id` bigint unsigned DEFAULT NULL,
                  `created_by` bigint unsigned DEFAULT NULL,
                  `sent_at` timestamp NULL DEFAULT NULL,
                  `sent_to_email` varchar(255) DEFAULT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `deleted_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `quotations_tenant_id_foreign` (`tenant_id`),
                  KEY `quotations_lead_id_foreign` (`lead_id`),
                  KEY `quotations_deal_id_foreign` (`deal_id`),
                  KEY `quotations_root_quotation_id_foreign` (`root_quotation_id`),
                  KEY `quotations_owner_id_foreign` (`owner_id`),
                  KEY `quotations_tenant_id_quotation_number_index` (`tenant_id`,`quotation_number`),
                  CONSTRAINT `quotations_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL,
                  CONSTRAINT `quotations_deal_id_foreign` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE SET NULL,
                  CONSTRAINT `quotations_root_quotation_id_foreign` FOREIGN KEY (`root_quotation_id`) REFERENCES `quotations` (`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
