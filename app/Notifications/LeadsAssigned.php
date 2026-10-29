<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * ONE in-app alert for a bulk assignment ("250 leads assigned to you"),
 * instead of one notification per lead — assigning a thousand leads must
 * not bury the assignee under a thousand rows. A single lead still links
 * straight to it.
 */
class LeadsAssigned extends Notification
{
    use Queueable;

    public function __construct(private readonly int $count, private readonly ?Lead $single = null)
    {
    }

    public static function notifyAssignee(User $assignee, int $count, ?int $actorId = null, ?Lead $single = null): void
    {
        if ($count < 1 || (int) $assignee->id === (int) $actorId) {
            return;
        }

        try {
            $assignee->notify(new self($count, $single));
        } catch (\Throwable $exception) {
            report($exception); // an alert must never undo the assignment
        }
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tenant_id' => $notifiable->tenant_id,
            'lead_id' => $this->single?->id,
            'title' => $this->count === 1 ? 'Lead assigned to you' : number_format($this->count).' leads assigned to you',
            'message' => $this->single ? $this->single->name.' was assigned to you.' : 'Open your leads list to see them.',
            'url' => $this->single ? route('leads.show', $this->single->id) : route('leads.index'),
        ];
    }
}
