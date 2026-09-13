<?php

namespace App\Services\WhatsApp;

use App\Models\Contact;
use App\Models\Lead;
use App\Models\WhatsappAccount;
use App\Models\WhatsappAccountLog;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;

/**
 * Everything involved in actually moving a WhatsApp message: finding or
 * creating the conversation it belongs to, persisting it, handing it to the
 * right channel sender, and recording the outcome. Used by the chat box
 * (agent replies), campaigns, and — later — automation, so all three share
 * one message history per contact instead of drifting into separate
 * per-feature logs.
 */
class WhatsAppMessageService
{
    public function senderFor(WhatsappAccount $account): WhatsAppSenderInterface
    {
        return $account->channel_type === 'meta_cloud'
            ? app(MetaCloudApiSender::class)
            : app(UnofficialGatewaySender::class);
    }

    /**
     * Must be called with the correct tenant DB connection already active
     * (TenantContext set) — same requirement as every other tenant-scoped
     * model write in the app.
     */
    public function findOrCreateConversation(WhatsappAccount $account, string $waPhone, ?string $waName = null): WhatsappConversation
    {
        $normalized = $this->normalizePhone($waPhone);

        $conversation = WhatsappConversation::where('whatsapp_account_id', $account->id)
            ->where('wa_phone', $normalized)
            ->first();

        if ($conversation) {
            if ($waName && ! $conversation->wa_name) {
                $conversation->update(['wa_name' => $waName]);
            }

            return $conversation;
        }

        [$leadId, $contactId, $resolvedName] = $this->matchExistingRecord($normalized);

        return WhatsappConversation::create([
            'whatsapp_account_id' => $account->id,
            'wa_phone' => $normalized,
            'wa_name' => $waName ?: $resolvedName,
            'lead_id' => $leadId,
            'contact_id' => $contactId,
        ]);
    }

    /**
     * Send a message in an existing conversation: persists it as 'queued',
     * calls the channel sender, then updates status/counters. Returns the
     * saved message either way — check ->status to see if it actually went
     * out.
     */
    public function sendMessage(WhatsappAccount $account, WhatsappConversation $conversation, array $message, string $source = 'agent', ?int $sentBy = null): WhatsappMessage
    {
        $record = WhatsappMessage::create([
            'whatsapp_conversation_id' => $conversation->id,
            'direction' => 'out',
            'type' => $message['type'] ?? 'text',
            'body' => $message['body'] ?? null,
            'template_name' => $message['template_name'] ?? null,
            'template_params' => $message['template_params'] ?? null,
            'status' => 'queued',
            'source' => $source,
            'sent_by' => $sentBy,
        ]);

        $result = $this->senderFor($account)->send($account, $conversation->wa_phone, $message);

        $record->update([
            'status' => $result['success'] ? 'sent' : 'failed',
            'wa_message_id' => $result['wa_message_id'],
            'error_message' => $result['error'],
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'last_message_preview' => \Illuminate\Support\Str::limit($message['body'] ?? ('Template: '.($message['template_name'] ?? '')), 120),
        ]);

        $account->increment('messages_sent_count');
        $account->update(['last_used_at' => now()]);

        WhatsappAccountLog::create([
            'whatsapp_account_id' => $account->id,
            'tenant_id' => $account->tenant_id,
            'direction' => 'outbound',
            'status' => $result['success'] ? 'sent' : 'failed',
            'external_ref' => $result['wa_message_id'],
            'message' => $result['success'] ? 'Message sent to '.$conversation->wa_phone : $result['error'],
            'payload' => $message,
            'created_at' => now(),
        ]);

        return $record;
    }

    /**
     * Persist an inbound message from the webhook. Must run inside
     * TenantContext::run() — there's no authenticated user in that flow.
     */
    public function recordInbound(WhatsappAccount $account, string $waPhone, ?string $waName, array $message, ?string $waMessageId): WhatsappMessage
    {
        $conversation = $this->findOrCreateConversation($account, $waPhone, $waName);

        $record = WhatsappMessage::create([
            'whatsapp_conversation_id' => $conversation->id,
            'direction' => 'in',
            'type' => $message['type'] ?? 'text',
            'body' => $message['body'] ?? null,
            'media_url' => $message['media_url'] ?? null,
            'status' => 'received',
            'wa_message_id' => $waMessageId,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'last_message_preview' => \Illuminate\Support\Str::limit($message['body'] ?? 'Media message', 120),
            'unread_count' => $conversation->unread_count + 1,
            // Every inbound message reopens/extends the 24-hour free-form
            // reply window for the official Meta channel.
            'window_expires_at' => $account->channel_type === 'meta_cloud' ? now()->addHours(24) : null,
        ]);

        $account->increment('messages_received_count');

        return $record;
    }

    /**
     * Delivery/read status callbacks from the channel — matched by the
     * wa_message_id the send call originally got back.
     */
    public function updateStatus(string $waMessageId, string $status): void
    {
        WhatsappMessage::where('wa_message_id', $waMessageId)->update(['status' => $status]);
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone) ?: $phone;
    }

    /**
     * Best-effort link to an existing Lead/Contact by phone so the chat box
     * can show a real name instead of a bare number — matched on the last
     * 10 digits to tolerate country-code/formatting differences.
     *
     * @return array{0: ?int, 1: ?int, 2: ?string}
     */
    private function matchExistingRecord(string $normalized): array
    {
        $last10 = substr($normalized, -10);

        $lead = Lead::where('phone', 'like', "%{$last10}")->first();
        if ($lead) {
            return [$lead->id, $lead->contact_id, $lead->name];
        }

        $contact = Contact::where('phone', 'like', "%{$last10}")->first();
        if ($contact) {
            return [null, $contact->id, $contact->name];
        }

        return [null, null, null];
    }
}
