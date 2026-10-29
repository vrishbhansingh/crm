<?php

namespace App\Services\Ai;

use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\APITimeoutException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\PermissionDeniedException;
use Anthropic\Core\Exceptions\RateLimitException;

/** Turns a provider failure into something a user (or their admin) can act on. */
class AiErrors
{
    /**
     * True when the failure is about THIS key (bad/expired/revoked, out of
     * credit, rate limited) so trying the company's next configuration can
     * help. Network trouble or a server error would hit every key alike.
     */
    public static function isKeyProblem(\Throwable $e): bool
    {
        if (! $e instanceof APIStatusException) {
            return false;
        }

        $status = $e->status;
        if (in_array($status, [401, 402, 403, 429], true)) {
            return true;
        }

        return $status === 400 && (bool) preg_match('/credit|billing|balance|quota/i', $e->getMessage());
    }

    /** Short, human reason stored on the configuration and shown to admins. */
    public static function reason(\Throwable $e): string
    {
        return match (true) {
            $e instanceof AuthenticationException => 'The API key was rejected (invalid or expired).',
            $e instanceof PermissionDeniedException => 'The API key does not have permission (revoked or restricted).',
            $e instanceof RateLimitException => 'Rate limit reached for this key.',
            $e instanceof APIStatusException && $e->status === 402 => 'The account is out of credit.',
            $e instanceof APIStatusException && $e->status === 400 && preg_match('/credit|billing|balance|quota/i', $e->getMessage()) => 'The account is out of credit.',
            $e instanceof APITimeoutException, $e instanceof APIConnectionException => 'Could not reach the AI service.',
            default => 'The AI service returned an error.',
        };
    }

    public static function message(\Throwable $e): string
    {
        return match (true) {
            $e instanceof AuthenticationException, $e instanceof PermissionDeniedException => 'The AI assistant is not set up correctly (the API key was rejected). Ask your administrator to check the AI settings.',
            $e instanceof RateLimitException => 'The AI service is busy right now. Please try again in a minute.',
            $e instanceof APITimeoutException, $e instanceof APIConnectionException => 'Could not reach the AI service. Please try again in a moment.',
            $e instanceof \RuntimeException && $e->getMessage() === 'No AI configuration is available.' => 'The AI assistant is not set up yet. Ask your administrator to add an API key in AI settings.',
            self::isKeyProblem($e) => 'Every saved AI key failed (expired, out of credit or rate limited). Ask your administrator to check the AI settings.',
            default => 'The AI assistant is unavailable right now. Please try again shortly.',
        };
    }
}
