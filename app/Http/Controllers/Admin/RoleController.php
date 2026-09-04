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
        return view('roles.index');
    }

    public function data()
    {
        $tenant = $this->tenantAdmin();
        $roles = Role::where('tenant_id', $tenant->id)->withCount('users')->orderBy('name')->get();

        $totalGrantable = $this->grantablePermissions()->count();

        return response()->json([
            'status' => true,
            'data' => $roles->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'users_count' => $role->users_count,
                'permissions_count' => $role->name === 'Admin' ? $totalGrantable : $role->permissions()->count(),
                'total_permissions' => $totalGrantable,
                'is_protected' => $role->name === 'Admin',
            ]),
        ]);
    }

    /**
     * The permission catalog (grouped by module, plus the sensitive set)
     * for the "Manage Access" picker — with a role's currently granted
     * permission names included when editing an existing role.
     */
    public function permissionCatalog(?int $role = null)
    {
        $tenant = $this->tenantAdmin();
        $grouped = $this->grantablePermissions()->groupBy(fn ($permission) => str($permission->name)->before('.')->value());
        $sensitive = Permission::whereIn('name', self::SENSITIVE_PERMISSIONS)->orderBy('name')->get();

        $granted = [];
        if ($role) {
            $roleModel = Role::where('tenant_id', $tenant->id)->findOrFail($role);
            $granted = $roleModel->permissions()->pluck('name')->all();
        }

        $label = fn ($permission) => str($permission->name)->after('.')->replace('-', ' ')->ucfirst()->value();

        return response()->json([
            'status' => true,
            'groups' => $grouped->map(fn ($items, $module) => [
                'module' => $module,
                'label' => ucfirst($module),
                'permissions' => $items->map(fn ($p) => ['name' => $p->name, 'label' => $label($p)])->values(),
            ])->values(),
            'sensitive' => $sensitive->map(fn ($p) => [
                'name' => $p->name,
                'label' => $label($p),
                'note' => $p->name === 'users.impersonate' ? 'Sign in as any other user in this workspace without their password.' : null,
            ])->values(),
            'granted' => $granted,
            'total' => $this->grantablePermissions()->count() + $sensitive->count(),
        ]);
    }

    public function store(Request $request, PlatformAuditLogger $audit)
    {
        $tenant = $this->tenantAdmin();
        $data = $this->validated($request, $tenant);
        $role = Role::create([
            'tenant_id' => $tenant->id,
            'name' => trim($data['name']),
            'description' => $data['description'] ?? null,
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($data['permissions']);
        $audit->record('role.created', $tenant, metadata: ['role' => $role->name, 'permissions' => $data['permissions']]);

        return response()->json(['status' => true, 'message' => 'Role created', 'data' => ['id' => $role->id]]);
    }

    /**
     * Name + description only — the "Edit Info" action. Permissions are
     * untouched here; that's what "Manage Access" (updatePermissions) is for.
     */
    public function update(Request $request, int $role, PlatformAuditLogger $audit)
    {
        $tenant = $this->tenantAdmin();
        $roleModel = Role::where('tenant_id', $tenant->id)->findOrFail($role);
        $this->assertEditable($roleModel);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('roles', 'name')->where(fn ($q) => $q->where('tenant_id', $tenant->id)->where('guard_name', 'web'))->ignore($roleModel->id)],
            'description' => ['nullable', 'string', 'max:150'],
        ]);
        $this->assertNotProtectedName($data['name']);

        $oldName = $roleModel->name;
        $roleModel->update(['name' => trim($data['name']), 'description' => $data['description'] ?? null]);
        $audit->record('role.updated', $tenant, metadata: ['old_name' => $oldName, 'role' => $roleModel->name]);

        return response()->json(['status' => true, 'message' => 'Role updated']);
    }

    /**
     * Permissions only — the "Manage Access" action.
     */
    public function updatePermissions(Request $request, int $role, PlatformAuditLogger $audit)
    {
        $tenant = $this->tenantAdmin();
        $roleModel = Role::where('tenant_id', $tenant->id)->findOrFail($role);
        $this->assertEditable($roleModel);

        $data = $request->validate([
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')->where(fn ($q) => $q->where('name', 'not like', 'platform.%'))],
        ]);

        $roleModel->syncPermissions($data['permissions']);
        $audit->record('role.permissions_updated', $tenant, metadata: ['role' => $roleModel->name, 'permissions' => $data['permissions']]);

        return response()->json(['status' => true, 'message' => 'Permissions updated']);
    }

    public function destroy(int $role, PlatformAuditLogger $audit)
    {
        $tenant = $this->tenantAdmin();
        $roleModel = Role::where('tenant_id', $tenant->id)->findOrFail($role);
        $this->assertEditable($roleModel);
        if ($roleModel->users()->exists()) {
            return response()->json(['status' => false, 'message' => 'Reassign users before deleting this role.'], 422);
        }
        $name = $roleModel->name;
        $roleModel->delete();
        $audit->record('role.deleted', $tenant, metadata: ['role' => $name]);

        return response()->json(['status' => true, 'message' => 'Role deleted']);
    }

    private function grantablePermissions()
    {
        return Permission::where('name', 'not like', 'platform.%')
            ->whereNotIn('name', self::SENSITIVE_PERMISSIONS)
            ->where('name', 'not like', self::INERT_MODULE.'.%')
            ->orderBy('name')->get();
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
            'description' => ['nullable', 'string', 'max:150'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['required', 'string', Rule::exists('permissions', 'name')->where('guard_name', 'web')->where(fn ($query) => $query->where('name', 'not like', 'platform.%'))],
        ]);
        $this->assertNotProtectedName($data['name']);

        return $data;
    }

    private function assertNotProtectedName(string $name): void
    {
        if (in_array(strtolower(trim($name)), ['admin', 'super admin'], true)) {
            throw ValidationException::withMessages(['name' => 'This is a protected role name.']);
        }
    }

    private function assertEditable(Role $role): void
    {
        abort_if($role->name === 'Admin', 403, 'The Admin role is protected.');
    }
}
