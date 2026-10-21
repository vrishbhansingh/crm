<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-account switch and settings for the AI auto-reply bot. Off by default:
 * nothing is ever auto-sent to customers unless a tenant admin turns it on
 * for a specific WhatsApp number. The bot's per-chat state is derived from
 * the message history, so no per-conversation columns are needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->boolean('ai_bot_enabled')->default(false)->after('is_default');
            $table->text('ai_bot_instructions')->nullable()->after('ai_bot_enabled');
            $table->unsignedTinyInteger('ai_bot_max_replies')->default(6)->after('ai_bot_instructions');
            $table->string('ai_bot_handoff_message', 300)->nullable()->after('ai_bot_max_replies');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->dropColumn(['ai_bot_enabled', 'ai_bot_instructions', 'ai_bot_max_replies', 'ai_bot_handoff_message']);
        });
    }
};
