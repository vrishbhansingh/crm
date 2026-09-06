<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PermissionTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * A separate mirror of ForgotPasswordController rather than reusing it
 * directly: this one only ever sends a reset link to a Super Admin account
 * (a tenant user submitting their email here gets the same generic message
 * but no email — the admin domain isn't where they log in anyway), and
 * lands back on the Super Admin login instead of the tenant one. Matches
 * the existing split between SuperAdminAuthController and AuthController.
 */
class SuperAdminForgotPasswordController extends Controller
{
    public function show()
    {
        return view('auth.superadmin-forgot-password');
    }

    public function send(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $genericStatus = 'If a Super Admin account exists for that email, a password reset link has been sent.';

        $user = User::where('email', $request->email)->first();

        // hasRole() is team-scoped (Spatie teams reuse tenant_id), and
        // Super Admin's role lives under the platform team (0), not no
        // team — without pointing the registrar at that team first, this
        // would silently evaluate false for a genuine Super Admin. Same
        // gotcha already fixed in RestrictToAdminDomain.
        $isSuperAdmin = $user && PermissionTeam::run(null, fn () => $user->hasRole('Super Admin'));

        if (! $isSuperAdmin) {
            // Same response either way — this can't be used to tell
            // whether an email is registered, or registered as Super Admin.
            return back()->with('status', $genericStatus);
        }

        try {
            Password::broker('users')->sendResetLink($request->only('email'));
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['email' => 'We could not send the reset email right now due to a mail server problem. Please try again shortly, or fix the platform SMTP settings.'])
                ->onlyInput('email');
        }

        return back()->with('status', $genericStatus);
    }

    public function reset(Request $request, string $token)
    {
        return view('auth.superadmin-reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ]);

        $status = Password::broker('users')->reset($data, function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'session_token' => Str::random(60),
            ])->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            $message = $status === Password::INVALID_TOKEN
                ? "This reset link is invalid or has expired. If you requested more than one reset email, only the link in the most recent one still works — check your inbox for a newer email, or request a new link below."
                : __($status);

            return back()->withErrors(['email' => $message])->onlyInput('email');
        }

        return redirect()->route('superadmin.login')->with('success', 'Your password has been reset. You can now log in.');
    }
}
