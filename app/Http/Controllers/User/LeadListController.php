<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\CompanyDetails;
use App\Models\Lead;
use App\Models\Leadfollowup;
use App\Models\Order;
use App\Models\PaymentDetails;
use App\Models\ProjectInfo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;


class LeadListController extends Controller
{
    //
    function leadList()
    {
        return view('user.leadList.lead_list');
    }

    function viewleadList()
    {
        return view('user.leadList.view_lead');
    }
    function viewclosedlead()
    {
        return view('user.closedList.view-closed-lead');
    }

    function closed_lead_list()
    {
        return view('user.closedList.closed-lead');
    }
    function get_lead_list()
    {
        $user = Auth::guard('web')->user();
        $leads = Lead::where('assigned_to', $user->id)
            ->where('lead_status', '!=', 'closed')
            ->get();
        $leadData = [];
        foreach ($leads as $index => $lead) {
            $encodedId = rtrim(base64_encode($lead->id), '=');
            $leadData[] = [
                'id' => $lead->id,
                'sr_no' => $index + 1,
                'lead_type' => ucfirst($lead->lead_type ?? '-'),
                'name' => $lead->name ?? '-',
                'phone' => $lead->phone ?? '-',
                'email' => $lead->email ?? '',
                'lead_source' => ucfirst($lead->lead_source ?? '-'),
                'lead_status' => $lead->lead_status ?? '-',
                'priority' => ucfirst($lead->priority ?? '-'),
                'follow_up_date' => $lead->follow_up_date ?? '-',
                'action' => '
                <a class="action-btn view" id = "view_user_lead"
                   href="' . route('user.view_lead_list', $encodedId) . '">
                    <i class="fa fa-eye"></i> View
                </a>
            '
            ];
        }

        return response()->json([
            'leads' => $leadData
        ]);
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

    function get_viewLead_data(Request $request)
    {
        $decodedId = $this->decodeBase64Id($request->id);
        $lead = Lead::findOrFail($decodedId);

        if ($lead) {
            return response()->json([
                'status' => true,
                'lead' => $lead
            ]);
        }
    }

    function user_lead_call_update(Request $request)
    {
        $user_id = Auth::guard('web')->user()->id;
        $id = $request->id;
        $lead_response = $request->lead_response ? $request->lead_response : null;
        $follow_up_date = $request->followup_date;
        $follow_up_time = $request->followup_time;
        $call_status = $request->call_status;
        $call_note = $request->call_notes;

        $lead_follow_up = new Leadfollowup();
        $lead_follow_up->user_id = $user_id;
        $lead_follow_up->lead_id = $id;
        $lead_follow_up->lead_response = $lead_response;
        $lead_follow_up->follow_up_date = $follow_up_date;
        $lead_follow_up->follow_up_time = $follow_up_time;
        $lead_follow_up->call_status = $call_status;
        $lead_follow_up->call_note = $call_note;
        $result = $lead_follow_up->save();
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Lead follow up saved'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }

    function get_leadFollowups(Request $request)
    {
        $id = $request->lead_id;
        $leadId = $this->decodeBase64Id($id);
        $lead_follow_up = Leadfollowup::where('lead_id', $leadId)
            ->orderBy('created_at', 'desc')
            ->get();
        if ($lead_follow_up) {
            return response()->json([
                'status' => true,
                'lead_follow_up' => $lead_follow_up,
            ]);
        }
    }

    function get_closed_lead_list()
    {
        $user = Auth::guard('web')->user();
        $leads = Lead::where('assigned_to', $user->id)
            ->where('lead_status', '=', 'closed')
            ->get();
        $leadData = [];
        foreach ($leads as $index => $lead) {
            $encodedId = rtrim(base64_encode($lead->id), '=');
            $leadData[] = [
                'id' => $lead->id,
                'sr_no' => $index + 1,
                'lead_number' => $lead->lead_number,
                'lead_type' => ucfirst($lead->lead_type ?? '-'),
                'name' => $lead->name ?? '-',
                'phone' => $lead->phone ?? '-',
                'email' => $lead->email ?? '',
                'lead_source' => ucfirst($lead->lead_source ?? '-'),
                'lead_status' => $lead->lead_status ?? '-',
                'priority' => ucfirst($lead->priority ?? '-'),
                'follow_up_date' => $lead->follow_up_date ?? '-',
                'action' => '
                <a class="action-btn view" id = "view_user_lead"
                   href="' . route('user.view_closed_lead', $encodedId) . '">
                    <i class="fa fa-eye"></i> View
                </a>
            '
            ];
        }

        return response()->json([
            'leads' => $leadData
        ]);
    }

    public function update_lead_status(Request $request)
    {
        $decodedId = $this->decodeBase64Id($request->lead_id);
        $lead = Lead::find($decodedId);

        if (!$lead) {
            return response()->json(['status' => false, 'message' => 'Lead not found']);
        }

        if ($request->outcome != null) {
            $lead->lead_status = $request->lead_status;
            $lead->final_status = $request->outcome;
            $lead->save();
        } else {
            $lead->lead_status = $request->lead_status;
            $lead->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Lead status updated successfully'
        ]);
    }

    public function order_success(Request $request)
    {
        // Decode lead id
        $decodedId = $this->decodeBase64Id($request->lead_id);
        $userId = Auth::guard('web')->user()->id;
        // 🔹 Generate Order Number
        $lastOrder = Order::orderBy('id', 'desc')->first();

        if ($lastOrder && $lastOrder->order_number) {
            $lastNumber = (int) str_replace('ORD-', '', $lastOrder->order_number);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $orderNumber = 'ORD-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        $lastInvoice = Order::whereNotNull('invoice_id')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice && $lastInvoice->invoice_id) {
            $lastInvoiceNumber = (int) str_replace('INV-', '', $lastInvoice->invoice_id);
            $newInvoiceNumber = $lastInvoiceNumber + 1;
        } else {
            $newInvoiceNumber = 1;
        }

        $invoiceId = 'INV-' . str_pad($newInvoiceNumber, 6, '0', STR_PAD_LEFT);

        // 🔹 Create Order
        $order = new Order();
        $project = new ProjectInfo();
        $project->project_name = $request->project_name;
        $project->tech_stack = $request->tech_stack;
        $project->expected_start_date = $request->expected_start_date;
        $project->expected_delivery_date = $request->expected_delivery_date;
        $project->actual_delivery_date = $request->actual_delivery_date;
        $project->priority = $request->project_priority;
        $project->description = $request->project_description;

        $project->save();
        $projectId = $project->id;


        $order->order_number   = $orderNumber;
        $order->lead_id        = $decodedId;
        $order->user_id        = $userId;
        $order->project_id     = $projectId;
        // Order Info
        $order->invoice_id     = $invoiceId;
        $order->invoice_date   = now();
        $order->order_status   = $request->order_status ?? 'new';

        // Amount Details
        $order->sub_total      = $request->sub_total;
        $order->discount       = $request->discount ?? 0;
        $order->gst            = $request->gst ?? 0;
        $order->total_amount   = $request->total_amount;
        $order->net_amount     = $request->net_amount;
        $order->due_amount     = $request->due_amount;

        // Payment Details
        $order->payment_terms  = $request->payment_terms;
        $order->payment_mode   = $request->payment_mode;
        $order->paid_amount    = $request->paid_amount ?? 0;
        $order->payment_status = $request->payment_status ?? 'pending';

        // Optional defaults
        $order->currency = $request->currency;
        $result = $order->save();

        if ($request->paid_amount > 0) {
            $payment_detail = new PaymentDetails();
            $payment_detail->order_id = $order->id;
            $payment_detail->payment_mode = $request->payment_mode;
            $payment_detail->payment_date = $request->payment_date;
            $payment_detail->paid_amount = $request->paid_amount;
            $payment_detail->save();
        }


        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Something went wrong while saving order'
        ]);
    }
}
