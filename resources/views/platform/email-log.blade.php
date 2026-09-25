@extends('layouts.platform')
@section('title','Email Log')
@section('heading','Email Log')
@section('content')

<style>
    .status-pill { display: inline-flex; align-items: center; gap: 5px; }
    .status-pill .dot { width: 7px; height: 7px; border-radius: 50%; }
    .status-queued .dot, .status-sending .dot { background: #f59e0b; }
    .status-sent .dot { background: #16a34a; }
    .status-failed .dot { background: #dc2626; }
    .status-stuck .dot { background: #dc2626; }
    .status-queued, .status-sending { color: #92400e; }
    .status-sent { color: #166534; }
    .status-failed, .status-stuck { color: #991b1b; }
    .filter-bar { display: flex; flex-wrap: wrap; gap: 10px; align-items: end; margin-bottom: 4px; }
    .filter-bar .field { display: flex; flex-direction: column; gap: 4px; }
    .filter-bar label { margin: 0; }
    .filter-bar select, .filter-bar input { min-width: 150px; }
    .error-note { max-width: 320px; white-space: normal; font-size: 12px; color: #991b1b; }
    .subject-cell { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>

<div class="stat-grid mb-4">
    <div class="card stat"><span class="text-muted">Sent</span><strong>{{ number_format($stats['sent']) }}</strong></div>
    <div class="card stat"><span class="text-muted">Failed</span><strong>{{ number_format($stats['failed']) }}</strong></div>
    <div class="card stat"><span class="text-muted">In flight</span><strong>{{ number_format($stats['in_flight']) }}</strong></div>
    <div class="card stat"><span class="text-muted">Stuck (&gt;{{ \App\Models\EmailLog::STUCK_AFTER_MINUTES }}m)</span><strong style="{{ $stats['stuck'] > 0 ? 'color:#dc2626' : '' }}">{{ number_format($stats['stuck']) }}</strong></div>
</div>

<div class="card"><div class="card-body">
    <form method="get" class="filter-bar">
        <div class="field">
            <label>Status</label>
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach(['queued'=>'Queued','sending'=>'Sending','sent'=>'Sent','failed'=>'Failed','stuck'=>'Stuck'] as $val => $label)
                    <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label>Type</label>
            <select name="type" class="form-control" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach(\App\Models\EmailLog::TYPES as $val => $label)
                    <option value="{{ $val }}" {{ ($filters['type'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label>Company</label>
            <select name="tenant_id" class="form-control" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="platform" {{ ($filters['tenant_id'] ?? '') === 'platform' ? 'selected' : '' }}>Platform (no company)</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ (string)($filters['tenant_id'] ?? '') === (string)$tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label>Search</label>
            <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Email or subject">
        </div>
        <div class="field">
            <label>From</label>
            <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="field">
            <label>To</label>
            <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="field">
            <button class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
            @if(array_filter($filters))
                <a href="{{ route('superadmin.email_log.index') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </div>
    </form>
</div></div>

<div class="card"><div class="card-body">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>To</th>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Queued</th>
                    <th>Sent</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php
                        $isStuck = in_array($log->status, ['queued','sending']) && $log->queued_at && $log->queued_at->lt(now()->subMinutes(\App\Models\EmailLog::STUCK_AFTER_MINUTES));
                        $statusClass = $isStuck ? 'stuck' : $log->status;
                        $statusLabel = $isStuck ? 'Stuck' : ucfirst($log->status);
                    @endphp
                    <tr>
                        <td>{{ $log->to_email }}</td>
                        <td class="subject-cell" title="{{ $log->subject }}">{{ $log->subject ?: '—' }}</td>
                        <td>{{ \App\Models\EmailLog::TYPES[$log->type] ?? $log->type }}</td>
                        <td>{{ $log->tenant?->name ?? 'Platform' }}</td>
                        <td>
                            <span class="status-pill status-{{ $statusClass }}"><span class="dot"></span> {{ $statusLabel }}</span>
                            @if($log->error)
                                <div class="error-note">{{ \Illuminate\Support\Str::limit($log->error, 120) }}</div>
                            @endif
                        </td>
                        <td>{{ $log->queued_at?->format('d M, H:i') ?? '—' }}</td>
                        <td>{{ $log->sent_at?->format('d M, H:i') ?? '—' }}</td>
                        <td>
                            @if($log->type === 'campaign' && ($log->status === 'failed' || $isStuck))
                                <form method="post" action="{{ route('superadmin.email_log.retry', $log) }}" onsubmit="return confirm('Re-queue this email as a fresh attempt?');">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-rotate-right"></i> Retry</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No emails match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $logs->links() }}</div>
</div></div>

@endsection
