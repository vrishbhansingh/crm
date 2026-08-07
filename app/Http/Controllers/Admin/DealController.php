<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealStageHistory;
use App\Models\Lead;
use App\Models\MasterValue;
use App\Models\Order;
use App\Models\PaymentDetails;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\ProjectInfo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Backs the Kanban board + table alt-view for Deals (Phase 5). A Deal is the
 * pipeline-tracked middle stage between a Lead and an Order: leads convert
 * into a Deal (LeadDetailController::convertToDeal), and an Order is only
 * created once a deal reaches a Won stage (see moveStage/createOrderFromDeal
 * below). Data scope (all tenant deals vs only-my-deals) is decided here via
 * hasElevatedAccess(), same as Leads/Orders.
 */
class DealController extends Controller
{
    public function index()
    {
        Pipeline::ensureDefaultForTenant(Auth::guard('web')->user()->tenant_id);

        return view('deals.index');
    }

    public function listView()
    {
        Pipeline::ensureDefaultForTenant(Auth::guard('web')->user()->tenant_id);

        return view('deals.list');
    }

    public function data(Request $request)
    {
        $me = Auth::guard('web')->user();
        $query = $me->hasElevatedAccess() ? Deal::query() : Deal::where('owner_id', $me->id);

        $deals = $query->with(['stage', 'owner:id,name', 'lead:id,name'])->orderBy('id', 'desc')->get();

        $data = [];
        $sl_no = 1;
        foreach ($deals as $deal) {
            $viewUrl = route('deals.show', $deal->id);
            $action = "<a href='{$viewUrl}' class='btn btn-sm btn-info'><i class='fa fa-eye'></i> View</a>";

            $data[] = [
                'sl_no' => $sl_no++,
                'id' => $deal->id,
                'name' => $deal->name,
                'amount' => $deal->amount,
                'currency' => $deal->currency,
                'stage_name' => $deal->stage->name ?? '-',
                'stage_color' => $deal->stage->color ?? null,
                'owner_name' => $deal->owner->name ?? null,
                'lead_id' => $deal->lead_id,
                'lead_name' => $deal->lead->name ?? null,
                'expected_close_date' => $deal->expected_close_date,
                'status' => $deal->status,
                'action' => $action,
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function boardData(Request $request)
    {
        $me = Auth::guard('web')->user();
        $tenantId = $me->tenant_id;

        $pipeline = $request->filled('pipeline_id')
            ? Pipeline::findOrFail($request->pipeline_id)
            : Pipeline::ensureDefaultForTenant($tenantId);
        $pipeline->load('stages');

        $query = $me->hasElevatedAccess() ? Deal::query() : Deal::where('owner_id', $me->id);
        $deals = $query->where('pipeline_id', $pipeline->id)
            ->with(['owner:id,name', 'lead:id,name'])
            ->get();

        $stages = $pipeline->stages->map(function ($stage) use ($deals) {
            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'sort_order' => $stage->sort_order,
                'is_won' => $stage->is_won,
                'is_lost' => $stage->is_lost,
                'color' => $stage->color,
                'deals' => $deals->where('stage_id', $stage->id)->values()->map(fn ($deal) => [
                    'id' => $deal->id,
                    'name' => $deal->name,
                    'amount' => $deal->amount,
                    'currency' => $deal->currency,
                    'owner_name' => $deal->owner->name ?? null,
                    'expected_close_date' => $deal->expected_close_date,
                    'lead_id' => $deal->lead_id,
                ]),
            ];
        });

        $pipelines = Pipeline::where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'is_default']);

        return response()->json([
            'status' => true,
            'data' => [
                'pipeline_id' => $pipeline->id,
                'pipelines' => $pipelines,
                'stages' => $stages,
            ],
        ]);
    }

    public function create()
    {
        $tenantId = Auth::guard('web')->user()->tenant_id;
        Pipeline::ensureDefaultForTenant($tenantId);

        $pipelines = Pipeline::with('stages')->where('is_active', true)->orderBy('sort_order')->get();
        $users = User::where('status', 'Active')->when($tenantId !== null, fn ($q) => $q->where('tenant_id', $tenantId))->get(['id', 'name']);
        $currencies = MasterValue::options('currency');
        $leads = Lead::orderBy('id', 'desc')->limit(200)->get(['id', 'name', 'company_name']);

        return view('deals.create', compact('pipelines', 'users', 'currencies', 'leads'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'pipeline_id' => 'required|exists:pipelines,id',
            'stage_id' => ['required', Rule::exists('pipeline_stages', 'id')->where('pipeline_id', $request->pipeline_id)],
            'amount' => 'nullable|numeric',
            'currency' => 'nullable|string|max:10',
            'owner_id' => 'nullable|exists:users,id',
            'lead_id' => 'nullable|exists:leads,id',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $deal = Deal::create([
            'pipeline_id' => $request->pipeline_id,
            'stage_id' => $request->stage_id,
            'lead_id' => $request->lead_id,
            'owner_id' => $request->owner_id,
            'created_by' => Auth::guard('web')->id(),
            'name' => $request->name,
            'amount' => $request->amount ?? 0,
            'currency' => $request->currency,
            'expected_close_date' => $request->expected_close_date,
            'notes' => $request->notes,
        ]);

        DealStageHistory::create([
            'deal_id' => $deal->id,
            'from_stage_id' => null,
            'to_stage_id' => $deal->stage_id,
            'changed_by' => Auth::guard('web')->id(),
        ]);

        return response()->json(['status' => true, 'message' => 'Deal created successfully', 'id' => $deal->id]);
    }

    public function edit($id)
    {
        $deal = Deal::findOrFail($id);
        $tenantId = Auth::guard('web')->user()->tenant_id;

        $pipelines = Pipeline::with('stages')->where('is_active', true)->orderBy('sort_order')->get();
        $users = User::where('status', 'Active')->when($tenantId !== null, fn ($q) => $q->where('tenant_id', $tenantId))->get(['id', 'name']);
        $currencies = MasterValue::options('currency');
        $leads = Lead::orderBy('id', 'desc')->limit(200)->get(['id', 'name', 'company_name']);

        return view('deals.create', compact('deal', 'pipelines', 'users', 'currencies', 'leads'));
    }

    public function update(Request $request, $id)
    {
        $deal = Deal::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'nullable|numeric',
            'currency' => 'nullable|string|max:10',
            'owner_id' => 'nullable|exists:users,id',
            'lead_id' => 'nullable|exists:leads,id',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $deal->fill($request->only(['name', 'amount', 'currency', 'owner_id', 'lead_id', 'expected_close_date', 'notes']));
        $deal->save();

        return response()->json(['status' => true, 'message' => 'Deal updated successfully']);
    }

    public function destroy($id)
    {
        $deal = Deal::findOrFail($id);
        $deal->delete();

        return response()->json(['status' => true, 'message' => 'Deal deleted successfully']);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'deal_id' => 'required|exists:deals,id',
            'owner_id' => 'required|exists:users,id',
        ]);

        $deal = Deal::findOrFail($request->deal_id);
        $deal->owner_id = $request->owner_id;
        $deal->save();

        return response()->json(['status' => true, 'message' => 'Deal assigned successfully']);
    }

    /**
     * Moves a deal between stages (Kanban drag-and-drop, or the Deal Detail
     * page's dropdown fallback), records history, and — the moment a deal
     * lands on a Won stage — creates the actual Order. This is the new home
     * for the Order-creation logic that used to live in
     * LeadDetailController::convertToOrder (pre-Phase-5, Lead -> Order
     * directly); now it's Lead -> Deal -> Order, triggered here instead.
     */
    public function moveStage(Request $request, $id)
    {
        $deal = Deal::findOrFail($id);

        $request->validate([
            'to_stage_id' => ['required', Rule::exists('pipeline_stages', 'id')->where('pipeline_id', $deal->pipeline_id)],
        ]);

        $toStage = PipelineStage::findOrFail($request->to_stage_id);

        if ($toStage->is_lost) {
            $request->validate(['lost_reason_id' => 'required|exists:master_values,id']);
        }

        if ($toStage->is_won) {
            $request->validate([
                'paid_amount' => 'nullable|numeric|min:0',
                'payment_mode' => 'nullable|string|max:50',
                'payment_date' => 'nullable|date',
            ]);
        }

        DealStageHistory::create([
            'deal_id' => $deal->id,
            'from_stage_id' => $deal->stage_id,
            'to_stage_id' => $toStage->id,
            'changed_by' => Auth::guard('web')->id(),
        ]);

        $deal->stage_id = $toStage->id;

        if ($toStage->is_won) {
            $order = $this->createOrderFromDeal($deal, $request->only(['paid_amount', 'payment_mode', 'payment_date']));
            $deal->order_id = $order->id;
            $deal->status = 'won';
            $deal->closed_at = now();
            $deal->lost_reason_id = null;
        } elseif ($toStage->is_lost) {
            $deal->status = 'lost';
            $deal->closed_at = now();
            $deal->lost_reason_id = $request->lost_reason_id;
        } else {
            // Normal open stage — clear any closed-state fields left over
            // from moving backward out of a Won/Lost stage.
            $deal->status = 'open';
            $deal->closed_at = null;
            $deal->lost_reason_id = null;
        }

        $deal->save();

        return response()->json(['status' => true, 'message' => 'Deal stage updated', 'data' => $deal->fresh(['stage', 'order'])]);
    }

    /**
     * Same order-number/invoice-id generation and Order/ProjectInfo field
     * mapping as the old LeadDetailController::convertToOrder, sourced from
     * the Deal (there's no order form at drag-and-drop time) instead of a
     * request payload. An optional initial payment (captured by the
     * move-to-Won modal) is recorded the same way convertToOrder used to —
     * a PaymentDetails row plus the matching paid/due/status fields on the
     * Order — rather than always leaving the order fully unpaid.
     */
    private function createOrderFromDeal(Deal $deal, array $payment = []): Order
    {
        $lastOrder = Order::orderBy('id', 'desc')->first();
        $nextNumber = $lastOrder && $lastOrder->order_number
            ? ((int) str_replace('ORD-', '', $lastOrder->order_number)) + 1
            : 1;
        $orderNumber = 'ORD-'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        $lastInvoice = Order::whereNotNull('invoice_id')->orderBy('id', 'desc')->first();
        $nextInvoiceNumber = $lastInvoice && $lastInvoice->invoice_id
            ? ((int) str_replace('INV-', '', $lastInvoice->invoice_id)) + 1
            : 1;
        $invoiceId = 'INV-'.str_pad($nextInvoiceNumber, 6, '0', STR_PAD_LEFT);

        $project = ProjectInfo::create([
            'tenant_id' => $deal->tenant_id,
            'project_name' => $deal->name,
            'expected_delivery_date' => $deal->expected_close_date,
            'description' => $deal->notes,
        ]);

        $paidAmount = (float) ($payment['paid_amount'] ?? 0);
        $paidAmount = min($paidAmount, (float) $deal->amount);
        $dueAmount = max((float) $deal->amount - $paidAmount, 0);
        $paymentStatus = $paidAmount <= 0 ? 'pending' : ($dueAmount <= 0 ? 'paid' : 'partial');

        $order = Order::create([
            'tenant_id' => $deal->tenant_id,
            'order_number' => $orderNumber,
            'lead_id' => $deal->lead_id,
            'user_id' => $deal->owner_id ?? Auth::guard('web')->id(),
            'project_id' => $project->id,
            'invoice_id' => $invoiceId,
            'invoice_date' => now(),
            'order_status' => 'new',
            'sub_total' => $deal->amount,
            'discount' => 0,
            'gst' => 0,
            'total_amount' => $deal->amount,
            'net_amount' => $deal->amount,
            'due_amount' => $dueAmount,
            'paid_amount' => $paidAmount,
            'payment_status' => $paymentStatus,
            'currency' => $deal->currency,
        ]);

        if ($paidAmount > 0) {
            PaymentDetails::create([
                'tenant_id' => $deal->tenant_id,
                'order_id' => $order->id,
                'payment_mode' => $payment['payment_mode'] ?? null,
                'payment_date' => $payment['payment_date'] ?? now(),
                'paid_amount' => $paidAmount,
            ]);
        }

        return $order;
    }
}
