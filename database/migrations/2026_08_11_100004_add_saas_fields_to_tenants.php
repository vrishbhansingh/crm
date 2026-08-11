<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('contact_email')->nullable()->after('status');
            $table->string('plan', 40)->default('standard')->after('contact_email');
            $table->string('timezone', 64)->default('Asia/Kolkata')->after('plan');
            $table->string('locale', 10)->default('en')->after('timezone');
            $table->unsignedInteger('max_users')->nullable()->after('locale');
            $table->timestamp('trial_ends_at')->nullable()->after('max_users');
            $table->json('settings')->nullable()->after('trial_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'plan', 'timezone', 'locale', 'max_users', 'trial_ends_at', 'settings']);
        });
    }
};
