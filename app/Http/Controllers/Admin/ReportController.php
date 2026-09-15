<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\EmailCampaign;
use App\Models\Lead;
use App\Models\LeadIntegration;
use App\Models\LeadIntegrationLog;
use App\Models\Leadfollowup;
use App\Models\Order;
use App\Models\PaymentDetails;
use App\Models\Task;
use App\Models\User;
use App\Models\WhatsappAccount;
use App\Models\WhatsappCampaign;
use App\Models\WhatsappMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function data(Request $request)
    {
        [$from, $to] = $this->range($request);

        $leadQuery = $this->leadQuery()->whereBetween('created_at', [$from, $to]);
        $dealQuery = $this->dealQuery()->whereBetween('deals.created_at', [$from, $to]);
        $closedQuery = $this->dealQuery()->whereBetween('closed_at', [$from, $to]);
        $orderQuery = $this->orderQuery()->whereBetween('invoice_date', [$from, $to]);
        $paymentQuery = $this->paymentQuery()->whereBetween('payment_date', [$from, $to]);

        $wonCount = (clone $closedQuery)->where('deals.status', 'won')->count();
        $lostCount = (clone $closedQuery)->where('deals.status', 'lost')->count();
        $closedCount = $wonCount + $lostCount;

        $summary = [
            'leads_created' => (clone $leadQuery)->count(),
            'deals_created' => (clone $dealQuery)->count(),
            'won_deals' => $wonCount,
            'win_rate' => $closedCount ? round(($wonCount / $closedCount) * 100, 1) : 0,
            'pipeline_value' => (float) $this->dealQuery()->where('deals.status', 'open')->sum('amount'),
            'booked_revenue' => (float) (clone $orderQuery)->sum('net_amount'),
            'cash_collected' => (float) (clone $paymentQuery)->sum('payment_details.paid_amount'),
        ];

        $funnel = $this->dealQuery()
            ->join('pipeline_stages', 'pipeline_stages.id', '=', 'deals.stage_id')
            ->whereBetween('deals.created_at', [$from, $to])
            ->groupBy('pipeline_stages.id', 'pipeline_stages.name', 'pipeline_stages.color', 'pipeline_stages.sort_order')
            ->orderBy('pipeline_stages.sort_order')
            ->get([
                'pipeline_stages.name', 'pipeline_stages.color',
                DB::raw('COUNT(deals.id) as total'), DB::raw('COALESCE(SUM(deals.amount), 0) as value'),
            ]);

        $sources = (clone $leadQuery)
            ->selectRaw("COALESCE(NULLIF(lead_source, ''), 'Unknown') as label, COUNT(*) as total")
            ->groupBy('label')->orderByDesc('total')->limit(10)->get();

        // Deals live on the tenant connection, users on the master
        // connection — a SQL join across them isn't possible, so aggregate
        // on deals alone and resolve owner names separately (same fix as
        // DashboardController::topPerformers()).
        $ownerRows = $this->dealQuery()
            ->whereBetween('deals.created_at', [$from, $to])
            ->groupBy('owner_id')
            ->orderByDesc('won_value')
            ->get([
                'owner_id',
                DB::raw('COUNT(deals.id) as deals'),
                DB::raw("SUM(CASE WHEN deals.status = 'won' THEN 1 ELSE 0 END) as won"),
                DB::raw("COALESCE(SUM(CASE WHEN deals.status = 'won' THEN deals.amount ELSE 0 END), 0) as won_value"),
            ]);

        $ownerNames = User::whereIn('id', $ownerRows->pluck('owner_id')->filter())->get()->keyBy('id');
        $owners = $ownerRows->map(fn ($row) => [
            'name' => $ownerNames->get($row->owner_id)?->name ?? 'Unassigned',
            'deals' => $row->deals,
            'won' => $row->won,
            'won_value' => $row->won_value,
        ]);

        $revenueByDay = (clone $orderQuery)
            ->selectRaw('DATE(invoice_date) as day, COALESCE(SUM(net_amount), 0) as total')
            ->groupBy('day')->orderBy('day')->pluck('total', 'day');
        $cashByDay = (clone $paymentQuery)
            ->selectRaw('DATE(payment_date) as day, COALESCE(SUM(payment_details.paid_amount), 0) as total')
            ->groupBy('day')->orderBy('day')->pluck('total', 'day');
        $days = collect($revenueByDay->keys())->merge($cashByDay->keys())->unique()->sort()->values();

        // Monthly Summary — same booked-revenue / cash-collected figures as
        // the trend chart above, just bucketed by month instead of by day,
        // plus month-over-month revenue growth. No "expenses" column here:
        // this app doesn't track expenses, so showing one would mean making
        // up a number rather than reporting real data.
        $revenueByMonth = (clone $orderQuery)
            ->selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as ym, COALESCE(SUM(net_amount), 0) as total")
            ->groupBy('ym')->orderBy('ym')->pluck('total', 'ym');
        $cashByMonth = (clone $paymentQuery)
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as ym, COALESCE(SUM(payment_details.paid_amount), 0) as total")
            ->groupBy('ym')->orderBy('ym')->pluck('total', 'ym');
        $months = collect($revenueByMonth->keys())->merge($cashByMonth->keys())->unique()->sort()->values();

        $monthlySummary = $months->map(function ($ym, $i) use ($revenueByMonth, $cashByMonth, $months) {
            $revenue = (float) ($revenueByMonth[$ym] ?? 0);
            $cash = (float) ($cashByMonth[$ym] ?? 0);
            $prevYm = $i > 0 ? $months[$i - 1] : null;
            $prevRevenue = $prevYm ? (float) ($revenueByMonth[$prevYm] ?? 0) : null;
            $growth = ($prevRevenue !== null && $prevRevenue > 0)
                ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1)
                : null;

            return [
                'month' => Carbon::createFromFormat('Y-m', $ym)->format('F Y'),
                'revenue' => $revenue,
                'cash_collected' => $cash,
                'outstanding' => max($revenue - $cash, 0),
                'growth' => $growth,
            ];
        })->reverse()->values();

        return response()->json([
            'summary' => $summary,
            'funnel' => $funnel,
            'sources' => $sources,
            'owners' => $owners,
            'monthlySummary' => $monthlySummary,
            'trend' => [
                'labels' => $days,
                'revenue' => $days->map(fn ($day) => (float) ($revenueByDay[$day] ?? 0)),
                'cash' => $days->map(fn ($day) => (float) ($cashByDay[$day] ?? 0)),
            ],
        ]);
    }

    /**
     * Leads Report — status funnel, source performance (leads vs. actually
     * converted, not just raw count), priority mix, and how long converted
     * leads in range took to close, so a manager can see which sources
     * bring leads that actually turn into business rather than just volume.
     */
    public function leads(Request $request)
    {
        [$from, $to] = $this->range($request);
        $leadQuery = $this->leadQuery()->whereBetween('created_at', [$from, $to]);

        $byStatus = (clone $leadQuery)
            ->selectRaw("COALESCE(NULLIF(lead_status, ''), 'new') as label, COUNT(*) as total")
            ->groupBy('label')->orderByDesc('total')->get();

        $byPriority = (clone $leadQuery)
            ->selectRaw("COALESCE(NULLIF(priority, ''), 'medium') as label, COUNT(*) as total")
            ->groupBy('label')->orderByDesc('total')->get();

        $bySource = (clone $leadQuery)
            ->selectRaw("COALESCE(NULLIF(lead_source, ''), 'Unknown') as label, COUNT(*) as total, SUM(CASE WHEN is_converted = 'Yes' THEN 1 ELSE 0 END) as converted")
            ->groupBy('label')->orderByDesc('total')->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'total' => (int) $row->total,
                'converted' => (int) $row->converted,
                'rate' => $row->total ? round(($row->converted / $row->total) * 100, 1) : 0,
            ]);

        $converted = (clone $leadQuery)->where('is_converted', 'Yes')->whereNotNull('converted_at')->get(['created_at', 'converted_at']);
        $avgDaysToConvert = $converted->isEmpty() ? null : round(
            $converted->avg(fn ($lead) => Carbon::parse($lead->created_at)->diffInDays(Carbon::parse($lead->converted_at))), 1
        );

        // Leads sitting with no next follow-up scheduled and no activity —
        // easy to lose track of otherwise.
        $stale = (clone $leadQuery)
            ->whereNull('follow_up_date')
            ->where('is_converted', 'No')
            ->whereNotIn('lead_status', ['closed', 'converted'])
            ->count();

        return response()->json([
            'total' => (clone $leadQuery)->count(),
            'converted' => (clone $leadQuery)->where('is_converted', 'Yes')->count(),
            'avg_days_to_convert' => $avgDaysToConvert,
            'stale' => $stale,
            'by_status' => $byStatus,
            'by_priority' => $byPriority,
            'by_source' => $bySource,
        ]);
    }

    /**
     * Follow-ups Report — Leadfollowup rows are logged/completed follow-up
     * interactions (a call actually made), while Lead.follow_up_date is the
     * next one still scheduled. Reporting both together is what turns
     * "follow-up discipline" from a feeling into a number per agent.
     */
    public function followUps(Request $request)
    {
        [$from, $to] = $this->range($request);

        $completed = $this->followUpLogQuery()->whereBetween('created_at', [$from, $to]);
        $scheduled = $this->leadQuery()->whereNotNull('follow_up_date')->whereBetween('follow_up_date', [$from, $to]);
        $overdue = $this->leadQuery()
            ->whereNotNull('follow_up_date')
            ->where('follow_up_date', '<', now()->toDateString())
            ->where('is_converted', 'No')
            ->whereNotIn('lead_status', ['closed', 'converted']);

        $completedRows = (clone $completed)->get(['user_id']);
        $scheduledRows = (clone $scheduled)->get(['assigned_to']);
        $overdueRows = (clone $overdue)->get(['assigned_to']);

        $userIds = $completedRows->pluck('user_id')
            ->merge($scheduledRows->pluck('assigned_to'))
            ->merge($overdueRows->pluck('assigned_to'))
            ->filter()->unique();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $perAgent = $userIds->map(function ($id) use ($users, $completedRows, $scheduledRows, $overdueRows) {
            return [
                'name' => $users->get($id)?->name ?? 'Unknown',
                'completed' => $completedRows->where('user_id', $id)->count(),
                'scheduled' => $scheduledRows->where('assigned_to', $id)->count(),
                'overdue' => $overdueRows->where('assigned_to', $id)->count(),
            ];
        })->sortByDesc('completed')->values();

        return response()->json([
            'completed' => $completedRows->count(),
            'scheduled' => $scheduledRows->count(),
            'overdue' => $overdueRows->count(),
            'by_agent' => $perAgent,
        ]);
    }

    /**
     * Agent-wise Report — one row per team member: leads handled and
     * converted, deals won, follow-ups logged, task completion — the
     * "sales activity report" a manager actually reviews per rep.
     */
    public function agents(Request $request)
    {
        [$from, $to] = $this->range($request);

        $agents = $this->agentScopedUsers();

        $leads = $this->leadQuery()->whereBetween('created_at', [$from, $to])->get(['assigned_to', 'is_converted']);
        $deals = $this->dealQuery()->whereBetween('deals.created_at', [$from, $to])->get(['owner_id', 'status', 'amount']);
        $followUps = $this->followUpLogQuery()->whereBetween('created_at', [$from, $to])->get(['user_id']);
        $tasks = $this->taskQuery()->whereBetween('created_at', [$from, $to])->get(['assigned_to', 'status', 'due_at']);

        $rows = $agents->map(function (User $agent) use ($leads, $deals, $followUps, $tasks) {
            $agentLeads = $leads->where('assigned_to', $agent->id);
            $agentDeals = $deals->where('owner_id', $agent->id);
            $agentTasks = $tasks->where('assigned_to', $agent->id);
            $wonDeals = $agentDeals->where('status', 'won');
            $overdueTasks = $agentTasks->filter(fn ($t) => ! in_array($t->status, ['completed', 'cancelled']) && $t->due_at && Carbon::parse($t->due_at)->isPast());

            return [
                'name' => $agent->name,
                'leads' => $agentLeads->count(),
                'leads_converted' => $agentLeads->where('is_converted', 'Yes')->count(),
                'deals' => $agentDeals->count(),
                'deals_won' => $wonDeals->count(),
                'won_value' => (float) $wonDeals->sum('amount'),
                'followups_logged' => $followUps->where('user_id', $agent->id)->count(),
                'tasks_completed' => $agentTasks->where('status', 'completed')->count(),
                'tasks_overdue' => $overdueTasks->count(),
            ];
        })->sortByDesc('won_value')->values();

        return response()->json(['agents' => $rows]);
    }

    /**
     * Communications Report — email + WhatsApp send volume/outcomes and a
     * combined "most recent contact" feed, since neither channel has a
     * single unified place showing what actually went out and whether it
     * landed.
     */
    public function communications(Request $request)
    {
        [$from, $to] = $this->range($request);

        $emailCampaigns = EmailCampaign::whereBetween('created_at', [$from, $to])->orderByDesc('id')->limit(20)->get()
            ->map(fn ($c) => [
                'name' => $c->name, 'status' => $c->status, 'recipients' => $c->total_recipients,
                'sent' => $c->sent_count, 'failed' => $c->failed_count, 'sent_at' => $c->sent_at,
            ]);
        $emailTotals = [
            'campaigns' => EmailCampaign::whereBetween('created_at', [$from, $to])->count(),
            'sent' => (int) EmailCampaign::whereBetween('created_at', [$from, $to])->sum('sent_count'),
            'failed' => (int) EmailCampaign::whereBetween('created_at', [$from, $to])->sum('failed_count'),
        ];

        $waCampaigns = WhatsappCampaign::whereBetween('created_at', [$from, $to])->orderByDesc('id')->limit(20)->get()
            ->map(fn ($c) => [
                'name' => $c->name, 'status' => $c->status, 'recipients' => $c->total_recipients,
                'sent' => $c->sent_count, 'failed' => $c->failed_count, 'sent_at' => $c->sent_at,
            ]);

        $waMessages = WhatsappMessage::whereBetween('created_at', [$from, $to]);
        $waByStatus = (clone $waMessages)
            ->selectRaw('status, direction, COUNT(*) as total')
            ->groupBy('status', 'direction')->get();

        $waAccounts = WhatsappAccount::where('tenant_id', $this->tenantId())->get(['name', 'channel_type', 'messages_sent_count', 'messages_received_count']);

        return response()->json([
            'email' => ['totals' => $emailTotals, 'campaigns' => $emailCampaigns],
            'whatsapp' => [
                'totals' => [
                    'campaigns' => WhatsappCampaign::whereBetween('created_at', [$from, $to])->count(),
                    'sent' => (clone $waMessages)->where('direction', 'out')->whereIn('status', ['sent', 'delivered', 'read'])->count(),
                    'failed' => (clone $waMessages)->where('direction', 'out')->where('status', 'failed')->count(),
                    'received' => (clone $waMessages)->where('direction', 'in')->count(),
                ],
                'campaigns' => $waCampaigns,
                'by_status' => $waByStatus,
                'accounts' => $waAccounts,
            ],
        ]);
    }

    /**
     * Automation Report — everything the system did on its own: webhook
     * leads captured per platform, reminder notifications fired, and
     * scheduled campaigns that dispatched without a human clicking Send.
     */
    public function automation(Request $request)
    {
        [$from, $to] = $this->range($request);
        $tenantId = $this->tenantId();

        $webhookLogs = LeadIntegrationLog::where('tenant_id', $tenantId)->whereBetween('created_at', [$from, $to]);
        $byPlatform = LeadIntegration::where('tenant_id', $tenantId)
            ->withCount(['logs as created_count' => fn ($q) => $q->whereBetween('created_at', [$from, $to])->where('status', 'created')])
            ->get(['id', 'platform', 'name'])
            ->groupBy('platform')
            ->map(fn ($group, $platform) => [
                'platform' => $platform,
                'integrations' => $group->count(),
                'leads_created' => $group->sum('created_count'),
            ])->values();

        $webhookByStatus = (clone $webhookLogs)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        $taskRemindersSent = $this->taskQuery()->whereNotNull('notification_sent_at')->whereBetween('notification_sent_at', [$from, $to])->count();
        $leadRemindersSent = $this->leadQuery()->whereNotNull('follow_up_notified_at')->whereBetween('follow_up_notified_at', [$from, $to])->count();

        $scheduledEmailsSent = EmailCampaign::whereNotNull('scheduled_at')->where('status', 'sent')->whereBetween('sent_at', [$from, $to])->count();
        $scheduledWaSent = WhatsappCampaign::whereNotNull('scheduled_at')->where('status', 'sent')->whereBetween('sent_at', [$from, $to])->count();

        return response()->json([
            'webhook_leads_created' => (int) ($webhookByStatus['created'] ?? 0),
            'webhook_duplicates_ignored' => (int) ($webhookByStatus['duplicate'] ?? 0) + (int) ($webhookByStatus['ignored'] ?? 0),
            'webhook_failed' => (int) ($webhookByStatus['failed'] ?? 0),
            'by_platform' => $byPlatform,
            'reminders_sent' => $taskRemindersSent + $leadRemindersSent,
            'task_reminders_sent' => $taskRemindersSent,
            'lead_reminders_sent' => $leadRemindersSent,
            'scheduled_campaigns_sent' => $scheduledEmailsSent + $scheduledWaSent,
        ]);
    }

    private function range(Request $request): array
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($request->from ?: now()->subDays(29)->toDateString())->startOfDay();
        $to = Carbon::parse($request->to ?: now()->toDateString())->endOfDay();
        abort_if($from->diffInDays($to) > 730, 422, 'Report range cannot exceed two years.');

        return [$from, $to];
    }

    private function tenantId(): ?int
    {
        return Auth::guard('web')->user()?->tenant_id;
    }

    private function leadQuery()
    {
        $query = Lead::query();
        $user = Auth::guard('web')->user();
        if (! $user->hasElevatedAccess() && ! $user->hasRole('Marketing')) {
            $query->where('assigned_to', $user->id);
        }

        return $query;
    }

    private function dealQuery()
    {
        $query = Deal::query();
        $user = Auth::guard('web')->user();
        if (! $user->hasElevatedAccess() && ! $user->hasRole('Finance')) {
            $query->where('owner_id', $user->id);
        }

        return $query;
    }

    private function orderQuery()
    {
        $query = Order::query();
        $user = Auth::guard('web')->user();
        if (! $user->hasElevatedAccess() && ! $user->hasRole('Finance')) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function paymentQuery()
    {
        $query = PaymentDetails::query();
        $user = Auth::guard('web')->user();
        if (! $user->hasElevatedAccess() && ! $user->hasRole('Finance')) {
            $query->whereHas('order', fn ($order) => $order->where('user_id', $user->id));
        }

        return $query;
    }

    private function taskQuery()
    {
        $query = Task::query();
        $user = Auth::guard('web')->user();
        if (! $user->hasElevatedAccess()) {
            $query->where(fn ($q) => $q->where('assigned_to', $user->id)->orWhere('created_by', $user->id));
        }

        return $query;
    }

    private function followUpLogQuery()
    {
        $query = Leadfollowup::query();
        $user = Auth::guard('web')->user();
        if (! $user->hasElevatedAccess() && ! $user->hasRole('Marketing')) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    /**
     * Every active agent this report's viewer is allowed to see — elevated
     * roles see the whole team, everyone else only sees themselves (same
     * scoping rule as the other *Query() helpers, just for users instead
     * of a tenant-scoped model).
     */
    private function agentScopedUsers()
    {
        $user = Auth::guard('web')->user();
        $query = User::where('tenant_id', $user->tenant_id)->where('status', 'Active');
        if (! $user->hasElevatedAccess()) {
            $query->where('id', $user->id);
        }

        return $query->get();
    }
}
