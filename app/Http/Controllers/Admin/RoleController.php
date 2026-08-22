<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\PlatformAuditLogger;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Permissions that are either functionally inert for a custom role, or
     * dangerous enough to need calling out instead of sitting anonymously in
     * the general grid. Both lists are hidden from the ordinary grouped grid
     * and rendered separately in the view.
     *
     * `roles.*` — the entire module — does nothing for a custom role: every
     * write action in this controller is hard-gated to the tenant's literal
     * protected Admin account (tenantAdmin() below), not to a Spatie
     * permission, so ticking any roles.* checkbox grants a capability that
     * can never actually be exercised. Left seeded (existing grants, if any,
     * are harmless) but no longer offered — a checkbox that visibly does
     * nothing erodes trust in the ones that do.
     */
    private const INERT_MODULE = 'roles';

    private const SENSITIVE_PERMISSIONS = ['users.impersonate'];

    public function index()
    {
        $tenant = $this->tenantAdmin();
        $roles = Role::where('tenant_id', $tenant->id)->withCount('users')->orderBy('name')->get();
        $all = Permission::where('name', 'not like', 'platform.%')
            ->whereNotIn('name', self::SENSITIVE_PERMISSIONS)
            ->where('name', 'not like', self::INERT_MODULE.'.%')
            ->orderBy('name')->get();
        $permissions = $all->groupBy(fn ($permission) => str($permission->name)->before('.')->value());
        $sensitivePermissions = Permission::whereIn('name', self::SENSITIVE_PERMISSIONS)->orderBy('name')->get();

        return view('roles.index', compact('tenant', 'roles', 'permissions', 'sensitivePermissions'));
    }

    public function store(Request $request, PlatformAuditLogger $audit)
    {
        $tenant = $this->tenantAdmin();
        $data = $this->validated($request, $tenant);
        $role = Role::create(['tenant_id' => $tenant->id, 'name' => trim($data['name']), 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions']);
        $audit->record('role.created', $tenant, metadata: ['role' => $role->name, 'permissions' => $data['permissions']]);

        return back()->with('success', 'Role created. It is now available when adding users.');
    }

    public function update(Request $request, int $role, PlatformAuditLogger $audit)
    {
        $tenant = $this->tenantAdmin();
        $role = Role::where('tenant_id', $tenant->id)->findOrFail($role);
        $this->assertEditable($role);
        $data = $this->validated($request, $tenant, $role->id);
        $oldName = $role->name;
        $role->update(['name' => trim($data['name'])]);
        $role->syncPermissions($data['permissions']);
        $audit->record('role.updated', $tenant, metadata: ['old_name' => $oldName, 'role' => $role->name, 'permissions' => $data['permissions']]);

        return back()->with('success', 'Role and permissions updated.');
    }

    public function destroy(int $role, PlatformAuditLogger $audit)
    {
        $tenant = $this->tenantAdmin();
        $role = Role::where('tenant_id', $tenant->id)->findOrFail($role);
        $this->assertEditable($role);
        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'Reassign users before deleting this role.']);
        }
        $name = $role->name;
        $role->delete();
        $audit->record('role.deleted', $tenant, metadata: ['role' => $name]);

        return back()->with('success', 'Role deleted.');
    }

    private function tenantAdmin(): Tenant
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 403);
        $tenant = Tenant::findOrFail($tenantId);
        abort_unless($tenant->admin_user_id === Auth::id() && Auth::user()->hasRole('Admin'), 403, 'Only the company Admin can manage roles.');

        return $tenant;
    }

    private function validated(Request $request, Tenant $tenant, ?int $ignoreRoleId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('roles', 'name')->where(fn ($query) => $query->where('tenant_id', $tenant->id)->where('guard_name', 'web'))->ignore($ignoreRoleId)],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['required', 'string', Rule::exists('permissions', 'name')->where('guard_name', 'web')->where(fn ($query) => $query->where('name', 'not like', 'platform.%'))],
        ]);
        if (in_array(strtolower(trim($data['name'])), ['admin', 'super admin'], true)) {
            throw ValidationException::withMessages(['name' => 'This is a protected role name.']);
        }

        return $data;
    }

    private function assertEditable(Role $role): void
    {
        abort_if($role->name === 'Admin', 403, 'The Admin role is protected.');
    }
}
