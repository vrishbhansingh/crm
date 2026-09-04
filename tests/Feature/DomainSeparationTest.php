<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DomainSeparationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_crm_domain_blocks_superadmin_routes_and_logs_out_a_super_admin_who_lands_there(): void
    {
        $super = $this->makeUser(null, 'Super Admin');

        $this->actingAs($super)->withSession(['session_token' => $super->session_token])
            ->get($this->crmUrl('/superadmin'))
            ->assertRedirect(route('user.login'));

        $this->assertGuest('web');
    }

    public function test_crm_domain_serves_regular_login_normally(): void
    {
        $this->get($this->crmUrl('/login'))->assertOk();
    }

    public function test_admin_domain_blocks_every_non_superadmin_route(): void
    {
        $this->get($this->adminUrl('/login'))->assertNotFound();
        $this->get($this->adminUrl('/register'))->assertNotFound();
    }

    public function test_admin_domain_serves_the_superadmin_login(): void
    {
        $this->get($this->adminUrl('/superadmin/login'))->assertOk();
    }

    public function test_admin_domain_forbids_a_non_super_admin_from_the_superadmin_area(): void
    {
        $tenant = Tenant::create(['name' => 'Domain Tenant', 'slug' => 'domain-'.Str::random(8), 'status' => 'Active']);
        $tenantUser = $this->makeUser($tenant->id, 'Admin');

        $this->actingAs($tenantUser)->withSession(['session_token' => $tenantUser->session_token])
            ->get($this->adminUrl('/superadmin'))
            ->assertForbidden();
    }

    private function crmUrl(string $path): string
    {
        return 'http://'.config('app.crm_domain').$path;
    }

    private function adminUrl(string $path): string
    {
        return 'http://'.config('app.admin_domain').$path;
    }

    private function makeUser(?int $tenantId, string $role): User
    {
        $user = User::create([
            'tenant_id' => $tenantId,
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
