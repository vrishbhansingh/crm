<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant business schema, tier 5: `deal_stage_history`, depends on `deals`
 * and `pipeline_stages`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('deal_stage_history')) {
            DB::statement(<<<'SQL'
                CREATE TABLE `deal_stage_history` (
                  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                  `deal_id` bigint unsigned NOT NULL,
                  `from_stage_id` bigint unsigned DEFAULT NULL,
                  `to_stage_id` bigint unsigned DEFAULT NULL,
                  `changed_by` bigint unsigned DEFAULT NULL,
                  `created_at` timestamp NULL DEFAULT NULL,
                  `updated_at` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `deal_stage_history_deal_id_foreign` (`deal_id`),
                  KEY `deal_stage_history_from_stage_id_foreign` (`from_stage_id`),
                  KEY `deal_stage_history_to_stage_id_foreign` (`to_stage_id`),
                  KEY `deal_stage_history_changed_by_foreign` (`changed_by`),
                  CONSTRAINT `deal_stage_history_deal_id_foreign` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
                  CONSTRAINT `deal_stage_history_from_stage_id_foreign` FOREIGN KEY (`from_stage_id`) REFERENCES `pipeline_stages` (`id`) ON DELETE SET NULL,
                  CONSTRAINT `deal_stage_history_to_stage_id_foreign` FOREIGN KEY (`to_stage_id`) REFERENCES `pipeline_stages` (`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_stage_history');
    }
};
