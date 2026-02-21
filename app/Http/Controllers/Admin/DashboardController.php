<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
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
}
