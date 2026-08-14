<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CompanyAdminManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Support\PermissionTeam;

class PlatformUserController extends Controller
{
    private const RESERVED_ROLES = ['Super Admin', 'Admin'];

    public function index(Tenant $tenant)
    {
        PermissionTeam::set($tenant->id);
        $tenant->load(['users.roles', 'admin']);
        $roles = Role::where('tenant_id', $tenant->id)->whereNotIn('name', self::RESERVED_ROLES)->orderBy('name')->pluck('name');

        return view('platform.users', compact('tenant', 'roles'));
    }

    public function store(Request $request, Tenant $tenant)
    {
        PermissionTeam::set($tenant->id);
        abort_if($tenant->max_users && $tenant->users()->count() >= $tenant->max_users, 422, 'This company has reached its user limit.');
        $roles = Role::where('tenant_id', $tenant->id)->whereNotIn('name', self::RESERVED_ROLES)->pluck('name');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'max:72'],
            'role' => ['required', Rule::in($roles)],
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => 'Active',
        ]);
        $user->assignRole($data['role']);
        $this->audit('user.created', $tenant, $user, ['role' => $data['role']]);

        return back()->with('success', 'User created in the central identity directory.');
    }

    public function update(Request $request, Tenant $tenant, User $user)
    {
        PermissionTeam::set($tenant->id);
        abort_unless((int) $user->tenant_id === $tenant->id, 404);
        $isAdmin = $tenant->admin_user_id === $user->id;
        $roles = Role::where('tenant_id', $tenant->id)->whereNotIn('name', self::RESERVED_ROLES)->pluck('name');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Block'])],
            'password' => ['nullable', 'string', 'min:8', 'max:72'],
            'role' => [$isAdmin ? 'nullable' : 'required', Rule::in($roles)],
        ]);
        abort_if($isAdmin && $data['status'] !== 'Active', 422, 'Transfer company administration before disabling this administrator.');

        $user->fill(collect($data)->only(['name', 'email', 'phone', 'status'])->all());
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        if (! $isAdmin) {
            $user->syncRoles([$data['role']]);
        }
        $this->audit('user.updated', $tenant, $user);

        return back()->with('success', 'User account updated.');
    }

    public function transferAdmin(Request $request, Tenant $tenant, CompanyAdminManager $admins)
    {
        PermissionTeam::set($tenant->id);
        $data = $request->validate(['user_id' => ['required', 'integer']]);
        $newAdmin = $tenant->users()->findOrFail($data['user_id']);
        $oldAdmin = $tenant->admin;
        $admins->assign($tenant, $newAdmin);
        $this->audit('company.admin_transferred', $tenant, $newAdmin, ['previous_admin_id' => $oldAdmin?->id]);

        return back()->with('success', 'Company administrator transferred. Exactly one Admin remains.');
    }

    private function audit(string $event, Tenant $tenant, ?User $target = null, array $metadata = []): void
    {
        PlatformAuditLog::create([
            'actor_id' => Auth::id(),
            'tenant_id' => $tenant->id,
            'target_user_id' => $target?->id,
            'event' => $event,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
        ]);
    }
}
