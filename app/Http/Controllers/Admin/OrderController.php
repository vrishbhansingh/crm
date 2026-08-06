<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use PDO;

class OrderController extends Controller
{
    //
    public function sales_orders()
    {
        return view('admin.sales-orders.sales-orders');
    }
    public function edit_sales_orders()
    {
        return view('admin.sales-orders.edit-order');
    }

    function view_sales_order_list()
    {
        return view('admin.sales-orders.view-sales-order');
    }
    function get_order_list()
    {
        $orders = Order::select(
            'orders.*',
            'users.name as user_name',
            'project_info.project_name'
        )
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->leftJoin('project_info', 'project_info.id', '=', 'orders.project_id')
            ->orderBy('orders.id', 'DESC')
            ->get();
        $data = [];
        $sl_no = 1;

        foreach ($orders as $order) {
            $action = '
                <a href="' . route('admin.view_sales_order_list', $order->id) . '"
                   class="btn btn-sm btn-info mb-1">
                    <i class="fa fa-eye"></i>
                </a>

                <a 
                    href="' . route('admin.edit_sales_orders', $order->id) . '"
                    class="btn btn-sm btn-primary mb-1 editOrderLink"
                >
                    <i class="fa fa-edit"></i>
                </a>
                ';
            $data[] = [
                'sl_no' => $sl_no++,
                'order_number'   => $order->order_number,
                'invoice_date'   => $order->invoice_date,
                'invoice_id'     => $order->invoice_id,
                'user_name'        => $order->user_name,
                'project_name'     => $order->project_name,
                'total_amount'   => $order->total_amount,
                'paid_amount'    => $order->paid_amount,
                'due_amount'     => $order->due_amount,
                'currency'       => $order->currency,
                'payment_status' => $order->payment_status,
                'order_status'   => $order->order_status,
                'action'         => $action
            ];
        }
        if ($data) {
            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }
    }

    public function get_order_by_id(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);

        $order = Order::findOrFail($request->id);

        // 🔹 Build data array first (BEST PRACTICE)
        $data = [];

        $data['id']             = $order->id;
        $data['order_number']   = $order->order_number;

        $data['invoice_date']   = $order->invoice_date
            ? date('Y-m-d', strtotime($order->invoice_date))
            : '';


        $data['sub_total']      = $order->sub_total;
        $data['discount']       = $order->discount;
        $data['gst']            = $order->gst;
        $data['total_amount']   = $order->total_amount;

        $data['payment_terms']  = $order->payment_terms;
        $data['payment_status'] = $order->payment_status;

        $data['currency']       = $order->currency;
        $data['paid_amount']    = $order->paid_amount;
        $data['due_amount']     = $order->due_amount;
        $data['net_amount']     = $order->net_amount;

        $data['order_status']   = $order->order_status;

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }

    public function update_sales_order(Request $request)
    {

        $order = Order::findOrFail($request->id);

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

        // 4️⃣ Save
        $result = $order->update();
        if ($result) {
            return response()->json([
                'status'  => true,
                'message' => 'Order updated successfully'
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong'
            ]);
        }
    }

    function get_view_sales_order_list(Request $request)
    {
        $id = $request->id;
        $orders = Order::select(
            'orders.*',
            'users.name as user_name',
            'project_info.project_name'
        )
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->leftJoin('project_info', 'project_info.id', '=', 'orders.project_id')
            ->where('orders.id', $id)
            ->get();
        $data = [];
        $sl_no = 1;

        foreach ($orders as $order) {
            $payments = \App\Models\PaymentDetails::where('order_id', $order->id)
                ->orderBy('payment_date', 'asc')
                ->get(['payment_mode', 'payment_date', 'paid_amount']);

            $data[] = [
                'sl_no' => $sl_no++,
                'order_number'   => $order->order_number,
                'invoice_date'   => $order->invoice_date,
                'invoice_id'     => $order->invoice_id,
                'user_name'      => $order->user_name,
                'project_name'   => $order->project_name,
                'sub_total'      => $order->sub_total,
                'discount'       => $order->discount,
                'gst'            => $order->gst,
                'net_amount'     => $order->net_amount,
                'total_amount'   => $order->total_amount,
                'paid_amount'    => $order->paid_amount,
                'due_amount'     => $order->due_amount,
                'currency'       => $order->currency,
                'payment_terms'  => $order->payment_terms,
                'payment_status' => $order->payment_status,
                'order_status'   => $order->order_status,
                'payments'       => $payments,
            ];
        }
        if ($data) {
            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }
    }
}
