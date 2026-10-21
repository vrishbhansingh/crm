<?php

namespace App\Services\Ai;

use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APITimeoutException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\PermissionDeniedException;
use Anthropic\Core\Exceptions\RateLimitException;

/** Turns a provider failure into something a user (or their admin) can act on. */
class AiErrors
{
    public static function message(\Throwable $e): string
    {
        return match (true) {
            $e instanceof AuthenticationException, $e instanceof PermissionDeniedException => 'The AI assistant is not set up correctly (the API key was rejected). Ask your administrator to check the ANTHROPIC_API_KEY setting.',
            $e instanceof RateLimitException => 'The AI service is busy right now. Please try again in a minute.',
            $e instanceof APITimeoutException, $e instanceof APIConnectionException => 'Could not reach the AI service. Please try again in a moment.',
            default => 'The AI assistant is unavailable right now. Please try again shortly.',
        };
    }
}
