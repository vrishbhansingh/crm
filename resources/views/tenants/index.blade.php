<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Management</title>
    <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .tenant-card { border: 0; border-radius: 14px; box-shadow: 0 7px 25px rgba(30, 64, 175, .08); }
        .tenant-pill { display: inline-block; padding: 3px 9px; border-radius: 20px; background: #eef2ff; color: #4338ca; font-size: 12px; }
        .context-active { border-left: 4px solid #2563eb; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; }
    </style>
</head>
<body>
<div class="container-scroller">
    @include('include.header')
    <div class="container-fluid page-body-wrapper">
        @include('include.sidebar')
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div><h3 class="mb-1">Tenant Management</h3><p class="text-muted mb-0">Provision and govern isolated CRM workspaces.</p></div>
                    <button class="btn btn-primary" data-toggle="collapse" data-target="#newTenant"><i class="fa fa-plus"></i> New Tenant</button>
                </div>

                @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
                @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

                <div class="collapse mb-4" id="newTenant">
                    <div class="card tenant-card"><div class="card-body">
                        <h5>Create tenant and first administrator</h5>
                        <form method="post" action="{{ route('tenants.store') }}">@csrf
                            <div class="form-grid">
                                <input class="form-control" name="name" placeholder="Tenant name" required>
                                <input class="form-control" name="slug" placeholder="Slug (optional)">
                                <input class="form-control" type="email" name="contact_email" placeholder="Billing/contact email">
                                <select class="form-control" name="plan" required><option value="standard">Standard</option><option value="trial">Trial</option><option value="professional">Professional</option><option value="enterprise">Enterprise</option></select>
                                <input class="form-control" name="timezone" value="Asia/Kolkata" required>
                                <input class="form-control" name="locale" value="en" required>
                                <input class="form-control" type="number" min="1" name="max_users" placeholder="User limit (optional)">
                                <input class="form-control" type="date" name="trial_ends_at">
                                <input class="form-control" name="admin_name" placeholder="Admin name" required>
                                <input class="form-control" type="email" name="admin_email" placeholder="Admin email" required>
                                <input class="form-control" name="admin_phone" placeholder="Admin phone">
                                <input class="form-control" type="password" name="admin_password" placeholder="Admin password (8+ chars)" required>
                            </div>
                            <button class="btn btn-primary mt-3">Create workspace</button>
                        </form>
                    </div></div>
                </div>

                <div class="row">
                    @foreach($tenants as $tenant)
                    <div class="col-lg-6 mb-4">
                        <div class="card tenant-card h-100 {{ (int) session('tenant_context_id') === $tenant->id ? 'context-active' : '' }}"><div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <div><h5 class="mb-1">{{ $tenant->name }}</h5><small class="text-muted">{{ $tenant->slug }}</small></div>
                                <div><span class="tenant-pill">{{ ucfirst($tenant->plan) }}</span> <span class="badge badge-{{ $tenant->status === 'Active' ? 'success' : 'secondary' }}">{{ $tenant->status }}</span></div>
                            </div>
                            <p class="small text-muted">{{ $tenant->users_count }} users{{ $tenant->max_users ? ' / '.$tenant->max_users.' limit' : '' }} · {{ $tenant->timezone }}</p>
                            <form method="post" action="{{ route('tenants.update', $tenant) }}">@csrf @method('PUT')
                                <div class="form-grid">
                                    <input class="form-control" name="name" value="{{ $tenant->name }}" required>
                                    <input class="form-control" name="slug" value="{{ $tenant->slug }}" required>
                                    <input class="form-control" type="email" name="contact_email" value="{{ $tenant->contact_email }}" placeholder="Contact email">
                                    <select class="form-control" name="plan">@foreach(['trial','standard','professional','enterprise'] as $plan)<option value="{{ $plan }}" @selected($tenant->plan === $plan)>{{ ucfirst($plan) }}</option>@endforeach</select>
                                    <input class="form-control" name="timezone" value="{{ $tenant->timezone }}" required>
                                    <input class="form-control" name="locale" value="{{ $tenant->locale }}" required>
                                    <input class="form-control" type="number" min="1" name="max_users" value="{{ $tenant->max_users }}" placeholder="User limit">
                                    <input class="form-control" type="date" name="trial_ends_at" value="{{ optional($tenant->trial_ends_at)->format('Y-m-d') }}">
                                    <select class="form-control" name="status"><option value="Active" @selected($tenant->status === 'Active')>Active</option><option value="Inactive" @selected($tenant->status === 'Inactive')>Inactive</option></select>
                                </div>
                                <button class="btn btn-outline-primary btn-sm mt-3">Save settings</button>
                            </form>
                            <form method="post" action="{{ route('tenants.destroy', $tenant) }}" class="mt-2" onsubmit="return confirm('Delete this empty tenant?')">@csrf @method('DELETE')<button class="btn btn-link text-danger btn-sm p-0">Delete if empty</button></form>
                        </div></div>
                    </div>
                    @endforeach
                </div>
            </div>
            @include('include.footer')
        </div>
    </div>
</div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
</body>
</html>
