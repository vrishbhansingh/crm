<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('leads', 'email_normalized')) {
            return;
        }

        // Duplicate detection (App\Services\LeadUniquenessService) compares
        // against these instead of the raw email/phone columns, so
        // "John@Example.com" vs "john@example.com" and differently-formatted
        // phone numbers are recognized as the same contact. Plain (not
        // unique) indexes: some tenants already have legacy duplicate
        // leads, and a unique constraint would fail this migration outright
        // for them — uniqueness itself is enforced at the application layer
        // (see LeadUniquenessService::findDuplicate()), not the database.
        Schema::table('leads', function (Blueprint $table) {
            $table->string('email_normalized', 255)->nullable()->after('email')->index();
            $table->string('phone_normalized', 32)->nullable()->after('phone')->index();
        });

        // Backfill existing rows so pre-existing leads are immediately
        // checkable against, not just leads saved after this migration.
        DB::table('leads')->orderBy('id')->select('id', 'email', 'phone')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('leads')->where('id', $row->id)->update([
                        'email_normalized' => \App\Services\LeadUniquenessService::normalizeEmail($row->email),
                        'phone_normalized' => \App\Services\LeadUniquenessService::normalizePhone($row->phone),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['email_normalized', 'phone_normalized']);
        });
    }
};
