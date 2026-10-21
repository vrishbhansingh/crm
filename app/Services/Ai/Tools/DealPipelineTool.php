<?php

namespace App\Services\Ai\Tools;

use App\Models\User;

class DealPipelineTool extends CrmTool
{
    public function name(): string
    {
        return 'deal_pipeline';
    }

    public function description(): string
    {
        return 'Deal counts and value per pipeline stage, plus open/won/lost totals, and optionally a list of the biggest open deals. Agents see only deals they own; admins/managers see all.';
    }

    public function permission(): string
    {
        return 'deals.view';
    }

    public function schema(): array
    {
        return ['type' => 'object', 'properties' => [
            'list_top_open' => ['type' => 'integer', 'description' => 'Also list this many largest open deals (0-15). Default 5.'],
        ]];
    }

    public function run(User $user, array $input): array
    {
        $byStage = $this->deals($user)->where('status', 'open')
            ->with('stage:id,name')->get()
            ->groupBy(fn ($d) => $d->stage?->name ?? 'Unknown')
            ->map(fn ($g) => ['deals' => $g->count(), 'value' => (float) $g->sum('amount')]);

        $top = max(0, min(15, (int) ($input['list_top_open'] ?? 5)));

        return [
            'scope' => $user->hasElevatedAccess() ? 'whole company' : 'your own deals',
            'open' => ['count' => $this->deals($user)->where('status', 'open')->count(), 'value' => (float) $this->deals($user)->where('status', 'open')->sum('amount')],
            'won' => ['count' => $this->deals($user)->where('status', 'won')->count(), 'value' => (float) $this->deals($user)->where('status', 'won')->sum('amount')],
            'lost' => ['count' => $this->deals($user)->where('status', 'lost')->count()],
            'open_by_stage' => $byStage->all(),
            'largest_open' => $top === 0 ? [] : $this->deals($user)->where('status', 'open')->with(['stage:id,name', 'owner:id,name'])
                ->orderByDesc('amount')->limit($top)->get()->map(fn ($d) => [
                    'id' => $d->id, 'name' => $d->name, 'amount' => (float) $d->amount, 'currency' => $d->currency,
                    'stage' => $d->stage?->name, 'owner' => $d->owner?->name, 'expected_close' => optional($d->expected_close_date)->toDateString() ?? $d->expected_close_date,
                ])->all(),
        ];
    }
}
