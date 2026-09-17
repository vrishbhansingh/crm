<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'category',
        'uom',
        'hsn_sac',
        'unit_price',
        'tax_rate_id',
        'description',
        'status',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }
}
