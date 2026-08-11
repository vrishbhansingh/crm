<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Platform-level role, deliberately excluded from the ordinary Add/Edit
     * User form — assigning it here would be an accidental privilege
     * escalation path. (Unified interface increment 1 — this used to be
     * moot since the form only ever offered a single hardcoded "Agent"
     * option; now that every real role is selectable, this exclusion matters.)
     */
    private const NON_ASSIGNABLE_ROLES = ['Super Admin', 'Company Admin'];

    public function user_profile()
    {
        return view('admin.userList.user_list');
    }

    public function getRoles()
    {
        $roles = Role::whereNotIn('name', self::NON_ASSIGNABLE_ROLES)
            ->orderBy('name')
            ->pluck('name');

        return response()->json(['data' => $roles]);
    }

    public function getUserList()
    {
        $actingUser = Auth::guard('web')->user();

        $query = User::with('roles');

        // A platform-level Super Admin (tenant_id null) manages every tenant's
        // users; a tenant-scoped admin only manages their own tenant's users.
        if (($tenantId = TenantContext::id()) !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $users = $query->get();

        $data = [];
        $sl_no = 1;
        foreach ($users as $user) {
            $roleName = $user->getRoleNames()->first();
            $safeName = e($user->name);
            $safeEmail = e($user->email);
            $safePhone = e($user->phone);
            $safeRole = e($roleName);
            $safeStatus = e($user->status);

            $action = "<div class='action-stack'>";
            if ($actingUser->can('users.edit')) {
                $action .= "
                    <button
                        class='btn btn-sm btn-success action-status editbtn'
                        data-id='{$user->id}' data-name='{$safeName}' data-email='{$safeEmail}' data-phone='{$safePhone}' data-role='{$safeRole}'
                        data-status='{$safeStatus}'>
                        <i class='fa fa-check-circle'></i> Edit
                    </button>";
            }
            if ($actingUser->can('users.delete') && $user->id !== $actingUser->id) {
                $action .= "<button class='btn btn-sm btn-danger delete_data' data-id='{$user->id}'><i class='fa fa-trash'></i> Delete</button>";
            }
            $action .= '</div>';

            $data[] = [
                'id' => $user->id,
                'sl_no' => $sl_no++,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $roleName,
                'status' => $user->status,
                'action' => $action,
            ];
        }

        return response()->json([
            'data' => $data,
        ]);
    }

    public function add_user(Request $request)
    {
        $assignableRoles = Role::whereNotIn('name', self::NON_ASSIGNABLE_ROLES)->pluck('name');

        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'digits_between:10,12'],
            'role' => ['required', 'string', Rule::in($assignableRoles)],
            'status' => ['required', 'in:Active,Inactive'],
            'password' => ['required', 'string', 'min:8', 'max:20'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $actingUser = Auth::guard('web')->user();
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant before creating a user.');
        $tenant = Tenant::findOrFail($tenantId);
        abort_if($tenant->max_users !== null && $tenant->users()->count() >= $tenant->max_users, 422, 'This tenant has reached its user limit.');

        $user = new User();
        $user->tenant_id = $tenantId;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->status = $request->status;
        $user->password = Hash::make($request->password);
        $user->save();

        $user->assignRole($request->role);

        return response()->json([
            'status' => true,
            'message' => 'User added successfully',
        ]);
    }

    public function toggleUserStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'edit_status_value' => 'required|in:Active,Inactive,Block',
        ]);

        $user = $this->findManagedUser($request->integer('id'));
        if ($user->id === Auth::guard('web')->id() && $request->edit_status_value !== 'Active') {
            return response()->json(['status' => false, 'message' => 'You cannot deactivate your own account.'], 422);
        }
        $user->status = $request->edit_status_value;
        $user->update();

        return response()->json(['status' => true]);
    }

    public function edit_user(Request $request)
    {
        $assignableRoles = Role::whereNotIn('name', self::NON_ASSIGNABLE_ROLES)->pluck('name');

        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($request->id)],
            'phone' => ['required', 'digits_between:10,12'],
            'role' => ['required', 'string', Rule::in($assignableRoles)],
            'status' => ['required', 'in:Active,Inactive,Block'],
            'password' => ['nullable', 'string', 'min:8', 'max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = $this->findManagedUser($request->integer('id'));
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->status = $request->status;

        if (! empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->update();
        $user->syncRoles([$request->role]);

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
        ]);
    }

    public function delete_user(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $user = $this->findManagedUser($request->integer('id'));

        if ($user->id === Auth::guard('web')->id()) {
            return response()->json(['status' => false, 'message' => 'You cannot delete your own account.'], 422);
        }

        $result = $user->delete();

        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'User deleted Successfully',
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Something went wrong',
        ]);
    }

    private function findManagedUser(int $id): User
    {
        $actingUser = Auth::guard('web')->user();
        $query = User::query();

        if (($tenantId = TenantContext::id()) !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->findOrFail($id);
    }
}
