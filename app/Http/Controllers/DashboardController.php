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
use Illuminate\Support\Facades\DB;

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
                'followUps' => $this->teamFollowUps(),
                'closingSoon' => $this->dealsClosingSoon(),
                'pipeline' => $this->pipelineByStage(),
                'topPerformers' => $this->topPerformers(),
                'recentLeads' => $this->recentLeads(),
                'revenue' => $this->revenueOverview(),
                'leadSources' => $this->leadSources(),
            ]);
        }

        return response()->json([
            'status' => true,
            'scope' => 'own',
            'data' => $this->ownData($user->id),
            'followUps' => $this->userFollowUps($user->id),
            'pipeline' => $this->pipelineByStage($user->id),
            'recentLeads' => $this->recentLeads($user->id),
            'revenue' => $this->revenueOverview($user->id),
            'leadSources' => $this->leadSources($user->id),
        ]);
    }

    /**
     * Open-deal breakdown per pipeline stage (count + value), same
     * join/group shape as ReportController's $funnel query but all-time
     * and open-only — this feeds the dashboard's "Sales Pipeline" widget.
     */
    private function pipelineByStage(?int $ownerId = null): array
    {
        return Deal::query()
            ->join('pipeline_stages', 'pipeline_stages.id', '=', 'deals.stage_id')
            ->where('deals.status', 'open')
            ->when($ownerId, fn ($q) => $q->where('deals.owner_id', $ownerId))
            ->groupBy('pipeline_stages.id', 'pipeline_stages.name', 'pipeline_stages.color', 'pipeline_stages.sort_order')
            ->orderBy('pipeline_stages.sort_order')
            ->get([
                'pipeline_stages.name',
                'pipeline_stages.color',
                DB::raw('COUNT(deals.id) as deal_count'),
                DB::raw('COALESCE(SUM(deals.amount), 0) as deal_value'),
            ])
            ->map(fn ($row) => [
                'name' => $row->name,
                'color' => $row->color,
                'count' => (int) $row->deal_count,
                'value' => (float) $row->deal_value,
            ])
            ->all();
    }

    /**
     * Top 5 deal owners by won value. Deliberately does NOT join `deals` to
     * `users` in SQL — `deals` lives on the per-tenant `tenant` connection
     * while `users` only exists on the master connection (see
     * User::getConnectionName()), so a cross-connection join 42S02s. Owner
     * names/avatars are resolved with a separate User lookup instead.
     */
    private function topPerformers(int $limit = 5): array
    {
        $rows = Deal::query()
            ->whereNotNull('owner_id')
            ->groupBy('owner_id')
            ->orderByDesc('won_value')
            ->limit($limit)
            ->get([
                'owner_id',
                DB::raw('COUNT(id) as deals_count'),
                DB::raw("SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as won_count"),
                DB::raw("COALESCE(SUM(CASE WHEN status = 'won' THEN amount ELSE 0 END), 0) as won_value"),
            ])
            ->filter(fn ($row) => $row->deals_count > 0);

        $users = User::whereIn('id', $rows->pluck('owner_id'))->get()->keyBy('id');

        return $rows->map(function ($row) use ($users) {
            $performer = $users->get($row->owner_id);

            return [
                'id' => $row->owner_id,
                'name' => $performer?->name ?? 'Unassigned',
                'avatar' => $performer?->avatar ? asset($performer->avatar) : null,
                'phone' => $performer?->phone,
                'role' => $performer?->getRoleNames()->first(),
                'deals' => (int) $row->deals_count,
                'won' => (int) $row->won_count,
                'wonValue' => (float) $row->won_value,
            ];
        })->values()->all();
    }

    private function recentLeads(?int $userId = null, int $limit = 6): array
    {
        return Lead::query()
            ->when($userId, fn ($q) => $q->where('assigned_to', $userId))
            ->latest()
            ->limit($limit)
            ->get(['id', 'name', 'email', 'phone', 'lead_source', 'lead_status', 'created_at'])
            ->map(fn (Lead $lead) => [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'source' => $lead->lead_source ? ucfirst(str_replace('_', ' ', $lead->lead_source)) : 'Unknown',
                'status' => $lead->lead_status ? ucfirst(str_replace('_', ' ', $lead->lead_status)) : 'New',
                'created_at' => $lead->created_at?->format('d M Y'),
                'url' => route('leads.show', $lead->id),
            ])
            ->all();
    }

    /**
     * Booked revenue (orders) vs cash collected (payments) per month for
     * the trailing window — the monthly equivalent of ReportController's
     * daily $trend, feeding the dashboard's "Revenue Overview" bar chart.
     */
    private function revenueOverview(?int $userId = null, int $months = 6): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths($months - 1);

        $revenueByMonth = Order::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->where('invoice_date', '>=', $start)
            ->selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as ym, COALESCE(SUM(net_amount), 0) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $cashByMonth = PaymentDetails::query()
            ->whereHas('order', fn ($q) => $userId ? $q->where('user_id', $userId) : $q)
            ->where('payment_date', '>=', $start)
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as ym, COALESCE(SUM(payment_details.paid_amount), 0) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $months = collect(range($months - 1, 0))->map(fn ($i) => Carbon::now()->startOfMonth()->subMonths($i));

        return [
            'labels' => $months->map(fn ($m) => $m->format('M Y'))->all(),
            'revenue' => $months->map(fn ($m) => (float) ($revenueByMonth[$m->format('Y-m')] ?? 0))->all(),
            'cash' => $months->map(fn ($m) => (float) ($cashByMonth[$m->format('Y-m')] ?? 0))->all(),
        ];
    }

    private function leadSources(?int $userId = null): array
    {
        return Lead::query()
            ->when($userId, fn ($q) => $q->where('assigned_to', $userId))
            ->selectRaw("COALESCE(NULLIF(lead_source, ''), 'Unknown') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(fn ($row) => [
                'label' => ucfirst(str_replace('_', ' ', $row->label)),
                'total' => (int) $row->total,
            ])
            ->all();
    }

    private function teamData(): array
    {
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        return [
            'totalLead' => Lead::count(),
            'newLeadToday' => Lead::where('lead_type', 'new')
                ->whereDate('created_at', Carbon::today())
                ->count(),
            'newLeadYesterday' => Lead::where('lead_type', 'new')
                ->whereDate('created_at', Carbon::yesterday())
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
