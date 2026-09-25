<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailLog;
use App\Services\MailConfigurator;
use App\Tenancy\TenantConnectionManager;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * One recipient's send, run by the scheduled queue worker (see Kernel's
 * schedule()) rather than inline in the request — a campaign of thousands
 * no longer has to finish within one HTTP request, and each attempt's
 * outcome lands in the email log the Super Admin watches. A failed send
 * here is a normal, expected outcome (a bad address, a bounced mailbox) —
 * it's recorded and the batch moves on, not retried, matching the
 * synchronous behavior this replaced.
 */
class SendCampaignEmailJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    /**
     * @param  array<int, array{path: string, name: string, mime: ?string}>  $attachments
     */
    public function __construct(
        private readonly ?int $tenantId,
        private readonly int $campaignId,
        private readonly int $recipientId,
        private readonly string $toEmail,
        private readonly string $subject,
        private readonly string $body,
        private readonly int $emailLogId,
        private readonly array $attachments = [],
    ) {}

    public function handle(TenantConnectionManager $connections, MailConfigurator $mailer): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $log = EmailLog::find($this->emailLogId);

        // "shared" tenancy mode runs every tenant off the one master
        // connection (used in tests, and optionally in real deployments) —
        // there's no per-tenant database to activate, and trying to would
        // fail for a tenant with no provisioned database.
        $needsTenantSwitch = $this->tenantId && config('tenancy.mode') !== 'shared';

        if ($needsTenantSwitch) {
            $connections->activate($this->tenantId);
        }

        $campaign = null;
        $recipientRow = null;

        try {
            $campaign = EmailCampaign::find($this->campaignId);
            $recipientRow = EmailCampaignRecipient::find($this->recipientId);
            $mailer->configureFor($campaign?->tenant);
            $log?->update(['status' => 'sending']);

            Mail::to($this->toEmail)->send(new CampaignMail($this->subject, $this->body, $this->attachments));

            $recipientRow?->forceFill(['status' => 'sent', 'sent_at' => now(), 'error' => null])->save();
            $campaign?->increment('sent_count');
            $log?->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (Throwable $exception) {
            report($exception);
            $recipientRow?->forceFill(['status' => 'failed', 'error' => $exception->getMessage()])->save();
            $campaign?->increment('failed_count');
            $log?->update(['status' => 'failed', 'error' => $exception->getMessage()]);
        } finally {
            if ($needsTenantSwitch) {
                $connections->deactivate();
            }
        }
    }
}
