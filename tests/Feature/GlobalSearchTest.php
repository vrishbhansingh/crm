<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Models\User;
use App\Support\PermissionTeam;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private Tenant $otherTenant;

    private User $admin;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'Search Co', 'slug' => 'gs-'.$suffix, 'status' => 'Active']);
        $this->otherTenant = Tenant::create(['name' => 'Other Co', 'slug' => 'gs-other-'.$suffix, 'status' => 'Active']);

        $this->admin = User::create(['tenant_id' => $this->tenant->id, 'name' => 'GS Admin', 'email' => "gs-admin-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'gs-admin-'.$suffix]);
        $this->agent = User::create(['tenant_id' => $this->tenant->id, 'name' => 'GS Agent', 'email' => "gs-agent-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'gs-agent-'.$suffix]);

        PermissionTeam::run($this->tenant->id, function () {
            $role = Role::findOrCreate('Admin', 'web');
            $role->syncPermissions(Permission::where('guard_name', 'web')->get());
            $this->admin->assignRole($role);

            $agentRole = Role::findOrCreate('Sales Agent', 'web');
            $agentRole->syncPermissions(Permission::whereIn('name', ['leads.view', 'deals.view'])->where('guard_name', 'web')->get());
            $this->agent->assignRole($agentRole);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_search_returns_matching_leads_deals_companies_and_contacts_for_a_privileged_user(): void
    {
        TenantContext::set($this->tenant->id);

        Lead::create(['tenant_id' => $this->tenant->id, 'lead_number' => 1001, 'name' => 'Zephyr Industries Lead', 'lead_status' => 'new', 'status' => 'active']);
        $company = Company::create(['tenant_id' => $this->tenant->id, 'owner_id' => $this->admin->id, 'name' => 'Zephyr Industries']);
        Contact::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'owner_id' => $this->admin->id, 'name' => 'Zephyr Contact Person']);
        $pipeline = Pipeline::create(['tenant_id' => $this->tenant->id, 'name' => 'GS Pipeline', 'is_active' => true, 'sort_order' => 0]);
        $stage = PipelineStage::create(['tenant_id' => $this->tenant->id, 'pipeline_id' => $pipeline->id, 'name' => 'Prospecting', 'sort_order' => 0]);
        Deal::create(['tenant_id' => $this->tenant->id, 'owner_id' => $this->admin->id, 'company_id' => $company->id, 'pipeline_id' => $pipeline->id, 'stage_id' => $stage->id, 'name' => 'Zephyr Renewal Deal', 'status' => 'open', 'currency' => 'INR', 'amount' => 1000]);

        TenantContext::clear();

        $response = $this->actingAs($this->admin, 'web')
            ->withSession(['session_token' => $this->admin->session_token])
            ->getJson('/search?q=Zephyr');

        $response->assertOk();
        $types = collect($response->json('results'))->pluck('type')->unique()->sort()->values();
        $this->assertEquals(['Company', 'Contact', 'Deal', 'Lead'], $types->all());
    }

    public function test_short_query_returns_no_results(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->withSession(['session_token' => $this->admin->session_token])
            ->getJson('/search?q=Z');

        $response->assertOk()->assertJson(['results' => []]);
    }

    public function test_a_user_without_a_modules_view_permission_never_sees_that_module_in_results(): void
    {
        TenantContext::set($this->tenant->id);
        Company::create(['tenant_id' => $this->tenant->id, 'owner_id' => $this->admin->id, 'name' => 'Falcon Traders']);
        Lead::create(['tenant_id' => $this->tenant->id, 'lead_number' => 1002, 'name' => 'Falcon Traders Lead', 'lead_status' => 'new', 'status' => 'active', 'assigned_to' => $this->agent->id]);
        TenantContext::clear();

        // $this->agent only has leads.view and deals.view — companies.view is absent.
        $response = $this->actingAs($this->agent, 'web')
            ->withSession(['session_token' => $this->agent->session_token])
            ->getJson('/search?q=Falcon');

        $response->assertOk();
        $types = collect($response->json('results'))->pluck('type')->unique();
        $this->assertTrue($types->contains('Lead'));
        $this->assertFalse($types->contains('Company'));
    }

    public function test_a_non_elevated_agent_only_sees_their_own_leads_not_a_colleagues(): void
    {
        TenantContext::set($this->tenant->id);
        Lead::create(['tenant_id' => $this->tenant->id, 'lead_number' => 1003, 'name' => 'Orbit Mine Lead', 'lead_status' => 'new', 'status' => 'active', 'assigned_to' => $this->admin->id]);
        TenantContext::clear();

        $response = $this->actingAs($this->agent, 'web')
            ->withSession(['session_token' => $this->agent->session_token])
            ->getJson('/search?q=Orbit');

        $response->assertOk();
        $this->assertEmpty($response->json('results'));
    }

    public function test_the_dashboard_renders_the_search_box_in_the_top_bar(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->withSession(['session_token' => $this->admin->session_token])
            ->get('/dashboard');

        $response->assertOk();
        $response->assertSee('id="globalSearchInput"', false);
        $response->assertSee('id="globalSearchResults"', false);
        $response->assertSee(route('search'), false);
    }

    public function test_search_never_crosses_tenant_boundaries(): void
    {
        TenantContext::set($this->otherTenant->id);
        Company::create(['tenant_id' => $this->otherTenant->id, 'owner_id' => null, 'name' => 'Quokka Corp']);
        TenantContext::clear();

        $response = $this->actingAs($this->admin, 'web')
            ->withSession(['session_token' => $this->admin->session_token])
            ->getJson('/search?q=Quokka');

        $response->assertOk();
        $this->assertEmpty($response->json('results'));
    }
}
