<?php

namespace App\Jobs;

use App\Models\WhatsappAccount;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\WhatsApp\WhatsAppBot;
use App\Support\TenantContext;
use App\Tenancy\TenantConnectionManager;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Runs the WhatsApp bot for one inbound message. Dispatched with
 * dispatchAfterResponse(): the webhook has already answered the provider
 * (so slow AI calls never cause provider retries) and no queue worker is
 * needed. By then the webhook has torn down the tenant connection, so this
 * re-activates it itself. Never throws: a failure here must not surface in
 * the webhook response.
 */
class RunWhatsAppBot
{
    use Dispatchable;

    public function __construct(public readonly int $accountId, public readonly int $conversationId, public readonly int $messageId)
    {
    }

    public function handle(TenantConnectionManager $connections, WhatsAppBot $bot): void
    {
        $activated = false;

        try {
            $account = WhatsappAccount::find($this->accountId);
            if (! $account || ! $account->ai_bot_enabled) {
                return;
            }

            if (config('tenancy.mode') !== 'shared') {
                $connections->activate($account->tenant);
                $activated = true;
            }

            TenantContext::run($account->tenant_id, function () use ($account, $bot) {
                $conversation = WhatsappConversation::find($this->conversationId);
                $message = WhatsappMessage::find($this->messageId);
                if ($conversation && $message) {
                    $bot->respond($account, $conversation, $message);
                }
            });
        } catch (\Throwable $exception) {
            report($exception);
        } finally {
            if ($activated) {
                $connections->deactivate();
            }
        }
    }
}
