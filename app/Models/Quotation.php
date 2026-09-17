<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use BelongsToTenant, SoftDeletes;

    public const STATUSES = ['draft', 'sent', 'accepted', 'rejected', 'expired'];

    protected $fillable = [
        'tenant_id',
        'quotation_number',
        'lead_id',
        'deal_id',
        'status',
        'version',
        'root_quotation_id',
        'valid_until',
        'currency',
        'sub_total',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'terms_conditions',
        'notes',
        'owner_id',
        'created_by',
        'sent_at',
        'sent_to_email',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'sub_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sent_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rootQuotation(): BelongsTo
    {
        return $this->belongsTo(self::class, 'root_quotation_id');
    }

    /**
     * Every version sharing this quote's number, oldest first — v1 has no
     * root_quotation_id, so it's matched by id rather than by the FK.
     */
    public function versions()
    {
        $rootId = $this->root_quotation_id ?? $this->id;

        return self::where('id', $rootId)->orWhere('root_quotation_id', $rootId)->orderBy('version');
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Recomputes the four money columns from the current items() and
     * persists them — called by the controller after every line-item
     * mutation rather than from a model hook, since it depends on
     * already-saved sibling rows.
     */
    public function recalculateTotals(): void
    {
        $items = $this->items()->get();

        $subTotal = $items->sum(fn (QuotationItem $item) => (float) $item->quantity * (float) $item->unit_price);
        $discountAmount = $items->sum(function (QuotationItem $item) {
            $gross = (float) $item->quantity * (float) $item->unit_price;

            return $gross * (float) $item->discount_percent / 100;
        });
        $taxAmount = $items->sum(function (QuotationItem $item) {
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
}
