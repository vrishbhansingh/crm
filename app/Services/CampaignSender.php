<?php

namespace App\Services;

use App\Jobs\SendCampaignEmailJob;
use App\Models\Company;
use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\Lead;
use App\Tenancy\TenantConnectionManager;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

/**
 * Everything involved in actually running a campaign: turning its audience
 * filters into a concrete recipient list, then sending to each one. Used
 * both by the "Send now" controller action (already inside the right
 * tenant's request context) and by the scheduled-campaign command (which
 * activates each tenant's connection itself before calling in).
 */
class CampaignSender
{
    private const FILTERABLE_COLUMNS = [
        'leads' => ['lead_status', 'lead_source', 'priority', 'assigned_to', 'city'],
        'contacts' => ['status', 'city'],
        'companies' => ['status', 'industry', 'city'],
    ];

    public function __construct(
        private readonly TemplateVariableResolver $variables,
        private readonly EmailLogger $emailLogger,
        private readonly TenantConnectionManager $connections,
    ) {}

    /**
     * Resolve the campaign's audience_filters into a concrete list of
     * recipients (skipping anyone without an email on file) and persist
     * them as pending rows. Safe to call again on a draft — existing
     * pending rows for this campaign are replaced, not duplicated.
     */
    public function buildRecipients(EmailCampaign $campaign): int
    {
        $campaign->recipients()->where('status', 'pending')->delete();

        $records = $this->audienceQuery($campaign)->get();

        $rows = $records->map(fn ($record) => [
            'tenant_id' => $campaign->tenant_id,
            'email_campaign_id' => $campaign->id,
            'recipient_type' => $this->singularType($campaign->audience_type),
            'recipient_id' => $record->id,
            'email' => $record->email,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        if ($rows !== []) {
            EmailCampaignRecipient::insert($rows);
        }

        $total = $campaign->recipients()->count();
        $campaign->forceFill(['total_recipients' => $total])->save();

        return $total;
    }

    /**
     * How many records currently match the campaign's filters — used for
     * the "N recipients" preview before committing to send.
     */
    public function previewCount(string $audienceType, array $filters): int
    {
        $campaign = new EmailCampaign(['audience_type' => $audienceType, 'audience_filters' => $filters]);

        return $this->audienceQuery($campaign)->count();
    }

    /**
     * Builds one queued job per recipient (variables already resolved, so
     * the job itself doesn't need the lead/contact/company record at all)
     * and dispatches them as a batch — the campaign's own status only
     * flips to its final sent/failed once every job in the batch has run,
     * via the batch's finally() callback below. Actual delivery happens
     * whenever the scheduled queue worker next runs (see Kernel::schedule),
     * not within this request.
     */
    public function send(EmailCampaign $campaign): void
    {
        $campaign->forceFill(['status' => 'sending'])->save();

        $subject = $campaign->subject ?: $campaign->template->subject;
        $body = $campaign->template->body;
        $tenantId = $campaign->tenant_id;
        $tenant = $campaign->tenant;

        // Resolved once, up front, while the right tenant connection is
        // still guaranteed active — turns each EmailTemplateAttachment row
        // into a plain, queue-serializable array (absolute disk path, not
        // the model), so the job never needs its own DB/tenant lookup just
        // to find the files.
        $attachments = $campaign->template->attachments->map(fn ($attachment) => [
            'path' => Storage::disk('local')->path($attachment->stored_path),
            'name' => $attachment->original_name,
            'mime' => $attachment->mime_type,
        ])->all();

        $jobs = [];

        $campaign->recipients()->where('status', 'pending')->orderBy('id')
            ->chunkById(50, function ($recipients) use (&$jobs, $subject, $body, $campaign, $tenantId, $tenant, $attachments) {
                foreach ($recipients as $recipientRow) {
                    $record = $recipientRow->recipient();

                    if (! $record) {
                        $recipientRow->forceFill(['status' => 'failed', 'error' => 'Record no longer exists'])->save();
                        $campaign->increment('failed_count');

                        continue;
                    }

                    $context = $this->variables->contextFor($record, $tenant);
                    $resolvedSubject = $this->variables->resolve($subject, $context);
                    $resolvedBody = $this->variables->resolve($body, $context);

                    $log = $this->emailLogger->queued('campaign', $tenantId, $recipientRow->email, $resolvedSubject, [
                        'campaign_id' => $campaign->id,
                        'recipient_id' => $recipientRow->id,
                        'body' => $resolvedBody,
                    ]);

                    $jobs[] = new SendCampaignEmailJob(
                        $tenantId, $campaign->id, $recipientRow->id, $recipientRow->email,
                        $resolvedSubject, $resolvedBody, $log->id, $attachments,
                    );
                }
            });

        if ($jobs === []) {
            $campaign->refresh();
            $campaign->forceFill([
                'status' => $campaign->failed_count > 0 && $campaign->sent_count === 0 ? 'failed' : 'sent',
                'sent_at' => now(),
            ])->save();

            return;
        }

        Bus::batch($jobs)
            ->name('campaign-'.$campaign->id)
            // Without this, Laravel cancels the entire batch the moment a
            // single job fails (its default) — every recipient queued
            // after a bad address would then skip itself via the
            // `$this->batch()?->cancelled()` guard at the top of
            // SendCampaignEmailJob::handle() and never send, contradicting
            // that job's own stated behavior: "a failed send here is a
            // normal, expected outcome... it's recorded and the batch
            // moves on."
            ->allowFailures()
            ->finally(function () use ($campaign, $tenantId) {
                // This callback can itself run later, outside the request
                // that dispatched it — no tenant connection is guaranteed to
                // be active, so (re)activate it before touching the campaign.
                // "shared" tenancy mode has no per-tenant database at all.
                $needsTenantSwitch = $tenantId && config('tenancy.mode') !== 'shared';

                if ($needsTenantSwitch) {
                    $this->connections->activate($tenantId);
                }

                $campaign->refresh();
                $campaign->forceFill([
                    'status' => $campaign->failed_count > 0 && $campaign->sent_count === 0 ? 'failed' : 'sent',
                    'sent_at' => now(),
                ])->save();

                if ($needsTenantSwitch) {
                    $this->connections->deactivate();
                }
            })
            ->dispatch();
    }

    private function audienceQuery(EmailCampaign $campaign)
    {
        $model = match ($campaign->audience_type) {
            'leads' => new Lead,
            'contacts' => new Contact,
            'companies' => new Company,
            default => throw new \InvalidArgumentException("Unknown audience type: {$campaign->audience_type}"),
        };

        $query = $model->newQuery()->whereNotNull('email')->where('email', '!=', '');
        $allowed = self::FILTERABLE_COLUMNS[$campaign->audience_type] ?? [];

        foreach ((array) $campaign->audience_filters as $column => $value) {
            if (in_array($column, $allowed, true) && $value !== null && $value !== '') {
                $query->where($column, $value);
            }
        }

        return $query;
    }

    private function singularType(string $audienceType): string
    {
        return match ($audienceType) {
            'leads' => 'lead',
            'contacts' => 'contact',
            'companies' => 'company',
            default => rtrim($audienceType, 's'),
        };
    }
}
