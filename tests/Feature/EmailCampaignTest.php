<?php

namespace Tests\Feature;

use App\Mail\CampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmailCampaignTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = Str::lower(Str::random(10));
        $this->tenant = Tenant::create([
            'name' => "Campaign Tenant {$suffix}",
            'slug' => "campaign-{$suffix}",
            'status' => 'Active',
        ]);
        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Campaign Manager',
            'email' => "campaign-{$suffix}@example.test",
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => Str::random(60),
        ]);

        foreach (['templates.view', 'templates.create', 'templates.delete', 'campaigns.view', 'campaigns.create', 'campaigns.send'] as $permission) {
            $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->user, 'web')
            ->withSession(['session_token' => $this->user->session_token]);
    }

    public function test_a_campaign_resolves_variables_per_recipient_and_sends_only_to_matching_leads(): void
    {
        Mail::fake();

        $matchingLead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Priya Sharma',
            'email' => 'priya@example.test',
            'lead_status' => 'Hot',
            'status' => 'Active',
            'is_converted' => 'No',
        ]);
        $otherLead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Someone Else',
            'email' => 'someone@example.test',
            'lead_status' => 'Cold',
            'status' => 'Active',
            'is_converted' => 'No',
        ]);

        $template = EmailTemplate::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Follow-up',
            'subject' => 'Hi {{lead.name}}, following up',
            'body' => '<p>Dear {{lead.name}}, thanks for your interest.</p>',
        ]);

        $preview = $this->postJson(route('campaigns.preview_audience'), [
            'audience_type' => 'leads',
            'filters' => ['lead_status' => 'Hot'],
        ])->assertOk()->json();
        $this->assertSame(1, $preview['count']);

        $store = $this->postJson(route('campaigns.store'), [
            'name' => 'Hot Leads Follow-up',
            'email_template_id' => $template->id,
            'audience_type' => 'leads',
            'filters' => ['lead_status' => 'Hot'],
        ])->assertOk()->json();

        $campaign = EmailCampaign::findOrFail($store['data']['id']);

        $this->postJson(route('campaigns.send', $campaign))
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.sent_count', 1)
            ->assertJsonPath('data.status', 'sent');

        Mail::assertSent(CampaignMail::class, function (CampaignMail $mail) use ($matchingLead) {
            return $mail->hasTo($matchingLead->email)
                && $mail->mailSubject === 'Hi Priya Sharma, following up'
                && str_contains($mail->htmlBody, 'Dear Priya Sharma');
        });

        Mail::assertNotSent(CampaignMail::class, fn (CampaignMail $mail) => $mail->hasTo($otherLead->email));

        $campaign->refresh();
        $this->assertSame(1, $campaign->total_recipients);
        $this->assertSame('sent', $campaign->recipients()->first()->status);
    }

    public function test_cc_bcc_and_a_chosen_smtp_are_saved_used_and_validated_and_the_template_preview_resolves(): void
    {
        Mail::fake();

        $lead = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Asha Rao', 'assigned_to' => $this->user->id, 'email' => 'asha@example.test', 'lead_status' => 'Hot', 'status' => 'Active', 'is_converted' => 'No']);
        $template = EmailTemplate::create(['tenant_id' => $this->tenant->id, 'name' => 'Hello', 'subject' => 'Hi {{lead.name}}', 'body' => '<p>Dear {{lead.name}}</p>']);
        $smtp = \App\Models\TenantMailSetting::create(['tenant_id' => $this->tenant->id, 'name' => 'Sales SMTP', 'smtp_host' => 'smtp.example.test', 'smtp_port' => 587]);
        $foreign = \App\Models\TenantMailSetting::create(['tenant_id' => Tenant::create(['name' => 'Other', 'slug' => 'oth-'.Str::lower(Str::random(8)), 'status' => 'Active'])->id, 'name' => 'Theirs', 'smtp_host' => 'x.test', 'smtp_port' => 25]);

        $base = ['name' => 'Wizard', 'email_template_id' => $template->id, 'audience_type' => 'leads', 'filters' => ['lead_status' => 'Hot']];

        $this->postJson(route('campaigns.store'), $base + ['cc' => ['not-an-email']])->assertStatus(422);
        $this->postJson(route('campaigns.store'), $base + ['mail_setting_id' => $foreign->id])->assertStatus(422);

        $id = $this->postJson(route('campaigns.store'), $base + ['mail_setting_id' => $smtp->id, 'cc' => ['boss@example.test'], 'bcc' => ['audit@example.test']])->assertOk()->json('data.id');
        $campaign = EmailCampaign::findOrFail($id);
        $this->assertSame($smtp->id, (int) $campaign->mail_setting_id);

        $this->postJson(route('campaigns.send', $campaign))->assertOk()->assertJsonPath('data.sent_count', 1);
        Mail::assertSent(CampaignMail::class, fn (CampaignMail $m) => $m->hasTo('asha@example.test') && $m->hasCc('boss@example.test') && $m->hasBcc('audit@example.test'));
        $this->assertSame('smtp.example.test', config('mail.mailers.smtp.host'), 'the campaign used its own chosen SMTP');

        $this->postJson(route('campaigns.template_preview'), ['email_template_id' => $template->id, 'audience_type' => 'leads'])
            ->assertOk()->assertJsonPath('subject', 'Hi Asha Rao');
    }

    public function test_a_template_used_by_a_campaign_cannot_be_deleted(): void
    {
        $template = EmailTemplate::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'In use',
            'subject' => 'Subject',
            'body' => 'Body',
        ]);
        EmailCampaign::create([
            'tenant_id' => $this->tenant->id,
            'email_template_id' => $template->id,
            'name' => 'Uses it',
            'audience_type' => 'leads',
            'audience_filters' => [],
        ]);

        $this->deleteJson(route('templates.destroy', $template))
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }
}
