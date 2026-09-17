<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'quotation_id',
        'product_id',
        'description',
        'uom',
        'quantity',
        'unit_price',
        'discount_percent',
        'tax_rate_id',
        'tax_percent',
        'line_total',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * quantity * unit_price, less the line discount, plus the line tax —
     * called before save so line_total is always consistent with its own
     * inputs even if a caller forgets to set it explicitly.
     */
    public function computeLineTotal(): float
    {
        $gross = (float) $this->quantity * (float) $this->unit_price;
        $afterDiscount = $gross - ($gross * (float) $this->discount_percent / 100);
        $withTax = $afterDiscount + ($afterDiscount * (float) $this->tax_percent / 100);

        return round($withTax, 2);
    }

    protected static function booted(): void
    {
        static::saving(function (QuotationItem $item) {
            $item->line_total = $item->computeLineTotal();
        });
    }
}
