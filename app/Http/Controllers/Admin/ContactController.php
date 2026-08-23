<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function index()
    {
        $tenantId = TenantContext::id();
        $companies = Company::orderBy('name')->get(['id', 'name']);
        $owners = User::where('status', 'Active')
            ->when($tenantId !== null, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')->get(['id', 'name']);

        return view('contacts.index', compact('companies', 'owners'));
    }

    public function data(Request $request)
    {
        $user = Auth::guard('web')->user();
        $query = Contact::with(['company:id,name', 'owner:id,name'])
            ->withCount(['leads', 'deals']);

        if (! $user->hasElevatedAccess()) {
            $query->where('owner_id', $user->id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->integer('company_id'));
        }

        if ($request->filled('search')) {
            $term = '%'.$request->string('search').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        $perPage = min(max((int) $request->input('per_page', 20), 5), 100);
        $paginator = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }

    /**
     * Unpaginated {id, name, company} list for pickers (e.g. the Lead create
     * form's "Existing Contact" select) — data() above now paginates for the
     * Contacts list page itself, which would silently truncate a picker to
     * one page's worth of contacts if it used the same endpoint.
     */
    public function options()
    {
        $user = Auth::guard('web')->user();
        $query = Contact::with('company:id,name')->orderBy('name');

        if (! $user->hasElevatedAccess()) {
            $query->where('owner_id', $user->id);
        }

        return response()->json(['data' => $query->get(['id', 'name', 'phone', 'email', 'alternate_phone', 'designation', 'company_id'])]);
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant before creating a contact.');
        $data = $this->validatedData($request, $tenantId);
        $data['tenant_id'] = $tenantId;

        $contact = Contact::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Contact created successfully',
            'id' => $contact->id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $contact = $this->findEditableContact($id);
        $contact->update($this->validatedData($request, $contact->tenant_id));

        return response()->json(['status' => true, 'message' => 'Contact updated successfully']);
    }

    public function destroy($id)
    {
        $contact = $this->findEditableContact($id);

        if ($contact->leads()->exists() || $contact->deals()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This contact is linked to leads or deals. Mark it inactive instead.',
            ], 422);
        }

        $contact->delete();

        return response()->json(['status' => true, 'message' => 'Contact deleted successfully']);
    }

    private function validatedData(Request $request, ?int $tenantId): array
    {
        return $request->validate([
            'company_id' => ['nullable', $this->tenantExistsRule('companies', $tenantId)],
            'owner_id' => ['nullable', $this->tenantExistsRule('users', $tenantId)],
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'alternate_phone' => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
            'source' => 'nullable|string|max:50',
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'notes' => 'nullable|string',
        ]);
    }

    private function tenantExistsRule(string $table, ?int $tenantId)
    {
        $connection = $table === 'users' || config('tenancy.mode') === 'shared' ? config('tenancy.master_connection', 'mysql') : 'tenant';

        return Rule::exists($connection.'.'.$table, 'id')->where(fn ($query) => $tenantId === null
            ? $query->whereNull('tenant_id')
            : $query->where('tenant_id', $tenantId));
    }

    private function findEditableContact(int $id): Contact
    {
        $user = Auth::guard('web')->user();
        $query = Contact::query();

        if (! $user->hasElevatedAccess()) {
            $query->where('owner_id', $user->id);
        }

        return $query->findOrFail($id);
    }
}
