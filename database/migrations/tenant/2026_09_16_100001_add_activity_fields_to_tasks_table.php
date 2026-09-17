<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tasks', 'activity_type')) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table) {
            // Task/Call/Meeting all live in one row — see activity_details
            // for the fields that only apply to calls or meetings.
            $table->string('activity_type', 20)->default('task')->after('related_id')->index();
            $table->json('activity_details')->nullable()->after('description');
            $table->json('checklist')->nullable()->after('activity_details');

            $table->string('recurrence_rule', 20)->nullable()->after('completed_at');
            $table->smallInteger('recurrence_interval')->unsigned()->nullable()->after('recurrence_rule');
            $table->date('recurrence_end_date')->nullable()->after('recurrence_interval');
            $table->unsignedBigInteger('recurrence_parent_id')->nullable()->after('recurrence_end_date')->index();

            $table->unsignedBigInteger('depends_on_task_id')->nullable()->after('recurrence_parent_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'activity_type',
                'activity_details',
                'checklist',
                'recurrence_rule',
                'recurrence_interval',
                'recurrence_end_date',
                'recurrence_parent_id',
                'depends_on_task_id',
            ]);
        });
    }
};
