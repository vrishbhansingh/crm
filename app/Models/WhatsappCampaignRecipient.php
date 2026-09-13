<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappCampaignRecipient extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'whatsapp_campaign_id', 'recipient_type', 'recipient_id',
        'phone', 'status', 'error', 'wa_message_id', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WhatsappCampaign::class, 'whatsapp_campaign_id');
    }

    public function recipient(): Lead|Contact|Company|null
    {
        return match ($this->recipient_type) {
            'lead' => Lead::find($this->recipient_id),
            'contact' => Contact::find($this->recipient_id),
            'company' => Company::find($this->recipient_id),
            default => null,
        };
    }
}
