<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentDetails;
use App\Models\ProjectInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderDetailController extends Controller
{
    public function show($id)
    {
        $this->findVisibleOrder($id);

        return view('orders.show', ['orderId' => $id]);
    }

    public function update(Request $request, $id)
    {
        $order = $this->findEditableOrder($id);
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:255', Rule::unique('orders', 'order_number')->ignore($order->id)],
            'invoice_date' => 'nullable|date',
            'sub_total' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'gst' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'order_status' => ['required', Rule::in(['new', 'approved', 'in_progress', 'on_hold', 'delivered', 'closed', 'cancelled'])],
            'currency' => 'required|string|max:10',
        ]);

        $order->fill($data);
        $order->net_amount = $data['total_amount'];
        $order->due_amount = max((float) $order->net_amount - (float) $order->paid_amount, 0);
        $order->payment_status = $order->due_amount <= 0
            ? 'paid'
            : ((float) $order->paid_amount > 0 ? 'partial' : 'pending');
        $result = $order->save();

        return response()->json([
            'status' => (bool) $result,
            'message' => $result ? 'Order updated successfully' : 'Something went wrong',
        ]);
    }

    public function paymentsData($id)
    {
        $order = $this->findVisibleOrder($id);
        $order->load(['user', 'project', 'lead']);

        $payments = PaymentDetails::where('order_id', $order->id)
            ->orderBy('payment_date', 'desc')
            ->get(['id', 'payment_mode', 'payment_date', 'paid_amount']);

        return response()->json([
            'status' => true,
            'data' => $payments,
            'order' => [
                'order_number' => $order->order_number,
                'invoice_id' => $order->invoice_id,
                'invoice_date' => $order->invoice_date,
                'sub_total' => $order->sub_total,
                'discount' => $order->discount,
                'gst' => $order->gst,
                'total_amount' => $order->total_amount,
                'net_amount' => $order->net_amount,
                'paid_amount' => $order->paid_amount,
                'due_amount' => $order->due_amount,
                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status,
                'payment_terms' => $order->payment_terms,
                'currency' => $order->currency,
                'user_name' => $order->user->name ?? null,
                'project_name' => $order->project->project_name ?? null,
                'lead_number' => $order->lead->lead_number ?? null,
            ],
            'due_amount' => $order->due_amount,
            'order_number' => $order->order_number,
        ]);
    }

    public function paymentsStore(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'payment_mode' => 'required|in:cash,upi,bank_transfer,cheque',
            'paid_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        $this->findEditableOrder($request->integer('order_id'));

        DB::connection(app(\App\Tenancy\TenantConnectionManager::class)->connectionName())->transaction(function () use ($request) {
            $order = Order::whereKey($request->integer('order_id'))->lockForUpdate()->firstOrFail();
            $amount = (float) $request->paid_amount;
            $due = (float) $order->due_amount;

            if ($amount > $due) {
                abort(422, 'Payment amount cannot exceed the outstanding balance.');
            }

            PaymentDetails::create([
                'tenant_id' => $order->tenant_id,
                'order_id' => $order->id,
                'payment_mode' => $request->payment_mode,
                'paid_amount' => $amount,
                'payment_date' => $request->payment_date,
            ]);

            $order->paid_amount = (float) $order->paid_amount + $amount;
            $order->due_amount = max((float) $order->net_amount - (float) $order->paid_amount, 0);
            $order->payment_status = $order->due_amount <= 0 ? 'paid' : 'partial';
            $order->save();

            if ($order->payment_status === 'paid' && $order->project_id) {
                ProjectInfo::whereKey($order->project_id)->update(['actual_delivery_date' => now()]);
            }
        });

        return response()->json(['status' => true, 'message' => 'Payment recorded successfully']);
    }

    private function findVisibleOrder(int $id): Order
    {
        $user = Auth::guard('web')->user();
        $query = Order::query();

        if (! $user->hasElevatedAccess() && ! $user->hasAnyRole(['Finance', 'Support'])) {
            $query->where('user_id', $user->id);
        }

        return $query->findOrFail($id);
    }

    private function findEditableOrder(int $id): Order
    {
        $user = Auth::guard('web')->user();
        $query = Order::query();

        if (! $user->hasElevatedAccess() && ! $user->hasRole('Finance')) {
            $query->where('user_id', $user->id);
        }

        return $query->findOrFail($id);
    }
}
