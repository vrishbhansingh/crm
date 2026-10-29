<?php

namespace App\Services;

/**
 * Open/click tracking for campaign emails.
 *
 * Every tracked URL carries a signed token (HMAC of the payload with the
 * app key): the tenant and recipient it belongs to, and for clicks the exact
 * destination. Nothing in it can be edited — so a click link can only ever
 * redirect to the URL that was really in the email (no open-redirect abuse),
 * and a forged pixel can't write into another company's data.
 *
 * Opens are approximate by nature: mail apps that pre-load images (Apple
 * Mail Privacy Protection, Gmail's image proxy) can count an open the person
 * never made, and people who block images are never counted. Clicks are the
 * reliable signal.
 */
class CampaignTracker
{
    /** Rewrites http(s) links in the HTML and appends a 1x1 open pixel. */
    public function instrument(string $html, ?int $tenantId, int $recipientRowId): string
    {
        $tenantId = (int) $tenantId;

        $html = preg_replace_callback(
            '/(<a\b[^>]*?\shref\s*=\s*)(["\'])(https?:\/\/[^"\']+)\2/i',
            fn (array $m) => $m[1].$m[2].$this->attr($this->clickUrl($tenantId, $recipientRowId, html_entity_decode($m[3]))).$m[2],
            $html,
        ) ?? $html;

        $pixel = '<img src="'.$this->attr($this->openUrl($tenantId, $recipientRowId)).'" width="1" height="1" alt="" style="display:none;border:0;width:1px;height:1px;">';

        return stripos($html, '</body>') !== false
            ? preg_replace('/<\/body>/i', $pixel.'</body>', $html, 1)
            : $html.$pixel;
    }

    public function openUrl(int $tenantId, int $recipientRowId): string
    {
        return route('tracking.open', ['token' => $this->seal(['k' => 'o', 't' => $tenantId, 'r' => $recipientRowId])]);
    }

    public function clickUrl(int $tenantId, int $recipientRowId, string $destination): string
    {
        return route('tracking.click', ['token' => $this->seal(['k' => 'c', 't' => $tenantId, 'r' => $recipientRowId, 'u' => $destination])]);
    }

    /** @return array{k: string, t: int, r: int, u?: string}|null null when the token is malformed or not ours */
    public function open(string $token): ?array
    {
        [$payload, $signature] = array_pad(explode('.', $token, 2), 2, '');

        if ($payload === '' || ! hash_equals($this->sign($payload), $signature)) {
            return null;
        }

        $data = json_decode((string) base64_decode(strtr($payload, '-_', '+/')), true);

        if (! is_array($data) || ! isset($data['k'], $data['t'], $data['r'])) {
            return null;
        }

        if (($data['k'] ?? null) === 'c' && ! preg_match('#^https?://#i', (string) ($data['u'] ?? ''))) {
            return null;
        }

        return ['k' => (string) $data['k'], 't' => (int) $data['t'], 'r' => (int) $data['r']] + (isset($data['u']) ? ['u' => (string) $data['u']] : []);
    }

    private function attr(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function seal(array $data): string
    {
        $payload = rtrim(strtr(base64_encode(json_encode($data, JSON_UNESCAPED_SLASHES)), '+/', '-_'), '=');

        return $payload.'.'.$this->sign($payload);
    }

    private function sign(string $payload): string
    {
        return substr(hash_hmac('sha256', $payload, (string) config('app.key')), 0, 24);
    }
}
