<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the single flat SMTP config on `tenants` with a real one-to-many
 * table: a company can now save multiple named SMTP configurations and
 * switch which one is active, instead of only ever having one. The old
 * `tenants.smtp_*` columns are left in place (not dropped) and their data is
 * copied into this table as each tenant's first "Default" config, so nobody
 * loses an SMTP setup they already configured.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_mail_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->string('smtp_host')->nullable();
            $table->unsignedSmallInteger('smtp_port')->nullable();
            $table->string('smtp_encryption', 10)->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('smtp_from_address')->nullable();
            $table->string('smtp_from_name')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        DB::table('tenants')
            ->where('smtp_enabled', true)
            ->whereNotNull('smtp_host')
            ->orderBy('id')
            ->each(function ($tenant) {
                DB::table('tenant_mail_settings')->insert([
                    'tenant_id' => $tenant->id,
                    'name' => 'Default',
                    'is_active' => true,
                    'smtp_host' => $tenant->smtp_host,
                    'smtp_port' => $tenant->smtp_port,
                    'smtp_encryption' => $tenant->smtp_encryption,
                    'smtp_username' => $tenant->smtp_username,
                    'smtp_password' => $tenant->smtp_password,
                    'smtp_from_address' => $tenant->smtp_from_address,
                    'smtp_from_name' => $tenant->smtp_from_name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_mail_settings');
    }
};
