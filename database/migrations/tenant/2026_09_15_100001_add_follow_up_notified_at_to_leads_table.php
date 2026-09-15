<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('leads', 'follow_up_notified_at')) {
            return;
        }

        Schema::table('leads', function (Blueprint $table) {
            // Tracks whether a reminder notification has already gone out
            // for the lead's current follow_up_date/time — same pattern as
            // tasks.notification_sent_at, so a follow-up only ever fires
            // once and re-scheduling it (a new date/time) fires again.
            $table->timestamp('follow_up_notified_at')->nullable()->index()->after('follow_up_time');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('follow_up_notified_at');
        });
    }
};
