<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlatformAuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlatformProfileController extends Controller
{
    public function edit()
    {
        return view('platform.profile', ['user' => Auth::guard('web')->user()]);
    }

    public function update(Request $request, PlatformAuditLogger $logger)
    {
        $user = Auth::guard('web')->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($data);
        $logger->record('superadmin.profile_updated', target: $user, metadata: $data);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request, PlatformAuditLogger $logger)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'max:72'],
            'confirm_password' => ['required', 'same:new_password'],
        ]);

        $user = Auth::guard('web')->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->session_token = Str::random(60);
        $user->save();
        $logger->record('superadmin.password_changed', target: $user);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login')->with('success', 'Password updated. Please sign in again.');
    }
}
