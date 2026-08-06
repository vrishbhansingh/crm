<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
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
    private const NON_ASSIGNABLE_ROLES = ['Super Admin'];

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
        if ($actingUser->tenant_id !== null) {
            $query->where('tenant_id', $actingUser->tenant_id);
        }

        $users = $query->get();

        $data = [];
        $sl_no = 1;
        foreach ($users as $user) {
            $roleName = $user->getRoleNames()->first();

            $action = "
                <div class='action-stack'>
                    <button
                        class='btn btn-sm btn-success action-status editbtn'
                        data-id='{$user->id}' data-name='{$user->name}' data-email='{$user->email}' data-phone='{$user->phone}' data-role='{$roleName}'
                        data-status='{$user->status}'>
                        <i class='fa fa-check-circle'></i> Edit
                    </button>
                </div>";

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
        $tenantId = $actingUser->tenant_id ?? Tenant::first()?->id;

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
        $user = User::findOrFail($request->id);
        $user->status = $request->edit_status_value;
        $user->update();

        return response()->json(['status' => true]);
    }

    public function edit_user(Request $request)
    {
        $assignableRoles = Role::whereNotIn('name', self::NON_ASSIGNABLE_ROLES)->pluck('name');

        $validator = Validator::make($request->all(), [
            'role' => ['required', 'string', Rule::in($assignableRoles)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::findOrFail($request->id);
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
        $user = User::findOrFail($request->id);
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
}
