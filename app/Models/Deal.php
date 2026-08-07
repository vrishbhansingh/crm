<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deal extends Model
{
    use HasFactory, BelongsToTenant;
    protected $table = 'deals';
    protected $fillable = [
        'tenant_id',
        'pipeline_id',
        'stage_id',
        'lead_id',
        'order_id',
        'owner_id',
        'lost_reason_id',
        'created_by',
        'name',
        'amount',
        'currency',
        'expected_close_date',
        'closed_at',
        'status',
        'notes',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function lostReason(): BelongsTo
    {
        return $this->belongsTo(MasterValue::class, 'lost_reason_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function history(): HasMany
    {
        return $this->hasMany(DealStageHistory::class)->latest();
    }
}
