<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * QuotationNumberService mirrors LeadNumberService's two-step pattern —
 * create the row, then assign the number via a race-safe tenant_sequences
 * upsert — so this column needs to tolerate a brief null between those two
 * steps, same as leads.lead_number already does.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `quotations` MODIFY `quotation_number` VARCHAR(50) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `quotations` MODIFY `quotation_number` VARCHAR(50) NOT NULL');
    }
};
