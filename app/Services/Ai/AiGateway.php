<?php

namespace App\Services\Ai;

use App\Models\AiConfiguration;
use App\Services\Ai\Drivers\AiDriver;
use App\Services\Ai\Drivers\AnthropicDriver;
use App\Services\Ai\Drivers\OpenAiCompatibleDriver;
use App\Support\TenantContext;
use Psr\Http\Client\ClientInterface;

/**
 * The one AI client the app uses. It calls the company's saved configurations
 * in priority order, each through the right driver for its provider (Claude,
 * OpenAI, Gemini, Groq, ...). If a key is the problem (invalid/expired, out of
 * credit, rate limited, wrong model) it is marked failing, its admins are
 * alerted, and the next configuration is tried — automatically, even if that
 * one belongs to a different provider. Anything else (network, server error)
 * is thrown straight away: another key would fail the same way.
 */
class AiGateway implements AiClient
{
    /** Sent as $model to mean "the cheap topic-check model of whichever provider answers". */
    public const GUARD_MODEL = '@guard';

    /** @param  ClientInterface|null  $transporter  Test seam: a PSR-18 client (e.g. Guzzle with a MockHandler). */
    public function __construct(private readonly ?ClientInterface $transporter = null)
    {
    }

    public function send(string $system, array $messages, array $tools, ?array $jsonSchema = null, ?string $model = null): array
    {
        $resolver = app(AiConfigResolver::class);
        $candidates = $resolver->candidates(TenantContext::id());
        $last = null;

        foreach ($candidates as $i => $candidate) {
            try {
                $result = $this->driverFor($candidate['provider'])->call(
                    $candidate['key'], $this->modelFor($candidate, $model), $system, $messages, $tools, $jsonSchema,
                );
                $resolver->markSuccess($candidate['config'], $result['usage']['input'] ?? 0, $result['usage']['output'] ?? 0);

                return $result;
            } catch (\Throwable $e) {
                if (! AiErrors::isKeyProblem($e)) {
                    throw $e;
                }

                $last = $e;
                if ($candidate['config']) {
                    $resolver->markFailed($candidate['config'], $e, stillWorking: $i < count($candidates) - 1);
                }
            }
        }

        throw $last ?? new \RuntimeException('No AI configuration is available.');
    }

    /**
     * The Test button: one tiny real call with exactly this configuration (no
     * failover), recording the outcome on it.
     *
     * @return array{ok: bool, message: string}
     */
    public function ping(AiConfiguration $config): array
    {
        $provider = $config->provider ?: 'anthropic';
        $model = $config->model ?: ($provider === 'anthropic' ? (string) config('ai.model') : AiProviders::defaultModel($provider));

        try {
            $result = $this->driverFor($provider)->call((string) $config->api_key, $model, 'Reply with the single word OK.', [['role' => 'user', 'content' => 'ping']], [], null);
            $config->forceFill(['status' => 'ok', 'last_error' => null, 'last_tested_at' => now(), 'failed_at' => null, 'notified_at' => null])->save();
            app(AiConfigResolver::class)->recordUsage($config, $result['usage']['input'] ?? 0, $result['usage']['output'] ?? 0);

            return ['ok' => true, 'message' => 'Working. The key was accepted.'];
        } catch (\Throwable $e) {
            $reason = AiErrors::reason($e);
            $config->forceFill(['status' => 'failing', 'last_error' => $reason, 'last_tested_at' => now(), 'failed_at' => now()])->save();

            return ['ok' => false, 'message' => $reason];
        }
    }

    private function modelFor(array $candidate, ?string $override): string
    {
        if ($override === self::GUARD_MODEL) {
            return AiProviders::guardModel($candidate['provider'], (string) config('ai.guard_model')) ?: $candidate['model'];
        }

        return $override ?: $candidate['model'];
    }

    private function driverFor(string $provider): AiDriver
    {
        $info = AiProviders::get($provider) ?? AiProviders::get('anthropic');
        $http = $this->transporter ?? $this->defaultTransporter();

        return ($info['driver'] ?? 'anthropic') === 'anthropic'
            ? new AnthropicDriver($http)
            : new OpenAiCompatibleDriver($http, $info, $provider);
    }

    /**
     * Our own HTTP client so a slow or unreachable API fails in seconds
     * instead of hanging a chat for the SDK's 10-minute default, and so
     * HTTPS works on a WAMP PHP that ships without a CA bundle configured
     * (cURL error 60). Verification stays ON: when PHP has no bundle of
     * its own, we point at the Mozilla bundle WAMP already installs.
     */
    private function defaultTransporter(): \GuzzleHttp\Client
    {
        $verify = config('ai.ca_bundle') ?: (ini_get('curl.cainfo') || ini_get('openssl.cafile') ? true : $this->wampCaBundle() ?? true);

        return new \GuzzleHttp\Client([
            'timeout' => (float) config('ai.timeout'),
            'connect_timeout' => 10,
            'verify' => $verify,
        ]);
    }

    private function wampCaBundle(): ?string
    {
        $bundles = glob(dirname(base_path(), 2).'/bin/php/*/extras/ssl/cacert.pem') ?: [];
        sort($bundles);

        return $bundles ? end($bundles) : null;
    }
}
