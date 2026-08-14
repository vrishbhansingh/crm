<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\TenantConnectionManager;
use App\Support\PermissionTeam;
use Closure;
use Illuminate\Http\Request;

class EnsureActiveApiUser
{
    public function __construct(private readonly TenantConnectionManager $connections) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        PermissionTeam::set($user?->tenant_id);

        abort_unless($user && $user->status === 'Active', 403, 'This API account is inactive.');
        if (config('tenancy.mode') === 'shared') {
            abort_unless(
                $user->tenant_id !== null && Tenant::find($user->tenant_id)?->isAccessible(),
                403,
                'This tenant workspace is inactive.'
            );

            return $next($request);
        }

        $tenant = $user->tenant_id === null ? null : Tenant::find($user->tenant_id);
        abort_unless(
            $tenant?->isAccessible(),
            403,
            'This tenant workspace is inactive.'
        );

        $this->connections->activate($tenant);

        try {
            return $next($request);
        } finally {
            $this->connections->deactivate();
            PermissionTeam::set(null);
        }
    }
}
