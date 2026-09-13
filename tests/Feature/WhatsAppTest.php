<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsappAccount;
use App\Models\WhatsappCampaign;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Support\PermissionTeam;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WhatsAppTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'WhatsApp Co', 'slug' => 'whatsapp-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $this->admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'WhatsApp Admin',
            'email' => Str::lower(Str::random(10)).'@example.test',
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => Str::random(60),
        ]);

        PermissionTeam::run($this->tenant->id, function () {
            $role = Role::findOrCreate('Admin', 'web');
            foreach (['whatsapp.view', 'whatsapp.create', 'whatsapp.send', 'whatsapp.manage-settings', 'whatsapp.delete'] as $name) {
                $role->givePermissionTo(Permission::findOrCreate($name, 'web'));
            }
            $this->admin->assignRole($role);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->admin, 'web')->withSession(['session_token' => $this->admin->session_token]);
    }

    private function metaAccount(array $overrides = []): WhatsappAccount
    {
        return WhatsappAccount::create(array_merge([
            'tenant_id' => $this->tenant->id,
            'channel_type' => 'meta_cloud',
            'name' => 'Support Line',
            'phone_number' => '+911234567890',
            'webhook_token' => WhatsappAccount::generateWebhookToken(),
            'verify_token' => 'my-verify-token',
            'credentials' => ['phone_number_id' => '1234567890', 'access_token' => 'test-token'],
        ], $overrides));
    }

    private function unofficialAccount(array $overrides = []): WhatsappAccount
    {
        return WhatsappAccount::create(array_merge([
            'tenant_id' => $this->tenant->id,
            'channel_type' => 'unofficial',
            'name' => 'Gateway Line',
            'phone_number' => '+911234500000',
            'webhook_token' => WhatsappAccount::generateWebhookToken(),
            'credentials' => ['api_url' => 'https://gateway.test/instance1/messages/chat', 'api_key' => 'abc123'],
        ], $overrides));
    }

    public function test_settings_chat_and_campaigns_pages_load(): void
    {
        $this->get('/whatsapp/settings')->assertOk()->assertSee('WhatsApp Settings');
        $this->get('/whatsapp/chat')->assertOk()->assertSee('WhatsApp Chat');
        $this->get('/whatsapp/campaigns')->assertOk()->assertSee('WhatsApp Campaigns');
    }

    public function test_tenant_admin_can_connect_a_meta_cloud_account(): void
    {
        $this->postJson('/whatsapp/settings', [
            'channel_type' => 'meta_cloud',
            'name' => 'Sales Line',
            'phone_number_id' => '999888777',
            'access_token' => 'secret-token',
        ])->assertOk()->assertJsonPath('status', true);

        $this->assertDatabaseHas('whatsapp_accounts', [
            'tenant_id' => $this->tenant->id,
            'channel_type' => 'meta_cloud',
            'name' => 'Sales Line',
        ]);

        $account = WhatsappAccount::where('tenant_id', $this->tenant->id)->first();
        $this->assertSame('999888777', $account->credential('phone_number_id'));
        $this->assertTrue($account->is_default, 'The first connected account should become the default.');
    }

    public function test_tenant_admin_can_connect_an_unofficial_gateway_account(): void
    {
        $this->postJson('/whatsapp/settings', [
            'channel_type' => 'unofficial',
            'name' => 'Cheap Gateway',
            'api_url' => 'https://gateway.test/i1/messages/chat',
            'api_key' => 'key-123',
        ])->assertOk()->assertJsonPath('status', true);

        $account = WhatsappAccount::where('tenant_id', $this->tenant->id)->first();
        $this->assertSame('unofficial', $account->channel_type);
        $this->assertSame('https://gateway.test/i1/messages/chat', $account->credential('api_url'));
    }

    public function test_editing_an_account_does_not_change_its_webhook_token(): void
    {
        $account = $this->metaAccount();
        $originalToken = $account->webhook_token;

        $this->putJson('/whatsapp/settings/'.$account->id, ['name' => 'Renamed Line'])->assertOk();

        $account->refresh();
        $this->assertSame('Renamed Line', $account->name);
        $this->assertSame($originalToken, $account->webhook_token);
    }

    public function test_a_tenant_cannot_modify_another_tenants_whatsapp_account(): void
    {
        $otherTenant = Tenant::create(['name' => 'Other Co', 'slug' => 'other-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $foreignAccount = WhatsappAccount::create([
            'tenant_id' => $otherTenant->id, 'channel_type' => 'meta_cloud', 'name' => 'Not yours',
            'webhook_token' => WhatsappAccount::generateWebhookToken(),
        ]);

        $this->putJson('/whatsapp/settings/'.$foreignAccount->id, ['name' => 'Hijacked'])->assertNotFound();
        $this->deleteJson('/whatsapp/settings/'.$foreignAccount->id)->assertNotFound();
    }

    public function test_sending_a_text_message_via_meta_cloud_records_a_sent_message(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.OUT1']]], 200),
        ]);

        $account = $this->metaAccount();
        $conversation = WhatsappConversation::create([
            'tenant_id' => $this->tenant->id, 'whatsapp_account_id' => $account->id, 'wa_phone' => '919000000001',
            'window_expires_at' => now()->addHours(10),
        ]);

        $this->postJson('/whatsapp/chat/conversations/'.$conversation->id.'/send', [
            'type' => 'text', 'body' => 'Hello there',
        ])->assertOk()->assertJsonPath('status', true);

        $message = WhatsappMessage::where('whatsapp_conversation_id', $conversation->id)->first();
        $this->assertSame('sent', $message->status);
        $this->assertSame('wamid.OUT1', $message->wa_message_id);
        $this->assertSame(1, $account->fresh()->messages_sent_count);
    }

    public function test_sending_outside_the_24_hour_window_is_blocked_for_meta_cloud(): void
    {
        $account = $this->metaAccount();
        $conversation = WhatsappConversation::create([
            'tenant_id' => $this->tenant->id, 'whatsapp_account_id' => $account->id, 'wa_phone' => '919000000002',
            'window_expires_at' => now()->subHour(),
        ]);

        $this->postJson('/whatsapp/chat/conversations/'.$conversation->id.'/send', [
            'type' => 'text', 'body' => 'Too late',
        ])->assertStatus(422);

        $this->assertSame(0, WhatsappMessage::where('whatsapp_conversation_id', $conversation->id)->count());
    }

    public function test_a_failed_send_is_recorded_with_the_gateway_error(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid token']], 401),
        ]);

        $account = $this->metaAccount();
        $conversation = WhatsappConversation::create([
            'tenant_id' => $this->tenant->id, 'whatsapp_account_id' => $account->id, 'wa_phone' => '919000000003',
            'window_expires_at' => now()->addHours(5),
        ]);

        $this->postJson('/whatsapp/chat/conversations/'.$conversation->id.'/send', [
            'type' => 'text', 'body' => 'Will fail',
        ])->assertOk()->assertJsonPath('status', false);

        $message = WhatsappMessage::where('whatsapp_conversation_id', $conversation->id)->first();
        $this->assertSame('failed', $message->status);
        $this->assertSame('Invalid token', $message->error_message);
    }

    public function test_sending_via_the_unofficial_gateway_posts_to_its_configured_url(): void
    {
        Http::fake([
            'gateway.test/*' => Http::response(['id' => 'gw-msg-1'], 200),
        ]);

        $account = $this->unofficialAccount();
        $conversation = WhatsappConversation::create([
            'tenant_id' => $this->tenant->id, 'whatsapp_account_id' => $account->id, 'wa_phone' => '919000000004',
        ]);

        $this->postJson('/whatsapp/chat/conversations/'.$conversation->id.'/send', [
            'type' => 'text', 'body' => 'Via gateway',
        ])->assertOk()->assertJsonPath('status', true);

        $message = WhatsappMessage::where('whatsapp_conversation_id', $conversation->id)->first();
        $this->assertSame('sent', $message->status);
        $this->assertSame('gw-msg-1', $message->wa_message_id);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'gateway.test') && $request['to'] === '919000000004' && str_contains($request->url(), 'token=abc123');
        });
    }

    public function test_inbound_meta_webhook_creates_a_conversation_and_message(): void
    {
        $account = $this->metaAccount();

        $payload = [
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'contacts' => [['profile' => ['name' => 'Ravi Kumar'], 'wa_id' => '919876543210']],
                        'messages' => [['from' => '919876543210', 'id' => 'wamid.IN1', 'type' => 'text', 'text' => ['body' => 'Hi, I need a quote']]],
                    ],
                ]],
            ]],
        ];

        $this->postJson('/webhooks/whatsapp/'.$account->webhook_token, $payload)->assertOk();

        $conversation = WhatsappConversation::where('tenant_id', $this->tenant->id)->where('wa_phone', '919876543210')->first();
        $this->assertNotNull($conversation);
        $this->assertSame('Ravi Kumar', $conversation->wa_name);
        $this->assertSame(1, $conversation->unread_count);

        $message = WhatsappMessage::where('whatsapp_conversation_id', $conversation->id)->first();
        $this->assertSame('in', $message->direction);
        $this->assertSame('Hi, I need a quote', $message->body);
        $this->assertSame(1, $account->fresh()->messages_received_count);
    }

    public function test_inbound_meta_webhook_links_to_an_existing_lead_by_phone(): void
    {
        $account = $this->metaAccount();
        $lead = Lead::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Known Lead', 'phone' => '9876500000',
            'lead_type' => 'inquiry', 'lead_status' => 'new', 'is_converted' => 'No',
        ]);

        $payload = [
            'entry' => [['changes' => [['value' => [
                'messages' => [['from' => '919876500000', 'id' => 'wamid.IN2', 'type' => 'text', 'text' => ['body' => 'Following up']]],
            ]]]]],
        ];

        $this->postJson('/webhooks/whatsapp/'.$account->webhook_token, $payload)->assertOk();

        $conversation = WhatsappConversation::where('tenant_id', $this->tenant->id)->where('wa_phone', '919876500000')->first();
        $this->assertSame($lead->id, $conversation->lead_id);
    }

    public function test_meta_verification_handshake_echoes_challenge_when_token_matches(): void
    {
        $account = $this->metaAccount(['verify_token' => 'secret-verify']);

        $this->get('/webhooks/whatsapp/'.$account->webhook_token.'?hub_mode=subscribe&hub_verify_token=secret-verify&hub_challenge=54321')
            ->assertOk()->assertSee('54321');

        $this->get('/webhooks/whatsapp/'.$account->webhook_token.'?hub_mode=subscribe&hub_verify_token=wrong&hub_challenge=54321')
            ->assertForbidden();
    }

    public function test_inbound_generic_gateway_webhook_creates_a_message(): void
    {
        $account = $this->unofficialAccount();

        $this->postJson('/webhooks/whatsapp/'.$account->webhook_token, [
            'from' => '919111122233',
            'body' => 'Hello from gateway',
            'pushname' => 'Gateway Contact',
        ])->assertOk();

        $conversation = WhatsappConversation::where('tenant_id', $this->tenant->id)->where('wa_phone', '919111122233')->first();
        $this->assertNotNull($conversation);
        $this->assertSame('Gateway Contact', $conversation->wa_name);
        $this->assertSame('Hello from gateway', $conversation->messages()->first()->body);
    }

    public function test_unknown_whatsapp_webhook_token_404s(): void
    {
        $this->postJson('/webhooks/whatsapp/does-not-exist')->assertNotFound();
    }

    public function test_a_disabled_account_ignores_inbound_webhooks(): void
    {
        $account = $this->metaAccount(['is_active' => false]);

        $this->postJson('/webhooks/whatsapp/'.$account->webhook_token, [
            'entry' => [['changes' => [['value' => ['messages' => [['from' => '919000000009', 'id' => 'x', 'text' => ['body' => 'hi']]]]]]]],
        ])->assertOk()->assertJsonPath('status', 'ignored');

        $this->assertSame(0, WhatsappConversation::where('tenant_id', $this->tenant->id)->count());
    }

    public function test_a_campaign_can_be_built_and_sent_to_matching_leads(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.C1']]], 200)]);

        $account = $this->metaAccount();
        Lead::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Campaign Lead', 'phone' => '9000011122',
            'lead_type' => 'inquiry', 'lead_status' => 'new', 'is_converted' => 'No',
        ]);

        $this->postJson('/whatsapp/campaigns', [
            'name' => 'Diwali Offer',
            'whatsapp_account_id' => $account->id,
            'message_type' => 'text',
            'body' => 'Hi {{lead.name}}, special offer for you!',
            'audience_type' => 'leads',
            'filters' => [],
        ])->assertOk()->assertJsonPath('status', true);

        $campaign = WhatsappCampaign::where('tenant_id', $this->tenant->id)->first();

        $this->postJson('/whatsapp/campaigns/'.$campaign->id.'/send')
            ->assertOk()
            ->assertJsonPath('status', true);

        $campaign->refresh();
        $this->assertSame(1, $campaign->sent_count);
        $this->assertSame('sent', $campaign->status);

        $message = WhatsappMessage::whereHas('conversation', fn ($q) => $q->where('wa_phone', '9000011122'))->first();
        $this->assertSame('Hi Campaign Lead, special offer for you!', $message->body);
    }

    public function test_a_campaign_with_no_matching_recipients_cannot_be_sent(): void
    {
        $account = $this->metaAccount();

        $this->postJson('/whatsapp/campaigns', [
            'name' => 'Empty Campaign', 'whatsapp_account_id' => $account->id,
            'message_type' => 'text', 'body' => 'Hi', 'audience_type' => 'leads', 'filters' => [],
        ])->assertOk();

        $campaign = WhatsappCampaign::where('tenant_id', $this->tenant->id)->first();

        $this->postJson('/whatsapp/campaigns/'.$campaign->id.'/send')->assertStatus(422);
    }

    public function test_deleting_a_tenant_cascades_to_its_whatsapp_accounts(): void
    {
        $tenant = Tenant::create(['name' => 'Cascade WA Co', 'slug' => 'cascade-wa-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $account = WhatsappAccount::create([
            'tenant_id' => $tenant->id, 'channel_type' => 'meta_cloud', 'name' => 'Doomed',
            'webhook_token' => WhatsappAccount::generateWebhookToken(),
        ]);

        $tenant->delete();

        $this->assertDatabaseMissing('whatsapp_accounts', ['id' => $account->id]);
    }
}
