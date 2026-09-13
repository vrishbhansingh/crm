<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsappAccount;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Sends via the official WhatsApp Business Cloud API (developers.facebook.com/
 * docs/whatsapp/cloud-api). Free-form text only works inside the 24-hour
 * customer-service window after the contact's last inbound message; outside
 * it, Meta rejects anything but an approved template — the caller decides
 * which to send, this class just shapes the request either way.
 */
class MetaCloudApiSender implements WhatsAppSenderInterface
{
    private const API_VERSION = 'v20.0';

    public function send(WhatsappAccount $account, string $toPhone, array $message): array
    {
        $phoneNumberId = $account->credential('phone_number_id');
        $accessToken = $account->credential('access_token');

        if (! $phoneNumberId || ! $accessToken) {
            return ['success' => false, 'wa_message_id' => null, 'error' => 'This account is missing its Meta phone number ID or access token.'];
        }

        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => ltrim($toPhone, '+'),
        ];

        if (($message['type'] ?? 'text') === 'template' && ! empty($message['template_name'])) {
            $components = [];
            $params = $message['template_params'] ?? [];
            if (! empty($params)) {
                $components[] = [
                    'type' => 'body',
                    'parameters' => array_map(fn ($value) => ['type' => 'text', 'text' => (string) $value], array_values($params)),
                ];
            }

            $body['type'] = 'template';
            $body['template'] = array_filter([
                'name' => $message['template_name'],
                'language' => ['code' => $message['template_language'] ?? 'en_US'],
                'components' => $components,
            ]);
        } else {
            $body['type'] = 'text';
            $body['text'] = ['body' => $message['body'] ?? ''];
        }

        try {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(15)
                ->post("https://graph.facebook.com/".self::API_VERSION."/{$phoneNumberId}/messages", $body);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'wa_message_id' => $response->json('messages.0.id'),
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'wa_message_id' => null,
                'error' => $response->json('error.message') ?? "Meta API returned HTTP {$response->status()}",
            ];
        } catch (Throwable $exception) {
            report($exception);

            return ['success' => false, 'wa_message_id' => null, 'error' => $exception->getMessage()];
        }
    }

    /**
     * Verify the HMAC-SHA256 signature Meta sends in `X-Hub-Signature-256`
     * over the raw request body, using the app secret — same scheme
     * already used for the lead-capture webhook.
     */
    public function signatureIsValid(WhatsappAccount $account, string $rawBody, ?string $signatureHeader): bool
    {
        $appSecret = $account->credential('app_secret');

        if (! $appSecret) {
            return true; // no secret configured — signature check skipped, matches lead webhook behavior
        }

        if (! $signatureHeader || ! str_starts_with($signatureHeader, 'sha256=')) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $appSecret);

        return hash_equals($expected, substr($signatureHeader, 7));
    }
}
