<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantDatabaseProvisioner;
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

    public function store(Request $request, TenantDatabaseProvisioner $provisioner)
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

        [$tenant, $admin] = DB::transaction(function () use ($data) {
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

            $tenant->update(['admin_user_id' => $admin->id]);

            return [$tenant, $admin];
        });

        try {
            $provisioner->provision($tenant);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['tenant' => 'Company was created, but database provisioning failed. Use Retry provisioning after checking the database settings.']);
        }

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
        $hasData = $tenant->users()->exists() || $tenant->database_name !== null;

        if ($hasData) {
            return back()->withErrors(['tenant' => 'This tenant contains users or CRM records. Deactivate it instead of deleting it.']);
        }

        $tenant->delete();

        return back()->with('success', 'Empty tenant deleted.');
    }

    public function approve(Tenant $tenant, TenantDatabaseProvisioner $provisioner)
    {
        if ($tenant->provision_status !== 'ready') {
            try {
                $tenant = $provisioner->provision($tenant);
            } catch (\Throwable $exception) {
                report($exception);

                return back()->withErrors(['tenant' => 'Approval stopped because the tenant database could not be provisioned.']);
            }
        }

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

    public function provision(Tenant $tenant, TenantDatabaseProvisioner $provisioner)
    {
        try {
            $provisioner->provision($tenant);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['tenant' => 'Provisioning failed: '.$exception->getMessage()]);
        }

        return back()->with('success', 'Tenant database is ready and passed its health check.');
    }

    public function health(Tenant $tenant, TenantDatabaseProvisioner $provisioner)
    {
        return back()->with(
            $provisioner->healthCheck($tenant) ? 'success' : 'error',
            $tenant->name.' database is '.($tenant->last_health_status === 'healthy' ? 'healthy.' : 'unreachable.')
        );
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
