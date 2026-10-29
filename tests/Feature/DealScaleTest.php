<?php

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Models\User;
use App\Support\DefaultTenantRoles;
use App\Support\PermissionTeam;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/** The Kanban board and the deals list with far more deals than fit on a screen. */
class DealScaleTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $admin;
    private User $agent;
    private Pipeline $pipeline;
    private PipelineStage $early;
    private PipelineStage $late;

    protected function setUp(): void
    {
        parent::setUp();

        $s = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'Deal Scale Co', 'slug' => 'dscale-'.$s, 'status' => 'Active']);
        $make = fn (string $n) => User::create(['tenant_id' => $this->tenant->id, 'name' => $n, 'email' => Str::slug($n)."-{$s}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(30)]);
        $this->admin = $make('Deal Admin');
        $this->agent = $make('Deal Agent');

        PermissionTeam::run($this->tenant->id, function () {
            $adminRole = Role::findOrCreate('Admin', 'web');
            $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());
            $this->admin->assignRole($adminRole);
            $this->agent->assignRole(DefaultTenantRoles::seedAgentRole());
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        PermissionTeam::set($this->tenant->id);
        TenantContext::set($this->tenant->id);

        $this->pipeline = Pipeline::create(['tenant_id' => $this->tenant->id, 'name' => 'Sales', 'is_active' => true]);
        $this->early = PipelineStage::create(['tenant_id' => $this->tenant->id, 'pipeline_id' => $this->pipeline->id, 'name' => 'Prospecting', 'sort_order' => 1, 'probability' => 10]);
        $this->late = PipelineStage::create(['tenant_id' => $this->tenant->id, 'pipeline_id' => $this->pipeline->id, 'name' => 'Negotiation', 'sort_order' => 2, 'probability' => 60]);

        $this->actingAs($this->admin, 'web')->withSession(['session_token' => $this->admin->session_token]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        PermissionTeam::set(null);
        parent::tearDown();
    }

    private function deal(string $name, PipelineStage $stage, User $owner, float $amount = 1000, ?string $close = null): Deal
    {
        return Deal::create([
            'tenant_id' => $this->tenant->id, 'pipeline_id' => $this->pipeline->id, 'stage_id' => $stage->id, 'owner_id' => $owner->id,
            'name' => $name, 'amount' => $amount, 'status' => 'open', 'expected_close_date' => $close,
        ]);
    }

    private function board(array $query = []): array
    {
        return collect($this->getJson('/deals/board-data?'.http_build_query($query))->assertOk()->json('data.stages'))->keyBy('name')->all();
    }

    public function test_a_big_stage_sends_one_page_of_cards_with_the_real_total_and_loads_the_rest_on_demand(): void
    {
        for ($i = 1; $i <= 35; $i++) {
            $this->deal("Prospect {$i}", $this->early, $this->admin, 100);
        }
        $this->deal('Big negotiation', $this->late, $this->admin, 5000);

        $stages = $this->board();
        $early = $stages['Prospecting'];

        $this->assertCount(30, $early['deals'], 'only the first page of cards is sent');
        $this->assertSame(35, $early['deal_count'], 'but the real count is reported');
        $this->assertEquals(3500.0, $early['total_amount']);
        $this->assertTrue($early['has_more']);
        $this->assertSame('Prospect 35', $early['deals'][0]['name'], 'newest first');
        $this->assertFalse($stages['Negotiation']['has_more']);
        $this->assertCount(1, $stages['Negotiation']['deals']);

        $more = $this->getJson("/deals/board-stage/{$this->early->id}?offset=30")->assertOk()->json('data');
        $this->assertCount(5, $more['deals']);
        $this->assertFalse($more['has_more']);
        $this->assertSame('Prospect 5', $more['deals'][0]['name']);

        // From the very start, the button would say 5 more remain.
        $first = $this->getJson("/deals/board-stage/{$this->early->id}?offset=0")->json('data');
        $this->assertCount(30, $first['deals']);
        $this->assertTrue($first['has_more']);
        $this->assertSame(5, $first['remaining']);
    }

    public function test_the_board_and_load_more_only_ever_show_an_agents_own_deals(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            $this->deal("Boss deal {$i}", $this->early, $this->admin);
        }
        $this->deal('Agent deal', $this->early, $this->agent, 250);

        $this->actingAs($this->agent, 'web')->withSession(['session_token' => $this->agent->session_token]);
        $early = $this->board()['Prospecting'];

        $this->assertSame(1, $early['deal_count']);
        $this->assertEquals(250.0, $early['total_amount']);
        $this->assertSame(['Agent deal'], collect($early['deals'])->pluck('name')->all());
        $this->assertSame(['Agent deal'], collect($this->getJson("/deals/board-stage/{$this->early->id}")->json('data.deals'))->pluck('name')->all());
    }

    public function test_the_deals_list_pages_up_to_200_and_filters_by_stage_owner_and_close_date(): void
    {
        $this->deal('Early A', $this->early, $this->admin, 100, '2026-11-05');
        $this->deal('Early B', $this->early, $this->agent, 100, '2026-12-20');
        $this->deal('Late A', $this->late, $this->agent, 100, '2026-11-25');

        $names = fn (array $q) => collect($this->getJson('/deals/data?'.http_build_query($q))->assertOk()->json('data'))->pluck('name')->sort()->values()->all();

        $this->assertSame(200, $this->getJson('/deals/data?per_page=9999')->json('meta.per_page'));
        $this->assertSame(['Early A', 'Early B', 'Late A'], $names([]));
        $this->assertSame(['Early A', 'Early B'], $names(['stage_id' => $this->early->id]));
        $this->assertSame(['Early B', 'Late A'], $names(['owner_id' => $this->agent->id]));
        $this->assertSame(['Early B'], $names(['stage_id' => $this->early->id, 'owner_id' => $this->agent->id]));
        $this->assertSame(['Early A', 'Late A'], $names(['date_from' => '2026-11-01', 'date_to' => '2026-11-30']));
        $this->assertSame(['Early A', 'Early B', 'Late A'], $names(['date_from' => 'garbage']));
    }

    public function test_an_agent_cannot_use_the_owner_filter_to_see_other_peoples_deals(): void
    {
        $this->deal('Boss deal', $this->early, $this->admin);
        $this->deal('Agent deal', $this->early, $this->agent);
        $this->actingAs($this->agent, 'web')->withSession(['session_token' => $this->agent->session_token]);

        $rows = $this->getJson('/deals/data?owner_id='.$this->admin->id)->assertOk()->json('data');
        $this->assertSame(['Agent deal'], collect($rows)->pluck('name')->all());
    }

    public function test_filter_options_offer_stages_to_everyone_but_owners_only_to_people_who_see_the_whole_team(): void
    {
        $admin = $this->getJson('/deals/filter-options')->assertOk()->json();
        $this->assertSame(['Prospecting', 'Negotiation'], collect($admin['stages'])->pluck('label')->all());
        $this->assertEqualsCanonicalizing(['Deal Admin', 'Deal Agent'], collect($admin['owners'])->pluck('name')->all());

        $this->actingAs($this->agent, 'web')->withSession(['session_token' => $this->agent->session_token]);
        $agent = $this->getJson('/deals/filter-options')->assertOk()->json();
        $this->assertCount(2, $agent['stages']);
        $this->assertSame([], $agent['owners']);
    }

    public function test_the_deal_pages_render_with_the_new_controls(): void
    {
        $this->get('/deals/list')->assertOk()->assertSee('dealFilterStage', false)->assertSee('dealFilterOwner', false)->assertSee('dealFilterFrom', false);
        $this->get('/deals')->assertOk()->assertSee('kanban-load-more', false);
    }
}
