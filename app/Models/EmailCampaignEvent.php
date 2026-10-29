<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One open or click on a campaign email. */
class EmailCampaignEvent extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $fillable = ['tenant_id', 'email_campaign_id', 'email_campaign_recipient_id', 'type', 'url', 'user_agent', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(EmailCampaignRecipient::class, 'email_campaign_recipient_id');
    }
}
