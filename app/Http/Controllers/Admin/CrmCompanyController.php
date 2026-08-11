<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CrmCompanyController extends Controller
{
    public function index()
    {
        $tenantId = TenantContext::id();
        $owners = User::where('status', 'Active')
            ->when($tenantId !== null, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')->get(['id', 'name']);

        return view('companies.index', compact('owners'));
    }

    public function data()
    {
        $user = Auth::guard('web')->user();
        $query = Company::with('owner:id,name')
            ->withCount(['contacts', 'leads', 'deals']);

        if (! $user->hasElevatedAccess()) {
            $query->where('owner_id', $user->id);
        }

        return response()->json([
            'data' => $query->latest()->get(),
        ]);
    }

    public function options()
    {
        $user = Auth::guard('web')->user();
        $query = Company::orderBy('name');

        if (! $user->hasElevatedAccess()) {
            $query->where('owner_id', $user->id);
        }

        return response()->json(['data' => $query->get(['id', 'name'])]);
    }

    public function show($id)
    {
        $this->findVisibleCompany($id);

        return view('companies.show', ['companyId' => $id]);
    }

    public function detail($id)
    {
        $company = $this->findVisibleCompany($id);
        $company->load([
            'owner:id,name',
            'contacts' => fn ($query) => $query->with('owner:id,name')->orderByDesc('is_primary')->orderBy('name'),
            'leads' => fn ($query) => $query->with('assignedUser:id,name')->latest()->limit(25),
            'deals' => fn ($query) => $query->with(['stage:id,name,color', 'owner:id,name'])->latest()->limit(25),
        ])->loadCount(['contacts', 'leads', 'deals']);

        return response()->json(['status' => true, 'data' => $company]);
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant before creating a company.');
        $data = $this->validatedData($request, $tenantId);
        $data['tenant_id'] = $tenantId;

        $company = Company::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Company created successfully',
            'id' => $company->id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $company = $this->findEditableCompany($id);
        $company->update($this->validatedData($request, $company->tenant_id));

        return response()->json(['status' => true, 'message' => 'Company updated successfully']);
    }

    public function destroy($id)
    {
        $company = $this->findEditableCompany($id);

        if ($company->contacts()->exists() || $company->leads()->exists() || $company->deals()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This company is linked to contacts, leads, or deals. Mark it inactive instead.',
            ], 422);
        }

        $company->delete();

        return response()->json(['status' => true, 'message' => 'Company deleted successfully']);
    }

    private function validatedData(Request $request, ?int $tenantId): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',
            'status' => ['required', Rule::in(['prospect', 'customer', 'partner', 'inactive'])],
            'owner_id' => ['nullable', $this->tenantUserRule($tenantId)],
            'notes' => 'nullable|string',
        ]);
    }

    private function tenantUserRule(?int $tenantId)
    {
        return Rule::exists('users', 'id')->where(fn ($query) => $tenantId === null
            ? $query->whereNull('tenant_id')
            : $query->where('tenant_id', $tenantId));
    }

    private function findVisibleCompany(int $id): Company
    {
        $user = Auth::guard('web')->user();
        $query = Company::query();

        if (! $user->hasElevatedAccess()) {
            $query->where('owner_id', $user->id);
        }

        return $query->findOrFail($id);
    }

    private function findEditableCompany(int $id): Company
    {
        return $this->findVisibleCompany($id);
    }
}
