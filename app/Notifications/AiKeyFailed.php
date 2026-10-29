<?php

namespace App\Notifications;

use App\Models\AiConfiguration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when one of a company's AI keys stops working (expired, no credit,
 * rate limited). In-app for the company's admins, and by email to the
 * address saved on the configuration. The assistant keeps working if
 * another configuration is available.
 */
class AiKeyFailed extends Notification
{
    use Queueable;

    public function __construct(private readonly AiConfiguration $config, private readonly string $reason, private readonly bool $stillWorking)
    {
    }

    public function via(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable ? ['mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('AI key needs attention: '.$this->config->name)
            ->line('The AI configuration "'.$this->config->name.'" stopped working.')
            ->line('Reason: '.$this->reason)
            ->line($this->stillWorking ? 'The assistant switched to another saved configuration automatically, so nothing is broken yet.' : 'No other working configuration is saved, so the assistant is unavailable until this is fixed.')
            ->action('Open AI settings', route('ai.settings'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tenant_id' => $this->config->tenant_id,
            'title' => 'AI key needs attention',
            'message' => '"'.$this->config->name.'": '.$this->reason.($this->stillWorking ? ' (switched to another key)' : ' (assistant is unavailable)'),
            'url' => route('ai.settings'),
        ];
    }
}
