<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\RequestForQuotation;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RfqManagementTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $suffix = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'RFQ Co', 'slug' => 'rfq-'.$suffix, 'status' => 'Active']);
        $this->user = User::create([
            'tenant_id' => $this->tenant->id, 'name' => 'RFQ User',
            'email' => "rfq-{$suffix}@example.test", 'password' => Hash::make('password'),
            'status' => 'Active', 'session_token' => 'rfq-'.$suffix,
        ]);
        foreach (['purchase_orders.view', 'purchase_orders.create', 'purchase_orders.edit', 'purchase_orders.delete', 'vendors.view', 'products.view'] as $permission) {
            $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->user, 'web')->withSession(['session_token' => $this->user->session_token]);
    }

    public function test_user_can_create_an_rfq_with_items_and_invited_vendors(): void
    {
        $vendorA = Vendor::create(['tenant_id' => $this->tenant->id, 'name' => 'Vendor A', 'status' => 'Active']);
        $vendorB = Vendor::create(['tenant_id' => $this->tenant->id, 'name' => 'Vendor B', 'status' => 'Active']);

        $response = $this->postJson('/rfqs', [
            'items' => [['description' => 'Steel rods', 'uom' => 'Kg', 'quantity' => 100]],
            'vendor_ids' => [$vendorA->id, $vendorB->id],
        ])->assertOk();

        $rfq = RequestForQuotation::findOrFail($response->json('id'));
        $this->assertSame(1, $rfq->items()->count());
        $this->assertSame(2, $rfq->invitedVendors()->count());
        $this->assertStringStartsWith('RFQ-'.now()->format('Y').'-', $rfq->rfq_number);
    }

    public function test_recording_a_vendor_quote_and_converting_to_a_purchase_order(): void
    {
        $vendor = Vendor::create(['tenant_id' => $this->tenant->id, 'name' => 'Winning Vendor', 'status' => 'Active']);
        $rfq = RequestForQuotation::create(['tenant_id' => $this->tenant->id, 'rfq_number' => 'RFQ-TEST-1', 'status' => 'draft', 'owner_id' => $this->user->id, 'created_by' => $this->user->id]);
        $rfq->items()->create(['tenant_id' => $this->tenant->id, 'description' => 'Bolts', 'uom' => 'Pcs', 'quantity' => 500, 'sort_order' => 1]);
        $invite = $rfq->invitedVendors()->create(['tenant_id' => $this->tenant->id, 'vendor_id' => $vendor->id]);

        $this->postJson("/rfqs/{$rfq->id}/vendors/{$invite->id}/quote", ['quoted_amount' => 4500])->assertOk();
        $this->assertSame('4500.00', (string) $invite->fresh()->quoted_amount);

        $response = $this->postJson("/rfqs/{$rfq->id}/convert-to-po", ['vendor_id' => $vendor->id])->assertOk();
        $po = PurchaseOrder::findOrFail($response->json('purchase_order_id'));

        $this->assertSame($vendor->id, $po->vendor_id);
        $this->assertSame($rfq->id, $po->rfq_id);
        $this->assertSame(1, $po->items()->count());
        $this->assertSame('Bolts', $po->items()->first()->description);
        $this->assertSame('converted', $rfq->fresh()->status);
    }

    public function test_index_create_and_show_pages_render(): void
    {
        $vendor = Vendor::create(['tenant_id' => $this->tenant->id, 'name' => 'Page Vendor', 'status' => 'Active']);
        $rfq = RequestForQuotation::create(['tenant_id' => $this->tenant->id, 'rfq_number' => 'RFQ-TEST-2', 'status' => 'draft', 'owner_id' => $this->user->id, 'created_by' => $this->user->id]);
        $rfq->invitedVendors()->create(['tenant_id' => $this->tenant->id, 'vendor_id' => $vendor->id]);

        $this->get('/rfqs')->assertOk();
        $this->get('/rfqs/create')->assertOk();
        $this->get("/rfqs/{$rfq->id}")->assertOk();
    }
}
