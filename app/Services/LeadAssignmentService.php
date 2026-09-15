<?php

namespace App\Services;

use App\Models\LeadAssignmentRule;

/**
 * Rules-based lead auto-assignment: Product rules checked first, then
 * State, then Source, then a single tenant-wide Round Robin pool as the
 * final fallback — same priority order shown in the settings page. A rule
 * with more than one agent cycles through them in turn via its own cursor,
 * so consecutive matching leads don't all land on the same person.
 *
 * Returns null when no rule matches (or none are configured at all) — the
 * caller falls back to its own existing default (currently
 * leastLoadedSalesUserId in LeadController/WebhookLeadController), so this
 * is purely additive and never worse than the pre-existing behavior.
 */
class LeadAssignmentService
{
    private const VALUE_TYPES = ['product' => 'product', 'state' => 'state', 'source' => 'lead_source'];

    /**
     * @param  array  $lead  Must carry whichever of 'product'/'state'/
     *                       'lead_source' keys are available for the lead
     *                       being assigned.
     */
    public function resolve(int $tenantId, array $lead): ?int
    {
        foreach (self::VALUE_TYPES as $ruleType => $leadKey) {
            $value = trim((string) ($lead[$leadKey] ?? ''));
            if ($value === '') {
                continue;
            }

            $rule = LeadAssignmentRule::where('tenant_id', $tenantId)
                ->where('rule_type', $ruleType)
                ->where('is_active', true)
                ->whereRaw('LOWER(match_value) = ?', [mb_strtolower($value)])
                ->orderBy('sort_order')
                ->first();

            if ($rule && ($agentId = $this->nextFromRule($rule)) !== null) {
                return $agentId;
            }
        }

        $roundRobin = LeadAssignmentRule::where('tenant_id', $tenantId)
            ->where('rule_type', 'round_robin')
            ->where('is_active', true)
            ->first();

        return $roundRobin ? $this->nextFromRule($roundRobin) : null;
    }

    private function nextFromRule(LeadAssignmentRule $rule): ?int
    {
        $agents = $rule->activeAgents();
        if ($agents->isEmpty()) {
            return null;
        }

        $index = $rule->cursor % $agents->count();
        $agentId = $agents[$index]->id;
        $rule->update(['cursor' => ($index + 1) % $agents->count()]);

        return $agentId;
    }
}
