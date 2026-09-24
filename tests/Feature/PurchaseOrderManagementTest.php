<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\TaxRate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PurchaseOrderManagementTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $suffix = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'PO Co', 'slug' => 'po-'.$suffix, 'status' => 'Active']);
        $this->user = User::create([
            'tenant_id' => $this->tenant->id, 'name' => 'PO User',
            'email' => "po-{$suffix}@example.test", 'password' => Hash::make('password'),
            'status' => 'Active', 'session_token' => 'po-'.$suffix,
        ]);
        foreach (['purchase_orders.view', 'purchase_orders.create', 'purchase_orders.edit', 'purchase_orders.delete', 'vendors.view', 'products.view'] as $permission) {
            $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->user, 'web')->withSession(['session_token' => $this->user->session_token]);
    }

    private function vendor(): Vendor
    {
        return Vendor::create(['tenant_id' => $this->tenant->id, 'name' => 'Test Vendor', 'status' => 'Active']);
    }

    public function test_user_can_create_a_purchase_order_and_it_gets_a_number(): void
    {
        $vendor = $this->vendor();

        $response = $this->postJson('/purchase-orders', ['vendor_id' => $vendor->id])->assertOk();
        $po = PurchaseOrder::findOrFail($response->json('id'));

        $this->assertSame($this->tenant->id, $po->tenant_id);
        $this->assertSame($vendor->id, $po->vendor_id);
        $this->assertSame('draft', $po->status);
        $this->assertStringStartsWith('PO-'.now()->format('Y').'-', $po->po_number);
    }

    public function test_user_can_create_a_purchase_order_with_line_items_in_one_request(): void
    {
        $vendor = $this->vendor();
        $product = Product::create(['tenant_id' => $this->tenant->id, 'name' => 'Raw Material', 'unit_price' => 100, 'status' => 'Active']);
        $taxRate = TaxRate::create(['tenant_id' => $this->tenant->id, 'name' => 'GST 18%', 'rate_percent' => 18]);

        $response = $this->postJson('/purchase-orders', [
            'vendor_id' => $vendor->id,
            'items' => [
                ['product_id' => $product->id, 'description' => 'Raw Material', 'quantity' => 10, 'unit_price' => 100, 'tax_rate_id' => $taxRate->id],
            ],
        ])->assertOk();

        $po = PurchaseOrder::findOrFail($response->json('id'));
        $this->assertSame(1, $po->items()->count());
        // 10 * 100 = 1000, +18% tax = 1180
        $this->assertSame('1000.00', (string) $po->sub_total);
        $this->assertSame('180.00', (string) $po->tax_amount);
        $this->assertSame('1180.00', (string) $po->total_amount);
    }

    public function test_editing_is_blocked_once_sent_and_send_requires_at_least_one_item(): void
    {
        $vendor = $this->vendor();
        $po = PurchaseOrder::create(['tenant_id' => $this->tenant->id, 'vendor_id' => $vendor->id, 'po_number' => 'PO-TEST-1', 'status' => 'draft', 'owner_id' => $this->user->id, 'created_by' => $this->user->id]);

        $this->postJson("/purchase-orders/{$po->id}/send")->assertUnprocessable();

        $this->postJson("/purchase-orders/{$po->id}/items", ['description' => 'Item', 'quantity' => 1, 'unit_price' => 50])->assertOk();
        $this->postJson("/purchase-orders/{$po->id}/send")->assertOk();
        $this->assertSame('sent', $po->fresh()->status);

        $this->putJson("/purchase-orders/{$po->id}", ['vendor_id' => $vendor->id, 'notes' => 'x'])->assertUnprocessable();
    }

    public function test_purchase_order_from_another_tenant_is_not_visible(): void
    {
        $other = Tenant::create(['name' => 'Other PO Co', 'slug' => 'other-po-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $foreignVendor = Vendor::withoutGlobalScopes()->create(['tenant_id' => $other->id, 'name' => 'Foreign Vendor', 'status' => 'Active']);
        $foreignPo = PurchaseOrder::withoutGlobalScopes()->create(['tenant_id' => $other->id, 'vendor_id' => $foreignVendor->id, 'po_number' => 'PO-OTHER-1', 'status' => 'draft']);

        $this->getJson("/purchase-orders/{$foreignPo->id}/detail")->assertNotFound();
    }

    public function test_pdf_route_returns_a_pdf_document(): void
    {
        $vendor = $this->vendor();
        $po = PurchaseOrder::create(['tenant_id' => $this->tenant->id, 'vendor_id' => $vendor->id, 'po_number' => 'PO-TEST-2', 'status' => 'draft', 'owner_id' => $this->user->id, 'created_by' => $this->user->id]);

        $response = $this->get("/purchase-orders/{$po->id}/pdf");
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_index_create_and_show_pages_render(): void
    {
        $vendor = $this->vendor();
        $po = PurchaseOrder::create(['tenant_id' => $this->tenant->id, 'vendor_id' => $vendor->id, 'po_number' => 'PO-TEST-3', 'status' => 'draft', 'owner_id' => $this->user->id, 'created_by' => $this->user->id]);

        $this->get('/purchase-orders')->assertOk();
        $this->get('/purchase-orders/create')->assertOk();
        $this->get("/purchase-orders/{$po->id}")->assertOk();
    }
}
