<?php

namespace App\Http\Controllers;

use App\Models\CustomerContact;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\MasterValue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Storage;
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
        $query = $me->hasElevatedAccess() ? Lead::query() : Lead::where('assigned_to', $me->id);

        if ($request->status === 'closed') {
            $query->where('lead_status', 'closed');
        } elseif (! $me->hasElevatedAccess()) {
            // Individual contributors default to their open leads (matches
            // the old user.lead_list behavior); elevated roles see everything
            // mixed together by default (matches the old admin.lead behavior).
            $query->where('lead_status', '!=', 'closed');
        }

        $leads = $query->get();
        $data = [];
        $sl_no = 1;
        foreach ($leads as $lead) {
            $editUrl = route('leads.edit', $lead->id);
            $viewUrl = route('leads.show', $lead->id);
            $action = "
                <div class='action-stack'>
                    <a href='{$viewUrl}'
                        class='btn btn-sm btn-info action-status'>
                        <i class='fa fa-eye'></i> View
                    </a>

                    <a href='{$editUrl}'
                        class='btn btn-sm btn-success action-status editbtn'
                        '>
                        <i class='fa fa-check-circle'></i> Edit
                    </a>

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
                'sl_no'              => $lead->lead_number,
                'id'                 => $lead->id,

                /* ================= LEAD INFO ================= */
                'lead_type'          => $lead->lead_type,
                'lead_source'        => $lead->lead_source,
                'company_name'       => $lead->company_name,
                'gst_no'             => $lead->gst_no,
                'name'               => $lead->name,
                'phone'              => $lead->phone,
                'alternate_phone'    => $lead->alternate_phone,
                'email'              => $lead->email,

                /* ================= LOCATION ================= */
                'city'               => $lead->city,
                'state'              => $lead->state,
                'country'            => $lead->country,

                /* ================= PRODUCT / SERVICE ================= */
                'product'            => $lead->product,
                'service'            => $lead->service,
                'budget'             => $lead->budget,
                'requirement'        => $lead->requirement,

                /* ================= STATUS & PRIORITY ================= */
                'lead_status'        => $lead->lead_status,
                'priority'           => $lead->priority,
                'status_reason'      => $lead->status_reason,

                /* ================= FOLLOW UP ================= */
                'follow_up_date'     => $lead->follow_up_date,
                'follow_up_time'     => $lead->follow_up_time,
                'follow_up_note'     => $lead->follow_up_note,

                /* ================= ASSIGNMENT ================= */
                'assigned_to'        => $user_name,
                'assigned_by'        => $lead->assigned_by,
                'assigned_at'        => $lead->assigned_at,

                /* ================= CONTACT TRACKING ================= */
                'last_contacted_at'  => $lead->last_contacted_at,
                'last_contacted_by'  => $lead->last_contacted_by,

                /* ================= CONVERSION ================= */
                'is_converted'       => $lead->is_converted,
                'converted_at'       => $lead->converted_at,
                'conversion_value'   => $lead->conversion_value,

                /* ================= NOTES ================= */
                'remarks'            => $lead->remarks,
                'internal_note'      => $lead->internal_note,

                /* ================= UI ================= */
                'status'             => $lead->status,   // Active / Inactive (system)
                'action'             => $action           // Edit / Delete / View buttons
            ];
        }



        return response()->json([
            'data' => $data,
        ]);
    }
    public function toggleLeadStatus(Request $request)
    {
        $lead = Lead::findOrFail($request->id);
        $lead->status = $lead->status === 'Active' ? 'Inactive' : 'Active';
        $result = $lead->update();

        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Status updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function updateLead(Request $request)
    {
        $lead = Lead::findOrFail($request->id);
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
                'message' => 'Lead Status updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }

    public function add_lead(Request $request)
    {

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
                'max:255'
            ],
            'gst_no' => [
                'required',
                'string',
                'max:255'
            ],

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'phone' => [
                'required',
                'string',
                'max:20'
            ],

            'email' => [
                'nullable',
                'email',
                'max:255'
            ],

            // ================= LOCATION =================
            'city' => [
                'nullable',
                'string',
                'max:255'
            ],

            'state' => [
                'nullable',
                'string',
                'max:255'
            ],

            'country' => [
                'nullable',
                'string',
                'max:255'
            ],

            // ================= PRODUCT / SERVICE =================
            'product' => [
                'nullable',
                'string',
                'max:255'
            ],

            'service' => [
                'nullable',
                'string',
                'max:255'
            ],

            'budget' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'requirement' => [
                'nullable',
                'string'
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
                'max:255'
            ],

            // ================= FOLLOW UP =================
            'follow_up_date' => [
                'nullable',
                'date'
            ],

            'follow_up_time' => [
                'nullable'
            ],

            'follow_up_note' => [
                'nullable',
                'string',
                'max:255'
            ],

            // ================= ASSIGNMENT =================
            'assigned_to' => [
                'nullable',
                'integer'
            ],

            'assigned_by' => [
                'nullable',
                'integer'
            ],

            'assigned_at' => [
                'nullable',
                'date'
            ],

            // ================= CONTACT TRACKING =================
            'last_contacted_at' => [
                'nullable',
            ],

            'last_contacted_by' => [
                'nullable',
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
                'min:0'
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
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        $lastLead = Lead::orderBy('id', 'desc')->first();

        if ($lastLead && $lastLead->lead_number) {
            $nextLeadNumber = $lastLead->lead_number + 1;
        } else {
            $nextLeadNumber = 1; // starting number
        }
        $lead = new Lead();
        $lead->lead_number = $nextLeadNumber;
        // A platform Super Admin has no tenant context (TenantContext::id()
        // is null for them), so BelongsToTenant's creating hook can't infer
        // one — without this, leads they create would get tenant_id=null and
        // become invisible to the tenant's own sales team. Same fallback
        // pattern as Admin\UserController::add_user.
        $lead->tenant_id = Auth::guard('web')->user()->tenant_id ?? \App\Models\Tenant::first()?->id;
        $lead->lead_type = $request->lead_type;
        $lead->lead_source = $request->lead_source;
        $lead->company_name = $request->company_name;
        $lead->gst_no = $request->gst_no;
        $lead->name = $request->name;
        $lead->phone = $request->phone;
        $lead->alternate_phone = $request->alternate_phone;
        $lead->email = $request->email;
        $lead->city  = $request->city;
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
        $lead->is_converted = $request->is_converted;
        $lead->converted_at = $request->converted_at;
        $lead->conversion_value = $request->conversion_value;
        $lead->remarks = $request->remarks;
        $lead->internal_note = $request->internal_note;
        $result = $lead->save();

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

        $customer_contact_detials = new CustomerContact();

        $customer_contact_detials->name = $request->customer_name;
        $customer_contact_detials->phone = $request->customer_phone;
        $customer_contact_detials->email = $request->customer_email;
        $customer_contact_detials->designation = $request->designation;
        $customer_contact_detials->budget = $request->customer_budget;
        $customer_contact_detials->city = $request->customer_city;
        $customer_contact_detials->lead_id = $lead->id;

        $customerResult = $customer_contact_detials->save();


        if ($result && $customerResult) {
            return response()->json([
                'status' => true,
                'message' => 'Lead added succuessfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
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
        if (!$request->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Lead ID is required'
            ], 400);
        }

        // Fetch lead
        $lead = Lead::findOrFail($request->id);

        // Prepare response data (NOT missing a single field)
        $data = [
            'id'                 => $lead->id,
            'lead_type'          => $lead->lead_type,
            'lead_source'        => $lead->lead_source,
            'company_name'       => $lead->company_name,
            'gst_no'               => $lead->gst_no,
            'name'               => $lead->name,
            'phone'              => $lead->phone,
            'alternate_phone'    => $lead->alternate_phone,
            'email'              => $lead->email,

            'city'               => $lead->city,
            'state'              => $lead->state,
            'country'            => $lead->country,

            'product'            => $lead->product,
            'service'            => $lead->service,
            'budget'             => $lead->budget,
            'requirement'        => $lead->requirement,

            'lead_status'        => $lead->lead_status,
            'priority'           => $lead->priority,
            'status_reason'      => $lead->status_reason,
            'status'             => $lead->status,

            'follow_up_date'     => $lead->follow_up_date,
            'follow_up_time'     => $lead->follow_up_time,
            'follow_up_note'     => $lead->follow_up_note,

            'assigned_to'        => $lead->assigned_to,
            'assigned_by'        => $lead->assigned_by,
            'assigned_at'        => $lead->assigned_at,

            'last_contacted_at'  => $lead->last_contacted_at,
            'last_contacted_by'  => $lead->last_contacted_by,

            'is_converted'       => $lead->is_converted,
            'converted_at'       => $lead->converted_at,
            'conversion_value'   => $lead->conversion_value,

            'remarks'            => $lead->remarks,
            'internal_note'      => $lead->internal_note,
        ];

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }

    public function edit_lead_data(Request $request)
    {
        $lead = Lead::findOrFail($request->id);
        $previousStatus = $lead->lead_status;
        $previousAssignee = $lead->assigned_to;

        $lead->lead_type       = $request->lead_type;
        $lead->lead_source     = $request->lead_source;

        // ================Company Details ============
        $lead->company_name            = $request->company_name;
        $lead->gst_no            = $request->gst_no;

        // ================= CONTACT =================
        $lead->name            = $request->name;
        $lead->phone           = $request->phone;
        $lead->alternate_phone = $request->alternate_phone;
        $lead->email           = $request->email;

        // ================= LOCATION =================
        $lead->city            = $request->city;
        $lead->state           = $request->state;
        $lead->country         = $request->country;

        // ================= PRODUCT / SERVICE =================
        $lead->product         = $request->product;
        $lead->service         = $request->service;
        $lead->budget          = $request->budget;
        $lead->requirement     = $request->requirement;

        // ================= STATUS =================
        $lead->lead_status     = $request->lead_status;
        $lead->priority        = $request->priority;
        $lead->status_reason   = $request->status_reason;

        // ================= FOLLOW UP =================
        $lead->follow_up_date  = $request->follow_up_date;
        $lead->follow_up_time  = $request->follow_up_time;
        $lead->follow_up_note  = $request->follow_up_note;

        // ================= ASSIGNMENT =================
        $lead->assigned_to     = $request->assigned_to;
        $lead->assigned_by     = $request->assigned_by;
        $lead->assigned_at     = $request->assigned_at;

        // ================= CONTACT TRACKING =================
        $lead->last_contacted_at = $request->last_contacted_at;
        $lead->last_contacted_by = $request->last_contacted_by;

        // ================= CONVERSION =================
        $lead->is_converted     = $request->is_converted;
        $lead->converted_at     = $request->converted_at;
        $lead->conversion_value = $request->conversion_value;

        // ================= NOTES =================
        $lead->remarks         = $request->remarks;
        $lead->internal_note   = $request->internal_note;

        // ================= SAVE =================
        $result =  $lead->update();

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
                'status'  => true,
                'message' => 'Lead updated successfully'
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function delete_lead_data(Request $request)
    {
        $lead = Lead::findOrFail($request->id);
        $result = $lead->delete();
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Lead deleted Successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function leads_import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new class implements ToCollection, WithHeadingRow {

            public function collection(Collection $rows)
            {
                foreach ($rows as $row) {
                    $lastLead = Lead::orderBy('id', 'desc')->first();

                    if ($lastLead && $lastLead->lead_number) {
                        $nextLeadNumber = $lastLead->lead_number + 1;
                    } else {
                        $nextLeadNumber = 1; // starting number
                    }
                    $lead = new Lead();
                    $lead->lead_number         = $nextLeadNumber;
                    $lead->lead_type           = $row['lead_type'] ?? 'inquiry';
                    $lead->lead_source         = $row['lead_source'] ?? null;
                    $lead->name                = $row['name'] ?? null;
                    $lead->phone               = $row['phone'] ?? null;
                    $lead->alternate_phone     = $row['alternate_phone'] ?? null;
                    $lead->email               = $row['email'] ?? null;
                    $lead->city                = $row['city'] ?? null;
                    $lead->state               = $row['state'] ?? null;
                    $lead->country             = $row['country'] ?? null;
                    $lead->product             = $row['product'] ?? null;
                    $lead->service             = $row['service'] ?? null;
                    $lead->budget              = $row['budget'] ?? null;
                    $lead->lead_status         = $row['lead_status'] ?? 'new';
                    $lead->priority            = $row['priority'] ?? 'high';
                    $lead->status_reason       = $row['status_reason'] ?? null;

                    if (!empty($row['follow_up_date'])) {

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
                    if (!empty($row['follow_up_time'])) {

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
                    $lead->follow_up_note      = $row['follow_up_note'] ?? null;
                    $lead->requirement         = $row['requirement'] ?? null;
                    $lead->assigned_to         = $row['assigned_to'] ?? null;
                    $lead->assigned_by         = $row['assigned_by'] ?? null;
                    $lead->assigned_at         = $row['assigned_at'] ?? null;
                    $lead->last_contacted_at   = $row['last_contacted_at'] ?? null;
                    $lead->last_contacted_by   = $row['last_contacted_by'] ?? null;
                    $lead->is_converted        = $row['is_converted'] ?? 'No';
                    $lead->converted_at        = $row['converted_at'] ?? null;
                    $lead->conversion_value    = $row['conversion_value'] ?? null;
                    $lead->remarks             = $row['remarks'] ?? null;
                    $lead->internal_note       = $row['internal_note'] ?? null;
                    $lead->status              = $row['status'] ?? 'Active';
                    $lead->save();
                }
            }
        }, $request->file('file'));

        return response()->json([
            'status' => true,
            'message' => 'Leads imported successfully'
        ]);
    }

    public function downloadFormat()
    {
        $filePath = public_path('lead-format/lead_format.xlsx');

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download(
            $filePath,
            'leads_upload_format.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]
        );
    }
    function getAssignUsers()
    {
        $tenantId = Auth::guard('web')->user()->tenant_id;

        $users = User::where('status', 'Active')
            ->when($tenantId !== null, fn ($q) => $q->where('tenant_id', $tenantId))
            ->get();

        return response()->json([
            'users' => $users
        ]);
    }
    function assignLead(Request $request)
    {
        $user_lead = Lead::find($request->lead_id);
        $user_lead->assigned_to = $request->user_id;
        $result = $user_lead->update();
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Assigned User Successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }

    function bulkAssignLead(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'exists:leads,id',
        ]);

        $user = User::findOrFail($request->user_id);

        foreach ($request->lead_ids as $leadId) {

            $lead = Lead::find($leadId);

            if ($lead) {
                $lead->assigned_to = $user->id;
                $lead->assigned_at = now();
                // optional
                // $lead->assigned_by = auth()->id();

                $lead->save();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Leads assigned successfully',
        ]);
    }
}
