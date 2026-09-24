<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rfq_items')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `rfq_items` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `rfq_id` bigint unsigned NOT NULL,
                  `product_id` bigint unsigned DEFAULT NULL,
                  `description` varchar(255) NOT NULL,
                  `uom` varchar(100) DEFAULT NULL,
                  `quantity` decimal(12,2) NOT NULL DEFAULT 1.00,
                  `sort_order` int unsigned NOT NULL DEFAULT 0,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `rfq_items_tenant_id_foreign` (`tenant_id`),
                  KEY `rfq_items_product_id_foreign` (`product_id`),
                  KEY `rfq_items_rfq_sort_index` (`rfq_id`,`sort_order`),
                  CONSTRAINT `rfq_items_rfq_id_foreign` FOREIGN KEY (`rfq_id`) REFERENCES `request_for_quotations` (`id`) ON DELETE CASCADE,
                  CONSTRAINT `rfq_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_items');
    }
};
