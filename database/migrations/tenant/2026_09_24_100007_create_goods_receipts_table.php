<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('goods_receipts')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `goods_receipts` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `grn_number` varchar(50) DEFAULT NULL,
                  `purchase_order_id` bigint unsigned NOT NULL,
                  `received_date` date NOT NULL,
                  `notes` text,
                  `created_by` bigint unsigned DEFAULT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `goods_receipts_tenant_id_foreign` (`tenant_id`),
                  KEY `goods_receipts_purchase_order_id_foreign` (`purchase_order_id`),
                  CONSTRAINT `goods_receipts_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};
