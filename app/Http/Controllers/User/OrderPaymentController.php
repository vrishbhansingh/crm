<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentDetails;
use App\Models\ProjectInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderPaymentController extends Controller
{
    //
    public function order_management()
    {
        return view('user.order_management.order_payment');
    }
    function order_view()
    {
        return view('user.order_management.payment_management');
    }
   
    public function get_order_payments()
    {
        $user_id =  Auth::guard('user')->user()->id;
        $orders = Order::where('user_id', $user_id)
            ->get();
        $data = [];
        $sl_no = 1;

        foreach ($orders as $order) {

            // Payment Status Badge


            // Action Button
            $action = '
                <a class="btn btn-sm btn-info"
                    href="' . route('user.order_view', $order->id) . '">
                    <i class="fa fa-eye"></i> View
                </a>
            ';

            $data[] = [
                'sl_no'          => $sl_no++,
                'order_number'   => $order->order_number,
                'payment_mode'   => strtoupper($order->payment_mode),
                'net_amount'     => $order->net_amount,
                2,
                'paid_amount'    => $order->paid_amount,
                2,
                'due_amount'     => $order->due_amount,
                2,
                'payment_status' => $order->payment_status,
                'action'         => $action
            ];
        }

        return response()->json([
            'data' => $data
        ]);
    }

    public function get_payment_data(Request $request)
    {
        $order_id = $request->id;

        $payments = PaymentDetails::join('orders', 'orders.id', '=', 'payment_details.order_id')

            // 🔥 Join user table
            ->leftJoin('user_list', 'user_list.id', '=', 'orders.user_id')

            // 🔥 Join project table
            ->leftJoin('project_info', 'project_info.id', '=', 'orders.project_id')
            ->leftJoin('leads', 'leads.id', '=', 'orders.lead_id')

            ->where('payment_details.order_id', $order_id)

            ->select(
                'payment_details.*',

                // Order table data
                'orders.order_number',
                'orders.lead_id',
                'orders.invoice_date',
                'orders.invoice_id',
                'orders.project_id',
                'orders.user_id',
                'orders.sub_total',
                'orders.discount',
                'orders.gst',
                'orders.total_amount',
                'orders.order_status',
                'orders.payment_terms',
                'orders.payment_status',
                'orders.currency',
                'orders.due_amount',
                'orders.net_amount',

                // 🔥 Extra Joined Data
                'user_list.name as user_name',
                'project_info.project_name as project_name',
                'leads.lead_number as lead_number'
            )

            ->orderBy('payment_details.payment_date', 'desc')
            ->get();
        // ✅ keep get() for future multiple payments

        if ($payments->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Payment not found',
                'data' => []
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $payments,
            'due_amount' => $payments->first()->due_amount,
            'order_number' => $payments->first()->order_number
        ]);
    }

    function add_order_payment(Request $request)
    {

        $rules = [
            'order_id'     => 'required|exists:orders,id',
            'payment_mode' => 'required|in:cash,upi,bank_transfer,cheque',
            'paid_amount'  => 'required|numeric|min:1',
            'payment_date' => 'required|date'
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

        $project_info = ProjectInfo::where('order_id',$order_id);

        $halfAmount = $net_amount / 2;

        if ($net_amount == $order->paid_amount) {
            $order->payment_status = 'paid';
            $project_info->actual_delivery_date = now();

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
