<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use App\Models\PlatformAuditLog;
use App\Support\TenantContext;
use App\Support\PermissionTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Single centralized login for every role (Phase 1 Step 5), replacing the
 * old Admin\AuthController + User\LoginController pair. Existing route
 * names/URLs (user.login, admin.login_submit, ...) are kept so no other
 * view/JS needs to change — they all now resolve here. The only sign-in
 * pages are /login (this one) and /superadmin/login — the old /admin/login
 * duplicate of this same page was removed.
 */
class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route(Auth::guard('web')->user()->hasRole('Super Admin') ? 'superadmin.dashboard' : 'dashboard');
        }

        return view('auth.login', ['submitRoute' => route('user.login_submit')]);
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
                'message' => 'Use the Super Admin portal to sign in.',
                'location' => $request->getScheme().'://'.config('app.admin_domain').'/superadmin/login',
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
            Auth::guard('web')->logout();
        }

        // Only one guard now — safe to fully invalidate the session.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('user.login');
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

        // The superadmin panel this was submitted from lives on the admin
        // domain, but `dashboard` is a CRM-domain-only route — build an
        // absolute cross-domain redirect rather than route()'s default
        // (which would reuse the current, wrong, request host). Scheme is
        // taken from the current request rather than hardcoded, since this
        // app isn't served over HTTPS on every environment.
        return redirect()->to($request->getScheme().'://'.config('app.crm_domain').route('dashboard', absolute: false))
            ->with('success', 'Support session started. Actions are audited.');
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

        // Mirror image of platformImpersonate's fix: this is submitted from
        // a CRM-domain page (the "End support session" banner shown while
        // impersonating), but superadmin.dashboard only exists on the
        // admin domain.
        return redirect()->to($request->getScheme().'://'.config('app.admin_domain').route('superadmin.dashboard', absolute: false));
    }
}
