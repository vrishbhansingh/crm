<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Settings | CRM</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        :root { --bg: #f5f7fb; --card: #ffffff; --border: #e6e9f0; --text: #1f2937; --muted: #6b7280; --primary: #2563eb; }
        body { background: var(--bg); font-family: "Inter", system-ui, sans-serif; }
        .panel { background: var(--card); border-radius: 14px; border: 1px solid var(--border); padding: 22px; max-width: 720px; }
        .section-title { font-size: 15px; font-weight: 600; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 8px; }
        label { font-size: 12px; font-weight: 600; color: var(--muted); }
        .hint { font-size: 12px; color: var(--muted); margin-top: -8px; margin-bottom: 16px; }
    </style>
</head>

<body>

    <div class="container-scroller">
        @include('include.header')

        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')

            <div class="content-wrapper">

                <h3 class="mb-1">Mail Settings</h3>
                <p class="text-muted mb-4">Configure this company's own SMTP for outgoing emails (invoices, password resets, notifications). If disabled, the platform's default mail server is used instead.</p>

                <div class="panel section">
                    <div class="section-title">SMTP configuration</div>

                    <form id="mailSettingsForm" method="POST" action="{{ route('settings.mail.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="smtp_enabled" name="smtp_enabled" value="1" {{ $tenant->smtp_enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="smtp_enabled" style="font-size:14px;font-weight:600;color:var(--text)">Use this company's own SMTP server</label>
                        </div>

                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label>SMTP Host</label>
                                <input type="text" name="smtp_host" class="form-control" placeholder="smtp.example.com" value="{{ old('smtp_host', $tenant->smtp_host) }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Port</label>
                                <input type="number" name="smtp_port" class="form-control" placeholder="587" value="{{ old('smtp_port', $tenant->smtp_port) }}">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Encryption</label>
                                <select name="smtp_encryption" class="form-control">
                                    <option value="" @selected(! $tenant->smtp_encryption)>None</option>
                                    <option value="tls" @selected($tenant->smtp_encryption==='tls')>TLS</option>
                                    <option value="ssl" @selected($tenant->smtp_encryption==='ssl')>SSL</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Username</label>
                                <input type="text" name="smtp_username" class="form-control" value="{{ old('smtp_username', $tenant->smtp_username) }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Password</label>
                                <input type="password" name="smtp_password" class="form-control" placeholder="{{ $tenant->smtp_password ? 'Leave blank to keep current password' : '' }}">
                            </div>

                            <div class="col-md-6 form-group">
                                <label>From address</label>
                                <input type="email" name="smtp_from_address" class="form-control" placeholder="notifications@yourcompany.com" value="{{ old('smtp_from_address', $tenant->smtp_from_address) }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>From name</label>
                                <input type="text" name="smtp_from_name" class="form-control" placeholder="{{ $tenant->name }}" value="{{ old('smtp_from_name', $tenant->smtp_from_name) }}">
                            </div>
                        </div>

                        <div class="text-right mt-3">
                            <button type="submit" class="btn btn-primary">Save mail settings</button>
                        </div>
                    </form>
                </div>

                <div class="panel section mt-4">
                    <div class="section-title">Send a test email</div>
                    <p class="hint">Save your settings first, then send a test email to confirm they work.</p>
                    <form id="testEmailForm" method="POST" action="{{ route('settings.mail.test') }}" class="form-inline">
                        @csrf
                        <input type="email" name="test_email" class="form-control mr-2" placeholder="you@example.com" required style="min-width:280px">
                        <button type="submit" class="btn btn-outline-primary">Send test email</button>
                    </form>
                </div>

                @include('include.footer')
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>

    <script>
        @if(session('success'))
            toastr.success(@json(session('success')));
        @endif
        @if($errors->any())
            toastr.error(@json($errors->first()));
        @endif
    </script>

</body>

</html>
