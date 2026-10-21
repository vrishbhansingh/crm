<?php

namespace App\Services\Ai\Tools;

use App\Models\LeadActivity;
use App\Models\MasterValue;
use App\Models\User;
use Carbon\Carbon;

class UpdateLeadStatusTool extends WriteTool
{
    public function name(): string
    {
        return 'update_lead_status';
    }

    public function description(): string
    {
        return 'Propose changing a lead\'s status and/or its next follow-up date. This only PREPARES the change; the user must confirm before it is saved. Cannot mark a lead "converted" (that is done by converting it to a deal in the CRM). Agents can only change their own leads.';
    }

    public function permission(): string
    {
        return 'leads.edit';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'lead_id' => ['type' => 'integer', 'description' => 'The lead id.'],
                'status' => ['type' => 'string', 'description' => 'New status code, e.g. contacted, interested, follow_up, not_interested, closed. Optional if only setting a follow-up.'],
                'follow_up_date' => ['type' => 'string', 'description' => 'Next follow-up date, YYYY-MM-DD. Optional.'],
                'follow_up_time' => ['type' => 'string', 'description' => 'Follow-up time, HH:MM (24h). Optional.'],
            ],
            'required' => ['lead_id'],
        ];
    }

    public function proposal(User $user, array $input): array
    {
        $lead = $this->leads($user)->find((int) ($input['lead_id'] ?? 0));
        if (! $lead) {
            return ['error' => 'No such lead, or it is not assigned to you.'];
        }

        $status = isset($input['status']) ? trim((string) $input['status']) : null;
        $date = isset($input['follow_up_date']) ? trim((string) $input['follow_up_date']) : null;
        $time = isset($input['follow_up_time']) ? trim((string) $input['follow_up_time']) : null;

        if ($status === null && $date === null) {
            return ['error' => 'Say which status to set, or a follow-up date.'];
        }

        if ($status !== null) {
            if ($status === 'converted') {
                return ['error' => 'A lead can\'t be marked converted here. Convert it to a deal from the lead\'s page.'];
            }
            if (! MasterValue::options('lead_status')->pluck('code')->contains($status)) {
                return ['error' => 'Unknown status "'.$status.'". Valid: '.MasterValue::options('lead_status')->pluck('code')->reject(fn ($c) => $c === 'converted')->implode(', ').'.'];
            }
        }

        if ($date !== null && ! $this->validDate($date)) {
            return ['error' => 'The follow-up date must look like YYYY-MM-DD.'];
        }
        if ($time !== null && ! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) {
            return ['error' => 'The follow-up time must look like HH:MM (24-hour).'];
        }

        $changes = [];
        if ($status !== null) {
            $changes[] = 'status "'.$lead->lead_status.'" → "'.$status.'"';
        }
        if ($date !== null) {
            $changes[] = 'next follow-up '.$date.($time ? ' '.$time : '');
        }

        return ['summary' => 'Update lead #'.($lead->lead_number ?: $lead->id).' ('.$lead->name.'): '.implode(', ', $changes)];
    }

    public function execute(User $user, array $input): string
    {
        $this->revalidate($user, $input);
        $lead = $this->leads($user)->findOrFail((int) $input['lead_id']);

        $previous = $lead->lead_status;
        if (! empty($input['status'])) {
            $lead->lead_status = trim($input['status']);
        }
        if (! empty($input['follow_up_date'])) {
            $lead->follow_up_date = trim($input['follow_up_date']);
            $lead->follow_up_time = ! empty($input['follow_up_time']) ? trim($input['follow_up_time']).':00' : null;
        }
        $lead->save();

        if ($previous !== $lead->lead_status) {
            LeadActivity::create([
                'tenant_id' => $lead->tenant_id,
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'type' => 'status_changed',
                'description' => "Status changed from \"{$previous}\" to \"{$lead->lead_status}\" (via CRM Assistant)",
            ]);
        }

        return 'Updated '.$lead->name.'.';
    }

    private function validDate(string $value): bool
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $value) !== false && Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d') === $value;
        } catch (\Throwable) {
            return false;
        }
    }
}
