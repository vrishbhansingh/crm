<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'rfq_id',
        'product_id',
        'description',
        'uom',
        'quantity',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(RequestForQuotation::class, 'rfq_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
