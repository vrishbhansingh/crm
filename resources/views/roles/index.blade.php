<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Roles & Permissions</title><link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}"><link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"><style>.role-card{border:0;border-radius:14px;box-shadow:0 6px 22px rgba(15,23,42,.07)}.permission-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px}.permission-group{background:#f8fafc;border-radius:10px;padding:12px}.permission-group label{display:block;font-size:12px;margin:5px 0}.protected{background:#dcfce7;color:#166534;padding:4px 9px;border-radius:15px;font-size:12px}.sensitive-box{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px}.sensitive-box .sensitive-title{color:#b91c1c;font-weight:700;font-size:13px;margin-bottom:4px}.sensitive-box label{display:block;font-size:12.5px;margin:6px 0 0;color:#7f1d1d}.sensitive-box small{color:#991b1b}.inert-note{background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:12px;font-size:12.5px;color:#64748b}</style></head><body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper"><div class="d-flex justify-content-between align-items-center mb-4"><div><h3>Roles & Permissions</h3><p class="text-muted mb-0">Create roles for {{ $tenant->name }}. The protected Admin account is not counted as a normal user role.</p></div><button class="btn btn-primary" data-toggle="collapse" data-target="#createRole">Create role</button></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@php
    $permissionFields = function ($role = null) use ($permissions, $sensitivePermissions) {
        $label = fn ($permission) => str_replace('-', ' ', ucfirst(str($permission->name)->after('.')->value()));
        echo '<div class="permission-grid">';
        foreach ($permissions as $module => $items) {
            echo '<div class="permission-group"><strong>'.ucfirst($module).'</strong>';
            foreach ($items as $permission) {
                $checked = $role && $role->hasPermissionTo($permission) ? 'checked' : '';
                echo '<label><input type="checkbox" name="permissions[]" value="'.$permission->name.'" '.$checked.'> '.$label($permission).'</label>';
            }
            echo '</div>';
        }
        echo '</div>';
        echo '<div class="sensitive-box mt-3"><div class="sensitive-title"><i class="fa fa-exclamation-triangle"></i> Sensitive permissions</div><small>Grants unusually broad access — review before enabling.</small>';
        foreach ($sensitivePermissions as $permission) {
            $checked = $role && $role->hasPermissionTo($permission) ? 'checked' : '';
            $note = $permission->name === 'users.impersonate' ? 'Sign in as any other user in this workspace without their password.' : '';
            echo '<label><input type="checkbox" name="permissions[]" value="'.$permission->name.'" '.$checked.'> <b>'.$label($permission).'</b>'.($note ? ' — '.$note : '').'</label>';
        }
        echo '</div>';
        echo '<div class="inert-note mt-3"><i class="fa fa-info-circle"></i> Role &amp; permission management (creating or editing roles) is restricted to the protected company Admin account itself and can\'t be delegated to a custom role, so it isn\'t offered as a grantable permission here.</div>';
    };
@endphp
<div class="collapse mb-4" id="createRole"><div class="card role-card"><div class="card-body"><form method="post" action="{{ route('roles.store') }}">@csrf<div class="form-group"><label>Role name</label><input class="form-control" name="name" placeholder="Example: Agent, Sales Manager, Marketing" required></div>{{ $permissionFields() }}<button class="btn btn-primary mt-3">Create role</button></form></div></div></div>
<div class="row">@foreach($roles as $role)<div class="col-lg-6 mb-4"><div class="card role-card h-100"><div class="card-body"><div class="d-flex justify-content-between"><h5>{{ $role->name }}</h5><div><span class="badge badge-light">{{ $role->users_count }} users</span>@if($role->name==='Admin') <span class="protected">Protected</span>@endif</div></div>
@if($role->name==='Admin')<p class="text-muted">The single company Admin always has all tenant permissions and cannot be renamed or deleted.</p>@else<form method="post" action="{{ route('roles.update',$role) }}">@csrf @method('PUT')<input class="form-control mb-3" name="name" value="{{ $role->name }}" required>{{ $permissionFields($role) }}<button class="btn btn-outline-primary btn-sm mt-3">Save role</button></form><form method="post" action="{{ route('roles.destroy',$role) }}" class="mt-2" onsubmit="return confirm('Delete this unused role?')">@csrf @method('DELETE')<button class="btn btn-link text-danger p-0">Delete role</button></form>@endif
</div></div></div>@endforeach</div></div>@include('include.footer')</div></div></div><script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script></body></html>
