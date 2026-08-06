<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Converts columns that were MySQL ENUMs (requiring a migration to add a new
 * value) into plain VARCHAR(50), now that valid values live in master_values
 * instead. Lossless: ENUMs already store/return their values as strings, and
 * live data was checked beforehand to only use already-valid values.
 */
return new class extends Migration
{
    // Preserves each column's original NULL-ability/default exactly —
    // only the type changes from ENUM to VARCHAR(50).
    private array $statements = [
        "ALTER TABLE `leads` MODIFY `lead_type` VARCHAR(50) NULL DEFAULT 'inquiry'",
        "ALTER TABLE `leads` MODIFY `lead_source` VARCHAR(50) NULL",
        "ALTER TABLE `leads` MODIFY `priority` VARCHAR(50) NOT NULL DEFAULT 'high'",
        "ALTER TABLE `leads` MODIFY `lead_status` VARCHAR(50) NOT NULL DEFAULT 'new'",
        "ALTER TABLE `orders` MODIFY `order_status` VARCHAR(50) NOT NULL DEFAULT 'new'",
        "ALTER TABLE `orders` MODIFY `payment_terms` VARCHAR(50) NULL",
        "ALTER TABLE `orders` MODIFY `payment_status` VARCHAR(50) NULL",
        "ALTER TABLE `orders` MODIFY `currency` VARCHAR(50) NOT NULL DEFAULT 'INR'",
        "ALTER TABLE `project_info` MODIFY `priority` VARCHAR(50) NULL",
        "ALTER TABLE `payment_details` MODIFY `payment_mode` VARCHAR(50) NULL",
    ];

    public function up(): void
    {
        foreach ($this->statements as $statement) {
            DB::statement($statement);
        }
    }

    public function down(): void
    {
        // Original ENUM definitions are documented in the Phase 1 schema-hygiene
        // migrations (database/migrations/2026_08_03_1000*_create_*_table.php) —
        // not restored here since down() isn't expected to be run in production.
    }
};
