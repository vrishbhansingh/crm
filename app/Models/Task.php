<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'assigned_to', 'created_by', 'related_type', 'related_id',
        'title', 'description', 'priority', 'status', 'due_at', 'remind_at', 'notification_sent_at', 'completed_at',
        'activity_type', 'activity_details', 'checklist',
        'recurrence_rule', 'recurrence_interval', 'recurrence_end_date', 'recurrence_parent_id',
        'depends_on_task_id',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'remind_at' => 'datetime',
        'notification_sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'recurrence_end_date' => 'date',
        'activity_details' => 'array',
        'checklist' => 'array',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(self::class, 'depends_on_task_id');
    }

    public function recurrenceParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'recurrence_parent_id');
    }

    /**
     * True when this task can't be completed yet because the task it
     * depends on isn't done — the blocking task may live outside the
     * caller's visibility scope, so this checks existence directly rather
     * than relying on an already-loaded relation.
     */
    public function isBlocked(): bool
    {
        if (! $this->depends_on_task_id) {
            return false;
        }

        return self::whereKey($this->depends_on_task_id)->where('status', '!=', 'completed')->exists();
    }
}
