<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('admin_user_id')->nullable()->after('approved_by')->constrained('users')->nullOnDelete();
            $table->unique('admin_user_id');
            $table->string('database_name', 64)->nullable()->unique()->after('admin_user_id');
            $table->string('database_host')->nullable()->after('database_name');
            $table->unsignedSmallInteger('database_port')->nullable()->after('database_host');
            $table->string('database_username')->nullable()->after('database_port');
            $table->text('database_password')->nullable()->after('database_username');
            $table->string('provision_status', 30)->default('pending')->after('database_password')->index();
            $table->unsignedInteger('schema_version')->default(1)->after('provision_status');
            $table->timestamp('provisioned_at')->nullable()->after('schema_version');
            $table->timestamp('last_health_check_at')->nullable()->after('provisioned_at');
            $table->string('last_health_status', 30)->nullable()->after('last_health_check_at');
            $table->text('provision_error')->nullable()->after('last_health_status');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['admin_user_id']);
            $table->dropUnique(['admin_user_id']);
            $table->dropUnique(['database_name']);
            $table->dropColumn([
                'admin_user_id', 'database_name', 'database_host', 'database_port',
                'database_username', 'database_password', 'provision_status',
                'schema_version', 'provisioned_at', 'last_health_check_at',
                'last_health_status', 'provision_error',
            ]);
        });
    }
};
