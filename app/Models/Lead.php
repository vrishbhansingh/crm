<?php

namespace App\Models;

use App\Domain\Leads\LeadScoringService;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lead extends Model
{
    use HasFactory, BelongsToTenant;
    protected $table = 'leads';
    protected $fillable = [
        'tenant_id',
        'lead_type',
        'lead_source',
        'name',
        'phone',
        'alternate_phone',
        'email',
        'city',
        'state',
        'country',
        'products',
        'service',
        'budget',
        'requirement',
        'lead_status',
        'priority',
        'follow_up_date',
        'follow_up_time',
        'follow_up_note',
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'remarks',
        'internal_note',
        'is_converted',
        'converted_at',
        'conversion_value',
        'status_reason',
        'last_contacted_at',
        'status',
        'score',
    ];

    protected static function booted(): void
    {
        static::saving(function (Lead $lead) {
            $lead->score = app(LeadScoringService::class)->score($lead);
        });
    }

    public function customerContact(): HasOne
    {
        return $this->hasOne(CustomerContact::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LeadAttachment::class)->latest('created_at');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(Leadfollowup::class)->latest('created_at');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'lead_tag');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    /**
     * Named assignedUser (not assignedTo) — Eloquent snake_cases relation
     * names in JSON output, and "assigned_to" is already the raw FK column;
     * a same-named relation would silently overwrite it in serialized output.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
