<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class SecurityController extends Controller
{
    //
    function security()
    {
        return view('admin.security.security');
    }

    public function update_pass(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6',
            'confirm_password' => 'required|min:6',
        ]);

        $admin = Admin::first();

        if (!Hash::check($request->current_password, $admin->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect'
            ]);
        }

        // ❌ new & confirm mismatch (backend safety)
        if ($request->new_password !== $request->confirm_password) {
            return response()->json([
                'status' => false,
                'message' => 'New password and confirm password do not match'
            ]);
        }

        // ✅ Update password
        $admin->password = Hash::make($request->new_password);
        $admin->update();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => true,
            'message' => 'Password updated successfully. Please login again.'
        ]);
    }
}
