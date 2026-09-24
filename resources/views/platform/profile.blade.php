@extends('layouts.platform')
@section('title','My Profile')
@section('heading','My Profile')
@section('content')

<div class="row">
    <div class="col-lg-6">
        <div class="card"><div class="card-body">
            <h5>Account details</h5>
            <p class="text-muted mb-4" style="font-size:12.5px;">Your name, email, and phone for this Super Admin account.</p>
            <form method="post" action="{{ route('superadmin.profile.update') }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="Optional">
                </div>
                <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save changes</button>
            </form>
        </div></div>
    </div>

    <div class="col-lg-6">
        <div class="card"><div class="card-body">
            <h5>Change password</h5>
            <p class="text-muted mb-4" style="font-size:12.5px;">You'll be signed out and need to log back in after this.</p>
            <form method="post" action="{{ route('superadmin.profile.password') }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Current password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New password</label>
                    <input type="password" name="new_password" class="form-control" minlength="8" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm new password</label>
                    <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                </div>
                <button class="btn btn-outline-primary"><i class="fa-solid fa-key"></i> Update password</button>
            </form>
        </div></div>
    </div>
</div>

@endsection
