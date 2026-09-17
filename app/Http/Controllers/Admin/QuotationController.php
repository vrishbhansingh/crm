<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyDetails;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\TaxRate;
use App\Services\QuotationNumberService;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Deliberately namespaced/named distinctly from the legacy
 * App\Http\Controllers\User\QuotationController (route names 'quotation.*')
 * — that controller's 3 static print templates are retired once this
 * module's "Quotations" panel ships on the Lead/Deal detail pages.
 */
class QuotationController extends Controller
{
    public function index()
    {
        return view('quotations.index');
    }

    public function data(Request $request)
    {
        $query = $this->visibleQuery()->with(['lead:id,name', 'deal:id,name', 'owner:id,name'])
            ->whereNull('root_quotation_id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('lead_id')) {
            $query->where('lead_id', $request->integer('lead_id'));
        }
        if ($request->filled('deal_id')) {
            $query->where('deal_id', $request->integer('deal_id'));
        }
        if ($request->filled('search')) {
            $term = '%'.$request->string('search').'%';
            $query->where(function ($q) use ($term) {
                $q->where('quotation_number', 'like', $term)
                    ->orWhereHas('lead', fn ($lq) => $lq->where('name', 'like', $term))
                    ->orWhereHas('deal', fn ($dq) => $dq->where('name', 'like', $term));
            });
        }

        $quotations = $query->latest('id')->get();

        return response()->json(['data' => $quotations]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'lead_id' => ['nullable', 'integer'],
            'deal_id' => ['nullable', 'integer'],
        ]);

        return view('quotations.create', [
            'leadId' => $request->integer('lead_id') ?: null,
            'dealId' => $request->integer('deal_id') ?: null,
        ]);
    }

    /**
     * {id,label} picker options for the create form's lead/deal selector —
     * kept under quotations.* permissions rather than reusing
     * TaskController::relatedOptions(), which is gated by tasks.view.
     */
    public function linkOptions(string $type)
    {
        abort_unless(in_array($type, ['lead', 'deal'], true), 404);
        $user = Auth::guard('web')->user();

        if ($type === 'lead') {
            $query = Lead::query();
            if (! $user->hasElevatedAccess()) {
                $query->where('assigned_to', $user->id);
            }
            $records = $query->latest('id')->limit(100)->get(['id', 'name']);
        } else {
            $query = Deal::query();
            if (! $user->hasElevatedAccess()) {
                $query->where('owner_id', $user->id);
            }
            $records = $query->latest('id')->limit(100)->get(['id', 'name']);
        }

        return response()->json(['data' => $records->map(fn ($r) => ['id' => $r->id, 'label' => $r->name])]);
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant before creating a quotation.');

        $data = $this->validatedQuotationData($request, $tenantId);
        $data['tenant_id'] = $tenantId;
        $data['status'] = 'draft';
        $data['version'] = 1;
        $data['owner_id'] = $data['owner_id'] ?? Auth::guard('web')->id();
        $data['created_by'] = Auth::guard('web')->id();
        $data['terms_conditions'] = $data['terms_conditions'] ?? CompanyDetails::first()?->default_quotation_terms;

        $quotation = Quotation::create($data);
        QuotationNumberService::saveNew($quotation);

        return response()->json(['status' => true, 'message' => 'Quotation created successfully', 'id' => $quotation->id]);
    }

    public function show(int $id)
    {
        $this->findVisible($id);

        return view('quotations.show', ['quotationId' => $id]);
    }

    public function detail(int $id)
    {
        $quotation = $this->findVisible($id);
        $quotation->load(['items.product:id,name', 'lead:id,name,email,phone', 'deal:id,name', 'owner:id,name']);

        return response()->json(['status' => true, 'data' => array_merge($quotation->toArray(), [
            'is_editable' => $quotation->isEditable(),
        ])]);
    }

    public function pdf(int $id)
    {
        $quotation = $this->findVisible($id);
        $quotation->load(['items', 'lead', 'deal.contact', 'deal.company']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('quotations.pdf', [
            'quotation' => $quotation,
            'company' => CompanyDetails::first(),
            'customer' => $this->resolveCustomer($quotation),
        ]);

        return $pdf->stream($quotation->quotation_number.'.pdf');
    }

    private function resolveCustomer(Quotation $quotation): array
    {
        if ($quotation->lead) {
            return [
                'name' => $quotation->lead->name,
                'company' => $quotation->lead->company_name,
                'email' => $quotation->lead->email,
                'phone' => $quotation->lead->phone,
            ];
        }

        if ($quotation->deal) {
            return [
                'name' => $quotation->deal->contact->name ?? $quotation->deal->company->name ?? $quotation->deal->name,
                'company' => $quotation->deal->company->name ?? null,
                'email' => $quotation->deal->contact->email ?? $quotation->deal->company->email ?? null,
                'phone' => $quotation->deal->contact->phone ?? $quotation->deal->company->phone ?? null,
            ];
        }

        return ['name' => null, 'company' => null, 'email' => null, 'phone' => null];
    }

    public function update(Request $request, int $id)
    {
        $quotation = $this->findEditable($id);

        $data = $this->validatedQuotationData($request, $quotation->tenant_id, $quotation);
        $quotation->update($data);

        return response()->json(['status' => true, 'message' => 'Quotation updated successfully']);
    }

    public function destroy(int $id)
    {
        $quotation = $this->findEditable($id);
        $quotation->delete();

        return response()->json(['status' => true, 'message' => 'Quotation deleted successfully']);
    }

    public function addItem(Request $request, int $id)
    {
        $quotation = $this->findEditable($id);
        $data = $this->validatedItemData($request, $quotation);
        $data['tenant_id'] = $quotation->tenant_id;
        $data['quotation_id'] = $quotation->id;
        $data['sort_order'] = $quotation->items()->max('sort_order') + 1;

        $item = QuotationItem::create($data);
        $quotation->recalculateTotals();

        return response()->json(['status' => true, 'message' => 'Item added', 'id' => $item->id]);
    }

    public function updateItem(Request $request, int $id, int $itemId)
    {
        $quotation = $this->findEditable($id);
        $item = $quotation->items()->findOrFail($itemId);
        $item->update($this->validatedItemData($request, $quotation));
        $quotation->recalculateTotals();

        return response()->json(['status' => true, 'message' => 'Item updated']);
    }

    public function removeItem(int $id, int $itemId)
    {
        $quotation = $this->findEditable($id);
        $quotation->items()->findOrFail($itemId)->delete();
        $quotation->recalculateTotals();

        return response()->json(['status' => true, 'message' => 'Item removed']);
    }

    private function validatedQuotationData(Request $request, ?int $tenantId, ?Quotation $existing = null): array
    {
        $data = $request->validate([
            'lead_id' => ['nullable', 'integer', Rule::exists($this->tenantTable('leads'), 'id')->where('tenant_id', $tenantId)],
            'deal_id' => ['nullable', 'integer', Rule::exists($this->tenantTable('deals'), 'id')->where('tenant_id', $tenantId)],
            'valid_until' => ['nullable', 'date'],
            'currency' => ['nullable', 'string', 'max:10'],
            'terms_conditions' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            // users lives on the master connection even in database-per-tenant
            // mode (see User::getConnectionName()), so this one is never
            // prefixed — unlike leads/deals/products/tax_rates above/below.
            'owner_id' => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $tenantId)],
        ]);

        $leadId = $data['lead_id'] ?? $existing?->lead_id;
        $dealId = $data['deal_id'] ?? $existing?->deal_id;
        abort_unless(($leadId !== null) xor ($dealId !== null), 422, 'A quotation must be linked to exactly one lead or one deal.');

        return $data;
    }

    private function validatedItemData(Request $request, Quotation $quotation): array
    {
        $data = $request->validate([
            'product_id' => ['nullable', Rule::exists($this->tenantTable('products'), 'id')->where('tenant_id', $quotation->tenant_id)],
            'description' => ['required', 'string', 'max:255'],
            'uom' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_rate_id' => ['nullable', Rule::exists($this->tenantTable('tax_rates'), 'id')->where('tenant_id', $quotation->tenant_id)],
        ]);

        $data['tax_percent'] = isset($data['tax_rate_id'])
            ? (float) (TaxRate::find($data['tax_rate_id'])?->rate_percent ?? 0)
            : 0;

        return $data;
    }

    /**
     * Tenant-scoped tables (leads, deals, products, tax_rates, quotations,
     * tasks, …) live on the separate 'tenant' connection in database-mode
     * tenancy, not the default one Rule::exists() checks by default — same
     * fix already established in DealController/MasterDataController.
     */
    private function tenantTable(string $table): string
    {
        return (config('tenancy.mode') === 'database' ? 'tenant.' : '').$table;
    }

    private function visibleQuery()
    {
        $user = Auth::guard('web')->user();
        $query = Quotation::query();

        if (! $user->hasElevatedAccess()) {
            $query->where(fn ($q) => $q->where('owner_id', $user->id)->orWhere('created_by', $user->id));
        }

        return $query;
    }

    private function findVisible(int $id): Quotation
    {
        return $this->visibleQuery()->findOrFail($id);
    }

    private function findEditable(int $id): Quotation
    {
        $quotation = $this->findVisible($id);
        abort_unless($quotation->isEditable(), 422, 'This quotation has already been sent — create a new version to make changes.');

        return $quotation;
    }
}
