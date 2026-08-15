@extends('layouts.platform')
@section('title','Super Admin overview')
@section('heading','Super Admin overview')
@section('content')
<div class="stat-grid mb-4">
@foreach(['total'=>'Companies','active'=>'Active','pending'=>'Pending approval','expired'=>'Expired','expiring'=>'Expiring in 14 days','database_issues'=>'Database issues','users'=>'Tenant users'] as $key=>$label)
<div class="card stat"><span class="text-muted">{{ $label }}</span><strong>{{ $stats[$key] }}</strong></div>
@endforeach
</div>
@if($needsAttention->isNotEmpty())
<div class="card mb-4 border-warning"><div class="card-body">
<h5 class="text-warning">Needs attention ({{ $needsAttention->count() }})</h5>
<div class="table-responsive"><table class="table"><thead><tr><th>Company</th><th>Approval</th><th>Database</th><th>Action</th></tr></thead><tbody>
@foreach($needsAttention as $tenant)
<tr>
<td>{{ $tenant->name }}<br><small class="text-muted">{{ $tenant->contact_email ?: '—' }}</small></td>
<td><span class="badge badge-{{ $tenant->approval_status==='pending'?'warning':'danger' }}">{{ ucfirst($tenant->approval_status) }}</span></td>
<td><span class="badge badge-{{ $tenant->provision_status }}">{{ $tenant->provision_status }}</span>@if($tenant->provision_error)<br><small class="text-danger">{{ \Illuminate\Support\Str::limit($tenant->provision_error, 60) }}</small>@endif</td>
<td class="d-flex flex-wrap" style="gap:6px">
@if($tenant->approval_status==='pending')<form method="post" action="{{ route('superadmin.tenants.approve',$tenant) }}">@csrf<button class="btn btn-sm btn-success">Approve</button></form>@endif
@if(in_array($tenant->provision_status,['pending','failed']))<form method="post" action="{{ route('superadmin.tenants.provision',$tenant) }}">@csrf<button class="btn btn-sm btn-outline-secondary">Retry provisioning</button></form>@endif
<a class="btn btn-sm btn-outline-primary" href="{{ route('superadmin.tenants.index') }}">Details</a>
</td>
</tr>
@endforeach
</tbody></table></div>
</div></div>
@endif
<div class="card"><div class="card-body">
<div class="d-flex justify-content-between"><h5>Latest registrations</h5><a href="{{ route('superadmin.tenants.index') }}">Manage all</a></div>
<div class="table-responsive"><table class="table"><thead><tr><th>Company</th><th>Contact</th><th>Users</th><th>Approval</th><th>Database</th><th>Expires</th></tr></thead><tbody>
@forelse($recentSignups as $tenant)
<tr><td><a href="{{ route('superadmin.users.index',$tenant) }}">{{ $tenant->name }}</a><br><small>{{ $tenant->created_at->format('d M Y H:i') }}</small></td><td>{{ $tenant->contact_email ?: '—' }}</td><td>1 Admin + {{ max($tenant->users_count - 1, 0) }} users</td><td>{{ ucfirst($tenant->approval_status) }}</td><td><span class="badge badge-{{ $tenant->provision_status }}">{{ $tenant->provision_status }}</span></td><td>{{ optional($tenant->trial_ends_at)->format('d M Y') ?: 'No expiry' }}</td></tr>
@empty<tr><td colspan="6">No companies yet.</td></tr>@endforelse
</tbody></table></div></div></div>
<div class="card mt-4"><div class="card-body"><div class="d-flex justify-content-between"><h5>Recent tenant activity</h5><a href="{{ route('superadmin.audit.index') }}">Full audit log</a></div><div class="table-responsive"><table class="table"><thead><tr><th>Time</th><th>Event</th><th>Company</th><th>Actor</th><th>Target user</th></tr></thead><tbody>
@forelse($recentActivity as $activity)<tr><td>{{ $activity->created_at->format('d M Y H:i') }}</td><td>{{ str_replace('.', ' ', ucfirst($activity->event)) }}</td><td>{{ $activity->tenant?->name ?: '—' }}</td><td>{{ $activity->actor?->email ?: 'System/signup' }}</td><td>{{ $activity->targetUser?->email ?: '—' }}</td></tr>@empty<tr><td colspan="5">No tenant activity recorded yet.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
