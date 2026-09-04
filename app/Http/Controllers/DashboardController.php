<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Leadfollowup;
use App\Models\Order;
use App\Models\PaymentDetails;
use App\Models\Task;
use App\Models\User;
use App\Models\UserAttendance;
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
            ]);
        }

        return response()->json([
            'status' => true,
            'scope' => 'own',
            'data' => $this->ownData($user->id),
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
            'callLead' => Lead::where('lead_source', 'cold_call')->count(),
            'webLead' => Lead::where('lead_source', 'website')->count(),
            'facebook' => Lead::whereIn('lead_source', ['facebook_ads', 'linkedIn'])->count(),
            'tasksDueToday' => Task::whereDate('due_at', $today)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
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

    public function attendanceTeam()
    {
        $user = Auth::guard('web')->user();
        abort_unless($user->hasElevatedAccess() || $user->hasRole('HR'), 403);

        $today = Carbon::now('Asia/Kolkata')->toDateString();
        $latestIds = UserAttendance::whereDate('date', $today)
            ->selectRaw('MAX(id) as id')
            ->groupBy('user_id')
            ->pluck('id');

        // No SQL join here: user_attendance lives in the tenant database,
        // users lives in the master database — two separate physical
        // connections can't be joined in one query. Fetch each side
        // separately and merge in PHP instead.
        $records = UserAttendance::whereIn('id', $latestIds)
            ->orderBy('date', 'desc')
            ->get(['user_id', 'date as check_in', 'check_out']);

        $names = User::whereIn('id', $records->pluck('user_id'))->pluck('name', 'id');

        $data = $records->map(fn (UserAttendance $r) => [
            'name' => $names[$r->user_id] ?? 'Unknown',
            'user_id' => $r->user_id,
            'check_in' => $r->check_in,
            'check_out' => $r->check_out,
        ])->values();

        $totalUsers = User::where('tenant_id', TenantContext::id())->where('status', 'Active')->count();
        $checkedIn = $data->whereNull('check_out')->count();
        $checkedOut = $data->whereNotNull('check_out')->count();

        return response()->json([
            'status' => true,
            'data' => $data,
            'summary' => [
                'present' => $checkedIn,
                'checked_out' => $checkedOut,
                'not_marked' => max(0, $totalUsers - $data->count()),
                'total' => $totalUsers,
            ],
        ]);
    }

    public function attendanceStatus()
    {
        $userId = Auth::guard('web')->id();
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        $marked = UserAttendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->exists();

        return response()->json(['status' => true, 'attendanceMarked' => $marked]);
    }

    public function checkIn()
    {
        $userId = Auth::guard('web')->id();
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        $already = UserAttendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->exists();

        if ($already) {
            return response()->json(['status' => true, 'message' => 'Attendance already marked for today']);
        }

        UserAttendance::create([
            'user_id' => $userId,
            'date' => Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s'),
        ]);

        return response()->json(['status' => true, 'message' => 'Attendance registered']);
    }
}
