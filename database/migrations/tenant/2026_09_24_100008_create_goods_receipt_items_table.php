<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('goods_receipt_items')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `goods_receipt_items` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `goods_receipt_id` bigint unsigned NOT NULL,
                  `purchase_order_item_id` bigint unsigned NOT NULL,
                  `received_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `remarks` varchar(255) DEFAULT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `goods_receipt_items_tenant_id_foreign` (`tenant_id`),
                  KEY `goods_receipt_items_purchase_order_item_id_foreign` (`purchase_order_item_id`),
                  CONSTRAINT `goods_receipt_items_goods_receipt_id_foreign` FOREIGN KEY (`goods_receipt_id`) REFERENCES `goods_receipts` (`id`) ON DELETE CASCADE,
                  CONSTRAINT `goods_receipt_items_purchase_order_item_id_foreign` FOREIGN KEY (`purchase_order_item_id`) REFERENCES `purchase_order_items` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
    }
};
