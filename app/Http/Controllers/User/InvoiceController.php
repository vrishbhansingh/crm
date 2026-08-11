<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentDetails;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    //
    public function invoice($id)
    {

        // Get order with lead & user
        $user = Auth::guard('web')->user();
        $query = Order::with(['lead', 'user', 'project']);

        if (! $user->hasElevatedAccess() && ! $user->hasAnyRole(['Finance', 'Support'])) {
            $query->where('user_id', $user->id);
        }

        $order = $query->findOrFail($id);

        $payments = PaymentDetails::where('order_id', $id)
            ->orderBy('payment_date', 'asc')
            ->get();

        // Last payment date = invoice date
        $lastPayment = $payments->last();
        $invoiceDate = $lastPayment ? $lastPayment->payment_date : $order->invoice_date;

        return view('user.invoice.invoice', compact(
            'order',
            'payments',
            'invoiceDate'
        ));
    }
}
