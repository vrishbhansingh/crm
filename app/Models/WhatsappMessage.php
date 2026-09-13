<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'whatsapp_conversation_id', 'direction', 'type', 'body',
        'template_name', 'template_params', 'media_url', 'status', 'wa_message_id',
        'error_message', 'source', 'sent_by',
    ];

    protected $casts = [
        'template_params' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsappConversation::class, 'whatsapp_conversation_id');
    }

    public function sender(): ?User
    {
        return $this->sent_by ? User::find($this->sent_by) : null;
    }
}
