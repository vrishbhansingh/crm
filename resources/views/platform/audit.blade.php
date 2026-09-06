@extends('layouts.platform')
@section('title','Audit log')
@section('heading','Platform audit log')
@section('content')
<div class="card mb-4"><div class="card-body">
<form method="get" action="{{ route('superadmin.audit.index') }}" class="form-row align-items-end">
<div class="col-md-4 mb-2"><label class="text-muted small mb-1">Company</label><select class="form-control" name="tenant_id"><option value="">All companies</option>@foreach($tenants as $t)<option value="{{ $t->id }}" @selected(request('tenant_id')==$t->id)>{{ $t->name }}</option>@endforeach</select></div>
<div class="col-md-4 mb-2"><label class="text-muted small mb-1">Event</label><select class="form-control" name="event"><option value="">All events</option>@foreach($events as $event)<option value="{{ $event }}" @selected(request('event')===$event)>{{ str_replace('.', ' ', ucfirst($event)) }}</option>@endforeach</select></div>
<div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Filter</button></div>
<div class="col-md-2 mb-2"><a class="btn btn-outline-secondary btn-block" href="{{ route('superadmin.audit.index') }}">Clear</a></div>
</form>
</div></div>
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table"><thead><tr><th>Time</th><th>Event</th><th>Company</th><th>Actor</th><th>Target user</th><th>IP</th><th>Details</th></tr></thead><tbody>
@forelse($logs as $log)
<tr>
<td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
<td><span class="badge badge-secondary">{{ str_replace('.', ' ', ucfirst($log->event)) }}</span></td>
<td>{{ $log->tenant?->name ?: '—' }}</td>
<td>{{ $log->actor?->email ?: 'System/signup' }}</td>
<td>{{ $log->targetUser?->email ?: '—' }}</td>
<td>{{ $log->ip_address ?: '—' }}</td>
<td style="max-width:260px;white-space:normal;word-break:break-word">
    @if($log->metadata)
        <small class="text-muted" title="{{ json_encode($log->metadata) }}">{{ \Illuminate\Support\Str::limit(json_encode($log->metadata), 80) }}</small>
    @else
        <small class="text-muted">—</small>
    @endif
</td>
</tr>
@empty
<tr><td colspan="7">No audit events match these filters.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $logs->links() }}</div>
</div></div>
@endsection
