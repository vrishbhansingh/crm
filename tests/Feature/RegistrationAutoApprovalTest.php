<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegistrationAutoApprovalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_self_service_signup_can_log_in_immediately_without_super_admin_approval(): void
    {
        config(['tenancy.require_approval' => false]);
        $email = Str::random(10).'@example.test';

        $this->post('/register', [
            'organization_name' => 'Freshly Registered Co',
            'name' => 'New Owner',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('register.success'))->assertSessionHas('auto_approved', true);

        $tenant = Tenant::where('contact_email', $email)->firstOrFail();
        $this->assertSame('Active', $tenant->status);
        $this->assertSame('approved', $tenant->approval_status);
        $this->assertNotNull($tenant->approved_at);

        $admin = User::where('email', $email)->firstOrFail();
        $this->assertSame('Active', $admin->status);
        $this->assertTrue($tenant->isAccessible());

        $this->postJson('/login', ['email' => $email, 'password' => 'password123'])
            ->assertOk()
            ->assertJsonPath('status', true);
    }

    public function test_require_approval_flag_keeps_the_old_pending_flow(): void
    {
        config(['tenancy.require_approval' => true]);
        $email = Str::random(10).'@example.test';

        $this->post('/register', [
            'organization_name' => 'Gated Co',
            'name' => 'New Owner',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('register.success'))->assertSessionMissing('auto_approved');

        $tenant = Tenant::where('contact_email', $email)->firstOrFail();
        $this->assertSame('pending', $tenant->approval_status);
        $this->assertSame('Inactive', $tenant->status);

        $this->postJson('/login', ['email' => $email, 'password' => 'password123'])
            ->assertOk()
            ->assertJsonPath('status', false);
    }

    public function test_auto_approved_tenant_still_appears_in_the_super_admin_companies_list(): void
    {
        config(['tenancy.require_approval' => false]);
        $superAdmin = User::create([
            'tenant_id' => null,
            'name' => 'Super Admin',
            'email' => Str::random(10).'@example.test',
            'password' => bcrypt('password'),
            'status' => 'Active',
            'session_token' => Str::random(60),
        ]);
        \Spatie\Permission\Models\Role::findOrCreate('Super Admin', 'web');
        $superAdmin->assignRole('Super Admin');

        $email = Str::random(10).'@example.test';
        $this->post('/register', [
            'organization_name' => 'Listed Co',
            'name' => 'New Owner',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('register.success'));

        $this->actingAs($superAdmin)->withSession(['session_token' => $superAdmin->session_token])
            ->get('/superadmin/companies')
            ->assertOk()
            ->assertSee('Listed Co');
    }
}
