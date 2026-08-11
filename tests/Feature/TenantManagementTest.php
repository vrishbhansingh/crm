<?php

namespace Tests\Feature;

use App\Models\Company;
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
        Role::findOrCreate('Company Admin', 'web');
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

        $this->post('/platform/tenants', [
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
        $this->assertTrue($admin->hasRole('Company Admin'));
        $this->assertSame(25, $tenant->max_users);
    }

    public function test_selected_tenant_context_isolates_platform_queries_and_writes(): void
    {
        $first = $this->tenant('first');
        $second = $this->tenant('second');
        $firstCompany = Company::withoutGlobalScopes()->create(['tenant_id' => $first->id, 'name' => 'First Company', 'status' => 'prospect']);
        $secondCompany = Company::withoutGlobalScopes()->create(['tenant_id' => $second->id, 'name' => 'Second Company', 'status' => 'prospect']);

        $this->post('/platform/tenant-context', ['tenant_id' => $first->id])->assertRedirect('/dashboard');
        $this->withSession(['tenant_context_id' => $first->id])
            ->getJson('/companies/data')
            ->assertOk()
            ->assertJsonFragment(['id' => $firstCompany->id])
            ->assertJsonMissing(['id' => $secondCompany->id]);

        $this->withSession(['tenant_context_id' => $first->id])
            ->postJson('/companies', ['name' => 'Context Company', 'status' => 'prospect'])
            ->assertOk();
        $this->assertDatabaseHas('companies', ['tenant_id' => $first->id, 'name' => 'Context Company']);
    }

    public function test_platform_writes_require_a_selected_tenant(): void
    {
        $this->postJson('/companies', ['name' => 'Ambiguous Company', 'status' => 'prospect'])
            ->assertUnprocessable();
        $this->assertDatabaseMissing('companies', ['name' => 'Ambiguous Company']);
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
