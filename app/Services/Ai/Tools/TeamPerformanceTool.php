<?php

namespace App\Services\Ai\Tools;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;

/**
 * The admin-level question: "how is my team doing". Never offered to an
 * agent — not by permission alone (an Agent role could be given
 * reports.view) but by elevatedOnly(), because it exposes colleagues'
 * numbers.
 */
class TeamPerformanceTool extends CrmTool
{
    public function name(): string
    {
        return 'team_performance';
    }

    public function description(): string
    {
        return 'Per-team-member workload and results: leads assigned, leads converted, open deals and their value, won deals, and overdue tasks. Admin/manager only.';
    }

    public function permission(): string
    {
        return 'reports.view';
    }

    public function elevatedOnly(): bool
    {
        return true;
    }

    public function schema(): array
    {
        return ['type' => 'object', 'properties' => []];
    }

    public function run(User $user, array $input): array
    {
        $members = User::where('tenant_id', $user->tenant_id)->where('status', 'Active')->orderBy('name')->get(['id', 'name']);

        $leads = Lead::selectRaw('assigned_to, COUNT(*) as total, SUM(is_converted = "Yes") as converted')->groupBy('assigned_to')->get()->keyBy('assigned_to');
        $openDeals = Deal::where('status', 'open')->selectRaw('owner_id, COUNT(*) as c, SUM(amount) as v')->groupBy('owner_id')->get()->keyBy('owner_id');
        $wonDeals = Deal::where('status', 'won')->selectRaw('owner_id, COUNT(*) as c, SUM(amount) as v')->groupBy('owner_id')->get()->keyBy('owner_id');
        $overdue = Task::whereNotIn('status', ['completed', 'cancelled'])->whereNotNull('due_at')->where('due_at', '<', now())
            ->selectRaw('assigned_to, COUNT(*) as c')->groupBy('assigned_to')->pluck('c', 'assigned_to');

        return ['team' => $members->map(fn ($m) => [
            'name' => $m->name,
            'leads_assigned' => (int) ($leads[$m->id]->total ?? 0),
            'leads_converted' => (int) ($leads[$m->id]->converted ?? 0),
            'open_deals' => (int) ($openDeals[$m->id]->c ?? 0),
            'open_deal_value' => (float) ($openDeals[$m->id]->v ?? 0),
            'won_deals' => (int) ($wonDeals[$m->id]->c ?? 0),
            'won_deal_value' => (float) ($wonDeals[$m->id]->v ?? 0),
            'overdue_tasks' => (int) ($overdue[$m->id] ?? 0),
        ])->all(), 'unassigned_leads' => (int) ($leads->first(fn ($row) => $row->assigned_to === null)->total ?? 0)];
    }
}
