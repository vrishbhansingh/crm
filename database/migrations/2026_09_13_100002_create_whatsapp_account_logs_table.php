<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_account_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_account_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('direction', 10); // inbound | outbound
            $table->string('status', 20); // received, sent, failed, ignored
            $table->string('external_ref', 190)->nullable();
            $table->text('message')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['whatsapp_account_id', 'external_ref']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_account_logs');
    }
};
