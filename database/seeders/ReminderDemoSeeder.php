<?php

namespace Database\Seeders;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use App\Support\PermissionTeam;
use App\Support\TenantContext;
use App\Tenancy\TenantConnectionManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Exercises the full reminder pipeline (Task.remind_at -> TaskDueReminder,
 * Lead.follow_up_date/time -> LeadFollowUpReminder) against the same demo
 * tenant DemoDataSeeder already populated, so both the notification bell
 * and the redesigned dashboard widget have real, varied data to show —
 * some overdue, some due today, some upcoming — instead of an empty state.
 *
 * Purely additive: updates a couple of DemoDataSeeder's own rows (by the
 * exact titles/names it creates) to carry reminder times, adds a handful
 * of new task/lead rows for variety, then runs the two reminder commands
 * so real notifications land immediately — nothing here fakes a
 * Notification row directly, so this exercises the actual detection
 * queries rather than bypassing them.
 *
 * Run with: php artisan db:seed --class=ReminderDemoSeeder
 * (requires DemoDataSeeder to have been run first)
 */
class ReminderDemoSeeder extends Seeder
{
    private const TENANT_ID = 5;

    private const USER_ID = 5;

    public function run(): void
    {
        $connectionManager = app(TenantConnectionManager::class);
        $connectionManager->activate(self::TENANT_ID);
        $connectionName = $connectionManager->connectionName();
        TenantContext::set(self::TENANT_ID);
        PermissionTeam::set(self::TENANT_ID);

        DB::connection($connectionName)->transaction(function () {
            $this->backdateExistingTaskReminders();
            $this->seedFreshTasks();
            $this->scheduleLeadFollowUps();
        });

        PermissionTeam::set(null);
        TenantContext::clear();
        $connectionManager->deactivate();

        // Run the real detection commands (not a hand-made Notification
        // row) so what shows up in the bell is exactly what production's
        // everyMinute() schedule would have produced.
        Artisan::call('crm:send-task-reminders');
        Artisan::call('crm:send-lead-followup-reminders');

        $this->command?->info('Reminder demo data seeded and both reminder commands run — check the notification bell and dashboard.');
    }

    /**
     * Give two of DemoDataSeeder's own tasks a remind_at (it never sets
     * one), backdated a few minutes so they're immediately due.
     */
    private function backdateExistingTaskReminders(): void
    {
        $targets = [
            'Follow up on Nimbus Cloud proposal' => now()->subMinutes(12),
            'Send revised quote to Harbor Logistics' => now()->subMinutes(40),
        ];

        foreach ($targets as $title => $remindAt) {
            Task::where('tenant_id', self::TENANT_ID)
                ->where('title', $title)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->get()
                ->each(fn (Task $task) => $task->forceFill([
                    'remind_at' => $remindAt,
                    'notification_sent_at' => null,
                ])->save());
        }
    }

    /**
     * A few new tasks distinct from DemoDataSeeder's, spanning overdue /
     * due-now / due-soon so the dashboard's three urgency groups all have
     * something in them.
     */
    private function seedFreshTasks(): void
    {
        $dealIds = Deal::where('tenant_id', self::TENANT_ID)->pluck('id', 'name');

        $rows = [
            [
                'title' => 'Demo call with Skyline Retail',
                'deal' => 'Skyline Retail — POS rollout',
                'priority' => 'high',
                'due_in_minutes' => -90,
                'remind_minutes_ago' => 90,
            ],
            [
                'title' => 'Prep renewal notes for Ironclad AMC',
                'deal' => 'Ironclad — annual AMC renewal',
                'priority' => 'medium',
                'due_in_minutes' => 45,
                'remind_minutes_ago' => 5,
            ],
            [
                'title' => 'Internal sync: Nimbus Cloud migration risks',
                'deal' => 'Nimbus Cloud — migration project',
                'priority' => 'urgent',
                'due_in_minutes' => 60 * 26,
                'remind_in_minutes' => 60 * 25, // 25h from now — not due yet, stays out of this run
            ],
        ];

        foreach ($rows as $row) {
            $dueAt = now()->addMinutes($row['due_in_minutes']);
            $remindAt = isset($row['remind_in_minutes'])
                ? now()->addMinutes($row['remind_in_minutes'])
                : now()->subMinutes($row['remind_minutes_ago']);

            Task::create([
                'tenant_id' => self::TENANT_ID,
                'assigned_to' => self::USER_ID,
                'created_by' => self::USER_ID,
                'related_type' => 'deal',
                'related_id' => $dealIds[$row['deal']] ?? null,
                'title' => $row['title'],
                'priority' => $row['priority'],
                'status' => 'todo',
                'due_at' => $dueAt,
                'remind_at' => $remindAt,
            ]);
        }
    }

    /**
     * Schedule follow-ups on a couple of DemoDataSeeder's own leads —
     * one overdue, one due today — so LeadFollowUpReminder fires for both
     * via the model's own saving() hook resetting follow_up_notified_at.
     */
    private function scheduleLeadFollowUps(): void
    {
        $schedule = [
            'Ananya Sharma' => ['date' => now()->subHours(2), 'time' => now()->subHours(2)->format('H:i:s')],
            'Rohit Malhotra' => ['date' => now()->subMinutes(20), 'time' => now()->subMinutes(20)->format('H:i:s')],
            'Kavita Iyer' => ['date' => now()->addDay(), 'time' => '11:30:00'],
        ];

        foreach ($schedule as $name => $when) {
            Lead::where('tenant_id', self::TENANT_ID)
                ->where('name', $name)
                ->get()
                ->each(function (Lead $lead) use ($when) {
                    $lead->follow_up_date = $when['date']->toDateString();
                    $lead->follow_up_time = $when['time'];
                    $lead->save();
                });
        }
    }
}
