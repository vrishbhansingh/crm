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
        $data['due_at'] = $this->toUtc($data['due_at'] ?? null, $task->tenant_id);
        $data['remind_at'] = $this->toUtc($data['remind_at'] ?? null, $task->tenant_id);
        $this->assertRelatedRecord($data, $task->tenant_id);
        $data['completed_at'] = $data['status'] === 'completed' ? ($task->completed_at ?? now()) : null;
        $task->update($data);

        return response()->json(['status' => true, 'message' => 'Task updated successfully']);
    }

    public function complete(int $id)
    {
        $task = $this->findEditable($id);
        $task->update(['status' => 'completed', 'completed_at' => now()]);

        return response()->json(['status' => true, 'message' => 'Task completed']);
    }

    public function destroy(int $id)
    {
        $this->findEditable($id)->delete();

        return response()->json(['status' => true, 'message' => 'Task deleted']);
    }

    private function validatedData(Request $request, int $tenantId): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $tenantId)],
            'related_type' => ['nullable', Rule::in(array_keys(self::RELATED_MODELS))],
            'related_id' => ['nullable', 'integer', 'required_with:related_type'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'status' => ['required', Rule::in(['todo', 'in_progress', 'completed', 'cancelled'])],
            'due_at' => ['nullable', 'date'],
            'remind_at' => ['nullable', 'date', 'before_or_equal:due_at'],
        ]);
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
