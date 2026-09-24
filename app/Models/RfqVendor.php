<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqVendor extends Model
{
    use BelongsToTenant;

    protected $table = 'rfq_vendors';

    protected $fillable = [
        'tenant_id',
        'rfq_id',
        'vendor_id',
        'quoted_amount',
        'quoted_at',
        'notes',
    ];

    protected $casts = [
        'quoted_amount' => 'decimal:2',
        'quoted_at' => 'datetime',
    ];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(RequestForQuotation::class, 'rfq_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
