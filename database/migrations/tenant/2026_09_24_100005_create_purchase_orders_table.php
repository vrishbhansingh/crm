<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchase_orders')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `purchase_orders` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `po_number` varchar(50) DEFAULT NULL,
                  `vendor_id` bigint unsigned DEFAULT NULL,
                  `rfq_id` bigint unsigned DEFAULT NULL,
                  `status` varchar(20) NOT NULL DEFAULT 'draft',
                  `sub_total` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `expected_delivery_date` date DEFAULT NULL,
                  `terms_conditions` text,
                  `notes` text,
                  `owner_id` bigint unsigned DEFAULT NULL,
                  `created_by` bigint unsigned DEFAULT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `deleted_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `purchase_orders_tenant_id_foreign` (`tenant_id`),
                  KEY `purchase_orders_vendor_id_foreign` (`vendor_id`),
                  KEY `purchase_orders_rfq_id_foreign` (`rfq_id`),
                  KEY `purchase_orders_tenant_id_po_number_index` (`tenant_id`,`po_number`),
                  CONSTRAINT `purchase_orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL,
                  CONSTRAINT `purchase_orders_rfq_id_foreign` FOREIGN KEY (`rfq_id`) REFERENCES `request_for_quotations` (`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
