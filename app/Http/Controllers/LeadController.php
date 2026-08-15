<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\MasterValue;
use App\Models\User;
use App\Services\LeadNumberService;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LeadController extends Controller
{
    //
    public function lead()
    {
        return view('admin.lead.lead');
    }

    public function add_lead_view()
    {
        return view('admin.lead.add_lead');
    }

    public function edit_lead_view()
    {
        return view('admin.lead.edit_lead');
    }

    public function get_lead(Request $request)
    {
        $me = Auth::guard('web')->user();

        // Elevated roles see every lead in the tenant; everyone else sees
        // only leads assigned to them — unified interface data scoping.
        $baseQuery = $me->hasElevatedAccess() ? Lead::query() : Lead::where('assigned_to', $me->id);

        // Active/Converted tab counts, scoped the same way as the main
        // query but computed before search/filter narrowing so the tab
        // badges reflect the whole tab, not just the current search result.
        $counts = [
            'active' => (clone $baseQuery)->whereDoesntHave('deal')->count(),
            'converted' => (clone $baseQuery)->whereHas('deal')->count(),
        ];

        $converted = $request->boolean('converted');
        $query = (clone $baseQuery)->{$converted ? 'whereHas' : 'whereDoesntHave'}('deal');

        if ($request->filled('search')) {
            $term = '%'.$request->string('search').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('company_name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }
        foreach (['lead_status', 'priority', 'lead_source'] as $filterField) {
            if ($request->filled($filterField)) {
                $query->where($filterField, $request->string($filterField));
            }
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->integer('assigned_to'));
        }

        $perPage = min(max((int) $request->input('per_page', 20), 5), 100);
        $paginator = $query->with('deal:id,lead_id,name')->orderByDesc('id')->paginate($perPage);

        $data = [];
        foreach ($paginator->items() as $lead) {
            $editUrl = route('leads.edit', $lead->id);
            $dealUrl = $lead->deal ? route('deals.show', $lead->deal->id) : null;
            $action = "
                <div class='action-stack'>
                    <a href='{$editUrl}'
                        class='btn btn-sm btn-success action-status editbtn'
                        '>
                        <i class='fa fa-check-circle'></i> Edit
                    </a>";

            if ($dealUrl) {
                $action .= "
                    <a href='{$dealUrl}'
                        class='btn btn-sm btn-primary action-status'>
                        <i class='fa fa-briefcase'></i> View Deal
                    </a>";
            }

            $action .= "
                    <button
                        class='btn btn-sm btn-danger action-delete delete_data'
                        data-id='{$lead->id}'
                        data-toggle='modal'
                        data-target='#deleteConfirmModal'>
                        <i class='fa fa-trash'></i> Delete
                    </button>
                </div>";
            $user = User::find($lead->assigned_to);

            if ($user) {
                $user_name = $user->name;
            } else {
                $user_name = null;
            }

            $data[] = [

                /* ================= BASIC ================= */
                'sl_no' => $lead->lead_number,
                'id' => $lead->id,

                /* ================= LEAD INFO ================= */
                'lead_type' => $lead->lead_type,
                'lead_source' => $lead->lead_source,
                'company_name' => $lead->company_name,
                'gst_no' => $lead->gst_no,
                'name' => $lead->name,
                'phone' => $lead->phone,
                'alternate_phone' => $lead->alternate_phone,
                'email' => $lead->email,

                /* ================= LOCATION ================= */
                'city' => $lead->city,
                'state' => $lead->state,
                'country' => $lead->country,

                /* ================= PRODUCT / SERVICE ================= */
                'product' => $lead->product,
                'service' => $lead->service,
                'budget' => $lead->budget,
                'requirement' => $lead->requirement,

                /* ================= STATUS & PRIORITY ================= */
                'lead_status' => $lead->lead_status,
                'priority' => $lead->priority,
                'status_reason' => $lead->status_reason,

                /* ================= FOLLOW UP ================= */
                'follow_up_date' => $lead->follow_up_date,
                'follow_up_time' => $lead->follow_up_time,
                'follow_up_note' => $lead->follow_up_note,

                /* ================= ASSIGNMENT ================= */
                'assigned_to' => $user_name,
                'assigned_by' => $lead->assigned_by,
                'assigned_at' => $lead->assigned_at,

                /* ================= CONTACT TRACKING ================= */
                'last_contacted_at' => $lead->last_contacted_at,
                'last_contacted_by' => $lead->last_contacted_by,

                /* ================= CONVERSION ================= */
                'is_converted' => $lead->is_converted,
                'converted_at' => $lead->converted_at,
                'conversion_value' => $lead->conversion_value,
                'deal_id' => $lead->deal->id ?? null,

                /* ================= NOTES ================= */
                'remarks' => $lead->remarks,
                'internal_note' => $lead->internal_note,

                /* ================= UI ================= */
                'status' => $lead->status,   // Active / Inactive (system)
                'action' => $action,           // Edit / Delete / View buttons
            ];
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
            ],
            'counts' => $counts,
        ]);
    }

    public function toggleLeadStatus(Request $request)
    {
        $lead = $this->findEditableLead($request->integer('id'));
        $lead->status = $lead->status === 'Active' ? 'Inactive' : 'Active';
        $result = $lead->update();

        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Status updated successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }

    public function updateLead(Request $request)
    {
        $lead = $this->findEditableLead($request->integer('id'));
        $previousStatus = $lead->lead_status;
        $lead->lead_status = $request->lead_status;
        if ($request->filled('status_reason')) {
            $lead->status_reason = $request->status_reason;
        }
        $result = $lead->update();

        if ($result && $previousStatus !== $lead->lead_status) {
            LeadActivity::create([
                'tenant_id' => $lead->tenant_id,
                'lead_id' => $lead->id,
                'user_id' => Auth::guard('web')->id(),
                'type' => $lead->lead_status === 'not_interested' ? 'lost' : 'status_changed',
                'description' => "Status changed from \"{$previousStatus}\" to \"{$lead->lead_status}\""
                    .($lead->status_reason ? " — {$lead->status_reason}" : ''),
            ]);
        }

        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Lead Status updated successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }

    public function add_lead(Request $request)
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant before creating a lead.');

        $rules = [

            // ================= BASIC =================
            'lead_type' => [
                'required',
                Rule::in(MasterValue::options('lead_type')->pluck('code')),
            ],

            'lead_source' => [
                'required',
                'string',
                'max:255',
                Rule::in(MasterValue::options('lead_source')->pluck('code')),
            ],
            'company_name' => [
                'required',
                'string',
                'max:255',
            ],
            'gst_no' => [
                'required',
                'string',
                'max:255',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            // ================= LOCATION =================
            'city' => [
                'nullable',
                'string',
                'max:255',
            ],

            'state' => [
                'nullable',
                'string',
                'max:255',
            ],

            'country' => [
                'nullable',
                'string',
                'max:255',
            ],

            // ================= PRODUCT / SERVICE =================
            'product' => [
                'nullable',
                'string',
                'max:255',
            ],

            'service' => [
                'nullable',
                'string',
                'max:255',
            ],

            'budget' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'requirement' => [
                'nullable',
                'string',
            ],

            // ================= STATUS =================
            'lead_status' => [
                'required',
                Rule::in(MasterValue::options('lead_status')->pluck('code')),
            ],

            'priority' => [
                'required',
                Rule::in(MasterValue::options('lead_priority')->pluck('code')),
            ],

            'status_reason' => [
                'nullable',
                'string',
                'max:255',
            ],

            // ================= FOLLOW UP =================
            'follow_up_date' => [
                'nullable',
                'date',
            ],

            'follow_up_time' => [
                'nullable',
            ],

            'follow_up_note' => [
                'nullable',
                'string',
                'max:255',
            ],

            // ================= ASSIGNMENT =================
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('tenant_id', $tenantId),
            ],

            'assigned_by' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('tenant_id', $tenantId),
            ],

            'assigned_at' => [
                'nullable',
                'date',
            ],

            // ================= CONTACT TRACKING =================
            'last_contacted_at' => [
                'nullable',
            ],

            'last_contacted_by' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('tenant_id', $tenantId),
            ],

            // ================= CONVERSION =================
            'is_converted' => [
                'nullable',
            ],

            'converted_at' => [
                'nullable',
            ],

            'conversion_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            // ================= NOTES =================
            'internal_note' => [
                'nullable',
            ],

            'customer_name' => [
                'required',
            ],

            'customer_phone' => [
                'required',
            ],

            'customer_email' => [
                'required',
            ],

            'designation' => [
                'required',
            ],

            'customer_budget' => [
                'required',
            ],

            'customer_city' => [
                'required',
            ],

        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }
        $lead = new Lead();
        // A platform Super Admin has no tenant context (TenantContext::id()
        // is null for them), so BelongsToTenant's creating hook can't infer
        // one — without this, leads they create would get tenant_id=null and
        // become invisible to the tenant's own sales team. Same fallback
        // pattern as Admin\UserController::add_user.
        $lead->tenant_id = $tenantId;
        $lead->lead_type = $request->lead_type;
        $lead->lead_source = $request->lead_source;
        $lead->company_name = $request->company_name;
        $lead->gst_no = $request->gst_no;
        $lead->name = $request->name;
        $lead->phone = $request->phone;
        $lead->alternate_phone = $request->alternate_phone;
        $lead->email = $request->email;
        $lead->city = $request->city;
        $lead->state = $request->state;
        $lead->country = $request->country;
        $lead->product = $request->product;
        $lead->service = $request->service;
        $lead->budget = $request->budget;
        $lead->lead_status = $request->lead_status;
        $lead->priority = $request->priority;
        $lead->status_reason = $request->status_reason;
        $lead->follow_up_date = $request->follow_up_date;
        $lead->follow_up_time = $request->follow_up_time;
        $lead->follow_up_note = $request->follow_up_note;
        $lead->requirement = $request->requirement;

        // Auto-assignment: if nobody was explicitly picked, hand the lead to
        // whichever active sales-role user in this tenant currently has the
        // fewest assigned leads, instead of leaving it unassigned.
        $wasAutoAssigned = false;
        $assignedTo = $request->assigned_to;
        if (empty($assignedTo)) {
            $assignedTo = $this->leastLoadedSalesUserId($lead->tenant_id);
            $wasAutoAssigned = (bool) $assignedTo;
        }

        $lead->assigned_to = $assignedTo;
        $lead->assigned_by = $request->assigned_by ?: Auth::guard('web')->id();
        $lead->assigned_at = $assignedTo ? ($request->assigned_at ?: now()) : null;
        $lead->last_contacted_at = $request->last_contacted_at;
        $lead->last_contacted_by = $request->last_contacted_by;
        // Conversion state is controlled only by the lead-to-deal workflow.
        // A newly entered lead must never arrive pre-converted from form data.
        $lead->is_converted = 'No';
        $lead->converted_at = null;
        $lead->conversion_value = null;
        $lead->remarks = $request->remarks;
        $lead->internal_note = $request->internal_note;
        LeadNumberService::saveNew($lead);
        $result = true;

        LeadActivity::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'user_id' => Auth::guard('web')->id(),
            'type' => 'created',
            'description' => 'Lead created',
        ]);

        if ($assignedTo) {
            LeadActivity::create([
                'tenant_id' => $lead->tenant_id,
                'lead_id' => $lead->id,
                'user_id' => Auth::guard('web')->id(),
                'type' => 'assigned',
                'description' => $wasAutoAssigned
                    ? 'Auto-assigned to '.optional(User::find($assignedTo))->name
                    : 'Assigned to '.optional(User::find($assignedTo))->name,
            ]);
        }

        $company = Company::firstOrCreate([
            'tenant_id' => $lead->tenant_id,
            'name' => $lead->company_name,
        ], [
            'owner_id' => $assignedTo,
            'gst_number' => $lead->gst_no,
            'city' => $lead->city,
            'state' => $lead->state,
            'country' => $lead->country,
            'status' => 'prospect',
        ]);

        $contact = Contact::create([
            'tenant_id' => $lead->tenant_id,
            'company_id' => $company->id,
            'owner_id' => $assignedTo,
            'name' => $request->customer_name,
            'phone' => $request->customer_phone,
            'email' => $request->customer_email,
            'designation' => $request->designation,
            'city' => $request->customer_city,
            'is_primary' => true,
            'source' => 'lead',
            'status' => 'active',
        ]);

        $lead->company_id = $company->id;
        $lead->contact_id = $contact->id;
        $customerResult = $lead->save();

        if ($result && $customerResult) {
            return response()->json([
                'status' => true,
                'message' => 'Lead added succuessfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }

    /**
     * The current tenant's active Sales Executive/Sales Manager with the
     * fewest currently-assigned active leads, for auto-assignment on create.
     * Returns null if there's nobody eligible (e.g. a brand-new tenant).
     */
    private function leastLoadedSalesUserId(?int $tenantId): ?int
    {
        if ($tenantId === null) {
            return null;
        }

        $candidate = User::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'Active')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Sales Executive', 'Sales Manager']))
            ->withCount(['leads' => fn ($q) => $q->where('status', 'Active')])
            ->orderBy('leads_count')
            ->first();

        return $candidate?->id;
    }

    public function get_edit_lead_data(Request $request)
    {

        // Validate request
        if (! $request->id) {
            return response()->json([
                'status' => false,
                'message' => 'Lead ID is required',
            ], 400);
        }

        // Fetch lead
        $lead = $this->findEditableLead($request->integer('id'));

        // Prepare response data (NOT missing a single field)
        $data = [
            'id' => $lead->id,
            'lead_type' => $lead->lead_type,
            'lead_source' => $lead->lead_source,
            'company_name' => $lead->company_name,
            'gst_no' => $lead->gst_no,
            'name' => $lead->name,
            'phone' => $lead->phone,
            'alternate_phone' => $lead->alternate_phone,
            'email' => $lead->email,

            'city' => $lead->city,
            'state' => $lead->state,
            'country' => $lead->country,

            'product' => $lead->product,
            'service' => $lead->service,
            'budget' => $lead->budget,
            'requirement' => $lead->requirement,

            'lead_status' => $lead->lead_status,
            'priority' => $lead->priority,
            'status_reason' => $lead->status_reason,
            'status' => $lead->status,

            'follow_up_date' => $lead->follow_up_date,
            'follow_up_time' => $lead->follow_up_time,
            'follow_up_note' => $lead->follow_up_note,

            'assigned_to' => $lead->assigned_to,
            'assigned_by' => $lead->assigned_by,
            'assigned_at' => $lead->assigned_at,

            'last_contacted_at' => $lead->last_contacted_at,
            'last_contacted_by' => $lead->last_contacted_by,

            'is_converted' => $lead->is_converted,
            'converted_at' => $lead->converted_at,
            'conversion_value' => $lead->conversion_value,

            'remarks' => $lead->remarks,
            'internal_note' => $lead->internal_note,
        ];

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function edit_lead_data(Request $request)
    {
        $lead = $this->findEditableLead($request->integer('id'));
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'alternate_phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'conversion_value' => ['nullable', 'numeric', 'min:0'],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $lead->tenant_id)],
            'assigned_by' => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $lead->tenant_id)],
            'last_contacted_by' => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $lead->tenant_id)],
        ]);
        $previousStatus = $lead->lead_status;
        $previousAssignee = $lead->assigned_to;

        $lead->lead_type = $request->lead_type;
        $lead->lead_source = $request->lead_source;

        // ================Company Details ============
        $lead->company_name = $request->company_name;
        $lead->gst_no = $request->gst_no;

        // ================= CONTACT =================
        $lead->name = $request->name;
        $lead->phone = $request->phone;
        $lead->alternate_phone = $request->alternate_phone;
        $lead->email = $request->email;

        // ================= LOCATION =================
        $lead->city = $request->city;
        $lead->state = $request->state;
        $lead->country = $request->country;

        // ================= PRODUCT / SERVICE =================
        $lead->product = $request->product;
        $lead->service = $request->service;
        $lead->budget = $request->budget;
        $lead->requirement = $request->requirement;

        // ================= STATUS =================
        $lead->lead_status = $request->lead_status;
        $lead->priority = $request->priority;
        $lead->status_reason = $request->status_reason;

        // ================= FOLLOW UP =================
        $lead->follow_up_date = $request->follow_up_date;
        $lead->follow_up_time = $request->follow_up_time;
        $lead->follow_up_note = $request->follow_up_note;

        // ================= ASSIGNMENT =================
        $lead->assigned_to = $request->assigned_to;
        $lead->assigned_by = $request->assigned_by;
        $lead->assigned_at = $request->assigned_at;

        // ================= CONTACT TRACKING =================
        $lead->last_contacted_at = $request->last_contacted_at;
        $lead->last_contacted_by = $request->last_contacted_by;

        // Conversion fields are intentionally unchanged here. Only the
        // transactional lead-to-deal workflow may change conversion state.

        // ================= NOTES =================
        $lead->remarks = $request->remarks;
        $lead->internal_note = $request->internal_note;

        // ================= SAVE =================
        $result = $lead->update();

        if ($result && $lead->company_name) {
            $company = Company::firstOrCreate([
                'tenant_id' => $lead->tenant_id,
                'name' => $lead->company_name,
            ], [
                'owner_id' => $lead->assigned_to,
                'gst_number' => $lead->gst_no,
                'city' => $lead->city,
                'state' => $lead->state,
                'country' => $lead->country,
                'status' => 'prospect',
            ]);
            $lead->company_id = $company->id;
            $lead->save();
        }

        if ($result) {
            if ($previousStatus !== $lead->lead_status) {
                LeadActivity::create([
                    'tenant_id' => $lead->tenant_id,
                    'lead_id' => $lead->id,
                    'user_id' => Auth::guard('web')->id(),
                    'type' => 'status_changed',
                    'description' => "Status changed from \"{$previousStatus}\" to \"{$lead->lead_status}\"",
                ]);
            }

            if ($previousAssignee != $lead->assigned_to && $lead->assigned_to) {
                LeadActivity::create([
                    'tenant_id' => $lead->tenant_id,
                    'lead_id' => $lead->id,
                    'user_id' => Auth::guard('web')->id(),
                    'type' => 'assigned',
                    'description' => 'Reassigned to '.optional(User::find($lead->assigned_to))->name,
                ]);
            }
        }

        // ================= RESPONSE =================
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Lead updated successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }

    public function delete_lead_data(Request $request)
    {
        $lead = $this->findEditableLead($request->integer('id'));
        $result = $lead->delete();
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Lead deleted Successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }

    public function leads_import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant before importing leads.');

        Excel::import(new class($tenantId) implements ToCollection, WithHeadingRow
        {
            public function __construct(private readonly int $tenantId) {}

            public function collection(Collection $rows)
            {
                foreach ($rows as $row) {
                    $lead = new Lead();
                    $lead->tenant_id = $this->tenantId;
                    $lead->lead_type = $row['lead_type'] ?? 'inquiry';
                    $lead->lead_source = $row['lead_source'] ?? null;
                    $lead->name = $row['name'] ?? null;
                    $lead->phone = $row['phone'] ?? null;
                    $lead->alternate_phone = $row['alternate_phone'] ?? null;
                    $lead->email = $row['email'] ?? null;
                    $lead->city = $row['city'] ?? null;
                    $lead->state = $row['state'] ?? null;
                    $lead->country = $row['country'] ?? null;
                    $lead->product = $row['product'] ?? null;
                    $lead->service = $row['service'] ?? null;
                    $lead->budget = $row['budget'] ?? null;
                    $lead->lead_status = $row['lead_status'] ?? 'new';
                    $lead->priority = $row['priority'] ?? 'high';
                    $lead->status_reason = $row['status_reason'] ?? null;

                    if (! empty($row['follow_up_date'])) {

                        if (is_numeric($row['follow_up_date'])) {

                            $lead->follow_up_date = Date::excelToDateTimeObject(
                                $row['follow_up_date']
                            )->format('Y-m-d');
                        } else {

                            $lead->follow_up_date = Carbon::createFromFormat(
                                'd-m-Y',
                                $row['follow_up_date']
                            )->format('Y-m-d');
                        }
                    }

                    // ===== TIME =====
                    if (! empty($row['follow_up_time'])) {

                        if (is_numeric($row['follow_up_time'])) {
                            // Excel time comes as decimal (like 0.5 = 12:00 PM)
                            $lead->follow_up_time = Carbon::instance(
                                Date::excelToDateTimeObject($row['follow_up_time'])
                            )->format('H:i:s');
                        } else {
                            $lead->follow_up_time = Carbon::parse($row['follow_up_time'])
                                ->format('H:i:s');
                        }
                    }
                    $lead->follow_up_note = $row['follow_up_note'] ?? null;
                    $lead->requirement = $row['requirement'] ?? null;
                    $lead->assigned_to = $this->tenantUserId($row['assigned_to'] ?? null);
                    $lead->assigned_by = $this->tenantUserId($row['assigned_by'] ?? null);
                    $lead->assigned_at = $row['assigned_at'] ?? null;
                    $lead->last_contacted_at = $row['last_contacted_at'] ?? null;
                    $lead->last_contacted_by = $this->tenantUserId($row['last_contacted_by'] ?? null);
                    $lead->is_converted = 'No';
                    $lead->converted_at = null;
                    $lead->conversion_value = null;
                    $lead->remarks = $row['remarks'] ?? null;
                    $lead->internal_note = $row['internal_note'] ?? null;
                    $lead->status = $row['status'] ?? 'Active';
                    LeadNumberService::saveNew($lead);
                }
            }

            private function tenantUserId($id): ?int
            {
                if (! $id) {
                    return null;
                }

                return User::where('tenant_id', $this->tenantId)->whereKey($id)->value('id');
            }
        }, $request->file('file'));

        return response()->json([
            'status' => true,
            'message' => 'Leads imported successfully',
        ]);
    }

    public function downloadFormat()
    {
        $filePath = public_path('lead-format/lead_format.xlsx');

        if (! file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download(
            $filePath,
            'leads_upload_format.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    public function getAssignUsers()
    {
        $tenantId = TenantContext::id();

        $users = User::where('status', 'Active')
            ->when($tenantId !== null, fn ($q) => $q->where('tenant_id', $tenantId))
            ->get();

        return response()->json([
            'users' => $users,
        ]);
    }

    public function assignLead(Request $request)
    {
        $request->validate(['lead_id' => 'required|integer', 'user_id' => 'required|integer']);
        $lead = Lead::findOrFail($request->integer('lead_id'));
        $user = User::where('tenant_id', $lead->tenant_id)->findOrFail($request->integer('user_id'));
        $lead->assigned_to = $user->id;
        $lead->assigned_by = Auth::guard('web')->id();
        $lead->assigned_at = now();
        $result = $lead->save();
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Assigned User Successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }

    public function bulkAssignLead(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'integer',
        ]);

        $leads = Lead::whereIn('id', $request->lead_ids)->get();
        abort_if($leads->count() !== count(array_unique($request->lead_ids)), 422, 'One or more leads are invalid.');
        $tenantIds = $leads->pluck('tenant_id')->unique();
        abort_if($tenantIds->count() !== 1, 422, 'Leads from different tenants cannot be assigned together.');
        $user = User::where('tenant_id', $tenantIds->first())->findOrFail($request->integer('user_id'));

        foreach ($leads as $lead) {
            $lead->assigned_to = $user->id;
            $lead->assigned_by = Auth::guard('web')->id();
            $lead->assigned_at = now();
            $lead->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Leads assigned successfully',
        ]);
    }

    private function findEditableLead(int $id): Lead
    {
        $user = Auth::guard('web')->user();
        $query = Lead::query();

        if (! $user->hasElevatedAccess()) {
            $query->where('assigned_to', $user->id);
        }

        return $query->findOrFail($id);
    }
}
