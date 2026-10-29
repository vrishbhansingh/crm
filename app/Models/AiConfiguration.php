<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One saved AI provider key for a company. Central connection; api_key is encrypted and hidden. */
class AiConfiguration extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'provider', 'api_key', 'key_hint', 'notify_email', 'model', 'priority', 'is_active',
        'requests_count', 'input_tokens', 'output_tokens', 'status', 'last_error', 'last_tested_at', 'last_used_at', 'failed_at', 'notified_at', 'created_by',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'is_active' => 'boolean',
        'last_tested_at' => 'datetime',
        'last_used_at' => 'datetime',
        'failed_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    protected $hidden = ['api_key'];

    public static function hintFor(string $key): string
    {
        return '…'.substr($key, -4);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
