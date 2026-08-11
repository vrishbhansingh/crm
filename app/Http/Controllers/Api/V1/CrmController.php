<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Task;
use App\Services\LeadNumberService;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CrmController extends Controller
{
    public function leads(Request $request)
    {
        $this->authorizeAbility($request, 'leads.view');
        $query = Lead::with(['company:id,name', 'contact:id,name', 'assignedUser:id,name']);
        if (! $request->user()->hasElevatedAccess()) {
            $query->where('assigned_to', $request->user()->id);
        }
        $this->search($query, $request, ['name', 'email', 'phone', 'company_name']);

        return response()->json($query->latest()->paginate($this->perPage($request)));
    }

    public function deals(Request $request)
    {
        $this->authorizeAbility($request, 'deals.view');
        $query = Deal::with(['pipeline:id,name', 'stage:id,name,color', 'company:id,name', 'contact:id,name', 'owner:id,name']);
        if (! $request->user()->hasElevatedAccess()) {
            $query->where('owner_id', $request->user()->id);
        }
        $this->search($query, $request, ['name']);

        return response()->json($query->latest()->paginate($this->perPage($request)));
    }

    public function companies(Request $request)
    {
        $this->authorizeAbility($request, 'companies.view');
        $query = Company::with('owner:id,name')->withCount(['contacts', 'leads', 'deals']);
        if (! $request->user()->hasElevatedAccess()) {
            $query->where('owner_id', $request->user()->id);
        }
        $this->search($query, $request, ['name', 'email', 'phone']);

        return response()->json($query->latest()->paginate($this->perPage($request)));
    }

    public function contacts(Request $request)
    {
        $this->authorizeAbility($request, 'contacts.view');
        $query = Contact::with(['company:id,name', 'owner:id,name']);
        if (! $request->user()->hasElevatedAccess()) {
            $query->where('owner_id', $request->user()->id);
        }
        $this->search($query, $request, ['name', 'email', 'phone']);

        return response()->json($query->latest()->paginate($this->perPage($request)));
    }

    public function tasks(Request $request)
    {
        $this->authorizeAbility($request, 'tasks.view');
        $query = Task::with(['assignee:id,name', 'creator:id,name']);
        if (! $request->user()->hasElevatedAccess()) {
            $query->where(fn ($q) => $q->where('assigned_to', $request->user()->id)->orWhere('created_by', $request->user()->id));
        }
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));
        $this->search($query, $request, ['title', 'description']);

        return response()->json($query->orderBy('due_at')->paginate($this->perPage($request)));
    }

    public function storeLead(Request $request)
    {
        $this->authorizeWrite($request, 'leads.create');
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'A tenant context is required.');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'lead_source' => ['nullable', 'string', 'max:100'],
            'lead_type' => ['nullable', 'string', 'max:100'],
            'lead_status' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', 'string', 'max:50'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'requirement' => ['nullable', 'string', 'max:5000'],
        ]);

        $lead = new Lead(array_merge($data, [
                'tenant_id' => $tenantId,
                'assigned_to' => $request->user()->id,
                'assigned_by' => $request->user()->id,
                'assigned_at' => now(),
                'status' => 'Active',
                'is_converted' => 'No',
            ]));
        LeadNumberService::saveNew($lead);
        LeadActivity::create(['tenant_id' => $tenantId, 'lead_id' => $lead->id, 'user_id' => $request->user()->id, 'type' => 'created', 'description' => 'Lead created through API']);

        return response()->json(['data' => $lead], 201);
    }

    public function storeTask(Request $request)
    {
        $this->authorizeWrite($request, 'tasks.create');
        $tenantId = TenantContext::id();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $tenantId)],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'status' => ['nullable', Rule::in(['todo', 'in_progress'])],
            'due_at' => ['nullable', 'date'],
            'remind_at' => ['nullable', 'date', 'before_or_equal:due_at'],
        ]);
        $task = Task::create(array_merge($data, ['tenant_id' => $tenantId, 'created_by' => $request->user()->id, 'status' => $data['status'] ?? 'todo']));

        return response()->json(['data' => $task], 201);
    }

    private function authorizeAbility(Request $request, string $permission): void
    {
        abort_unless($request->user()->tokenCan('crm:read') && $request->user()->can($permission), 403);
    }

    private function authorizeWrite(Request $request, string $permission): void
    {
        abort_unless($request->user()->tokenCan('crm:write') && $request->user()->can($permission), 403);
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 25), 1), 100);
    }

    private function search($query, Request $request, array $columns): void
    {
        if (! $request->filled('search')) {
            return;
        }
        $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->search).'%';
        $query->where(function ($q) use ($columns, $term) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', $term);
            }
        });
    }
}
