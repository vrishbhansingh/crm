<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $suffix = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'Audit Tenant', 'slug' => 'audit-'.$suffix, 'status' => 'Active']);
        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Audit User',
            'email' => "audit-{$suffix}@example.test",
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => 'audit-session-'.$suffix,
        ]);
        $this->user->givePermissionTo(Permission::findOrCreate('audit.view', 'web'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->user, 'web')->withSession(['session_token' => $this->user->session_token]);
    }

    public function test_model_changes_are_recorded_with_actor_and_tenant(): void
    {
        $lead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Audited Lead',
            'status' => 'Active',
            'is_converted' => 'No',
        ]);
        $lead->update(['name' => 'Audited Lead Updated']);

        $log = AuditLog::where('auditable_type', 'Lead')->where('auditable_id', $lead->id)->where('event', 'updated')->firstOrFail();
        $this->assertSame($this->tenant->id, $log->tenant_id);
        $this->assertSame($this->user->id, $log->actor_id);
        $this->assertSame('Audited Lead', $log->old_values['name']);
        $this->assertSame('Audited Lead Updated', $log->new_values['name']);
    }

    public function test_sensitive_user_fields_are_redacted(): void
    {
        $this->user->update(['password' => Hash::make('a-new-password')]);

        $log = AuditLog::where('auditable_type', 'User')->where('auditable_id', $this->user->id)->where('event', 'updated')->latest()->firstOrFail();
        $this->assertSame('[REDACTED]', $log->old_values['password']);
        $this->assertSame('[REDACTED]', $log->new_values['password']);
    }

    public function test_audit_api_is_tenant_isolated(): void
    {
        $other = Tenant::create(['name' => 'Other Audit Tenant', 'slug' => 'other-audit-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $otherLead = Lead::withoutGlobalScopes()->create(['tenant_id' => $other->id, 'name' => 'Other Audit Lead', 'status' => 'Active', 'is_converted' => 'No']);
        $ownLead = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Own Audit Lead', 'status' => 'Active', 'is_converted' => 'No']);

        $response = $this->getJson('/audit-log/data')->assertOk()
            ->assertJsonFragment(['auditable_type' => 'Lead', 'auditable_id' => $ownLead->id]);

        $foreignLogIsVisible = collect($response->json('data'))->contains(
            fn (array $log) => $log['auditable_type'] === 'Lead' && $log['auditable_id'] === $otherLead->id
        );
        $this->assertFalse($foreignLogIsVisible);
    }
}
