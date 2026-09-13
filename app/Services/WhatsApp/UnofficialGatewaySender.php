<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsappAccount;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Sends through a generic third-party WhatsApp gateway — the kind that
 * pairs a plain API key with a sender number rather than Meta's official
 * onboarding (Ultramsg, Green-API, WasenderAPI, and similar all share
 * roughly this REST shape: POST to a per-account URL, API key as either a
 * query param or header, `to` + `body` fields for the message). No 24-hour
 * window restriction applies here since it doesn't go through Meta.
 */
class UnofficialGatewaySender implements WhatsAppSenderInterface
{
    public function send(WhatsappAccount $account, string $toPhone, array $message): array
    {
        $apiUrl = $account->credential('api_url');
        $apiKey = $account->credential('api_key');

        if (! $apiUrl) {
            return ['success' => false, 'wa_message_id' => null, 'error' => 'This account has no gateway API URL configured.'];
        }

        $text = $message['type'] === 'template'
            ? $this->renderTemplateAsText($message)
            : (string) ($message['body'] ?? '');

        $payload = array_merge([
            'to' => $toPhone,
            'body' => $text,
            'number' => $account->phone_number,
        ], (array) $account->credential('extra_params', []));

        $keyLocation = $account->credential('api_key_location', 'query'); // 'query' | 'header' | 'body'
        $keyParam = $account->credential('api_key_param', 'token');
        $method = strtolower($account->credential('http_method', 'post'));

        $request = Http::acceptJson()->timeout(15);

        if ($apiKey && $keyLocation === 'header') {
            $request = $request->withHeaders([$keyParam ?: 'Authorization' => $apiKey]);
        } elseif ($apiKey && $keyLocation === 'body') {
            $payload[$keyParam ?: 'token'] = $apiKey;
        }

        $url = $apiUrl;
        if ($apiKey && $keyLocation === 'query') {
            $url .= (str_contains($apiUrl, '?') ? '&' : '?').($keyParam ?: 'token').'='.urlencode($apiKey);
        }

        try {
            $response = $method === 'get' ? $request->get($url, $payload) : $request->post($url, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'wa_message_id' => $this->extractMessageId($response->json()),
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'wa_message_id' => null,
                'error' => $response->json('error') ?? $response->json('message') ?? "Gateway returned HTTP {$response->status()}",
            ];
        } catch (Throwable $exception) {
            report($exception);

            return ['success' => false, 'wa_message_id' => null, 'error' => $exception->getMessage()];
        }
    }

    private function renderTemplateAsText(array $message): string
    {
        $text = $message['body'] ?? ($message['template_name'] ?? '');

        foreach ((array) ($message['template_params'] ?? []) as $index => $value) {
            $text = str_replace('{{'.($index + 1).'}}', (string) $value, $text);
        }

        return $text;
    }

    private function extractMessageId(?array $json): ?string
    {
        if (! $json) {
            return null;
        }

        return $json['id']
            ?? $json['message_id']
            ?? $json['data']['id']
            ?? $json['key']['id']
            ?? null;
    }
}
