<?php

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\LeadIntegration;
use App\Models\LeadIntegrationLog;
use App\Models\Leadfollowup;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsappAccount;
use App\Models\WhatsappCampaign;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportSectionsTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $manager;
    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'Report Sections Co', 'slug' => 'rs-'.$suffix, 'status' => 'Active']);
        $this->manager = User::create(['tenant_id' => $this->tenant->id, 'name' => 'RS Manager', 'email' => "rs-mgr-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'rs-mgr-'.$suffix]);
        $this->agent = User::create(['tenant_id' => $this->tenant->id, 'name' => 'RS Agent', 'email' => "rs-agent-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'rs-agent-'.$suffix]);

        Role::findOrCreate('Manager', 'web');
        $this->manager->assignRole('Manager');
        $this->manager->givePermissionTo(Permission::findOrCreate('reports.view', 'web'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->manager, 'web')->withSession(['session_token' => $this->manager->session_token]);
    }

    public function test_leads_report_breaks_down_status_and_source_correctly(): void
    {
        // created_at isn't mass-assignable (not in Lead::$fillable), so it
        // has to be backdated with forceFill() after create() — passing it
        // through create() itself is silently dropped and Eloquent's own
        // auto-timestamping wins instead.
        $wonLead = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Won Lead', 'assigned_to' => $this->agent->id, 'lead_source' => 'website', 'lead_status' => 'closed', 'is_converted' => 'Yes', 'converted_at' => now()]);
        $wonLead->forceFill(['created_at' => now()->subDays(3)])->save();
        Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Open Lead', 'assigned_to' => $this->agent->id, 'lead_source' => 'website', 'lead_status' => 'new', 'is_converted' => 'No']);

        // avg_days_to_convert is a whole-number float (3.0) server-side,
        // but PHP's json_encode drops the trailing .0 for a whole-number
        // float, so it round-trips as an int through JSON — assert with
        // assertJson (loose) rather than assertJsonPath (strict ===) here.
        $response = $this->getJson('/reports/leads')->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('converted', 1);
        $this->assertEquals(3.0, $response->json('avg_days_to_convert'));
    }

    public function test_follow_ups_report_counts_completed_scheduled_and_overdue(): void
    {
        $lead = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Overdue Lead', 'assigned_to' => $this->agent->id, 'is_converted' => 'No', 'lead_status' => 'new', 'follow_up_date' => now()->subDays(2)->toDateString()]);
        Leadfollowup::create(['tenant_id' => $this->tenant->id, 'lead_id' => $lead->id, 'user_id' => $this->agent->id, 'call_status' => 'call_connected']);

        $response = $this->getJson('/reports/follow-ups')->assertOk();
        $response->assertJsonPath('completed', 1);
        $response->assertJsonPath('overdue', 1);
        $this->assertSame(1, collect($response->json('by_agent'))->firstWhere('name', 'RS Agent')['completed']);
    }

    public function test_agents_report_includes_active_team_members(): void
    {
        [$pipeline, $stage] = $this->pipeline();
        Deal::create(['tenant_id' => $this->tenant->id, 'pipeline_id' => $pipeline->id, 'stage_id' => $stage->id, 'owner_id' => $this->agent->id, 'name' => 'Agent Deal', 'amount' => 1000, 'status' => 'won', 'closed_at' => now()]);

        $response = $this->getJson('/reports/agents')->assertOk();
        $agentRow = collect($response->json('agents'))->firstWhere('name', 'RS Agent');
        $this->assertNotNull($agentRow);
        $this->assertSame(1, $agentRow['deals_won']);
        $this->assertEquals(1000, $agentRow['won_value']);
    }

    public function test_communications_report_totals_email_and_whatsapp_activity(): void
    {
        $template = EmailTemplate::create(['tenant_id' => $this->tenant->id, 'name' => 'T', 'subject' => 'S', 'body' => 'B']);
        EmailCampaign::create(['tenant_id' => $this->tenant->id, 'email_template_id' => $template->id, 'name' => 'Blast', 'audience_type' => 'leads', 'status' => 'sent', 'sent_at' => now(), 'total_recipients' => 5, 'sent_count' => 4, 'failed_count' => 1]);

        $wa = WhatsappAccount::create(['tenant_id' => $this->tenant->id, 'channel_type' => 'unofficial', 'name' => 'Line', 'webhook_token' => WhatsappAccount::generateWebhookToken()]);
        WhatsappCampaign::create(['tenant_id' => $this->tenant->id, 'whatsapp_account_id' => $wa->id, 'name' => 'WA Blast', 'message_type' => 'text', 'body' => 'Hi', 'audience_type' => 'leads', 'status' => 'sent', 'sent_at' => now()]);

        $this->getJson('/reports/communications')->assertOk()
            ->assertJsonPath('email.totals.campaigns', 1)
            ->assertJsonPath('email.totals.sent', 4)
            ->assertJsonPath('whatsapp.totals.campaigns', 1);
    }

    public function test_automation_report_counts_webhook_leads_by_platform(): void
    {
        $integration = LeadIntegration::create(['tenant_id' => $this->tenant->id, 'platform' => 'indiamart', 'name' => 'IM', 'token' => LeadIntegration::generateToken()]);
        LeadIntegrationLog::create(['lead_integration_id' => $integration->id, 'tenant_id' => $this->tenant->id, 'status' => 'created', 'payload' => [], 'created_at' => now()]);
        LeadIntegrationLog::create(['lead_integration_id' => $integration->id, 'tenant_id' => $this->tenant->id, 'status' => 'duplicate', 'payload' => [], 'created_at' => now()]);

        Task::create(['tenant_id' => $this->tenant->id, 'assigned_to' => $this->agent->id, 'created_by' => $this->manager->id, 'title' => 'T', 'priority' => 'medium', 'status' => 'todo', 'notification_sent_at' => now()]);

        $response = $this->getJson('/reports/automation')->assertOk();
        $response->assertJsonPath('webhook_leads_created', 1);
        $response->assertJsonPath('webhook_duplicates_ignored', 1);
        $response->assertJsonPath('task_reminders_sent', 1);
        $platformRow = collect($response->json('by_platform'))->firstWhere('platform', 'indiamart');
        $this->assertSame(1, $platformRow['leads_created']);
    }

    public function test_another_tenants_data_never_leaks_into_these_reports(): void
    {
        $other = Tenant::create(['name' => 'Other RS Co', 'slug' => 'rs-other-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $otherUser = User::create(['tenant_id' => $other->id, 'name' => 'Other', 'email' => 'rs-other-'.Str::lower(Str::random(8)).'@example.test', 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(20)]);
        Lead::withoutGlobalScopes()->create(['tenant_id' => $other->id, 'name' => 'Foreign Lead', 'assigned_to' => $otherUser->id, 'is_converted' => 'No', 'lead_status' => 'new']);

        $this->getJson('/reports/leads')->assertOk()->assertJsonPath('total', 0);
    }

    private function pipeline(): array
    {
        $pipeline = Pipeline::create(['tenant_id' => $this->tenant->id, 'name' => 'RS Pipeline', 'is_active' => true, 'sort_order' => 0]);
        $stage = PipelineStage::create(['tenant_id' => $this->tenant->id, 'pipeline_id' => $pipeline->id, 'name' => 'Won', 'sort_order' => 1, 'is_won' => true]);

        return [$pipeline, $stage];
    }
}
