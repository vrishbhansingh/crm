<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Domain-locked license enforcement.
 *
 * On a schedule (cached) this phones home to the license server, verifies the
 * SIGNED verdict with the embedded public key, and locks the app if the domain
 * is not licensed, the license is suspended/expired, or it stays unreachable
 * past the grace period. A copied installation on a new domain will fail the
 * domain check and lock.
 */
class LicenseCheck
{
    public function handle(Request $request, Closure $next)
    {
        $cfg = config('license');

        // Disabled (local dev / not yet provisioned) — do nothing.
        if (! ($cfg['enabled'] ?? false)) {
            return $next($request);
        }

        $host = $request->getHost();

        // Local/dev hosts always allowed.
        if (in_array($host, $cfg['bypass_hosts'] ?? [], true)) {
            return $next($request);
        }

        $now = time();
        $state = Cache::get('license_state'); // ['status','recheck_at','last_ok']

        // Fresh "valid" verdict still cached — allow without any network call.
        if ($state && $state['status'] === 'valid' && $now < $state['recheck_at']) {
            return $next($request);
        }

        $result = $this->verify($cfg, $host, $now);

        if ($result === 'valid') {
            Cache::put('license_state', [
                'status'     => 'valid',
                'recheck_at' => $now + $cfg['recheck_seconds'],
                'last_ok'    => $now,
            ], $cfg['grace_seconds'] + 86400);

            return $next($request);
        }

        if ($result === 'invalid') {
            // Server explicitly said not licensed / suspended / wrong domain — lock now.
            Cache::put('license_state', [
                'status'     => 'invalid',
                'recheck_at' => $now + 300,
                'last_ok'    => $state['last_ok'] ?? 0,
            ], $cfg['grace_seconds'] + 86400);

            return $this->lock();
        }

        // result === 'unreachable' — fall back to grace period from last success.
        $lastOk = $state['last_ok'] ?? 0;
        if ($lastOk && ($now - $lastOk) < $cfg['grace_seconds']) {
            return $next($request);
        }

        return $this->lock();
    }

    /**
     * @return string 'valid' | 'invalid' | 'unreachable'
     */
    private function verify(array $cfg, string $host, int $now): string
    {
        if (empty($cfg['server_url']) || empty($cfg['key'])) {
            return 'unreachable';
        }

        $nonce = Str::random(32);
        $fingerprint = hash('sha256', gethostname() . '|' . PHP_OS . '|' . base_path());

        try {
            $resp = Http::timeout($cfg['timeout'])->acceptJson()->post($cfg['server_url'], [
                'license_key' => $cfg['key'],
                'domain'      => $host,
                'fingerprint' => $fingerprint,
                'nonce'       => $nonce,
            ]);
        } catch (\Throwable $e) {
            return 'unreachable';
        }

        if (! $resp->ok()) {
            return 'unreachable';
        }

        $data = $resp->json();
        if (! is_array($data) || empty($data['signature'])) {
            return 'unreachable';
        }

        // Rebuild the exact canonical string the server signed.
        $canonical = implode('|', [
            $data['license_key'] ?? '',
            $data['domain'] ?? '',
            $data['status'] ?? '',
            $data['company'] ?? '',
            $data['expires_at'] ?? '',
            $data['nonce'] ?? '',
            $data['issued_at'] ?? '',
        ]);

        $sigOk = openssl_verify(
            $canonical,
            base64_decode($data['signature']),
            $cfg['public_key'],
            OPENSSL_ALGO_SHA256
        ) === 1;

        // Bad/forged signature — do not trust it.
        if (! $sigOk) {
            return 'unreachable';
        }

        // Anti-replay: the nonce must match ours, and the verdict must be fresh.
        if (($data['nonce'] ?? '') !== $nonce) {
            return 'unreachable';
        }
        if (abs($now - (int) ($data['issued_at'] ?? 0)) > 600) {
            return 'unreachable';
        }

        // Domain lock: the verdict must be for THIS host.
        if (($data['domain'] ?? '') !== $host) {
            return 'invalid';
        }

        // Expiry.
        if (! empty($data['expires_at']) && strtotime($data['expires_at']) < $now) {
            return 'invalid';
        }

        return ($data['status'] ?? '') === 'active' ? 'valid' : 'invalid';
    }

    private function lock()
    {
        return response()->view('license.invalid', [], 403);
    }
}
