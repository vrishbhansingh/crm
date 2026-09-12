<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Webhook-based lead capture from external platforms (IndiaMART, JustDial,
 * Facebook Lead Ads, Google Ads, WhatsApp, Website/Zapier). Lives centrally
 * rather than per-tenant: the public webhook URL only carries an opaque
 * token, and resolving "which tenant does this token belong to" has to
 * happen before any tenant database connection can be activated — a central
 * table gives that a single, fast lookup instead of scanning every tenant's
 * own database for a match.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('platform', 40);
            $table->string('name', 150);
            $table->string('token', 64)->unique();
            $table->text('secret')->nullable();
            $table->string('verify_token', 80)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('leads_created_count')->default(0);
            $table->timestamp('last_received_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_integrations');
    }
};
