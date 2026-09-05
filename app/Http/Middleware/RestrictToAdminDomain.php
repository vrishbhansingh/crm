<?php

namespace App\Http\Middleware;

use App\Support\PermissionTeam;
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

            if (Auth::check() && $this->isSuperAdmin(Auth::user())) {
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

            if (Auth::check() && ! $this->isSuperAdmin(Auth::user())) {
                abort(403, 'Super Admin access only.');
            }

            return $next($request);
        }

        // Any other host (localhost during local dev, etc.) — unrestricted.
        return $next($request);
    }

    /**
     * hasRole('Super Admin') is a team-scoped check (Spatie teams reuse
     * tenant_id), and Super Admin's role lives under the platform team (0),
     * not no team at all — calling hasRole() without first pointing the
     * registrar at that team silently evaluates against no team and returns
     * false even for a genuine Super Admin. Scoped to just this check (via
     * PermissionTeam::run(), which restores whatever team was active
     * before) rather than set at the top of handle(), so it can't affect
     * team context for any other request path this middleware also guards.
     */
    private function isSuperAdmin($user): bool
    {
        return PermissionTeam::run(null, fn () => $user->hasRole('Super Admin'));
    }
}
