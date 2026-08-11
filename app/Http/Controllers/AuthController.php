<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAttendance;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Single centralized login for every role (Phase 1 Step 5), replacing the
 * old Admin\AuthController + User\LoginController pair. Existing route
 * names/URLs (admin.login, user.login, admin.login_submit, ...) are kept
 * so no other view/JS needs to change — they all now resolve here.
 */
class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route(Auth::guard('web')->user()->hasRole('Super Admin') ? 'platform.dashboard' : 'dashboard');
        }

        $submitRoute = $request->is('admin/*') ? route('admin.login_submit') : route('user.login_submit');

        return view('auth.login', compact('submitRoute'));
    }

    public function login_submit(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Credentials',
            ]);
        }

        if ($user->status !== 'Active') {
            return response()->json([
                'status' => false,
                'message' => $user->status === 'Block'
                    ? 'Your account has been blocked by the administrator.'
                    : 'Your account is inactive.',
            ]);
        }

        if ($user->tenant_id !== null && $user->tenant?->approval_status === 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Your company signup is awaiting Super Admin approval.',
            ]);
        }

        if ($user->tenant_id !== null && ($user->tenant?->approval_status !== 'approved' || $user->tenant?->status !== 'Active')) {
            return response()->json([
                'status' => false,
                'message' => 'Your organization workspace is inactive. Contact the platform administrator.',
            ]);
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        // Single-device login: issue a fresh token; a newer login elsewhere
        // replaces it, and the enforcing middleware logs the older session out.
        $token = Str::random(60);
        $user->session_token = $token;
        $user->last_login = now();
        $user->save();
        $request->session()->put('session_token', $token);
        $request->session()->forget('tenant_context_id');

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'location' => route($user->hasRole('Super Admin') ? 'platform.dashboard' : 'dashboard'),
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('web')->user();

        if ($user) {
            $open = UserAttendance::where('user_id', $user->id)
                ->whereNull('check_out')
                ->orderBy('id', 'desc')
                ->first();

            if ($open) {
                $open->check_out = now();
                $open->save();
            }

            Auth::guard('web')->logout();
        }

        // Only one guard now — safe to fully invalidate the session.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $loginRoute = $request->is('admin/*') ? 'admin.login' : 'user.login';

        return redirect()->route($loginRoute);
    }

    /**
     * Log in as another user without their password. Gated by the
     * `users.impersonate` permission on the route (Phase 1 Step 6) instead
     * of the old hardcoded `Auth::guard('admin')->check()`.
     */
    public function impersonate(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $actingUser = Auth::guard('web')->user();
        $query = User::query();

        if (($tenantId = TenantContext::id()) !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $target = $query->find($request->integer('id'));

        if (! $target) {
            abort(404, 'User not found.');
        }

        if ($target->hasRole('Super Admin') && ! $actingUser->hasRole('Super Admin')) {
            abort(403, 'Only a Super Admin may impersonate another Super Admin.');
        }

        session([
            'impersonator_id' => Auth::guard('web')->id(),
            'impersonated_user_id' => $target->id,
        ]);

        Auth::guard('web')->loginUsingId($target->id);
        $request->session()->regenerate();
        $request->session()->forget('tenant_context_id');

        // Match the target's current single-session token so impersonation
        // passes the single-device check without logging the real user out.
        $request->session()->put('session_token', $target->session_token);

        $target->last_login = now();
        $target->save();

        return response()->json([
            'status' => true,
            'message' => 'Logged in as user successfully',
        ]);
    }
}
