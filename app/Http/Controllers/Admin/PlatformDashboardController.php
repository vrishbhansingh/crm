<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class PlatformDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total' => Tenant::count(),
            'active' => Tenant::where('status', 'Active')->where('approval_status', 'approved')->count(),
            'pending' => Tenant::where('approval_status', 'pending')->count(),
            'rejected' => Tenant::where('approval_status', 'rejected')->count(),
            'users' => DB::table('users')->whereNotNull('tenant_id')->count(),
        ];
        $recentSignups = Tenant::withCount('users')->latest()->limit(8)->get();

        return view('platform.dashboard', compact('stats', 'recentSignups'));
    }
}
