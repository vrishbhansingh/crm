<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\TenantContext;
use App\Tenancy\TenantConnectionManager;
use Closure;
use Illuminate\Http\Request;

class ActivateTenantDatabase
{
    public function __construct(private readonly TenantConnectionManager $connections) {}

    public function handle(Request $request, Closure $next)
    {
        $this->connections->deactivate();

        if (config('tenancy.mode') === 'shared') {
            return $next($request);
        }

        if ($request->user()?->hasRole('Super Admin')) {
            return redirect()->route('superadmin.dashboard');
        }

        $tenantId = TenantContext::id();

        abort_if($tenantId === null, 403, 'Select a company workspace before opening the CRM.');

        $tenant = Tenant::findOrFail($tenantId);
        abort_unless($tenant->isAccessible(), 403, $tenant->accessBlockReason());

        $this->connections->activate($tenant);

        try {
            return $next($request);
        } finally {
            $this->connections->deactivate();
        }
    }
}
