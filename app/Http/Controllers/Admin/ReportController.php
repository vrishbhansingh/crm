<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Order;
use App\Models\PaymentDetails;
use App\Models\User;
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
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($request->from ?: now()->subDays(29)->toDateString())->startOfDay();
        $to = Carbon::parse($request->to ?: now()->toDateString())->endOfDay();
        abort_if($from->diffInDays($to) > 730, 422, 'Report range cannot exceed two years.');

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
}
