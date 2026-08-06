<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Documents the live `user_list` table exactly as it exists in production.
 * This table becomes `user_list_legacy` in Phase 1 Step 4 once identities
 * are unified into a single `users` table — kept here for schema history.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_list')) {
            return;
        }

        DB::statement(<<<'SQL'
            CREATE TABLE `user_list` (
              `id` int unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) DEFAULT NULL,
              `email` varchar(255) DEFAULT NULL,
              `phone` varchar(255) DEFAULT NULL,
              `role` enum('agent') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
              `password` varchar(255) DEFAULT NULL,
              `backup` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
              `last_login` datetime DEFAULT NULL,
              `status` enum('Active','Inactive','Block') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
              `created_at` timestamp NOT NULL,
              `updated_at` timestamp NOT NULL,
              `session_token` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_list');
    }
};
