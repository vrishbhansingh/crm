<?php

namespace App\Services;

use App\Models\EmailLog;
use Throwable;

/**
 * Central place every outbound email passes through so the Super Admin's
 * email log sees all of it — synchronous sends (password resets, SMTP
 * tests) and queued ones (campaigns) alike.
 */
class EmailLogger
{
    /**
     * Wrap a synchronous send: logs it as sent or failed around whatever
     * $send() does, then re-throws on failure so the caller's own
     * error-handling (it likely already has some) is unaffected — this
     * only adds the log row, it never changes what the caller sees happen.
     */
    public function sync(string $type, ?int $tenantId, string $toEmail, ?string $subject, callable $send, array $context = []): void
    {
        $log = EmailLog::create([
            'tenant_id' => $tenantId,
            'type' => $type,
            'to_email' => $toEmail,
            'subject' => $subject,
            'status' => 'sending',
            'context' => $context,
            'queued_at' => now(),
        ]);

        try {
            $send();
            $log->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (Throwable $exception) {
            $log->update(['status' => 'failed', 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    /**
     * Create the log row for a queued send up front (so it shows as
     * "queued" immediately, before a worker ever picks it up) — the job
     * that actually sends it is responsible for moving it to sending/
     * sent/failed itself.
     */
    public function queued(string $type, ?int $tenantId, string $toEmail, ?string $subject, array $context = []): EmailLog
    {
        return EmailLog::create([
            'tenant_id' => $tenantId,
            'type' => $type,
            'to_email' => $toEmail,
            'subject' => $subject,
            'status' => 'queued',
            'context' => $context,
            'queued_at' => now(),
        ]);
    }
}
