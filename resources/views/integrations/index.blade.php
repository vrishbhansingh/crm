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
            --primary: #2563eb; --primary-dark: #1d4ed8; --border: #e5e7eb;
            --text-dark: #111827; --text-muted: #6b7280; --surface: #f8fafc;
        }

        .li-wrap { font-size: 13.5px; }

        .li-header {
            background: #fff; padding: 16px 20px; border-radius: 12px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05); margin-bottom: 18px;
            display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
        }
        .li-header h1 { font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 0 0 4px; }
        .li-header p { font-size: 12.5px; color: var(--text-muted); margin: 0; max-width: 640px; }

        /* Platform quick-connect strip */
        .li-picker { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 4px; margin-bottom: 20px; }
        .li-pick-tile {
            flex: 0 0 auto; width: 150px; background: #fff; border: 1px solid var(--border); border-radius: 12px;
            padding: 12px; cursor: pointer; transition: .15s; text-align: center;
        }
        .li-pick-tile:hover { border-color: var(--primary); box-shadow: 0 4px 14px rgba(37,99,235,.12); transform: translateY(-1px); }
        .li-pick-tile .li-icon { margin: 0 auto 8px; }
        .li-pick-tile .li-pick-label { font-size: 12px; font-weight: 700; color: var(--text-dark); }
        .li-pick-tile .li-pick-count { font-size: 10.5px; color: var(--text-muted); margin-top: 2px; }
        .li-pick-tile .li-pick-count.has-some { color: #166534; font-weight: 700; }

        .li-icon { width: 38px; height: 38px; border-radius: 10px; background: #eef2ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }

        /* Platform group sections */
        .li-group { background: #fff; border-radius: 12px; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05); margin-bottom: 16px; overflow: hidden; }
        .li-group-head { display: flex; align-items: center; gap: 12px; padding: 16px 20px; }
        .li-group-title { font-weight: 700; font-size: 14.5px; color: var(--text-dark); display: flex; align-items: center; gap: 8px; }
        .li-group-desc { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
        .li-count-badge { font-size: 10.5px; font-weight: 800; padding: 2px 9px; border-radius: 999px; background: var(--surface); color: var(--text-muted); }
        .li-count-badge.has-some { background: #dcfce7; color: #166534; }

        .li-table { width: 100%; border-collapse: collapse; }
        .li-table th { text-align: left; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted); padding: 8px 20px; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); background: var(--surface); }
        .li-table td { padding: 10px 20px; border-bottom: 1px solid var(--border); vertical-align: middle; font-size: 12.5px; }
        .li-table tr:last-child td { border-bottom: none; }
        .li-row-name { font-weight: 700; color: var(--text-dark); }
        .li-row-sub { font-size: 11px; color: var(--text-muted); font-family: monospace; margin-top: 2px; display: flex; align-items: center; gap: 6px; }
        .li-row-sub .copyUrlBtn { border: none; background: none; color: var(--primary); cursor: pointer; padding: 0; font-size: 11px; }
        .li-pill { font-size: 11px; color: #374151; background: var(--surface); border-radius: 6px; padding: 3px 8px; display: inline-block; }
        .li-pill.muted { color: #9ca3af; }

        .li-empty { padding: 24px 20px; text-align: center; color: var(--text-muted); font-size: 12.5px; }

        .li-actions-btn { width: 26px; height: 26px; border-radius: 7px; border: 1px solid var(--border); background: #fff; color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; font-size: 11px; }
        .li-dropdown-menu { font-size: 12px; border-radius: 10px; box-shadow: 0 12px 32px rgba(15,23,42,.12); border: none; padding: 4px; min-width: 170px; }
        .li-dropdown-menu .dropdown-item { border-radius: 6px; padding: 6px 10px; display: flex; align-items: center; gap: 8px; }
        .li-dropdown-menu .dropdown-item.text-danger:hover { background: #fef2f2; }

        .li-steps-toggle { font-size: 11.5px; color: var(--primary); font-weight: 600; cursor: pointer; display: block; padding: 10px 20px; border-top: 1px dashed var(--border); }
        .guide-steps { list-style: none; margin: 0; padding: 12px 20px 16px; counter-reset: guide-step; }
        .guide-steps li { counter-increment: guide-step; position: relative; padding: 0 0 10px 28px; margin-bottom: 10px; border-bottom: 1px dashed var(--border); font-size: 12px; color: #374151; line-height: 1.5; }
        .guide-steps li:last-child { padding-bottom: 0; margin-bottom: 0; border-bottom: none; }
        .guide-steps li::before { content: counter(guide-step); position: absolute; left: 0; top: 0; width: 18px; height: 18px; border-radius: 50%; background: #eef2ff; color: var(--primary); font-weight: 700; font-size: 10px; display: flex; align-items: center; justify-content: center; }
        .li-verify-note { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; border-radius: 8px; padding: 8px 10px; font-size: 11.5px; margin: 0 20px 14px; }

        /* Modal form */
        .li-modal .modal-dialog { max-width: 700px; }
        .li-form-section { margin-bottom: 16px; }
        .li-form-section-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; color: #94a3b8; margin-bottom: 8px; }
        .li-mapping-row { display: flex; gap: 8px; margin-bottom: 8px; align-items: center; }
        .li-mapping-row select, .li-mapping-row input { font-size: 12.5px; }
        .li-mapping-remove { border: none; background: none; color: #ef4444; font-size: 14px; padding: 0 6px; }
        .li-add-mapping { font-size: 12px; color: var(--primary); font-weight: 600; background: none; border: none; padding: 0; }

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
                    <div>
                        <h1>Lead Integrations</h1>
                        <p>Connect the platforms you sell on — every enquiry becomes a lead here automatically, assigned and routed exactly the way you configure below.</p>
                    </div>
                </div>

                <div class="li-picker">
                    @foreach($platforms as $key => $meta)
                        @php($count = $countsByPlatform->get($key, 0))
                        <div class="li-pick-tile addWebhookBtn" data-platform="{{ $key }}" data-label="{{ $meta['label'] }}">
                            <div class="li-icon"><i class="fa {{ $meta['icon'] }}"></i></div>
                            <div class="li-pick-label">{{ $meta['label'] }}</div>
                            <div class="li-pick-count {{ $count ? 'has-some' : '' }}">{{ $count ? $count.' connected' : 'Not connected' }}</div>
                        </div>
                    @endforeach
                </div>

                @foreach($platforms as $key => $meta)
                    @php($rows = $integrations->where('platform', $key))
                    <div class="li-group">
                        <div class="li-group-head">
                            <div class="li-icon"><i class="fa {{ $meta['icon'] }}"></i></div>
                            <div style="flex:1">
                                <div class="li-group-title">{{ $meta['label'] }} <span class="li-count-badge {{ $rows->count() ? 'has-some' : '' }}">{{ $rows->count() }} webhook{{ $rows->count() === 1 ? '' : 's' }}</span></div>
                                <div class="li-group-desc">{{ $meta['description'] }}</div>
                            </div>
                            @can('integrations.create')
                            <button class="btn btn-sm btn-primary addWebhookBtn" data-platform="{{ $key }}" data-label="{{ $meta['label'] }}"><i class="fa fa-plus"></i> Add webhook</button>
                            @endcan
                        </div>

                        @if($rows->isEmpty())
                            <div class="li-empty">No {{ $meta['label'] }} webhooks yet — add one to start capturing leads from this platform.</div>
                        @else
                            <div class="table-responsive">
                                <table class="li-table">
                                    <thead>
                                        <tr>
                                            <th>Webhook</th>
                                            <th>Funnel</th>
                                            <th>Assigned</th>
                                            <th>Leads</th>
                                            <th>Status</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rows as $row)
                                            <tr>
                                                <td>
                                                    <div class="li-row-name">{{ $row->name }}</div>
                                                    <div class="li-row-sub">
                                                        <span id="url-{{ $row->id }}" style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;vertical-align:bottom">{{ $row->webhookUrl() }}</span>
                                                        <button type="button" class="copyUrlBtn" data-url="{{ $row->webhookUrl() }}"><i class="fa fa-copy"></i></button>
                                                    </div>
                                                    @if($row->verify_token)
                                                        <div class="li-row-sub">Verify token: <code>{{ $row->verify_token }}</code></div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php($pipeline = $row->pipeline_id ? $pipelines->firstWhere('id', $row->pipeline_id) : null)
                                                    <span class="li-pill {{ $pipeline ? '' : 'muted' }}">{{ $pipeline?->name ?? 'Lead only' }}</span>
                                                </td>
                                                <td>
                                                    @php($assignee = $row->default_assigned_to ? $users->firstWhere('id', $row->default_assigned_to) : null)
                                                    <span class="li-pill {{ $assignee ? '' : 'muted' }}">{{ $assignee?->name ?? 'Auto-assign' }}</span>
                                                </td>
                                                <td>
                                                    {{ $row->leads_created_count }}
                                                    @if($row->last_received_at)
                                                        <div class="li-row-sub" style="font-family:inherit">last {{ $row->last_received_at->diffForHumans() }}</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <label class="mb-0" style="cursor:pointer">
                                                        <input type="checkbox" class="toggleActiveInput" data-id="{{ $row->id }}" {{ $row->is_active ? 'checked' : '' }}>
                                                        {{ $row->is_active ? 'Active' : 'Paused' }}
                                                    </label>
                                                </td>
                                                <td class="text-right">
                                                    <div class="dropdown">
                                                        <button class="li-actions-btn" type="button" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></button>
                                                        <div class="dropdown-menu dropdown-menu-right li-dropdown-menu">
                                                            <a class="dropdown-item viewLogsBtn" href="#" data-id="{{ $row->id }}" data-label="{{ $row->name }}"><i class="fa fa-history"></i> Recent activity</a>
                                                            @can('integrations.edit')
                                                            <a class="dropdown-item editWebhookBtn" href="#" data-id="{{ $row->id }}"><i class="fa fa-pencil"></i> Edit settings</a>
                                                            <a class="dropdown-item regenerateBtn" href="#" data-id="{{ $row->id }}"><i class="fa fa-refresh"></i> Regenerate URL</a>
                                                            @endcan
                                                            @can('integrations.delete')
                                                            <a class="dropdown-item text-danger removeBtn" href="#" data-id="{{ $row->id }}" data-label="{{ $row->name }}"><i class="fa fa-trash"></i> Remove</a>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <a class="li-steps-toggle" data-toggle="collapse" href="#steps-{{ $key }}"><i class="fa fa-list-ol"></i> How to set this up</a>
                        <div class="collapse" id="steps-{{ $key }}">
                            <ol class="guide-steps">
                                @foreach($meta['steps'] as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                @endforeach

                @include('include.footer')
            </div>
        </div>
    </div>

    <!-- Create / Edit webhook modal -->
    <div class="modal fade li-modal" id="webhookModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Connect <span id="modalPlatformLabel"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="webhookForm">
                    <input type="hidden" id="webhook_id">
                    <input type="hidden" id="webhook_platform">
                    <div class="modal-body">
                        <div class="li-form-section">
                            <label>Name this webhook</label>
                            <input type="text" id="webhook_name" class="form-control" placeholder="e.g. Main website enquiry form" required>
                        </div>

                        <div class="li-form-section">
                            <div class="li-form-section-label">New lead defaults</div>
                            <div class="form-row">
                                <div class="col-md-4 mb-2">
                                    <label>Lead type</label>
                                    <select id="webhook_lead_type" class="form-control">
                                        <option value="">Default (Inquiry)</option>
                                        @foreach($leadTypes as $type)
                                            <option value="{{ $type->code }}">{{ $type->label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label>Lead status</label>
                                    <select id="webhook_lead_status" class="form-control">
                                        <option value="">Default (New)</option>
                                        @foreach($leadStatuses as $status)
                                            <option value="{{ $status->code }}">{{ $status->label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label>Priority</label>
                                    <select id="webhook_priority" class="form-control">
                                        <option value="">Default (Medium)</option>
                                        @foreach($leadPriorities as $priority)
                                            <option value="{{ $priority->code }}">{{ $priority->label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="li-form-section">
                            <div class="li-form-section-label">Routing</div>
                            <div class="form-row">
                                <div class="col-md-6 mb-2">
                                    <label>Assign to</label>
                                    <select id="webhook_assigned_to" class="form-control">
                                        <option value="">Auto-assign (round robin)</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label>Funnel</label>
                                    <select id="webhook_pipeline_id" class="form-control">
                                        <option value="">Lead only — don't create a deal</option>
                                        @foreach($pipelines as $pipeline)
                                            <option value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Automatically adds a Deal in this funnel's first stage alongside the lead.</small>
                                </div>
                            </div>
                        </div>

                        <div class="li-form-section" id="mappingSection">
                            <div class="li-form-section-label">Field mapping <span class="text-muted" style="text-transform:none;font-weight:400">(optional — only if this platform's field names differ from the usual ones)</span></div>
                            <div id="mappingRows"></div>
                            <button type="button" class="li-add-mapping" id="addMappingRow"><i class="fa fa-plus"></i> Add field mapping</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="webhookSubmitBtn">Create webhook</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Mapping row template -->
    <template id="mappingRowTemplate">
        <div class="li-mapping-row">
            <select class="form-control map-field" style="max-width:150px">
                @foreach($mappableFields as $field)
                    <option value="{{ $field }}">{{ ucfirst($field) }}</option>
                @endforeach
            </select>
            <span class="text-muted">=</span>
            <input type="text" class="form-control map-key" placeholder="Incoming field name, e.g. phn">
            <button type="button" class="li-mapping-remove"><i class="fa fa-times"></i></button>
        </div>
    </template>

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
        const webhooksIndexUrl = "{{ route('integrations.index') }}";
        const webhooksBaseUrl = "{{ url('integrations') }}";

        function addMappingRow(field, key) {
            const clone = document.getElementById('mappingRowTemplate').content.cloneNode(true);
            const $row = $(clone).find('.li-mapping-row');
            if (field) $row.find('.map-field').val(field);
            if (key) $row.find('.map-key').val(key);
            $('#mappingRows').append(clone);
        }

        $(document).on('click', '#addMappingRow', function () { addMappingRow(); });
        $(document).on('click', '.li-mapping-remove', function () { $(this).closest('.li-mapping-row').remove(); });

        function resetWebhookForm() {
            $('#webhookForm')[0].reset();
            $('#webhook_id').val('');
            $('#mappingRows').empty();
        }

        // ---- Connect / Add webhook ----
        $(document).on('click', '.addWebhookBtn', function () {
            resetWebhookForm();
            const platform = $(this).data('platform');
            $('#webhook_platform').val(platform);
            $('#modalPlatformLabel').text($(this).data('label'));
            $('#webhookSubmitBtn').text('Create webhook');
            $('#webhookModal').modal('show');
        });

        // ---- Edit webhook ----
        $(document).on('click', '.editWebhookBtn', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            resetWebhookForm();

            $.get(webhooksBaseUrl + '/' + id, function (response) {
                const data = response.data;
                $('#webhook_id').val(data.id);
                $('#webhook_platform').val(data.platform);
                $('#modalPlatformLabel').text(data.name);
                $('#webhook_name').val(data.name);
                $('#webhook_lead_type').val(data.default_lead_type || '');
                $('#webhook_lead_status').val(data.default_lead_status || '');
                $('#webhook_priority').val(data.default_priority || '');
                $('#webhook_assigned_to').val(data.default_assigned_to || '');
                $('#webhook_pipeline_id').val(data.pipeline_id || '');

                const mapping = data.field_mapping || {};
                Object.keys(mapping).forEach((field) => addMappingRow(field, mapping[field]));

                $('#webhookSubmitBtn').text('Save changes');
                $('#webhookModal').modal('show');
            }).fail(function () {
                toastr.error('Could not load this webhook.');
            });
        });

        $(document).on('submit', '#webhookForm', function (e) {
            e.preventDefault();
            const id = $('#webhook_id').val();

            const mapping = {};
            $('#mappingRows .li-mapping-row').each(function () {
                const field = $(this).find('.map-field').val();
                const key = $(this).find('.map-key').val().trim();
                if (field && key) mapping[field] = key;
            });

            const payload = {
                platform: $('#webhook_platform').val(),
                name: $('#webhook_name').val(),
                default_lead_type: $('#webhook_lead_type').val(),
                default_lead_status: $('#webhook_lead_status').val(),
                default_priority: $('#webhook_priority').val(),
                default_assigned_to: $('#webhook_assigned_to').val(),
                pipeline_id: $('#webhook_pipeline_id').val(),
                field_mapping: mapping,
            };

            const request = id
                ? $.ajax({ url: webhooksBaseUrl + '/' + id, type: 'POST', data: Object.assign({ _method: 'PUT' }, payload) })
                : $.post(webhooksIndexUrl, payload);

            request.done(function (response) {
                if (response.status) {
                    toastr.success(response.message);
                    $('#webhookModal').modal('hide');
                    setTimeout(() => location.reload(), 700);
                } else {
                    toastr.error(response.message);
                }
            }).fail(function (xhr) {
                const errors = xhr.responseJSON?.errors;
                const firstError = errors ? Object.values(errors)[0]?.[0] : null;
                toastr.error(firstError || xhr.responseJSON?.message || 'Something went wrong');
            });
        });

        $(document).on('click', '.copyUrlBtn', function () {
            navigator.clipboard?.writeText($(this).data('url'));
            toastr.success('Webhook URL copied.');
        });

        $(document).on('change', '.toggleActiveInput', function () {
            const id = $(this).data('id');
            const checked = $(this).is(':checked');
            $.ajax({
                url: webhooksBaseUrl + '/' + id,
                type: 'POST',
                data: { _method: 'PUT', is_active: checked ? 1 : 0 },
                success: function (response) { toastr.success(response.message); },
                error: function () { toastr.error('Could not update this webhook.'); }
            });
        });

        $(document).on('click', '.regenerateBtn', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            if (!confirm('Generate a new webhook URL? The old one will stop working immediately, so you\'ll need to update it on the platform side too.')) return;
            $.post(webhooksBaseUrl + '/' + id + '/regenerate', {}, function (response) {
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

        $(document).on('click', '.removeBtn', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            const label = $(this).data('label');
            if (!confirm('Remove the "' + label + '" webhook? Its URL will stop accepting leads immediately.')) return;
            $.ajax({
                url: webhooksBaseUrl + '/' + id,
                type: 'POST',
                data: { _method: 'DELETE' },
                success: function (response) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 700);
                },
                error: function () { toastr.error('Something went wrong'); }
            });
        });

        $(document).on('click', '.viewLogsBtn', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            $('#logsPlatformLabel').text($(this).data('label'));
            $('#logsList').html('<p class="text-muted text-center py-4">Loading&hellip;</p>');
            $('#logsModal').modal('show');

            $.get(webhooksBaseUrl + '/' + id + '/logs', function (response) {
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
