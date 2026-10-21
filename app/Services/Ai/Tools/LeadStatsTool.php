<?php

namespace App\Services\Ai\Tools;

use App\Models\User;

class LeadStatsTool extends CrmTool
{
    public function name(): string
    {
        return 'lead_stats';
    }

    public function description(): string
    {
        return 'Lead counts grouped by status and by source, plus how many are new this week, overdue for follow-up, and converted. Scoped to the user: agents get their own numbers, admins/managers get the whole company.';
    }

    public function permission(): string
    {
        return 'leads.view';
    }

    public function schema(): array
    {
        return ['type' => 'object', 'properties' => ['days' => ['type' => 'integer', 'description' => 'Look-back window for "new" leads, 1-365. Default 7.']]];
    }

    public function run(User $user, array $input): array
    {
        $days = max(1, min(365, (int) ($input['days'] ?? 7)));
        $today = now(optional($user->tenant)->timezone ?: config('app.timezone'))->toDateString();

        return [
            'scope' => $user->hasElevatedAccess() ? 'whole company' : 'your own leads',
            'total' => $this->leads($user)->count(),
            'by_status' => $this->leads($user)->selectRaw('lead_status as k, COUNT(*) as c')->groupBy('lead_status')->pluck('c', 'k')->all(),
            'by_source' => $this->leads($user)->selectRaw('lead_source as k, COUNT(*) as c')->groupBy('lead_source')->pluck('c', 'k')->all(),
            'new_in_last_days' => ['days' => $days, 'count' => $this->leads($user)->where('created_at', '>=', now()->subDays($days))->count()],
            'follow_up_overdue' => $this->leads($user)->whereNotNull('follow_up_date')->where('follow_up_date', '<', $today)->whereNotIn('lead_status', ['converted', 'closed'])->count(),
            'follow_up_today' => $this->leads($user)->where('follow_up_date', $today)->count(),
            'converted' => $this->leads($user)->where('is_converted', 'Yes')->count(),
            'unassigned' => $user->hasElevatedAccess() ? $this->leads($user)->whereNull('assigned_to')->count() : null,
        ];
    }
}
