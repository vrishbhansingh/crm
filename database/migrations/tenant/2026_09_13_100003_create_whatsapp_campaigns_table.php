<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('whatsapp_campaigns')) {
            return;
        }

        Schema::create('whatsapp_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('whatsapp_account_id');
            $table->string('name', 150);
            $table->string('message_type', 20)->default('text'); // text | template
            $table->string('template_name', 150)->nullable();
            $table->json('template_params')->nullable();
            $table->text('body')->nullable();
            $table->string('audience_type'); // leads | contacts | companies
            $table->json('audience_filters')->nullable();
            $table->string('status')->default('draft'); // draft, scheduled, sending, sent, failed
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_campaigns');
    }
};
