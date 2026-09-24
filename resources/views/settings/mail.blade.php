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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">

    <style>
        :root { --primary: #4338ca; --primary-dark: #312e81; --border: #e5e7eb; --text-dark: #111827; --text-muted: #6b7280; }
        body { font-family: "Inter", system-ui, sans-serif; }

        .crm-page-header {
            background: #fff; padding: 20px 22px; border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06); border-left: 4px solid var(--primary);
            display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; flex-wrap: wrap;
        }
        .crm-header-left { display: flex; align-items: center; gap: 14px; }
        .crm-header-icon {
            width: 44px; height: 44px; border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;
        }
        .crm-page-header h4 { font-weight: 800; font-size: 18px; color: var(--text-dark); margin: 0; }
        .crm-subtitle { color: var(--text-muted); font-size: 13px; max-width: 620px; display: block; margin-top: 2px; }

        .settings-card {
            background: #fff; border-radius: 14px; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
            padding: 20px 22px; margin-bottom: 18px;
        }
        .settings-card-title { font-size: 14.5px; font-weight: 700; color: var(--text-dark); margin-bottom: 2px; }
        .settings-card-sub { font-size: 12.5px; color: var(--text-muted); margin-bottom: 18px; }

        .smtp-row {
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
            padding: 14px 16px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 10px; flex-wrap: wrap;
        }
        .smtp-row:last-child { margin-bottom: 0; }
        .smtp-row-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
        .smtp-icon {
            width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
            background: #eef2ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 15px;
        }
        .smtp-name { font-weight: 700; font-size: 13.5px; color: var(--text-dark); display: flex; align-items: center; gap: 8px; }
        .smtp-detail { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
        .smtp-actions { display: flex; gap: 6px; flex-wrap: wrap; flex-shrink: 0; }
        .smtp-actions .btn { border-radius: 8px; font-size: 12px; padding: 6px 11px; font-weight: 600; }

        .badge-active-smtp {
            background: #dcfce7; color: #166534; font-size: 10px; font-weight: 800;
            text-transform: uppercase; letter-spacing: .03em; padding: 3px 8px; border-radius: 999px;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .badge-active-smtp .status-dot { width: 6px; height: 6px; border-radius: 50%; background: #22c55e; }

        .empty-state { text-align: center; padding: 36px 20px; color: var(--text-muted); }
        .empty-state .empty-icon { font-size: 30px; color: #cbd5e1; margin-bottom: 12px; display: block; }
        .empty-state p { max-width: 420px; margin: 0 auto 16px; font-size: 13px; }

        .test-email-row { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
        .test-email-row .test-icon {
            width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
            background: #fff7ed; color: #c2410c; display: flex; align-items: center; justify-content: center; font-size: 15px;
        }
        .test-email-row form { display: flex; gap: 8px; flex: 1; min-width: 260px; flex-wrap: wrap; }
        .test-email-row input { flex: 1; min-width: 220px; height: 40px; border-radius: 9px; border: 1px solid var(--border); padding: 0 13px; font-size: 13px; }
        .test-email-row .btn { border-radius: 9px; font-weight: 600; font-size: 13px; padding: 0 18px; height: 40px; white-space: nowrap; }

        .btn-primary-brand { background: var(--primary); border-color: var(--primary); color: #fff; }
        .btn-primary-brand:hover { background: var(--primary-dark); border-color: var(--primary-dark); color: #fff; }

        #smtpModal .modal-dialog { max-width: 640px; }
        #smtpModal .modal-content { border-radius: 14px; border: none; }
        #smtpModal label { font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .02em; }
        .hint { font-size: 12px; color: var(--text-muted); margin-top: -8px; margin-bottom: 16px; }

        [data-theme="dark"] {
            --border: #2a2e40;
            --text-dark: #eef0f6;
            --text-muted: #9aa1b5;
        }
        [data-theme="dark"] .crm-page-header,
        [data-theme="dark"] .settings-card {
            background: #1a1d2b;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }
        [data-theme="dark"] .smtp-row { background: #16192a; }
        [data-theme="dark"] .smtp-icon { background: rgba(99, 102, 241, 0.16); color: #a5b4fc; }
        [data-theme="dark"] .test-email-row .test-icon { background: rgba(251, 146, 60, 0.14); color: #fb923c; }
        [data-theme="dark"] .badge-active-smtp { background: rgba(74, 222, 128, 0.16); color: #4ade80; }
        [data-theme="dark"] .empty-state .empty-icon { color: #343850; }
        [data-theme="dark"] #smtpModal .modal-content { background: #1a1d2b; color: #eef0f6; }
        [data-theme="dark"] .test-email-row input { background: #16192a; color: #eef0f6; }
    </style>
</head>

<body>

    <div class="container-scroller">
        @include('include.header')

        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="crm-page-header">
                    <div class="crm-header-left">
                        <div class="crm-header-icon"><i class="fa fa-envelope"></i></div>
                        <div>
                            <h4>Mail Settings</h4>
                            <small class="crm-subtitle">One or more SMTP configs for this company's outgoing email. Whichever's marked Active is used — if none is, the platform's default mail server sends on your behalf.</small>
                        </div>
                    </div>
                    <button class="btn btn-primary-brand" id="addSmtpBtn" data-toggle="modal" data-target="#smtpModal"><i class="fa fa-plus"></i> Add SMTP</button>
                </div>

                <div class="settings-card">
                    <div class="settings-card-title">Saved SMTP configurations</div>
                    <div class="settings-card-sub">{{ $mailSettings->count() }} configured</div>

                    @if($mailSettings->isEmpty())
                        <div class="empty-state">
                            <i class="fa fa-envelope-o empty-icon"></i>
                            <p>No SMTP configured yet — emails from this company currently send through the platform's default mail server.</p>
                            <button class="btn btn-primary-brand" data-toggle="modal" data-target="#smtpModal"><i class="fa fa-plus"></i> Add your first SMTP</button>
                        </div>
                    @else
                        @foreach($mailSettings as $setting)
                            <div class="smtp-row">
                                <div class="smtp-row-left">
                                    <div class="smtp-icon"><i class="fa fa-server"></i></div>
                                    <div style="min-width:0;">
                                        <div class="smtp-name">
                                            {{ $setting->name }}
                                            @if($setting->is_active)<span class="badge-active-smtp"><span class="status-dot"></span> Active</span>@endif
                                        </div>
                                        <div class="smtp-detail">{{ $setting->smtp_host }}:{{ $setting->smtp_port }} @if($setting->smtp_encryption) · {{ strtoupper($setting->smtp_encryption) }} @endif @if($setting->smtp_from_address) · from {{ $setting->smtp_from_address }} @endif</div>
                                    </div>
                                </div>
                                <div class="smtp-actions">
                                    @unless($setting->is_active)
                                        <form method="post" action="{{ route('settings.mail.activate', $setting) }}">@csrf<button class="btn btn-outline-success">Set active</button></form>
                                    @endunless
                                    <button type="button" class="btn btn-outline-secondary editSmtpBtn"
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
                                    <form method="post" action="{{ route('settings.mail.destroy', $setting) }}" onsubmit="return confirm('Delete &quot;{{ $setting->name }}&quot;?');">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i class="fa fa-trash"></i></button></form>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="settings-card">
                    <div class="settings-card-title">Send a test email</div>
                    <div class="settings-card-sub">Sends using whichever config is Active above (or the platform default if none is).</div>
                    <div class="test-email-row">
                        <div class="test-icon"><i class="fa fa-paper-plane"></i></div>
                        <form id="testEmailForm" method="POST" action="{{ route('settings.mail.test') }}">
                            @csrf
                            <input type="email" name="test_email" placeholder="you@example.com" required>
                            <button type="submit" class="btn btn-outline-primary">Send test email</button>
                        </form>
                    </div>
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
                        <button type="submit" class="btn btn-primary-brand">Save configuration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>
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
