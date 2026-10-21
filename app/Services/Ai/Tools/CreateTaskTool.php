<?php

namespace App\Services\Ai\Tools;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Services\MailConfigurator;
use Carbon\Carbon;

class CreateTaskTool extends WriteTool
{
    public function name(): string
    {
        return 'create_task';
    }

    public function description(): string
    {
        return 'Propose creating a task (optionally linked to a lead). This only PREPARES the task; the user must confirm before it is created. Tasks are assigned to the user themselves; admins/managers may name another team member.';
    }

    public function permission(): string
    {
        return 'tasks.create';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'description' => 'Short task title.'],
                'due' => ['type' => 'string', 'description' => 'Due date/time in the company\'s local time: YYYY-MM-DD or YYYY-MM-DD HH:MM. Optional.'],
                'priority' => ['type' => 'string', 'enum' => ['low', 'medium', 'high', 'urgent'], 'description' => 'Default medium.'],
                'lead_id' => ['type' => 'integer', 'description' => 'Link the task to this lead. Optional.'],
                'description' => ['type' => 'string', 'description' => 'Extra detail. Optional.'],
                'assignee_name' => ['type' => 'string', 'description' => 'Team member to assign to. Admins/managers only; ignored for agents.'],
            ],
            'required' => ['title'],
        ];
    }

    public function proposal(User $user, array $input): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '' || mb_strlen($title) > 255) {
            return ['error' => 'The task needs a title (max 255 characters).'];
        }

        $priority = $input['priority'] ?? 'medium';
        if (! in_array($priority, ['low', 'medium', 'high', 'urgent'], true)) {
            return ['error' => 'Priority must be low, medium, high or urgent.'];
        }

        $due = null;
        if (! empty($input['due'])) {
            $due = $this->parseDue((string) $input['due'], $user);
            if (! $due) {
                return ['error' => 'I couldn\'t read that due date. Use YYYY-MM-DD or YYYY-MM-DD HH:MM.'];
            }
        }

        $lead = null;
        if (! empty($input['lead_id'])) {
            $lead = $this->leads($user)->find((int) $input['lead_id']);
            if (! $lead) {
                return ['error' => 'No such lead, or it is not assigned to you.'];
            }
        }

        $assignee = $this->resolveAssignee($user, $input);
        if (is_string($assignee)) {
            return ['error' => $assignee];
        }

        $summary = 'Create '.$priority.'-priority task “'.$title.'”'
            .($due ? ' due '.$due->timezone($this->tz($user))->format('Y-m-d H:i') : '')
            .($lead ? ', linked to lead #'.($lead->lead_number ?: $lead->id).' ('.$lead->name.')' : '')
            .', assigned to '.($assignee->id === $user->id ? 'you' : $assignee->name);

        return ['summary' => $summary];
    }

    public function execute(User $user, array $input): string
    {
        $this->revalidate($user, $input);

        $assignee = $this->resolveAssignee($user, $input);
        $lead = ! empty($input['lead_id']) ? $this->leads($user)->findOrFail((int) $input['lead_id']) : null;

        $task = Task::create([
            'tenant_id' => $user->tenant_id,
            'assigned_to' => $assignee->id,
            'created_by' => $user->id,
            'related_type' => $lead ? 'lead' : null,
            'related_id' => $lead?->id,
            'title' => trim($input['title']),
            'description' => isset($input['description']) ? trim((string) $input['description']) ?: null : null,
            'priority' => $input['priority'] ?? 'medium',
            'status' => 'todo',
            'activity_type' => 'task',
            'due_at' => ! empty($input['due']) ? $this->parseDue((string) $input['due'], $user) : null,
        ]);

        if ($assignee->id !== $user->id) {
            try {
                app(MailConfigurator::class)->configureFor($assignee->tenant);
                $assignee->notify(new TaskAssigned($task, $user, 0));
            } catch (\Throwable $exception) {
                report($exception); // the task is created; a failed notification must not undo that
            }
        }

        return 'Task “'.$task->title.'” created'.($assignee->id === $user->id ? '.' : ' and assigned to '.$assignee->name.'.');
    }

    /** @return User|string The assignee, or an error message. */
    private function resolveAssignee(User $user, array $input): User|string
    {
        $name = trim((string) ($input['assignee_name'] ?? ''));
        if ($name === '' || ! $user->hasElevatedAccess()) {
            return $user;
        }

        $matches = User::where('tenant_id', $user->tenant_id)->where('status', 'Active')
            ->where('name', 'like', '%'.addcslashes($name, '%_\\').'%')->get();

        return match (true) {
            $matches->isEmpty() => 'I couldn\'t find an active team member called "'.$name.'".',
            $matches->count() > 1 => 'More than one person matches "'.$name.'": '.$matches->pluck('name')->implode(', ').'. Which one?',
            default => $matches->first(),
        };
    }

    private function parseDue(string $value, User $user): ?Carbon
    {
        $value = trim($value);
        foreach (['Y-m-d H:i', 'Y-m-d'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $value, $this->tz($user));
                if ($parsed !== false && $parsed->format($format) === $value) {
                    return $format === 'Y-m-d' ? $parsed->setTime(9, 0)->utc() : $parsed->utc();
                }
            } catch (\Throwable) {
            }
        }

        return null;
    }

    private function tz(User $user): string
    {
        return optional($user->tenant)->timezone ?: config('app.timezone');
    }
}
