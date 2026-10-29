<?php

namespace App\Services\Ai\Drivers;

use App\Services\Ai\AiProviderException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

/**
 * Any provider that speaks the OpenAI "chat completions" protocol: OpenAI,
 * Google Gemini (its OpenAI-compatible endpoint), Groq, Mistral, DeepSeek,
 * OpenRouter. Only the base URL and token parameter differ — see AiProviders.
 */
class OpenAiCompatibleDriver implements AiDriver
{
    public function __construct(private readonly ClientInterface $transporter, private readonly array $provider, private readonly string $providerKey)
    {
    }

    public function call(string $key, string $model, string $system, array $messages, array $tools, ?array $jsonSchema): array
    {
        if ($jsonSchema) {
            // Json-object mode is understood by every provider; the schema rides along in the instructions.
            $system .= "\n\nRespond with ONLY a single JSON object, no other text, matching this JSON schema:\n".json_encode($jsonSchema, JSON_UNESCAPED_SLASHES);
        }

        $body = [
            'model' => $model,
            'messages' => array_merge([['role' => 'system', 'content' => $system]], $this->toOpenAi($messages)),
            ($this->provider['token_param'] ?? 'max_tokens') => (int) config('ai.max_tokens'),
        ];
        if ($tools) {
            $body['tools'] = array_map(fn ($t) => ['type' => 'function', 'function' => [
                'name' => $t['name'], 'description' => $t['description'] ?? '', 'parameters' => $this->schema($t['inputSchema'] ?? ['type' => 'object', 'properties' => []]),
            ]], $tools);
        }
        if ($jsonSchema) {
            $body['response_format'] = ['type' => 'json_object'];
        }

        $request = new Request('POST', rtrim($this->provider['base_url'], '/').'/chat/completions', [
            'Authorization' => 'Bearer '.$key,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        try {
            $response = $this->transporter->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new AiProviderException(null, 'Could not reach '.($this->provider['label'] ?? 'the provider').': '.$e->getMessage(), $this->providerKey);
        }

        $status = $response->getStatusCode();
        $data = json_decode((string) $response->getBody(), true);

        if ($status < 200 || $status >= 300) {
            $message = is_array($data) ? ($data['error']['message'] ?? $data[0]['error']['message'] ?? $data['message'] ?? '') : '';
            throw new AiProviderException($status, (string) $message, $this->providerKey);
        }

        $choice = $data['choices'][0] ?? null;
        if (! $choice) {
            throw new AiProviderException($status, 'The provider returned an empty answer.', $this->providerKey);
        }

        $message = $choice['message'] ?? [];
        $toolCalls = [];
        foreach ($message['tool_calls'] ?? [] as $i => $call) {
            $input = json_decode((string) ($call['function']['arguments'] ?? '{}'), true);
            $toolCalls[] = [
                'id' => (string) (($call['id'] ?? '') !== '' ? $call['id'] : 'call_'.$i),
                'name' => (string) ($call['function']['name'] ?? ''),
                'input' => is_array($input) ? $input : [],
            ];
        }

        return [
            'stop_reason' => match ($choice['finish_reason'] ?? null) {
                'tool_calls', 'function_call' => 'tool_use',
                'length' => 'max_tokens',
                'content_filter' => 'refusal',
                default => 'end_turn',
            },
            'text' => trim((string) ($message['content'] ?? '')),
            'tool_calls' => $toolCalls,
            'raw_content' => null,
            'usage' => ['input' => (int) ($data['usage']['prompt_tokens'] ?? 0), 'output' => (int) ($data['usage']['completion_tokens'] ?? 0)],
        ];
    }

    private function toOpenAi(array $messages): array
    {
        $out = [];
        foreach ($messages as $m) {
            switch ($m['role']) {
                case 'tool_results':
                    foreach ($m['results'] as $r) {
                        $out[] = ['role' => 'tool', 'tool_call_id' => $r['id'], 'content' => $r['content']];
                    }
                    break;

                case 'assistant':
                    $entry = ['role' => 'assistant', 'content' => ($m['content'] ?? '') !== '' ? $m['content'] : null];
                    if (! empty($m['tool_calls'])) {
                        $entry['tool_calls'] = array_map(fn ($c) => [
                            'id' => $c['id'], 'type' => 'function',
                            'function' => ['name' => $c['name'], 'arguments' => json_encode($c['input'] ?: new \stdClass(), JSON_UNESCAPED_UNICODE)],
                        ], $m['tool_calls']);
                    } else {
                        $entry['content'] = (string) ($m['content'] ?? '');
                    }
                    $out[] = $entry;
                    break;

                default:
                    $out[] = ['role' => 'user', 'content' => (string) $m['content']];
            }
        }

        return $out;
    }

    /** An empty "properties" must serialise as {} (an object), never [] (a list). */
    private function schema(array $schema): array
    {
        if (array_key_exists('properties', $schema) && $schema['properties'] === []) {
            $schema['properties'] = new \stdClass();
        }

        return $schema;
    }
}
