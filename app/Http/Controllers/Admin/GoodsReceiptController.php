<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Services\DocumentNumberService;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Records a delivery against a sent/partially_received PurchaseOrder.
 * Each line's `received_qty` is added to (not replacing) its running
 * total on purchase_order_items, since a PO can receive multiple partial
 * deliveries over time — the GRN itself is kept as an immutable receiving
 * event for the audit trail.
 */
class GoodsReceiptController extends Controller
{
    public function store(Request $request, int $poId)
    {
        $tenantId = TenantContext::id();
        $po = PurchaseOrder::with('items')->findOrFail($poId);
        abort_unless(in_array($po->status, ['sent', 'partially_received'], true), 422, 'Only a sent purchase order can receive goods.');

        $data = $request->validate([
            'received_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $lines = $this->validatedLines($request, $po);
        abort_if(empty($lines), 422, 'Record at least one received quantity.');

        $grn = DB::connection($this->tenantConnection())->transaction(function () use ($data, $lines, $po, $tenantId) {
            $grn = GoodsReceipt::create([
                'tenant_id' => $tenantId,
                'purchase_order_id' => $po->id,
                'received_date' => $data['received_date'],
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::guard('web')->id(),
            ]);
            DocumentNumberService::assign($grn, 'grn_number', 'goods_receipt_number', 'GRN', fn () => GoodsReceipt::withoutGlobalScopes()->where('tenant_id', $tenantId)->count());

            foreach ($lines as $line) {
                GoodsReceiptItem::create([
                    'tenant_id' => $tenantId,
                    'goods_receipt_id' => $grn->id,
                    'purchase_order_item_id' => $line['purchase_order_item_id'],
                    'received_qty' => $line['received_qty'],
                    'remarks' => $line['remarks'] ?? null,
                ]);

                $item = $po->items->firstWhere('id', $line['purchase_order_item_id']);
                $item->increment('received_qty', $line['received_qty']);
            }

            $po->refreshReceivingStatus();

            return $grn;
        });

        return response()->json(['status' => true, 'message' => 'Goods receipt recorded', 'id' => $grn->id]);
    }

    private function validatedLines(Request $request, PurchaseOrder $po): array
    {
        $itemIds = $po->items->pluck('id')->all();

        $validated = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.purchase_order_item_id' => ['required', Rule::in($itemIds)],
            'lines.*.received_qty' => ['required', 'numeric', 'min:0.01'],
            'lines.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        return array_values(array_filter($validated['lines'], fn ($line) => (float) $line['received_qty'] > 0));
    }

    private function tenantConnection(): string
    {
        return config('tenancy.mode') === 'database' ? 'tenant' : config('tenancy.master_connection', 'mysql');
    }
}
