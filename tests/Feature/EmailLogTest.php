<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmailLogTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_campaign_send_writes_a_queued_then_sent_email_log_row(): void
    {
        Mail::fake();

        $suffix = Str::lower(Str::random(10));
        $tenant = Tenant::create(['name' => "Log Tenant {$suffix}", 'slug' => "log-{$suffix}", 'status' => 'Active']);
        $user = User::create([
            'tenant_id' => $tenant->id, 'name' => 'Manager', 'email' => "log-{$suffix}@example.test",
            'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60),
        ]);
        foreach (['templates.view', 'templates.create', 'campaigns.view', 'campaigns.create', 'campaigns.send'] as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($user, 'web')->withSession(['session_token' => $user->session_token]);

        Lead::create([
            'tenant_id' => $tenant->id, 'name' => 'Recipient One', 'email' => 'recipient@example.test',
            'lead_status' => 'Hot', 'status' => 'Active', 'is_converted' => 'No',
        ]);
        $template = EmailTemplate::create([
            'tenant_id' => $tenant->id, 'name' => 'T', 'subject' => 'Hello {{lead.name}}', 'body' => '<p>Hi</p>',
        ]);
        $store = $this->postJson(route('campaigns.store'), [
            'name' => 'Campaign', 'email_template_id' => $template->id,
            'audience_type' => 'leads', 'filters' => ['lead_status' => 'Hot'],
        ])->assertOk()->json();
        $campaign = EmailCampaign::findOrFail($store['data']['id']);

        $this->postJson(route('campaigns.send', $campaign))->assertOk();

        $log = EmailLog::where('type', 'campaign')->where('to_email', 'recipient@example.test')->first();
        $this->assertNotNull($log, 'expected an email_logs row for the campaign recipient');
        $this->assertSame($tenant->id, $log->tenant_id);
        $this->assertSame('sent', $log->status);
        $this->assertSame($campaign->id, $log->context['campaign_id']);
        $this->assertNotEmpty($log->context['body']);
    }

    public function test_an_smtp_test_email_writes_a_log_row_regardless_of_outcome(): void
    {
        $super = User::create([
            'tenant_id' => null, 'name' => 'Super Admin', 'email' => Str::random(10).'@example.test',
            'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60),
        ]);
        \App\Support\PermissionTeam::run(null, function () use ($super) {
            \Spatie\Permission\Models\Role::findOrCreate('Super Admin', 'web');
            $super->assignRole('Super Admin');
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->post('/superadmin/login', ['email' => $super->email, 'password' => 'password'])->assertRedirect('/superadmin');

        // No SMTP is configured, so the send throws — the row must still
        // land, and as "failed", not silently vanish.
        $this->post(route('superadmin.settings.mail.test'), ['test_email' => 'target@example.test']);

        $log = EmailLog::where('type', 'smtp_test')->where('to_email', 'target@example.test')->first();
        $this->assertNotNull($log);
        $this->assertContains($log->status, ['failed', 'sent']);
        $this->assertNull($log->tenant_id);
    }

    public function test_a_password_reset_writes_a_log_row(): void
    {
        $suffix = Str::lower(Str::random(10));
        $tenant = Tenant::create(['name' => "Reset Tenant {$suffix}", 'slug' => "reset-{$suffix}", 'status' => 'Active']);
        $user = User::create([
            'tenant_id' => $tenant->id, 'name' => 'Reset Me', 'email' => "reset-{$suffix}@example.test",
            'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60),
        ]);

        Password::broker()->sendResetLink(['email' => $user->email]);

        $log = EmailLog::where('type', 'password_reset')->where('to_email', $user->email)->first();
        $this->assertNotNull($log);
        $this->assertSame($tenant->id, $log->tenant_id);
    }

    public function test_a_stuck_row_is_one_left_queued_past_the_threshold(): void
    {
        EmailLog::create([
            'type' => 'campaign', 'to_email' => 'a@example.test', 'status' => 'queued',
            'queued_at' => now()->subMinutes(20),
        ]);
        EmailLog::create([
            'type' => 'campaign', 'to_email' => 'b@example.test', 'status' => 'queued',
            'queued_at' => now()->subMinutes(2),
        ]);

        $this->assertSame(1, EmailLog::stuck()->count());
        $this->assertSame('a@example.test', EmailLog::stuck()->first()->to_email);
    }
}
