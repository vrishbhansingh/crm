<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WhatsApp Settings | CRM Admin</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        :root { --primary: #25d366; --primary-dark: #128c7e; --border: #e5e7eb; --text-dark: #111827; --text-muted: #6b7280; }

        .crm-page-header {
            background: #fff; padding: 18px 22px; border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06); border-left: 4px solid var(--primary);
            display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 20px; flex-wrap: wrap;
        }
        .crm-header-left { display: flex; align-items: center; gap: 14px; }
        .crm-header-icon {
            width: 42px; height: 42px; border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .crm-page-header h4 { font-weight: 700; font-size: 18px; color: var(--text-dark); margin: 0; }
        .crm-subtitle { color: var(--text-muted); font-size: 13px; }

        .channel-pick { display: flex; gap: 14px; margin-bottom: 22px; flex-wrap: wrap; }
        .channel-pick-card {
            flex: 1; min-width: 260px; background: #fff; border: 1px solid var(--border); border-radius: 14px;
            padding: 18px; cursor: pointer; transition: box-shadow .15s ease, border-color .15s ease;
        }
        .channel-pick-card:hover { box-shadow: 0 8px 22px rgba(0,0,0,.08); border-color: var(--primary); }
        .channel-pick-card i { font-size: 22px; color: var(--primary-dark); margin-bottom: 8px; display: block; }
        .channel-pick-card h6 { font-weight: 700; margin: 0 0 4px; }
        .channel-pick-card p { color: var(--text-muted); font-size: 12.5px; margin: 0; }

        .account-card {
            background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 18px 20px;
            margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
        }
        .account-card .acc-name { font-weight: 700; color: var(--text-dark); }
        .account-badge { padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
        .account-badge.meta_cloud { background: #dbeafe; color: #1d4ed8; }
        .account-badge.unofficial { background: #fef3c7; color: #92400e; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .status-dot.on { background: #22c55e; }
        .status-dot.off { background: #9ca3af; }
        .webhook-url-box {
            background: #f9fafb; border: 1px dashed var(--border); border-radius: 8px; padding: 8px 12px;
            font-family: monospace; font-size: 12px; word-break: break-all; margin-top: 8px;
        }
        .empty-state { text-align: center; padding: 40px 20px; color: var(--text-muted); }
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
                        <div class="crm-header-icon"><i class="fa fa-whatsapp"></i></div>
                        <div>
                            <h4>WhatsApp Settings</h4>
                            <small class="crm-subtitle">Connect the official Meta Cloud API, or a third-party gateway using just an API key and number.</small>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('whatsapp.chat') }}" class="btn btn-outline-success btn-sm"><i class="fa fa-comments"></i> Open Chat</a>
                        <a href="{{ route('whatsapp.campaigns.index') }}" class="btn btn-outline-success btn-sm"><i class="fa fa-paper-plane"></i> Campaigns</a>
                    </div>
                </div>

                <div class="channel-pick">
                    <div class="channel-pick-card" data-toggle="modal" data-target="#connectModal" data-channel="meta_cloud">
                        <i class="fa fa-facebook-official"></i>
                        <h6>Meta Cloud API (Official)</h6>
                        <p>WhatsApp Business Platform via Meta — needs a phone number ID and access token from your Meta developer app.</p>
                    </div>
                    <div class="channel-pick-card" data-toggle="modal" data-target="#connectModal" data-channel="unofficial">
                        <i class="fa fa-plug"></i>
                        <h6>Unofficial Gateway</h6>
                        <p>A third-party WhatsApp API provider (Ultramsg, Green-API, WasenderAPI, etc.) — just an API key and sender number.</p>
                    </div>
                </div>

                <div id="accountsList">
                    @forelse($accounts as $account)
                        <div class="account-card" data-id="{{ $account->id }}">
                            <div style="flex:1; min-width:220px;">
                                <span class="account-badge {{ $account->channel_type }}">{{ $account->channel_type === 'meta_cloud' ? 'Meta Cloud API' : 'Unofficial Gateway' }}</span>
                                @if($account->is_default)<span class="account-badge" style="background:#e0f2fe;color:#075985;">Default</span>@endif
                                <div class="acc-name mt-1">{{ $account->name }}</div>
                                <small class="text-muted">{{ $account->phone_number ?: 'No sender number set' }} &middot;
                                    <span class="status-dot {{ $account->is_active ? 'on' : 'off' }}"></span>{{ $account->is_active ? 'Active' : 'Paused' }}
                                    &middot; {{ $account->messages_sent_count }} sent / {{ $account->messages_received_count }} received
                                </small>
                                <div class="webhook-url-box">{{ $account->webhookUrl() }}</div>
                                @if($account->channel_type === 'meta_cloud')
                                    <small class="text-muted d-block mt-1">Verify Token: <code>{{ $account->verify_token }}</code></small>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-secondary btn-edit-account" data-id="{{ $account->id }}"><i class="fa fa-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-secondary btn-regen-account" data-id="{{ $account->id }}" title="Regenerate webhook URL"><i class="fa fa-refresh"></i></button>
                                <button class="btn btn-sm btn-outline-danger btn-delete-account" data-id="{{ $account->id }}"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state"><i class="fa fa-whatsapp" style="font-size:36px;"></i><p class="mt-2">No WhatsApp account connected yet — pick a channel above to get started.</p></div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>

    {{-- Connect / Edit modal --}}
    <div class="modal fade" id="connectModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="accountForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="accountModalTitle">Connect WhatsApp</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="accountId">
                        <input type="hidden" name="channel_type" id="channelType" value="meta_cloud">

                        <div class="form-group">
                            <label>Display Name</label>
                            <input type="text" name="name" id="accName" class="form-control" placeholder="e.g. Support Line" required>
                        </div>
                        <div class="form-group">
                            <label>WhatsApp Number (for reference)</label>
                            <input type="text" name="phone_number" id="accPhone" class="form-control" placeholder="e.g. +91 98765 43210">
                        </div>

                        <div id="metaFields">
                            <hr>
                            <p class="text-muted small">From your Meta App → WhatsApp → API Setup.</p>
                            <div class="form-group"><label>Phone Number ID</label><input type="text" name="phone_number_id" class="form-control"></div>
                            <div class="form-group"><label>Access Token</label><input type="password" name="access_token" class="form-control" autocomplete="new-password"></div>
                            <div class="form-group"><label>App Secret <small class="text-muted">(optional, verifies inbound signatures)</small></label><input type="password" name="app_secret" class="form-control" autocomplete="new-password"></div>
                            <div class="form-group"><label>WABA ID <small class="text-muted">(optional)</small></label><input type="text" name="waba_id" class="form-control"></div>
                        </div>

                        <div id="unofficialFields" style="display:none;">
                            <hr>
                            <p class="text-muted small">Your gateway provider's dashboard gives you these (Ultramsg, Green-API, WasenderAPI, etc).</p>
                            <div class="form-group"><label>API URL</label><input type="text" name="api_url" class="form-control" placeholder="https://api.example.com/instance123/messages/chat"></div>
                            <div class="form-group"><label>API Key</label><input type="password" name="api_key" class="form-control" autocomplete="new-password"></div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Send Key As</label>
                                    <select name="api_key_location" class="form-control">
                                        <option value="query">Query param</option>
                                        <option value="header">Header</option>
                                        <option value="body">Body field</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Key Param/Header Name</label>
                                    <input type="text" name="api_key_param" class="form-control" value="token">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>HTTP Method</label>
                                    <select name="http_method" class="form-control">
                                        <option value="post">POST</option>
                                        <option value="get">GET</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        function toggleChannelFields(channel) {
            $('#channelType').val(channel);
            $('#metaFields').toggle(channel === 'meta_cloud');
            $('#unofficialFields').toggle(channel === 'unofficial');
        }

        $('.channel-pick-card').on('click', function () {
            $('#accountForm')[0].reset();
            $('#accountId').val('');
            $('#accountModalTitle').text('Connect WhatsApp');
            toggleChannelFields($(this).data('channel'));
        });

        $('#accountForm').on('submit', function (e) {
            e.preventDefault();
            var id = $('#accountId').val();
            var url = id ? '/whatsapp/settings/' + id : '/whatsapp/settings';
            var method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url, method: method, data: $(this).serialize(),
                success: function (res) {
                    toastr.success(res.message || 'Saved');
                    setTimeout(function () { window.location.reload(); }, 700);
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                    }
                    toastr.error(msg);
                }
            });
        });

        $('.btn-edit-account').on('click', function () {
            var id = $(this).data('id');
            $.get('/whatsapp/settings/' + id, function (res) {
                var d = res.data;
                $('#accountForm')[0].reset();
                $('#accountId').val(d.id);
                $('#accName').val(d.name);
                $('#accPhone').val(d.phone_number);
                toggleChannelFields(d.channel_type);
                var c = d.credentials || {};
                Object.keys(c).forEach(function (key) {
                    $('#accountForm [name="' + key + '"]').val(c[key]);
                });
                $('#accountModalTitle').text('Edit ' + d.name);
                $('#connectModal').modal('show');
            });
        });

        $('.btn-regen-account').on('click', function () {
            if (!confirm('Generate a new webhook URL? The old one will stop working — update it on the provider side too.')) return;
            var id = $(this).data('id');
            $.post('/whatsapp/settings/' + id + '/regenerate', function (res) {
                toastr.success(res.message);
                setTimeout(function () { window.location.reload(); }, 900);
            });
        });

        $('.btn-delete-account').on('click', function () {
            if (!confirm('Remove this WhatsApp account? Its webhook URL will stop working.')) return;
            var id = $(this).data('id');
            $.ajax({
                url: '/whatsapp/settings/' + id, method: 'DELETE',
                success: function (res) { toastr.success(res.message); setTimeout(function () { window.location.reload(); }, 700); }
            });
        });
    </script>
</body>
</html>
