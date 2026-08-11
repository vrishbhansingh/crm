<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class TaskReminderNotificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_due_task_reminder_is_sent_once_and_can_be_read(): void
    {
        $suffix = Str::lower(Str::random(8));
        $tenant = Tenant::create(['name' => 'Reminder Tenant', 'slug' => 'reminder-'.$suffix, 'status' => 'Active']);
        $user = User::create(['tenant_id' => $tenant->id, 'name' => 'Reminder User', 'email' => "reminder-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'reminder-'.$suffix]);
        $task = Task::create(['tenant_id' => $tenant->id, 'assigned_to' => $user->id, 'created_by' => $user->id, 'title' => 'Reminder task', 'priority' => 'high', 'status' => 'todo', 'due_at' => now()->addHour(), 'remind_at' => now()->subMinute()]);

        $this->artisan('crm:send-task-reminders')->assertSuccessful();
        $this->artisan('crm:send-task-reminders')->assertSuccessful();
        $this->assertSame(1, $user->notifications()->count());
        $this->assertNotNull($task->fresh()->notification_sent_at);

        $notification = $user->notifications()->firstOrFail();
        $this->actingAs($user, 'web')->withSession(['session_token' => $user->session_token])
            ->postJson("/notifications/{$notification->id}/read")
            ->assertOk();
        $this->assertNotNull($notification->fresh()->read_at);
    }
}
