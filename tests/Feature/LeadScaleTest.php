<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\LeadsAssigned;
use App\Support\DefaultTenantRoles;
use App\Support\PermissionTeam;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/** Filters, page sizes and bulk assignment on a lead book that no longer fits on one screen. */
class LeadScaleTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $admin;
    private User $agent;
    private User $rep;

    protected function setUp(): void
    {
        parent::setUp();

        $s = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'Scale Co', 'slug' => 'scale-'.$s, 'status' => 'Active']);
        $make = fn (string $n) => User::create(['tenant_id' => $this->tenant->id, 'name' => $n, 'email' => Str::slug($n)."-{$s}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(30)]);
        $this->admin = $make('Scale Admin');
        $this->agent = $make('Scale Agent');
        $this->rep = $make('Scale Rep');

        PermissionTeam::run($this->tenant->id, function () {
            $adminRole = Role::findOrCreate('Admin', 'web');
            $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());
            $this->admin->assignRole($adminRole);
            $this->agent->assignRole(DefaultTenantRoles::seedAgentRole());
            $this->agent->givePermissionTo(Permission::findOrCreate('leads.assign', 'web'));
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        PermissionTeam::set($this->tenant->id);
        TenantContext::set($this->tenant->id);

        $this->actingAs($this->admin, 'web')->withSession(['session_token' => $this->admin->session_token]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        PermissionTeam::set(null);
        parent::tearDown();
    }

    /** 12 Maharashtra (Pune x8, Mumbai x4) + 6 Gujarat leads, spread over products and dates. */
    private function seedLeads(?User $owner = null): void
    {
        $i = 0;
        foreach ([['Maharashtra', 'Pune', 8], ['Maharashtra', 'Mumbai', 4], ['Gujarat', 'Surat', 6]] as [$state, $city, $n]) {
            for ($k = 0; $k < $n; $k++) {
                $i++;
                $lead = Lead::create([
                    'tenant_id' => $this->tenant->id, 'name' => "Lead {$i}", 'phone' => '9'.str_pad((string) (100000000 + $i), 9, '0'), 'email' => "lead{$i}@example.test",
                    'state' => $state, 'city' => $city, 'product' => $i % 2 ? 'Router' : 'Switch', 'lead_type' => $i % 3 ? 'inquiry' : 'customer',
                    'lead_status' => 'new', 'lead_source' => 'website', 'priority' => 'medium', 'is_converted' => 'No',
                    'assigned_to' => $owner?->id,
                ]);
                // created_at is not mass-assignable, so back-date it explicitly.
                $lead->forceFill(['created_at' => now()->subDays($i)])->save();
            }
        }
    }

    private function names(array $query = []): array
    {
        return collect($this->getJson('/leads/data?'.http_build_query($query))->assertOk()->json('data'))->pluck('name')->all();
    }

    public function test_every_filter_narrows_the_list_and_they_combine(): void
    {
        $this->seedLeads();

        $this->assertCount(18, $this->names(['per_page' => 50]));
        $this->assertCount(12, $this->names(['per_page' => 50, 'state' => 'Maharashtra']));
        $this->assertCount(8, $this->names(['per_page' => 50, 'state' => 'Maharashtra', 'city' => 'Pune']));
        $this->assertCount(6, $this->names(['per_page' => 50, 'state' => 'Gujarat']));
        $this->assertCount(9, $this->names(['per_page' => 50, 'product' => 'Router']));
        $this->assertCount(6, $this->names(['per_page' => 50, 'lead_type' => 'customer']));
        $this->assertCount(4, $this->names(['per_page' => 50, 'state' => 'Maharashtra', 'product' => 'Router', 'lead_type' => 'inquiry']), 'filters combine (AND)');

        // Created-date range: leads 3..6 days old.
        $inRange = $this->names(['per_page' => 50, 'date_from' => now()->subDays(6)->toDateString(), 'date_to' => now()->subDays(3)->toDateString()]);
        $this->assertCount(4, $inRange);

        // Junk dates are ignored rather than breaking the list.
        $this->assertCount(18, $this->names(['per_page' => 50, 'date_from' => 'not-a-date']));
    }

    public function test_page_size_goes_up_to_200_and_no_further_and_the_total_is_always_reported(): void
    {
        $this->seedLeads();

        $big = $this->getJson('/leads/data?per_page=5000')->assertOk();
        $this->assertSame(200, $big->json('meta.per_page'), 'capped at 200');
        $this->assertSame(18, $big->json('meta.total'));
        $this->assertSame(5, $this->getJson('/leads/data?per_page=1')->json('meta.per_page'), 'and never below 5');
        $this->assertSame(25, $this->getJson('/leads/data')->json('meta.per_page'), 'default is 25');
    }

    public function test_the_list_does_not_look_up_the_assignee_once_per_row(): void
    {
        $this->seedLeads($this->rep);

        DB::enableQueryLog();
        $rows = $this->getJson('/leads/data?per_page=50')->assertOk()->json('data');
        $userQueries = collect(DB::getQueryLog())->filter(fn ($q) => preg_match('/from `?users`?/i', $q['query']))->count();
        DB::disableQueryLog();

        $this->assertCount(18, $rows);
        $this->assertSame('Scale Rep', $rows[0]['assigned_to']);
        $this->assertLessThanOrEqual(5, $userQueries, "18 rows used {$userQueries} user queries; it must not grow with the page size");
    }

    public function test_filter_options_list_only_what_the_user_can_see_and_cities_follow_their_state(): void
    {
        $this->seedLeads();
        Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Hidden', 'phone' => '9777777777', 'state' => 'Kerala', 'city' => 'Kochi', 'product' => 'Secret', 'lead_type' => 'inquiry', 'lead_status' => 'new', 'is_converted' => 'No', 'assigned_to' => $this->rep->id]);

        $admin = $this->getJson('/leads/filter-options')->assertOk()->json();
        $this->assertEqualsCanonicalizing(['Gujarat', 'Kerala', 'Maharashtra'], $admin['states']);
        $this->assertEqualsCanonicalizing(['Mumbai', 'Pune'], $admin['cities_by_state']['Maharashtra']);
        $this->assertEqualsCanonicalizing(['Router', 'Secret', 'Switch'], $admin['products']);

        // An agent sees only the options from their own leads.
        $mine = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Mine', 'phone' => '9666666666', 'state' => 'Goa', 'city' => 'Panaji', 'product' => 'Cable', 'lead_type' => 'inquiry', 'lead_status' => 'new', 'is_converted' => 'No', 'assigned_to' => $this->agent->id]);
        $this->actingAs($this->agent, 'web')->withSession(['session_token' => $this->agent->session_token]);
        $agent = $this->getJson('/leads/filter-options')->assertOk()->json();
        $this->assertSame(['Goa'], $agent['states']);
        $this->assertSame(['Cable'], $agent['products']);
        $this->assertArrayNotHasKey('Kerala', $agent['cities_by_state']);
    }

    public function test_assign_all_matching_uses_the_filters_updates_everything_and_sends_one_notification(): void
    {
        $this->seedLeads();

        $response = $this->postJson('/leads/bulk-assign', ['user_id' => $this->rep->id, 'all_matching' => 1, 'converted' => 0, 'state' => 'Maharashtra', 'city' => 'Pune'])->assertOk();

        $this->assertSame(8, $response->json('count'));
        $this->assertSame(8, Lead::where('assigned_to', $this->rep->id)->count(), 'only the 8 Pune leads');
        $this->assertSame(0, Lead::where('assigned_to', $this->rep->id)->where('city', '!=', 'Pune')->count());
        $this->assertSame(8, LeadActivity::where('user_id', $this->admin->id)->where('type', 'assigned')->count(), 'each lead gets a timeline entry');
        $this->assertSame(1, $this->rep->notifications()->where('type', LeadsAssigned::class)->count(), 'one summary alert, not one per lead');
        $this->assertStringContainsString('8 leads assigned to you', $this->rep->notifications()->first()->data['title']);
    }

    public function test_assign_all_matching_refuses_an_over_large_selection_and_unknown_assignees(): void
    {
        $this->seedLeads();
        config(['crm.bulk_assign_limit' => 5]);

        $this->postJson('/leads/bulk-assign', ['user_id' => $this->rep->id, 'all_matching' => 1, 'converted' => 0])
            ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'Narrow the filters'));
        $this->assertSame(0, Lead::where('assigned_to', $this->rep->id)->count(), 'nothing changed');

        config(['crm.bulk_assign_limit' => 5000]);
        $other = Tenant::create(['name' => 'Other', 'slug' => 'o-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $stranger = User::create(['tenant_id' => $other->id, 'name' => 'Stranger', 'email' => 'stranger-'.Str::random(6).'@example.test', 'password' => Hash::make('x'), 'status' => 'Active', 'session_token' => Str::random(20)]);
        $this->postJson('/leads/bulk-assign', ['user_id' => $stranger->id, 'all_matching' => 1, 'converted' => 0])->assertNotFound();
    }

    public function test_an_agent_can_only_bulk_assign_leads_they_can_see(): void
    {
        $this->seedLeads();
        $mine = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Mine', 'phone' => '9555555555', 'lead_type' => 'inquiry', 'lead_status' => 'new', 'is_converted' => 'No', 'assigned_to' => $this->agent->id]);
        $notMine = Lead::where('name', 'Lead 1')->firstOrFail();
        $this->actingAs($this->agent, 'web')->withSession(['session_token' => $this->agent->session_token]);

        // By id: someone else's lead is rejected outright.
        $this->postJson('/leads/bulk-assign', ['user_id' => $this->rep->id, 'lead_ids' => [$mine->id, $notMine->id]])->assertStatus(422);
        $this->assertNull($notMine->fresh()->assigned_to);

        // All matching: scoped to the agent's own leads only.
        $this->postJson('/leads/bulk-assign', ['user_id' => $this->rep->id, 'all_matching' => 1, 'converted' => 0])->assertOk()->assertJsonPath('count', 1);
        $this->assertSame($this->rep->id, $mine->fresh()->assigned_to);
        $this->assertNull($notMine->fresh()->assigned_to, 'the company\'s other leads were never touched');
    }

    public function test_the_leads_page_has_the_compact_stats_the_full_filter_panel_and_bulk_selection(): void
    {
        $html = $this->get('/leads')->assertOk()->getContent();

        foreach (['leadFilterState', 'leadFilterCity', 'leadFilterProduct', 'leadFilterType', 'leadFilterDateFrom', 'leadFilterDateTo', 'selectAllLeads', 'selectAllMatchingBtn'] as $id) {
            $this->assertStringContainsString('id="'.$id.'"', $html, "{$id} is missing from the page");
        }
        $this->assertStringNotContainsString('lead-stat-icon" style=', $html, 'the KPI icons are gone');
    }
}
