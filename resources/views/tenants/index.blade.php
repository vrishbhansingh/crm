@extends('layouts.platform')
@section('title','Companies')
@section('heading','Companies & registrations')
@section('content')

<style>
    .filter-card .form-control { height: 42px; }

    .tenant-card { padding: 0; overflow: hidden; }
    .tenant-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; padding: 20px 24px 0; }
    .tenant-name { font-size: 16.5px; font-weight: 700; color: var(--ink); margin: 0 0 3px; }
    .tenant-email { font-size: 12.5px; color: var(--muted); }
    .tenant-badges { display: flex; gap: 6px; flex-shrink: 0; }

    .tenant-stats { display: flex; flex-wrap: wrap; gap: 20px 32px; padding: 16px 24px; margin-top: 14px; background: #fafbff; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
    .tenant-stat-label { display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; margin-bottom: 3px; }
    .tenant-stat-value { font-size: 13.5px; color: #334155; font-weight: 600; }

    .tenant-actions { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; padding: 14px 24px 20px; }
    .tenant-actions .action-group { display: flex; flex-wrap: wrap; gap: 8px; }
    .tenant-provision-error { margin: 0 24px 16px; }
    .tenant-reject-form { padding: 0 24px 20px; }

    /* Bootstrap's own .modal-backdrop/.fade transitions already do the
       heavy lifting; this just keeps every modal in this page visually
       consistent with the card language above instead of the framework's
       stock plain-white-corners look. */
    .modal-content { border: none; border-radius: 14px; overflow: hidden; }
    .modal-header { border-bottom: 1px solid var(--border); padding: 18px 24px; }
    .modal-title { font-size: 16px; font-weight: 700; }
    .modal-body { padding: 22px 24px; }
    .modal-footer { border-top: 1px solid var(--border); padding: 14px 24px; }

    .backup-note { display: flex; gap: 10px; align-items: flex-start; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; border-radius: 10px; padding: 12px 14px; font-size: 12.5px; line-height: 1.5; margin-top: 14px; }
    .backup-note i { margin-top: 2px; }
</style>

<div class="card filter-card mb-4"><div class="card-body">
<form method="get" action="{{ route('superadmin.tenants.index') }}" class="form-row align-items-end">
<div class="col-md-5 mb-2"><label>Search</label><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Company name or contact email"></div>
<div class="col-md-3 mb-2"><label>Approval status</label><select class="form-control" name="approval_status"><option value="">All</option>@foreach(['pending','approved','rejected'] as $status)<option value="{{ $status }}" @selected(request('approval_status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
<div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Filter</button></div>
<div class="col-md-2 mb-2"><a class="btn btn-outline-secondary btn-block" href="{{ route('superadmin.tenants.index') }}">Clear</a></div>
</form>
</div></div>

<div class="card mb-4"><div class="card-body d-flex justify-content-between align-items-center flex-wrap" style="gap:12px">
    <div><h5 class="mb-1">Create company</h5><p class="text-muted mb-0">Creates a central admin and a fresh, isolated database.</p></div>
    <button class="btn btn-primary" data-toggle="modal" data-target="#createCompanyModal"><i class="fa fa-plus"></i> New company</button>
</div></div>

@foreach($tenants as $tenant)
<div class="card tenant-card mb-3">
    <div class="tenant-head">
        <div>
            <h5 class="tenant-name">{{ $tenant->name }}</h5>
            <span class="tenant-email">{{ $tenant->contact_email ?: 'No contact email' }}</span>
        </div>
        <div class="tenant-badges">
            <span class="badge badge-{{ $tenant->approval_status==='approved'?'success':($tenant->approval_status==='pending'?'warning':'danger') }}">{{ ucfirst($tenant->approval_status) }}</span>
            <span class="badge badge-{{ $tenant->provision_status }}">DB {{ $tenant->provision_status }}</span>
        </div>
    </div>

    <div class="tenant-stats">
        <div><span class="tenant-stat-label">Users</span><span class="tenant-stat-value">1 Admin + {{ max($tenant->users_count - 1, 0) }} users</span></div>
        <div><span class="tenant-stat-label">Plan</span><span class="tenant-stat-value">{{ ucfirst($tenant->plan) }}</span></div>
        <div><span class="tenant-stat-label">Expires</span><span class="tenant-stat-value">{{ optional($tenant->trial_ends_at)->format('d M Y') ?: 'No expiry' }}</span></div>
        <div><span class="tenant-stat-label">Admin</span><span class="tenant-stat-value">{{ $tenant->admin?->email ?: 'Not assigned' }}</span></div>
        <div><span class="tenant-stat-label">Database</span><span class="tenant-stat-value">{{ $tenant->database_name ?: 'No database' }}</span></div>
    </div>

    @if($tenant->provision_error)
        <div class="tenant-provision-error alert alert-danger mb-0"><small>{{ $tenant->provision_error }}</small></div>
    @endif

    @if($tenant->approval_status==='pending')
        <div class="tenant-reject-form">
            <form method="post" action="{{ route('superadmin.tenants.reject',$tenant) }}" class="form-inline">
                @csrf
                <input class="form-control mr-2" name="rejection_reason" placeholder="Reason for rejecting this signup" required style="min-width:280px">
                <button class="btn btn-sm btn-outline-danger">Confirm rejection</button>
            </form>
        </div>
    @endif

    <div class="tenant-actions">
        <div class="action-group">
            <a class="btn btn-sm btn-outline-primary" href="{{ route('superadmin.users.index',$tenant) }}"><i class="fa fa-users"></i> Manage users</a>
            @if($tenant->approval_status==='pending')
                <form method="post" action="{{ route('superadmin.tenants.approve',$tenant) }}">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
            @endif
            <form method="post" action="{{ route('superadmin.tenants.provision',$tenant) }}">
                @csrf
                <button class="btn btn-sm btn-outline-secondary" title="Re-runs this company's database setup — safe to use any time, it only adds what's missing rather than resetting existing data.">
                    {{ $tenant->provision_status==='ready'?'Re-provision safely':'Retry provisioning' }}
                </button>
            </form>
            @if($tenant->database_name)
                <form method="post" action="{{ route('superadmin.tenants.health',$tenant) }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary" title="Checks that this company's database is currently reachable.">Health check</button>
                </form>
            @endif
            <button class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#editModal{{ $tenant->id }}"><i class="fa fa-pencil"></i> Edit settings</button>
        </div>
        <button class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#deleteModal{{ $tenant->id }}"><i class="fa fa-trash"></i> Delete company</button>
    </div>
</div>

{{-- Edit settings modal --}}
<div class="modal fade" id="editModal{{ $tenant->id }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form method="post" action="{{ route('superadmin.tenants.update',$tenant) }}">
        @csrf @method('PUT')
        <input type="hidden" name="slug" value="{{ $tenant->slug }}">
        <input type="hidden" name="timezone" value="{{ $tenant->timezone }}">
        <input type="hidden" name="locale" value="{{ $tenant->locale }}">
        <div class="modal-header">
          <h5 class="modal-title">Edit {{ $tenant->name }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="col-md-6 mb-3"><label>Company name</label><input class="form-control" name="name" value="{{ $tenant->name }}" required></div>
            <div class="col-md-6 mb-3"><label>Contact email</label><input class="form-control" type="email" name="contact_email" value="{{ $tenant->contact_email }}"></div>
            <div class="col-md-4 mb-3"><label>Plan</label><select class="form-control" name="plan">@foreach(['trial','standard','professional','enterprise'] as $plan)<option value="{{ $plan }}" @selected($tenant->plan===$plan)>{{ ucfirst($plan) }}</option>@endforeach</select></div>
            <div class="col-md-4 mb-3"><label>Expiry date</label><input class="form-control" type="date" name="trial_ends_at" value="{{ optional($tenant->trial_ends_at)->format('Y-m-d') }}"></div>
            <div class="col-md-2 mb-3"><label>User limit</label><input class="form-control" type="number" min="1" name="max_users" value="{{ $tenant->max_users }}" placeholder="No limit"></div>
            <div class="col-md-2 mb-3"><label>Status</label><select class="form-control" name="status"><option @selected($tenant->status==='Active')>Active</option><option @selected($tenant->status==='Inactive')>Inactive</option></select></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
          <button class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Delete company modal --}}
<div class="modal fade" id="deleteModal{{ $tenant->id }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="post" action="{{ route('superadmin.tenants.destroy',$tenant) }}" class="delete-company-form">
        @csrf @method('DELETE')
        <div class="modal-header">
          <h5 class="modal-title text-danger"><i class="fa fa-triangle-exclamation"></i> Delete {{ $tenant->name }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">This permanently deletes <strong>{{ $tenant->name }}</strong> and its {{ $tenant->users_count }} user account(s). This cannot be undone from this screen.</p>

          @if($tenant->database_name)
            <div class="backup-note"><i class="fa fa-shield-halved"></i> A full backup of this company's database is saved automatically on the server before anything is dropped — always, with no way to skip it. You'll find it afterward under <strong>Backups</strong> in the sidebar.</div>
          @endif

          <label class="mt-4 mb-1">Type <strong>{{ $tenant->name }}</strong> to confirm</label>
          <input class="form-control confirm-name-input" name="confirm_name" data-expected="{{ $tenant->name }}" autocomplete="off" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger" disabled>Permanently delete</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach

{{-- Create company modal --}}
<div class="modal fade" id="createCompanyModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form method="post" action="{{ route('superadmin.tenants.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Create company</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <h6 class="text-muted text-uppercase" style="font-size:11px;font-weight:800;letter-spacing:.04em">Company</h6>
          <div class="form-row">
            <div class="col-md-6 mb-3"><label>Company name</label><input class="form-control" name="name" required></div>
            <div class="col-md-6 mb-3"><label>Contact email</label><input class="form-control" type="email" name="contact_email"></div>
            <div class="col-md-4 mb-3"><label>Plan</label><select class="form-control" name="plan"><option value="trial">Trial</option><option value="standard">Standard</option><option value="professional">Professional</option><option value="enterprise">Enterprise</option></select></div>
            <div class="col-md-4 mb-3"><label>Timezone</label><input class="form-control" name="timezone" value="Asia/Kolkata" required></div>
            <div class="col-md-4 mb-3"><label>Locale</label><input class="form-control" name="locale" value="en" required></div>
            <div class="col-md-6 mb-3"><label>User limit</label><input class="form-control" type="number" min="1" name="max_users" placeholder="Unlimited"></div>
            <div class="col-md-6 mb-3"><label>Trial ends</label><input class="form-control" type="date" name="trial_ends_at"></div>
          </div>
          <hr>
          <h6 class="text-muted text-uppercase" style="font-size:11px;font-weight:800;letter-spacing:.04em">Administrator account</h6>
          <div class="form-row">
            <div class="col-md-6 mb-3"><label>Admin name</label><input class="form-control" name="admin_name" required></div>
            <div class="col-md-6 mb-3"><label>Admin email</label><input class="form-control" type="email" name="admin_email" required></div>
            <div class="col-md-6 mb-3"><label>Admin phone</label><input class="form-control" name="admin_phone"></div>
            <div class="col-md-6 mb-3"><label>Admin password</label><input class="form-control" type="password" name="admin_password" required></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
          <button class="btn btn-primary">Create &amp; provision</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.confirm-name-input').forEach(function (input) {
        input.addEventListener('input', function () {
            var button = input.closest('form').querySelector('.btn-danger');
            button.disabled = input.value !== input.dataset.expected;
        });
    });
</script>
@endpush

@endsection
