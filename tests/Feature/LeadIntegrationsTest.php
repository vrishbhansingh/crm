<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadIntegration;
use App\Models\LeadIntegrationLog;
use App\Models\Tenant;
use App\Models\User;
use App\Support\PermissionTeam;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LeadIntegrationsTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Integrations Co', 'slug' => 'integrations-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $this->admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Integrations Admin',
            'email' => Str::lower(Str::random(10)).'@example.test',
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => Str::random(60),
        ]);

        PermissionTeam::run($this->tenant->id, function () {
            $role = Role::findOrCreate('Admin', 'web');
            foreach (['integrations.view', 'integrations.create', 'integrations.edit', 'integrations.delete'] as $name) {
                $role->givePermissionTo(Permission::findOrCreate($name, 'web'));
            }
            $this->admin->assignRole($role);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->admin, 'web')->withSession(['session_token' => $this->admin->session_token]);
    }

    public function test_tenant_admin_can_view_the_integrations_page(): void
    {
        $this->get('/integrations')->assertOk()->assertSee('Lead Integrations');
    }

    public function test_tenant_admin_can_connect_a_platform(): void
    {
        $this->postJson('/integrations', ['platform' => 'website', 'name' => 'Main site form'])
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('lead_integrations', [
            'tenant_id' => $this->tenant->id,
            'platform' => 'website',
            'name' => 'Main site form',
        ]);
    }

    public function test_multiple_webhooks_of_the_same_platform_are_allowed(): void
    {
        $this->postJson('/integrations', ['platform' => 'website', 'name' => 'Main site form'])->assertOk();
        $this->postJson('/integrations', ['platform' => 'website', 'name' => 'Landing page form'])->assertOk();

        $this->assertSame(2, LeadIntegration::where('tenant_id', $this->tenant->id)->where('platform', 'website')->count());
    }

    public function test_default_lead_type_status_priority_and_assignee_are_applied(): void
    {
        $agent = User::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Dedicated Agent',
            'email' => Str::lower(Str::random(10)).'@example.test',
            'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60),
        ]);

        $integration = LeadIntegration::create([
            'tenant_id' => $this->tenant->id, 'platform' => 'website', 'name' => 'Priority form',
            'token' => LeadIntegration::generateToken(),
            'default_lead_type' => 'hot', 'default_lead_status' => 'contacted', 'default_priority' => 'high',
            'default_assigned_to' => $agent->id,
        ]);

        $this->postJson('/webhooks/leads/'.$integration->token, ['name' => 'Anita', 'phone' => '9000000001'])
            ->assertJsonPath('status', 'created');

        $lead = Lead::where('tenant_id', $this->tenant->id)->where('phone', '9000000001')->first();
        $this->assertSame('hot', $lead->lead_type);
        $this->assertSame('contacted', $lead->lead_status);
        $this->assertSame('high', $lead->priority);
        $this->assertSame($agent->id, $lead->assigned_to);
    }

    public function test_custom_field_mapping_overrides_the_built_in_heuristic(): void
    {
        $integration = LeadIntegration::create([
            'tenant_id' => $this->tenant->id, 'platform' => 'website', 'name' => 'Odd field names',
            'token' => LeadIntegration::generateToken(),
            'field_mapping' => ['phone' => 'phn', 'name' => 'full'],
        ]);

        $this->postJson('/webhooks/leads/'.$integration->token, [
            'full' => 'Mapped Name',
            'phn' => '9000000002',
        ])->assertJsonPath('status', 'created');

        $lead = Lead::where('tenant_id', $this->tenant->id)->where('phone', '9000000002')->first();
        $this->assertNotNull($lead);
        $this->assertSame('Mapped Name', $lead->name);
    }

    public function test_a_pipeline_on_the_integration_auto_creates_a_deal_in_the_first_open_stage(): void
    {
        $pipeline = \App\Models\Pipeline::create(['tenant_id' => $this->tenant->id, 'name' => 'Webhook Funnel', 'is_active' => true, 'sort_order' => 0]);
        \App\Models\PipelineStage::create(['tenant_id' => $this->tenant->id, 'pipeline_id' => $pipeline->id, 'name' => 'New', 'sort_order' => 0]);
        \App\Models\PipelineStage::create(['tenant_id' => $this->tenant->id, 'pipeline_id' => $pipeline->id, 'name' => 'Won', 'sort_order' => 1, 'is_won' => true]);

        $integration = LeadIntegration::create([
            'tenant_id' => $this->tenant->id, 'platform' => 'website', 'name' => 'Funnel form',
            'token' => LeadIntegration::generateToken(), 'pipeline_id' => $pipeline->id,
        ]);

        $response = $this->postJson('/webhooks/leads/'.$integration->token, ['name' => 'Funnel Lead', 'phone' => '9000000003']);
        $response->assertJsonPath('status', 'created');
        $dealId = $response->json('deal_id');
        $this->assertNotNull($dealId);

        $lead = Lead::where('tenant_id', $this->tenant->id)->where('phone', '9000000003')->first();
        $this->assertSame('Yes', $lead->is_converted);

        $deal = \App\Models\Deal::find($dealId);
        $this->assertSame($pipeline->id, $deal->pipeline_id);
        $this->assertSame($lead->id, $deal->lead_id);
    }

    public function test_tenant_admin_can_edit_a_webhooks_settings(): void
    {
        $integration = LeadIntegration::create([
            'tenant_id' => $this->tenant->id, 'platform' => 'website', 'name' => 'Editable form',
            'token' => LeadIntegration::generateToken(),
        ]);
        $originalToken = $integration->token;

        $this->putJson('/integrations/'.$integration->id, [
            'name' => 'Renamed form',
            'default_priority' => 'low',
        ])->assertOk();

        $integration->refresh();
        $this->assertSame('Renamed form', $integration->name);
        $this->assertSame('low', $integration->default_priority);
        $this->assertSame($originalToken, $integration->token, 'Editing settings must not change the webhook URL.');
    }

    public function test_webhook_creates_a_lead_from_a_generic_payload(): void
    {
        $integration = LeadIntegration::create([
            'tenant_id' => $this->tenant->id, 'platform' => 'website', 'name' => 'Site form',
            'token' => LeadIntegration::generateToken(),
        ]);

        $this->postJson('/webhooks/leads/'.$integration->token, [
            'name' => 'Ravi Kumar',
            'phone' => '9876543210',
            'email' => 'ravi@example.test',
            'message' => 'Need a quote for CNC machines.',
        ])->assertOk()->assertJsonPath('status', 'created');

        $lead = Lead::where('tenant_id', $this->tenant->id)->where('phone', '9876543210')->first();
        $this->assertNotNull($lead);
        $this->assertSame('Ravi Kumar', $lead->name);
        $this->assertSame('website', $lead->lead_source);
        $this->assertSame('Need a quote for CNC machines.', $lead->requirement);

        $this->assertSame(1, $integration->fresh()->leads_created_count);
    }

    public function test_webhook_parses_indiamart_field_names(): void
    {
        $integration = LeadIntegration::create([
            'tenant_id' => $this->tenant->id, 'platform' => 'indiamart', 'name' => 'IndiaMART',
            'token' => LeadIntegration::generateToken(),
        ]);

        $this->postJson('/webhooks/leads/'.$integration->token, [
            'SENDER_NAME' => 'Suresh Traders',
            'SENDER_MOBILE' => '9123456780',
            'SENDER_EMAIL' => 'suresh@example.test',
            'SENDER_COMPANY' => 'Suresh Traders Pvt Ltd',
            'QUERY_MESSAGE' => 'Looking for lathe machines',
            'UNIQUE_QUERY_ID' => 'IM12345',
        ])->assertOk()->assertJsonPath('status', 'created');

        $lead = Lead::where('tenant_id', $this->tenant->id)->where('phone', '9123456780')->first();
        $this->assertNotNull($lead);
        $this->assertSame('indiamart', $lead->lead_source);
        $this->assertSame('Suresh Traders Pvt Ltd', $lead->company_name);
    }

    public function test_duplicate_webhook_delivery_does_not_create_a_second_lead(): void
    {
        $integration = LeadIntegration::create([
            'tenant_id' => $this->tenant->id, 'platform' => 'indiamart', 'name' => 'IndiaMART',
            'token' => LeadIntegration::generateToken(),
        ]);

        $payload = [
            'SENDER_NAME' => 'Suresh Traders',
            'SENDER_MOBILE' => '9123456780',
            'UNIQUE_QUERY_ID' => 'IM-DUPE-1',
        ];

        $this->postJson('/webhooks/leads/'.$integration->token, $payload)->assertJsonPath('status', 'created');
        $this->postJson('/webhooks/leads/'.$integration->token, $payload)->assertJsonPath('status', 'duplicate');

        $this->assertSame(1, Lead::where('tenant_id', $this->tenant->id)->where('phone', '9123456780')->count());
    }

    public function test_webhook_ignores_a_payload_with_no_contact_method(): void
    {
        $integration = LeadIntegration::create([
            'tenant_id' => $this->tenant->id, 'platform' => 'website', 'name' => 'Site form',
            'token' => LeadIntegration::generateToken(),
        ]);

        $this->postJson('/webhooks/leads/'.$integration->token, ['name' => 'No Contact Info'])
            ->assertOk()->assertJsonPath('status', 'ignored');

        $this->assertDatabaseMissing('leads', ['tenant_id' => $this->tenant->id, 'name' => 'No Contact Info']);
    }

    public function test_webhook_to_a_paused_integration_does_not_create_a_lead(): void
    {
        $integration = LeadIntegration::create([
            'tenant_id' => $this->tenant->id, 'platform' => 'website', 'name' => 'Site form',
            'token' => LeadIntegration::generateToken(), 'is_active' => false,
        ]);

        $this->postJson('/webhooks/leads/'.$integration->token, ['name' => 'X', 'phone' => '111'])
            ->assertOk()->assertJsonPath('status', 'ignored');

        $this->assertSame(0, Lead::where('tenant_id', $this->tenant->id)->count());
    }

    public function test_unknown_webhook_token_404s(): void
    {
        $this->postJson('/webhooks/leads/does-not-exist')->assertNotFound();
    }

    public function test_a_tenant_cannot_modify_another_tenants_integration(): void
    {
        $otherTenant = Tenant::create(['name' => 'Other Co', 'slug' => 'other-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $foreignIntegration = LeadIntegration::create([
            'tenant_id' => $otherTenant->id, 'platform' => 'website', 'name' => 'Not yours',
            'token' => LeadIntegration::generateToken(),
        ]);

        $this->putJson('/integrations/'.$foreignIntegration->id, ['is_active' => false])->assertNotFound();
        $this->deleteJson('/integrations/'.$foreignIntegration->id)->assertNotFound();
    }

    public function test_meta_verification_handshake_echoes_challenge_when_token_matches(): void
    {
        $integration = LeadIntegration::create([
            'tenant_id' => $this->tenant->id, 'platform' => 'whatsapp', 'name' => 'WhatsApp',
            'token' => LeadIntegration::generateToken(), 'verify_token' => 'my-secret-verify-token',
        ]);

        $this->get('/webhooks/leads/'.$integration->token.'?hub_mode=subscribe&hub_verify_token=my-secret-verify-token&hub_challenge=12345')
            ->assertOk()
            ->assertSee('12345');

        $this->get('/webhooks/leads/'.$integration->token.'?hub_mode=subscribe&hub_verify_token=wrong&hub_challenge=12345')
            ->assertForbidden();
    }

    public function test_deleting_a_tenant_cascades_to_its_integrations(): void
    {
        $tenant = Tenant::create(['name' => 'Cascade Co', 'slug' => 'cascade-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $integration = LeadIntegration::create([
            'tenant_id' => $tenant->id, 'platform' => 'website', 'name' => 'Site form',
            'token' => LeadIntegration::generateToken(),
        ]);
        LeadIntegrationLog::create([
            'lead_integration_id' => $integration->id, 'tenant_id' => $tenant->id,
            'status' => 'created', 'payload' => ['a' => 1], 'created_at' => now(),
        ]);

        $tenant->delete();

        $this->assertDatabaseMissing('lead_integrations', ['id' => $integration->id]);
        $this->assertDatabaseMissing('lead_integration_logs', ['lead_integration_id' => $integration->id]);
    }
}
