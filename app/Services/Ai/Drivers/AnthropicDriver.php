<?php

namespace App\Services\Ai\Drivers;

use Anthropic\Client;
use Anthropic\Messages\ToolUseBlock;
use Psr\Http\Client\ClientInterface;

/** Claude, through Anthropic's official PHP SDK. */
class AnthropicDriver implements AiDriver
{
    public function __construct(private readonly ClientInterface $transporter)
    {
    }

    public function call(string $key, string $model, string $system, array $messages, array $tools, ?array $jsonSchema): array
    {
        $client = new Client(apiKey: $key, requestOptions: [
            'transporter' => $this->transporter,
            'timeout' => (float) config('ai.timeout'),
            'maxRetries' => 1,
        ]);

        $args = [
            'model' => $model,
            'maxTokens' => config('ai.max_tokens'),
            'system' => [['type' => 'text', 'text' => $system]],
            'messages' => $this->toAnthropic($messages),
            'outputConfig' => ['effort' => config('ai.effort')],
        ];
        if ($jsonSchema) {
            $args['outputConfig']['format'] = ['type' => 'json_schema', 'schema' => $jsonSchema];
        }
        if ($tools) { // tool-less calls (summaries, drafts, checks) must not send an empty list
            $args['tools'] = array_map(fn ($t) => ['inputSchema' => $this->schema($t['inputSchema'] ?? [])] + $t, $tools);
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
            // Echoed back untouched on the next turn of a tool loop (thinking blocks must be preserved verbatim).
            'raw_content' => $response->content,
            'usage' => ['input' => (int) ($response->usage->inputTokens ?? 0), 'output' => (int) ($response->usage->outputTokens ?? 0)],
        ];
    }

    private function toAnthropic(array $messages): array
    {
        $out = [];
        foreach ($messages as $m) {
            switch ($m['role']) {
                case 'tool_results':
                    $out[] = ['role' => 'user', 'content' => array_map(fn ($r) => ['type' => 'tool_result', 'toolUseID' => $r['id'], 'content' => $r['content']], $m['results'])];
                    break;

                case 'assistant':
                    if (empty($m['tool_calls'])) {
                        $out[] = ['role' => 'assistant', 'content' => (string) $m['content']];
                        break;
                    }
                    // Claude's own blocks (with any thinking) when we have them; otherwise rebuild from the neutral form
                    // (e.g. the turn came from another provider after a failover).
                    $raw = $m['raw'] ?? null;
                    $out[] = ['role' => 'assistant', 'content' => (is_array($raw) && isset($raw[0]) && is_object($raw[0])) ? $raw : $this->rebuild($m)];
                    break;

                default:
                    $out[] = ['role' => 'user', 'content' => (string) $m['content']];
            }
        }

        return $out;
    }

    private function rebuild(array $m): array
    {
        $blocks = [];
        if (($m['content'] ?? '') !== '') {
            $blocks[] = ['type' => 'text', 'text' => $m['content']];
        }
        foreach ($m['tool_calls'] as $call) {
            $blocks[] = ['type' => 'tool_use', 'id' => $call['id'], 'name' => $call['name'], 'input' => $call['input'] ?: new \stdClass()];
        }

        return $blocks;
    }

    /** An empty "properties" must serialise as {} (an object), never [] (a list). */
    private function schema(array $schema): array|object
    {
        if (array_key_exists('properties', $schema) && $schema['properties'] === []) {
            $schema['properties'] = new \stdClass();
        }

        return $schema;
    }
}
