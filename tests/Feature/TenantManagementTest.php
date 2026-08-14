<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TenantManagementTest extends TestCase
{
    use DatabaseTransactions;

    private User $platformAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = Str::lower(Str::random(10));
        $this->platformAdmin = User::create([
            'tenant_id' => null,
            'name' => 'Platform QA',
            'email' => "platform-{$suffix}@example.test",
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => 'platform-session-'.$suffix,
        ]);
        Role::findOrCreate('Super Admin', 'web');
        foreach (['platform.manage-tenants', 'companies.view', 'companies.create'] as $permission) {
            $this->platformAdmin->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        $this->platformAdmin->assignRole('Super Admin');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->platformAdmin, 'web')
            ->withSession(['session_token' => $this->platformAdmin->session_token]);
    }

    public function test_platform_admin_can_provision_a_tenant_with_an_admin(): void
    {
        $slug = 'tenant-'.Str::lower(Str::random(10));

        $this->post('/superadmin/companies', [
            'name' => 'Provisioned Tenant',
            'slug' => $slug,
            'contact_email' => 'billing@example.test',
            'plan' => 'professional',
            'timezone' => 'Asia/Kolkata',
            'locale' => 'en',
            'max_users' => 25,
            'admin_name' => 'Tenant Admin',
            'admin_email' => $slug.'@example.test',
            'admin_password' => 'password123',
        ])->assertRedirect()->assertSessionHas('success');

        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        $admin = User::where('tenant_id', $tenant->id)->where('email', $slug.'@example.test')->firstOrFail();
        \App\Support\PermissionTeam::set($tenant->id);
        $this->assertTrue($admin->hasRole('Admin'));
        $this->assertSame(25, $tenant->max_users);
    }

    public function test_super_admin_tenant_context_route_is_not_exposed(): void
    {
        $this->post('/superadmin/company-context', ['tenant_id' => 1])->assertNotFound();
    }

    public function test_super_admin_is_redirected_out_of_the_tenant_crm(): void
    {
        config(['tenancy.mode' => 'database']);

        $this->get('/dashboard')->assertRedirect('/superadmin');
    }

    private function tenant(string $label): Tenant
    {
        $suffix = Str::lower(Str::random(8));

        return Tenant::create([
            'name' => ucfirst($label).' Tenant',
            'slug' => "{$label}-{$suffix}",
            'status' => 'Active',
        ]);
    }
}
