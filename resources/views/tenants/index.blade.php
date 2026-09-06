@extends('layouts.platform')
@section('title','Companies')
@section('heading','Companies & registrations')
@section('content')
<div class="card mb-4"><div class="card-body">
<form method="get" action="{{ route('superadmin.tenants.index') }}" class="form-row align-items-end">
<div class="col-md-5 mb-2"><label class="text-muted small mb-1">Search</label><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Company name or contact email"></div>
<div class="col-md-3 mb-2"><label class="text-muted small mb-1">Approval status</label><select class="form-control" name="approval_status"><option value="">All</option>@foreach(['pending','approved','rejected'] as $status)<option value="{{ $status }}" @selected(request('approval_status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
<div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Filter</button></div>
<div class="col-md-2 mb-2"><a class="btn btn-outline-secondary btn-block" href="{{ route('superadmin.tenants.index') }}">Clear</a></div>
</form>
</div></div>
<div class="card mb-4"><div class="card-body">
<div class="d-flex justify-content-between"><div><h5>Create company</h5><p class="text-muted">Creates a central admin and fresh isolated database.</p></div><button class="btn btn-primary" data-toggle="collapse" data-target="#newCompany">New company</button></div>
<div class="collapse mt-3" id="newCompany"><form method="post" action="{{ route('superadmin.tenants.store') }}">@csrf<div class="form-row">
@foreach([['name','Company name','text'],['contact_email','Contact email','email'],['admin_name','Admin name','text'],['admin_email','Admin email','email'],['admin_phone','Admin phone','text'],['admin_password','Admin password','password']] as $field)<div class="col-md-4 mb-2"><label>{{ $field[1] }}</label><input class="form-control" type="{{ $field[2] }}" name="{{ $field[0] }}" placeholder="{{ $field[1] }}" {{ in_array($field[0],['name','admin_name','admin_email','admin_password'])?'required':'' }}></div>@endforeach
<div class="col-md-3 mb-2"><label>Plan</label><select class="form-control" name="plan"><option value="trial">Trial</option><option value="standard">Standard</option><option value="professional">Professional</option><option value="enterprise">Enterprise</option></select></div><div class="col-md-3 mb-2"><label>Timezone</label><input class="form-control" name="timezone" value="Asia/Kolkata" required></div><div class="col-md-2 mb-2"><label>Locale</label><input class="form-control" name="locale" value="en" required></div><div class="col-md-2 mb-2"><label>User limit</label><input class="form-control" type="number" min="1" name="max_users" placeholder="Unlimited"></div><div class="col-md-2 mb-2"><label>Trial ends</label><input class="form-control" type="date" name="trial_ends_at"></div></div><button class="btn btn-primary mt-2">Create &amp; provision</button></form></div>
</div></div>
@foreach($tenants as $tenant)
<div class="card mb-3"><div class="card-body">
<div class="d-flex justify-content-between flex-wrap" style="gap:10px">
    <div><h5 class="mb-1">{{ $tenant->name }}</h5><span class="text-muted" style="font-size:13px">{{ $tenant->contact_email ?: 'No contact email' }}</span></div>
    <div style="display:flex;gap:6px;align-items:flex-start">
        <span class="badge badge-{{ $tenant->approval_status==='approved'?'success':($tenant->approval_status==='pending'?'warning':'danger') }}">{{ ucfirst($tenant->approval_status) }}</span>
        <span class="badge badge-{{ $tenant->provision_status }}">DB {{ $tenant->provision_status }}</span>
    </div>
</div>
<div class="d-flex flex-wrap mt-3 mb-2" style="gap:22px 32px;padding:12px 0;border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9">
    <div><span style="display:block;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;color:#94a3b8;margin-bottom:2px">Users</span><span style="font-size:13.5px;color:#334155">1 Admin + {{ max($tenant->users_count - 1, 0) }} users</span></div>
    <div><span style="display:block;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;color:#94a3b8;margin-bottom:2px">Plan</span><span style="font-size:13.5px;color:#334155">{{ ucfirst($tenant->plan) }}</span></div>
    <div><span style="display:block;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;color:#94a3b8;margin-bottom:2px">Expires</span><span style="font-size:13.5px;color:#334155">{{ optional($tenant->trial_ends_at)->format('d M Y') ?: 'No expiry' }}</span></div>
    <div><span style="display:block;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;color:#94a3b8;margin-bottom:2px">Admin</span><span style="font-size:13.5px;color:#334155">{{ $tenant->admin?->email ?: 'Not assigned' }}</span></div>
    <div><span style="display:block;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;color:#94a3b8;margin-bottom:2px">Database</span><span style="font-size:13.5px;color:#334155">{{ $tenant->database_name ?: 'No database' }}</span></div>
</div>
@if($tenant->provision_error)<div class="alert alert-danger"><small>{{ $tenant->provision_error }}</small></div>@endif
<div class="d-flex flex-wrap" style="gap:8px"><a class="btn btn-sm btn-outline-primary" href="{{ route('superadmin.users.index',$tenant) }}">Manage users</a>
@if($tenant->approval_status==='pending')<form method="post" action="{{ route('superadmin.tenants.approve',$tenant) }}">@csrf<button class="btn btn-sm btn-success">Approve</button></form><button class="btn btn-sm btn-outline-danger" data-toggle="collapse" data-target="#reject{{ $tenant->id }}">Reject</button>@endif
<form method="post" action="{{ route('superadmin.tenants.provision',$tenant) }}">@csrf<button class="btn btn-sm btn-outline-secondary">{{ $tenant->provision_status==='ready'?'Re-provision safely':'Retry provisioning' }}</button></form>
@if($tenant->database_name)<form method="post" action="{{ route('superadmin.tenants.health',$tenant) }}">@csrf<button class="btn btn-sm btn-outline-secondary">Health check</button></form>@endif
<button class="btn btn-sm btn-outline-dark" data-toggle="collapse" data-target="#edit{{ $tenant->id }}">Edit settings</button>
</div>
<div class="collapse mt-2" id="reject{{ $tenant->id }}"><form method="post" action="{{ route('superadmin.tenants.reject',$tenant) }}" class="form-inline">@csrf<input class="form-control mr-2" name="rejection_reason" placeholder="Reason required" required><button class="btn btn-danger">Confirm rejection</button></form></div>
<div class="collapse mt-2" id="edit{{ $tenant->id }}"><hr><form method="post" action="{{ route('superadmin.tenants.update',$tenant) }}">@csrf @method('PUT')<input type="hidden" name="slug" value="{{ $tenant->slug }}"><input type="hidden" name="timezone" value="{{ $tenant->timezone }}"><input type="hidden" name="locale" value="{{ $tenant->locale }}"><div class="form-row"><div class="col-md-3"><input class="form-control" name="name" value="{{ $tenant->name }}" required></div><div class="col-md-3"><input class="form-control" type="email" name="contact_email" value="{{ $tenant->contact_email }}"></div><div class="col-md-2"><select class="form-control" name="plan">@foreach(['trial','standard','professional','enterprise'] as $plan)<option value="{{ $plan }}" @selected($tenant->plan===$plan)>{{ ucfirst($plan) }}</option>@endforeach</select></div><div class="col-md-2"><input class="form-control" type="date" name="trial_ends_at" value="{{ optional($tenant->trial_ends_at)->format('Y-m-d') }}"></div><div class="col-md-1"><input class="form-control" type="number" min="1" name="max_users" value="{{ $tenant->max_users }}"></div><div class="col-md-1"><select class="form-control" name="status"><option @selected($tenant->status==='Active')>Active</option><option @selected($tenant->status==='Inactive')>Inactive</option></select></div></div><button class="btn btn-sm btn-outline-primary mt-2">Save settings</button></form></div>
</div></div>
@endforeach
@endsection
