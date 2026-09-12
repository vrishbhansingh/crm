<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lead Integrations | CRM</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --border: #e5e7eb;
            --text-dark: #111827;
            --text-muted: #6b7280;
            --surface: #f8fafc;
        }

        .li-wrap { font-size: 13.5px; }

        .li-header {
            background: #fff; padding: 16px 20px; border-radius: 12px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05); margin-bottom: 18px;
        }
        .li-header h1 { font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 0 0 4px; }
        .li-header p { font-size: 12.5px; color: var(--text-muted); margin: 0; max-width: 700px; }

        .li-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 16px; }

        .li-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05); padding: 18px 20px; }
        .li-card-head { display: flex; align-items: flex-start; gap: 12px; }
        .li-icon { width: 40px; height: 40px; border-radius: 10px; background: #eef2ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0; }
        .li-title { font-weight: 700; font-size: 14.5px; color: var(--text-dark); display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .li-desc { font-size: 12.5px; color: var(--text-muted); margin-top: 3px; line-height: 1.5; }

        .li-badge { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; padding: 3px 9px; border-radius: 999px; }
        .li-badge.on { background: #dcfce7; color: #166534; }
        .li-badge.off { background: #f1f5f9; color: #64748b; }

        .li-url-row { display: flex; gap: 6px; margin-top: 14px; }
        .li-url-row input { flex: 1; font-size: 12px; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 0 10px; height: 34px; font-family: monospace; }

        .li-stats { font-size: 11.5px; color: var(--text-muted); margin-top: 8px; }
        .li-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 12px; }
        .li-actions .btn { font-size: 11.5px; padding: 4px 10px; }

        .li-toggle-row { display: flex; align-items: center; justify-content: space-between; margin-top: 12px; padding-top: 12px; border-top: 1px dashed var(--border); }

        .li-steps-toggle { font-size: 12px; color: var(--primary); font-weight: 600; cursor: pointer; margin-top: 12px; display: inline-block; }

        .guide-steps { list-style: none; margin: 12px 0 0; padding: 0; counter-reset: guide-step; }
        .guide-steps li { counter-increment: guide-step; position: relative; padding: 0 0 12px 30px; margin-bottom: 12px; border-bottom: 1px dashed var(--border); font-size: 12px; color: #374151; line-height: 1.55; }
        .guide-steps li:last-child { padding-bottom: 0; margin-bottom: 0; border-bottom: none; }
        .guide-steps li::before {
            content: counter(guide-step); position: absolute; left: 0; top: 0;
            width: 19px; height: 19px; border-radius: 50%; background: #eef2ff; color: var(--primary);
            font-weight: 700; font-size: 10.5px; display: flex; align-items: center; justify-content: center;
        }

        .li-verify-note { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; border-radius: 8px; padding: 8px 10px; font-size: 11.5px; margin-top: 10px; }

        .li-log-row { padding: 9px 0; border-bottom: 1px solid var(--border); font-size: 12px; }
        .li-log-row:last-child { border-bottom: none; }
        .li-log-status { font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 2px 7px; border-radius: 999px; }
        .li-log-status.created { background: #dcfce7; color: #166534; }
        .li-log-status.duplicate { background: #fef3c7; color: #92400e; }
        .li-log-status.ignored { background: #f1f5f9; color: #64748b; }
        .li-log-status.failed { background: #fee2e2; color: #991b1b; }
        .li-log-payload { font-family: monospace; font-size: 10.5px; color: #64748b; background: var(--surface); border-radius: 6px; padding: 6px 8px; margin-top: 4px; white-space: pre-wrap; word-break: break-all; max-height: 120px; overflow-y: auto; }
    </style>
</head>

<body>

    <div class="container-scroller">
        @include('include.header')

        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')

            <div class="content-wrapper li-wrap">

                <div class="li-header">
                    <h1>Lead Integrations</h1>
                    <p>Connect the platforms you already advertise or sell on — every new enquiry from a connected platform becomes a lead here automatically, assigned the same way a lead created by hand would be.</p>
                </div>

                <div class="li-grid">
                    @foreach($platforms as $platform => $meta)
                        @php($integration = $integrations->get($platform))
                        <div class="li-card">
                            <div class="li-card-head">
                                <div class="li-icon"><i class="fa {{ $meta['icon'] }}"></i></div>
                                <div style="flex:1">
                                    <div class="li-title">
                                        {{ $meta['label'] }}
                                        @if($integration)
                                            <span class="li-badge {{ $integration->is_active ? 'on' : 'off' }}">{{ $integration->is_active ? 'Connected' : 'Paused' }}</span>
                                        @endif
                                    </div>
                                    <div class="li-desc">{{ $meta['description'] }}</div>
                                </div>
                            </div>

                            @if($integration)
                                <div class="li-url-row">
                                    <input type="text" readonly value="{{ $integration->webhookUrl() }}" onclick="this.select()" id="url-{{ $platform }}">
                                    <button class="btn btn-sm btn-outline-primary copyUrlBtn" data-target="url-{{ $platform }}" type="button"><i class="fa fa-copy"></i></button>
                                </div>

                                @if($integration->verify_token)
                                    <div class="li-verify-note"><i class="fa fa-key"></i> Verify Token: <code>{{ $integration->verify_token }}</code></div>
                                @endif

                                <div class="li-stats">
                                    {{ $integration->leads_created_count }} lead{{ $integration->leads_created_count === 1 ? '' : 's' }} captured
                                    @if($integration->last_received_at)
                                        &middot; last received {{ $integration->last_received_at->diffForHumans() }}
                                    @endif
                                </div>

                                <div class="li-toggle-row">
                                    <label class="mb-0" style="font-size:12px;font-weight:600;color:#374151">
                                        <input type="checkbox" class="toggleActiveInput" data-id="{{ $integration->id }}" {{ $integration->is_active ? 'checked' : '' }}>
                                        Active
                                    </label>
                                    <div class="li-actions" style="margin-top:0">
                                        <button class="btn btn-sm btn-outline-secondary viewLogsBtn" data-id="{{ $integration->id }}" data-label="{{ $meta['label'] }}">Recent activity</button>
                                        @can('integrations.edit')
                                        <button class="btn btn-sm btn-outline-secondary regenerateBtn" data-id="{{ $integration->id }}">Regenerate URL</button>
                                        @endcan
                                        @can('integrations.delete')
                                        <button class="btn btn-sm btn-outline-danger removeBtn" data-id="{{ $integration->id }}" data-label="{{ $meta['label'] }}">Remove</button>
                                        @endcan
                                    </div>
                                </div>
                            @else
                                @can('integrations.create')
                                <button class="btn btn-sm btn-primary connectBtn mt-2" data-platform="{{ $platform }}" data-label="{{ $meta['label'] }}"><i class="fa fa-plug"></i> Connect</button>
                                @endcan
                            @endif

                            <a class="li-steps-toggle" data-toggle="collapse" href="#steps-{{ $platform }}"><i class="fa fa-list-ol"></i> How to set this up</a>
                            <div class="collapse" id="steps-{{ $platform }}">
                                <ol class="guide-steps">
                                    @foreach($meta['steps'] as $step)
                                        <li>{{ $step }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    @endforeach
                </div>

                @include('include.footer')
            </div>
        </div>
    </div>

    <!-- Connect modal -->
    <div class="modal fade" id="connectModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Connect <span id="connectPlatformLabel"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="connectForm">
                    <input type="hidden" id="connect_platform">
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label>Name this connection</label>
                            <input type="text" id="connect_name" class="form-control" placeholder="e.g. Main website enquiry form" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create webhook URL</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Logs modal -->
    <div class="modal fade" id="logsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Recent activity &mdash; <span id="logsPlatformLabel"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" style="max-height:60vh;overflow-y:auto">
                    <div id="logsList"><p class="text-muted text-center py-4">Loading&hellip;</p></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
        const esc = value => $('<div>').text(value ?? '').html();

        $(document).on('click', '.connectBtn', function () {
            $('#connect_platform').val($(this).data('platform'));
            $('#connectPlatformLabel').text($(this).data('label'));
            $('#connect_name').val($(this).data('label'));
            $('#connectModal').modal('show');
        });

        $(document).on('submit', '#connectForm', function (e) {
            e.preventDefault();
            $.post("{{ route('integrations.store') }}", {
                platform: $('#connect_platform').val(),
                name: $('#connect_name').val(),
            }, function (response) {
                if (response.status) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 700);
                } else {
                    toastr.error(response.message);
                }
            }).fail(function (xhr) {
                toastr.error(xhr.responseJSON?.message || xhr.responseJSON?.errors?.name?.[0] || xhr.responseJSON?.errors?.platform?.[0] || 'Something went wrong');
            });
        });

        $(document).on('click', '.copyUrlBtn', function () {
            const input = document.getElementById($(this).data('target'));
            input.select();
            navigator.clipboard?.writeText(input.value);
            toastr.success('Webhook URL copied.');
        });

        $(document).on('change', '.toggleActiveInput', function () {
            const id = $(this).data('id');
            const checked = $(this).is(':checked');
            $.ajax({
                url: "{{ url('integrations') }}/" + id,
                type: 'POST',
                data: { _method: 'PUT', is_active: checked ? 1 : 0 },
                success: function (response) {
                    toastr.success(response.message);
                },
                error: function () {
                    toastr.error('Could not update this integration.');
                }
            });
        });

        $(document).on('click', '.regenerateBtn', function () {
            const id = $(this).data('id');
            if (!confirm('Generate a new webhook URL? The old one will stop working immediately, so you\'ll need to update it on the platform side too.')) return;
            $.post("{{ url('integrations') }}/" + id + '/regenerate', {}, function (response) {
                if (response.status) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 900);
                } else {
                    toastr.error(response.message);
                }
            }).fail(function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Something went wrong');
            });
        });

        $(document).on('click', '.removeBtn', function () {
            const id = $(this).data('id');
            const label = $(this).data('label');
            if (!confirm('Remove the ' + label + ' connection? Its webhook URL will stop accepting leads immediately.')) return;
            $.ajax({
                url: "{{ url('integrations') }}/" + id,
                type: 'POST',
                data: { _method: 'DELETE' },
                success: function (response) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 700);
                },
                error: function () {
                    toastr.error('Something went wrong');
                }
            });
        });

        $(document).on('click', '.viewLogsBtn', function () {
            const id = $(this).data('id');
            $('#logsPlatformLabel').text($(this).data('label'));
            $('#logsList').html('<p class="text-muted text-center py-4">Loading&hellip;</p>');
            $('#logsModal').modal('show');

            $.get("{{ url('integrations') }}/" + id + '/logs', function (response) {
                if (!response.data.length) {
                    $('#logsList').html('<p class="text-muted text-center py-4">No webhook calls received yet.</p>');
                    return;
                }
                let html = '';
                response.data.forEach((log) => {
                    const when = log.created_at ? new Date(log.created_at).toLocaleString() : '';
                    html += `<div class="li-log-row">
                        <span class="li-log-status ${log.status}">${esc(log.status)}</span>
                        <span style="color:#94a3b8;margin-left:8px">${esc(when)}</span>
                        ${log.message ? `<div style="margin-top:4px">${esc(log.message)}</div>` : ''}
                        <div class="li-log-payload">${esc(JSON.stringify(log.payload))}</div>
                    </div>`;
                });
                $('#logsList').html(html);
            }).fail(function () {
                $('#logsList').html('<p class="text-danger text-center py-4">Could not load activity.</p>');
            });
        });
    </script>

</body>

</html>
