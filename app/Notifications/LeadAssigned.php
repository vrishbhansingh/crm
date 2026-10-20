<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Instant in-app alert for the person a lead lands on — speed-to-lead only
 * works if the assignee actually finds out. Skipped when someone assigns a
 * lead to themselves (nothing to be told).
 */
class LeadAssigned extends Notification
{
    use Queueable;

    public function __construct(private readonly Lead $lead, private readonly string $how = 'assigned')
    {
    }

    /**
     * Never lets a notification failure break the assignment itself.
     */
    public static function notifyAssignee(Lead $lead, ?int $actorId = null, string $how = 'assigned'): void
    {
        if (! $lead->assigned_to || (int) $lead->assigned_to === (int) $actorId) {
            return;
        }

        try {
            $user = User::where('status', 'Active')->find($lead->assigned_to);
            $user?->notify(new self($lead, $how));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $detail = collect([$this->lead->company_name, $this->lead->product, $this->lead->lead_source])->filter()->implode(' · ');

        return [
            'tenant_id' => $this->lead->tenant_id,
            'lead_id' => $this->lead->id,
            'title' => $this->how === 'auto' ? 'New lead auto-assigned to you' : 'Lead assigned to you',
            'message' => $this->lead->name.($detail ? ' — '.$detail : ''),
            'url' => route('leads.show', $this->lead->id),
        ];
    }
}
