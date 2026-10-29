<?php

namespace Tests\Feature;

use App\Mail\CampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignEvent;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CampaignTracker;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmailCampaignTrackingTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $s = Str::lower(Str::random(10));
        $this->tenant = Tenant::create(['name' => "Track Tenant {$s}", 'slug' => "track-{$s}", 'status' => 'Active']);
        $this->user = User::create(['tenant_id' => $this->tenant->id, 'name' => 'Track Manager', 'email' => "track-{$s}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60)]);

        foreach (['templates.view', 'templates.create', 'campaigns.view', 'campaigns.create', 'campaigns.send'] as $permission) {
            $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        TenantContext::set($this->tenant->id);

        $this->actingAs($this->user, 'web')->withSession(['session_token' => $this->user->session_token]);
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    private function sentCampaign(): array
    {
        Mail::fake();
        $lead = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Priya Sharma', 'email' => 'priya@example.test', 'lead_status' => 'Hot', 'status' => 'Active', 'is_converted' => 'No']);
        $template = EmailTemplate::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Offer', 'subject' => 'Offer for {{lead.name}}',
            'body' => '<html><body><p>Hi {{lead.name}}</p><a href="https://example.com/pricing?a=1&amp;b=2">See pricing</a> <a href="mailto:sales@example.test">Mail us</a></body></html>',
        ]);
        $campaign = EmailCampaign::findOrFail($this->postJson(route('campaigns.store'), [
            'name' => 'Offer blast', 'email_template_id' => $template->id, 'audience_type' => 'leads', 'filters' => ['lead_status' => 'Hot'],
        ])->assertOk()->json('data.id'));
        $this->postJson(route('campaigns.send', $campaign))->assertOk();

        $html = '';
        Mail::assertSent(CampaignMail::class, function (CampaignMail $m) use (&$html) {
            $html = $m->htmlBody;

            return true;
        });

        return [$campaign, $campaign->recipients()->firstOrFail(), $html, $lead];
    }

    public function test_sent_emails_carry_a_pixel_and_tracked_links_and_opens_and_clicks_are_recorded(): void
    {
        [$campaign, $recipient, $html] = $this->sentCampaign();

        $this->assertMatchesRegularExpression('#<img src="[^"]*/t/o/[^"]+" width="1" height="1"#', $html);
        $this->assertStringNotContainsString('href="https://example.com/pricing', $html, 'the real link is hidden behind the tracked one');
        $this->assertStringContainsString('href="mailto:sales@example.test"', $html, 'mailto links are left alone');

        preg_match('#src="([^"]*/t/o/[^"]+)"#', $html, $pixel);
        preg_match('#href="([^"]*/t/c/[^"]+)"#', $html, $link);

        // Open: image comes back, and it is recorded once as a unique open, twice as total opens.
        $this->get(html_entity_decode($pixel[1]))->assertOk()->assertHeader('Content-Type', 'image/gif');
        $this->get(html_entity_decode($pixel[1]))->assertOk();
        $recipient->refresh();
        $campaign->refresh();
        $this->assertSame(2, $recipient->open_count);
        $this->assertNotNull($recipient->opened_at);
        $this->assertSame(1, $campaign->opened_count, 'unique people, not total opens');
        $this->assertSame(0, $campaign->clicked_count);

        // Click: redirected to the REAL destination (query string intact), recorded.
        $this->get(html_entity_decode($link[1]))->assertRedirect('https://example.com/pricing?a=1&b=2');
        $recipient->refresh();
        $campaign->refresh();
        $this->assertSame(1, $recipient->click_count);
        $this->assertSame(1, $campaign->clicked_count);
        $this->assertSame(3, EmailCampaignEvent::where('email_campaign_id', $campaign->id)->count());
        $this->assertSame('https://example.com/pricing?a=1&b=2', EmailCampaignEvent::where('type', 'click')->first()->url);
    }

    public function test_a_click_counts_as_an_open_even_when_images_are_blocked(): void
    {
        [$campaign, $recipient, $html] = $this->sentCampaign();
        preg_match('#href="([^"]*/t/c/[^"]+)"#', $html, $link);

        $this->get(html_entity_decode($link[1]))->assertRedirect();

        $this->assertNotNull($recipient->fresh()->opened_at);
        $this->assertSame(1, $campaign->fresh()->opened_count);
        $this->assertSame(1, $campaign->fresh()->clicked_count);
    }

    public function test_edited_or_forged_tokens_record_nothing_and_cannot_be_used_as_an_open_redirect(): void
    {
        [$campaign, $recipient, $html] = $this->sentCampaign();
        preg_match('#src="([^"]*/t/o/[^"]+)"#', $html, $pixel);
        preg_match('#href="([^"]*/t/c/[^"]+)"#', $html, $link);

        // A pixel with a damaged signature still returns an image (never an error page) but records nothing.
        $this->get(html_entity_decode($pixel[1]).'x')->assertOk()->assertHeader('Content-Type', 'image/gif');
        $this->get('/t/o/not-a-real-token')->assertOk();
        $this->assertSame(0, $recipient->fresh()->open_count);

        // A click link with a damaged signature is refused, not redirected anywhere.
        $this->get(html_entity_decode($link[1]).'x')->assertNotFound();
        $this->get('/t/c/anything.at-all')->assertNotFound();

        // Even a correctly signed token cannot point at a non-web destination.
        $tracker = app(CampaignTracker::class);
        $evil = $tracker->clickUrl($this->tenant->id, $recipient->id, 'javascript:alert(1)');
        $this->get($evil)->assertNotFound();

        $this->assertSame(0, EmailCampaignEvent::where('email_campaign_id', $campaign->id)->count());
    }

    public function test_the_report_shows_who_opened_who_clicked_and_is_filterable_and_company_scoped(): void
    {
        $campaign = EmailCampaign::create(['tenant_id' => $this->tenant->id, 'name' => 'Report me', 'email_template_id' => EmailTemplate::create(['tenant_id' => $this->tenant->id, 'name' => 'T', 'subject' => 'S', 'body' => 'B'])->id, 'audience_type' => 'leads', 'status' => 'sent', 'sent_at' => now()]);
        $mk = fn (string $name, string $email, array $extra = []) => EmailCampaignRecipient::create(array_merge([
            'tenant_id' => $this->tenant->id, 'email_campaign_id' => $campaign->id, 'recipient_type' => 'lead',
            'recipient_id' => Lead::create(['tenant_id' => $this->tenant->id, 'name' => $name, 'email' => $email, 'status' => 'Active', 'is_converted' => 'No'])->id,
            'email' => $email, 'status' => 'sent', 'sent_at' => now()->subHour(),
        ], $extra));

        $clicker = $mk('Clara Clicker', 'clara@example.test', ['opened_at' => now()->subMinutes(50), 'open_count' => 2, 'clicked_at' => now()->subMinutes(49), 'click_count' => 1]);
        $mk('Oscar Opener', 'oscar@example.test', ['opened_at' => now()->subMinutes(30), 'open_count' => 1]);
        $mk('Nina Nothing', 'nina@example.test');
        $mk('Fred Failed', 'fred@example.test', ['status' => 'failed', 'error' => 'Mailbox full', 'sent_at' => null]);
        EmailCampaignEvent::create(['tenant_id' => $this->tenant->id, 'email_campaign_id' => $campaign->id, 'email_campaign_recipient_id' => $clicker->id, 'type' => 'click', 'url' => 'https://example.com/a', 'created_at' => now()->subMinutes(49)]);
        EmailCampaignEvent::create(['tenant_id' => $this->tenant->id, 'email_campaign_id' => $campaign->id, 'email_campaign_recipient_id' => $clicker->id, 'type' => 'click', 'url' => 'https://example.com/a', 'created_at' => now()->subMinutes(40)]);

        $this->get(route('campaigns.report', $campaign->id))->assertOk()->assertSee('Report me');

        $data = $this->getJson(route('campaigns.report.data', $campaign->id))->assertOk()->json('data');
        $this->assertSame(['total' => 4, 'sent' => 3, 'failed' => 1, 'pending' => 0, 'opened' => 2, 'open_rate' => 66.7, 'clicked' => 1, 'click_rate' => 33.3], $data['summary']);
        $this->assertSame('https://example.com/a', $data['top_links'][0]['url']);
        $this->assertEquals(2, $data['top_links'][0]['clicks']);
        $this->assertEquals(1, $data['top_links'][0]['people']);
        $this->assertSame('Clara Clicker', $data['timeline'][1]['name'] ?? null);

        $names = fn (string $filter, string $q = '') => collect($this->getJson(route('campaigns.report.data', $campaign->id).'?filter='.$filter.'&q='.$q)->json('data.recipients'))->pluck('name')->sort()->values()->all();
        $this->assertSame(['Clara Clicker'], $names('clicked'));
        $this->assertSame(['Clara Clicker', 'Oscar Opener'], $names('opened'));
        $this->assertSame(['Nina Nothing'], $names('not_opened'));
        $this->assertSame(['Fred Failed'], $names('failed'));
        $this->assertSame(['Oscar Opener'], $names('all', 'oscar'));

        // Another company's campaign is simply not there.
        $other = Tenant::create(['name' => 'Other Co', 'slug' => 'other-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $foreign = EmailCampaign::withoutGlobalScope('tenant')->create(['tenant_id' => $other->id, 'name' => 'Secret', 'email_template_id' => $campaign->email_template_id, 'audience_type' => 'leads', 'status' => 'sent']);
        $this->getJson(route('campaigns.report.data', $foreign->id))->assertNotFound();
        $this->get(route('campaigns.report', $foreign->id))->assertNotFound();
    }

    public function test_the_campaign_list_has_a_report_button_and_open_click_columns(): void
    {
        $this->get(route('campaigns.index'))->assertOk()->assertSee('Opened')->assertSee('Clicked')->assertSee('/report', false);
    }
}
