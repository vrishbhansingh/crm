<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RequestForQuotation;
use App\Models\RfqItem;
use App\Models\RfqVendor;
use App\Services\DocumentNumberService;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Lightweight RFQ: items carry no price (that's what's being asked for),
 * and each invited vendor gets a single lump-sum quoted_amount recorded
 * manually once they respond — not an itemized back-and-forth. Reached as
 * a sub-tab of the Purchase Orders index rather than its own nav item, and
 * shares the purchase_orders.* permission module.
 */
class RfqController extends Controller
{
    public function index()
    {
        return view('purchase-orders.rfq-index');
    }

    public function data(Request $request)
    {
        $query = RequestForQuotation::withCount('invitedVendors');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('search')) {
            $query->where('rfq_number', 'like', '%'.$request->string('search').'%');
        }

        return response()->json(['data' => $query->latest('id')->get()]);
    }

    public function create()
    {
        return view('purchase-orders.rfq-create');
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant before creating an RFQ.');

        $data = $request->validate(['notes' => ['nullable', 'string', 'max:5000']]);
        $items = $this->validatedItemsArray($request, $tenantId);
        $vendorIds = $this->validatedVendorIds($request, $tenantId);

        $data['tenant_id'] = $tenantId;
        $data['status'] = 'draft';
        $data['owner_id'] = Auth::guard('web')->id();
        $data['created_by'] = Auth::guard('web')->id();

        $rfq = DB::connection($this->tenantConnection())->transaction(function () use ($data, $items, $vendorIds, $tenantId) {
            $rfq = RequestForQuotation::create($data);
            DocumentNumberService::assign($rfq, 'rfq_number', 'rfq_number', 'RFQ', fn () => RequestForQuotation::withoutGlobalScopes()->where('tenant_id', $tenantId)->count());

            foreach ($items as $sort => $item) {
                $item['tenant_id'] = $tenantId;
                $item['rfq_id'] = $rfq->id;
                $item['sort_order'] = $sort + 1;
                RfqItem::create($item);
            }

            foreach ($vendorIds as $vendorId) {
                RfqVendor::create(['tenant_id' => $tenantId, 'rfq_id' => $rfq->id, 'vendor_id' => $vendorId]);
            }

            return $rfq;
        });

        return response()->json(['status' => true, 'message' => 'RFQ created successfully', 'id' => $rfq->id]);
    }

    public function show(int $id)
    {
        RequestForQuotation::findOrFail($id);

        return view('purchase-orders.rfq-show', ['rfqId' => $id]);
    }

    public function detail(int $id)
    {
        $rfq = RequestForQuotation::with(['items.product:id,name', 'invitedVendors.vendor', 'owner:id,name'])->findOrFail($id);

        return response()->json(['status' => true, 'data' => array_merge($rfq->toArray(), [
            'is_editable' => $rfq->isEditable(),
        ])]);
    }

    /**
     * Records/updates a single invited vendor's lump-sum response.
     */
    public function recordQuote(Request $request, int $id, int $vendorRowId)
    {
        $rfq = RequestForQuotation::findOrFail($id);
        $row = $rfq->invitedVendors()->findOrFail($vendorRowId);

        $data = $request->validate([
            'quoted_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);
        $data['quoted_at'] = now();

        $row->update($data);

        return response()->json(['status' => true, 'message' => 'Vendor quote recorded']);
    }

    /**
     * Creates a draft PurchaseOrder from the RFQ's items for the chosen
     * vendor. Unit prices are left at 0 since the RFQ only tracked a
     * lump-sum total per vendor, not a per-line breakdown — the admin
     * fills those in on the resulting draft PO before sending it.
     */
    public function convertToPo(Request $request, int $id)
    {
        $tenantId = TenantContext::id();
        $rfq = RequestForQuotation::with('items')->findOrFail($id);
        abort_unless($rfq->status !== 'converted', 422, 'This RFQ has already been converted to a purchase order.');

        $data = $request->validate([
            'vendor_id' => ['required', Rule::exists($this->tenantTable('vendors'), 'id')->where('tenant_id', $tenantId)],
        ]);

        $po = DB::connection($this->tenantConnection())->transaction(function () use ($rfq, $data, $tenantId) {
            $po = PurchaseOrder::create([
                'tenant_id' => $tenantId,
                'vendor_id' => $data['vendor_id'],
                'rfq_id' => $rfq->id,
                'status' => 'draft',
                'owner_id' => Auth::guard('web')->id(),
                'created_by' => Auth::guard('web')->id(),
            ]);
            DocumentNumberService::assign($po, 'po_number', 'purchase_order_number', 'PO', fn () => PurchaseOrder::withoutGlobalScopes()->where('tenant_id', $tenantId)->count());

            foreach ($rfq->items as $sort => $rfqItem) {
                PurchaseOrderItem::create([
                    'tenant_id' => $tenantId,
                    'purchase_order_id' => $po->id,
                    'product_id' => $rfqItem->product_id,
                    'description' => $rfqItem->description,
                    'uom' => $rfqItem->uom,
                    'quantity' => $rfqItem->quantity,
                    'unit_price' => 0,
                    'sort_order' => $sort + 1,
                ]);
            }

            $rfq->update(['status' => 'converted']);

            return $po;
        });

        return response()->json(['status' => true, 'message' => 'Purchase order created from RFQ', 'purchase_order_id' => $po->id]);
    }

    public function destroy(int $id)
    {
        $rfq = RequestForQuotation::findOrFail($id);
        abort_unless($rfq->isEditable(), 422, 'Only a draft RFQ can be deleted.');
        $rfq->delete();

        return response()->json(['status' => true, 'message' => 'RFQ deleted successfully']);
    }

    private function validatedItemsArray(Request $request, int $tenantId): array
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', Rule::exists($this->tenantTable('products'), 'id')->where('tenant_id', $tenantId)],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.uom' => ['nullable', 'string', 'max:100'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ]);

        return $validated['items'];
    }

    private function validatedVendorIds(Request $request, int $tenantId): array
    {
        $validated = $request->validate([
            'vendor_ids' => ['required', 'array', 'min:1'],
            'vendor_ids.*' => ['integer', Rule::exists($this->tenantTable('vendors'), 'id')->where('tenant_id', $tenantId)],
        ]);

        return $validated['vendor_ids'];
    }

    private function tenantConnection(): string
    {
        return config('tenancy.mode') === 'database' ? 'tenant' : config('tenancy.master_connection', 'mysql');
    }

    private function tenantTable(string $table): string
    {
        return (config('tenancy.mode') === 'database' ? 'tenant.' : '').$table;
    }
}
