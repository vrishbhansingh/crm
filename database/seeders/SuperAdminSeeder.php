<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\PermissionTeam;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        PermissionTeam::run(null, function () {
            $role = Role::findOrCreate('Super Admin', 'web');

            User::firstOrCreate(
                ['email' => 'superadmin@crm.com'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('password'),
                    'status' => 'Active',
                    'session_token' => Str::random(60),
                ]
            )->assignRole($role);
        });
    }
}
