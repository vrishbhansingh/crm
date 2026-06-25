<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAttendance;
use App\Models\UserList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

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

        // Only ever authenticate against the FIRST user row — never a second,
        // even if extra rows were added directly in the database.
        $user = User::orderBy('id', 'asc')->first();

        if (!$user || $user->email !== $request->email || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Credentials',
            ]);
        }

        if ($user->status !== 'Active') {
            return response()->json([
                'status' => false,
                'message' => 'Your account has been blocked by the administrator.',
            ]);
        }

        Auth::guard('user')->login($user);
        $request->session()->regenerate();

        // Single-device login: issue a fresh token and invalidate other sessions.
        $token = Str::random(60);
        $user->last_login = now();
        $user->session_token = $token;
        $user->save();
        $request->session()->put('session_token_user', $token);

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
        ]);
    }

    function logout(Request $request)
    {
        if (Auth::guard('user')->check()) {
            // Stamp check-out on the latest attendance that hasn't been closed yet.
            $open = UserAttendance::where('user_id', Auth::guard('user')->id())
                ->whereNull('check_out')
                ->orderBy('id', 'desc')
                ->first();
            if ($open) {
                $open->check_out = \Carbon\Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');
                $open->save();
            }

            // Log out ONLY the user guard — do NOT invalidate the whole session,
            // or it would also log out the admin guard (they share one session).
            Auth::guard('user')->logout();
            $request->session()->forget('session_token_user');
        }

        return redirect()->route('user.login');
    }
    function user_attend(Request $request)
    {
        $userId = Auth::guard('user')->id();
        $today  = \Carbon\Carbon::now('Asia/Kolkata')->toDateString();

        // Only once per day — if already marked today, don't create a duplicate.
        $already = UserAttendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->exists();
        if ($already) {
            return response()->json([
                'status' => true,
                'message' => 'Attendance already marked for today',
            ]);
        }

        $user_attendence = new UserAttendance();
        $user_attendence->user_id = $userId;
        // Use server time (IST) so the date is consistent with the "marked today" check.
        $user_attendence->date = \Carbon\Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');
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

        // Match the user's current single-session token so impersonation
        // passes the single-device check without logging the real user out.
        $request->session()->put('session_token_user', $user->session_token);

        // Update last login (optional)
        $user->last_login = now();
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Logged in as user successfully'
        ]);
    }
}
