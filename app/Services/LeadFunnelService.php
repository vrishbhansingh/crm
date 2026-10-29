<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\DealStageHistory;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Pipeline;
use App\Models\Tenant;

/**
 * Funnel automation: puts a new lead into a sales pipeline as a deal in its
 * first open stage — the same result as clicking "Convert to deal", done
 * automatically at the moment the lead is created.
 *
 * Which pipeline: the one the caller names (e.g. a lead integration's own
 * funnel), otherwise the company's default funnel (Lead Assignment settings).
 * With neither set, nothing happens and the lead stays a plain lead.
 *
 * Never throws and never blocks lead capture: any problem (pipeline deleted,
 * no open stage, DB hiccup) is reported and the lead simply stays a lead.
 */
class LeadFunnelService
{
    /** @var array<int, int|null> */
    private array $defaults = [];

    public function defaultPipelineId(int $tenantId): ?int
    {
        if (! array_key_exists($tenantId, $this->defaults)) {
            $this->defaults[$tenantId] = Tenant::whereKey($tenantId)->value('default_pipeline_id') ?: null;
        }

        return $this->defaults[$tenantId];
    }

    public function forgetCache(): void
    {
        $this->defaults = [];
    }

    public function enter(Lead $lead, ?int $pipelineId = null, string $via = 'automation', ?int $actorId = null): ?Deal
    {
        try {
            $pipelineId = $pipelineId ?: $this->defaultPipelineId((int) $lead->tenant_id);
            if (! $pipelineId || $lead->deal()->exists()) {
                return null;
            }

            $pipeline = Pipeline::withoutGlobalScope('tenant')->where('tenant_id', $lead->tenant_id)->where('is_active', true)->find($pipelineId);
            $stage = $pipeline?->stages()->where('is_won', false)->where('is_lost', false)->orderBy('sort_order')->first();
            if (! $pipeline || ! $stage) {
                return null;
            }

            return $lead->getConnection()->transaction(function () use ($lead, $pipeline, $stage, $via, $actorId) {
                $ownerId = $lead->assigned_to
                    ?: app(DealAssignmentService::class)->resolve($lead->tenant_id, ['pipeline_id' => $pipeline->id, 'source' => $lead->lead_source])
                    ?: $actorId;

                $deal = Deal::create([
                    'tenant_id' => $lead->tenant_id,
                    'pipeline_id' => $pipeline->id,
                    'stage_id' => $stage->id,
                    'lead_id' => $lead->id,
                    'company_id' => $lead->company_id,
                    'contact_id' => $lead->contact_id,
                    'source' => $lead->lead_source,
                    'owner_id' => $ownerId,
                    'created_by' => $actorId,
                    'name' => trim(($lead->company_name ?: $lead->name).' - Deal'),
                    'amount' => $lead->conversion_value ?? 0,
                ]);

                DealStageHistory::create([
                    'deal_id' => $deal->id,
                    'from_stage_id' => null,
                    'to_stage_id' => $stage->id,
                    'changed_by' => $actorId,
                ]);

                $lead->is_converted = 'Yes';
                $lead->converted_at = now();
                $lead->conversion_value = $lead->conversion_value ?? 0;
                $lead->save();

                LeadActivity::create([
                    'tenant_id' => $lead->tenant_id,
                    'lead_id' => $lead->id,
                    'user_id' => $actorId,
                    'type' => 'converted',
                    'description' => 'Automatically added to the "'.$pipeline->name.'" funnel ('.$stage->name.') via '.$via.'.',
                ]);

                return $deal;
            });
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    }
}
