<?php

namespace App\Services;

use App\Mail\CampaignMail;
use App\Models\Company;
use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\Lead;
use Illuminate\Support\Facades\Mail;
use Throwable;

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

    public function __construct(private readonly TemplateVariableResolver $variables) {}

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

    public function send(EmailCampaign $campaign): void
    {
        $campaign->forceFill(['status' => 'sending'])->save();

        app(MailConfigurator::class)->configureFor($campaign->tenant);

        $subject = $campaign->subject ?: $campaign->template->subject;
        $body = $campaign->template->body;

        $campaign->recipients()->where('status', 'pending')->orderBy('id')
            ->chunkById(50, function ($recipients) use ($subject, $body, $campaign) {
                foreach ($recipients as $recipientRow) {
                    $this->sendOne($recipientRow, $subject, $body, $campaign);
                }
            });

        $campaign->refresh();
        $campaign->forceFill([
            'status' => $campaign->failed_count > 0 && $campaign->sent_count === 0 ? 'failed' : 'sent',
            'sent_at' => now(),
        ])->save();
    }

    private function sendOne(EmailCampaignRecipient $recipientRow, string $subject, string $body, EmailCampaign $campaign): void
    {
        $record = $recipientRow->recipient();

        if (! $record) {
            $recipientRow->forceFill(['status' => 'failed', 'error' => 'Record no longer exists'])->save();
            $campaign->increment('failed_count');

            return;
        }

        $context = $this->variables->contextFor($record, $campaign->tenant);

        try {
            Mail::to($recipientRow->email)->send(new CampaignMail(
                $this->variables->resolve($subject, $context),
                $this->variables->resolve($body, $context),
            ));

            $recipientRow->forceFill(['status' => 'sent', 'sent_at' => now(), 'error' => null])->save();
            $campaign->increment('sent_count');
        } catch (Throwable $exception) {
            report($exception);
            $recipientRow->forceFill(['status' => 'failed', 'error' => $exception->getMessage()])->save();
            $campaign->increment('failed_count');
        }
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
