<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RegistrationController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'organization_name' => ['required', 'string', 'max:150'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ]);

        DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'name' => $data['organization_name'],
                'slug' => Str::slug($data['organization_name']).'-'.Str::lower(Str::random(8)),
                'status' => 'Inactive',
                'approval_status' => 'pending',
                'signup_source' => 'self_service',
                'contact_email' => $data['email'],
                'plan' => 'trial',
                'timezone' => 'Asia/Kolkata',
                'locale' => 'en',
                'trial_ends_at' => now()->addDays(14),
            ]);

            $admin = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'status' => 'Inactive',
                'session_token' => Str::random(60),
            ]);

            Role::findOrCreate('Company Admin', 'web');
            $admin->assignRole('Company Admin');
        });

        return redirect()->route('register.success');
    }

    public function success()
    {
        return view('auth.registration-success');
    }
}
