<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ProfileController extends Controller
{
    public function profile()
    {
        return view('user.profile.profile');
    }

    /**
     * Mirrors CompanyController::edit_com_details()'s upload pattern exactly
     * (public_path('uploads/...') + File::move(), not the Storage facade)
     * so an avatar behaves the same way the company logo already does —
     * immediately renderable via plain asset() in the header.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = Auth::guard('web')->user();
        $user->name = $request->name;
        $user->phone = $request->phone;

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            if (! $file->isValid()) {
                return response()->json(['status' => false, 'message' => 'Invalid image file']);
            }

            $uploadPath = public_path('uploads/avatars');

            if (! File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }

            if (! empty($user->avatar) && File::exists(public_path($user->avatar))) {
                File::delete(public_path($user->avatar));
            }

            $fileName = 'avatar_'.$user->id.'_'.time().'.'.$file->extension();
            $file->move($uploadPath, $fileName);

            $user->avatar = 'uploads/avatars/'.$fileName;
        }

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'avatar_url' => $user->avatar ? asset($user->avatar) : null,
        ]);
    }
}
