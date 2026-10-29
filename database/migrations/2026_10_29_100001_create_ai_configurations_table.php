<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A company's own AI provider keys. Several can be saved: the assistant
 * uses them in priority order and moves to the next one automatically when
 * a key expires, runs out of credit or is rate limited. Lives on the central
 * connection (like lead_integrations / whatsapp_accounts) so a key can be
 * resolved before any tenant database is open. api_key is encrypted by the
 * model cast and never sent back to the browser.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('api_key');
            $table->string('key_hint', 12)->nullable();
            $table->string('notify_email', 190)->nullable();
            $table->string('model', 60)->nullable();
            $table->unsignedSmallInteger('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('status', 20)->default('untested'); // untested | ok | failing
            $table->text('last_error')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_configurations');
    }
};
