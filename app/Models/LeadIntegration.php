<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A tenant's webhook connection to one external lead source (IndiaMART,
 * JustDial, Facebook Lead Ads, Google Ads, WhatsApp, or a generic
 * website/Zapier form). Lives on the central connection — see the
 * migration for why — identified publicly only by `token`, an opaque
 * random string that stands in for authentication on the receiving
 * webhook route (nobody can guess it, and it's never paired with the
 * tenant's real identity in the URL).
 */
class LeadIntegration extends Model
{
    protected $fillable = [
        'tenant_id', 'platform', 'name', 'token', 'secret', 'verify_token',
        'is_active', 'leads_created_count', 'last_received_at', 'created_by',
        'default_lead_type', 'default_lead_status', 'default_priority',
        'default_assigned_to', 'pipeline_id', 'field_mapping',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_received_at' => 'datetime',
        'secret' => 'encrypted',
        'field_mapping' => 'array',
    ];

    protected $hidden = ['secret', 'verify_token'];

    public static function generateToken(): string
    {
        do {
            $token = Str::random(48);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function defaultAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'default_assigned_to');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LeadIntegrationLog::class)->latest('created_at');
    }

    public function webhookUrl(): string
    {
        return url('/webhooks/leads/'.$this->token);
    }
}
