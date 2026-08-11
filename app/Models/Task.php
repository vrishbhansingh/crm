<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'assigned_to', 'created_by', 'related_type', 'related_id',
        'title', 'description', 'priority', 'status', 'due_at', 'remind_at', 'notification_sent_at', 'completed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'remind_at' => 'datetime',
        'notification_sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
