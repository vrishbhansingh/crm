<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One row per vendor invited to an RFQ. `quoted_amount` is a single
 * lump-sum figure per vendor (not per line item) — matching how SMB RFQs
 * are usually negotiated over email/phone rather than itemized back.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rfq_vendors')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `rfq_vendors` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `rfq_id` bigint unsigned NOT NULL,
                  `vendor_id` bigint unsigned NOT NULL,
                  `quoted_amount` decimal(12,2) DEFAULT NULL,
                  `quoted_at` timestamp NULL DEFAULT NULL,
                  `notes` varchar(255) DEFAULT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `rfq_vendors_tenant_id_foreign` (`tenant_id`),
                  KEY `rfq_vendors_vendor_id_foreign` (`vendor_id`),
                  UNIQUE KEY `rfq_vendors_rfq_vendor_unique` (`rfq_id`,`vendor_id`),
                  CONSTRAINT `rfq_vendors_rfq_id_foreign` FOREIGN KEY (`rfq_id`) REFERENCES `request_for_quotations` (`id`) ON DELETE CASCADE,
                  CONSTRAINT `rfq_vendors_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_vendors');
    }
};
