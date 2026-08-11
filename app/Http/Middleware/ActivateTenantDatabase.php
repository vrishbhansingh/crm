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
        $tenantId = TenantContext::id();

        abort_if($tenantId === null, 403, 'Select a company workspace before opening the CRM.');

        $tenant = Tenant::whereKey($tenantId)
            ->where('status', 'Active')
            ->where('approval_status', 'approved')
            ->where('provision_status', 'ready')
            ->firstOrFail();

        $this->connections->activate($tenant);

        try {
            return $next($request);
        } finally {
            $this->connections->deactivate();
        }
    }
}
