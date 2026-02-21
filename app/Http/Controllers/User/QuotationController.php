<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\CompanyDetails;
use App\Models\Lead;
use App\Models\Order;
use Illuminate\Http\Client\Request;

class QuotationController extends Controller
{
    //
    public function quotation_template_1($id)
    {
        $decodedId = $this->decodeBase64Id($id);
        $lead = Lead::findOrFail($decodedId);
        $order = Order::where('lead_id', $decodedId)->first();
        $company_details = CompanyDetails::first();
        return view('user.quotation.template-1', compact('lead', 'order', 'company_details'));
    }

    public function quotation_template_2($id)
    {
        $decodedId = $this->decodeBase64Id($id);
        $lead = Lead::findOrFail($decodedId);
        $order = Order::where('lead_id', $decodedId)->first();
        $company_details = CompanyDetails::first();
        return view('user.quotation.template-2', compact('lead', 'order', 'company_details'));
    }
    public function quotation_template_3($id)
    {
        $decodedId = $this->decodeBase64Id($id);
        $lead = Lead::findOrFail($decodedId);
        $order = Order::where('lead_id', $decodedId)->first();
        $company_details = CompanyDetails::first();
        return view('user.quotation.template-3', compact('lead', 'order', 'company_details'));
    }

    public function decodeBase64Id($encodedId)
    {
        // padding add karo (important)
        $padding = strlen($encodedId) % 4;
        if ($padding > 0) {
            $encodedId .= str_repeat('=', 4 - $padding);
        }

        return base64_decode($encodedId);
    }



}
