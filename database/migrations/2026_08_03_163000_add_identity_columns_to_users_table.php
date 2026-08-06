<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends the stock `users` table into the CRM's single identity table,
 * replacing the split admin_details/user_list design. tenant_id is nullable:
 * null means a platform-level Super Admin (not scoped to any one tenant).
 * legacy_type/legacy_id record where each row was migrated from, for
 * auditing and for the FK-remap step in crm:migrate-identities.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')
                ->constrained('tenants')->nullOnDelete();
            $table->string('username')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->enum('status', ['Active', 'Inactive', 'Block'])->default('Active')->after('password');
            $table->string('session_token')->nullable()->after('status');
            $table->string('legacy_type')->nullable()->after('session_token');
            $table->unsignedInteger('legacy_id')->nullable()->after('legacy_type');

            $table->index(['legacy_type', 'legacy_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn(['username', 'phone', 'status', 'session_token', 'legacy_type', 'legacy_id']);
        });
    }
};
