<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('deals', 'source')) {
            return;
        }

        Schema::table('deals', function (Blueprint $table) {
            // Deals had no source of their own before this — a deal
            // converted from a lead inherits the lead's lead_source, and a
            // directly-created deal can have one picked explicitly. Same
            // free-text-ish convention as leads.lead_source (matched
            // against the same 'lead_source' master values), not an FK.
            $table->string('source', 60)->nullable()->after('contact_id');
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
