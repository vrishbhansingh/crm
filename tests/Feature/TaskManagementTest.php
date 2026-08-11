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
