<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Facebook Lead Ads webhooks only deliver a leadgen_id; the actual answers
 * have to be fetched from the Graph API with a Page access token. Stored
 * encrypted (model cast) and only ever used server-side.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_integrations', function (Blueprint $table) {
            $table->text('page_access_token')->nullable()->after('secret');
        });
    }

    public function down(): void
    {
        Schema::table('lead_integrations', function (Blueprint $table) {
            $table->dropColumn('page_access_token');
        });
    }
};
