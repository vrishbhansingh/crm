<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAttendance;
use App\Models\UserList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    //
    function showLogin()
    {
        return view('user.login');
    }

    public function login_submit(Request $request)
    {
        date_default_timezone_set('Asia/Kolkata');
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $credentials = $request->only('email', 'password');

        if (Auth::guard('user')->attempt($credentials)) {
            $user = Auth::guard('user')->user();
            if ($user->status === 'Active') {
                $request->session()->regenerate();

                $check  = User::where('id', Auth::guard('user')->user()->id)->first();
                if ($check) {
                    $check->last_login = now();
                    $check->update();
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                ]);
            }
            else{
                return response()->json([
                'status' => false,
                'message' => 'Your account has been blocked by the administrator.',
            ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Credentials',
            ]);
        }
    }

    function logout(Request $request)
    {
        if (Auth::guard('user')->check()) {
            Auth::guard('user')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('user.login');
    }
    function user_attend(Request $request)
    {
        $user_id = $request->user_id;
        $attendance_datetime = $request->attendance_datetime;
        $user_attendence = new UserAttendance();
        $user_attendence->user_id = $user_id;
        $user_attendence->date = $attendance_datetime;
        $result = $user_attendence->save();
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Attendence Registered'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }



    public function user_login_through_admin(Request $request)
    {
        $userId = $request->id;

        // Get user (ANY STATUS allowed)
        $user = UserList::find($userId);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ]);
        }

        // ✅ Ensure admin is logged in
        if (!Auth::guard('admin')->check()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ]);
        }

        // 🔐 Save admin ID for revert impersonation later
        session([
            'impersonator_admin_id' => Auth::guard('admin')->id(),
            'impersonated_user_id'  => $user->id,
        ]);

        // ✅ Login as user WITHOUT checking status
        Auth::guard('user')->loginUsingId($user->id);

        $request->session()->regenerate();

        // Update last login (optional)
        $user->last_login = now();
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Logged in as user successfully'
        ]);
    }
}
