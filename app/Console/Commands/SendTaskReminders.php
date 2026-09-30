<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\Tenant;
use App\Notifications\TaskDueReminder;
use App\Services\EmailLogger;
use App\Services\MailConfigurator;
use App\Support\TenantContext;
use App\Tenancy\TenantConnectionManager;
use App\Support\PermissionTeam;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    protected $signature = 'crm:send-task-reminders';

    protected $description = 'Send due in-app and email reminders for assigned CRM tasks';

    public function handle(TenantConnectionManager $connections): int
    {
        $sent = 0;

        if (config('tenancy.mode') === 'shared') {
            $this->sendForActiveTenant($sent);
            $this->sendDueTodayForActiveTenant($sent, null);
            $this->info("Sent {$sent} task reminder(s).");

            return self::SUCCESS;
        }

        Tenant::accessible()
            ->where('provision_status', 'ready')
            ->orderBy('id')
            ->each(function (Tenant $tenant) use ($connections, &$sent) {
                // A single bad tenant (e.g. marked "ready" but never actually
                // provisioned a database — a real, pre-existing data state
                // found while testing this) must not abort the whole run:
                // every tenant ordered after it in this ->each() would
                // otherwise silently stop getting reminders, forever, on
                // every scheduled run, with zero visibility into why.
                try {
                    $connections->activate($tenant);
                } catch (\Throwable $exception) {
                    report($exception);

                    return;
                }

                TenantContext::set($tenant->id);
                PermissionTeam::set($tenant->id);

                try {
                    $this->sendForActiveTenant($sent);
                    $this->sendDueTodayForActiveTenant($sent, $tenant);
                } finally {
                    TenantContext::clear();
                    PermissionTeam::set(null);
                    $connections->deactivate();
                }
            });

        $this->info("Sent {$sent} task reminder(s).");

        return self::SUCCESS;
    }

    /**
     * The assignee's own chosen "remind at" moment — independent of the due
     * date, may never even be set.
     */
    private function sendForActiveTenant(int &$sent): void
    {
        Task::query()
            ->with(['assignee.tenant'])
            ->whereNull('notification_sent_at')
            ->whereNotNull('remind_at')
            ->where('remind_at', '<=', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('id')
            ->chunkById(100, function ($tasks) use (&$sent) {
                foreach ($tasks as $task) {
                    if (! $task->assignee || $task->assignee->status !== 'Active' || $task->assignee->tenant?->status !== 'Active') {
                        continue;
                    }

                    // The mail channel needs the assignee's own tenant's SMTP
                    // config active — set per task rather than once per
                    // outer tenant loop, since "shared" tenancy mode has no
                    // such loop and calls this without one.
                    app(MailConfigurator::class)->configureFor($task->assignee->tenant);
                    $this->notifyReminder('task_reminder', 'Task reminder: '.$task->title, $task);
                    $task->forceFill(['notification_sent_at' => now()])->save();
                    $sent++;
                }
            });
    }

    /**
     * A second, independent reminder tied to the due date itself — fires
     * once on the calendar day a task is due, whether or not the assignee
     * ever set a remind_at, so nothing due today can silently pass with
     * zero notice. Tracked separately (due_reminder_sent_at) so it never
     * suppresses, or gets suppressed by, the remind_at reminder above.
     */
    private function sendDueTodayForActiveTenant(int &$sent, ?Tenant $tenant): void
    {
        $timezone = $tenant?->timezone ?? config('app.timezone');
        $startOfDay = now($timezone)->startOfDay()->utc();
        $endOfDay = now($timezone)->endOfDay()->utc();

        Task::query()
            ->with(['assignee.tenant'])
            ->whereNull('due_reminder_sent_at')
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$startOfDay, $endOfDay])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('id')
            ->chunkById(100, function ($tasks) use (&$sent) {
                foreach ($tasks as $task) {
                    if (! $task->assignee || $task->assignee->status !== 'Active' || $task->assignee->tenant?->status !== 'Active') {
                        continue;
                    }

                    app(MailConfigurator::class)->configureFor($task->assignee->tenant);
                    $this->notifyReminder('task_due_today', 'Due today: '.$task->title, $task);
                    $task->forceFill(['due_reminder_sent_at' => now()])->save();
                    $sent++;
                }
            });
    }

    /**
     * A broken/unreachable SMTP host (the exact thing that surfaced this
     * while testing locally) throws out of the mail channel — Notification
     * sends channels in via()'s order, 'database' before 'mail', so the
     * in-app row is already committed by the time that happens. Swallowing
     * the exception here (instead of letting EmailLogger::sync's rethrow
     * propagate) means the in-app notification still counts as delivered,
     * the caller still marks *_sent_at so this task isn't retried forever,
     * and one broken tenant's mail no longer aborts every task and tenant
     * still left in the run.
     */
    private function notifyReminder(string $logType, string $subject, Task $task): void
    {
        try {
            app(EmailLogger::class)->sync(
                $logType, $task->assignee->tenant_id, $task->assignee->email, $subject,
                fn () => $task->assignee->notify(new TaskDueReminder($task)),
                ['task_id' => $task->id],
            );
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
