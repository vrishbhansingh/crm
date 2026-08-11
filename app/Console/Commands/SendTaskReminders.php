<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDueReminder;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    protected $signature = 'crm:send-task-reminders';

    protected $description = 'Send due in-app reminders for assigned CRM tasks';

    public function handle(): int
    {
        $sent = 0;

        Task::withoutGlobalScopes()
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

        $this->info("Sent {$sent} task reminder(s).");

        return self::SUCCESS;
    }
}
