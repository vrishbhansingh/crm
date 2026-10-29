<?php

namespace App\Services\Ai;

use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\APITimeoutException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\PermissionDeniedException;
use Anthropic\Core\Exceptions\RateLimitException;

/** Turns a provider failure (from any provider) into something a user, or their admin, can act on. */
class AiErrors
{
    private const BILLING = '/credit|billing|balance|quota|insufficient|exceeded your current/i';

    /** HTTP status of a provider failure, whichever SDK or driver raised it. */
    private static function status(\Throwable $e): ?int
    {
        return match (true) {
            $e instanceof APIStatusException => $e->status,
            $e instanceof AiProviderException => $e->status,
            default => null,
        };
    }

    /** The provider's own explanation, if it gave one. */
    private static function detail(\Throwable $e): string
    {
        return $e instanceof AiProviderException ? $e->providerMessage : $e->getMessage();
    }

    /**
     * True when the failure is about THIS configuration (bad/expired/revoked
     * key, out of credit, rate limited, wrong model name) so trying the
     * company's next configuration can help. Network trouble or a server
     * error would hit every key alike.
     */
    public static function isKeyProblem(\Throwable $e): bool
    {
        $status = self::status($e);
        if ($status === null) {
            return false;
        }

        if (in_array($status, [401, 402, 403, 404, 429], true)) {
            return true;
        }

        return $status === 400 && (bool) (preg_match(self::BILLING, self::detail($e)) || preg_match('/model/i', self::detail($e)));
    }

    /** Short, human reason stored on the configuration and shown to admins. */
    public static function reason(\Throwable $e): string
    {
        $status = self::status($e);
        $detail = self::detail($e);

        return match (true) {
            $e instanceof AuthenticationException, $status === 401 => 'The API key was rejected (invalid or expired).',
            $e instanceof PermissionDeniedException, $status === 403 => 'The API key does not have permission (revoked, restricted, or wrong region).',
            $e instanceof RateLimitException, $status === 429 => (bool) preg_match(self::BILLING, $detail) ? 'The account is out of credit or quota.' : 'Rate limit reached for this key.',
            $status === 402, ($status === 400 && preg_match(self::BILLING, $detail)) => 'The account is out of credit.',
            $status === 404 => 'The selected model was not found for this key. Pick another model.',
            $status === 400 && preg_match('/model/i', $detail) => 'The provider did not accept the selected model. Pick another model.',
            $e instanceof APITimeoutException, $e instanceof APIConnectionException, $e instanceof AiProviderException && $status === null => 'Could not reach the AI service.',
            default => 'The AI service returned an error'.($detail !== '' ? ': '.mb_strimwidth($detail, 0, 160, '…') : '.'),
        };
    }

    public static function message(\Throwable $e): string
    {
        $status = self::status($e);

        return match (true) {
            $e instanceof AuthenticationException, $e instanceof PermissionDeniedException, in_array($status, [401, 403], true) => 'The AI assistant is not set up correctly (the API key was rejected). Ask your administrator to check the AI settings.',
            $e instanceof RateLimitException, $status === 429 => 'The AI service is busy right now. Please try again in a minute.',
            $e instanceof APITimeoutException, $e instanceof APIConnectionException, $e instanceof AiProviderException && $status === null => 'Could not reach the AI service. Please try again in a moment.',
            $e instanceof \RuntimeException && $e->getMessage() === 'No AI configuration is available.' => 'The AI assistant is not set up yet. Ask your administrator to add an API key in AI settings.',
            self::isKeyProblem($e) => 'Every saved AI key failed (expired, out of credit, rate limited or wrong model). Ask your administrator to check the AI settings.',
            default => 'The AI assistant is unavailable right now. Please try again shortly.',
        };
    }
}
