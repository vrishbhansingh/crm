<?php

namespace Tests\Feature;

use App\Models\AiConfiguration;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\AiKeyFailed;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiConfigResolver;
use App\Services\Ai\AiGateway;
use App\Services\Ai\AssistantService;
use App\Services\Ai\ToolRegistry;
use App\Support\DefaultTenantRoles;
use App\Support\PermissionTeam;
use App\Support\TenantContext;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\FakeAiClient;
use Tests\TestCase;

class AiConfigurationTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $admin;
    private User $agent;
    private array $history = [];

    protected function setUp(): void
    {
        parent::setUp();

        $s = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'AI Config Co', 'slug' => 'aicfg-'.$s, 'status' => 'Active']);
        $make = fn (string $n) => User::create(['tenant_id' => $this->tenant->id, 'name' => $n, 'email' => Str::slug($n)."-{$s}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(30)]);
        $this->admin = $make('Cfg Admin');
        $this->agent = $make('Cfg Agent');

        PermissionTeam::run($this->tenant->id, function () {
            $adminRole = Role::findOrCreate('Admin', 'web');
            $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());
            $this->admin->assignRole($adminRole);
            $this->agent->assignRole(DefaultTenantRoles::seedAgentRole());
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        PermissionTeam::set($this->tenant->id);
        TenantContext::set($this->tenant->id);

        // Tests start with NO server-wide key and the scope gate off (it has its own tests below).
        config(['ai.api_key' => null, 'ai.enabled' => true, 'ai.scope_guard' => false]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        PermissionTeam::set(null);
        parent::tearDown();
    }

    private function cfg(string $name, int $priority, array $extra = []): AiConfiguration
    {
        return AiConfiguration::create(array_merge([
            'tenant_id' => $this->tenant->id, 'name' => $name, 'api_key' => 'sk-ant-'.Str::random(30), 'key_hint' => '…test',
            'priority' => $priority, 'is_active' => true,
        ], $extra));
    }

    /** Requests sent through the client are recorded in $this->history. @return array{0: AiGateway, 1: MockHandler} */
    private function clientWith(array $responses): array
    {
        $this->history = [];
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($this->history));

        return [new AiGateway(new Guzzle(['handler' => $stack])), $mock];
    }

    private function ok(string $text = 'hello'): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'id' => 'msg_1', 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-test',
            'content' => [['type' => 'text', 'text' => $text]], 'stop_reason' => 'end_turn', 'stop_sequence' => null,
            'usage' => ['input_tokens' => 1, 'output_tokens' => 1],
        ]));
    }

    private function error(int $status, string $type, string $message): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode(['type' => 'error', 'error' => ['type' => $type, 'message' => $message]]));
    }

    private function openAiOk(string $text = 'hi', array $extra = []): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'choices' => [['finish_reason' => 'stop', 'message' => array_merge(['role' => 'assistant', 'content' => $text], $extra)]],
            'usage' => ['prompt_tokens' => 120, 'completion_tokens' => 30],
        ]));
    }

    public function test_an_openai_compatible_provider_works_and_its_usage_is_recorded(): void
    {
        $g = $this->cfg('Groq', 1, ['provider' => 'groq', 'api_key' => 'gsk_'.Str::random(30)]);
        [$client] = $this->clientWith([$this->openAiOk('from groq')]);

        $result = $client->send('sys', [['role' => 'user', 'content' => 'hi']], []);

        $this->assertSame('from groq', $result['text']);
        $this->assertSame('https://api.groq.com/openai/v1/chat/completions', (string) $this->history[0]['request']->getUri());
        $this->assertSame('Bearer '.$g->api_key, $this->history[0]['request']->getHeaderLine('Authorization'));
        $g->refresh();
        $this->assertSame(1, (int) $g->requests_count);
        $this->assertSame(120, (int) $g->input_tokens);
        $this->assertSame(30, (int) $g->output_tokens);
    }

    public function test_openai_compatible_tool_calls_are_translated(): void
    {
        $this->cfg('OpenAI', 1, ['provider' => 'openai', 'api_key' => 'sk-'.Str::random(30)]);
        $toolCall = ['tool_calls' => [['id' => 'call_1', 'type' => 'function', 'function' => ['name' => 'find_leads', 'arguments' => '{"q":"x"}']]]];
        [$client] = $this->clientWith([new Response(200, [], json_encode([
            'choices' => [['finish_reason' => 'tool_calls', 'message' => array_merge(['role' => 'assistant', 'content' => null], $toolCall)]],
            'usage' => ['prompt_tokens' => 5, 'completion_tokens' => 5],
        ]))]);

        $result = $client->send('sys', [['role' => 'user', 'content' => 'hi']], [['name' => 'find_leads', 'description' => 'd', 'input_schema' => ['type' => 'object', 'properties' => ['q' => ['type' => 'string']]]]]);

        $this->assertSame('tool_use', $result['stop_reason']);
        $this->assertSame('find_leads', $result['tool_calls'][0]['name']);
        $this->assertSame(['q' => 'x'], $result['tool_calls'][0]['input']);
        $this->assertSame('call_1', $result['tool_calls'][0]['id']);
    }

    public function test_failover_works_across_providers_and_a_missing_model_counts_as_a_key_problem(): void
    {
        $a = $this->cfg('Gemini', 1, ['provider' => 'gemini', 'api_key' => 'AIza'.Str::random(30)]);
        $b = $this->cfg('Claude', 2);
        [$client] = $this->clientWith([
            new Response(404, [], json_encode(['error' => ['message' => 'model not found']])),
            $this->ok('claude saved the day'),
        ]);

        $this->assertSame('claude saved the day', $client->send('sys', [['role' => 'user', 'content' => 'hi']], [])['text']);
        $this->assertSame('failing', $a->fresh()->status);
        $this->assertStringContainsString('model', $a->fresh()->last_error);
    }

    public function test_catalog_cost_and_validation(): void
    {
        $this->assertGreaterThan(5, count(\App\Services\Ai\AiProviders::all()));
        $cost = \App\Services\Ai\AiProviders::cost('anthropic', 'claude-sonnet-5-5', 1_000_000, 1_000_000);
        $this->assertEquals(12.0, $cost);

        $this->actingAs($this->admin, 'web')->withSession(['session_token' => $this->admin->session_token]);
        $key = 'or-key-'.Str::random(30);
        $this->postJson('/settings/ai', ['name' => 'OR', 'provider' => 'openrouter', 'api_key' => $key])->assertStatus(422);
        $this->postJson('/settings/ai', ['name' => 'OR', 'provider' => 'openrouter', 'api_key' => $key, 'model' => 'openai/gpt-4o-mini'])->assertOk();
        $this->postJson('/settings/ai', ['name' => 'X', 'provider' => 'nonsense', 'api_key' => $key])->assertStatus(422);
        $this->get('/settings/ai')->assertOk()->assertSee('Get ', false)->assertSee('third-party', false);
    }

    public function test_a_failing_key_switches_to_the_next_one_automatically_and_alerts_the_admins(): void
    {
        $first = $this->cfg('Main key', 1, ['notify_email' => 'owner@example.test']);
        $second = $this->cfg('Backup key', 2);
        [$client, $mock] = $this->clientWith([$this->error(401, 'authentication_error', 'invalid x-api-key'), $this->ok('answer from the backup')]);

        $result = $client->send('sys', [['role' => 'user', 'content' => 'hi']], []);

        $this->assertSame('answer from the backup', $result['text']);
        $this->assertCount(2, $this->history);
        $this->assertSame($first->api_key, $this->history[0]['request']->getHeaderLine('x-api-key'));
        $this->assertSame($second->api_key, $this->history[1]['request']->getHeaderLine('x-api-key'), 'the second request used the second key');

        $first->refresh();
        $second->refresh();
        $this->assertSame('failing', $first->status);
        $this->assertStringContainsString('rejected', $first->last_error);
        $this->assertNotNull($first->notified_at);
        $this->assertSame('ok', $second->status);
        $this->assertNotNull($second->last_used_at);

        $note = $this->admin->notifications()->where('type', AiKeyFailed::class)->first();
        $this->assertNotNull($note, 'admins are told in-app');
        $this->assertStringContainsString('switched to another key', $note->data['message']);
    }

    public function test_a_failed_key_is_tried_last_during_its_cool_down_then_again_afterwards(): void
    {
        $a = $this->cfg('A', 1, ['status' => 'failing', 'failed_at' => now()->subMinutes(2)]);
        $b = $this->cfg('B', 2, ['status' => 'ok']);
        $resolver = app(AiConfigResolver::class);

        $this->assertSame(['B', 'A'], array_map(fn ($c) => $c['config']->name, $resolver->candidates($this->tenant->id)));

        $a->update(['failed_at' => now()->subMinutes(20)]);
        $this->assertSame(['A', 'B'], array_map(fn ($c) => $c['config']->name, $resolver->candidates($this->tenant->id)), 'cool-down over: back in priority order');

        $a->update(['is_active' => false]);
        $this->assertSame(['B'], array_map(fn ($c) => $c['config']->name, $resolver->candidates($this->tenant->id)), 'turned-off keys are never used');
    }

    public function test_when_every_key_fails_the_error_is_clear_and_each_key_is_marked(): void
    {
        $a = $this->cfg('A', 1);
        $b = $this->cfg('B', 2);
        [$client] = $this->clientWith([$this->error(401, 'authentication_error', 'bad'), $this->error(400, 'invalid_request_error', 'Your credit balance is too low to access the API.')]);

        try {
            $client->send('sys', [['role' => 'user', 'content' => 'hi']], []);
            $this->fail('should have thrown');
        } catch (\Throwable $e) {
            $this->assertStringContainsString('Every saved AI key failed', \App\Services\Ai\AiErrors::message($e));
        }

        $this->assertSame('failing', $a->fresh()->status);
        $this->assertSame('failing', $b->fresh()->status);
        $this->assertStringContainsString('out of credit', $b->fresh()->last_error);
        $messages = $this->admin->notifications()->where('type', AiKeyFailed::class)->get()->pluck('data.message');
        $this->assertTrue($messages->contains(fn ($m) => str_contains($m, 'switched to another key')), 'the first failure says a backup took over');
        $this->assertTrue($messages->contains(fn ($m) => str_contains($m, 'unavailable')), 'the last failure says nothing is left');
    }

    public function test_a_server_error_does_not_burn_through_the_keys(): void
    {
        $a = $this->cfg('A', 1);
        $b = $this->cfg('B', 2);
        [$client, $mock] = $this->clientWith([$this->error(500, 'api_error', 'boom'), $this->error(500, 'api_error', 'boom'), $this->ok()]);

        try {
            $client->send('sys', [['role' => 'user', 'content' => 'hi']], []);
            $this->fail('should have thrown');
        } catch (\Throwable $e) {
            $this->assertFalse(\App\Services\Ai\AiErrors::isKeyProblem($e));
        }

        $this->assertSame('untested', $a->fresh()->status, 'a provider outage is not the key\'s fault');
        $this->assertSame(1, $mock->count(), 'the second key was never tried');
    }

    public function test_the_test_button_checks_exactly_one_key_and_records_the_result(): void
    {
        $good = $this->cfg('Good', 1);
        $bad = $this->cfg('Bad', 2);
        [$client] = $this->clientWith([$this->ok('OK'), $this->error(401, 'authentication_error', 'bad')]);

        $this->assertTrue($client->ping($good)['ok']);
        $this->assertSame('ok', $good->fresh()->status);
        $this->assertNotNull($good->fresh()->last_tested_at);

        $result = $client->ping($bad);
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('rejected', $result['message']);
        $this->assertSame('failing', $bad->fresh()->status);
    }

    public function test_a_company_never_uses_another_companys_keys_or_the_server_key_when_it_has_its_own(): void
    {
        $other = Tenant::create(['name' => 'Other AI Co', 'slug' => 'aiother-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        AiConfiguration::create(['tenant_id' => $other->id, 'name' => 'Theirs', 'api_key' => 'sk-ant-'.Str::random(30), 'priority' => 1, 'is_active' => true]);
        $resolver = app(AiConfigResolver::class);

        $this->assertSame([], $resolver->candidates($this->tenant->id), 'no own key and no server key: nothing to use');
        $this->assertFalse($resolver->hasAny($this->tenant->id));
        $this->assertTrue($resolver->hasAny($other->id));

        config(['ai.api_key' => 'sk-ant-server-default-key-123456']);
        $this->assertCount(1, $resolver->candidates($this->tenant->id), 'no own key: the server default is the fallback');

        $this->cfg('Own', 1);
        $names = array_map(fn ($c) => $c['config']?->name, $resolver->candidates($this->tenant->id));
        $this->assertSame(['Own'], $names, 'having its own key, the server default is not used');
    }

    public function test_settings_page_crud_never_leaks_the_key_and_is_company_scoped(): void
    {
        $this->actingAs($this->admin, 'web')->withSession(['session_token' => $this->admin->session_token]);

        $this->get('/settings/ai')->assertOk()->assertSee('Add AI provider');

        $key = 'sk-ant-api03-SECRETSECRETSECRET1234';
        $id = $this->postJson('/settings/ai', ['name' => 'Main', 'provider' => 'anthropic', 'api_key' => $key, 'notify_email' => 'me@example.test'])->assertOk()->json('data.id');
        $this->assertSame($key, AiConfiguration::find($id)->api_key, 'stored (encrypted at rest) and readable by the app');
        $this->assertNotSame($key, \DB::table('ai_configurations')->where('id', $id)->value('api_key'), 'the database column holds ciphertext, not the key');

        $listing = $this->getJson('/settings/ai/data')->assertOk();
        $this->assertStringNotContainsString('SECRETSECRET', $listing->getContent());
        $this->assertSame('…1234', $listing->json('data.0.key_hint'));

        $this->postJson('/settings/ai', ['name' => 'Second', 'provider' => 'anthropic', 'api_key' => 'sk-ant-api03-second-key-abcdefgh'])->assertOk();
        $this->assertSame(['Main', 'Second'], collect($this->getJson('/settings/ai/data')->json('data'))->pluck('name')->all());

        // Reorder, then edit without re-entering the key.
        $this->postJson("/settings/ai/{$id}/move", ['direction' => 'down'])->assertOk();
        $this->assertSame(['Second', 'Main'], collect($this->getJson('/settings/ai/data')->json('data'))->pluck('name')->all());
        $this->putJson("/settings/ai/{$id}", ['name' => 'Main renamed', 'provider' => 'anthropic', 'is_active' => 0])->assertOk();
        $this->assertSame($key, AiConfiguration::find($id)->api_key, 'a blank key field keeps the saved key');
        $this->assertFalse(AiConfiguration::find($id)->is_active);

        $this->postJson('/settings/ai', ['name' => 'Bad', 'provider' => 'anthropic', 'api_key' => 'short'])->assertStatus(422);

        // Another company's configuration is invisible and untouchable.
        $other = Tenant::create(['name' => 'Other Co 2', 'slug' => 'aiother2-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $foreign = AiConfiguration::create(['tenant_id' => $other->id, 'name' => 'Foreign', 'api_key' => 'sk-ant-'.Str::random(30), 'priority' => 1]);
        $this->putJson("/settings/ai/{$foreign->id}", ['name' => 'hijack', 'provider' => 'anthropic'])->assertNotFound();
        $this->deleteJson("/settings/ai/{$foreign->id}")->assertNotFound();
        $this->postJson("/settings/ai/{$foreign->id}/test")->assertNotFound();

        $this->deleteJson("/settings/ai/{$id}")->assertOk();
        $this->assertNull(AiConfiguration::find($id));
    }

    public function test_only_people_who_can_manage_settings_can_open_the_ai_settings(): void
    {
        $this->actingAs($this->agent, 'web')->withSession(['session_token' => $this->agent->session_token]);

        $this->get('/settings/ai')->assertForbidden();
        $this->postJson('/settings/ai', ['name' => 'x', 'api_key' => 'sk-ant-'.Str::random(30)])->assertForbidden();
    }

    public function test_the_chat_shows_admins_a_setup_card_with_configure_now_and_everyone_else_nothing(): void
    {
        $this->actingAs($this->admin, 'web')->withSession(['session_token' => $this->admin->session_token]);
        $this->get('/dashboard')->assertOk()->assertSee('const needsSetup = true;', false)->assertSee('Configure now', false);

        $this->actingAs($this->agent, 'web')->withSession(['session_token' => $this->agent->session_token]);
        $this->get('/dashboard')->assertOk()->assertDontSee('aiFab', false)->assertDontSee('const needsSetup', false);

        // Once the company has a key, the normal chat appears for everyone (no setup state).
        $this->cfg('Main', 1);
        $this->get('/dashboard')->assertOk()->assertSee('aiFab', false)->assertSee('const needsSetup = false;', false);
        $this->actingAs($this->admin, 'web')->withSession(['session_token' => $this->admin->session_token]);
        $this->get('/dashboard')->assertOk()->assertSee('aiFab', false)->assertSee('const needsSetup = false;', false);
    }

    public function test_the_scope_gate_refuses_off_topic_messages_before_the_assistant_or_its_data_tools_run(): void
    {
        config(['ai.scope_guard' => true, 'ai.guard_model' => 'claude-haiku-5-5']);
        $fake = new FakeAiClient([
            ['stop_reason' => 'end_turn', 'text' => 'OUT', 'tool_calls' => [], 'raw_content' => []],
        ]);
        $service = new AssistantService($fake, app(ToolRegistry::class));

        $result = $service->ask($this->admin, 'Write me a poem about the moon', []);

        $this->assertSame(AssistantService::REFUSAL, $result['reply']);
        $this->assertCount(1, $fake->calls, 'only the gate ran: the assistant never saw the message');
        $this->assertSame([], $fake->calls[0]['tools'], 'the gate has no tools');
        $this->assertSame('@guard', $fake->calls[0]['model'], 'the gate uses the cheaper model');
        $this->assertStringContainsString('untrusted', $fake->calls[0]['system']);
    }

    public function test_the_scope_gate_lets_crm_questions_through_and_fails_closed_on_anything_unclear(): void
    {
        config(['ai.scope_guard' => true]);

        $fake = new FakeAiClient([
            ['stop_reason' => 'end_turn', 'text' => 'IN', 'tool_calls' => [], 'raw_content' => []],
            ['stop_reason' => 'end_turn', 'text' => 'You have 3 open deals.', 'tool_calls' => [], 'raw_content' => []],
        ]);
        $service = new AssistantService($fake, app(ToolRegistry::class));
        $this->assertSame('You have 3 open deals.', $service->ask($this->admin, 'How many open deals do I have?', [])['reply']);
        $this->assertCount(2, $fake->calls);

        foreach (['', 'MAYBE', 'I think it is fine', 'OUTSIDE', 'INVALID'] as $unclear) {
            $fake = new FakeAiClient([['stop_reason' => 'end_turn', 'text' => $unclear, 'tool_calls' => [], 'raw_content' => []]]);
            $reply = (new AssistantService($fake, app(ToolRegistry::class)))->ask($this->admin, 'anything', [])['reply'];
            $this->assertSame(AssistantService::REFUSAL, $reply, "'{$unclear}' must not count as in-scope");
        }
    }
}
