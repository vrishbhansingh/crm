<?php

namespace App\Http\Controllers;

use App\Exports\LeadImportReportExport;
use App\Imports\LeadsImport;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\MasterValue;
use App\Models\User;
use App\Services\LeadNumberService;
use App\Services\LeadUniquenessService;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

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
            'newToday' => (clone $baseQuery)->whereDate('created_at', Carbon::today())->count(),
            'followUpDue' => (clone $baseQuery)->whereDate('follow_up_date', '<=', Carbon::today())
                ->whereNotIn('lead_status', ['converted', 'not_interested', 'closed'])
                ->count(),
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
        $sl_no = ($paginator->currentPage() - 1) * $paginator->perPage() + 1;
        foreach ($paginator->items() as $lead) {
            $editUrl = route('leads.edit', $lead->id);
            $dealUrl = $lead->deal ? route('deals.show', $lead->deal->id) : null;
            $action = "
                <div class='row-actions'>
                    <button type='button' class='row-actions-btn' aria-label='Actions'><i class='fa fa-ellipsis-v'></i></button>
                    <div class='row-actions-menu'>
                        <a href='{$editUrl}' class='editbtn'><i class='fa fa-pencil'></i> Edit</a>";

            if ($dealUrl) {
                $action .= "
                        <a href='{$dealUrl}'><i class='fa fa-briefcase'></i> View Deal</a>";
            }

            $action .= "
                        <button
                            type='button'
                            class='action-delete delete_data text-danger'
                            data-id='{$lead->id}'
                            data-toggle='modal'
                            data-target='#deleteConfirmModal'>
                            <i class='fa fa-trash'></i> Delete
                        </button>
                    </div>
                </div>";
            $user = User::find($lead->assigned_to);

            if ($user) {
                $user_name = $user->name;
            } else {
                $user_name = null;
            }

            $data[] = [

                /* ================= BASIC ================= */
                'sl_no' => $sl_no++,
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
            // An existing company/contact can be linked by ID instead of
            // typing their details again — the free-text fields below are
            // then only required when no ID was picked (a new record).
            'company_id' => [
                'nullable',
                'integer',
                Rule::exists('companies', 'id')->where('tenant_id', $tenantId),
            ],
            'company_name' => [
                Rule::requiredIf(fn () => ! $request->filled('company_id')),
                'nullable',
                'string',
                'max:255',
            ],
            'gst_no' => [
                'nullable',
                'string',
                'max:255',
            ],

            'contact_id' => [
                'nullable',
                'integer',
                Rule::exists('contacts', 'id')->where('tenant_id', $tenantId),
            ],

            'name' => [
                Rule::requiredIf(fn () => ! $request->filled('contact_id')),
                'nullable',
                'string',
                'max:255',
            ],

            'phone' => [
                Rule::requiredIf(fn () => ! $request->filled('contact_id')),
                'nullable',
                'string',
                'max:20',
            ],

            'alternate_phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'email' => [
                Rule::requiredIf(fn () => ! $request->filled('contact_id')),
                'nullable',
                'email',
                'max:255',
            ],

            'designation' => [
                Rule::requiredIf(fn () => ! $request->filled('contact_id')),
                'nullable',
                'string',
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
            // assigned_by/last_contacted_*/conversion fields were dropped
            // from the create form entirely: assigned_by already defaults to
            // the current user below, and a brand-new lead can't have been
            // "last contacted" or converted before it exists.
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('tenant_id', $tenantId),
            ],

            // ================= NOTES =================
            'internal_note' => [
                'nullable',
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // What this lead's own name/phone/email will actually be — either
        // an existing contact's (contact_id) or the form's own fields for a
        // brand-new one — resolved *before* anything is created, so the
        // mandatory-field and duplicate checks below run against real,
        // final values and reject before a Company/Contact/Lead row is
        // ever written (not after, which would leave orphans behind).
        if ($request->filled('contact_id')) {
            $pickedContact = Contact::where('tenant_id', $tenantId)->findOrFail($request->integer('contact_id'));
            $leadName = $pickedContact->name;
            $leadPhone = $pickedContact->phone;
            $leadEmail = $pickedContact->email;
        } else {
            $leadName = $request->name;
            $leadPhone = $request->phone;
            $leadEmail = $request->email;
        }

        if (! $leadName || ! $leadPhone || ! $leadEmail) {
            return response()->json([
                'status' => false,
                'message' => 'Name, email, and phone number are all required to create a lead.',
            ], 422);
        }

        $emailNormalized = LeadUniquenessService::normalizeEmail($leadEmail);
        $phoneNormalized = LeadUniquenessService::normalizePhone($leadPhone);
        $duplicate = LeadUniquenessService::findDuplicate($emailNormalized, $phoneNormalized);
        if ($duplicate) {
            $field = LeadUniquenessService::duplicateField($duplicate, $emailNormalized, $phoneNormalized);

            return response()->json([
                'status' => false,
                'message' => "A lead with this {$field} already exists (\"{$duplicate->name}\", lead #{$duplicate->lead_number}).",
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

        // Auto-assignment: if nobody was explicitly picked, try the
        // tenant's configured assignment rules (product/state/source/round
        // robin, in that order) first; if none match or none are
        // configured, fall back to whichever active sales-role user
        // currently has the fewest assigned leads, instead of leaving the
        // lead unassigned.
        $wasAutoAssigned = false;
        $assignedTo = $request->assigned_to;
        if (empty($assignedTo)) {
            $assignedTo = app(\App\Services\LeadAssignmentService::class)->resolve($lead->tenant_id, [
                'product' => $lead->product, 'state' => $lead->state, 'lead_source' => $lead->lead_source,
            ]) ?: $this->leastLoadedSalesUserId($lead->tenant_id);
            $wasAutoAssigned = (bool) $assignedTo;
        }

        $lead->assigned_to = $assignedTo;
        $lead->assigned_by = Auth::guard('web')->id();
        $lead->assigned_at = $assignedTo ? now() : null;
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

        // Existing company/contact can be linked by ID instead of always
        // creating a new one — the picker on the form sends company_id /
        // contact_id when the user chose an existing record there.
        if ($request->filled('company_id')) {
            $company = Company::where('tenant_id', $lead->tenant_id)->findOrFail($request->integer('company_id'));
        } else {
            $company = Company::firstOrCreate([
                'tenant_id' => $lead->tenant_id,
                'name' => $request->company_name,
            ], [
                'owner_id' => $assignedTo,
                'gst_number' => $request->gst_no,
                'city' => $lead->city,
                'state' => $lead->state,
                'country' => $lead->country,
                'status' => 'prospect',
            ]);
        }
        $lead->company_name = $company->name;
        $lead->gst_no = $company->gst_number ?? $request->gst_no;

        if ($request->filled('contact_id')) {
            $contact = Contact::where('tenant_id', $lead->tenant_id)->findOrFail($request->integer('contact_id'));
        } else {
            $contact = Contact::create([
                'tenant_id' => $lead->tenant_id,
                'company_id' => $company->id,
                'owner_id' => $assignedTo,
                'name' => $request->name,
                'phone' => $request->phone,
                'alternate_phone' => $request->alternate_phone,
                'email' => $request->email,
                'designation' => $request->designation,
                'city' => $lead->city,
                'is_primary' => true,
                'source' => 'lead',
                'status' => 'active',
            ]);
        }
        $lead->name = $contact->name;
        $lead->phone = $contact->phone;
        $lead->alternate_phone = $contact->alternate_phone;
        $lead->email = $contact->email;

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
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'alternate_phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'conversion_value' => ['nullable', 'numeric', 'min:0'],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $lead->tenant_id)],
            'assigned_by' => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $lead->tenant_id)],
            'last_contacted_by' => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $lead->tenant_id)],
        ]);

        $emailNormalized = LeadUniquenessService::normalizeEmail($request->email);
        $phoneNormalized = LeadUniquenessService::normalizePhone($request->phone);
        $duplicate = LeadUniquenessService::findDuplicate($emailNormalized, $phoneNormalized, $lead->id);
        if ($duplicate) {
            $field = LeadUniquenessService::duplicateField($duplicate, $emailNormalized, $phoneNormalized);

            return response()->json([
                'status' => false,
                'message' => "Another lead already uses this {$field} (\"{$duplicate->name}\", lead #{$duplicate->lead_number}).",
            ], 422);
        }

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

        $importer = new LeadsImport($tenantId);
        Excel::import($importer, $request->file('file'));

        $summary = $importer->summary();
        $problemRows = $importer->problemRows();

        $reportUrl = null;
        if ($problemRows) {
            $filename = 'lead-import-report-'.now()->format('Ymd-His').'-'.substr(md5(uniqid('', true)), 0, 8).'.xlsx';
            $relativePath = 'lead-import-reports/'.$tenantId.'/'.$filename;
            Excel::store(new LeadImportReportExport($problemRows), $relativePath, 'local');
            $reportUrl = route('leads.import_report.download', ['filename' => $filename]);
        }

        return response()->json([
            'status' => true,
            'message' => "Lead import completed — {$summary['success']} uploaded, {$summary['skipped']} skipped, {$summary['failed']} failed.",
            'summary' => $summary,
            // Capped so a huge file can't blow up the response payload — the
            // downloadable report (when $reportUrl is set) always has every
            // problem row, not just this preview slice.
            'rows' => array_slice($problemRows, 0, 200),
            'rows_truncated' => count($problemRows) > 200,
            'report_url' => $reportUrl,
        ]);
    }

    /**
     * The downloadable Import Report generated by leads_import() above —
     * every skipped/failed row with its reason, so a user can fix those
     * specific rows and re-upload instead of re-checking the whole file.
     * Filename embeds the tenant id in its storage path (not the URL
     * itself), and is re-checked here so one tenant can't download
     * another's report even by guessing a filename.
     */
    public function downloadImportReport(string $filename)
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant first.');

        // Defends against path traversal in the filename segment — it's
        // always machine-generated (see leads_import()), so a legitimate
        // request never contains anything outside this pattern.
        abort_unless(preg_match('/^lead-import-report-[\w-]+\.xlsx$/', $filename), 404);

        $relativePath = 'lead-import-reports/'.$tenantId.'/'.$filename;
        abort_unless(Storage::disk('local')->exists($relativePath), 404);

        return Storage::disk('local')->download($relativePath, 'lead-import-report.xlsx');
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
