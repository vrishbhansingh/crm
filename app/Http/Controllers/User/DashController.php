<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Leadfollowup;
use App\Models\Order;
use App\Models\PaymentDetails;
use Illuminate\Http\Request;
use App\Models\UserAttendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class DashController extends Controller
{
    //
    function index()
    {

        return view('user.dashboard');
    }
    function getAttendence()
    {
        $user = Auth::guard('user')->user();
        // Check against today's date in IST — the same timezone attendance is saved in.
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        $attendanceMarked = UserAttendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->exists();
        return response()->json([
            'success' => true,
            'attendanceMarked' => $attendanceMarked,
        ]);
    }

    function dashboard_data()
    {
        $today = Carbon::now('Asia/Kolkata')->toDateString();
        $userId =Auth::guard('user')->user()->id;

        $data = [];

        // ===================== LEADS =====================
        $data['my_leads'] = Lead::where('assigned_to', $userId)->count();

        $data['my_hot_leads'] = Lead::where('assigned_to', $userId)
            ->where('lead_type', 'hot')
            ->count();

        $data['today_followups'] = Leadfollowup::where('follow_up_date', $today)
            ->count();

        $data['overdue_followups'] = Lead::where('assigned_to', $userId)
            ->whereDate('follow_up_date', '<', now())
            ->where('lead_status', '!=', 'closed')
            ->count();

        // ===================== ORDERS =====================
        $data['my_orders'] = Order::where('user_id', $userId)->count();

        $data['active_orders'] = Order::where('user_id', $userId)
            ->where('status', 'Active')
            ->count();

        // ===================== PAYMENTS =====================
        $data['payment_collected'] = PaymentDetails::sum('paid_amount');

        $data['pending_payment'] = Order::where('user_id', $userId)
            ->sum('due_amount');

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}
