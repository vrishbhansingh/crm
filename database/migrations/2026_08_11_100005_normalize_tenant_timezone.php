<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tenants')->where('timezone', 'Asia/Calcutta')->update(['timezone' => 'Asia/Kolkata']);
        DB::statement("ALTER TABLE `tenants` MODIFY `timezone` VARCHAR(64) NOT NULL DEFAULT 'Asia/Kolkata'");
    }

    public function down(): void
    {
        DB::table('tenants')->where('timezone', 'Asia/Kolkata')->update(['timezone' => 'Asia/Calcutta']);
        DB::statement("ALTER TABLE `tenants` MODIFY `timezone` VARCHAR(64) NOT NULL DEFAULT 'Asia/Calcutta'");
    }
};
