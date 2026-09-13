<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A tenant's connection for sending/receiving WhatsApp messages — either
 * the official Meta WhatsApp Business Cloud API ('meta_cloud') or a
 * generic third-party gateway configured with just an API key and sender
 * number ('unofficial'). Lives on the central connection, same reasoning
 * as LeadIntegration: the public inbound webhook has to resolve a tenant
 * from nothing but the opaque `webhook_token` in the URL, before any
 * tenant database connection exists.
 */
class WhatsappAccount extends Model
{
    protected $fillable = [
        'tenant_id', 'channel_type', 'name', 'phone_number', 'is_active', 'is_default',
        'webhook_token', 'verify_token', 'credentials', 'messages_sent_count',
        'messages_received_count', 'last_used_at', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'credentials' => 'encrypted:array',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = ['credentials', 'verify_token'];

    public static function generateWebhookToken(): string
    {
        do {
            $token = Str::random(48);
        } while (self::where('webhook_token', $token)->exists());

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

    public function logs(): HasMany
    {
        return $this->hasMany(WhatsappAccountLog::class)->latest('created_at');
    }

    public function webhookUrl(): string
    {
        return url('/webhooks/whatsapp/'.$this->webhook_token);
    }

    public function credential(string $key, mixed $default = null): mixed
    {
        return ($this->credentials ?? [])[$key] ?? $default;
    }
}
