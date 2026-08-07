<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pipeline;
use Illuminate\Http\Request;

/**
 * Settings -> Pipelines admin screen (Phase 5), structural clone of
 * Admin\MasterDataController's two-column list + modal pattern. Tenant
 * scoping is handled by Pipeline's BelongsToTenant trait, same as every
 * other tenant-scoped model — no manual tenant_id filtering needed here.
 */
class PipelineController extends Controller
{
    public function index()
    {
        return view('pipelines.index');
    }

    public function data()
    {
        $pipelines = Pipeline::withCount('deals')->orderBy('sort_order')->orderBy('name')->get();

        return response()->json(['data' => $pipelines]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'is_default' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->boolean('is_default')) {
            Pipeline::where('is_default', true)->update(['is_default' => false]);
        }

        Pipeline::create([
            'name' => $request->name,
            'is_default' => $request->boolean('is_default'),
            'is_active' => true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json(['status' => true, 'message' => 'Pipeline created successfully']);
    }

    public function update(Request $request, $id)
    {
        $pipeline = Pipeline::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:150',
            'is_default' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->boolean('is_default')) {
            Pipeline::where('id', '!=', $pipeline->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $pipeline->name = $request->name;
        $pipeline->is_default = $request->boolean('is_default');
        if ($request->filled('sort_order')) {
            $pipeline->sort_order = $request->sort_order;
        }
        $pipeline->save();

        return response()->json(['status' => true, 'message' => 'Pipeline updated successfully']);
    }

    public function toggleStatus($id)
    {
        $pipeline = Pipeline::findOrFail($id);
        $pipeline->is_active = ! $pipeline->is_active;
        $pipeline->save();

        return response()->json(['status' => true, 'is_active' => $pipeline->is_active]);
    }

    public function destroy($id)
    {
        $pipeline = Pipeline::findOrFail($id);

        if ($pipeline->deals()->exists()) {
            return response()->json(['status' => false, 'message' => 'This pipeline has deals in it and cannot be deleted'], 422);
        }

        $pipeline->delete();

        return response()->json(['status' => true, 'message' => 'Pipeline deleted successfully']);
    }
}
