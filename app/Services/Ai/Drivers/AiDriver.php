<?php

namespace App\Services\Ai\Drivers;

/**
 * One way of talking to a provider. The assistant works with a neutral
 * conversation format (see AssistantService), and each driver translates it
 * to and from its provider's wire format, so the rest of the app never needs
 * to know which provider answered.
 *
 * Neutral messages:
 *   ['role' => 'user', 'content' => string]
 *   ['role' => 'assistant', 'content' => string, 'tool_calls' => [[id, name, input]]?, 'raw' => mixed?]
 *   ['role' => 'tool_results', 'results' => [['id' => string, 'content' => string]]]
 */
interface AiDriver
{
    /**
     * @return array{stop_reason: ?string, text: string, tool_calls: array<int, array{id: string, name: string, input: array}>, raw_content: mixed, usage: array{input: int, output: int}}
     */
    public function call(string $key, string $model, string $system, array $messages, array $tools, ?array $jsonSchema): array;
}
