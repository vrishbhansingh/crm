<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Company-wide "default funnel": every new lead that arrives without a funnel
 * of its own (manual form, bulk upload, API, WhatsApp, or an integration with
 * no pipeline chosen) is automatically added to this pipeline as a deal.
 *
 * A plain integer, not a foreign key: pipelines live in each company's own
 * database (DB-per-tenant) while `tenants` is central — same reasoning as
 * lead_integrations.pipeline_id. NULL = leads stay leads (the old behaviour).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->unsignedBigInteger('default_pipeline_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('default_pipeline_id');
        });
    }
};
