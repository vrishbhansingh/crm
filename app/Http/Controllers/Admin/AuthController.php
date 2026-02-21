<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //
    public function login()
    {
        return view('admin.auth.login');
    }

    public function login_submit(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        $credentials = $request->only('username', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $user = Auth::guard('admin')->user();

            if ($user->status === 'Active') {
                $request->session()->regenerate();

                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'location' => url('admin/dashboard/'),
                ]);
            } else {
                Auth::guard('admin')->logout();
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong',
                ]);
            }
        } else {

            return response()->json([
                'status' => false,
                'message' => 'Invalid Credentials',
            ]);
        }
    }
    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('admin.login');
    }
}
