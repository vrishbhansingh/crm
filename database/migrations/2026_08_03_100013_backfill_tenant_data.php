<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * One-time data migration: creates the first tenant from the existing
 * (single-row) company_details data and backfills tenant_id on every
 * business table so existing data keeps working under the new tenant scope.
 */
return new class extends Migration
{
    public function up(): void
    {
        $company = DB::table('company_details')->orderBy('id')->first();
        $name = $company->company_name ?? 'Default Company';

        $tenantId = DB::table('tenants')->insertGetId([
            'name' => $name,
            'slug' => Str::slug($name) ?: 'default-company',
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tables = [
            'company_details', 'leads', 'orders', 'customer_contact',
            'lead_follow_up', 'project_info', 'payment_details', 'user_attendance',
        ];

        foreach ($tables as $table) {
            DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        }
    }

    public function down(): void
    {
        // Backfilled data intentionally left in place — reversing this migration
        // alone would orphan business rows. Roll back the tenant_id columns
        // migration (which drops the columns entirely) if a full revert is needed.
    }
};
