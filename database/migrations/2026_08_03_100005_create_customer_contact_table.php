<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Documents the live `customer_contact` table exactly as it exists in production.
 * `lead_id` is indexed but has no FK constraint in production — preserved as-is.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_contact')) {
            return;
        }

        DB::statement(<<<'SQL'
            CREATE TABLE `customer_contact` (
              `id` int NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `phone` varchar(50) NOT NULL,
              `email` varchar(255) NOT NULL,
              `designation` varchar(255) NOT NULL,
              `budget` double(10,2) NOT NULL,
              `city` varchar(255) NOT NULL,
              `lead_id` int unsigned NOT NULL,
              `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `lead_id` (`lead_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_contact');
    }
};
