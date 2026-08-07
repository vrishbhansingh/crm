<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    //
    public function sales_orders()
    {
        return view('orders.index');
    }

    function get_order_list()
    {
        $me = Auth::guard('web')->user();

        // Elevated roles see every order in the tenant; everyone else sees
        // only orders assigned to them — same unified-interface data scoping
        // Leads already uses (Admin\LeadController::get_lead).
        $query = $me->hasElevatedAccess() ? Order::query() : Order::where('orders.user_id', $me->id);

        $orders = $query->select(
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
                <a href="' . route('orders.show', $order->id) . '"
                   class="btn btn-sm btn-info mb-1">
                    <i class="fa fa-eye"></i> View
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

        return response()->json([
            'status' => true,
            'data' => []
        ]);
    }
}
