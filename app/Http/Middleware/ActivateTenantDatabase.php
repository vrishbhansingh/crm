<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\PermissionTeam;
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

        $user = $request->user();

        if (! $user) {
            // Not authenticated — let the downstream single-session
            // middleware redirect to the correct login page instead of
            // this middleware aborting with a tenant-workspace message.
            return $next($request);
        }

        // Role checks are team/tenant-scoped and the team context for this
        // request hasn't been set yet (EnsureSingleSession normally does
        // that, but it runs after this middleware) — set it before any
        // hasRole() call or it silently evaluates against no team and
        // returns false for everyone, including Super Admin.
        PermissionTeam::set($user->tenant_id);

        if ($user->hasRole('Super Admin')) {
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
