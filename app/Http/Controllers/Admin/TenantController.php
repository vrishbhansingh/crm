<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::withCount('users')->orderBy('name')->get();

        return view('tenants.index', compact('tenants'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', 'alpha_dash', 'unique:tenants,slug'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'plan' => ['required', Rule::in(['trial', 'standard', 'professional', 'enterprise'])],
            'timezone' => ['required', 'timezone'],
            'locale' => ['required', 'string', 'max:10'],
            'max_users' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'trial_ends_at' => ['nullable', 'date'],
            'admin_name' => ['required', 'string', 'max:100'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_phone' => ['nullable', 'string', 'max:30'],
            'admin_password' => ['required', 'string', 'min:8', 'max:72'],
        ]);

        DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'name' => $data['name'],
                'slug' => $data['slug'] ?: Str::slug($data['name']).'-'.Str::lower(Str::random(6)),
                'status' => 'Active',
                'approval_status' => 'approved',
                'signup_source' => 'platform',
                'approved_at' => now(),
                'approved_by' => Auth::guard('web')->id(),
                'contact_email' => $data['contact_email'] ?? null,
                'plan' => $data['plan'],
                'timezone' => $data['timezone'],
                'locale' => $data['locale'],
                'max_users' => $data['max_users'] ?? null,
                'trial_ends_at' => $data['trial_ends_at'] ?? null,
            ]);

            $admin = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'phone' => $data['admin_phone'] ?? null,
                'password' => Hash::make($data['admin_password']),
                'status' => 'Active',
                'session_token' => Str::random(60),
            ]);
            $admin->assignRole('Company Admin');
        });

        return back()->with('success', 'Tenant and company administrator created.');
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', Rule::unique('tenants', 'slug')->ignore($tenant->id)],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'plan' => ['required', Rule::in(['trial', 'standard', 'professional', 'enterprise'])],
            'timezone' => ['required', 'timezone'],
            'locale' => ['required', 'string', 'max:10'],
            'max_users' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'trial_ends_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        $tenant->update($data);

        if ($tenant->status === 'Inactive' && session('tenant_context_id') === $tenant->id) {
            session()->forget('tenant_context_id');
        }

        return back()->with('success', 'Tenant settings updated.');
    }

    public function destroy(Tenant $tenant)
    {
        $hasData = $tenant->users()->exists()
            || DB::table('leads')->where('tenant_id', $tenant->id)->exists()
            || DB::table('deals')->where('tenant_id', $tenant->id)->exists()
            || DB::table('orders')->where('tenant_id', $tenant->id)->exists()
            || DB::table('companies')->where('tenant_id', $tenant->id)->exists()
            || DB::table('contacts')->where('tenant_id', $tenant->id)->exists();

        if ($hasData) {
            return back()->withErrors(['tenant' => 'This tenant contains users or CRM records. Deactivate it instead of deleting it.']);
        }

        $tenant->delete();

        return back()->with('success', 'Empty tenant deleted.');
    }

    public function switch(Request $request)
    {
        $request->validate(['tenant_id' => ['nullable', 'integer']]);

        if ($request->filled('tenant_id')) {
            $tenant = Tenant::where('status', 'Active')->findOrFail($request->integer('tenant_id'));
            session()->put('tenant_context_id', $tenant->id);
        } else {
            session()->forget('tenant_context_id');
        }

        return redirect()->route('dashboard');
    }

    public function approve(Tenant $tenant)
    {
        DB::transaction(function () use ($tenant) {
            $tenant = Tenant::whereKey($tenant->id)->lockForUpdate()->firstOrFail();
            $tenant->update([
                'status' => 'Active',
                'approval_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::guard('web')->id(),
                'rejection_reason' => null,
            ]);
            $tenant->users()->whereHas('roles', fn ($query) => $query->where('name', 'Company Admin'))->update(['status' => 'Active']);
        });

        return back()->with('success', 'Signup approved. The company administrator can now log in.');
    }

    public function reject(Request $request, Tenant $tenant)
    {
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:2000']]);

        DB::transaction(function () use ($tenant, $data) {
            $tenant->update([
                'status' => 'Inactive',
                'approval_status' => 'rejected',
                'approved_at' => null,
                'approved_by' => Auth::guard('web')->id(),
                'rejection_reason' => $data['rejection_reason'],
            ]);
            $tenant->users()->update(['status' => 'Inactive']);
        });

        return back()->with('success', 'Signup rejected.');
    }
}
