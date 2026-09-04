<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Splits the app across two hosts: the CRM domain serves everything except
 * the Super Admin portal, the admin domain serves nothing but it. Which
 * routes count as "Super Admin portal" is decided purely by route name
 * (superadmin.*), not by the current user's role — the separation is meant
 * to be visible from the URL alone, before any auth check runs.
 */
class RestrictToAdminDomain
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $isSuperAdminRoute = (bool) $request->route()?->named('superadmin.*');

        if ($host === config('app.crm_domain')) {
            if (! $isSuperAdminRoute) {
                return $next($request);
            }

            if (Auth::check() && Auth::user()->hasRole('Super Admin')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('user.login')
                    ->with('error', 'Super Admins must use the admin portal.');
            }

            abort(404);
        }

        if ($host === config('app.admin_domain')) {
            if (! $isSuperAdminRoute) {
                abort(404);
            }

            if (Auth::check() && ! Auth::user()->hasRole('Super Admin')) {
                abort(403, 'Super Admin access only.');
            }

            return $next($request);
        }

        // Any other host (localhost during local dev, etc.) — unrestricted.
        return $next($request);
    }
}
