<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_campaign_recipients')) {
            return;
        }

        Schema::create('email_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('email_campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
            // Polymorphic by hand rather than morphs(): leads.id is `int
            // unsigned` while contacts/companies.id are `bigint unsigned`,
            // so a single strict FK type can't cover all three targets.
            $table->string('recipient_type'); // lead | contact | company
            $table->unsignedBigInteger('recipient_id');
            $table->string('email');
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_recipients');
    }
};
