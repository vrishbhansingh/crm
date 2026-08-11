<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealStageHistory;
use Illuminate\Support\Facades\Auth;

/**
 * Deal Detail page (Phase 5) — mirrors the shape of LeadDetailController's
 * show/detail/timeline split for the unified Lead Detail page.
 */
class DealDetailController extends Controller
{
    public function show($id)
    {
        $this->findVisibleDeal($id);

        return view('deals.show', ['dealId' => $id]);
    }

    public function detail($id)
    {
        $deal = $this->findVisibleDeal($id);
        $deal->load(['pipeline', 'stage', 'owner', 'lead', 'company', 'contact', 'order', 'lostReason', 'createdBy']);

        return response()->json(['status' => true, 'data' => $deal]);
    }

    public function timeline($id)
    {
        $this->findVisibleDeal($id);

        $history = DealStageHistory::with(['fromStage:id,name', 'toStage:id,name', 'changedBy:id,name'])
            ->where('deal_id', $id)
            ->latest()
            ->get();

        return response()->json(['status' => true, 'data' => $history]);
    }

    private function findVisibleDeal(int $id): Deal
    {
        $user = Auth::guard('web')->user();
        $query = Deal::query();

        if (! $user->hasElevatedAccess() && ! $user->hasAnyRole(['Finance', 'Support'])) {
            $query->where('owner_id', $user->id);
        }

        return $query->findOrFail($id);
    }
}
