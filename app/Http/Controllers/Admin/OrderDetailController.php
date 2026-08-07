<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentDetails;
use App\Models\ProjectInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Backs the unified Order Detail page (increment 2) — merges the old
 * admin edit-order/view-sales-order pages with the user payment_management
 * page's payment history + add-payment form into one page. Order edit
 * fields are gated by orders.edit (@can in the view, orders.edit route
 * middleware here); the payment history and add-payment form are available
 * to anyone with orders.view, elevated or not — recording a payment isn't
 * editing the order. Mirrors Admin\LeadDetailController's shape: show()
 * renders the page shell, the rest are the AJAX endpoints it calls.
 */
class OrderDetailController extends Controller
{
    public function show($id)
    {
        Order::findOrFail($id);

        return view('orders.show', ['orderId' => $id]);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // NOTE: invoice_id, lead_id, project_id, user_id are NOT edited on this
        // form — leave them untouched so an update can't wipe them to null.
        $order->order_number   = $request->order_number;
        $order->invoice_date   = $request->invoice_date;

        $order->sub_total      = $request->sub_total;
        $order->discount       = $request->discount;
        $order->gst            = $request->gst;
        $order->total_amount   = $request->total_amount;
        $order->paid_amount    = $request->paid_amount;
        $order->due_amount     = $request->due_amount;

        $order->order_status   = $request->order_status;
        $order->payment_status = $request->payment_status;
        $order->currency       = $request->currency;

        $result = $order->update();

        return response()->json([
            'status'  => (bool) $result,
            'message' => $result ? 'Order updated successfully' : 'Something went wrong',
        ]);
    }

    public function paymentsData($id)
    {
        $order = Order::with(['user', 'project', 'lead'])->findOrFail($id);

        $payments = PaymentDetails::where('order_id', $order->id)
            ->orderBy('payment_date', 'desc')
            ->get(['id', 'payment_mode', 'payment_date', 'paid_amount']);

        return response()->json([
            'status' => true,
            'data' => $payments,
            'order' => [
                'order_number'   => $order->order_number,
                'invoice_id'     => $order->invoice_id,
                'invoice_date'   => $order->invoice_date,
                'sub_total'      => $order->sub_total,
                'discount'       => $order->discount,
                'gst'            => $order->gst,
                'total_amount'   => $order->total_amount,
                'net_amount'     => $order->net_amount,
                'paid_amount'    => $order->paid_amount,
                'due_amount'     => $order->due_amount,
                'order_status'   => $order->order_status,
                'payment_status' => $order->payment_status,
                'payment_terms'  => $order->payment_terms,
                'currency'       => $order->currency,
                'user_name'      => $order->user->name ?? null,
                'project_name'   => $order->project->project_name ?? null,
                'lead_number'    => $order->lead->lead_number ?? null,
            ],
            // Kept at top level too — matches the shape the old
            // user.get_payment_data response used, which the merged view's
            // JS still reads directly.
            'due_amount' => $order->due_amount,
            'order_number' => $order->order_number,
        ]);
    }

    public function paymentsStore(Request $request)
    {
        $rules = [
            'order_id'     => 'required|exists:orders,id',
            'payment_mode' => 'required|in:cash,upi,bank_transfer,cheque',
            'paid_amount'  => 'required|numeric|min:1',
            'payment_date' => 'required|date',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $order_id = $request->order_id;
        $order = Order::where('id', $order_id)->first();
        $previous_due_amount = $order->due_amount;
        $previous_paid_amount = $order->paid_amount;
        $net_amount = $order->net_amount;
        $payment = new PaymentDetails();
        $payment->order_id = $request->order_id;
        $payment->payment_mode = $request->payment_mode;
        $payment->paid_amount = $request->paid_amount;
        $payment->payment_date = $request->payment_date;
        $order->due_amount = $previous_due_amount - $request->paid_amount;
        $order->paid_amount = $previous_paid_amount + $request->paid_amount;

        $halfAmount = $net_amount / 2;

        if ($net_amount == $order->paid_amount) {
            $order->payment_status = 'paid';

            // Bug fix (increment 2): the old code built
            // ProjectInfo::where('order_id', $order_id) — project_info has no
            // order_id column — then set ->actual_delivery_date on the
            // unexecuted query builder, which persisted nothing. The intent
            // was to mark the project delivered once its order is fully
            // paid; project_info is linked via orders.project_id, not the
            // other way around.
            if ($order->project_id) {
                ProjectInfo::where('id', $order->project_id)->update(['actual_delivery_date' => now()]);
            }
        } elseif ($order->paid_amount >= $halfAmount && $order->paid_amount < $net_amount) {
            $order->payment_status = 'partial';
        } else {
            $order->payment_status = 'pending';
        }

        if ($order)
            $result1 = $order->update();
        $result2 = $payment->save();
        if ($result1 && $result2) {
            return response()->json([
                'status' => true,
                'message' => 'Payment Successful'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Payment Failed'
            ]);
        }
    }
}
