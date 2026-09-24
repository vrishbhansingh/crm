<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function index()
    {
        return view('vendors.index');
    }

    public function data(Request $request)
    {
        $query = Vendor::query();

        if ($request->filled('search')) {
            $term = '%'.$request->string('search').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('contact_person', 'like', $term)
                    ->orWhere('gst_number', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json(['data' => $query->orderBy('name')->get()]);
    }

    /**
     * {id,label} list for the PO/RFQ vendor picker.
     */
    public function options()
    {
        $vendors = Vendor::where('status', 'Active')->orderBy('name')->get(['id', 'name', 'gst_number', 'state']);

        return response()->json(['data' => $vendors]);
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant before creating a vendor.');

        $data = $this->validatedData($request);
        $data['tenant_id'] = $tenantId;

        $vendor = Vendor::create($data);

        return response()->json(['status' => true, 'message' => 'Vendor created successfully', 'id' => $vendor->id]);
    }

    public function update(Request $request, int $id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->update($this->validatedData($request));

        return response()->json(['status' => true, 'message' => 'Vendor updated successfully']);
    }

    public function destroy(int $id)
    {
        Vendor::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Vendor deleted successfully']);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'gst_number' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',
            'payment_terms' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);
    }
}
