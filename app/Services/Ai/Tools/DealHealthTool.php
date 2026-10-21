<?php

namespace App\Services\Ai\Tools;

use App\Models\DealStageHistory;
use App\Models\User;
use Carbon\Carbon;

class DealHealthTool extends CrmTool
{
    public function name(): string
    {
        return 'deal_health';
    }

    public function description(): string
    {
        return 'Which open deals are at risk (stuck in a stage for a long time, or past their expected close date) and a probability-weighted revenue forecast by expected close month. Agents see only their own deals; admins/managers see the whole company.';
    }

    public function permission(): string
    {
        return 'deals.view';
    }

    public function schema(): array
    {
        return ['type' => 'object', 'properties' => [
            'stalled_after_days' => ['type' => 'integer', 'description' => 'A deal is "stalled" after this many days without moving stage, 3-180. Default 14.'],
        ]];
    }

    public function run(User $user, array $input): array
    {
        $staleDays = max(3, min(180, (int) ($input['stalled_after_days'] ?? 14)));
        $tz = optional($user->tenant)->timezone ?: config('app.timezone');
        $today = now($tz)->startOfDay();

        $deals = $this->deals($user)->where('status', 'open')->with(['stage:id,name,probability', 'owner:id,name'])->limit(500)->get();
        $lastMove = DealStageHistory::whereIn('deal_id', $deals->pluck('id'))->selectRaw('deal_id, MAX(created_at) as last_move')->groupBy('deal_id')->pluck('last_move', 'deal_id');

        $stalled = [];
        $overdue = [];
        $weightedTotal = 0.0;
        $byMonth = [];

        foreach ($deals as $deal) {
            $since = Carbon::parse($lastMove[$deal->id] ?? $deal->created_at);
            $idleDays = (int) $since->diffInDays(now());
            $amount = (float) $deal->amount;
            $weighted = $amount * ((int) ($deal->stage?->probability ?? 0)) / 100;
            $weightedTotal += $weighted;

            $closeDate = $deal->expected_close_date ? Carbon::parse($deal->expected_close_date, $tz)->startOfDay() : null;
            $monthKey = $closeDate ? $closeDate->format('Y-m') : 'no_close_date';
            $byMonth[$monthKey] = ($byMonth[$monthKey] ?? 0) + $weighted;

            $row = ['id' => $deal->id, 'name' => $deal->name, 'amount' => $amount, 'currency' => $deal->currency, 'stage' => $deal->stage?->name,
                'owner' => $deal->owner?->name, 'days_in_stage' => $idleDays, 'expected_close' => $closeDate?->toDateString()];

            if ($idleDays >= $staleDays) {
                $stalled[] = $row;
            }
            if ($closeDate && $closeDate->lt($today)) {
                $overdue[] = $row + ['days_past_close_date' => (int) $closeDate->diffInDays($today)];
            }
        }

        $byAmount = fn (array $rows) => collect($rows)->sortByDesc('amount')->take(10)->values()->all();
        ksort($byMonth);

        return [
            'scope' => $user->hasElevatedAccess() ? 'whole company' : 'your own deals',
            'open_deals_considered' => $deals->count(),
            'stalled_after_days' => $staleDays,
            'stalled_count' => count($stalled),
            'stalled_top' => $byAmount($stalled),
            'past_expected_close_count' => count($overdue),
            'past_expected_close_top' => $byAmount($overdue),
            'weighted_forecast_total' => round($weightedTotal, 2),
            'weighted_forecast_by_expected_close_month' => array_map(fn ($v) => round($v, 2), $byMonth),
            'note' => 'Weighted forecast = deal amount x its stage win-probability. Deals with no expected close date are grouped under no_close_date.',
        ];
    }
}
