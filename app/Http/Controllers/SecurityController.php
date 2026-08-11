<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * One security/change-password page for every role (unified interface,
 * increment 2), replacing Admin\SecurityController + User\SecurityController.
 * The user-side version never checked the current password before changing
 * it — the admin-side version did. Merging fixes that by construction:
 * everyone now goes through the version that checks.
 */
class SecurityController extends Controller
{
    public function show()
    {
        return view('security');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|max:72',
            'confirm_password' => 'required|same:new_password',
        ]);

        $user = Auth::guard('web')->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect',
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->session_token = Str::random(60);
        $user->update();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => true,
            'message' => 'Password updated successfully. Please login again.',
        ]);
    }
}
