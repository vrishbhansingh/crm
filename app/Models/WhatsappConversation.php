<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappConversation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'whatsapp_account_id', 'lead_id', 'contact_id', 'wa_phone', 'wa_name',
        'last_message_at', 'last_message_preview', 'unread_count', 'window_expires_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'window_expires_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class)->orderBy('created_at');
    }

    /**
     * True while a free-form reply is still allowed under Meta's 24-hour
     * customer-service window. Always true for channels that don't track
     * one (the unofficial gateway has no such restriction).
     */
    public function withinSessionWindow(): bool
    {
        return $this->window_expires_at === null || $this->window_expires_at->isFuture();
    }
}
