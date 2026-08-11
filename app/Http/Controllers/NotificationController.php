<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        return view('notifications.index');
    }

    public function data()
    {
        return response()->json([
            'data' => Auth::guard('web')->user()->notifications()->latest()->limit(100)->get(),
            'unread' => Auth::guard('web')->user()->unreadNotifications()->count(),
        ]);
    }

    public function read(Request $request, string $id)
    {
        $notification = Auth::guard('web')->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['status' => true, 'url' => $notification->data['url'] ?? route('notifications.index')]);
    }

    public function readAll()
    {
        Auth::guard('web')->user()->unreadNotifications->markAsRead();

        return response()->json(['status' => true]);
    }
}
