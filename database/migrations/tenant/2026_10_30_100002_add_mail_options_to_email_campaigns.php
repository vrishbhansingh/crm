<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Per-campaign SMTP choice (a saved tenant_mail_settings row) plus CC / BCC addresses. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('mail_setting_id')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->dropColumn(['mail_setting_id', 'cc', 'bcc']);
        });
    }
};
