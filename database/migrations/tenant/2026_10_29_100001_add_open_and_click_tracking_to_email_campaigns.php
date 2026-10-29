<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Email campaign engagement: when each recipient opened the email and which
 * links they clicked. Per-recipient totals live on email_campaign_recipients
 * (for fast lists), the campaign keeps unique opened/clicked counts, and
 * every individual open/click is kept in email_campaign_events so the
 * report can show a real timeline ("opened 10:42, clicked pricing link 10:43").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaign_recipients', function (Blueprint $table) {
            $table->timestamp('opened_at')->nullable();
            $table->unsignedInteger('open_count')->default(0);
            $table->timestamp('clicked_at')->nullable();
            $table->unsignedInteger('click_count')->default(0);
        });

        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->unsignedInteger('opened_count')->default(0);
            $table->unsignedInteger('clicked_count')->default(0);
        });

        Schema::create('email_campaign_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreignId('email_campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
            $table->foreignId('email_campaign_recipient_id')->constrained('email_campaign_recipients')->cascadeOnDelete();
            $table->string('type', 10); // open | click
            $table->text('url')->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['email_campaign_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_events');

        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->dropColumn(['opened_count', 'clicked_count']);
        });

        Schema::table('email_campaign_recipients', function (Blueprint $table) {
            $table->dropColumn(['opened_at', 'open_count', 'clicked_at', 'click_count']);
        });
    }
};
