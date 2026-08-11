<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskDueReminder extends Notification
{
    use Queueable;

    public function __construct(private readonly Task $task)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tenant_id' => $this->task->tenant_id,
            'task_id' => $this->task->id,
            'title' => 'Task reminder',
            'message' => $this->task->title,
            'due_at' => $this->task->due_at?->toIso8601String(),
            'url' => route('tasks.index', ['task' => $this->task->id]),
        ];
    }
}
