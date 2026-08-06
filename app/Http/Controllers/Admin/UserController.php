<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * The existing "Add/Edit User" UI only ever offers a single "Agent" role
     * option (unchanged in this phase — RBAC-aware role management UI is a
     * later phase). Map that legacy value onto the real seeded role and back
     * so the current view keeps working unmodified.
     */
    private const LEGACY_ROLE_MAP = ['agent' => 'Sales Executive'];

    public function user_profile()
    {
        return view('admin.userList.user_list');
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
            $legacyRole = array_search($roleName, self::LEGACY_ROLE_MAP) ?: $roleName;

            $action = "
                <div class='action-stack'>
                    <button
                        class='btn btn-sm btn-success action-status editbtn'
                        data-id='{$user->id}' data-name='{$user->name}' data-email='{$user->email}' data-phone='{$user->phone}' data-role='{$legacyRole}' data-backup=''
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
                'role' => $legacyRole,
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
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'digits_between:10,12'],
            'role' => ['required', 'string'],
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

        $roleName = self::LEGACY_ROLE_MAP[$request->role] ?? $request->role;
        $user->assignRole($roleName);

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
        $user = User::findOrFail($request->id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->status = $request->status;

        if (! empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->update();

        $roleName = self::LEGACY_ROLE_MAP[$request->role] ?? $request->role;
        $user->syncRoles([$roleName]);

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
