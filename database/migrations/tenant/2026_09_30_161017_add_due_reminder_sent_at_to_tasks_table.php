<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks the "due today" reminder separately from notification_sent_at
     * (which only tracks the manually-set remind_at reminder) — a task
     * can now fire both an assignee's chosen remind_at reminder AND a
     * same-day due reminder without either one suppressing the other.
     */
    public function up(): void
    {
        if (Schema::hasColumn('tasks', 'due_reminder_sent_at')) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('due_reminder_sent_at')->nullable()->after('notification_sent_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('due_reminder_sent_at');
        });
    }
};
