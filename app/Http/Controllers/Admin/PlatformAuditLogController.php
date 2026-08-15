<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\Tenant;
use Illuminate\Http\Request;

class PlatformAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = PlatformAuditLog::with(['actor', 'tenant', 'targetUser'])
            ->when($request->filled('tenant_id'), fn ($query) => $query->where('tenant_id', $request->integer('tenant_id')))
            ->when($request->filled('event'), fn ($query) => $query->where('event', $request->string('event')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $tenants = Tenant::orderBy('name')->get(['id', 'name']);
        $events = PlatformAuditLog::select('event')->distinct()->orderBy('event')->pluck('event');

        return view('platform.audit', compact('logs', 'tenants', 'events'));
    }
}
