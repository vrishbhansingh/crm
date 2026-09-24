<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'purchase_order_id',
        'product_id',
        'description',
        'uom',
        'hsn_sac',
        'quantity',
        'unit_price',
        'discount_percent',
        'tax_rate_id',
        'tax_percent',
        'line_total',
        'received_qty',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'line_total' => 'decimal:2',
        'received_qty' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function computeLineTotal(): float
    {
        $gross = (float) $this->quantity * (float) $this->unit_price;
        $afterDiscount = $gross - ($gross * (float) $this->discount_percent / 100);
        $withTax = $afterDiscount + ($afterDiscount * (float) $this->tax_percent / 100);

        return round($withTax, 2);
    }

    protected static function booted(): void
    {
        static::saving(function (PurchaseOrderItem $item) {
            $item->line_total = $item->computeLineTotal();
        });
    }
}
