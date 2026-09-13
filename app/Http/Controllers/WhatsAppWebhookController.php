<?php

namespace App\Http\Controllers;

use App\Models\WhatsappAccount;
use App\Services\WhatsApp\MetaCloudApiSender;
use App\Services\WhatsApp\WhatsAppMessageService;
use App\Support\TenantContext;
use App\Tenancy\TenantConnectionManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Public, unauthenticated endpoint for inbound WhatsApp traffic — identified
 * only by the account's opaque `webhook_token`, same shape as
 * WebhookLeadController. Handles both channel types: Meta's official
 * Cloud API webhook payload (messages + delivery/read statuses), and a
 * best-effort generic parser for "unofficial" gateways, whose inbound
 * webhook shape varies by provider.
 */
class WhatsAppWebhookController extends Controller
{
    public function handle(Request $request, string $token, TenantConnectionManager $connections, WhatsAppMessageService $messages, MetaCloudApiSender $metaSender)
    {
        $account = WhatsappAccount::where('webhook_token', $token)->first();
        abort_if(! $account, 404);

        if ($request->isMethod('get')) {
            return $this->verifyHandshake($request, $account);
        }

        if (! $account->is_active) {
            return response()->json(['status' => 'ignored']);
        }

        if ($account->channel_type === 'meta_cloud' && ! $metaSender->signatureIsValid($account, $request->getContent(), $request->header('X-Hub-Signature-256'))) {
            abort(403, 'Invalid signature.');
        }

        $payload = $request->all();

        if (config('tenancy.mode') !== 'shared') {
            $connections->activate($account->tenant);
        }

        try {
            TenantContext::run($account->tenant_id, function () use ($account, $payload, $messages) {
                $account->channel_type === 'meta_cloud'
                    ? $this->handleMetaPayload($account, $payload, $messages)
                    : $this->handleGenericPayload($account, $payload, $messages);
            });

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['status' => 'error'], 500);
        } finally {
            $connections->deactivate();
        }
    }

    private function verifyHandshake(Request $request, WhatsappAccount $account): Response
    {
        if ($request->query('hub_mode') === 'subscribe' || $request->query('hub.mode') === 'subscribe') {
            $sent = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
            $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

            if ($account->verify_token && hash_equals($account->verify_token, (string) $sent)) {
                return response((string) $challenge, 200);
            }

            abort(403, 'Verify token mismatch.');
        }

        return response('This is a WhatsApp webhook endpoint. Configure your provider to POST here.', 200);
    }

    /**
     * Meta's payload nests messages/statuses under entry[].changes[].value —
     * see developers.facebook.com/docs/whatsapp/cloud-api/webhooks.
     */
    private function handleMetaPayload(WhatsappAccount $account, array $payload, WhatsAppMessageService $messages): void
    {
        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];

                $names = [];
                foreach ($value['contacts'] ?? [] as $contact) {
                    $names[$contact['wa_id']] = $contact['profile']['name'] ?? null;
                }

                foreach ($value['messages'] ?? [] as $incoming) {
                    $from = $incoming['from'] ?? null;
                    if (! $from) {
                        continue;
                    }

                    $body = $incoming['text']['body']
                        ?? $incoming['button']['text']
                        ?? $incoming['interactive']['button_reply']['title']
                        ?? null;

                    $messages->recordInbound($account, $from, $names[$from] ?? null, [
                        'type' => $incoming['type'] ?? 'text',
                        'body' => $body,
                    ], $incoming['id'] ?? null);
                }

                foreach ($value['statuses'] ?? [] as $status) {
                    if (! empty($status['id']) && ! empty($status['status'])) {
                        $messages->updateStatus($status['id'], $status['status']);
                    }
                }
            }
        }
    }

    /**
     * No single shape covers every "unofficial" gateway's inbound webhook —
     * this accepts the field names the most common ones actually use
     * (Ultramsg/Green-API-style: `data.from`/`data.body`, or a flat
     * `from`/`body`/`message`/`phone`) so most work without extra
     * configuration; a provider with a very different shape may need a
     * small adjustment here.
     */
    private function handleGenericPayload(WhatsappAccount $account, array $payload, WhatsAppMessageService $messages): void
    {
        $data = $payload['data'] ?? $payload;

        $from = $data['from'] ?? $data['phone'] ?? $data['sender'] ?? $data['number'] ?? null;
        if (! $from) {
            return;
        }

        // A delivery/read status callback rather than an inbound message.
        if (isset($data['status']) && ! isset($data['body']) && ! isset($data['message'])) {
            if (! empty($data['id'])) {
                $messages->updateStatus((string) $data['id'], (string) $data['status']);
            }

            return;
        }

        $body = $data['body'] ?? $data['message'] ?? $data['text'] ?? null;
        $name = $data['pushname'] ?? $data['name'] ?? null;

        $messages->recordInbound($account, (string) $from, $name, [
            'type' => 'text',
            'body' => $body,
        ], isset($data['id']) ? (string) $data['id'] : null);
    }
}
