<?php

namespace App\Services\Ai\Tools;

use App\Models\LeadActivity;
use App\Models\User;

class GetLeadTool extends CrmTool
{
    public function name(): string
    {
        return 'get_lead';
    }

    public function description(): string
    {
        return 'Full detail for one lead by id, including its recent activity timeline. Use search_leads first if you only know the name.';
    }

    public function permission(): string
    {
        return 'leads.view';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => ['lead_id' => ['type' => 'integer', 'description' => 'The lead id.']],
            'required' => ['lead_id'],
        ];
    }

    public function run(User $user, array $input): array
    {
        $lead = $this->leads($user)->with('assignedUser:id,name')->find((int) ($input['lead_id'] ?? 0));
        if (! $lead) {
            return ['error' => 'No such lead, or it is not assigned to you.'];
        }

        $activities = LeadActivity::where('lead_id', $lead->id)->latest('id')->limit(10)->get(['type', 'description', 'created_at']);

        return [
            'id' => $lead->id,
            'lead_number' => $lead->lead_number,
            'name' => $lead->name,
            'company' => $lead->company_name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'city' => $lead->city,
            'state' => $lead->state,
            'status' => $lead->lead_status,
            'source' => $lead->lead_source,
            'priority' => $lead->priority,
            'product' => $lead->product,
            'requirement' => $lead->requirement,
            'assigned_to' => $lead->assignedUser?->name,
            'follow_up_date' => $lead->follow_up_date,
            'follow_up_time' => $lead->follow_up_time,
            'converted' => $lead->is_converted === 'Yes',
            'created' => optional($lead->created_at)->toDateTimeString(),
            'recent_activity' => $activities->map(fn ($a) => ['type' => $a->type, 'what' => $a->description, 'when' => optional($a->created_at)->toDateTimeString()])->all(),
        ];
    }
}
