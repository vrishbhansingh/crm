<?php

namespace App\Services\WhatsApp;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\WhatsappAccount;
use App\Models\WhatsappCampaign;
use App\Models\WhatsappCampaignRecipient;
use App\Services\TemplateVariableResolver;

/**
 * Runs a WhatsappCampaign end to end — mirrors CampaignSender (the email
 * equivalent) closely on purpose: same audience-filter shape, same
 * pending-recipients-then-send flow, so the two channels stay consistent
 * for anyone who already knows one.
 */
class WhatsAppCampaignSender
{
    private const FILTERABLE_COLUMNS = [
        'leads' => ['lead_status', 'lead_source', 'priority', 'assigned_to', 'city'],
        'contacts' => ['status', 'city'],
        'companies' => ['status', 'industry', 'city'],
    ];

    public function __construct(
        private readonly WhatsAppMessageService $messages,
        private readonly TemplateVariableResolver $variables,
    ) {}

    public function buildRecipients(WhatsappCampaign $campaign): int
    {
        $campaign->recipients()->where('status', 'pending')->delete();

        $records = $this->audienceQuery($campaign)->get();

        $rows = $records->map(fn ($record) => [
            'tenant_id' => $campaign->tenant_id,
            'whatsapp_campaign_id' => $campaign->id,
            'recipient_type' => $this->singularType($campaign->audience_type),
            'recipient_id' => $record->id,
            'phone' => $record->phone,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        if ($rows !== []) {
            WhatsappCampaignRecipient::insert($rows);
        }

        $total = $campaign->recipients()->count();
        $campaign->forceFill(['total_recipients' => $total])->save();

        return $total;
    }

    public function previewCount(string $audienceType, array $filters): int
    {
        $campaign = new WhatsappCampaign(['audience_type' => $audienceType, 'audience_filters' => $filters]);

        return $this->audienceQuery($campaign)->count();
    }

    public function send(WhatsappCampaign $campaign, WhatsappAccount $account): void
    {
        $campaign->forceFill(['status' => 'sending'])->save();

        $campaign->recipients()->where('status', 'pending')->orderBy('id')
            ->chunkById(50, function ($recipients) use ($campaign, $account) {
                foreach ($recipients as $recipientRow) {
                    $this->sendOne($recipientRow, $campaign, $account);
                }
            });

        $campaign->refresh();
        $campaign->forceFill([
            'status' => $campaign->failed_count > 0 && $campaign->sent_count === 0 ? 'failed' : 'sent',
            'sent_at' => now(),
        ])->save();
    }

    private function sendOne(WhatsappCampaignRecipient $recipientRow, WhatsappCampaign $campaign, WhatsappAccount $account): void
    {
        $record = $recipientRow->recipient();

        if (! $record || ! $recipientRow->phone) {
            $recipientRow->forceFill(['status' => 'failed', 'error' => 'No WhatsApp number on file'])->save();
            $campaign->increment('failed_count');

            return;
        }

        $context = $this->variables->contextFor($record, $campaign->tenant ?? null);
        $body = $campaign->body ? $this->variables->resolve($campaign->body, $context) : null;

        $conversation = $this->messages->findOrCreateConversation($account, $recipientRow->phone, $record->name ?? null);

        $message = $this->messages->sendMessage($account, $conversation, [
            'type' => $campaign->message_type,
            'body' => $body,
            'template_name' => $campaign->template_name,
            'template_params' => $campaign->template_params,
        ], source: 'campaign');

        if ($message->status === 'sent') {
            $recipientRow->forceFill(['status' => 'sent', 'sent_at' => now(), 'wa_message_id' => $message->wa_message_id, 'error' => null])->save();
            $campaign->increment('sent_count');
        } else {
            $recipientRow->forceFill(['status' => 'failed', 'error' => $message->error_message])->save();
            $campaign->increment('failed_count');
        }
    }

    private function audienceQuery(WhatsappCampaign $campaign)
    {
        $model = match ($campaign->audience_type) {
            'leads' => new Lead,
            'contacts' => new Contact,
            'companies' => new Company,
            default => throw new \InvalidArgumentException("Unknown audience type: {$campaign->audience_type}"),
        };

        $query = $model->newQuery()->whereNotNull('phone')->where('phone', '!=', '');
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
