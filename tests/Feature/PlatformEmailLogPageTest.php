<?php

namespace Tests\Feature;

use App\Models\EmailLog;
use App\Models\Tenant;
use App\Models\User;
use App\Support\PermissionTeam;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlatformEmailLogPageTest extends TestCase
{
    use DatabaseTransactions;

    private User $super;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Log View Co', 'slug' => 'logview-'.Str::random(8), 'status' => 'Active']);
        $this->super = User::create([
            'tenant_id' => null, 'name' => 'Super Admin', 'email' => Str::random(10).'@example.test',
            'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60),
        ]);
        PermissionTeam::run(null, function () {
            Role::findOrCreate('Super Admin', 'web');
            $this->super->assignRole('Super Admin');
        });
        $this->post('/superadmin/login', ['email' => $this->super->email, 'password' => 'password'])
            ->assertRedirect('/superadmin');

        EmailLog::create(['tenant_id' => $this->tenant->id, 'type' => 'campaign', 'to_email' => 'sent@example.test', 'status' => 'sent', 'queued_at' => now(), 'sent_at' => now()]);
        EmailLog::create(['tenant_id' => $this->tenant->id, 'type' => 'campaign', 'to_email' => 'failed@example.test', 'status' => 'failed', 'error' => 'Boom', 'queued_at' => now(), 'context' => ['campaign_id' => 1, 'recipient_id' => 1, 'body' => 'x']]);
        EmailLog::create(['tenant_id' => null, 'type' => 'smtp_test', 'to_email' => 'admin@example.test', 'status' => 'sent', 'queued_at' => now(), 'sent_at' => now()]);
        EmailLog::create(['tenant_id' => $this->tenant->id, 'type' => 'campaign', 'to_email' => 'stuck@example.test', 'status' => 'queued', 'queued_at' => now()->subMinutes(30)]);
    }

    public function test_the_page_loads_and_shows_every_log_row(): void
    {
        $response = $this->get(route('superadmin.email_log.index'))->assertOk();
        $response->assertSee('sent@example.test')
            ->assertSee('failed@example.test')
            ->assertSee('admin@example.test')
            ->assertSee('stuck@example.test');
    }

    public function test_filtering_by_stuck_status_returns_only_the_stuck_row(): void
    {
        $response = $this->get(route('superadmin.email_log.index', ['status' => 'stuck']))->assertOk();
        $response->assertSee('stuck@example.test')
            ->assertDontSee('sent@example.test')
            ->assertDontSee('failed@example.test');
    }

    public function test_filtering_by_failed_status_and_tenant(): void
    {
        $response = $this->get(route('superadmin.email_log.index', [
            'status' => 'failed', 'tenant_id' => $this->tenant->id,
        ]))->assertOk();
        $response->assertSee('failed@example.test')->assertDontSee('sent@example.test');
    }

    public function test_filtering_to_platform_only_hides_tenant_rows(): void
    {
        $response = $this->get(route('superadmin.email_log.index', ['tenant_id' => 'platform']))->assertOk();
        $response->assertSee('admin@example.test')
            ->assertDontSee('sent@example.test')
            ->assertDontSee('failed@example.test');
    }

    public function test_retrying_a_failed_campaign_email_requeues_it(): void
    {
        // The queue runs synchronously in tests (phpunit.xml sets
        // QUEUE_CONNECTION=sync), so by the time the request returns, the
        // re-queued attempt has already been picked up and finished — a
        // brand new row exists rather than the original staying "queued".
        Mail::fake();

        $log = EmailLog::where('to_email', 'failed@example.test')->first();
        $originalCount = EmailLog::where('to_email', 'failed@example.test')->count();

        $this->post(route('superadmin.email_log.retry', $log))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame($originalCount + 1, EmailLog::where('to_email', 'failed@example.test')->count());
        $this->assertDatabaseHas('email_logs', [
            'to_email' => 'failed@example.test', 'status' => 'sent',
        ]);
        // The original failed row is untouched — retry adds a new attempt,
        // it doesn't rewrite history.
        $log->refresh();
        $this->assertSame('failed', $log->status);
    }

    public function test_retrying_a_non_campaign_or_non_failed_row_is_refused(): void
    {
        $sentLog = EmailLog::where('to_email', 'sent@example.test')->first();
        $this->post(route('superadmin.email_log.retry', $sentLog))->assertSessionHasErrors('retry');
    }
}
