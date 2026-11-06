<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsappAccount;
use App\Models\WhatsappConversation;
use App\Support\PermissionTeam;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WhatsAppChatScopeTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $admin;
    private User $agent;
    private User $other;
    private WhatsappAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $s = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'Chat Scope Co', 'slug' => 'chatscope-'.$s, 'status' => 'Active']);
        $make = fn (string $n) => User::create(['tenant_id' => $this->tenant->id, 'name' => $n, 'email' => Str::slug($n)."-{$s}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(30)]);
        $this->admin = $make('Chat Admin');
        $this->agent = $make('Chat Agent');
        $this->other = $make('Chat Other');

        PermissionTeam::run($this->tenant->id, function () {
            $admin = Role::findOrCreate('Admin', 'web');
            $admin->syncPermissions(collect(['whatsapp.view', 'whatsapp.send'])->map(fn ($p) => Permission::findOrCreate($p, 'web')));
            $this->admin->assignRole($admin);
            $agentRole = Role::findOrCreate('Agent', 'web');
            $agentRole->syncPermissions(collect(['whatsapp.view', 'whatsapp.send', 'leads.view'])->map(fn ($p) => Permission::findOrCreate($p, 'web')));
            $this->agent->assignRole($agentRole);
            $this->other->assignRole($agentRole);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        PermissionTeam::set($this->tenant->id);
        TenantContext::set($this->tenant->id);

        $this->account = WhatsappAccount::create([
            'tenant_id' => $this->tenant->id, 'channel_type' => 'unofficial', 'name' => 'Line', 'phone_number' => '+911234500000',
            'webhook_token' => WhatsappAccount::generateWebhookToken(), 'credentials' => ['api_url' => 'https://gateway.test/x', 'api_key' => 'k'],
        ]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        PermissionTeam::set(null);
        parent::tearDown();
    }

    private function convo(string $phone, ?int $leadOwner = null): WhatsappConversation
    {
        $lead = $leadOwner ? Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'L '.$phone, 'phone' => $phone, 'assigned_to' => $leadOwner, 'lead_status' => 'New', 'status' => 'Active', 'is_converted' => 'No']) : null;

        return WhatsappConversation::create(['tenant_id' => $this->tenant->id, 'whatsapp_account_id' => $this->account->id, 'wa_phone' => $phone, 'lead_id' => $lead?->id]);
    }

    private function as(User $u): void
    {
        $this->actingAs($u, 'web')->withSession(['session_token' => $u->session_token]);
    }

    public function test_an_agent_only_sees_and_can_use_chats_of_their_own_leads_while_admins_see_all(): void
    {
        $mine = $this->convo('+919000000001', $this->agent->id);
        $theirs = $this->convo('+919000000002', $this->other->id);
        $stranger = $this->convo('+919000000003');

        $this->as($this->agent);
        $ids = collect($this->getJson('/whatsapp/chat/conversations')->assertOk()->json('data'))->pluck('id')->all();
        $this->assertSame([$mine->id], $ids);
        $this->getJson("/whatsapp/chat/conversations/{$mine->id}/messages")->assertOk();
        $this->getJson("/whatsapp/chat/conversations/{$theirs->id}/messages")->assertNotFound();
        $this->getJson("/whatsapp/chat/conversations/{$stranger->id}/messages")->assertNotFound();
        $this->postJson("/whatsapp/chat/conversations/{$theirs->id}/send", ['type' => 'text', 'body' => 'hi'])->assertNotFound();

        $this->as($this->admin);
        $all = collect($this->getJson('/whatsapp/chat/conversations')->json('data'))->pluck('id')->sort()->values()->all();
        $this->assertSame(collect([$mine->id, $theirs->id, $stranger->id])->sort()->values()->all(), $all);
    }

    public function test_a_chat_an_agent_starts_is_theirs_and_a_numbers_existing_chat_is_not_handed_over(): void
    {
        $this->as($this->agent);
        $id = $this->postJson('/whatsapp/chat/start', ['account_id' => $this->account->id, 'phone' => '+919111111111'])->assertOk()->json('data.id');
        $this->assertSame($this->agent->id, (int) WhatsappConversation::find($id)->assigned_to);
        $this->assertContains($id, collect($this->getJson('/whatsapp/chat/conversations')->json('data'))->pluck('id')->all());

        $this->as($this->other);
        $this->assertNotContains($id, collect($this->getJson('/whatsapp/chat/conversations')->json('data'))->pluck('id')->all());
        $this->postJson('/whatsapp/chat/start', ['account_id' => $this->account->id, 'phone' => '+919111111111'])->assertForbidden();
    }
}
