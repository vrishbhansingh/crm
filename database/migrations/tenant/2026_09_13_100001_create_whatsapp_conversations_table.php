<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('whatsapp_conversations')) {
            return;
        }

        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            // References the central whatsapp_accounts table — plain
            // integer, no FK, since that table lives in a different
            // database (same reasoning as lead_integrations.pipeline_id).
            $table->unsignedBigInteger('whatsapp_account_id');
            $table->unsignedInteger('lead_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('wa_phone', 20);
            $table->string('wa_name', 150)->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->string('last_message_preview', 255)->nullable();
            $table->unsignedInteger('unread_count')->default(0);
            // The Meta 24-hour customer-service window: free-form replies
            // are only allowed until this expires, after which a message
            // must use an approved template. Not meaningful for the
            // unofficial channel, which stays null.
            $table->timestamp('window_expires_at')->nullable();
            $table->timestamps();

            $table->unique(['whatsapp_account_id', 'wa_phone'], 'wa_conv_account_phone_unique');
            $table->index('lead_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversations');
    }
};
