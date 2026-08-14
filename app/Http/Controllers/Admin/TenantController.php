<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantDatabaseProvisioner;
use App\Services\PlatformAuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::withCount('users')->orderBy('name')->get();

        return view('tenants.index', compact('tenants'));
    }

    public function store(Request $request, TenantDatabaseProvisioner $provisioner, PlatformAuditLogger $audit)
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
                'trial_ends_at' => $data['trial_ends_at'] ?? ($data['plan'] === 'trial' ? now()->addDays(14) : null),
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
            \App\Support\PermissionTeam::run($tenant->id, function () use ($admin) {
                $role = \Spatie\Permission\Models\Role::findOrCreate('Admin', 'web');
                $role->syncPermissions(\Spatie\Permission\Models\Permission::where('name', 'not like', 'platform.%')->get());
                $admin->assignRole($role);
            });

            $tenant->update(['admin_user_id' => $admin->id]);

            return [$tenant, $admin];
        });

        try {
            $provisioner->provision($tenant);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['tenant' => 'Company was created, but database provisioning failed. Use Retry provisioning after checking the database settings.']);
        }

        $audit->record('tenant.created', $tenant, $admin, ['source' => 'superadmin', 'plan' => $tenant->plan]);

        return back()->with('success', 'Tenant and company administrator created.');
    }

    public function update(Request $request, Tenant $tenant, PlatformAuditLogger $audit)
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

        if (($data['max_users'] ?? null) !== null && $data['max_users'] < $tenant->users()->count()) {
            throw ValidationException::withMessages([
                'max_users' => 'The user limit cannot be lower than the company’s current user count.',
            ]);
        }

        $before = $tenant->only(['name', 'contact_email', 'plan', 'max_users', 'trial_ends_at', 'status']);
        $tenant->update($data);
        $audit->record('tenant.updated', $tenant, metadata: ['before' => $before, 'after' => $tenant->only(array_keys($before))]);

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

    public function approve(Tenant $tenant, TenantDatabaseProvisioner $provisioner, PlatformAuditLogger $audit)
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
                'trial_ends_at' => $tenant->plan === 'trial' && $tenant->trial_ends_at === null ? now()->addDays(14) : $tenant->trial_ends_at,
            ]);
            $tenant->admin?->update(['status' => 'Active']);
        });
        $audit->record('tenant.approved', $tenant->fresh(), $tenant->admin);

        return back()->with('success', 'Signup approved. The company administrator can now log in.');
    }

    public function provision(Tenant $tenant, TenantDatabaseProvisioner $provisioner, PlatformAuditLogger $audit)
    {
        try {
            $provisioner->provision($tenant);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['tenant' => 'Provisioning failed: '.$exception->getMessage()]);
        }

        $audit->record('tenant.provisioned', $tenant->fresh(), metadata: ['database_name' => $tenant->database_name]);

        return back()->with('success', 'Tenant database is ready and passed its health check.');
    }

    public function health(Tenant $tenant, TenantDatabaseProvisioner $provisioner, PlatformAuditLogger $audit)
    {
        $healthy = $provisioner->healthCheck($tenant);
        $audit->record('tenant.health_checked', $tenant->fresh(), metadata: ['healthy' => $healthy]);

        return back()->with($healthy ? 'success' : 'error', $tenant->name.' database is '.($healthy ? 'healthy.' : 'unreachable.'));
    }

    public function reject(Request $request, Tenant $tenant, PlatformAuditLogger $audit)
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
        $audit->record('tenant.rejected', $tenant->fresh(), metadata: ['reason' => $data['rejection_reason']]);

        return back()->with('success', 'Signup rejected.');
    }
}
