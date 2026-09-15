<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeadFollowUpReminderNotificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_due_follow_up_reminder_is_sent_once_and_can_be_read(): void
    {
        $suffix = Str::lower(Str::random(8));
        $tenant = Tenant::create(['name' => 'Follow-up Tenant', 'slug' => 'followup-'.$suffix, 'status' => 'Active']);
        $user = User::create(['tenant_id' => $tenant->id, 'name' => 'Follow-up User', 'email' => "followup-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'followup-'.$suffix]);
        $lead = Lead::create([
            'tenant_id' => $tenant->id, 'name' => 'Reminder Lead', 'phone' => '9000000001',
            'lead_type' => 'inquiry', 'lead_status' => 'new', 'is_converted' => 'No',
            'assigned_to' => $user->id,
            'follow_up_date' => now()->subMinute()->toDateString(),
            'follow_up_time' => now()->subMinute()->format('H:i:s'),
        ]);

        $this->artisan('crm:send-lead-followup-reminders')->assertSuccessful();
        $this->artisan('crm:send-lead-followup-reminders')->assertSuccessful();
        $this->assertSame(1, $user->notifications()->count());
        $this->assertNotNull($lead->fresh()->follow_up_notified_at);

        $notification = $user->notifications()->firstOrFail();
        $this->assertSame('Follow-up reminder', $notification->data['title']);
        $this->assertStringContainsString('Reminder Lead', $notification->data['message']);

        $this->actingAs($user, 'web')->withSession(['session_token' => $user->session_token])
            ->postJson("/notifications/{$notification->id}/read")
            ->assertOk();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_a_future_follow_up_is_not_notified_yet(): void
    {
        $suffix = Str::lower(Str::random(8));
        $tenant = Tenant::create(['name' => 'Future Tenant', 'slug' => 'future-'.$suffix, 'status' => 'Active']);
        $user = User::create(['tenant_id' => $tenant->id, 'name' => 'Future User', 'email' => "future-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'future-'.$suffix]);
        Lead::create([
            'tenant_id' => $tenant->id, 'name' => 'Not Yet Lead', 'phone' => '9000000002',
            'lead_type' => 'inquiry', 'lead_status' => 'new', 'is_converted' => 'No',
            'assigned_to' => $user->id,
            'follow_up_date' => now()->addDay()->toDateString(),
            'follow_up_time' => '11:00:00',
        ]);

        $this->artisan('crm:send-lead-followup-reminders')->assertSuccessful();
        $this->assertSame(0, $user->notifications()->count());
    }

    public function test_rescheduling_a_follow_up_clears_the_previous_notified_flag(): void
    {
        $suffix = Str::lower(Str::random(8));
        $tenant = Tenant::create(['name' => 'Reschedule Tenant', 'slug' => 'resched-'.$suffix, 'status' => 'Active']);
        $user = User::create(['tenant_id' => $tenant->id, 'name' => 'Reschedule User', 'email' => "resched-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'resched-'.$suffix]);
        $lead = Lead::create([
            'tenant_id' => $tenant->id, 'name' => 'Reschedule Lead', 'phone' => '9000000003',
            'lead_type' => 'inquiry', 'lead_status' => 'new', 'is_converted' => 'No',
            'assigned_to' => $user->id,
            'follow_up_date' => now()->subMinute()->toDateString(),
            'follow_up_time' => now()->subMinute()->format('H:i:s'),
        ]);

        $this->artisan('crm:send-lead-followup-reminders')->assertSuccessful();
        $this->assertSame(1, $user->notifications()->count());
        $this->assertNotNull($lead->fresh()->follow_up_notified_at);

        // Re-fetch rather than reusing $lead: the reminder command above
        // updated follow_up_notified_at through its own model instance
        // inside chunkById(), which $lead here was never refreshed from —
        // exactly like a real request would always load its own fresh
        // instance rather than holding one across an external process.
        $lead = $lead->fresh();
        $lead->follow_up_date = now()->subMinute()->toDateString();
        $lead->follow_up_time = now()->addMinute()->format('H:i:s');
        $lead->save();
        $this->assertNull($lead->fresh()->follow_up_notified_at);
    }
}
