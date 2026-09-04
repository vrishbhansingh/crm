<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\UserAttendance;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_team_attendance_does_not_join_across_the_tenant_and_master_connections(): void
    {
        $tenant = Tenant::create(['name' => 'Dash Tenant', 'slug' => 'dash-'.Str::random(8), 'status' => 'Active']);
        $admin = $this->user($tenant, 'Admin');
        $employee = $this->user($tenant, 'Employee');

        UserAttendance::create(['tenant_id' => $tenant->id, 'user_id' => $employee->id, 'date' => now('Asia/Kolkata')]);

        $this->actingAs($admin)->withSession(['session_token' => $admin->session_token])
            ->getJson(route('dashboard.attendance.team'))
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.name', 'Employee')
            ->assertJsonPath('summary.present', 1)
            ->assertJsonPath('summary.total', 2);
    }

    public function test_team_dashboard_data_includes_tasks_and_user_counts(): void
    {
        $tenant = Tenant::create(['name' => 'Dash Tenant Two', 'slug' => 'dash-two-'.Str::random(8), 'status' => 'Active']);
        $admin = $this->user($tenant, 'Admin');

        $this->actingAs($admin)->withSession(['session_token' => $admin->session_token])
            ->getJson(route('dashboard.data'))
            ->assertOk()
            ->assertJsonPath('scope', 'team')
            ->assertJsonStructure(['data' => ['totalLead', 'tasksDueToday', 'totalUsers']]);
    }

    private function user(Tenant $tenant, string $role): User
    {
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $role,
            'email' => Str::random(10).'@example.test',
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => Str::random(60),
        ]);
        Role::findOrCreate($role, 'web');
        $user->assignRole($role);

        return $user;
    }
}
