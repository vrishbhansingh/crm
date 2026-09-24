<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('request_for_quotations')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `request_for_quotations` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `tenant_id` bigint unsigned DEFAULT NULL,
                  `rfq_number` varchar(50) DEFAULT NULL,
                  `status` varchar(20) NOT NULL DEFAULT 'draft',
                  `notes` text,
                  `owner_id` bigint unsigned DEFAULT NULL,
                  `created_by` bigint unsigned DEFAULT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `deleted_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `request_for_quotations_tenant_id_foreign` (`tenant_id`),
                  KEY `rfq_tenant_id_rfq_number_index` (`tenant_id`,`rfq_number`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('request_for_quotations');
    }
};
