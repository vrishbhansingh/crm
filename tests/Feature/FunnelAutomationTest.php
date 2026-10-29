<?php

namespace Tests\Feature;

use App\Imports\LeadsImport;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadIntegration;
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

/** "Default funnel": every lead source feeds the company's funnel unless it names its own. */
class FunnelAutomationTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $admin;
    private Pipeline $funnelA;
    private Pipeline $funnelB;

    protected function setUp(): void
    {
        parent::setUp();

        $s = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'Funnel Co', 'slug' => 'funnel-'.$s, 'status' => 'Active']);
        $this->admin = User::create(['tenant_id' => $this->tenant->id, 'name' => 'Funnel Admin', 'email' => "funnel-{$s}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(30)]);

        PermissionTeam::run($this->tenant->id, function () {
            $role = Role::findOrCreate('Admin', 'web');
            $role->syncPermissions(Permission::where('guard_name', 'web')->get());
            $this->admin->assignRole($role);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        PermissionTeam::set($this->tenant->id);
        TenantContext::set($this->tenant->id);

        $this->funnelA = $this->makeFunnel('Funnel A');
        $this->funnelB = $this->makeFunnel('Funnel B');

        $this->actingAs($this->admin, 'web')->withSession(['session_token' => $this->admin->session_token]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        PermissionTeam::set(null);
        parent::tearDown();
    }

    private function makeFunnel(string $name): Pipeline
    {
        $pipeline = Pipeline::create(['tenant_id' => $this->tenant->id, 'name' => $name, 'is_active' => true]);
        PipelineStage::create(['tenant_id' => $this->tenant->id, 'pipeline_id' => $pipeline->id, 'name' => $name.' Won', 'sort_order' => 9, 'is_won' => true, 'probability' => 100]);
        PipelineStage::create(['tenant_id' => $this->tenant->id, 'pipeline_id' => $pipeline->id, 'name' => $name.' New', 'sort_order' => 1, 'probability' => 10]);

        return $pipeline;
    }

    private function setDefault(?Pipeline $pipeline): void
    {
        $this->putJson('/settings/lead-assignment/funnel', ['pipeline_id' => $pipeline?->id])->assertOk();
    }

    private function dealFor(string $leadName): ?Deal
    {
        $lead = Lead::where('tenant_id', $this->tenant->id)->where('name', $leadName)->first();

        return $lead ? Deal::where('lead_id', $lead->id)->first() : null;
    }

    public function test_default_funnel_can_be_set_switched_off_and_only_to_this_companys_funnels(): void
    {
        $this->setDefault($this->funnelA);
        $this->assertSame($this->funnelA->id, $this->tenant->fresh()->default_pipeline_id);

        $this->setDefault(null);
        $this->assertNull($this->tenant->fresh()->default_pipeline_id);

        $other = Tenant::create(['name' => 'Other Co', 'slug' => 'other-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $foreign = Pipeline::withoutGlobalScope('tenant')->create(['tenant_id' => $other->id, 'name' => 'Not yours', 'is_active' => true]);
        $this->putJson('/settings/lead-assignment/funnel', ['pipeline_id' => $foreign->id])->assertStatus(422);
        $this->assertNull($this->tenant->fresh()->default_pipeline_id);
    }

    public function test_a_lead_added_by_hand_goes_into_the_default_funnel_in_its_first_open_stage(): void
    {
        $this->setDefault($this->funnelA);

        $this->postJson('/leads', [
            'name' => 'Manual Lead', 'company_name' => 'Manual Co', 'email' => 'manual@example.test', 'phone' => '9000000101', 'designation' => 'Owner',
            'lead_type' => 'inquiry', 'lead_source' => 'website', 'lead_status' => 'new', 'priority' => 'medium',
        ])->assertOk()->assertJsonPath('status', true);

        $deal = $this->dealFor('Manual Lead');
        $this->assertNotNull($deal, 'the lead should have entered the funnel');
        $this->assertSame($this->funnelA->id, $deal->pipeline_id);
        $this->assertSame('Funnel A New', PipelineStage::find($deal->stage_id)->name);
        $this->assertSame('Yes', Lead::find($deal->lead_id)->is_converted);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $deal->lead_id, 'type' => 'converted']);
        $this->assertDatabaseHas('deal_stage_history', ['deal_id' => $deal->id, 'to_stage_id' => $deal->stage_id]);
    }

    public function test_with_no_default_funnel_leads_stay_plain_leads(): void
    {
        $this->postJson('/leads', [
            'name' => 'Plain Lead', 'company_name' => 'Plain Co', 'email' => 'plain@example.test', 'phone' => '9000000102', 'designation' => 'Owner',
            'lead_type' => 'inquiry', 'lead_source' => 'website', 'lead_status' => 'new', 'priority' => 'medium',
        ])->assertOk();

        $this->assertNull($this->dealFor('Plain Lead'));
        $this->assertSame('No', Lead::where('name', 'Plain Lead')->first()->is_converted);
    }

    public function test_bulk_uploaded_leads_enter_the_default_funnel(): void
    {
        $this->setDefault($this->funnelB);

        $import = new LeadsImport($this->tenant->id);
        $import->collection(collect([
            ['name' => 'Bulk One', 'email' => 'bulk1@example.test', 'phone' => '9000000111', 'lead_source' => 'referral'],
            ['name' => 'Bulk Two', 'email' => 'bulk2@example.test', 'phone' => '9000000112', 'lead_source' => 'referral'],
        ]));

        foreach (['Bulk One', 'Bulk Two'] as $name) {
            $this->assertSame($this->funnelB->id, $this->dealFor($name)?->pipeline_id, "{$name} should be in Funnel B");
        }
    }

    public function test_an_integrations_own_funnel_wins_and_one_without_a_funnel_uses_the_default(): void
    {
        $this->setDefault($this->funnelB);
        $own = LeadIntegration::create(['tenant_id' => $this->tenant->id, 'platform' => 'website', 'name' => 'Site form', 'token' => LeadIntegration::generateToken(), 'pipeline_id' => $this->funnelA->id]);
        $none = LeadIntegration::create(['tenant_id' => $this->tenant->id, 'platform' => 'website', 'name' => 'Other form', 'token' => LeadIntegration::generateToken()]);

        $this->postJson('/webhooks/leads/'.$own->token, ['name' => 'Has Own Funnel', 'phone' => '9000000121'])->assertJsonPath('status', 'created');
        $this->postJson('/webhooks/leads/'.$none->token, ['name' => 'Uses Default', 'phone' => '9000000122'])->assertJsonPath('status', 'created');

        $this->assertSame($this->funnelA->id, $this->dealFor('Has Own Funnel')?->pipeline_id);
        $this->assertSame($this->funnelB->id, $this->dealFor('Uses Default')?->pipeline_id);
    }

    public function test_a_lead_never_gets_two_deals_and_a_broken_default_never_blocks_lead_capture(): void
    {
        $this->setDefault($this->funnelA);
        $service = app(\App\Services\LeadFunnelService::class);

        $lead = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Once Only', 'phone' => '9000000131', 'lead_type' => 'inquiry', 'lead_status' => 'new', 'is_converted' => 'No']);
        $this->assertNotNull($service->enter($lead));
        $this->assertNull($service->enter($lead->fresh()), 'already in a funnel: no second deal');
        $this->assertSame(1, Deal::where('lead_id', $lead->id)->count());

        // The default funnel is deactivated later: new leads must still be created, just not as deals.
        $this->funnelA->update(['is_active' => false]);
        $this->postJson('/leads', [
            'name' => 'Still Captured', 'company_name' => 'Still Co', 'email' => 'still@example.test', 'phone' => '9000000132', 'designation' => 'Owner',
            'lead_type' => 'inquiry', 'lead_source' => 'website', 'lead_status' => 'new', 'priority' => 'medium',
        ])->assertOk()->assertJsonPath('status', true);
        $this->assertNotNull(Lead::where('name', 'Still Captured')->first());
        $this->assertNull($this->dealFor('Still Captured'));
    }

    public function test_the_lead_assignment_page_shows_the_default_funnel_setting(): void
    {
        $this->setDefault($this->funnelB);

        $this->get('/settings/lead-assignment')->assertOk()
            ->assertSee('Default funnel for new leads')
            ->assertSee('Funnel A')
            ->assertSee('selected', false);
    }
}
