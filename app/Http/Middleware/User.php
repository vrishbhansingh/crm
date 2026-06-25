<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class User
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('user')->check()) {
            // Single-device login: this session's token must match the one
            // stored on the account. A newer login elsewhere replaces it.
            $user = Auth::guard('user')->user();
            if ($user->session_token !== $request->session()->get('session_token_user')) {
                Auth::guard('user')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('user.login')
                    ->with('error', 'You have been logged out because your account was used on another device.');
            }

            return $next($request);
        }

        return redirect()->route('user.login');
    }
}
