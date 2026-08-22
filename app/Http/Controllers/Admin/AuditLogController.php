<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuditLogController extends Controller
{
    public function index()
    {
        $tenantId = TenantContext::id();
        $users = User::when($tenantId !== null, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('name')->get(['id', 'name']);

        return view('audit.index', compact('users'));
    }

    public function data(Request $request)
    {
        $request->validate([
            'event' => ['nullable', Rule::in(['created', 'updated', 'deleted'])],
            'type' => ['nullable', 'string', 'max:120'],
            'actor_id' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = AuditLog::with('actor:id,name')
            ->when($request->filled('event'), fn ($q) => $q->where('event', $request->event))
            ->when($request->filled('type'), fn ($q) => $q->where('auditable_type', $request->type))
            ->when($request->filled('actor_id'), fn ($q) => $q->where('actor_id', $request->integer('actor_id')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to));

        $perPage = min(max((int) $request->input('per_page', 25), 5), 100);
        $paginator = $query->latest()->paginate($perPage);

        $data = collect($paginator->items())->map(function (AuditLog $log) {
            return array_merge($log->toArray(), [
                'changes' => $this->diff($log->old_values, $log->new_values),
            ]);
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }

    /**
     * Plain-language change list instead of two raw JSON blobs — a non-technical
     * Admin can read "Status changed from New to Contacted" but not a column-name
     * diff of two objects. Timestamp columns are noise (every update touches
     * updated_at) so they're dropped rather than shown as a "change".
     */
    private function diff(?array $old, ?array $new): array
    {
        $old ??= [];
        $new ??= [];
        $ignored = ['created_at', 'updated_at'];
        $keys = array_diff(array_unique([...array_keys($old), ...array_keys($new)]), $ignored);

        $changes = [];
        foreach ($keys as $key) {
            $before = $old[$key] ?? null;
            $after = $new[$key] ?? null;
            if ($before === $after) {
                continue;
            }
            $changes[] = [
                'field' => ucwords(str_replace('_', ' ', $key)),
                'from' => $this->formatDiffValue($before),
                'to' => $this->formatDiffValue($after),
            ];
        }

        return $changes;
    }

    private function formatDiffValue(mixed $value): string
    {
        return match (true) {
            $value === null => '—',
            is_bool($value) => $value ? 'Yes' : 'No',
            is_array($value) => json_encode($value),
            default => (string) $value,
        };
    }
}
