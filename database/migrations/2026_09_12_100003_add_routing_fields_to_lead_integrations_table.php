<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a webhook integration decide, at creation time, exactly what a lead
 * arriving through it should look like: its starting type/status/priority,
 * who it's assigned to (instead of always falling back to round-robin), and
 * which sales funnel (Pipeline) it should be dropped into as a Deal.
 *
 * pipeline_id is a plain integer, not a real foreign key — Pipelines live in
 * each tenant's own isolated database (DB-per-tenant), while
 * lead_integrations is central, so there's no single database a FK
 * constraint could span. The starting *stage* within that pipeline is
 * deliberately not stored here at all: it's resolved fresh at lead-creation
 * time (first non-won/non-lost stage, same rule LeadDetailController's own
 * convertToDeal() already uses) rather than pinned to a stage id that could
 * later be renamed or deleted out from under a long-lived integration.
 * default_assigned_to CAN be a real FK: users are central too.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_integrations', function (Blueprint $table) {
            $table->string('default_lead_type', 40)->nullable()->after('platform');
            $table->string('default_lead_status', 40)->nullable()->after('default_lead_type');
            $table->string('default_priority', 40)->nullable()->after('default_lead_status');
            $table->foreignId('default_assigned_to')->nullable()->after('default_priority')->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('pipeline_id')->nullable()->after('default_assigned_to');
            $table->json('field_mapping')->nullable()->after('pipeline_id');
        });
    }

    public function down(): void
    {
        Schema::table('lead_integrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_assigned_to');
            $table->dropColumn(['default_lead_type', 'default_lead_status', 'default_priority', 'pipeline_id', 'field_mapping']);
        });
    }
};
