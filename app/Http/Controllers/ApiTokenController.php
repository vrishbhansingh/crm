<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiTokenController extends Controller
{
    public function index()
    {
        $tokens = Auth::guard('web')->user()->tokens()->latest()->get();

        return view('security.api-tokens', compact('tokens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', 'in:crm:read,crm:write'],
        ]);

        $user = Auth::guard('web')->user();
        abort_if($user->tenant_id === null, 422, 'Platform users cannot create unscoped API tokens. Use a tenant account.');
        $abilities = $request->input('abilities', ['crm:read']);
        $token = $user->createToken($request->name, $abilities);

        return back()->with('new_api_token', $token->plainTextToken);
    }

    public function destroy(int $id)
    {
        Auth::guard('web')->user()->tokens()->whereKey($id)->firstOrFail()->delete();

        return back()->with('success', 'API token revoked.');
    }
}
