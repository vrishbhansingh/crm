<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use App\Support\TenantContext;
use Illuminate\Http\Request;

/**
 * Rides the existing 'masters.*' permissions (Master Data settings page)
 * rather than a new permission module — tax rates are dropdown-adjacent
 * settings, just with a numeric rate MasterValue can't hold.
 */
class TaxRateController extends Controller
{
    public function data()
    {
        $rates = TaxRate::orderBy('rate_percent')->get();

        return response()->json(['data' => $rates]);
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant before adding a tax rate.');

        $data = $this->validatedData($request);
        $data['tenant_id'] = $tenantId;

        $rate = TaxRate::create($data);

        return response()->json(['status' => true, 'message' => 'Tax rate added successfully', 'id' => $rate->id]);
    }

    public function update(Request $request, int $id)
    {
        $rate = TaxRate::findOrFail($id);
        $rate->update($this->validatedData($request));

        return response()->json(['status' => true, 'message' => 'Tax rate updated successfully']);
    }

    public function toggleStatus(int $id)
    {
        $rate = TaxRate::findOrFail($id);
        $rate->update(['is_active' => ! $rate->is_active]);

        return response()->json(['status' => true, 'is_active' => $rate->is_active]);
    }

    public function destroy(int $id)
    {
        TaxRate::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Tax rate deleted successfully']);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'rate_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
    }
}
