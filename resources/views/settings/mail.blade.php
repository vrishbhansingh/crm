<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mail Settings | CRM</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        :root { --bg: #f5f7fb; --card: #ffffff; --border: #e6e9f0; --text: #1f2937; --muted: #6b7280; --primary: #2563eb; }
        body { background: var(--bg); font-family: "Inter", system-ui, sans-serif; }

        .crm-page-header{background:#fff;padding:26px 28px;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.06);margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
        .crm-page-header h3{margin:0 0 6px;font-weight:700;font-size:22px;color:#111827}.crm-page-header p{margin:0;color:#6b7280;font-size:14px;max-width:640px}

        .panel { background: var(--card); border-radius: 16px; border: none; box-shadow: 0 8px 24px rgba(15,23,42,.06); padding: 24px; max-width: 900px; }
        .section-title { font-size: 15.5px; font-weight: 700; margin-bottom: 18px; }
        label { font-size: 12.5px; font-weight: 600; color: var(--muted); }
        .hint { font-size: 12.5px; color: var(--muted); margin-top: -8px; margin-bottom: 16px; }

        .smtp-empty { text-align: center; padding: 30px 20px; color: var(--muted); }
        .smtp-empty i { font-size: 28px; color: #cbd5e1; margin-bottom: 10px; display: block; }

        .smtp-row { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 16px 4px; border-bottom: 1px solid var(--border); flex-wrap: wrap; }
        .smtp-row:last-child { border-bottom: none; }
        .smtp-name { font-weight: 700; font-size: 14px; color: var(--text); display: flex; align-items: center; gap: 8px; }
        .smtp-detail { font-size: 12.5px; color: var(--muted); margin-top: 2px; }
        .smtp-actions { display: flex; gap: 6px; flex-wrap: wrap; }

        .badge-active-smtp { background: #dcfce7; color: #166534; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; padding: 3px 9px; border-radius: 999px; }

        #smtpModal .modal-dialog { max-width: 640px; }
    </style>
</head>

<body>

    <div class="container-scroller">
        @include('include.header')

        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="crm-page-header">
                    <div>
                        <h3>Mail Settings</h3>
                        <p>Save one or more SMTP configurations for this company's outgoing emails (invoices, password resets, campaigns). Whichever one is marked Active is used; if none is, the platform's default mail server sends on your behalf instead.</p>
                    </div>
                    <button class="btn btn-primary" id="addSmtpBtn" data-toggle="modal" data-target="#smtpModal"><i class="fa fa-plus"></i> Add SMTP</button>
                </div>

                <div class="panel section mb-4">
                    <div class="section-title">Saved SMTP configurations</div>

                    @if($mailSettings->isEmpty())
                        <div class="smtp-empty">
                            <i class="fa fa-envelope-o"></i>
                            No SMTP configured yet — emails from this company currently send through the platform's default mail server.
                        </div>
                    @else
                        @foreach($mailSettings as $setting)
                            <div class="smtp-row">
                                <div>
                                    <div class="smtp-name">
                                        {{ $setting->name }}
                                        @if($setting->is_active)<span class="badge-active-smtp">Active</span>@endif
                                    </div>
                                    <div class="smtp-detail">{{ $setting->smtp_host }}:{{ $setting->smtp_port }} @if($setting->smtp_encryption) · {{ strtoupper($setting->smtp_encryption) }} @endif @if($setting->smtp_from_address) · from {{ $setting->smtp_from_address }} @endif</div>
                                </div>
                                <div class="smtp-actions">
                                    @unless($setting->is_active)
                                        <form method="post" action="{{ route('settings.mail.activate', $setting) }}">@csrf<button class="btn btn-sm btn-outline-success">Set active</button></form>
                                    @endunless
                                    <button type="button" class="btn btn-sm btn-outline-secondary editSmtpBtn"
                                        data-id="{{ $setting->id }}"
                                        data-name="{{ $setting->name }}"
                                        data-host="{{ $setting->smtp_host }}"
                                        data-port="{{ $setting->smtp_port }}"
                                        data-encryption="{{ $setting->smtp_encryption }}"
                                        data-username="{{ $setting->smtp_username }}"
                                        data-from-address="{{ $setting->smtp_from_address }}"
                                        data-from-name="{{ $setting->smtp_from_name }}">
                                        <i class="fa fa-pencil"></i> Edit
                                    </button>
                                    <form method="post" action="{{ route('settings.mail.destroy', $setting) }}" onsubmit="return confirm('Delete &quot;{{ $setting->name }}&quot;?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button></form>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="panel section">
                    <div class="section-title">Send a test email</div>
                    <p class="hint">Sends using whichever config is Active above (or the platform default if none is).</p>
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

    <!-- Add/Edit SMTP modal -->
    <div class="modal fade" id="smtpModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="smtpModalTitle">Add SMTP configuration</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="smtpForm" method="post" action="{{ route('settings.mail.store') }}">
                    @csrf
                    <div id="smtpMethodField"></div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Config name</label>
                            <input type="text" name="name" id="smtp_name" class="form-control" placeholder="e.g. Support inbox" required>
                        </div>
                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label>SMTP Host</label>
                                <input type="text" name="smtp_host" id="smtp_host" class="form-control" placeholder="smtp.example.com" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Port</label>
                                <input type="number" name="smtp_port" id="smtp_port" class="form-control" placeholder="587" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Encryption</label>
                                <select name="smtp_encryption" id="smtp_encryption" class="form-control">
                                    <option value="">None</option>
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Username</label>
                                <input type="text" name="smtp_username" id="smtp_username" class="form-control">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Password</label>
                                <input type="password" name="smtp_password" id="smtp_password" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>From address</label>
                                <input type="email" name="smtp_from_address" id="smtp_from_address" class="form-control" placeholder="notifications@yourcompany.com">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>From name</label>
                                <input type="text" name="smtp_from_name" id="smtp_from_name" class="form-control">
                            </div>
                        </div>
                        <p class="hint mb-0" id="smtpPasswordHint" style="display:none">Leave password blank to keep the current one.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save configuration</button>
                    </div>
                </form>
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

        $(document).on('click', '#addSmtpBtn', function() {
            $('#smtpModalTitle').text('Add SMTP configuration');
            $('#smtpForm')[0].reset();
            $('#smtpForm').attr('action', "{{ route('settings.mail.store') }}");
            $('#smtpMethodField').empty();
            $('#smtpPasswordHint').hide();
            $('#smtp_password').attr('required', false);
        });

        $(document).on('click', '.editSmtpBtn', function() {
            const d = $(this).data();
            $('#smtpModalTitle').text('Edit SMTP configuration');
            $('#smtpForm').attr('action', "{{ url('settings/mail') }}/" + d.id);
            $('#smtpMethodField').html('@method('PUT')');
            $('#smtp_name').val(d.name);
            $('#smtp_host').val(d.host);
            $('#smtp_port').val(d.port);
            $('#smtp_encryption').val(d.encryption || '');
            $('#smtp_username').val(d.username);
            $('#smtp_password').val('');
            $('#smtp_from_address').val(d.fromAddress);
            $('#smtp_from_name').val(d.fromName);
            $('#smtpPasswordHint').show();
            $('#smtpModal').modal('show');
        });
    </script>

</body>

</html>
