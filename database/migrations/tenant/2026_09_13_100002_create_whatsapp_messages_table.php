<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('whatsapp_messages')) {
            return;
        }

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('whatsapp_conversation_id')->constrained('whatsapp_conversations')->cascadeOnDelete();
            $table->string('direction', 10); // in | out
            $table->string('type', 20)->default('text'); // text, template, image, document, audio, video, location, other
            $table->text('body')->nullable();
            $table->string('template_name', 150)->nullable();
            $table->json('template_params')->nullable();
            $table->string('media_url', 500)->nullable();
            $table->string('status', 20)->default('queued'); // queued, sent, delivered, read, failed, received
            $table->string('wa_message_id', 120)->nullable();
            $table->text('error_message')->nullable();
            // Who/what put this message on the wire: 'agent' (a user typed
            // it), 'campaign', 'automation', or null for an inbound message.
            $table->string('source', 20)->nullable();
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->timestamps();

            $table->index('wa_message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
