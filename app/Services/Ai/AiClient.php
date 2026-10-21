<?php

namespace App\Services\Ai;

/**
 * The one seam between the assistant and the model provider, so the
 * assistant's loop, guardrails and tool scoping can be tested without
 * calling the real API.
 */
interface AiClient
{
    /**
     * @param  string  $system  System prompt.
     * @param  array  $messages  Conversation so far (provider message format).
     * @param  array  $tools  Tool definitions (name/description/inputSchema).
     * @param  array|null  $jsonSchema  When given, the reply text is constrained to JSON matching this schema.
     * @return array{stop_reason: ?string, text: string, tool_calls: array<int, array{id: string, name: string, input: array}>, raw_content: mixed}
     */
    public function send(string $system, array $messages, array $tools, ?array $jsonSchema = null): array;
}
