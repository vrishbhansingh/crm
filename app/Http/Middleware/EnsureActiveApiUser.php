<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\TenantConnectionManager;
use Closure;
use Illuminate\Http\Request;

class EnsureActiveApiUser
{
    public function __construct(private readonly TenantConnectionManager $connections) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        abort_unless($user && $user->status === 'Active', 403, 'This API account is inactive.');
        if (config('tenancy.mode') === 'shared') {
            abort_unless(
                $user->tenant_id !== null && Tenant::whereKey($user->tenant_id)->where('status', 'Active')->exists(),
                403,
                'This tenant workspace is inactive.'
            );

            return $next($request);
        }

        $tenant = $user->tenant_id === null ? null : Tenant::whereKey($user->tenant_id)
            ->where('status', 'Active')
            ->where('approval_status', 'approved')
            ->where('provision_status', 'ready')
            ->first();
        abort_unless(
            $tenant,
            403,
            'This tenant workspace is inactive.'
        );

        $this->connections->activate($tenant);

        try {
            return $next($request);
        } finally {
            $this->connections->deactivate();
        }
    }
}
