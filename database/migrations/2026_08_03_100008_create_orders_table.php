<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Documents the live `orders` table exactly as it exists in production,
 * including its 3 real foreign keys (the only enforced FKs in the whole schema).
 * Depends on `leads`, `project_info`, and `user_list` already existing.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            return;
        }

        DB::statement(<<<'SQL'
            CREATE TABLE `orders` (
              `id` int NOT NULL AUTO_INCREMENT,
              `order_number` varchar(255) DEFAULT NULL,
              `lead_id` int unsigned DEFAULT NULL,
              `invoice_date` datetime DEFAULT NULL,
              `invoice_id` varchar(10) DEFAULT NULL,
              `project_id` int unsigned DEFAULT NULL,
              `user_id` int unsigned DEFAULT NULL,
              `sub_total` double(10,2) DEFAULT NULL,
              `discount` double(10,2) DEFAULT NULL,
              `gst` int DEFAULT NULL,
              `total_amount` double(10,2) DEFAULT NULL,
              `order_status` enum('new','approved','in_progress','on_hold','delivered','closed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'new',
              `payment_terms` enum('advance','partial_advance','on_delivery','net_15','net_30') DEFAULT NULL,
              `payment_status` enum('pending','partial','paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
              `currency` enum('INR','EURO','DOLLOR') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'INR',
              `due_amount` double(10,2) DEFAULT NULL,
              `net_amount` double(10,2) DEFAULT NULL,
              `paid_amount` double(10,2) DEFAULT NULL,
              `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `lead_id` (`lead_id`),
              KEY `project_id` (`project_id`),
              KEY `user_id` (`user_id`),
              CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `project_info` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user_list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
