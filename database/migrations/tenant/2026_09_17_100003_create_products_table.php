<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `products` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `name` varchar(255) NOT NULL,
                  `sku` varchar(100) DEFAULT NULL,
                  `category` varchar(100) DEFAULT NULL,
                  `uom` varchar(100) DEFAULT NULL,
                  `hsn_sac` varchar(50) DEFAULT NULL,
                  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
                  `tax_rate_id` bigint unsigned DEFAULT NULL,
                  `description` text,
                  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `products_tenant_id_foreign` (`tenant_id`),
                  KEY `products_tax_rate_id_foreign` (`tax_rate_id`),
                  CONSTRAINT `products_tax_rate_id_foreign` FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
