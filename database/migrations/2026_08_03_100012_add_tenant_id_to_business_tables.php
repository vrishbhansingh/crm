<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds tenant scoping to every business table. InnoDB tables get a real FK
 * to `tenants`; the two MyISAM tables (company_details, payment_details)
 * can't hold a FK, so they get the column + index only, matching the
 * unenforced-relationship pattern already present elsewhere in this schema.
 */
return new class extends Migration
{
    private array $innoDbTables = [
        'leads', 'orders', 'customer_contact', 'lead_follow_up',
        'project_info', 'user_attendance',
    ];

    private array $myIsamTables = [
        'company_details', 'payment_details',
    ];

    public function up(): void
    {
        foreach ($this->innoDbTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')
                    ->constrained('tenants')->nullOnDelete();
            });
        }

        foreach ($this->myIsamTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->innoDbTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }

        foreach ($this->myIsamTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex([$tableName === 'company_details' ? 'company_details_tenant_id_index' : 'payment_details_tenant_id_index']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
