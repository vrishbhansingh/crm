<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDueReminder extends Notification
{
    use Queueable;

    public function __construct(private readonly Task $task)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $typeLabel = match ($this->task->activity_type) {
            'call' => 'call',
            'meeting' => 'meeting',
            default => 'task',
        };

        return (new MailMessage)
            ->subject('Reminder: '.$this->task->title)
            ->greeting('Hi '.$notifiable->name.',')
            ->line("You have a {$typeLabel} due".($this->task->due_at ? ' on '.$this->task->due_at->format('d M Y, h:i A') : '').'.')
            ->line($this->task->title)
            ->when($this->task->description, fn ($mail) => $mail->line($this->task->description))
            ->action('View task', route('tasks.index', ['task' => $this->task->id]))
            ->line('This is an automated reminder from your CRM.');
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
