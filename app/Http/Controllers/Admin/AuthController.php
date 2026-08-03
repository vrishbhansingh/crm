<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Models\UserList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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


        // Only ever authenticate against the FIRST admin row — never a second,
        // even if extra rows were added directly in the database.
        $admin = Admin::orderBy('id', 'asc')->first();
        $user = UserList::orderBy('id', 'asc')->first();

        if (!$admin || $admin->username !== $request->username || !Hash::check($request->password, $admin->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Credentials',
                ]);
        }

        if ($admin->status !== 'Active') {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ]);
        }

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        // Single-device login: issue a fresh token and invalidate other sessions.
        $token = Str::random(60);
        $admin->session_token = $token;
        $admin->save();
        $request->session()->put('session_token_admin', $token);

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'location' => url('admin/dashboard/'),
        ]);
    }
    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            // Log out ONLY the admin guard — do NOT invalidate the whole session,
            // or it would also log out the user guard (they share one session).
            Auth::guard('admin')->logout();
            $request->session()->forget('session_token_admin');
        }

        return redirect()->route('admin.login');
    }
}
