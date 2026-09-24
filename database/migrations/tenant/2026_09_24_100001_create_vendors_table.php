<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vendors')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `vendors` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `name` varchar(255) NOT NULL,
                  `contact_person` varchar(255) DEFAULT NULL,
                  `email` varchar(255) DEFAULT NULL,
                  `phone` varchar(50) DEFAULT NULL,
                  `gst_number` varchar(50) DEFAULT NULL,
                  `state` varchar(255) DEFAULT NULL,
                  `address` varchar(255) DEFAULT NULL,
                  `city` varchar(255) DEFAULT NULL,
                  `pincode` varchar(20) DEFAULT NULL,
                  `payment_terms` varchar(255) DEFAULT NULL,
                  `status` varchar(20) NOT NULL DEFAULT 'Active',
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `vendors_tenant_id_foreign` (`tenant_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
