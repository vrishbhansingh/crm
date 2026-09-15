<?php

namespace App\Services;

use App\Models\DealAssignmentRule;

/**
 * Deal-side counterpart to LeadAssignmentService: Pipeline rules checked
 * first, then Source, then a single tenant-wide Round Robin pool as the
 * final fallback. Same round-robin-via-cursor mechanics as the lead
 * version — see that class for the reasoning.
 */
class DealAssignmentService
{
    /**
     * @param  array  $deal  ['pipeline_id' => int|null, 'source' => ?string]
     */
    public function resolve(int $tenantId, array $deal): ?int
    {
        $pipelineId = $deal['pipeline_id'] ?? null;
        if ($pipelineId) {
            $rule = DealAssignmentRule::where('tenant_id', $tenantId)
                ->where('rule_type', 'pipeline')
                ->where('is_active', true)
                ->where('match_value', (string) $pipelineId)
                ->orderBy('sort_order')
                ->first();

            if ($rule && ($agentId = $this->nextFromRule($rule)) !== null) {
                return $agentId;
            }
        }

        $source = trim((string) ($deal['source'] ?? ''));
        if ($source !== '') {
            $rule = DealAssignmentRule::where('tenant_id', $tenantId)
                ->where('rule_type', 'source')
                ->where('is_active', true)
                ->whereRaw('LOWER(match_value) = ?', [mb_strtolower($source)])
                ->orderBy('sort_order')
                ->first();

            if ($rule && ($agentId = $this->nextFromRule($rule)) !== null) {
                return $agentId;
            }
        }

        $roundRobin = DealAssignmentRule::where('tenant_id', $tenantId)
            ->where('rule_type', 'round_robin')
            ->where('is_active', true)
            ->first();

        return $roundRobin ? $this->nextFromRule($roundRobin) : null;
    }

    private function nextFromRule(DealAssignmentRule $rule): ?int
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
