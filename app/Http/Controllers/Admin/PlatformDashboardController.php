<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\PlatformAuditLog;
use Illuminate\Support\Facades\DB;

class PlatformDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total' => Tenant::count(),
            'active' => Tenant::accessible()->where('provision_status', 'ready')->count(),
            'pending' => Tenant::where('approval_status', 'pending')->count(),
            'rejected' => Tenant::where('approval_status', 'rejected')->count(),
            'users' => DB::table('users')->whereNotNull('tenant_id')
                ->whereNotIn('id', DB::table('tenants')->whereNotNull('admin_user_id')->select('admin_user_id'))
                ->count(),
            'expired' => Tenant::whereNotNull('trial_ends_at')->where('trial_ends_at', '<=', now())->count(),
            'expiring' => Tenant::whereBetween('trial_ends_at', [now(), now()->addDays(14)])->count(),
            'database_issues' => Tenant::whereIn('provision_status', ['pending', 'failed'])->count(),
        ];
        $recentSignups = Tenant::withCount('users')->latest()->limit(8)->get();
        $recentActivity = PlatformAuditLog::with(['actor', 'tenant', 'targetUser'])->latest()->limit(12)->get();

        return view('platform.dashboard', compact('stats', 'recentSignups', 'recentActivity'));
    }
}
