<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Documents the live `payment_details` table exactly as it exists in production.
 * `order_id` has no FK constraint in production (MyISAM engine can't enforce one) — preserved as-is.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_details')) {
            return;
        }

        DB::statement(<<<'SQL'
            CREATE TABLE `payment_details` (
              `id` int NOT NULL AUTO_INCREMENT,
              `order_id` int DEFAULT NULL,
              `payment_mode` enum('upi','bank_transfer','cash','cheque','card') DEFAULT NULL,
              `payment_date` datetime DEFAULT NULL,
              `paid_amount` float(12,2) DEFAULT NULL,
              `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_details');
    }
};
