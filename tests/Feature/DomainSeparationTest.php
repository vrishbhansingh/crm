<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Support\PermissionTeam;
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

        // Poison the ambient team context before the request — a real HTTP
        // request starts fresh with no leftover team scope from whatever
        // this test's own arrange phase happened to set, so a middleware
        // that silently relies on that leftover state (rather than
        // establishing its own) would pass here but fail in production.
        PermissionTeam::set(999999);

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

        PermissionTeam::set(999999);

        $this->actingAs($tenantUser)->withSession(['session_token' => $tenantUser->session_token])
            ->get($this->adminUrl('/superadmin'))
            ->assertForbidden();
    }

    /**
     * Regression test: a genuine Super Admin must be able to load pages
     * after logging in, not just receive a redirect to one. hasRole() is a
     * team-scoped check and Super Admin's role lives under the platform
     * team (0) — a middleware that checks hasRole() without first pointing
     * the registrar at that team would 403 every page after a successful
     * login, even though the login itself succeeded.
     */
    public function test_a_logged_in_super_admin_can_actually_load_the_admin_domain_after_a_fresh_request(): void
    {
        $super = $this->makeUser(null, 'Super Admin');

        PermissionTeam::set(999999);

        $this->actingAs($super)->withSession(['session_token' => $super->session_token])
            ->get($this->adminUrl('/superadmin'))
            ->assertOk();
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

        // Scoped exactly like production: SuperAdminSeeder creates the
        // Super Admin role under the platform team (null -> 0), tenant
        // roles live under their own tenant's team.
        PermissionTeam::run($tenantId, function () use ($role, $user) {
            Role::findOrCreate($role, 'web');
            $user->assignRole($role);
        });

        return $user;
    }
}
