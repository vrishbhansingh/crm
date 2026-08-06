<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Documents the live `admin_details` table exactly as it exists in production.
 * Guarded by hasTable() so it is a no-op against the current database (the
 * table already exists) but still creates it correctly on a fresh install.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_details')) {
            return;
        }

        DB::statement(<<<'SQL'
            CREATE TABLE `admin_details` (
              `id` int NOT NULL AUTO_INCREMENT,
              `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
              `username` varchar(255) DEFAULT NULL,
              `email` varchar(255) DEFAULT NULL,
              `password` varchar(255) DEFAULT NULL,
              `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
              `created_At` timestamp NOT NULL,
              `updated_At` timestamp NOT NULL,
              `session_token` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_details');
    }
};
