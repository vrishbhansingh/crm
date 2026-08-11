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

class ApiIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $suffix = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'API Tenant', 'slug' => 'api-'.$suffix, 'status' => 'Active']);
        $this->user = User::create(['tenant_id' => $this->tenant->id, 'name' => 'API User', 'email' => "api-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active']);
        foreach (['leads.view', 'leads.create', 'tasks.view', 'tasks.create'] as $permission) {
            $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_api_token_reads_only_its_tenant_and_owned_records(): void
    {
        $ownLead = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'API Own Lead', 'assigned_to' => $this->user->id, 'status' => 'Active', 'is_converted' => 'No']);
        $otherTenant = Tenant::create(['name' => 'API Other', 'slug' => 'api-other-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $foreignLead = Lead::withoutGlobalScopes()->create(['tenant_id' => $otherTenant->id, 'name' => 'API Foreign Lead', 'status' => 'Active', 'is_converted' => 'No']);
        $token = $this->user->createToken('integration', ['crm:read'])->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/leads')->assertOk()
            ->assertJsonFragment(['id' => $ownLead->id, 'name' => 'API Own Lead']);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertFalse($ids->contains($foreignLead->id));
    }

    public function test_write_token_can_create_tenant_scoped_leads_and_tasks(): void
    {
        $token = $this->user->createToken('writer', ['crm:read', 'crm:write'])->plainTextToken;

        $leadResponse = $this->withToken($token)->postJson('/api/v1/leads', [
            'name' => 'Created through API',
            'email' => 'api-lead@example.test',
            'lead_source' => 'website',
        ])->assertCreated();
        $this->assertDatabaseHas('leads', ['id' => $leadResponse->json('data.id'), 'tenant_id' => $this->tenant->id, 'assigned_to' => $this->user->id]);

        $taskResponse = $this->withToken($token)->postJson('/api/v1/tasks', [
            'title' => 'API follow-up',
            'assigned_to' => $this->user->id,
            'priority' => 'high',
        ])->assertCreated();
        $this->assertSame($this->tenant->id, Task::findOrFail($taskResponse->json('data.id'))->tenant_id);
    }

    public function test_read_only_token_cannot_write_and_inactive_tenant_cannot_use_api(): void
    {
        $readToken = $this->user->createToken('reader', ['crm:read'])->plainTextToken;
        $this->withToken($readToken)->postJson('/api/v1/leads', ['name' => 'Forbidden'])->assertForbidden();

        $this->tenant->update(['status' => 'Inactive']);
        $this->withToken($readToken)->getJson('/api/v1/leads')->assertForbidden();
    }
}
