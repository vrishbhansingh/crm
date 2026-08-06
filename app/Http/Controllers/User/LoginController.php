<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Login/logout/impersonation moved to the unified App\Http\Controllers\AuthController
 * (Phase 1). Only the attendance check-in action remains here.
 */
class LoginController extends Controller
{
    function user_attend(Request $request)
    {
        $userId = Auth::guard('web')->id();
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
}
