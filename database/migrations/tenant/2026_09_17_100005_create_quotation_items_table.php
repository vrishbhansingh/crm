<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Line items snapshot the product's name/uom/price/tax at the moment
 * they're added (description/uom/unit_price/tax_percent are copies, not
 * live joins) — this is what makes a sent quotation immutable and safe to
 * re-render identically even if the Product is edited or deleted later.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quotation_items')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `quotation_items` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `quotation_id` bigint unsigned NOT NULL,
                  `product_id` bigint unsigned DEFAULT NULL,
                  `description` varchar(255) NOT NULL,
                  `uom` varchar(100) DEFAULT NULL,
                  `quantity` decimal(12,2) NOT NULL DEFAULT 1.00,
                  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
                  `tax_rate_id` bigint unsigned DEFAULT NULL,
                  `tax_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
                  `line_total` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `sort_order` int unsigned NOT NULL DEFAULT 0,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `quotation_items_tenant_id_foreign` (`tenant_id`),
                  KEY `quotation_items_product_id_foreign` (`product_id`),
                  KEY `quotation_items_tax_rate_id_foreign` (`tax_rate_id`),
                  KEY `quotation_items_quotation_id_sort_order_index` (`quotation_id`,`sort_order`),
                  CONSTRAINT `quotation_items_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE,
                  CONSTRAINT `quotation_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
                  CONSTRAINT `quotation_items_tax_rate_id_foreign` FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
