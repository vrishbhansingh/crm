<?php

namespace Tests\Support;

use App\Services\Ai\AiClient;

/** Scripted stand-in for the model: records what it was sent, replays canned turns. */
class FakeAiClient implements AiClient
{
    public array $calls = [];

    public function __construct(private array $script)
    {
    }

    public function send(string $system, array $messages, array $tools, ?array $jsonSchema = null, ?string $model = null): array
    {
        $this->calls[] = compact('system', 'messages', 'tools', 'jsonSchema', 'model');

        return array_shift($this->script) ?? ['stop_reason' => 'end_turn', 'text' => 'done', 'tool_calls' => [], 'raw_content' => []];
    }
}
