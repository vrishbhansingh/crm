<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealStageHistory;
use App\Models\Task;
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
        $deal->load(['pipeline.stages', 'stage', 'owner', 'lead', 'company', 'contact', 'order', 'lostReason', 'createdBy']);

        return response()->json(['status' => true, 'data' => $deal]);
    }

    public function timeline($id)
    {
        $this->findVisibleDeal($id);

        $history = DealStageHistory::with(['fromStage:id,name', 'toStage:id,name', 'changedBy:id,name'])
            ->where('deal_id', $id)
            ->latest()
            ->get();

        // Kept separate from stage-history $data (the UI's "Stage Timeline"
        // widget reads that shape specifically) — these are the deal's
        // linked tasks/calls/meetings, for a companion "Activities" panel.
        $tasks = Task::with('assignee:id,name')
            ->where('related_type', 'deal')->where('related_id', $id)
            ->latest('id')
            ->get()
            ->map(fn (Task $task) => [
                'activity_type' => $task->activity_type,
                'title' => $task->title,
                'status' => $task->status,
                'assignee_name' => $task->assignee->name ?? 'Unassigned',
                'touched_at' => $task->completed_at ?? $task->due_at ?? $task->created_at,
            ]);

        return response()->json(['status' => true, 'data' => $history, 'tasks' => $tasks]);
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
