<?php

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DealLifecycleTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    private Pipeline $pipeline;

    private PipelineStage $openStage;

    private PipelineStage $wonStage;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = Str::lower(Str::random(10));
        $this->tenant = Tenant::create([
            'name' => 'QA Tenant '.$suffix,
            'slug' => 'qa-'.$suffix,
            'status' => 'Active',
        ]);
        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'QA Sales User',
            'email' => "qa-{$suffix}@example.test",
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => 'qa-session-'.$suffix,
        ]);
        $this->pipeline = Pipeline::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'QA Pipeline',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $this->openStage = PipelineStage::create([
            'tenant_id' => $this->tenant->id,
            'pipeline_id' => $this->pipeline->id,
            'name' => 'Open',
            'sort_order' => 0,
            'is_won' => false,
            'is_lost' => false,
        ]);
        $this->wonStage = PipelineStage::create([
            'tenant_id' => $this->tenant->id,
            'pipeline_id' => $this->pipeline->id,
            'name' => 'Won',
            'sort_order' => 1,
            'is_won' => true,
            'is_lost' => false,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->user, 'web')
            ->withSession(['session_token' => $this->user->session_token]);
    }

    public function test_deals_sidebar_targets_the_list_page(): void
    {
        $this->grant('deals.view');

        $this->get('/deals/list')
            ->assertOk()
            ->assertSee('href="'.route('deals.list').'"', false);
    }

    public function test_deal_creation_rejects_relationships_from_another_tenant(): void
    {
        $this->grant('deals.create');
        $otherTenant = $this->createTenant('other');
        $otherLead = Lead::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Tenant Lead',
            'status' => 'Active',
            'is_converted' => 'No',
        ]);

        $this->postJson('/deals', [
            'name' => 'Invalid cross-tenant deal',
            'pipeline_id' => $this->pipeline->id,
            'stage_id' => $this->openStage->id,
            'lead_id' => $otherLead->id,
            'amount' => 100,
            'currency' => 'INR',
        ])->assertUnprocessable()->assertJsonValidationErrors('lead_id');

        $this->assertDatabaseMissing('deals', ['name' => 'Invalid cross-tenant deal']);
    }

    public function test_sales_user_cannot_move_another_users_deal(): void
    {
        $this->grant('deals.edit');
        $otherUser = $this->createUser($this->tenant, 'owner');
        $deal = $this->createDeal($otherUser);

        $this->postJson("/deals/{$deal->id}/move-stage", [
            'to_stage_id' => $this->wonStage->id,
        ])->assertNotFound();
    }

    public function test_a_lead_can_only_be_converted_once(): void
    {
        $this->grant('deals.create');
        $lead = $this->createLead($this->user);

        $first = $this->postJson("/leads/{$lead->id}/convert-to-deal", [
            'pipeline_id' => $this->pipeline->id,
            'amount' => 1250,
            'currency' => 'INR',
        ]);

        $first->assertOk();
        $this->postJson("/leads/{$lead->id}/convert-to-deal", [
            'pipeline_id' => $this->pipeline->id,
            'amount' => 1250,
            'currency' => 'INR',
        ])->assertConflict();

        $this->assertSame(1, Deal::withoutGlobalScopes()->where('lead_id', $lead->id)->count());
    }

    public function test_winning_a_deal_creates_only_one_order(): void
    {
        $this->grant('deals.edit');
        $lead = $this->createLead($this->user);
        $deal = $this->createDeal($this->user, $lead);

        $this->postJson("/deals/{$deal->id}/move-stage", [
            'to_stage_id' => $this->wonStage->id,
        ])->assertOk();

        $deal->refresh();
        $this->assertNotNull($deal->order_id);
        $this->postJson("/deals/{$deal->id}/move-stage", [
            'to_stage_id' => $this->wonStage->id,
        ])->assertUnprocessable();

        $this->assertSame(1, Order::withoutGlobalScopes()->where('lead_id', $lead->id)->count());
    }

    private function grant(string $permission): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createTenant(string $label): Tenant
    {
        $suffix = Str::lower(Str::random(10));

        return Tenant::create([
            'name' => "QA {$label} {$suffix}",
            'slug' => "qa-{$label}-{$suffix}",
            'status' => 'Active',
        ]);
    }

    private function createUser(Tenant $tenant, string $label): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'tenant_id' => $tenant->id,
            'name' => "QA {$label}",
            'email' => "qa-{$label}-{$suffix}@example.test",
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => 'qa-session-'.$suffix,
        ]);
    }

    private function createLead(User $owner): Lead
    {
        return Lead::create([
            'tenant_id' => $owner->tenant_id,
            'name' => 'QA Lead '.Str::random(8),
            'assigned_to' => $owner->id,
            'status' => 'Active',
            'is_converted' => 'No',
        ]);
    }

    private function createDeal(User $owner, Lead $lead = null): Deal
    {
        return Deal::create([
            'tenant_id' => $this->tenant->id,
            'pipeline_id' => $this->pipeline->id,
            'stage_id' => $this->openStage->id,
            'lead_id' => $lead?->id,
            'owner_id' => $owner->id,
            'created_by' => $this->user->id,
            'name' => 'QA Deal '.Str::random(8),
            'amount' => 1000,
            'currency' => 'INR',
        ]);
    }
}
