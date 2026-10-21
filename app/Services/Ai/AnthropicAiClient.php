<?php

namespace App\Services\Ai;

use Anthropic\Client;
use Anthropic\Messages\ToolUseBlock;

class AnthropicAiClient implements AiClient
{
    /**
     * Our own HTTP client so a slow or unreachable API fails in seconds
     * instead of hanging a chat for the SDK's 10-minute default, and so
     * HTTPS works on a WAMP PHP that ships without a CA bundle configured
     * (cURL error 60). Verification stays ON: when PHP has no bundle of
     * its own, we point at the Mozilla bundle WAMP already installs.
     */
    private function transporter(): \GuzzleHttp\Client
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

    public function send(string $system, array $messages, array $tools, ?array $jsonSchema = null): array
    {
        $client = new Client(apiKey: (string) config('ai.api_key'), requestOptions: [
            'transporter' => $this->transporter(),
            'timeout' => (float) config('ai.timeout'),
            'maxRetries' => 1,
        ]);

        $args = [
            'model' => config('ai.model'),
            'maxTokens' => config('ai.max_tokens'),
            'system' => [['type' => 'text', 'text' => $system]],
            'messages' => $messages,
            'outputConfig' => ['effort' => config('ai.effort')],
        ];
        if ($jsonSchema) {
            $args['outputConfig']['format'] = ['type' => 'json_schema', 'schema' => $jsonSchema];
        }
        if ($tools) { // tool-less calls (summaries, drafts) must not send an empty list
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
}
