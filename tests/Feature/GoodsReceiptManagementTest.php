<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class GoodsReceiptManagementTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    private PurchaseOrder $po;

    protected function setUp(): void
    {
        parent::setUp();
        $suffix = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'GRN Co', 'slug' => 'grn-'.$suffix, 'status' => 'Active']);
        $this->user = User::create([
            'tenant_id' => $this->tenant->id, 'name' => 'GRN User',
            'email' => "grn-{$suffix}@example.test", 'password' => Hash::make('password'),
            'status' => 'Active', 'session_token' => 'grn-'.$suffix,
        ]);
        foreach (['purchase_orders.view', 'purchase_orders.edit'] as $permission) {
            $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->user, 'web')->withSession(['session_token' => $this->user->session_token]);

        $vendor = Vendor::create(['tenant_id' => $this->tenant->id, 'name' => 'GRN Vendor', 'status' => 'Active']);
        $this->po = PurchaseOrder::create(['tenant_id' => $this->tenant->id, 'vendor_id' => $vendor->id, 'po_number' => 'PO-GRN-1', 'status' => 'sent', 'owner_id' => $this->user->id, 'created_by' => $this->user->id]);
    }

    public function test_partial_receipt_marks_po_partially_received_and_full_receipt_marks_received(): void
    {
        $item = $this->po->items()->create(['tenant_id' => $this->tenant->id, 'description' => 'Cable Reel', 'uom' => 'Nos', 'quantity' => 10, 'unit_price' => 20, 'sort_order' => 1]);

        $this->postJson("/purchase-orders/{$this->po->id}/goods-receipts", [
            'received_date' => now()->format('Y-m-d'),
            'lines' => [['purchase_order_item_id' => $item->id, 'received_qty' => 4]],
        ])->assertOk();

        $this->assertSame('4.00', (string) $item->fresh()->received_qty);
        $this->assertSame('partially_received', $this->po->fresh()->status);

        $this->postJson("/purchase-orders/{$this->po->id}/goods-receipts", [
            'received_date' => now()->format('Y-m-d'),
            'lines' => [['purchase_order_item_id' => $item->id, 'received_qty' => 6]],
        ])->assertOk();

        $this->assertSame('10.00', (string) $item->fresh()->received_qty);
        $this->assertSame('received', $this->po->fresh()->status);
        $this->assertSame(2, $this->po->goodsReceipts()->count());
    }

    public function test_goods_receipt_is_rejected_for_a_draft_purchase_order(): void
    {
        $this->po->update(['status' => 'draft']);
        $item = $this->po->items()->create(['tenant_id' => $this->tenant->id, 'description' => 'Item', 'quantity' => 5, 'unit_price' => 10, 'sort_order' => 1]);

        $this->postJson("/purchase-orders/{$this->po->id}/goods-receipts", [
            'received_date' => now()->format('Y-m-d'),
            'lines' => [['purchase_order_item_id' => $item->id, 'received_qty' => 1]],
        ])->assertUnprocessable();
    }
}
