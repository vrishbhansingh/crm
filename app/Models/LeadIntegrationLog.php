<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only: one row per inbound webhook call. No `updated_at` — nothing
 * about a delivery attempt should ever change after the fact.
 */
class LeadIntegrationLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'lead_integration_id', 'tenant_id', 'status', 'external_ref',
        'created_lead_id', 'message', 'payload', 'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function integration(): BelongsTo
    {
        return $this->belongsTo(LeadIntegration::class, 'lead_integration_id');
    }
}
