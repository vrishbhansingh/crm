@extends('layouts.platform')
@section('title','Mail settings')
@section('heading','Platform default mail settings')
@section('content')
<p class="text-muted mb-4">Used for every Super Admin email (like password resets) and for any company that hasn't configured its own SMTP.</p>
<div class="card mb-4"><div class="card-body">
<form method="post" action="{{ route('superadmin.settings.mail.update') }}">
@csrf @method('PUT')
<div class="form-row">
<div class="col-md-8 mb-2"><label class="text-muted small mb-1">SMTP Host</label><input class="form-control" name="smtp_host" placeholder="smtp.example.com" value="{{ old('smtp_host', $settings->smtp_host) }}"></div>
<div class="col-md-4 mb-2"><label class="text-muted small mb-1">Port</label><input class="form-control" type="number" name="smtp_port" placeholder="587" value="{{ old('smtp_port', $settings->smtp_port) }}"></div>
<div class="col-md-4 mb-2"><label class="text-muted small mb-1">Encryption</label><select class="form-control" name="smtp_encryption"><option value="" @selected(!$settings->smtp_encryption)>None</option><option value="tls" @selected($settings->smtp_encryption==='tls')>TLS</option><option value="ssl" @selected($settings->smtp_encryption==='ssl')>SSL</option></select></div>
<div class="col-md-4 mb-2"><label class="text-muted small mb-1">Username</label><input class="form-control" name="smtp_username" value="{{ old('smtp_username', $settings->smtp_username) }}"></div>
<div class="col-md-4 mb-2"><label class="text-muted small mb-1">Password</label><input class="form-control" type="password" name="smtp_password" placeholder="{{ $settings->smtp_password ? 'Leave blank to keep current password' : '' }}"></div>
<div class="col-md-6 mb-2"><label class="text-muted small mb-1">From address</label><input class="form-control" type="email" name="smtp_from_address" placeholder="noreply@yourplatform.com" value="{{ old('smtp_from_address', $settings->smtp_from_address) }}"></div>
<div class="col-md-6 mb-2"><label class="text-muted small mb-1">From name</label><input class="form-control" name="smtp_from_name" placeholder="CRM Platform" value="{{ old('smtp_from_name', $settings->smtp_from_name) }}"></div>
</div>
<button class="btn btn-primary mt-2">Save platform mail settings</button>
</form>
</div></div>
<div class="card"><div class="card-body">
<h5>Send a test email</h5>
<p class="text-muted small">Save settings first, then confirm they work.</p>
<form method="post" action="{{ route('superadmin.settings.mail.test') }}" class="form-inline">
@csrf
<input type="email" name="test_email" class="form-control mr-2" placeholder="you@example.com" required style="min-width:280px">
<button class="btn btn-outline-primary">Send test email</button>
</form>
</div></div>
@endsection
