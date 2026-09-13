<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsappAccount;

interface WhatsAppSenderInterface
{
    /**
     * Send one message through the given account's channel.
     *
     * $message: ['type' => 'text'|'template', 'body' => ?string,
     *            'template_name' => ?string, 'template_params' => ?array]
     *
     * @return array{success: bool, wa_message_id: ?string, error: ?string}
     */
    public function send(WhatsappAccount $account, string $toPhone, array $message): array;
}
