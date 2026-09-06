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

        $genericStatus = 'If an account exists for that email, a password reset link has been sent.';

        try {
            $status = Password::broker('users')->sendResetLink($request->only('email'));
        } catch (\Throwable $exception) {
            // Password::broker() only ever reaches the actual mail send
            // (and so only ever throws) once it has already found a real
            // matching user — an unknown email returns INVALID_USER below
            // without attempting to send anything. So a thrown exception
            // here always means a genuine SMTP/transport failure on a real
            // account, not "this email doesn't exist" — safe to say so
            // plainly instead of silently pretending it worked, which is
            // what was happening before and is why reset emails could fail
            // with no visible sign anything was wrong.
            report($exception);

            return back()
                ->withErrors(['email' => 'We could not send the reset email right now due to a mail server problem. Please try again shortly, or contact your administrator if this keeps happening.'])
                ->onlyInput('email');
        }

        // Every other status (RESET_LINK_SENT, INVALID_USER, or a broker
        // throttle) gets the exact same response, so this endpoint can't be
        // used to enumerate registered accounts by comparing messages.
        return back()->with('status', $genericStatus);
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
            // Laravel's stock "This password reset token is invalid." reads
            // like something broke, when the far more common cause is
            // mundane: the tokens table keeps exactly one row per email, so
            // requesting a second reset email silently invalidates the
            // first — someone who re-submitted "forgot password" and then
            // opened an older email lands here confused. Say so plainly.
            $message = $status === Password::INVALID_TOKEN
                ? "This reset link is invalid or has expired. If you requested more than one reset email, only the link in the most recent one still works — check your inbox for a newer email, or request a new link below."
                : __($status);

            return back()->withErrors(['email' => $message])->onlyInput('email');
        }

        return redirect()->route('user.login')->with('success', 'Your password has been reset. You can now log in.');
    }
}
