@extends('layouts.platform')
@section('title','Super Admin overview')
@section('heading','Super Admin overview')
@section('content')
<div class="stat-grid mb-4">
@foreach(['total'=>'Companies','active'=>'Active','pending'=>'Pending approval','rejected'=>'Rejected','users'=>'Tenant users'] as $key=>$label)
<div class="card stat"><span class="text-muted">{{ $label }}</span><strong>{{ $stats[$key] }}</strong></div>
@endforeach
</div>
<div class="card"><div class="card-body">
<div class="d-flex justify-content-between"><h5>Latest registrations</h5><a href="{{ route('superadmin.tenants.index') }}">Manage all</a></div>
<div class="table-responsive"><table class="table"><thead><tr><th>Company</th><th>Contact</th><th>Users</th><th>Approval</th><th>Database</th><th>Expires</th></tr></thead><tbody>
@forelse($recentSignups as $tenant)
<tr><td><a href="{{ route('superadmin.users.index',$tenant) }}">{{ $tenant->name }}</a><br><small>{{ $tenant->created_at->format('d M Y H:i') }}</small></td><td>{{ $tenant->contact_email ?: '—' }}</td><td>{{ $tenant->users_count }}</td><td>{{ ucfirst($tenant->approval_status) }}</td><td><span class="badge badge-{{ $tenant->provision_status }}">{{ $tenant->provision_status }}</span></td><td>{{ optional($tenant->trial_ends_at)->format('d M Y') ?: 'No expiry' }}</td></tr>
@empty<tr><td colspan="6">No companies yet.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
