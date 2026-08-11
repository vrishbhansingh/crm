<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\Tenant;
use App\Notifications\TaskDueReminder;
use App\Support\TenantContext;
use App\Tenancy\TenantConnectionManager;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    protected $signature = 'crm:send-task-reminders';

    protected $description = 'Send due in-app reminders for assigned CRM tasks';

    public function handle(TenantConnectionManager $connections): int
    {
        $sent = 0;

        if (config('tenancy.mode') === 'shared') {
            $this->sendForActiveTenant($sent);
            $this->info("Sent {$sent} task reminder(s).");

            return self::SUCCESS;
        }

        Tenant::where('status', 'Active')
            ->where('approval_status', 'approved')
            ->where('provision_status', 'ready')
            ->orderBy('id')
            ->each(function (Tenant $tenant) use ($connections, &$sent) {
                $connections->activate($tenant);
                TenantContext::set($tenant->id);

                try {
                    $this->sendForActiveTenant($sent);
                } finally {
                    TenantContext::clear();
                    $connections->deactivate();
                }
            });

        $this->info("Sent {$sent} task reminder(s).");

        return self::SUCCESS;
    }

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

                    $task->assignee->notify(new TaskDueReminder($task));
                    $task->forceFill(['notification_sent_at' => now()])->save();
                    $sent++;
                }
            });
    }
}
