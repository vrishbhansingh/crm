<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DealAssignmentRule;
use App\Models\MasterValue;
use App\Models\Pipeline;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Deal-side counterpart to LeadAssignmentController — same shape, same
 * upsert-a-single-round-robin-row behavior, just pipeline_id instead of
 * product/state as the first-priority match dimension (a deal has a
 * pipeline, not a product/state of its own).
 */
class DealAssignmentController extends Controller
{
    public function index()
    {
        $tenantId = TenantContext::id();
        $agents = User::where('tenant_id', $tenantId)->where('status', 'Active')->orderBy('name')->get(['id', 'name']);
        $pipelines = Pipeline::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']);
        $leadSources = MasterValue::options('lead_source');

        return view('deal_assignment.index', compact('agents', 'pipelines', 'leadSources'));
    }

    public function data()
    {
        $tenantId = TenantContext::id();
        $rules = DealAssignmentRule::where('tenant_id', $tenantId)->orderBy('sort_order')->orderBy('id')->get();
        $agents = User::where('tenant_id', $tenantId)->get(['id', 'name'])->keyBy('id');
        $pipelines = Pipeline::where('tenant_id', $tenantId)->get(['id', 'name'])->keyBy('id');

        $grouped = collect(DealAssignmentRule::TYPES)->mapWithKeys(function ($type) use ($rules, $agents, $pipelines) {
            return [$type => $rules->where('rule_type', $type)->map(fn (DealAssignmentRule $rule) => [
                'id' => $rule->id,
                'match_value' => $rule->match_value,
                'match_label' => $type === 'pipeline' ? ($pipelines->get((int) $rule->match_value)?->name ?? 'Deleted pipeline') : $rule->match_value,
                'is_active' => $rule->is_active,
                'agents' => collect($rule->agent_ids ?? [])->map(fn ($id) => ['id' => $id, 'name' => $agents->get($id)?->name ?? 'Removed user'])->values(),
            ])->values()];
        });

        return response()->json(['status' => true, 'data' => $grouped]);
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::id();
        $data = $this->validated($request, $tenantId);

        if ($data['rule_type'] === 'round_robin') {
            $existing = DealAssignmentRule::where('tenant_id', $tenantId)->where('rule_type', 'round_robin')->first();
            if ($existing) {
                $existing->update(['agent_ids' => $data['agent_ids'], 'is_active' => true]);

                return response()->json(['status' => true, 'message' => 'Round robin pool updated.']);
            }
        } else {
            $duplicate = DealAssignmentRule::where('tenant_id', $tenantId)
                ->where('rule_type', $data['rule_type'])
                ->whereRaw('LOWER(match_value) = ?', [mb_strtolower($data['match_value'])])
                ->exists();
            abort_if($duplicate, 422, 'A rule for this value already exists.');
        }

        DealAssignmentRule::create([
            'tenant_id' => $tenantId,
            'rule_type' => $data['rule_type'],
            'match_value' => $data['match_value'] ?? null,
            'agent_ids' => $data['agent_ids'],
            'created_by' => Auth::guard('web')->id(),
        ]);

        return response()->json(['status' => true, 'message' => 'Rule created.']);
    }

    public function update(Request $request, $id)
    {
        $rule = DealAssignmentRule::findOrFail($id);
        $this->authorizeRule($rule);
        $data = $this->validated($request, $rule->tenant_id, $rule);

        if ($rule->rule_type !== 'round_robin' && array_key_exists('match_value', $data)) {
            $duplicate = DealAssignmentRule::where('tenant_id', $rule->tenant_id)
                ->where('rule_type', $rule->rule_type)
                ->where('id', '!=', $rule->id)
                ->whereRaw('LOWER(match_value) = ?', [mb_strtolower($data['match_value'])])
                ->exists();
            abort_if($duplicate, 422, 'A rule for this value already exists.');
        }

        $update = collect($data)->only(['match_value', 'agent_ids'])->all();
        if ($request->has('is_active')) {
            $update['is_active'] = $request->boolean('is_active');
        }
        $rule->update($update);

        return response()->json(['status' => true, 'message' => 'Rule updated.']);
    }

    public function destroy($id)
    {
        $rule = DealAssignmentRule::findOrFail($id);
        $this->authorizeRule($rule);
        $rule->delete();

        return response()->json(['status' => true, 'message' => 'Rule removed.']);
    }

    private function validated(Request $request, int $tenantId, ?DealAssignmentRule $existing = null): array
    {
        $ruleType = $existing?->rule_type ?? $request->input('rule_type');

        return $request->validate([
            'rule_type' => [$existing ? 'sometimes' : 'required', Rule::in(DealAssignmentRule::TYPES)],
            'match_value' => [$ruleType === 'round_robin' ? 'nullable' : 'required', 'nullable', 'string', 'max:150'],
            'agent_ids' => ['required', 'array', 'min:1'],
            'agent_ids.*' => ['integer', Rule::exists('users', 'id')->where('tenant_id', $tenantId)],
        ]);
    }

    private function authorizeRule(DealAssignmentRule $rule): void
    {
        abort_unless($rule->tenant_id === TenantContext::id(), 404);
    }
}
