<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $fillable = [
        'tenant_id', 'type', 'to_email', 'subject', 'status',
        'error', 'attempts', 'context', 'queued_at', 'sent_at',
    ];

    protected $casts = [
        'context' => 'array',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public const TYPES = [
        'campaign' => 'Campaign',
        'password_reset' => 'Password reset',
        'smtp_test' => 'SMTP test',
        'task_reminder' => 'Task reminder',
        'lead_followup' => 'Lead follow-up',
        'other' => 'Other',
    ];

    public const STATUSES = ['queued', 'sending', 'sent', 'failed'];

    public function getConnectionName()
    {
        return config('tenancy.master_connection', 'mysql');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Not a stored status — a queued/sending row that's been sitting for
     * longer than this is almost certainly never going to complete (the
     * scheduled queue worker isn't running, or died mid-batch), so it's
     * surfaced as its own filterable state rather than left looking like
     * an ordinary in-progress send.
     */
    public const STUCK_AFTER_MINUTES = 15;

    public function scopeStuck($query)
    {
        return $query->whereIn('status', ['queued', 'sending'])
            ->where('queued_at', '<', now()->subMinutes(self::STUCK_AFTER_MINUTES));
    }

    /**
     * Same test as scopeStuck(), for a single already-loaded row — the
     * controller/view need to ask this about one row at a time (e.g. to
     * decide whether to show a Retry button), not run a fresh query.
     */
    public function isStuck(): bool
    {
        return in_array($this->status, ['queued', 'sending'], true)
            && $this->queued_at
            && $this->queued_at->lt(now()->subMinutes(self::STUCK_AFTER_MINUTES));
    }
}
