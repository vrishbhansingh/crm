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
     * The permission picker offers exactly these — not "every module x
     * every action" from the seeder's blanket cross-product. Each entry
     * here is verified against an actual `permission:module.action`
     * middleware in routes/app.php; a module/action combination that no
     * route enforces (e.g. `contacts.reject`, or anything under `projects.*`
     * — that module has no permission gate at all yet) is left out rather
     * than offered as a checkbox that would do nothing if ticked.
     *
     * Keep this in sync by hand when routes/app.php's `permission:` gates
     * change — regenerate with:
     *   grep -oE "permission:[a-z_-]+\.[a-z_-]+" routes/app.php | sort -u
     */
    private const MODULE_ACTIONS = [
        'leads' => ['view', 'create', 'edit', 'delete', 'assign', 'import'],
        'deals' => ['view', 'create', 'edit', 'delete', 'assign', 'manage-settings'],
        'companies' => ['view', 'create', 'edit', 'delete'],
        'contacts' => ['view', 'create', 'edit', 'delete'],
        'orders' => ['view', 'edit'],
        'tasks' => ['view', 'create', 'edit', 'delete'],
        'templates' => ['view', 'create', 'edit', 'delete'],
        'campaigns' => ['view', 'create', 'delete', 'send'],
        'masters' => ['view', 'create', 'edit', 'delete'],
        'users' => ['view', 'create', 'edit'],
        'company' => ['view', 'edit', 'manage-settings'],
        'reports' => ['view'],
        'audit' => ['view'],
        // 'users.impersonate' is deliberately excluded from every module
        // list here — it's real and route-enforced, but sensitive enough
        // that it isn't offered as a delegable checkbox at all; only the
        // protected Admin role (which gets every permission via a blanket
        // sync, not through this picker) has it.
    ];

    /**
     * Nicer names than a bare ucfirst($module) — matches how each module
     * is actually labeled in the sidebar (e.g. "company" is the tenant's
     * own org profile, shown everywhere else as "Organization Profile").
     */
    private const MODULE_LABELS = [
        'leads' => 'Leads',
        'deals' => 'Deals',
        'companies' => 'Companies',
        'contacts' => 'Contacts',
        'orders' => 'Orders',
        'tasks' => 'Tasks',
        'templates' => 'Email Templates',
        'campaigns' => 'Email Campaigns',
        'masters' => 'Master Data',
        'users' => 'Users',
        'company' => 'Organization Profile',
        'reports' => 'Reports',
        'audit' => 'Audit Log',
    ];

    public function index()
    {
        return view('roles.index');
    }

    public function data()
    {
        $tenant = $this->tenantAdmin();
        $roles = Role::where('tenant_id', $tenant->id)->withCount('users')->orderBy('name')->get();

        $grantableNames = $this->grantablePermissionNames();
        $totalGrantable = count($grantableNames);

        return response()->json([
            'status' => true,
            'data' => $roles->map(function (Role $role) use ($grantableNames, $totalGrantable) {
                $granted = $role->name === 'Admin'
                    ? $totalGrantable
                    : $role->permissions()->pluck('name')->intersect($grantableNames)->count();

                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description,
                    'users_count' => $role->users_count,
                    'permissions_count' => $granted,
                    'total_permissions' => $totalGrantable,
                    'is_protected' => $role->name === 'Admin',
                ];
            }),
        ]);
    }

    /**
     * The permission catalog (grouped by module) for the "Manage
     * Permissions" picker — with a role's currently granted permission
     * names included when editing an existing role.
     */
    public function permissionCatalog(?int $role = null)
    {
        $tenant = $this->tenantAdmin();
        $grouped = $this->grantablePermissions()->groupBy(fn ($permission) => str($permission->name)->before('.')->value());

        $granted = [];
        if ($role) {
            $roleModel = Role::where('tenant_id', $tenant->id)->findOrFail($role);
            $granted = $roleModel->permissions()->pluck('name')->all();
        }

        $label = fn ($permission) => str($permission->name)->after('.')->replace('-', ' ')->ucfirst()->value();

        // Preserve MODULE_ACTIONS order (leads/deals/companies first) rather
        // than whatever order the DB happens to return.
        $orderedGroups = collect(array_keys(self::MODULE_ACTIONS))
            ->filter(fn ($module) => $grouped->has($module))
            ->map(fn ($module) => [
                'module' => $module,
                'label' => self::MODULE_LABELS[$module] ?? ucfirst($module),
                'permissions' => $grouped->get($module)->map(fn ($p) => ['name' => $p->name, 'label' => $label($p)])->values(),
            ])->values();

        return response()->json([
            'status' => true,
            'groups' => $orderedGroups,
            'granted' => $granted,
            'total' => $this->grantablePermissions()->count(),
        ]);
    }

    /**
     * Name + description only — permissions always start empty and are
     * assigned afterward through the separate "Manage Permissions" action,
     * exactly like editing an existing role does.
     */
    public function store(Request $request, PlatformAuditLogger $audit)
    {
        $tenant = $this->tenantAdmin();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('roles', 'name')->where(fn ($q) => $q->where('tenant_id', $tenant->id)->where('guard_name', 'web'))],
            'description' => ['nullable', 'string', 'max:150'],
        ]);
        $this->assertNotProtectedName($data['name']);

        $role = Role::create([
            'tenant_id' => $tenant->id,
            'name' => trim($data['name']),
            'description' => $data['description'] ?? null,
            'guard_name' => 'web',
        ]);
        $audit->record('role.created', $tenant, metadata: ['role' => $role->name]);

        return response()->json(['status' => true, 'message' => 'Role created', 'data' => ['id' => $role->id]]);
    }

    /**
     * Name + description only — the "Edit" action. Permissions are
     * untouched here; that's what "Manage Permissions" (updatePermissions)
     * is for.
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
     * Permissions only — the "Manage Permissions" action.
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

    private function grantablePermissionNames(): array
    {
        $names = [];
        foreach (self::MODULE_ACTIONS as $module => $actions) {
            foreach ($actions as $action) {
                $names[] = "{$module}.{$action}";
            }
        }

        return $names;
    }

    private function grantablePermissions()
    {
        return Permission::whereIn('name', $this->grantablePermissionNames())->orderBy('name')->get();
    }

    private function tenantAdmin(): Tenant
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 403);
        $tenant = Tenant::findOrFail($tenantId);
        abort_unless($tenant->admin_user_id === Auth::id() && Auth::user()->hasRole('Admin'), 403, 'Only the company Admin can manage roles.');

        return $tenant;
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
