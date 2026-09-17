<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    private const RELATED_MODELS = [
        'lead' => Lead::class,
        'deal' => Deal::class,
        'company' => Company::class,
        'contact' => Contact::class,
        'order' => Order::class,
    ];

    public function index()
    {
        $tenantId = TenantContext::id();
        $users = User::where('status', 'Active')
            ->when($tenantId !== null, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')->get(['id', 'name']);

        return view('tasks.index', compact('users'));
    }

    public function data(Request $request)
    {
        $request->validate([
            'status' => ['nullable', Rule::in(['todo', 'in_progress', 'completed', 'cancelled'])],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assigned_to' => ['nullable', 'integer'],
            'due' => ['nullable', Rule::in(['today', 'overdue', 'upcoming'])],
        ]);

        $query = $this->visibleQuery()->with(['assignee:id,name', 'creator:id,name']);
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));
        $query->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority));
        $query->when($request->filled('assigned_to'), fn ($q) => $q->where('assigned_to', $request->integer('assigned_to')));
        $query->when($request->due === 'today', fn ($q) => $q->whereDate('due_at', today()));
        $query->when($request->due === 'overdue', fn ($q) => $q->where('due_at', '<', now())->whereNotIn('status', ['completed', 'cancelled']));
        $query->when($request->due === 'upcoming', fn ($q) => $q->where('due_at', '>=', now())->whereNotIn('status', ['completed', 'cancelled']));

        $tasks = $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->latest('id')
            ->get()
            ->map(fn (Task $task) => array_merge($task->toArray(), [
                'related_label' => $this->relatedLabel($task),
                'is_overdue' => $task->due_at && $task->due_at->isPast() && ! in_array($task->status, ['completed', 'cancelled'], true),
                'is_blocked' => $task->isBlocked(),
                'checklist_total' => count($task->checklist ?? []),
                'checklist_done' => count(array_filter($task->checklist ?? [], fn ($item) => $item['done'] ?? false)),
            ]));

        return response()->json(['data' => $tasks]);
    }

    public function assignableUsers()
    {
        $tenantId = TenantContext::id();
        $users = User::where('status', 'Active')
            ->when($tenantId !== null, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')->get(['id', 'name']);

        return response()->json(['users' => $users]);
    }

    public function relatedOptions(string $type)
    {
        abort_unless(isset(self::RELATED_MODELS[$type]), 404);
        $user = Auth::guard('web')->user();
        $query = self::RELATED_MODELS[$type]::query();

        if (! $user->hasElevatedAccess()) {
            match ($type) {
                'lead' => $query->where('assigned_to', $user->id),
                'deal' => $query->where('owner_id', $user->id),
                'company', 'contact' => $query->where('owner_id', $user->id),
                'order' => $query->where('user_id', $user->id),
            };
        }

        $records = $query->latest('id')->limit(100)->get()->map(fn ($record) => [
            'id' => $record->id,
            'label' => $type === 'order' ? $record->order_number : $record->name,
        ]);

        return response()->json(['data' => $records]);
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant before creating a task.');

        $data = $this->validatedData($request, $tenantId);
        $data['tenant_id'] = $tenantId;
        $data['created_by'] = Auth::guard('web')->id();
        $data['due_at'] = $this->toUtc($data['due_at'] ?? null, $tenantId);
        $data['remind_at'] = $this->toUtc($data['remind_at'] ?? null, $tenantId);
        $this->assertRelatedRecord($data, $tenantId);

        $task = Task::create($data);

        return response()->json(['status' => true, 'message' => 'Task created successfully', 'id' => $task->id]);
    }

    public function update(Request $request, int $id)
    {
        $task = $this->findEditable($id);
        $data = $this->validatedData($request, $task->tenant_id);
        abort_if(($data['depends_on_task_id'] ?? null) === $task->id, 422, 'A task cannot depend on itself.');
        $data['due_at'] = $this->toUtc($data['due_at'] ?? null, $task->tenant_id);
        $data['remind_at'] = $this->toUtc($data['remind_at'] ?? null, $task->tenant_id);
        $this->assertRelatedRecord($data, $task->tenant_id);

        $completingNow = $data['status'] === 'completed' && $task->status !== 'completed';
        if ($completingNow) {
            $this->assertNotBlocked($task);
        }

        $data['completed_at'] = $data['status'] === 'completed' ? ($task->completed_at ?? now()) : null;
        $task->update($data);

        if ($completingNow) {
            $this->spawnNextOccurrence($task);
        }

        return response()->json(['status' => true, 'message' => 'Task updated successfully']);
    }

    public function complete(int $id)
    {
        $task = $this->findEditable($id);
        $this->assertNotBlocked($task);
        $task->update(['status' => 'completed', 'completed_at' => now()]);
        $this->spawnNextOccurrence($task);

        return response()->json(['status' => true, 'message' => 'Task completed']);
    }

    public function destroy(int $id)
    {
        $this->findEditable($id)->delete();

        return response()->json(['status' => true, 'message' => 'Task deleted']);
    }

    public function checklistToggle(Request $request, int $id)
    {
        $request->validate(['index' => ['required', 'integer', 'min:0']]);
        $task = $this->findEditable($id);
        $checklist = $task->checklist ?? [];
        $index = $request->integer('index');
        abort_unless(array_key_exists($index, $checklist), 404);

        $checklist[$index]['done'] = ! ($checklist[$index]['done'] ?? false);
        $task->update(['checklist' => $checklist]);

        return response()->json(['status' => true, 'checklist' => $checklist]);
    }

    public function bulkCreate(Request $request)
    {
        $tenantId = TenantContext::id();
        abort_if($tenantId === null, 422, 'Select a tenant before creating tasks.');

        $validated = $request->validate([
            'related_type' => ['required', Rule::in(array_keys(self::RELATED_MODELS))],
            'related_ids' => ['required', 'array', 'min:1'],
            'related_ids.*' => ['integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $tenantId)],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'due_at' => ['nullable', 'date'],
            'activity_type' => ['nullable', Rule::in(['task', 'call', 'meeting'])],
        ]);

        $relatedIds = array_values(array_unique($validated['related_ids']));
        $model = self::RELATED_MODELS[$validated['related_type']];
        $found = $model::withoutGlobalScope('tenant')->where('tenant_id', $tenantId)->whereIn('id', $relatedIds)->count();
        abort_if($found !== count($relatedIds), 422, 'One or more selected records are invalid.');

        $dueAt = $this->toUtc($validated['due_at'] ?? null, $tenantId);
        $createdBy = Auth::guard('web')->id();
        $created = 0;

        foreach ($relatedIds as $relatedId) {
            Task::create([
                'tenant_id' => $tenantId,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'created_by' => $createdBy,
                'related_type' => $validated['related_type'],
                'related_id' => $relatedId,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'priority' => $validated['priority'],
                'status' => 'todo',
                'due_at' => $dueAt,
                'activity_type' => $validated['activity_type'] ?? 'task',
            ]);
            $created++;
        }

        return response()->json(['status' => true, 'message' => "{$created} task(s) created."]);
    }

    public function workload()
    {
        return view('tasks.workload');
    }

    public function workloadData()
    {
        $tenantId = TenantContext::id();
        $users = User::where('status', 'Active')
            ->when($tenantId !== null, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')->get(['id', 'name']);

        $tasks = $this->visibleQuery()->get(['id', 'assigned_to', 'status', 'due_at', 'completed_at']);

        $rows = $users->map(function (User $user) use ($tasks) {
            $mine = $tasks->where('assigned_to', $user->id);
            $openMine = $mine->whereNotIn('status', ['completed', 'cancelled']);

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'open' => $openMine->count(),
                'overdue' => $openMine->filter(fn ($t) => $t->due_at && $t->due_at->isPast())->count(),
                'due_today' => $openMine->filter(fn ($t) => $t->due_at && $t->due_at->isToday())->count(),
                'completed_this_week' => $mine->where('status', 'completed')
                    ->filter(fn ($t) => $t->completed_at && $t->completed_at->isAfter(now()->startOfWeek()))->count(),
            ];
        })->values();

        $unassigned = $tasks->whereNull('assigned_to')->whereNotIn('status', ['completed', 'cancelled'])->count();

        return response()->json(['data' => $rows, 'unassigned' => $unassigned]);
    }

    private function assertNotBlocked(Task $task): void
    {
        abort_if($task->isBlocked(), 422, 'Complete the blocking task first.');
    }

    /**
     * Recurring tasks don't reschedule in place — completing one spawns the
     * next occurrence as a fresh row, so the completed record stays an
     * honest history entry instead of silently jumping to a future date.
     */
    private function spawnNextOccurrence(Task $task): void
    {
        if (! $task->recurrence_rule || ! $task->due_at) {
            return;
        }

        $interval = max(1, $task->recurrence_interval ?? 1);
        $nextDue = match ($task->recurrence_rule) {
            'daily' => $task->due_at->copy()->addDays($interval),
            'weekly' => $task->due_at->copy()->addWeeks($interval),
            'monthly' => $task->due_at->copy()->addMonthsNoOverflow($interval),
            default => null,
        };

        if (! $nextDue) {
            return;
        }

        if ($task->recurrence_end_date && $nextDue->toDateString() > $task->recurrence_end_date->toDateString()) {
            return;
        }

        $remindOffsetSeconds = $task->remind_at ? $task->due_at->diffInSeconds($task->remind_at, false) : null;

        Task::create([
            'tenant_id' => $task->tenant_id,
            'assigned_to' => $task->assigned_to,
            'created_by' => $task->created_by,
            'related_type' => $task->related_type,
            'related_id' => $task->related_id,
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'status' => 'todo',
            'due_at' => $nextDue,
            'remind_at' => $remindOffsetSeconds !== null ? $nextDue->copy()->addSeconds($remindOffsetSeconds) : null,
            'activity_type' => $task->activity_type,
            'activity_details' => $task->activity_details,
            'recurrence_rule' => $task->recurrence_rule,
            'recurrence_interval' => $task->recurrence_interval,
            'recurrence_end_date' => $task->recurrence_end_date,
            'recurrence_parent_id' => $task->recurrence_parent_id ?? $task->id,
        ]);
    }

    private function validatedData(Request $request, int $tenantId): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $tenantId)],
            'related_type' => ['nullable', Rule::in(array_keys(self::RELATED_MODELS))],
            'related_id' => ['nullable', 'integer', 'required_with:related_type'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'status' => ['required', Rule::in(['todo', 'in_progress', 'completed', 'cancelled'])],
            'due_at' => ['nullable', 'date'],
            'remind_at' => ['nullable', 'date', 'before_or_equal:due_at'],
            'activity_type' => ['nullable', Rule::in(['task', 'call', 'meeting'])],
            'activity_details' => ['nullable', 'array'],
            'checklist' => ['nullable', 'array'],
            'checklist.*.text' => ['required_with:checklist', 'string', 'max:255'],
            'checklist.*.done' => ['nullable', 'boolean'],
            'recurrence_rule' => ['nullable', Rule::in(['daily', 'weekly', 'monthly'])],
            'recurrence_interval' => ['nullable', 'integer', 'min:1', 'max:365'],
            'recurrence_end_date' => ['nullable', 'date'],
            'depends_on_task_id' => ['nullable', Rule::exists('tasks', 'id')->where('tenant_id', $tenantId)],
        ]);

        $data['activity_type'] = $data['activity_type'] ?? 'task';

        return $data;
    }

    private function visibleQuery()
    {
        $user = Auth::guard('web')->user();
        $query = Task::query();

        if (! $user->hasElevatedAccess() && ! $user->hasRole('Support')) {
            $query->where(fn ($q) => $q->where('assigned_to', $user->id)->orWhere('created_by', $user->id));
        }

        return $query;
    }

    private function findEditable(int $id): Task
    {
        $user = Auth::guard('web')->user();
        $query = Task::query();

        if (! $user->hasElevatedAccess()) {
            $query->where(fn ($q) => $q->where('assigned_to', $user->id)->orWhere('created_by', $user->id));
        }

        return $query->findOrFail($id);
    }

    private function assertRelatedRecord(array $data, int $tenantId): void
    {
        if (empty($data['related_type'])) {
            return;
        }

        $model = self::RELATED_MODELS[$data['related_type']];
        abort_unless($model::withoutGlobalScope('tenant')->where('tenant_id', $tenantId)->whereKey($data['related_id'])->exists(), 422, 'The linked CRM record is invalid.');
    }

    private function relatedLabel(Task $task): ?string
    {
        if (! $task->related_type || ! isset(self::RELATED_MODELS[$task->related_type])) {
            return null;
        }

        $record = self::RELATED_MODELS[$task->related_type]::withoutGlobalScope('tenant')->find($task->related_id);

        return match ($task->related_type) {
            'order' => $record?->order_number,
            default => $record?->name,
        };
    }

    private function toUtc(?string $value, int $tenantId): ?Carbon
    {
        if (! $value) {
            return null;
        }

        $timezone = Tenant::find($tenantId)?->timezone ?? config('app.timezone');

        return Carbon::parse($value, $timezone)->utc();
    }
}
