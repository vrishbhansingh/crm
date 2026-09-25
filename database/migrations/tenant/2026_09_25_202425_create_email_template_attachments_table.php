<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_template_attachments', function (Blueprint $table) {
            $table->id();
            // No FK on tenant_id — tenants live in the master database, not
            // here (same reasoning as email_templates' own migration).
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('email_template_id');
            $table->foreign('email_template_id')->references('id')->on('email_templates')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_path');
            $table->unsignedBigInteger('size');
            $table->string('mime_type')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_template_attachments');
    }
};
