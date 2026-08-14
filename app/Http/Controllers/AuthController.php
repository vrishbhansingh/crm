<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use App\Models\PlatformAuditLog;
use App\Models\UserAttendance;
use App\Support\TenantContext;
use App\Support\PermissionTeam;
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
            return redirect()->route(Auth::guard('web')->user()->hasRole('Super Admin') ? 'superadmin.dashboard' : 'dashboard');
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
        PermissionTeam::set($user?->tenant_id);

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Credentials',
            ]);
        }

        if ($user->hasRole('Super Admin')) {
            return response()->json([
                'status' => false,
                'message' => 'Use the separate Super Admin portal to sign in.',
                'location' => route('superadmin.login'),
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

        if ($user->tenant_id !== null && (! $user->tenant || ! $user->tenant->isAccessible())) {
            return response()->json([
                'status' => false,
                'message' => $user->tenant?->accessBlockReason() ?? 'Your organization workspace is unavailable.',
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
            'location' => route('dashboard'),
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

    public function platformImpersonate(Request $request, Tenant $tenant, User $user)
    {
        abort_unless(Auth::user()?->hasRole('Super Admin'), 403);
        abort_unless((int) $user->tenant_id === $tenant->id && $user->status === 'Active', 404);
        abort_unless($tenant->isAccessible(), 422, $tenant->accessBlockReason() ?? 'This workspace is not ready.');

        $actor = Auth::user();
        PlatformAuditLog::create([
            'actor_id' => $actor->id,
            'tenant_id' => $tenant->id,
            'target_user_id' => $user->id,
            'event' => 'user.impersonation_started',
            'ip_address' => $request->ip(),
        ]);
        $request->session()->put('impersonator_id', $actor->id);
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('session_token', $user->session_token);

        return redirect()->route('dashboard')->with('success', 'Support session started. Actions are audited.');
    }

    public function stopImpersonating(Request $request)
    {
        $impersonatorId = $request->session()->pull('impersonator_id');
        abort_unless($impersonatorId, 403);
        $tenantId = Auth::user()?->tenant_id;

        Auth::loginUsingId($impersonatorId);
        $request->session()->regenerate();
        $request->session()->put('session_token', Auth::user()->session_token);
        $request->session()->forget('tenant_context_id');
        PlatformAuditLog::create([
            'actor_id' => $impersonatorId,
            'tenant_id' => $tenantId,
            'event' => 'user.impersonation_stopped',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('superadmin.dashboard');
    }
}
