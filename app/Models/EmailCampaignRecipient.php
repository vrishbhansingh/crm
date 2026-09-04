<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaignRecipient extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'email_campaign_id',
        'recipient_type',
        'recipient_id',
        'email',
        'status',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }

    /**
     * The actual Lead/Contact/Company record this row targets — resolved
     * dynamically since recipient_type isn't a fixed relation.
     */
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
