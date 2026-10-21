<?php

namespace App\Services\Ai\Tools;

use App\Models\LeadActivity;
use App\Models\User;

class AddLeadNoteTool extends WriteTool
{
    public function name(): string
    {
        return 'add_lead_note';
    }

    public function description(): string
    {
        return 'Propose adding a note to a lead\'s timeline. This only PREPARES the note; the user must confirm before it is saved. Agents can only add notes to their own leads.';
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
                'lead_id' => ['type' => 'integer', 'description' => 'The lead id (use search_leads to find it).'],
                'note' => ['type' => 'string', 'description' => 'The note text, max 2000 characters.'],
            ],
            'required' => ['lead_id', 'note'],
        ];
    }

    public function proposal(User $user, array $input): array
    {
        $lead = $this->leads($user)->find((int) ($input['lead_id'] ?? 0));
        if (! $lead) {
            return ['error' => 'No such lead, or it is not assigned to you.'];
        }

        $note = trim((string) ($input['note'] ?? ''));
        if ($note === '' || mb_strlen($note) > 2000) {
            return ['error' => 'The note must be between 1 and 2000 characters.'];
        }

        return ['summary' => 'Add a note to lead #'.($lead->lead_number ?: $lead->id).' ('.$lead->name.'): “'.mb_strimwidth($note, 0, 160, '…').'”'];
    }

    public function execute(User $user, array $input): string
    {
        $this->revalidate($user, $input);
        $lead = $this->leads($user)->findOrFail((int) $input['lead_id']);

        LeadActivity::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'type' => 'note',
            'description' => trim($input['note']).' (added via CRM Assistant)',
        ]);

        return 'Note added to '.$lead->name.'.';
    }
}
