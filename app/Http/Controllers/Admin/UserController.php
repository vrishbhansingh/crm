<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //
    public function user_profile()
    {
        return view('admin.userList.user_list');
    }
    public function getUserList()
    {
        $user = UserList::all();

        $data = [];
        $sl_no = 1;
        foreach ($user as $key) {
            // $editUrl = route('admin.edit_customer_details', $item->id);
            $action = "
                <div class='action-stack'>
                    <button
                        class='btn btn-sm btn-success action-status editbtn'
                        data-id='{$key->id}' data-name='{$key->name}' data-email='{$key->email}' data-phone='{$key->phone}' data-role='{$key->role}' data-backup='{$key->backup}'
                        data-status='{$key->status}'>
                        <i class='fa fa-check-circle'></i> Edit
                    </button>
                </div>";
            $data[] = [
                'id' => $key->id,
                'sl_no' => $sl_no++,
                'name' => $key->name,
                'email' => $key->email,
                'phone' => $key->phone,
                'role' => $key->role,
                'status' => $key->status,
                'action' => $action,
            ];
        }
        return response()->json([
            'data' => $data
        ]);
    }
    public function add_user(Request $request)
    {
        // Restrict to a single user for now.
        if (UserList::count() >= 1) {
            return response()->json([
                'status'  => false,
                'message' => 'Only one user is allowed.',
            ], 422);
        }

        $rules = [
        'name' => [
            'required',
            'string',
            'max:100',
        ],

        'email' => [
            'required'],

        'phone' => [
            'required',
            'digits_between:10,12',
        ],

        'role' => [
            'required',
            'string',
        ],

        'status' => [
            'required',
            'in:Active,Inactive',
        ],

        'password' => [
            'required',
            'string',
            'min:8',
            'max:20',
        ],
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);

    }

        $user = new UserList();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role = $request->role;
        $user->status = $request->status;
        $user->backup = $request->password;

        $user->password = Hash::make($request->password);
        $result = $user->save();

        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'User added successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }

    
    public function toggleUserStatus(Request $request)
    {
        $user = UserList::findOrFail($request->id);
        $user->status = $request->edit_status_value;
        $user->update();

        return response()->json(['status' => true]);
    }
    public function edit_user(Request $request)
    {
       $user = UserList::find($request->id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role = $request->role;
        $user->password = Hash::make($request->password);
        $user->status = $request->status;
        $result = $user->update();
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'User updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function delete_user(Request $request){
       $user = UserList::find($request->id);
       $result = $user->delete();
       if($result){
        return response()->json([
            'status' => true,
            'message' => 'User deleted Successfully'
        ]);
       }
       else{
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong'
        ]);
       }
    }
}
