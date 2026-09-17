<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('company_details', 'default_quotation_terms')) {
            return;
        }

        Schema::table('company_details', function (Blueprint $table) {
            // Pre-fills new quotations' terms & conditions — editable per
            // quotation afterward, so changing this default never touches
            // quotations that already copied it.
            $table->text('default_quotation_terms')->nullable()->after('ifsc_code');
        });
    }

    public function down(): void
    {
        Schema::table('company_details', function (Blueprint $table) {
            $table->dropColumn('default_quotation_terms');
        });
    }
};
