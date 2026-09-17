<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = $this->tenant('main');
        $this->user = $this->user($this->tenant, 'worker');
        foreach (['tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete'] as $permission) {
            $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->user, 'web')->withSession(['session_token' => $this->user->session_token]);
    }

    public function test_user_can_create_and_complete_a_linked_task(): void
    {
        $lead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Task Lead',
            'assigned_to' => $this->user->id,
            'status' => 'Active',
            'is_converted' => 'No',
        ]);

        $response = $this->postJson('/tasks', [
            'title' => 'Call the decision maker',
            'assigned_to' => $this->user->id,
            'related_type' => 'lead',
            'related_id' => $lead->id,
            'priority' => 'high',
            'status' => 'todo',
            'due_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertOk();

        $task = Task::findOrFail($response->json('id'));
        $this->postJson("/tasks/{$task->id}/complete")->assertOk();
        $this->assertSame('completed', $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_task_rejects_a_related_record_from_another_tenant(): void
    {
        $other = $this->tenant('other');
        $lead = Lead::withoutGlobalScopes()->create([
            'tenant_id' => $other->id,
            'name' => 'Other Lead',
            'status' => 'Active',
            'is_converted' => 'No',
        ]);

        $this->postJson('/tasks', [
            'title' => 'Invalid task',
            'related_type' => 'lead',
            'related_id' => $lead->id,
            'priority' => 'medium',
            'status' => 'todo',
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('tasks', ['title' => 'Invalid task']);
    }

    public function test_ordinary_user_cannot_view_or_modify_another_users_task(): void
    {
        $owner = $this->user($this->tenant, 'owner');
        $task = Task::create([
            'tenant_id' => $this->tenant->id,
            'assigned_to' => $owner->id,
            'created_by' => $owner->id,
            'title' => 'Private task',
            'priority' => 'medium',
            'status' => 'todo',
        ]);

        $this->getJson('/tasks/data')->assertOk()->assertJsonMissing(['id' => $task->id]);
        $this->postJson("/tasks/{$task->id}/complete")->assertNotFound();
        $this->deleteJson("/tasks/{$task->id}")->assertNotFound();
    }

    public function test_user_can_create_a_call_and_a_meeting_with_activity_details(): void
    {
        $call = $this->postJson('/tasks', [
            'title' => 'Follow-up call',
            'priority' => 'medium',
            'status' => 'todo',
            'activity_type' => 'call',
            'activity_details' => ['direction' => 'outbound', 'disposition' => 'connected', 'duration_minutes' => 12],
        ])->assertOk();

        $callTask = Task::findOrFail($call->json('id'));
        $this->assertSame('call', $callTask->activity_type);
        $this->assertSame('connected', $callTask->activity_details['disposition']);

        $meeting = $this->postJson('/tasks', [
            'title' => 'Kickoff meeting',
            'priority' => 'medium',
            'status' => 'todo',
            'activity_type' => 'meeting',
            'activity_details' => ['location' => 'HQ', 'attendees' => [['name' => 'Ananya', 'email' => 'a@example.test']]],
        ])->assertOk();

        $meetingTask = Task::findOrFail($meeting->json('id'));
        $this->assertSame('meeting', $meetingTask->activity_type);
        $this->assertSame('Ananya', $meetingTask->activity_details['attendees'][0]['name']);
    }

    public function test_completion_is_blocked_until_the_dependency_is_done(): void
    {
        $blocker = Task::create([
            'tenant_id' => $this->tenant->id, 'assigned_to' => $this->user->id, 'created_by' => $this->user->id,
            'title' => 'Blocker', 'priority' => 'medium', 'status' => 'todo',
        ]);
        $blocked = Task::create([
            'tenant_id' => $this->tenant->id, 'assigned_to' => $this->user->id, 'created_by' => $this->user->id,
            'title' => 'Blocked', 'priority' => 'medium', 'status' => 'todo', 'depends_on_task_id' => $blocker->id,
        ]);

        $this->postJson("/tasks/{$blocked->id}/complete")->assertUnprocessable();
        $this->assertSame('todo', $blocked->fresh()->status);

        $this->postJson("/tasks/{$blocker->id}/complete")->assertOk();
        $this->postJson("/tasks/{$blocked->id}/complete")->assertOk();
        $this->assertSame('completed', $blocked->fresh()->status);
    }

    public function test_completing_a_recurring_task_spawns_the_next_occurrence(): void
    {
        $dueAt = now()->addDay();
        $task = Task::create([
            'tenant_id' => $this->tenant->id, 'assigned_to' => $this->user->id, 'created_by' => $this->user->id,
            'title' => 'Weekly check-in', 'priority' => 'medium', 'status' => 'todo', 'due_at' => $dueAt,
            'recurrence_rule' => 'weekly', 'recurrence_interval' => 1,
        ]);

        $this->postJson("/tasks/{$task->id}/complete")->assertOk();

        $next = Task::where('recurrence_parent_id', $task->id)->first();
        $this->assertNotNull($next, 'Expected the next occurrence to be created.');
        $this->assertSame('todo', $next->status);
        $this->assertSame('Weekly check-in', $next->title);
        // Compare to the second — due_at round-trips through a MySQL
        // datetime column, which drops the microseconds a fresh Carbon
        // instance still carries.
        $this->assertSame($dueAt->copy()->addWeek()->format('Y-m-d H:i:s'), $next->due_at->format('Y-m-d H:i:s'));
    }

    public function test_checklist_item_can_be_toggled(): void
    {
        $task = Task::create([
            'tenant_id' => $this->tenant->id, 'assigned_to' => $this->user->id, 'created_by' => $this->user->id,
            'title' => 'With checklist', 'priority' => 'medium', 'status' => 'todo',
            'checklist' => [['text' => 'Step one', 'done' => false], ['text' => 'Step two', 'done' => false]],
        ]);

        $this->postJson("/tasks/{$task->id}/checklist-toggle", ['index' => 0])->assertOk();

        $this->assertTrue($task->fresh()->checklist[0]['done']);
        $this->assertFalse($task->fresh()->checklist[1]['done']);
    }

    public function test_bulk_create_makes_one_task_per_selected_lead(): void
    {
        $leadOne = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Lead One', 'status' => 'Active', 'is_converted' => 'No']);
        $leadTwo = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Lead Two', 'status' => 'Active', 'is_converted' => 'No']);

        $this->postJson('/tasks/bulk', [
            'related_type' => 'lead',
            'related_ids' => [$leadOne->id, $leadTwo->id],
            'title' => 'Send welcome email',
            'priority' => 'low',
        ])->assertOk();

        $this->assertSame(2, Task::where('title', 'Send welcome email')->count());
        $this->assertDatabaseHas('tasks', ['title' => 'Send welcome email', 'related_type' => 'lead', 'related_id' => $leadOne->id]);
        $this->assertDatabaseHas('tasks', ['title' => 'Send welcome email', 'related_type' => 'lead', 'related_id' => $leadTwo->id]);
    }

    private function tenant(string $label): Tenant
    {
        $suffix = Str::lower(Str::random(8));

        return Tenant::create(['name' => "Task {$label}", 'slug' => "task-{$label}-{$suffix}", 'status' => 'Active']);
    }

    private function user(Tenant $tenant, string $label): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'tenant_id' => $tenant->id,
            'name' => "Task {$label}",
            'email' => "task-{$label}-{$suffix}@example.test",
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => 'task-session-'.$suffix,
        ]);
    }
}
