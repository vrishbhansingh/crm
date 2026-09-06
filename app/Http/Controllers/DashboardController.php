<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Deal;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\Leadfollowup;
use App\Models\Order;
use App\Models\PaymentDetails;
use App\Models\Task;
use App\Models\User;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * One dashboard for every role (unified interface, increment 1) replacing
 * the separate Admin\DashboardController/User\DashController pair. Widget
 * set branches on User::hasElevatedAccess() instead of which folder you're in.
 */
class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function data()
    {
        $user = Auth::guard('web')->user();

        if ($user->hasElevatedAccess()) {
            return response()->json([
                'status' => true,
                'scope' => 'team',
                'data' => $this->teamData(),
                'charts' => $this->charts(),
                'followUps' => $this->teamFollowUps(),
                'closingSoon' => $this->dealsClosingSoon(),
            ]);
        }

        return response()->json([
            'status' => true,
            'scope' => 'own',
            'data' => $this->ownData($user->id),
            'followUps' => $this->userFollowUps($user->id),
        ]);
    }

    private function teamData(): array
    {
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        return [
            'totalLead' => Lead::count(),
            'newLeadToday' => Lead::where('lead_type', 'new')
                ->whereDate('created_at', Carbon::today())
                ->count(),
            'hotLead' => Lead::where('lead_type', 'hot')->count(),
            'webLead' => Lead::where('lead_source', 'website')->count(),
            'tasksDueToday' => Task::whereDate('due_at', $today)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'openDeals' => Deal::where('status', 'open')->count(),
            'pipelineValue' => (float) Deal::where('status', 'open')->sum('amount'),
            'totalCompanies' => Company::count(),
            'activeCampaigns' => EmailCampaign::whereIn('status', ['scheduled', 'sending'])->count(),
            'totalTemplates' => EmailTemplate::count(),
            'totalUsers' => User::where('tenant_id', TenantContext::id())->where('status', 'Active')->count(),
        ];
    }

    /**
     * Same widgets User\DashController used to compute, with three bugs
     * fixed while moving the logic: today_followups and payment_collected
     * were unscoped (counted/summed every user's data), and active_orders
     * filtered on the wrong column (`status` instead of `order_status`).
     */
    private function ownData(int $userId): array
    {
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        return [
            'my_leads' => Lead::where('assigned_to', $userId)->count(),
            'my_hot_leads' => Lead::where('assigned_to', $userId)->where('lead_type', 'hot')->count(),
            'today_followups' => Leadfollowup::where('user_id', $userId)
                ->where('follow_up_date', $today)
                ->count(),
            'overdue_followups' => Lead::where('assigned_to', $userId)
                ->whereDate('follow_up_date', '<', now())
                ->where('lead_status', '!=', 'closed')
                ->count(),
            'my_orders' => Order::where('user_id', $userId)->count(),
            'active_orders' => Order::where('user_id', $userId)
                ->whereNotIn('order_status', ['closed', 'cancelled', 'delivered'])
                ->count(),
            'payment_collected' => PaymentDetails::whereHas(
                'order',
                fn ($q) => $q->where('user_id', $userId)
            )->sum('paid_amount'),
            'pending_payment' => Order::where('user_id', $userId)->sum('due_amount'),
        ];
    }

    /**
     * Chart.js feeds for the team dashboard — reuses the same library and
     * `new Chart(...)` pattern already used on the Reports page rather than
     * introducing a second charting library.
     */
    private function charts(): array
    {
        $days = collect(range(13, 0))->map(fn ($i) => Carbon::today()->subDays($i));

        $leadsByDay = Lead::selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->where('created_at', '>=', Carbon::today()->subDays(13))
            ->groupBy('d')
            ->pluck('c', 'd');

        $leadsTrend = [
            'labels' => $days->map(fn ($d) => $d->format('d M'))->all(),
            'data' => $days->map(fn ($d) => (int) ($leadsByDay[$d->toDateString()] ?? 0))->all(),
        ];

        $leadsByStatus = Lead::selectRaw('lead_status, COUNT(*) as c')
            ->groupBy('lead_status')
            ->orderByDesc('c')
            ->get();

        $dealsByStage = Deal::with('stage:id,name')
            ->selectRaw('stage_id, COUNT(*) as c')
            ->groupBy('stage_id')
            ->get()
            ->sortByDesc('c');

        return [
            'leadsTrend' => $leadsTrend,
            'leadsByStatus' => [
                'labels' => $leadsByStatus->pluck('lead_status')->map(fn ($s) => ucfirst(str_replace('_', ' ', $s)))->all(),
                'data' => $leadsByStatus->pluck('c')->all(),
            ],
            'dealsByStage' => [
                'labels' => $dealsByStage->map(fn ($d) => $d->stage?->name ?? 'No stage')->all(),
                'data' => $dealsByStage->pluck('c')->all(),
            ],
        ];
    }

    /**
     * A single merged, sorted feed of what's coming up across the two
     * existing-but-separate reminder mechanisms: Lead.follow_up_date (set
     * directly on the lead) and Task.due_at/remind_at (the general-purpose
     * reminder system, already notification-backed, that can point at a
     * lead, a deal, or anything else via related_type/related_id).
     */
    private function teamFollowUps(): array
    {
        $leadFollowUps = Lead::whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '>=', Carbon::today()->subDays(3))
            ->whereDate('follow_up_date', '<=', Carbon::today()->addDays(7))
            ->where('lead_status', '!=', 'closed')
            ->orderBy('follow_up_date')
            ->limit(15)
            ->get(['id', 'name', 'follow_up_date', 'follow_up_time', 'assigned_to'])
            ->map(fn (Lead $lead) => $this->leadFollowUpEntry($lead));

        $taskFollowUps = Task::whereNotNull('due_at')
            ->where('due_at', '>=', Carbon::now()->subDays(3))
            ->where('due_at', '<=', Carbon::now()->addDays(7))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereIn('related_type', ['lead', 'deal'])
            ->orderBy('due_at')
            ->limit(15)
            ->get(['id', 'title', 'due_at', 'related_type', 'related_id'])
            ->map(fn (Task $task) => [
                'type' => $task->related_type,
                'id' => $task->related_id,
                'title' => $task->title,
                'when' => $task->due_at?->format('Y-m-d H:i'),
                'overdue' => $task->due_at && $task->due_at->isPast(),
                'url' => $task->related_type === 'deal' ? route('deals.show', $task->related_id) : route('leads.show', $task->related_id),
            ]);

        return $leadFollowUps->concat($taskFollowUps)
            ->sortBy('when')
            ->values()
            ->take(10)
            ->all();
    }

    private function dealsClosingSoon(): array
    {
        return Deal::where('status', 'open')
            ->whereNotNull('expected_close_date')
            ->whereDate('expected_close_date', '<=', Carbon::today()->addDays(14))
            ->orderBy('expected_close_date')
            ->limit(8)
            ->get(['id', 'name', 'amount', 'currency', 'expected_close_date'])
            ->map(function (Deal $deal) {
                // expected_close_date isn't cast to Carbon on the model
                // (it's a plain date-typed column read back as a string),
                // so it's parsed locally here rather than widening that
                // cast app-wide and risking other, un-audited call sites
                // that already assume a raw string.
                $closeDate = Carbon::parse($deal->expected_close_date);

                return [
                    'id' => $deal->id,
                    'name' => $deal->name,
                    'amount' => (float) $deal->amount,
                    'currency' => $deal->currency,
                    'expected_close_date' => $closeDate->format('d M Y'),
                    'overdue' => $closeDate->isPast() && ! $closeDate->isToday(),
                    'url' => route('deals.show', $deal->id),
                ];
            })
            ->all();
    }

    /**
     * follow_up_date/follow_up_time aren't cast to Carbon on the Lead model
     * (read back as plain strings), so they're parsed locally here rather
     * than widening that cast app-wide and risking other, un-audited call
     * sites that already assume raw strings.
     */
    private function leadFollowUpEntry(Lead $lead): array
    {
        $followUpAt = Carbon::parse($lead->follow_up_date.($lead->follow_up_time ? ' '.$lead->follow_up_time : ''));

        return [
            'type' => 'lead',
            'id' => $lead->id,
            'title' => $lead->name,
            'when' => $followUpAt->format('Y-m-d').($lead->follow_up_time ? ' '.$lead->follow_up_time : ''),
            'overdue' => $followUpAt->isPast() && ! $followUpAt->isToday(),
            'url' => route('leads.show', $lead->id),
        ];
    }

    private function userFollowUps(int $userId): array
    {
        $leadFollowUps = Lead::where('assigned_to', $userId)
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '>=', Carbon::today()->subDays(3))
            ->whereDate('follow_up_date', '<=', Carbon::today()->addDays(7))
            ->where('lead_status', '!=', 'closed')
            ->orderBy('follow_up_date')
            ->limit(10)
            ->get(['id', 'name', 'follow_up_date', 'follow_up_time'])
            ->map(fn (Lead $lead) => $this->leadFollowUpEntry($lead));

        $taskFollowUps = Task::where(fn ($q) => $q->where('assigned_to', $userId)->orWhere('created_by', $userId))
            ->whereNotNull('due_at')
            ->where('due_at', '>=', Carbon::now()->subDays(3))
            ->where('due_at', '<=', Carbon::now()->addDays(7))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereIn('related_type', ['lead', 'deal'])
            ->orderBy('due_at')
            ->limit(10)
            ->get(['id', 'title', 'due_at', 'related_type', 'related_id'])
            ->map(fn (Task $task) => [
                'type' => $task->related_type,
                'id' => $task->related_id,
                'title' => $task->title,
                'when' => $task->due_at?->format('Y-m-d H:i'),
                'overdue' => $task->due_at && $task->due_at->isPast(),
                'url' => $task->related_type === 'deal' ? route('deals.show', $task->related_id) : route('leads.show', $task->related_id),
            ]);

        return $leadFollowUps->concat($taskFollowUps)
            ->sortBy('when')
            ->values()
            ->take(10)
            ->all();
    }
}
