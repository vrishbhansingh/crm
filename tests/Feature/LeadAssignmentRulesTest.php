<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LeadAssignmentService;
use App\Support\PermissionTeam;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LeadAssignmentRulesTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $admin;
    private User $agentA;
    private User $agentB;
    private User $agentC;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'Lead Assign Co', 'slug' => 'lar-'.$suffix, 'status' => 'Active']);
        $this->admin = User::create(['tenant_id' => $this->tenant->id, 'name' => 'LAR Admin', 'email' => "lar-admin-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'lar-admin-'.$suffix]);
        $this->agentA = User::create(['tenant_id' => $this->tenant->id, 'name' => 'Agent A', 'email' => "lar-a-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'lar-a-'.$suffix]);
        $this->agentB = User::create(['tenant_id' => $this->tenant->id, 'name' => 'Agent B', 'email' => "lar-b-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'lar-b-'.$suffix]);
        $this->agentC = User::create(['tenant_id' => $this->tenant->id, 'name' => 'Agent C', 'email' => "lar-c-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'lar-c-'.$suffix]);

        PermissionTeam::run($this->tenant->id, function () {
            $role = Role::findOrCreate('Admin', 'web');
            $role->syncPermissions(Permission::where('guard_name', 'web')->get());
            $this->admin->assignRole($role);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->admin, 'web')->withSession(['session_token' => $this->admin->session_token]);
    }

    public function test_product_rule_round_robins_across_its_own_agents_before_falling_to_round_robin_pool(): void
    {
        TenantContext::set($this->tenant->id);

        $this->postJson('/settings/lead-assignment', [
            'rule_type' => 'product', 'match_value' => 'CNC Machine', 'agent_ids' => [$this->agentA->id, $this->agentB->id],
        ])->assertOk();
        $this->postJson('/settings/lead-assignment', [
            'rule_type' => 'round_robin', 'agent_ids' => [$this->agentC->id],
        ])->assertOk();

        $service = app(LeadAssignmentService::class);
        $this->assertSame($this->agentA->id, $service->resolve($this->tenant->id, ['product' => 'CNC Machine']));
        $this->assertSame($this->agentB->id, $service->resolve($this->tenant->id, ['product' => 'CNC Machine']));
        $this->assertSame($this->agentA->id, $service->resolve($this->tenant->id, ['product' => 'CNC Machine']));
        $this->assertSame($this->agentC->id, $service->resolve($this->tenant->id, ['product' => 'Unrelated']));
        TenantContext::clear();
    }

    public function test_priority_order_is_product_then_state_then_source(): void
    {
        TenantContext::set($this->tenant->id);
        $this->postJson('/settings/lead-assignment', ['rule_type' => 'product', 'match_value' => 'Widget', 'agent_ids' => [$this->agentA->id]])->assertOk();
        $this->postJson('/settings/lead-assignment', ['rule_type' => 'state', 'match_value' => 'Maharashtra', 'agent_ids' => [$this->agentB->id]])->assertOk();
        $this->postJson('/settings/lead-assignment', ['rule_type' => 'source', 'match_value' => 'website', 'agent_ids' => [$this->agentC->id]])->assertOk();

        $service = app(LeadAssignmentService::class);
        // Matches all three — product rule must win.
        $this->assertSame($this->agentA->id, $service->resolve($this->tenant->id, ['product' => 'Widget', 'state' => 'Maharashtra', 'lead_source' => 'website']));
        // No product match — state rule wins.
        $this->assertSame($this->agentB->id, $service->resolve($this->tenant->id, ['product' => 'Other', 'state' => 'Maharashtra', 'lead_source' => 'website']));
        // No product/state match — source rule wins.
        $this->assertSame($this->agentC->id, $service->resolve($this->tenant->id, ['product' => 'Other', 'state' => 'Other', 'lead_source' => 'website']));
        // Nothing matches and no round-robin pool configured.
        $this->assertNull($service->resolve($this->tenant->id, ['product' => 'Other', 'state' => 'Other', 'lead_source' => 'Other']));
        TenantContext::clear();
    }

    public function test_matching_is_case_insensitive(): void
    {
        TenantContext::set($this->tenant->id);
        $this->postJson('/settings/lead-assignment', ['rule_type' => 'source', 'match_value' => 'Website', 'agent_ids' => [$this->agentA->id]])->assertOk();

        $this->assertSame($this->agentA->id, app(LeadAssignmentService::class)->resolve($this->tenant->id, ['lead_source' => 'website']));
        TenantContext::clear();
    }

    public function test_creating_a_new_lead_without_an_explicit_assignee_uses_the_configured_rule(): void
    {
        TenantContext::set($this->tenant->id);
        $this->postJson('/settings/lead-assignment', ['rule_type' => 'product', 'match_value' => 'Router', 'agent_ids' => [$this->agentB->id]])->assertOk();
        TenantContext::clear();

        $response = $this->postJson('/leads', [
            'name' => 'New Lead', 'company_name' => 'New Lead Co', 'email' => 'newlead@example.test',
            'phone' => '9000000001', 'designation' => 'Manager',
            'lead_type' => 'inquiry', 'lead_source' => 'website', 'product' => 'Router', 'lead_status' => 'new', 'priority' => 'medium',
        ]);
        $response->assertOk($response->json('message'));

        $lead = Lead::where('name', 'New Lead')->first();
        $this->assertNotNull($lead);
        $this->assertSame($this->agentB->id, $lead->assigned_to);
    }

    public function test_duplicate_match_values_for_the_same_rule_type_are_rejected(): void
    {
        TenantContext::set($this->tenant->id);
        $this->postJson('/settings/lead-assignment', ['rule_type' => 'source', 'match_value' => 'referral', 'agent_ids' => [$this->agentA->id]])->assertOk();
        $this->postJson('/settings/lead-assignment', ['rule_type' => 'source', 'match_value' => 'Referral', 'agent_ids' => [$this->agentB->id]])->assertStatus(422);
        TenantContext::clear();
    }

    public function test_rules_never_leak_across_tenants(): void
    {
        $other = Tenant::create(['name' => 'Other LAR Co', 'slug' => 'lar-other-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $otherAgent = User::create(['tenant_id' => $other->id, 'name' => 'Other Agent', 'email' => 'lar-oa-'.Str::lower(Str::random(8)).'@example.test', 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(20)]);

        TenantContext::set($other->id);
        \App\Models\LeadAssignmentRule::create(['tenant_id' => $other->id, 'rule_type' => 'product', 'match_value' => 'Shared Product Name', 'agent_ids' => [$otherAgent->id]]);
        TenantContext::clear();

        TenantContext::set($this->tenant->id);
        $this->assertNull(app(LeadAssignmentService::class)->resolve($this->tenant->id, ['product' => 'Shared Product Name']));
        TenantContext::clear();
    }
}
