<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappCampaign extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'created_by', 'whatsapp_account_id', 'name', 'message_type',
        'template_name', 'template_params', 'body', 'audience_type', 'audience_filters',
        'status', 'scheduled_at', 'sent_at', 'total_recipients', 'sent_count', 'failed_count',
    ];

    protected $casts = [
        'template_params' => 'array',
        'audience_filters' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function recipients(): HasMany
    {
        return $this->hasMany(WhatsappCampaignRecipient::class);
    }
}
