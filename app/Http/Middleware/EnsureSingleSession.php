<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\PermissionTeam;

/**
 * Replaces the old separate Admin/User middleware (Phase 1 Step 6) now that
 * every role authenticates through the single `web` guard. Enforces the same
 * single-device login behavior: a session whose token doesn't match the
 * account's current session_token was superseded by a newer login elsewhere.
 */
class EnsureSingleSession
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            PermissionTeam::set($user->tenant_id);

            if ($user->tenant_id !== null && (! $user->tenant || ! $user->tenant->isAccessible())) {
                $message = $user->tenant?->accessBlockReason() ?? 'Your organization workspace is unavailable.';
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $loginRoute = $request->is('superadmin/*') ? 'superadmin.login' : 'user.login';

                return redirect()->route($loginRoute)
                    ->with('error', $message);
            }

            if ($user->session_token !== $request->session()->get('session_token')) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $loginRoute = $request->is('superadmin/*') ? 'superadmin.login' : 'user.login';

                return redirect()->route($loginRoute)
                    ->with('error', 'You have been logged out because your account was used on another device.');
            }

            return $next($request);
        }

        PermissionTeam::set(null);
        $loginRoute = $request->is('superadmin/*') ? 'superadmin.login' : 'user.login';

        return redirect()->route($loginRoute);
    }
}
