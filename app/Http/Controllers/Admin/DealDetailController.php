<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealStageHistory;

/**
 * Deal Detail page (Phase 5) — mirrors the shape of LeadDetailController's
 * show/detail/timeline split for the unified Lead Detail page.
 */
class DealDetailController extends Controller
{
    public function show($id)
    {
        return view('deals.show', ['dealId' => $id]);
    }

    public function detail($id)
    {
        $deal = Deal::with(['pipeline', 'stage', 'owner', 'lead', 'order', 'lostReason', 'createdBy'])->findOrFail($id);

        return response()->json(['status' => true, 'data' => $deal]);
    }

    public function timeline($id)
    {
        Deal::findOrFail($id);

        $history = DealStageHistory::with(['fromStage:id,name', 'toStage:id,name', 'changedBy:id,name'])
            ->where('deal_id', $id)
            ->latest()
            ->get();

        return response()->json(['status' => true, 'data' => $history]);
    }
}
