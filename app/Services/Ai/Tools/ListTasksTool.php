<?php

namespace App\Services\Ai\Tools;

use App\Models\User;

class ListTasksTool extends CrmTool
{
    public function name(): string
    {
        return 'list_tasks';
    }

    public function description(): string
    {
        return 'List tasks, soonest due first. Agents see tasks assigned to or created by them; admins/managers see the whole team and can filter by assignee name.';
    }

    public function permission(): string
    {
        return 'tasks.view';
    }

    public function schema(): array
    {
        return ['type' => 'object', 'properties' => [
            'when' => ['type' => 'string', 'enum' => ['overdue', 'today', 'upcoming', 'all'], 'description' => 'Which tasks. Default: all open tasks.'],
            'include_completed' => ['type' => 'boolean', 'description' => 'Include completed/cancelled tasks. Default false.'],
            'assigned_to_name' => ['type' => 'string', 'description' => 'Team member name. Honoured only for admins/managers.'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-25. Default 10.'],
        ]];
    }

    public function run(User $user, array $input): array
    {
        $tz = optional($user->tenant)->timezone ?: config('app.timezone');
        $query = $this->tasks($user);

        if (empty($input['include_completed'])) {
            $query->whereNotIn('status', ['completed', 'cancelled']);
        }

        match ($input['when'] ?? 'all') {
            'overdue' => $query->whereNotNull('due_at')->where('due_at', '<', now()),
            'today' => $query->whereBetween('due_at', [now($tz)->startOfDay()->utc(), now($tz)->endOfDay()->utc()]),
            'upcoming' => $query->where('due_at', '>=', now()),
            default => null,
        };

        if (! empty($input['assigned_to_name']) && $user->hasElevatedAccess()) {
            $ids = User::where('tenant_id', $user->tenant_id)->where('name', 'like', '%'.addcslashes((string) $input['assigned_to_name'], '%_\\').'%')->pluck('id');
            $query->whereIn('assigned_to', $ids);
        }

        $total = (clone $query)->count();
        $rows = $query->with('assignee:id,name')->orderByRaw('due_at IS NULL')->orderBy('due_at')->limit($this->limit($input))->get();

        return [
            'total_matching' => $total,
            'tasks' => $rows->map(fn ($t) => [
                'id' => $t->id, 'title' => $t->title, 'status' => $t->status, 'priority' => $t->priority,
                'due' => optional($t->due_at)->timezone($tz)->format('Y-m-d H:i'),
                'assigned_to' => $t->assignee?->name,
                'overdue' => $t->due_at && $t->due_at->isPast() && ! in_array($t->status, ['completed', 'cancelled'], true),
            ])->all(),
        ];
    }
}
