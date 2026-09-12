<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every inbound webhook call, successful or not — the only real way to
 * debug a platform's payload shape after the fact, and the deduplication
 * check (external_ref) against retried deliveries (IndiaMART, for one,
 * retries a failed push for up to 48 hours) needs a record to check against.
 * created_lead_id is a plain int, not a foreign key: the Lead it points to
 * lives in the tenant's own isolated database, not this central one, so
 * there's no real FK to declare across that boundary.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_integration_id')->constrained('lead_integrations')->cascadeOnDelete();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('status', 20);
            $table->string('external_ref', 190)->nullable();
            $table->unsignedBigInteger('created_lead_id')->nullable();
            $table->text('message')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['lead_integration_id', 'external_ref']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_integration_logs');
    }
};
