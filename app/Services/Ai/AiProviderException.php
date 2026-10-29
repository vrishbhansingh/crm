<?php

namespace App\Services\Ai;

/**
 * A failure talking to a non-Anthropic provider. $status is the HTTP status
 * (null for network trouble such as a timeout), $providerMessage is the
 * provider's own explanation, kept for the admin to read in the Test result.
 */
class AiProviderException extends \RuntimeException
{
    public function __construct(public readonly ?int $status, public readonly string $providerMessage, public readonly string $provider = '')
    {
        parent::__construct($providerMessage !== '' ? $providerMessage : 'AI provider error'.($status ? " (HTTP {$status})" : ''), $status ?? 0);
    }
}
