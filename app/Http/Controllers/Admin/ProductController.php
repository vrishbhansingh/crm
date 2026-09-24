<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterType;
use App\Models\MasterValue;
use App\Models\Product;
use App\Models\TaxRate;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index');
    }

    public function data(Request $request)
    {
        $query = Product::with('taxRate:id,name,rate_percent');

        if ($request->filled('search')) {
            $term = '%'.$request->string('search').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $products = $query->orderBy('name')->get();

        return response()->json(['data' => $products]);
    }

    /**
     * Lightweight {id,name,uom,unit_price,tax_rate_id,tax rate %} list for
     * the quotation line-item picker — kept separate from data() so that
     * page doesn't have to paginate/search through the full admin payload.
     */
    public function options()
    {
        $products = Product::where('status', 'Active')
            ->with('taxRate:id,rate_percent')
            ->orderBy('name')
            ->get(['id', 'name', 'uom', 'unit_price', 'tax_rate_id', 'hsn_sac']);

        return response()->json(['data' => $products]);
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant before creating a product.');

        $data = $this->validatedData($request, $tenantId);
        $data['tenant_id'] = $tenantId;

        $product = Product::create($data);

        return response()->json(['status' => true, 'message' => 'Product created successfully', 'id' => $product->id]);
    }

    public function update(Request $request, int $id)
    {
        $product = $this->findEditable($id);
        $product->update($this->validatedData($request, $product->tenant_id));

        return response()->json(['status' => true, 'message' => 'Product updated successfully']);
    }

    public function destroy(int $id)
    {
        $this->findEditable($id)->delete();

        return response()->json(['status' => true, 'message' => 'Product deleted successfully']);
    }

    /**
     * Quick-add for the "+" next to Category/UOM on the product form —
     * gated by products.create (not masters.create), since adding a
     * category while creating a product is part of that workflow, not a
     * separate settings task. Reuses the same MasterType/MasterValue
     * tables the Master Data admin screen manages, so anything added here
     * also shows up there.
     */
    public function storeCategory(Request $request)
    {
        return response()->json(['status' => true, 'data' => $this->quickAddMasterValue($request, 'product_category')]);
    }

    public function storeUom(Request $request)
    {
        return response()->json(['status' => true, 'data' => $this->quickAddMasterValue($request, 'uom')]);
    }

    /**
     * Quick-add only asks for the rate — a separate "name" field next to a
     * single-purpose % input was one more thing to fill in for no real
     * benefit, so the name is auto-derived ("GST 12%") the same way the
     * full Tax Rates screen already labels India GST slabs by convention.
     * That screen still lets it be renamed afterward for anything
     * non-GST (a name field there stays meaningful — "Exempt", "Zero-rated",
     * a customer-specific scheme, etc.), so nothing is lost by not asking
     * for it here.
     */
    public function storeTaxRate(Request $request)
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant first.');

        $data = $request->validate([
            'name' => 'nullable|string|max:100',
            'rate_percent' => 'required|numeric|min:0|max:100',
        ]);
        $data['tenant_id'] = $tenantId;
        $data['name'] = $data['name'] ?: 'GST '.rtrim(rtrim(number_format((float) $data['rate_percent'], 2), '0'), '.').'%';

        $taxRate = TaxRate::create($data);

        return response()->json(['status' => true, 'data' => ['id' => $taxRate->id, 'name' => $taxRate->name, 'rate_percent' => $taxRate->rate_percent]]);
    }

    private function quickAddMasterValue(Request $request, string $typeCode): array
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant first.');

        $data = $request->validate(['label' => 'required|string|max:150']);
        $code = Str::slug($data['label'], '_') ?: Str::random(8);

        $type = MasterType::where('code', $typeCode)->first();
        abort_if(! $type, 500, "Master type '{$typeCode}' is not configured.");

        // Reuse an existing value with the same code (global default or
        // this tenant's own) instead of creating a near-duplicate.
        $existing = MasterValue::where('master_type_id', $type->id)->where('code', $code)
            ->where(fn ($q) => $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId))
            ->first();
        if ($existing) {
            return ['code' => $existing->code, 'label' => $existing->label];
        }

        $value = MasterValue::create([
            'master_type_id' => $type->id,
            'tenant_id' => $tenantId,
            'code' => $code,
            'label' => $data['label'],
            'sort_order' => 0,
        ]);

        return ['code' => $value->code, 'label' => $value->label];
    }

    private function validatedData(Request $request, ?int $tenantId): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'uom' => 'nullable|string|max:100',
            'hsn_sac' => 'nullable|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'tax_rate_id' => ['nullable', Rule::exists($this->tenantTable('tax_rates'), 'id')->where('tenant_id', $tenantId)],
            'description' => 'nullable|string|max:5000',
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);
    }

    /**
     * tax_rates lives on the separate 'tenant' connection in database-mode
     * tenancy, not the default one Rule::exists() checks by default — same
     * fix already established in DealController/MasterDataController.
     */
    private function tenantTable(string $table): string
    {
        return (config('tenancy.mode') === 'database' ? 'tenant.' : '').$table;
    }

    private function findEditable(int $id): Product
    {
        return Product::findOrFail($id);
    }
}
