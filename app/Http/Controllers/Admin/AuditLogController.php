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

        return response()->json(['data' => $query->latest()->limit(500)->get()]);
    }
}
