<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentDetails;
use App\Models\ProjectInfo;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OrderPaymentTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    private Order $order;

    private ProjectInfo $project;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = Str::lower(Str::random(10));
        $this->tenant = Tenant::create(['name' => 'Payment QA '.$suffix, 'slug' => 'payment-qa-'.$suffix, 'status' => 'Active']);
        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Payment QA User',
            'email' => "payment-qa-{$suffix}@example.test",
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => 'payment-session-'.$suffix,
        ]);
        $this->project = ProjectInfo::create(['tenant_id' => $this->tenant->id, 'project_name' => 'Payment QA Project']);
        $this->order = Order::create([
            'tenant_id' => $this->tenant->id,
            'order_number' => 'ORD-QA-'.Str::upper(Str::random(8)),
            'invoice_id' => 'IQ'.Str::upper(Str::random(7)),
            'invoice_date' => now(),
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
            'sub_total' => 1000,
            'total_amount' => 1000,
            'net_amount' => 1000,
            'paid_amount' => 0,
            'due_amount' => 1000,
            'order_status' => 'new',
            'payment_status' => 'pending',
            'currency' => 'INR',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->user, 'web')->withSession(['session_token' => $this->user->session_token]);
    }

    public function test_view_permission_cannot_record_a_payment(): void
    {
        $this->grant('orders.view');

        $this->postJson('/orders/payments', $this->paymentPayload(100))->assertForbidden();
    }

    public function test_partial_and_final_payments_update_balances_atomically(): void
    {
        $this->grant('orders.edit');

        $this->postJson('/orders/payments', $this->paymentPayload(400))->assertOk();
        $this->order->refresh();
        $this->assertSame(400.0, (float) $this->order->paid_amount);
        $this->assertSame(600.0, (float) $this->order->due_amount);
        $this->assertSame('partial', $this->order->payment_status);

        $this->postJson('/orders/payments', $this->paymentPayload(600))->assertOk();
        $this->order->refresh();
        $this->project->refresh();
        $this->assertSame(1000.0, (float) $this->order->paid_amount);
        $this->assertSame(0.0, (float) $this->order->due_amount);
        $this->assertSame('paid', $this->order->payment_status);
        $this->assertNotNull($this->project->actual_delivery_date);
        $this->assertSame(2, PaymentDetails::where('order_id', $this->order->id)->count());
    }

    public function test_overpayment_is_rejected_without_changing_the_order(): void
    {
        $this->grant('orders.edit');

        $this->postJson('/orders/payments', $this->paymentPayload(1001))->assertUnprocessable();
        $this->order->refresh();
        $this->assertSame(0.0, (float) $this->order->paid_amount);
        $this->assertSame(1000.0, (float) $this->order->due_amount);
        $this->assertSame(0, PaymentDetails::where('order_id', $this->order->id)->count());
    }

    private function grant(string $permission): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function paymentPayload(float $amount): array
    {
        return [
            'order_id' => $this->order->id,
            'payment_mode' => 'bank_transfer',
            'paid_amount' => $amount,
            'payment_date' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
