<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Admin
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            // Single-device login: this session's token must match the one
            // stored on the account. A newer login elsewhere replaces it.
            $admin = Auth::guard('admin')->user();
            if ($admin->session_token !== $request->session()->get('session_token_admin')) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login')
                    ->with('error', 'You have been logged out because your account was used on another device.');
            }

            return $next($request);
        }

        // Agar admin login nahi hai
        return redirect()->route('admin.login');
    }
}
