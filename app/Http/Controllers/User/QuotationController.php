<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\CompanyDetails;
use App\Models\Lead;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    //
    public function quotation_template_1($id)
    {
        $decodedId = $this->decodeBase64Id($id);
        $lead = $this->findVisibleLead($decodedId);
        $order = $this->resolveOrder($lead, $decodedId);
        $company_details = CompanyDetails::first();

        return view('user.quotation.template-1', compact('lead', 'order', 'company_details'));
    }

    public function quotation_template_2($id)
    {
        $decodedId = $this->decodeBase64Id($id);
        $lead = $this->findVisibleLead($decodedId);
        $order = $this->resolveOrder($lead, $decodedId);
        $company_details = CompanyDetails::first();

        return view('user.quotation.template-2', compact('lead', 'order', 'company_details'));
    }

    public function quotation_template_3($id)
    {
        $decodedId = $this->decodeBase64Id($id);
        $lead = $this->findVisibleLead($decodedId);
        $order = $this->resolveOrder($lead, $decodedId);
        $company_details = CompanyDetails::first();

        return view('user.quotation.template-3', compact('lead', 'order', 'company_details'));
    }

    /**
     * Return the lead's order, or a non-persisted fallback order with sensible
     * defaults so the quotation templates render even when no order exists yet.
     */
    private function resolveOrder($lead, $leadId)
    {
        $order = Order::where('lead_id', $leadId)->first();

        if (! $order) {
            $amount = $lead->conversion_value ?? $lead->budget ?? 0;

            $order = new Order();
            $order->order_number = 'DRAFT';
            $order->currency = '₹';
            $order->sub_total = $amount;
            $order->discount = 0;
            $order->gst = 0;
            $order->total_amount = $amount;
            $order->net_amount = $amount;
            $order->paid_amount = 0;
            $order->due_amount = $amount;
            $order->payment_terms = '-';
            $order->payment_mode = '-';
            $order->payment_status = 'pending';
            $order->order_status = 'new';
            $order->invoice_date = now();
        }

        return $order;
    }

    public function decodeBase64Id($encodedId): int
    {
        // padding add karo (important)
        $padding = strlen($encodedId) % 4;
        if ($padding > 0) {
            $encodedId .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($encodedId, true);
        abort_if($decoded === false || ! ctype_digit($decoded) || (int) $decoded < 1, 404);

        return (int) $decoded;
    }

    private function findVisibleLead(int $id): Lead
    {
        $user = Auth::guard('web')->user();
        $query = Lead::query();

        if (! $user->hasElevatedAccess() && ! $user->hasAnyRole(['Finance', 'Support'])) {
            $query->where('assigned_to', $user->id);
        }

        return $query->findOrFail($id);
    }
}
