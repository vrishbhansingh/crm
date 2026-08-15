<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function show()
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            Password::broker('users')->sendResetLink($request->only('email'));
        } catch (\Throwable $exception) {
            // A misconfigured SMTP server (tenant or platform) must not turn
            // into a 500 for the person requesting a reset — log it for an
            // admin to notice and fix in Mail Settings.
            report($exception);
        }

        // Always the same response regardless of whether the email exists
        // or the send actually succeeded, so this endpoint can't be used to
        // enumerate registered accounts.
        return back()->with('status', 'If an account exists for that email, a password reset link has been sent.');
    }

    public function reset(Request $request, string $token)
    {
        return view('auth.reset-password', [
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
            return back()->withErrors(['email' => __($status)])->onlyInput('email');
        }

        return redirect()->route('user.login')->with('success', 'Your password has been reset. You can now log in.');
    }
}
