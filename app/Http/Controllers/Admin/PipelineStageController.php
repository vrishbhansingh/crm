<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Http\Request;

class PipelineStageController extends Controller
{
    public function data($pipelineId)
    {
        $pipeline = Pipeline::findOrFail($pipelineId);
        $stages = $pipeline->stages()->withCount('deals')->get();

        return response()->json(['data' => $stages]);
    }

    public function store(Request $request, $pipelineId)
    {
        $pipeline = Pipeline::findOrFail($pipelineId);

        $request->validate([
            'name' => 'required|string|max:150',
            'sort_order' => 'nullable|integer',
            'is_won' => 'nullable|boolean',
            'is_lost' => 'nullable|boolean',
            'probability' => 'nullable|integer|min:0|max:100',
            'color' => 'nullable|string|max:20',
        ]);

        $pipeline->stages()->create([
            'tenant_id' => $pipeline->tenant_id,
            'name' => $request->name,
            'sort_order' => $request->sort_order ?? 0,
            'is_won' => $request->boolean('is_won'),
            'is_lost' => $request->boolean('is_lost'),
            'probability' => $request->probability,
            'color' => $request->color,
        ]);

        return response()->json(['status' => true, 'message' => 'Stage added successfully']);
    }

    public function update(Request $request, $id)
    {
        $stage = PipelineStage::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:150',
            'sort_order' => 'nullable|integer',
            'is_won' => 'nullable|boolean',
            'is_lost' => 'nullable|boolean',
            'probability' => 'nullable|integer|min:0|max:100',
            'color' => 'nullable|string|max:20',
        ]);

        $stage->name = $request->name;
        $stage->is_won = $request->boolean('is_won');
        $stage->is_lost = $request->boolean('is_lost');
        $stage->probability = $request->probability;
        $stage->color = $request->color;
        if ($request->filled('sort_order')) {
            $stage->sort_order = $request->sort_order;
        }
        $stage->save();

        return response()->json(['status' => true, 'message' => 'Stage updated successfully']);
    }

    public function destroy($id)
    {
        $stage = PipelineStage::findOrFail($id);

        if ($stage->deals()->exists()) {
            return response()->json(['status' => false, 'message' => 'This stage has deals in it and cannot be deleted'], 422);
        }

        $stage->delete();

        return response()->json(['status' => true, 'message' => 'Stage deleted successfully']);
    }
}
