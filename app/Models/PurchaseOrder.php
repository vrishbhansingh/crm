<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use BelongsToTenant, SoftDeletes;

    public const STATUSES = ['draft', 'sent', 'partially_received', 'received', 'cancelled'];

    protected $fillable = [
        'tenant_id',
        'po_number',
        'vendor_id',
        'rfq_id',
        'status',
        'sub_total',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'expected_delivery_date',
        'terms_conditions',
        'notes',
        'owner_id',
        'created_by',
    ];

    protected $casts = [
        'expected_delivery_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class)->orderBy('sort_order');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(RequestForQuotation::class, 'rfq_id');
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
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

    /**
     * Same shape as Quotation::recalculateTotals() — sums the persisted
     * items() rather than trusting request input.
     */
    public function recalculateTotals(): void
    {
        $items = $this->items()->get();

        $subTotal = $items->sum(fn (PurchaseOrderItem $item) => (float) $item->quantity * (float) $item->unit_price);
        $discountAmount = $items->sum(function (PurchaseOrderItem $item) {
            $gross = (float) $item->quantity * (float) $item->unit_price;

            return $gross * (float) $item->discount_percent / 100;
        });
        $taxAmount = $items->sum(function (PurchaseOrderItem $item) {
            $gross = (float) $item->quantity * (float) $item->unit_price;
            $afterDiscount = $gross - ($gross * (float) $item->discount_percent / 100);

            return $afterDiscount * (float) $item->tax_percent / 100;
        });

        $this->forceFill([
            'sub_total' => round($subTotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'total_amount' => round($subTotal - $discountAmount + $taxAmount, 2),
        ])->save();
    }

    /**
     * Flips status between partially_received/received based on each
     * item's cumulative received_qty vs its ordered quantity — called by
     * GoodsReceiptController after recording a GRN.
     */
    public function refreshReceivingStatus(): void
    {
        if ($this->status === 'cancelled' || $this->status === 'draft') {
            return;
        }

        $items = $this->items()->get();
        $fullyReceived = $items->every(fn (PurchaseOrderItem $item) => (float) $item->received_qty >= (float) $item->quantity);
        $anyReceived = $items->contains(fn (PurchaseOrderItem $item) => (float) $item->received_qty > 0);

        $this->forceFill([
            'status' => $fullyReceived ? 'received' : ($anyReceived ? 'partially_received' : $this->status),
        ])->save();
    }
}
