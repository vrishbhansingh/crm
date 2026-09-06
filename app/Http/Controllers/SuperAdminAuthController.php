<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Support\PermissionTeam;

class SuperAdminAuthController extends Controller
{
    public function login(Request $request)
    {
        PermissionTeam::set(null);

        if (Auth::check()) {
            if (Auth::user()->hasRole('Super Admin')) {
                return redirect()->route('superadmin.dashboard');
            }

            // A leftover impersonation session (see RestrictToAdminDomain) —
            // clear it so it can't collide with the Super Admin login below.
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return view('auth.superadmin-login');
    }

    public function authenticate(Request $request)
    {
        PermissionTeam::set(null);
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! $user->hasRole('Super Admin') || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['email' => 'Invalid Super Admin credentials.'])->onlyInput('email');
        }
        if ($user->status !== 'Active') {
            return back()->withErrors(['email' => 'This Super Admin account is inactive.'])->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();
        $token = Str::random(60);
        $user->forceFill(['session_token' => $token, 'last_login' => now()])->save();
        $request->session()->put('session_token', $token);
        $request->session()->forget(['tenant_context_id', 'impersonator_id']);

        return redirect()->route('superadmin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login');
    }
}
