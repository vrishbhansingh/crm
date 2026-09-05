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

class SuperAdminPortalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_uses_separate_login_and_tenant_users_cannot_enter_it(): void
    {
        $super = $this->makeUser(null, 'Super Admin');
        $tenant = Tenant::create(['name' => 'Portal Tenant', 'slug' => 'portal-'.Str::random(8), 'status' => 'Active']);
        $tenantUser = $this->makeUser($tenant->id, 'Admin');

        $this->postJson('/login', ['email' => $super->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('status', false)
            ->assertJsonPath('location', 'http://'.config('app.admin_domain').'/superadmin/login');

        $this->post('/superadmin/login', ['email' => $super->email, 'password' => 'password'])
            ->assertRedirect('/superadmin');

        // The redirect target must actually be reachable, not just the
        // right URL — a middleware that mis-scopes its own hasRole() check
        // would redirect here successfully and then 403 this exact request.
        $this->get('/superadmin')->assertOk();

        auth()->logout();
        $this->actingAs($tenantUser)->withSession(['session_token' => $tenantUser->session_token])
            ->get('/superadmin')
            ->assertForbidden();
    }

    public function test_legacy_platform_urls_are_not_registered(): void
    {
        $this->get('/platform')->assertNotFound();
        $this->get('/platform/tenants')->assertNotFound();
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
