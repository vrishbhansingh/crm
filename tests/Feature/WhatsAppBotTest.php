<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsappAccount;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Notifications\WhatsAppHandoff;
use App\Services\Ai\AiClient;
use App\Services\WhatsApp\WhatsAppBot;
use App\Services\WhatsApp\WhatsAppMessageService;
use App\Support\PermissionTeam;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\FakeAiClient;
use Tests\TestCase;

class WhatsAppBotTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $admin;
    private WhatsappAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Pipes Co', 'slug' => 'bot-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $this->admin = User::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Bot Admin', 'email' => Str::lower(Str::random(10)).'@example.test',
            'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60),
        ]);
        PermissionTeam::run($this->tenant->id, function () {
            $role = Role::findOrCreate('Admin', 'web');
            foreach (['whatsapp.view', 'whatsapp.create', 'whatsapp.send', 'whatsapp.manage-settings', 'whatsapp.delete'] as $name) {
                $role->givePermissionTo(Permission::findOrCreate($name, 'web'));
            }
            $this->admin->assignRole($role);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        PermissionTeam::set($this->tenant->id);
        TenantContext::set($this->tenant->id);

        config(['ai.api_key' => 'test-key', 'ai.enabled' => true, 'ai.bot_daily_replies' => 300]);
        Http::fake(['gateway.test/*' => Http::response(['id' => 'gw-1'], 200)]);

        $this->account = WhatsappAccount::create([
            'tenant_id' => $this->tenant->id, 'channel_type' => 'unofficial', 'name' => 'Gateway Line', 'phone_number' => '+911234500000',
            'webhook_token' => WhatsappAccount::generateWebhookToken(),
            'credentials' => ['api_url' => 'https://gateway.test/instance1/messages/chat', 'api_key' => 'abc123'],
            'ai_bot_enabled' => true, 'ai_bot_instructions' => 'We sell steel pipes, 6-inch and 8-inch. Open 9-6 Mon-Sat.',
        ])->fresh(); // fresh(): picks up DB defaults such as is_active = true, as a real webhook load would
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        PermissionTeam::set(null);
        parent::tearDown();
    }

    private function turn(array $override = []): array
    {
        $collected = array_merge(['name' => '', 'company' => '', 'product' => '', 'quantity' => '', 'budget' => '', 'timeline' => '', 'city' => '', 'email' => ''], $override['collected'] ?? []);

        return ['stop_reason' => 'end_turn', 'tool_calls' => [], 'raw_content' => [], 'text' => json_encode(array_merge(
            ['reply' => 'Hello! What are you looking for?', 'handoff' => false, 'handoff_reason' => ''], $override, ['collected' => $collected]
        ))];
    }

    private function bot(array $script = []): array
    {
        $fake = new FakeAiClient($script);

        return [new WhatsAppBot($fake, app(WhatsAppMessageService::class)), $fake];
    }

    private function inbound(string $body, string $phone = '919000000010', string $type = 'text'): WhatsappMessage
    {
        return app(WhatsAppMessageService::class)->recordInbound($this->account, $phone, 'Ravi', ['type' => $type, 'body' => $body], 'wamid.'.Str::random(6));
    }

    private function conversationOf(WhatsappMessage $m): WhatsappConversation
    {
        return WhatsappConversation::findOrFail($m->whatsapp_conversation_id);
    }

    public function test_it_is_off_by_default_and_never_replies_when_off(): void
    {
        $this->account->update(['ai_bot_enabled' => false]);
        [$bot, $fake] = $this->bot([$this->turn()]);
        $in = $this->inbound('Hi, I need pipes');

        $this->assertSame('skipped:off', $bot->respond($this->account->fresh(), $this->conversationOf($in), $in));
        $this->assertSame([], $fake->calls);
        $this->assertSame(0, WhatsappMessage::where('direction', 'out')->count());
        $this->assertFalse((new WhatsappAccount())->ai_bot_enabled ?? false);
    }

    public function test_it_replies_captures_the_lead_and_only_gives_the_model_this_chat_and_business_info(): void
    {
        [$bot, $fake] = $this->bot([$this->turn(['reply' => 'Hi Ravi! How many 6-inch pipes do you need?', 'collected' => ['name' => 'Ravi Kumar', 'product' => '6-inch pipe', 'city' => 'Pune', 'email' => 'ravi@example.test']])]);
        $in = $this->inbound('Hi, I need 6-inch pipes');
        $conversation = $this->conversationOf($in);

        $this->assertSame('replied', $bot->respond($this->account, $conversation, $in));

        $out = WhatsappMessage::where('whatsapp_conversation_id', $conversation->id)->where('direction', 'out')->firstOrFail();
        $this->assertSame('bot', $out->source);
        $this->assertSame('Hi Ravi! How many 6-inch pipes do you need?', $out->body);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'gateway.test'));

        $lead = Lead::findOrFail($conversation->fresh()->lead_id);
        $this->assertSame('whatsapp', $lead->lead_source);
        $this->assertSame('Ravi Kumar', $lead->name);
        $this->assertSame('6-inch pipe', $lead->product);
        $this->assertSame('Pune', $lead->city);
        $this->assertSame('ravi@example.test', $lead->email);

        $call = $fake->calls[0];
        $this->assertSame([], $call['tools'], 'the bot has no tools and no CRM access');
        $this->assertNotNull($call['jsonSchema']);
        $this->assertStringContainsString('steel pipes', $call['system']);
        $this->assertStringContainsString('untrusted', $call['messages'][0]['content']);
        $this->assertStringContainsString('Never invent prices', $call['system']);
    }

    public function test_the_model_can_hand_off_which_notifies_the_team_and_silences_the_bot(): void
    {
        [$bot] = $this->bot([$this->turn(['reply' => 'Our team will contact you shortly.', 'handoff' => true, 'handoff_reason' => 'Customer wants a bulk quote', 'collected' => ['name' => 'Ravi', 'quantity' => '500']])]);
        $in = $this->inbound('Need 500 pipes, what is the best price?');
        $conversation = $this->conversationOf($in);

        $this->assertSame('handoff', $bot->respond($this->account, $conversation, $in));

        $this->assertSame('needs_human', $bot->status($conversation));
        $this->assertSame('bot_handoff', WhatsappMessage::where('whatsapp_conversation_id', $conversation->id)->where('direction', 'out')->firstOrFail()->source);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $conversation->fresh()->lead_id, 'type' => 'note']);
        $this->assertSame(1, $this->admin->notifications()->where('type', WhatsAppHandoff::class)->count());

        // A further customer message does not get an AI reply.
        $next = $this->inbound('hello? anyone there?');
        [$bot2, $fake2] = $this->bot([$this->turn()]);
        $this->assertSame('skipped:human', $bot2->respond($this->account, $this->conversationOf($next), $next));
        $this->assertSame([], $fake2->calls);
    }

    public function test_asking_for_a_person_hands_off_without_calling_the_model(): void
    {
        [$bot, $fake] = $this->bot([$this->turn()]);
        $in = $this->inbound('I want to talk to a human please');
        $conversation = $this->conversationOf($in);

        $this->assertSame('handoff', $bot->respond($this->account, $conversation, $in));
        $this->assertSame([], $fake->calls);
        $this->assertSame('needs_human', $bot->status($conversation));
    }

    public function test_a_human_reply_stops_the_bot_for_good(): void
    {
        [$bot, $fake] = $this->bot([$this->turn(), $this->turn()]);
        $first = $this->inbound('Hi');
        $conversation = $this->conversationOf($first);
        $this->assertSame('replied', $bot->respond($this->account, $conversation, $first));

        app(WhatsAppMessageService::class)->sendMessage($this->account, $conversation, ['type' => 'text', 'body' => 'Hi, this is Anil from the team'], source: 'agent', sentBy: $this->admin->id);
        $this->assertSame('human', $bot->status($conversation));

        $next = $this->inbound('Do you deliver to Pune?');
        $this->assertSame('skipped:human', $bot->respond($this->account, $conversation, $next));
        $this->assertCount(1, $fake->calls, 'no further AI calls once a person has replied');
    }

    public function test_it_hands_off_after_the_reply_cap(): void
    {
        $this->account->update(['ai_bot_max_replies' => 2]);
        [$bot, $fake] = $this->bot([$this->turn(), $this->turn(), $this->turn()]);
        $conversation = null;

        foreach (['Hi', 'Pipes', 'More pipes'] as $i => $text) {
            $in = $this->inbound($text);
            $conversation = $this->conversationOf($in);
            $result = $bot->respond($this->account->fresh(), $conversation, $in);
            $this->assertSame($i < 2 ? 'replied' : 'handoff', $result);
        }

        $this->assertCount(2, $fake->calls, 'the third message was handed off without another AI call');
        $this->assertSame('needs_human', $bot->status($conversation));
    }

    public function test_only_the_latest_text_message_gets_a_reply(): void
    {
        [$bot, $fake] = $this->bot([$this->turn()]);
        $older = $this->inbound('first message');
        $this->inbound('second message right after');

        $this->assertSame('skipped:stale', $bot->respond($this->account, $this->conversationOf($older), $older));

        $media = $this->inbound('', '919000000011', 'image');
        $this->assertSame('skipped:not_text', $bot->respond($this->account, $this->conversationOf($media), $media));
        $this->assertSame([], $fake->calls);
    }

    public function test_a_failed_or_garbled_ai_reply_sends_nothing_to_the_customer_and_alerts_the_team(): void
    {
        [$bot] = $this->bot([['stop_reason' => 'end_turn', 'tool_calls' => [], 'raw_content' => [], 'text' => 'this is not json']]);
        $in = $this->inbound('Hello');
        $conversation = $this->conversationOf($in);

        $this->assertSame('failed', $bot->respond($this->account, $conversation, $in));
        $this->assertSame(0, WhatsappMessage::where('whatsapp_conversation_id', $conversation->id)->where('direction', 'out')->count());
        $this->assertSame(1, $this->admin->notifications()->where('type', WhatsAppHandoff::class)->count());
    }

    public function test_daily_cap_stops_replies(): void
    {
        config(['ai.bot_daily_replies' => 1]);
        Cache::forget('wa-bot:cap:'.$this->account->id.':'.now()->toDateString());
        [$bot] = $this->bot([$this->turn(), $this->turn()]);

        $a = $this->inbound('one', '919000000012');
        $b = $this->inbound('two', '919000000013');
        $this->assertSame('replied', $bot->respond($this->account, $this->conversationOf($a), $a));
        $this->assertSame('skipped:daily_cap', $bot->respond($this->account, $this->conversationOf($b), $b));
    }

    public function test_details_the_customer_gives_never_overwrite_what_a_human_already_entered(): void
    {
        $existing = Lead::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Ravi Verma', 'phone' => '919000000010', 'city' => 'Mumbai', 'company_name' => 'Verma Traders',
            'lead_type' => 'inquiry', 'lead_status' => 'new', 'is_converted' => 'No',
        ]);
        [$bot] = $this->bot([$this->turn(['collected' => ['name' => 'Someone Else', 'city' => 'Pune', 'company' => 'Other Co', 'product' => '8-inch pipe']])]);
        $in = $this->inbound('Need 8-inch pipes');

        $bot->respond($this->account, $this->conversationOf($in), $in);

        $lead = $existing->fresh();
        $this->assertSame('Ravi Verma', $lead->name);
        $this->assertSame('Mumbai', $lead->city);
        $this->assertSame('Verma Traders', $lead->company_name);
        $this->assertSame('8-inch pipe', $lead->product, 'blank fields are filled in');
    }

    public function test_webhook_triggers_the_bot_only_when_it_is_switched_on_for_that_account(): void
    {
        $fake = new FakeAiClient([$this->turn(['reply' => 'Welcome! What do you need?']), $this->turn()]);
        $this->app->instance(AiClient::class, $fake);
        $payload = ['data' => ['from' => '919000000020', 'body' => 'Need pipes', 'id' => 'in-1', 'pushname' => 'Meera']];

        $this->postJson('/webhooks/whatsapp/'.$this->account->webhook_token, $payload)->assertOk();
        $conversation = WhatsappConversation::where('wa_phone', '919000000020')->firstOrFail();
        $this->assertSame('Welcome! What do you need?', WhatsappMessage::where('whatsapp_conversation_id', $conversation->id)->where('direction', 'out')->firstOrFail()->body);

        $this->account->update(['ai_bot_enabled' => false]);
        $payload['data'] = ['from' => '919000000021', 'body' => 'Hello', 'id' => 'in-2'];
        $this->postJson('/webhooks/whatsapp/'.$this->account->webhook_token, $payload)->assertOk();
        $this->assertCount(1, $fake->calls, 'switched-off account: the model is never called');
    }

    public function test_bot_settings_are_off_by_default_and_saved_from_the_settings_screen(): void
    {
        $this->actingAs($this->admin, 'web')->withSession(['session_token' => $this->admin->session_token]);

        $this->postJson('/whatsapp/settings', ['channel_type' => 'meta_cloud', 'name' => 'New Line', 'phone_number_id' => '555', 'access_token' => 'tok'])->assertOk();
        $created = WhatsappAccount::where('name', 'New Line')->firstOrFail();
        $this->assertFalse($created->ai_bot_enabled);

        $this->putJson('/whatsapp/settings/'.$created->id, [
            'name' => 'New Line', 'ai_bot_enabled' => '1', 'ai_bot_instructions' => 'We sell pipes.', 'ai_bot_max_replies' => 4, 'ai_bot_handoff_message' => 'Team will call you.',
        ])->assertOk();

        $created->refresh();
        $this->assertTrue($created->ai_bot_enabled);
        $this->assertSame('We sell pipes.', $created->ai_bot_instructions);
        $this->assertSame(4, $created->ai_bot_max_replies);

        $this->putJson('/whatsapp/settings/'.$created->id, ['name' => 'New Line', 'ai_bot_enabled' => '0'])->assertOk();
        $this->assertFalse($created->fresh()->ai_bot_enabled);
        $this->putJson('/whatsapp/settings/'.$created->id, ['name' => 'New Line', 'ai_bot_max_replies' => 99])->assertStatus(422);
    }
}
