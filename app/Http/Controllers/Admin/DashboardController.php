<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\UserAttendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    //
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function getDashboardData(Request $request)
    {
        $totalLead = Lead::count();
        $newLeadToday = Lead::where('lead_type', 'new')
            ->whereDate('created_at', Carbon::today())
            ->count();
        $hotLead = Lead::where('lead_type', 'hot')->count();
        $callLead = Lead::where('lead_source', 'cold_Call')->count();
        $webLead = Lead::where('lead_source', 'website')->count();
        $facebookLead = Lead::whereIn('lead_source', [
            'facebook_ads',
            'linkedin'
        ])->count();
        return response()->json([
            'status' => true,
            'totalLead' => $totalLead,
            'newLeadToday' => $newLeadToday,
            'callLead' => $callLead,
            'hotLead' => $hotLead,
            'facebook' => $facebookLead,
            'webLead' => $webLead,
        ]);
    }

    public function getAttendance()
    {
        // Latest attendance row per user (check-in = date, plus check_out if logged out).
        $latestIds = UserAttendance::selectRaw('MAX(id) as id')
            ->groupBy('user_id')
            ->pluck('id');

        $records = UserAttendance::join('users', 'users.id', '=', 'user_attendance.user_id')
            ->whereIn('user_attendance.id', $latestIds)
            ->orderBy('user_attendance.date', 'desc')
            ->get([
                'users.name',
                'user_attendance.user_id',
                'user_attendance.date as check_in',
                'user_attendance.check_out',
            ]);

        return response()->json([
            'status' => true,
            'data' => $records,
        ]);
    }
}
