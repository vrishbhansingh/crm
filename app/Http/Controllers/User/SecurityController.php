<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\UserList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    //
    function security()
    {
        return view('user.settings.security');
    }

    function update_security_pass(Request $request)
    {
        $request->validate([
            'new_password'     => 'required|min:6',
            'confirm_password' => 'required|min:6',
        ]);
        $user_id = Auth::guard('user')->user()->id;

        $user = UserList::findOrFail($user_id);

        // ❌ new & confirm mismatch (backend safety)
        if ($request->new_password !== $request->confirm_password) {
            return response()->json([
                'status' => false,
                'message' => 'New password and confirm password do not match'
            ]);
        }

        // ✅ Update password
        $user->password = Hash::make($request->new_password);
        $user->backup = $request->new_password;
        $user->update();

        Auth::guard('user')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json([
            'status' => true,
            'message' => 'Password updated successfully. Please login again.'
        ]);
    }
}
