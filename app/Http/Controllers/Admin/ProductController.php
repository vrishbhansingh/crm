<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\TenantContext;
use Illuminate\Http\Request;
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

    private function validatedData(Request $request, ?int $tenantId): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'uom' => 'nullable|string|max:100',
            'hsn_sac' => 'nullable|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'tax_rate_id' => ['nullable', Rule::exists('tax_rates', 'id')->where('tenant_id', $tenantId)],
            'description' => 'nullable|string|max:5000',
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);
    }

    private function findEditable(int $id): Product
    {
        return Product::findOrFail($id);
    }
}
