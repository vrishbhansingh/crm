<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshots product description/uom/hsn_sac/unit_price/tax_percent at
 * add-time, same immutability rationale as quotation_items. `received_qty`
 * is a running total kept in sync by GoodsReceiptController whenever a GRN
 * is recorded against this line, so PurchaseOrder status can be derived
 * without re-summing goods_receipt_items on every read.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchase_order_items')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `purchase_order_items` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `purchase_order_id` bigint unsigned NOT NULL,
                  `product_id` bigint unsigned DEFAULT NULL,
                  `description` varchar(255) NOT NULL,
                  `uom` varchar(100) DEFAULT NULL,
                  `hsn_sac` varchar(50) DEFAULT NULL,
                  `quantity` decimal(12,2) NOT NULL DEFAULT 1.00,
                  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
                  `tax_rate_id` bigint unsigned DEFAULT NULL,
                  `tax_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
                  `line_total` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `received_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `sort_order` int unsigned NOT NULL DEFAULT 0,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `purchase_order_items_tenant_id_foreign` (`tenant_id`),
                  KEY `purchase_order_items_product_id_foreign` (`product_id`),
                  KEY `purchase_order_items_tax_rate_id_foreign` (`tax_rate_id`),
                  KEY `purchase_order_items_po_sort_index` (`purchase_order_id`,`sort_order`),
                  CONSTRAINT `purchase_order_items_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
                  CONSTRAINT `purchase_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
                  CONSTRAINT `purchase_order_items_tax_rate_id_foreign` FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
