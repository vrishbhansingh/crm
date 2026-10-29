<?php

namespace App\Services\Ai;

use Anthropic\Client;
use Anthropic\Messages\ToolUseBlock;
use App\Models\AiConfiguration;
use App\Support\TenantContext;
use Psr\Http\Client\ClientInterface;

class AnthropicAiClient implements AiClient
{
    /** @param  ClientInterface|null  $transporter  Test seam: a PSR-18 client (e.g. Guzzle with a MockHandler). */
    public function __construct(private readonly ?ClientInterface $transporter = null)
    {
    }

    /**
     * Calls the model with the company's saved keys in priority order. If a
     * key is the problem (invalid/expired, out of credit, rate limited) it is
     * marked failing, its admins are alerted, and the next key is tried —
     * automatically, within the same request. Anything else (network,
     * server error) is thrown straight away: another key would fail the same.
     */
    public function send(string $system, array $messages, array $tools, ?array $jsonSchema = null, ?string $model = null): array
    {
        $resolver = app(AiConfigResolver::class);
        $candidates = $resolver->candidates(TenantContext::id());
        $last = null;

        foreach ($candidates as $i => $candidate) {
            try {
                $result = $this->call($candidate['key'], $model ?: $candidate['model'], $system, $messages, $tools, $jsonSchema);
                $resolver->markSuccess($candidate['config']);

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
     * The Test button: one tiny call with exactly this configuration (no
     * failover), recording the outcome on it.
     *
     * @return array{ok: bool, message: string}
     */
    public function ping(AiConfiguration $config): array
    {
        try {
            $this->call((string) $config->api_key, $config->model ?: (string) config('ai.model'), 'Reply with the single word OK.', [['role' => 'user', 'content' => 'ping']], [], null);
            $config->forceFill(['status' => 'ok', 'last_error' => null, 'last_tested_at' => now(), 'failed_at' => null, 'notified_at' => null])->save();

            return ['ok' => true, 'message' => 'Working. The key was accepted.'];
        } catch (\Throwable $e) {
            $reason = AiErrors::reason($e);
            $config->forceFill(['status' => 'failing', 'last_error' => $reason, 'last_tested_at' => now(), 'failed_at' => now()])->save();

            return ['ok' => false, 'message' => $reason];
        }
    }

    private function call(string $key, string $model, string $system, array $messages, array $tools, ?array $jsonSchema): array
    {
        $client = new Client(apiKey: $key, requestOptions: [
            'transporter' => $this->transporter ?? $this->defaultTransporter(),
            'timeout' => (float) config('ai.timeout'),
            'maxRetries' => 1,
        ]);

        $args = [
            'model' => $model,
            'maxTokens' => config('ai.max_tokens'),
            'system' => [['type' => 'text', 'text' => $system]],
            'messages' => $messages,
            'outputConfig' => ['effort' => config('ai.effort')],
        ];
        if ($jsonSchema) {
            $args['outputConfig']['format'] = ['type' => 'json_schema', 'schema' => $jsonSchema];
        }
        if ($tools) { // tool-less calls (summaries, drafts, checks) must not send an empty list
            $args['tools'] = $tools;
        }

        $response = $client->messages->create(...$args);

        $text = '';
        $toolCalls = [];
        foreach ($response->content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            } elseif ($block instanceof ToolUseBlock) {
                $toolCalls[] = ['id' => $block->id, 'name' => $block->name, 'input' => (array) $block->input];
            }
        }

        return [
            'stop_reason' => $response->stopReason,
            'text' => trim($text),
            'tool_calls' => $toolCalls,
            // Echoed back untouched on the next turn of a tool loop (thinking
            // blocks must be preserved verbatim).
            'raw_content' => $response->content,
        ];
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
