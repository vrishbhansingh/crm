<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Models\WhatsappConversation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/** In-app alert: the AI WhatsApp assistant has passed a chat to a person. */
class WhatsAppHandoff extends Notification
{
    use Queueable;

    public function __construct(private readonly Lead $lead, private readonly WhatsappConversation $conversation, private readonly string $reason)
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
            'conversation_id' => $this->conversation->id,
            'title' => 'WhatsApp chat needs a person',
            'message' => $this->lead->name.': '.$this->reason,
            'url' => route('whatsapp.chat'),
        ];
    }
}
