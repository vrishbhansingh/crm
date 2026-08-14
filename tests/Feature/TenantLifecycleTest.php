<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\PlatformAuditLog;
use App\Services\CompanyAdminManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantLifecycleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_expired_tenant_cannot_log_in_or_use_an_existing_session(): void
    {
        $tenant = Tenant::create([
            'name' => 'Expired Company', 'slug' => 'expired-'.Str::random(8),
            'status' => 'Active', 'approval_status' => 'approved',
            'provision_status' => 'ready', 'trial_ends_at' => now()->subMinute(),
        ]);
        $user = $this->user($tenant, 'Admin');

        $this->postJson('/user/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()->assertJsonPath('status', false)
            ->assertJsonPath('message', 'Your organization subscription has expired.');

        $this->actingAs($user)->withSession(['session_token' => $user->session_token])
            ->get('/dashboard')->assertRedirect('/user/login');
    }

    public function test_approval_starts_a_fourteen_day_trial_and_activates_only_the_company_admin(): void
    {
        $superAdmin = $this->user(null, 'Super Admin');
        $tenant = Tenant::create([
            'name' => 'Pending Company', 'slug' => 'pending-'.Str::random(8),
            'status' => 'Inactive', 'approval_status' => 'pending',
            'provision_status' => 'ready', 'plan' => 'trial', 'trial_ends_at' => null,
        ]);
        $companyAdmin = $this->user($tenant, 'Admin', 'Inactive');
        $employee = $this->user($tenant, 'Employee', 'Inactive');
        $tenant->update(['admin_user_id' => $companyAdmin->id]);

        $this->actingAs($superAdmin)->withSession(['session_token' => $superAdmin->session_token])
            ->post("/superadmin/companies/{$tenant->id}/approve")
            ->assertRedirect()->assertSessionHas('success');

        $tenant->refresh();
        $this->assertSame('approved', $tenant->approval_status);
        $this->assertTrue($tenant->trial_ends_at->between(now()->addDays(13), now()->addDays(15)));
        $this->assertSame('Active', $companyAdmin->fresh()->status);
        $this->assertSame('Inactive', $employee->fresh()->status);
        $this->assertTrue(PlatformAuditLog::where('tenant_id', $tenant->id)->where('event', 'tenant.approved')->exists());
    }

    public function test_transferring_company_admin_removes_the_role_from_every_other_user(): void
    {
        $tenant = Tenant::create([
            'name' => 'Admin Transfer Company', 'slug' => 'transfer-'.Str::random(8),
            'status' => 'Active', 'approval_status' => 'approved', 'provision_status' => 'ready',
        ]);
        $oldAdmin = $this->user($tenant, 'Admin');
        $newAdmin = $this->user($tenant, 'Manager');
        $tenant->update(['admin_user_id' => $oldAdmin->id]);

        app(CompanyAdminManager::class)->assign($tenant, $newAdmin);

        $this->assertFalse($oldAdmin->fresh()->hasRole('Admin'));
        $this->assertTrue($newAdmin->fresh()->hasRole('Admin'));
        $this->assertSame($newAdmin->id, $tenant->fresh()->admin_user_id);
        $this->assertSame(1, $tenant->users()->whereHas('roles', fn ($query) => $query->where('name', 'Admin'))->count());
    }

    private function user(Tenant|int|null $tenant, string $role, string $status = 'Active'): User
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;
        $user = User::create([
            'tenant_id' => $tenantId, 'name' => $role,
            'email' => Str::random(12).'@example.test',
            'password' => Hash::make('password'), 'status' => $status,
            'session_token' => Str::random(60),
        ]);
        Role::findOrCreate($role, 'web');
        $user->assignRole($role);

        return $user;
    }
}
