<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

            if ($user->tenant_id !== null && ($user->tenant?->approval_status !== 'approved' || $user->tenant?->status !== 'Active')) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login')
                    ->with('error', 'Your organization workspace is inactive.');
            }

            if ($user->session_token !== $request->session()->get('session_token')) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $loginRoute = $request->is('admin/*') ? 'admin.login' : 'user.login';

                return redirect()->route($loginRoute)
                    ->with('error', 'You have been logged out because your account was used on another device.');
            }

            return $next($request);
        }

        $loginRoute = $request->is('admin/*') ? 'admin.login' : 'user.login';

        return redirect()->route($loginRoute);
    }
}
