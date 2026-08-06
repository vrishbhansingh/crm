<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterType;
use App\Models\MasterValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * One reusable admin screen for every master data type (Phase 2) instead of
 * a separate page per lookup list. Global values (tenant_id null) are the
 * platform-wide defaults, editable only by a platform Super Admin; a
 * tenant's Company Admin can only add/edit/delete their own tenant's values.
 */
class MasterDataController extends Controller
{
    public function index()
    {
        return view('admin.master-data.index');
    }

    public function getTypes()
    {
        return response()->json([
            'data' => MasterType::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function getValues(Request $request)
    {
        $request->validate(['type_id' => 'required|exists:master_types,id']);

        $tenantId = Auth::guard('web')->user()->tenant_id;

        $values = MasterValue::where('master_type_id', $request->type_id)
            ->where(function ($query) use ($tenantId) {
                $query->whereNull('tenant_id');
                if ($tenantId !== null) {
                    $query->orWhere('tenant_id', $tenantId);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        return response()->json(['data' => $values]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'master_type_id' => 'required|exists:master_types,id',
            'code' => 'required|string|max:100',
            'label' => 'required|string|max:150',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
        ]);

        $tenantId = Auth::guard('web')->user()->tenant_id;

        $duplicate = MasterValue::where('master_type_id', $request->master_type_id)
            ->where('code', $request->code)
            ->where(function ($query) use ($tenantId) {
                $query->whereNull('tenant_id');
                if ($tenantId !== null) {
                    $query->orWhere('tenant_id', $tenantId);
                }
            })
            ->exists();

        if ($duplicate) {
            return response()->json([
                'status' => false,
                'message' => 'This code already exists for this type',
            ], 422);
        }

        MasterValue::create([
            'master_type_id' => $request->master_type_id,
            // Super Admin (tenant_id null) adds a global default seen by every
            // tenant; a Company Admin's addition only applies to their tenant.
            'tenant_id' => $tenantId,
            'code' => $request->code,
            'label' => $request->label,
            'color' => $request->color,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json(['status' => true, 'message' => 'Value added successfully']);
    }

    public function update(Request $request, $id)
    {
        $value = MasterValue::findOrFail($id);

        if (! $this->canManage($value)) {
            return response()->json(['status' => false, 'message' => 'You cannot edit a global default value'], 403);
        }

        $request->validate([
            'label' => 'required|string|max:150',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
        ]);

        $value->label = $request->label;
        $value->color = $request->color;
        if ($request->filled('sort_order')) {
            $value->sort_order = $request->sort_order;
        }
        $value->save();

        return response()->json(['status' => true, 'message' => 'Value updated successfully']);
    }

    public function toggleStatus($id)
    {
        $value = MasterValue::findOrFail($id);

        if (! $this->canManage($value)) {
            return response()->json(['status' => false, 'message' => 'You cannot edit a global default value'], 403);
        }

        $value->is_active = ! $value->is_active;
        $value->save();

        return response()->json(['status' => true, 'is_active' => $value->is_active]);
    }

    public function destroy($id)
    {
        $value = MasterValue::findOrFail($id);

        if (! $this->canManage($value)) {
            return response()->json(['status' => false, 'message' => 'You cannot delete a global default value'], 403);
        }

        $value->delete();

        return response()->json(['status' => true, 'message' => 'Value deleted successfully']);
    }

    private function canManage(MasterValue $value): bool
    {
        $tenantId = Auth::guard('web')->user()->tenant_id;

        // Platform Super Admin (tenant_id null) manages everything, including
        // globals. A Company Admin only manages their own tenant's values.
        return $tenantId === null || $value->tenant_id === $tenantId;
    }
}
