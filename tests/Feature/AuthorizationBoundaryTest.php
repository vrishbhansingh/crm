<?php

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuthorizationBoundaryTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant('primary');
        $this->user = $this->createUser($this->tenant, 'viewer');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->user, 'web')
            ->withSession(['session_token' => $this->user->session_token]);
    }

    public function test_read_permissions_do_not_authorize_mutations(): void
    {
        $this->grant('leads.view', 'users.view', 'company.view');
        $lead = $this->createLead($this->user);

        $this->postJson("/leads/{$lead->id}/notes", ['body' => 'unauthorized'])->assertForbidden();
        $this->postJson('/users', [])->assertForbidden();
        $this->postJson('/company-details', [])->assertForbidden();
    }

    public function test_ordinary_user_cannot_view_another_owners_sales_records(): void
    {
        $this->grant('leads.view', 'deals.view', 'orders.view');
        $owner = $this->createUser($this->tenant, 'owner');
        $lead = $this->createLead($owner);
        $pipeline = Pipeline::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Boundary Pipeline '.Str::random(6),
            'is_default' => true,
            'is_active' => true,
        ]);
        $stage = PipelineStage::create([
            'tenant_id' => $this->tenant->id,
            'pipeline_id' => $pipeline->id,
            'name' => 'Open',
            'sort_order' => 0,
        ]);
        $deal = Deal::create([
            'tenant_id' => $this->tenant->id,
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'lead_id' => $lead->id,
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'name' => 'Private Deal',
            'amount' => 100,
        ]);
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'order_number' => 'AUTH-'.Str::upper(Str::random(8)),
            'invoice_id' => 'AI'.Str::upper(Str::random(8)),
            'invoice_date' => now(),
            'user_id' => $owner->id,
            'total_amount' => 100,
            'net_amount' => 100,
            'paid_amount' => 0,
            'due_amount' => 100,
        ]);

        $this->get("/leads/{$lead->id}")->assertNotFound();
        $this->get("/deals/{$deal->id}")->assertNotFound();
        $this->get("/orders/{$order->id}")->assertNotFound();
    }

    public function test_tenant_admin_cannot_impersonate_a_user_from_another_tenant(): void
    {
        $this->grant('users.impersonate');
        $otherUser = $this->createUser($this->createTenant('other'), 'outsider');

        $this->postJson('/users/impersonate', ['id' => $otherUser->id])->assertNotFound();
        $this->assertAuthenticatedAs($this->user, 'web');
    }

    public function test_executable_lead_attachment_is_rejected(): void
    {
        Storage::fake('public');
        $this->grant('leads.edit');
        $lead = $this->createLead($this->user);

        $this->postJson("/leads/{$lead->id}/attachments", [
            'file' => UploadedFile::fake()->create('payload.php', 2, 'application/x-php'),
        ])->assertUnprocessable()->assertJsonValidationErrors('file');

        Storage::disk('public')->assertDirectoryEmpty('lead-attachments');
    }

    public function test_any_agent_with_edit_permission_can_upload_to_a_colleagues_lead_but_still_cannot_edit_it(): void
    {
        Storage::fake('local');
        $this->grant('leads.edit');
        $owner = $this->createUser($this->tenant, 'owner');
        $lead = $this->createLead($owner);

        $this->postJson("/leads/{$lead->id}/attachments", [
            'file' => UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf'),
        ])->assertOk()->assertJsonPath('status', true);

        $this->assertTrue(\App\Models\LeadAttachment::where('lead_id', $lead->id)->exists());

        $this->postJson("/leads/{$lead->id}/notes", ['body' => 'still not mine to edit'])
            ->assertNotFound();
    }

    /**
     * Documents stay company-isolated on two independent layers: the
     * uploaded file itself lives under lead-attachments/{tenant_id}/{lead_id}
     * on the private disk (never web-reachable directly), and the download
     * route always resolves through Lead/LeadAttachment's own tenant-scoped
     * query — so even knowing another company's attachment id outright
     * (not just guessing) can't retrieve it; the row itself isn't visible
     * outside its own tenant.
     */
    public function test_a_user_cannot_download_another_tenants_lead_attachment_even_knowing_its_id(): void
    {
        Storage::fake('local');
        $this->grant('leads.edit', 'leads.view');
        $lead = $this->createLead($this->user);

        $this->postJson("/leads/{$lead->id}/attachments", [
            'file' => UploadedFile::fake()->create('confidential.pdf', 100, 'application/pdf'),
        ])->assertOk();
        $attachmentId = \App\Models\LeadAttachment::where('lead_id', $lead->id)->firstOrFail()->id;

        $otherTenant = $this->createTenant('other');
        $otherUser = $this->createUser($otherTenant, 'outsider');
        $otherUser->givePermissionTo(Permission::findOrCreate('leads.view', 'web'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($otherUser, 'web')->withSession(['session_token' => $otherUser->session_token])
            ->get("/attachments/{$attachmentId}/download")
            ->assertNotFound();
    }

    private function grant(string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createTenant(string $label): Tenant
    {
        $suffix = Str::lower(Str::random(10));

        return Tenant::create([
            'name' => "Authorization {$label} {$suffix}",
            'slug' => "authorization-{$label}-{$suffix}",
            'status' => 'Active',
        ]);
    }

    private function createUser(Tenant $tenant, string $label): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'tenant_id' => $tenant->id,
            'name' => "Authorization {$label}",
            'email' => "authorization-{$label}-{$suffix}@example.test",
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => 'authorization-session-'.$suffix,
        ]);
    }

    private function createLead(User $owner): Lead
    {
        return Lead::create([
            'tenant_id' => $owner->tenant_id,
            'name' => 'Authorization Lead '.Str::random(8),
            'assigned_to' => $owner->id,
            'status' => 'Active',
            'is_converted' => 'No',
        ]);
    }
}
