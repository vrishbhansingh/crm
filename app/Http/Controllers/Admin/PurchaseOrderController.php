<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyDetails;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\TaxRate;
use App\Models\Vendor;
use App\Services\DocumentNumberService;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Mirrors QuotationController's shape (single-request create-with-items,
 * draft-only editability, tenantTable()/tenantConnection() helpers) — see
 * that controller for the reasoning behind each pattern.
 */
class PurchaseOrderController extends Controller
{
    public function index()
    {
        return view('purchase-orders.index');
    }

    public function data(Request $request)
    {
        $query = PurchaseOrder::with(['vendor:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->integer('vendor_id'));
        }
        if ($request->filled('search')) {
            $term = '%'.$request->string('search').'%';
            $query->where(function ($q) use ($term) {
                $q->where('po_number', 'like', $term)
                    ->orWhereHas('vendor', fn ($vq) => $vq->where('name', 'like', $term));
            });
        }

        return response()->json(['data' => $query->latest('id')->get()]);
    }

    public function create(Request $request)
    {
        $request->validate(['vendor_id' => ['nullable', 'integer']]);

        return view('purchase-orders.create', [
            'vendorId' => $request->integer('vendor_id') ?: null,
        ]);
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant before creating a purchase order.');

        $data = $this->validatedPoData($request, $tenantId);
        $items = $this->validatedItemsArray($request, $tenantId);

        $data['tenant_id'] = $tenantId;
        $data['status'] = 'draft';
        $data['owner_id'] = $data['owner_id'] ?? Auth::guard('web')->id();
        $data['created_by'] = Auth::guard('web')->id();

        $po = DB::connection($this->tenantConnection())->transaction(function () use ($data, $items, $tenantId) {
            $po = PurchaseOrder::create($data);
            DocumentNumberService::assign($po, 'po_number', 'purchase_order_number', 'PO', fn () => PurchaseOrder::withoutGlobalScopes()->where('tenant_id', $tenantId)->count());

            foreach ($items as $sort => $item) {
                $item['tenant_id'] = $tenantId;
                $item['purchase_order_id'] = $po->id;
                $item['sort_order'] = $sort + 1;
                $item['tax_percent'] = isset($item['tax_rate_id'])
                    ? (float) (TaxRate::find($item['tax_rate_id'])?->rate_percent ?? 0)
                    : 0;
                PurchaseOrderItem::create($item);
            }

            if ($items) {
                $po->recalculateTotals();
            }

            return $po;
        });

        return response()->json(['status' => true, 'message' => 'Purchase order created successfully', 'id' => $po->id]);
    }

    public function show(int $id)
    {
        PurchaseOrder::findOrFail($id);

        return view('purchase-orders.show', ['poId' => $id]);
    }

    public function detail(int $id)
    {
        $po = PurchaseOrder::with(['items.product:id,name', 'vendor', 'owner:id,name', 'goodsReceipts.items'])->findOrFail($id);

        return response()->json(['status' => true, 'data' => array_merge($po->toArray(), [
            'is_editable' => $po->isEditable(),
        ])]);
    }

    public function pdf(int $id)
    {
        $po = PurchaseOrder::with(['items', 'vendor'])->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('purchase-orders.pdf', [
            'po' => $po,
            'company' => CompanyDetails::first(),
        ]);

        return $pdf->stream($po->po_number.'.pdf');
    }

    public function update(Request $request, int $id)
    {
        $po = $this->findEditable($id);
        $po->update($this->validatedPoData($request, $po->tenant_id, $po));

        return response()->json(['status' => true, 'message' => 'Purchase order updated successfully']);
    }

    public function destroy(int $id)
    {
        $this->findEditable($id)->delete();

        return response()->json(['status' => true, 'message' => 'Purchase order deleted successfully']);
    }

    public function send(int $id)
    {
        $po = PurchaseOrder::findOrFail($id);
        abort_unless($po->status === 'draft', 422, 'Only a draft purchase order can be sent.');
        abort_if($po->items()->count() === 0, 422, 'Add at least one item before sending.');

        $po->update(['status' => 'sent']);

        return response()->json(['status' => true, 'message' => 'Purchase order marked as sent']);
    }

    public function cancel(int $id)
    {
        $po = PurchaseOrder::findOrFail($id);
        abort_if($po->status === 'received', 422, 'A fully received purchase order cannot be cancelled.');

        $po->update(['status' => 'cancelled']);

        return response()->json(['status' => true, 'message' => 'Purchase order cancelled']);
    }

    public function addItem(Request $request, int $id)
    {
        $po = $this->findEditable($id);
        $data = $this->validatedItemData($request, $po);
        $data['tenant_id'] = $po->tenant_id;
        $data['purchase_order_id'] = $po->id;
        $data['sort_order'] = $po->items()->max('sort_order') + 1;

        $item = PurchaseOrderItem::create($data);
        $po->recalculateTotals();

        return response()->json(['status' => true, 'message' => 'Item added', 'id' => $item->id]);
    }

    public function updateItem(Request $request, int $id, int $itemId)
    {
        $po = $this->findEditable($id);
        $item = $po->items()->findOrFail($itemId);
        $item->update($this->validatedItemData($request, $po));
        $po->recalculateTotals();

        return response()->json(['status' => true, 'message' => 'Item updated']);
    }

    public function removeItem(int $id, int $itemId)
    {
        $po = $this->findEditable($id);
        $po->items()->findOrFail($itemId)->delete();
        $po->recalculateTotals();

        return response()->json(['status' => true, 'message' => 'Item removed']);
    }

    private function validatedPoData(Request $request, ?int $tenantId, ?PurchaseOrder $existing = null): array
    {
        return $request->validate([
            'vendor_id' => ['required', Rule::exists($this->tenantTable('vendors'), 'id')->where('tenant_id', $tenantId)],
            'rfq_id' => ['nullable', Rule::exists($this->tenantTable('request_for_quotations'), 'id')->where('tenant_id', $tenantId)],
            'expected_delivery_date' => ['nullable', 'date'],
            'terms_conditions' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'owner_id' => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $tenantId)],
        ]);
    }

    private function validatedItemsArray(Request $request, int $tenantId): array
    {
        $validated = $request->validate([
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['nullable', Rule::exists($this->tenantTable('products'), 'id')->where('tenant_id', $tenantId)],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.uom' => ['nullable', 'string', 'max:100'],
            'items.*.hsn_sac' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_rate_id' => ['nullable', Rule::exists($this->tenantTable('tax_rates'), 'id')->where('tenant_id', $tenantId)],
        ]);

        return $validated['items'] ?? [];
    }

    private function validatedItemData(Request $request, PurchaseOrder $po): array
    {
        $data = $request->validate([
            'product_id' => ['nullable', Rule::exists($this->tenantTable('products'), 'id')->where('tenant_id', $po->tenant_id)],
            'description' => ['required', 'string', 'max:255'],
            'uom' => ['nullable', 'string', 'max:100'],
            'hsn_sac' => ['nullable', 'string', 'max:50'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_rate_id' => ['nullable', Rule::exists($this->tenantTable('tax_rates'), 'id')->where('tenant_id', $po->tenant_id)],
        ]);

        $data['tax_percent'] = isset($data['tax_rate_id'])
            ? (float) (TaxRate::find($data['tax_rate_id'])?->rate_percent ?? 0)
            : 0;

        return $data;
    }

    private function tenantConnection(): string
    {
        return config('tenancy.mode') === 'database' ? 'tenant' : config('tenancy.master_connection', 'mysql');
    }

    private function tenantTable(string $table): string
    {
        return (config('tenancy.mode') === 'database' ? 'tenant.' : '').$table;
    }

    private function findEditable(int $id): PurchaseOrder
    {
        $po = PurchaseOrder::findOrFail($id);
        abort_unless($po->isEditable(), 422, 'Only a draft purchase order can be edited.');

        return $po;
    }
}
