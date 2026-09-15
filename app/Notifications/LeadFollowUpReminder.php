<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeadFollowUpReminder extends Notification
{
    use Queueable;

    public function __construct(private readonly Lead $lead)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tenant_id' => $this->lead->tenant_id,
            'lead_id' => $this->lead->id,
            'title' => 'Follow-up reminder',
            'message' => 'Follow up with '.$this->lead->name.($this->lead->company_name ? ' ('.$this->lead->company_name.')' : ''),
            'follow_up_at' => $this->lead->follow_up_date,
            'url' => route('leads.show', $this->lead->id),
        ];
    }
}
