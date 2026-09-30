<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
        private readonly ?User $assignedBy = null,
        private readonly int $extraCount = 0,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $by = $this->assignedBy?->name ?? 'Someone';
        $typeLabel = match ($this->task->activity_type) {
            'call' => 'call',
            'meeting' => 'meeting',
            default => 'task',
        };

        return (new MailMessage)
            ->subject('New task assigned: '.$this->task->title)
            ->greeting('Hi '.$notifiable->name.',')
            ->line("{$by} assigned you a {$typeLabel}".($this->extraCount > 0 ? " (plus {$this->extraCount} more)" : '').'.')
            ->line($this->task->title)
            ->when($this->task->due_at, fn ($mail) => $mail->line('Due: '.$this->task->due_at->format('d M Y, h:i A')))
            ->action('View task', route('tasks.index', ['task' => $this->task->id]))
            ->line('This is an automated notification from your CRM.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tenant_id' => $this->task->tenant_id,
            'task_id' => $this->task->id,
            'title' => 'Task assigned',
            'message' => $this->extraCount > 0
                ? "{$this->task->title} (plus {$this->extraCount} more)"
                : $this->task->title,
            'assigned_by' => $this->assignedBy?->name,
            'due_at' => $this->task->due_at?->toIso8601String(),
            'url' => route('tasks.index', ['task' => $this->task->id]),
        ];
    }
}
