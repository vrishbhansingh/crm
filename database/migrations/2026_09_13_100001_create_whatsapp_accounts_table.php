<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            // 'meta_cloud' (official WhatsApp Business Cloud API) or
            // 'unofficial' (a generic third-party REST gateway configured
            // with just an API key + sender number, e.g. Ultramsg/Green-API).
            $table->string('channel_type', 20);
            $table->string('name', 150);
            $table->string('phone_number', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            // Opaque public identifier for the inbound webhook URL — same
            // pattern as lead_integrations.token.
            $table->string('webhook_token', 64)->unique();
            // Meta's own handshake secret (GET ?hub.verify_token=...); the
            // unofficial channel has no handshake so this stays null there.
            $table->string('verify_token', 80)->nullable();
            // Channel-specific settings (access_token/phone_number_id/
            // waba_id/app_secret for meta_cloud; api_url/api_key/
            // http_method/body_template/headers for unofficial) — kept as
            // one encrypted JSON blob rather than a pile of nullable
            // columns since the two channel types share almost no fields.
            $table->text('credentials')->nullable();
            $table->unsignedInteger('messages_sent_count')->default(0);
            $table->unsignedInteger('messages_received_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_accounts');
    }
};
