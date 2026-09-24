<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestForQuotation extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'request_for_quotations';

    public const STATUSES = ['draft', 'sent', 'closed', 'converted'];

    protected $fillable = [
        'tenant_id',
        'rfq_number',
        'status',
        'notes',
        'owner_id',
        'created_by',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class, 'rfq_id')->orderBy('sort_order');
    }

    public function invitedVendors(): HasMany
    {
        return $this->hasMany(RfqVendor::class, 'rfq_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'rfq_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }
}
