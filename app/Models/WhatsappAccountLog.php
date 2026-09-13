<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappAccountLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'whatsapp_account_id', 'tenant_id', 'direction', 'status', 'external_ref', 'message', 'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(WhatsappAccount::class, 'whatsapp_account_id');
    }
}
