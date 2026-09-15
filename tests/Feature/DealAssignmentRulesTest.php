<?php

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\DealAssignmentRule;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DealAssignmentService;
use App\Support\PermissionTeam;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DealAssignmentRulesTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $admin;
    private User $agentA;
    private User $agentB;
    private User $agentC;
    private Pipeline $pipeline;
    private PipelineStage $stage;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'Deal Assign Co', 'slug' => 'dar-'.$suffix, 'status' => 'Active']);
        $this->admin = User::create(['tenant_id' => $this->tenant->id, 'name' => 'DAR Admin', 'email' => "dar-admin-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'dar-admin-'.$suffix]);
        $this->agentA = User::create(['tenant_id' => $this->tenant->id, 'name' => 'Agent A', 'email' => "dar-a-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'dar-a-'.$suffix]);
        $this->agentB = User::create(['tenant_id' => $this->tenant->id, 'name' => 'Agent B', 'email' => "dar-b-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'dar-b-'.$suffix]);
        $this->agentC = User::create(['tenant_id' => $this->tenant->id, 'name' => 'Agent C', 'email' => "dar-c-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'dar-c-'.$suffix]);

        PermissionTeam::run($this->tenant->id, function () {
            $role = Role::findOrCreate('Admin', 'web');
            $role->syncPermissions(Permission::where('guard_name', 'web')->get());
            $this->admin->assignRole($role);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->admin, 'web')->withSession(['session_token' => $this->admin->session_token]);

        $this->pipeline = Pipeline::create(['tenant_id' => $this->tenant->id, 'name' => 'DAR Pipeline', 'is_active' => true, 'sort_order' => 0]);
        $this->stage = PipelineStage::create(['tenant_id' => $this->tenant->id, 'pipeline_id' => $this->pipeline->id, 'name' => 'Prospecting', 'sort_order' => 0]);
    }

    public function test_pipeline_rule_round_robins_before_falling_to_round_robin_pool(): void
    {
        TenantContext::set($this->tenant->id);
        $this->postJson('/settings/deal-assignment', [
            'rule_type' => 'pipeline', 'match_value' => (string) $this->pipeline->id, 'agent_ids' => [$this->agentA->id, $this->agentB->id],
        ])->assertOk();
        $this->postJson('/settings/deal-assignment', ['rule_type' => 'round_robin', 'agent_ids' => [$this->agentC->id]])->assertOk();

        $service = app(DealAssignmentService::class);
        $this->assertSame($this->agentA->id, $service->resolve($this->tenant->id, ['pipeline_id' => $this->pipeline->id]));
        $this->assertSame($this->agentB->id, $service->resolve($this->tenant->id, ['pipeline_id' => $this->pipeline->id]));
        $this->assertSame($this->agentA->id, $service->resolve($this->tenant->id, ['pipeline_id' => $this->pipeline->id]));
        $this->assertSame($this->agentC->id, $service->resolve($this->tenant->id, ['pipeline_id' => 999999]));
        TenantContext::clear();
    }

    public function test_pipeline_priority_beats_source(): void
    {
        TenantContext::set($this->tenant->id);
        $this->postJson('/settings/deal-assignment', ['rule_type' => 'pipeline', 'match_value' => (string) $this->pipeline->id, 'agent_ids' => [$this->agentA->id]])->assertOk();
        $this->postJson('/settings/deal-assignment', ['rule_type' => 'source', 'match_value' => 'referral', 'agent_ids' => [$this->agentB->id]])->assertOk();

        $service = app(DealAssignmentService::class);
        $this->assertSame($this->agentA->id, $service->resolve($this->tenant->id, ['pipeline_id' => $this->pipeline->id, 'source' => 'referral']));
        $this->assertSame($this->agentB->id, $service->resolve($this->tenant->id, ['pipeline_id' => 999999, 'source' => 'referral']));
        TenantContext::clear();
    }

    public function test_creating_a_deal_without_an_owner_uses_the_configured_rule(): void
    {
        TenantContext::set($this->tenant->id);
        $this->postJson('/settings/deal-assignment', ['rule_type' => 'pipeline', 'match_value' => (string) $this->pipeline->id, 'agent_ids' => [$this->agentB->id]])->assertOk();
        TenantContext::clear();

        $response = $this->postJson('/deals', [
            'name' => 'New Deal', 'pipeline_id' => $this->pipeline->id, 'stage_id' => $this->stage->id, 'amount' => 1000,
        ]);
        $response->assertOk();

        $deal = Deal::where('name', 'New Deal')->first();
        $this->assertNotNull($deal);
        $this->assertSame($this->agentB->id, $deal->owner_id);
    }

    public function test_deals_created_from_a_lead_inherit_the_leads_source(): void
    {
        TenantContext::set($this->tenant->id);
        $lead = \App\Models\Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Source Lead', 'lead_source' => 'indiamart', 'lead_type' => 'inquiry', 'lead_status' => 'new', 'is_converted' => 'No', 'assigned_to' => $this->agentA->id]);
        TenantContext::clear();

        $response = $this->postJson("/leads/{$lead->id}/convert-to-deal", ['pipeline_id' => $this->pipeline->id]);
        $response->assertOk();

        $deal = Deal::where('lead_id', $lead->id)->first();
        $this->assertNotNull($deal);
        $this->assertSame('indiamart', $deal->source);
        $this->assertSame($this->agentA->id, $deal->owner_id);
    }

    public function test_rules_never_leak_across_tenants(): void
    {
        $other = Tenant::create(['name' => 'Other DAR Co', 'slug' => 'dar-other-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $otherAgent = User::create(['tenant_id' => $other->id, 'name' => 'Other Agent', 'email' => 'dar-oa-'.Str::lower(Str::random(8)).'@example.test', 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(20)]);

        TenantContext::set($other->id);
        DealAssignmentRule::create(['tenant_id' => $other->id, 'rule_type' => 'pipeline', 'match_value' => (string) $this->pipeline->id, 'agent_ids' => [$otherAgent->id]]);
        TenantContext::clear();

        TenantContext::set($this->tenant->id);
        $this->assertNull(app(DealAssignmentService::class)->resolve($this->tenant->id, ['pipeline_id' => $this->pipeline->id]));
        TenantContext::clear();
    }
}
