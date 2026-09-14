<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WhatsApp Campaigns | CRM Admin</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">

    <style>
        :root { --primary: #25d366; --primary-dark: #128c7e; --border: #e5e7eb; --text-dark: #111827; --text-muted: #6b7280; }

        .crm-page-header {
            background: #fff; padding: 18px 22px; border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06); border-left: 4px solid var(--primary);
            display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 20px;
        }
        .crm-header-left { display: flex; align-items: center; gap: 14px; }
        .crm-header-icon {
            width: 42px; height: 42px; border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .crm-page-header h4 { font-weight: 700; font-size: 18px; color: var(--text-dark); margin: 0; }
        .crm-subtitle { color: var(--text-muted); font-size: 13px; }

        .crm-card { background: #fff; border-radius: 14px; box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05); border: 1px solid var(--border); padding: 4px; }

        .status-pill { padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
        .status-pill.draft { background: #f3f4f6; color: #6b7280; }
        .status-pill.scheduled { background: #fef3c7; color: #92400e; }
        .status-pill.sending { background: #dbeafe; color: #1d4ed8; }
        .status-pill.sent { background: #dcfce7; color: #15803d; }
        .status-pill.failed { background: #fee2e2; color: #b91c1c; }

        #audiencePreviewCount { font-weight: 700; color: var(--primary-dark); }

        [data-theme="dark"] {
            --border: #2a2e40;
            --text-dark: #eef0f6;
            --text-muted: #9aa1b5;
        }
        [data-theme="dark"] .crm-page-header,
        [data-theme="dark"] .crm-card {
            background: #1a1d2b;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.3);
        }
        [data-theme="dark"] .status-pill.draft { background: rgba(154, 161, 181, 0.16); color: #9aa1b5; }
        [data-theme="dark"] .status-pill.scheduled { background: rgba(250, 204, 21, 0.16); color: #facc15; }
        [data-theme="dark"] .status-pill.sending { background: rgba(147, 164, 253, 0.16); color: #93a4fd; }
        [data-theme="dark"] .status-pill.sent { background: rgba(74, 222, 128, 0.16); color: #4ade80; }
        [data-theme="dark"] .status-pill.failed { background: rgba(239, 68, 68, 0.16); color: #fca5a5; }
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
                            <h4>WhatsApp Campaigns</h4>
                            <small class="crm-subtitle">Send a WhatsApp message to a filtered list of leads, contacts, or companies.</small>
                        </div>
                    </div>
                    @can('whatsapp.create')
                    <button class="btn btn-success btn-sm" id="addCampaignBtn" data-toggle="modal" data-target="#campaignModal" @if($accounts->isEmpty()) disabled title="Connect a WhatsApp account first" @endif>
                        <i class="fa fa-plus"></i> New Campaign
                    </button>
                    @endcan
                </div>

                @if($accounts->isEmpty())
                <div class="alert alert-warning">
                    No WhatsApp account connected yet. <a href="{{ route('whatsapp.settings.index') }}">Connect one first</a> — a campaign always sends from a connected account.
                </div>
                @endif

                <div class="crm-card">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Campaign</th>
                                    <th>Message</th>
                                    <th>Audience</th>
                                    <th>Status</th>
                                    <th>Recipients</th>
                                    <th>Sent</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="campaignTable">
                                <tr><td colspan="7" class="text-center text-muted py-4">Loading…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                @include('include.footer')
            </div>
        </div>
    </div>

    <div class="modal fade" id="campaignModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New WhatsApp Campaign</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="campaignForm">
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Campaign Name</label>
                                <input type="text" id="campaign_name" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Send From</label>
                                <select id="campaign_account_id" class="form-control" required>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}" data-channel="{{ $acc->channel_type }}">{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Message Type</label>
                            <select id="campaign_message_type" class="form-control">
                                <option value="text">Free text</option>
                                <option value="template">Approved template (required outside Meta's 24h window)</option>
                            </select>
                        </div>

                        <div class="form-group" id="bodyField">
                            <label>Message <small class="text-muted">(supports @{{lead.name}}, @{{lead.company_name}}, @{{organization.name}}, etc.)</small></label>
                            <textarea id="campaign_body" class="form-control" rows="3" placeholder="Hi @{{lead.name}}, ..."></textarea>
                        </div>
                        <div class="form-group" id="templateField" style="display:none;">
                            <label>Approved Template Name</label>
                            <input type="text" id="campaign_template_name" class="form-control" placeholder="e.g. order_update">
                        </div>

                        <div class="form-group">
                            <label>Send to</label>
                            <select id="campaign_audience_type" class="form-control">
                                <option value="leads">Leads</option>
                                <option value="contacts">Contacts</option>
                                <option value="companies">Companies</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Filters <small class="text-muted">(leave any filter on "Any" to not narrow by it)</small></label>
                            <div id="audienceFilters" class="form-row"></div>
                        </div>

                        <div class="form-group">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="previewAudienceBtn">
                                <i class="fa fa-refresh"></i> Check recipient count
                            </button>
                            <span id="audiencePreviewCount" class="ml-2"></span>
                        </div>

                        <div class="form-group">
                            <label>Schedule for later <small class="text-muted">(optional — leave blank to only save as a draft; send it from the list when ready)</small></label>
                            <input type="datetime-local" id="campaign_scheduled_at" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Campaign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>

    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });

        const esc = value => $('<div>').text(value ?? '').html();

        const FILTER_FIELDS = {
            leads: [
                { key: 'lead_status', label: 'Lead Status', masterType: 'lead_status' },
                { key: 'lead_source', label: 'Lead Source', masterType: 'lead_source' },
                { key: 'priority', label: 'Priority', masterType: 'priority' },
            ],
            contacts: [{ key: 'status', label: 'Status', options: ['Active', 'Inactive'] }],
            companies: [{ key: 'status', label: 'Status', options: ['Active', 'Inactive'] }],
        };

        function statusLabel(status) { return status.charAt(0).toUpperCase() + status.slice(1); }

        function loadCampaigns() {
            $.get("{{ route('whatsapp.campaigns.data') }}", function (response) {
                let rows = '';
                response.data.forEach((c) => {
                    const canSend = ['draft', 'scheduled', 'failed'].includes(c.status);
                    rows += `
                        <tr>
                            <td>${esc(c.name)}</td>
                            <td class="text-muted text-capitalize">${esc(c.message_type)}</td>
                            <td class="text-capitalize">${esc(c.audience_type)}</td>
                            <td><span class="status-pill ${c.status}">${statusLabel(c.status)}</span></td>
                            <td>${c.total_recipients}</td>
                            <td>${c.sent_count}${c.failed_count ? ` <span class="text-danger">(${c.failed_count} failed)</span>` : ''}</td>
                            <td class="text-right">
                                @can('whatsapp.send')
                                ${canSend ? `<button class="btn btn-sm btn-outline-success sendCampaignBtn" data-id="${c.id}" data-name="${esc(c.name)}"><i class="fa fa-paper-plane"></i> Send</button>` : ''}
                                @endcan
                                @can('whatsapp.delete')
                                ${c.status !== 'sending' && c.status !== 'sent' ? `<button class="btn btn-sm btn-outline-danger deleteCampaignBtn" data-id="${c.id}"><i class="fa fa-trash"></i></button>` : ''}
                                @endcan
                            </td>
                        </tr>`;
                });
                $('#campaignTable').html(rows || '<tr><td colspan="7" class="text-center text-muted py-4">No campaigns yet.</td></tr>');
            });
        }

        function renderAudienceFilters() {
            const type = $('#campaign_audience_type').val();
            const fields = FILTER_FIELDS[type] || [];
            let html = '';
            fields.forEach((field) => {
                html += `<div class="form-group col-md-4"><label>${esc(field.label)}</label><select class="form-control audience-filter-input" data-key="${field.key}" ${field.masterType ? `data-master-type="${field.masterType}"` : ''}><option value="">Any</option>`;
                (field.options || []).forEach((opt) => { html += `<option value="${esc(opt)}">${esc(opt)}</option>`; });
                html += `</select></div>`;
            });
            $('#audienceFilters').html(html);
            $('#audiencePreviewCount').text('');
            loadMasterDropdowns();
        }

        function loadMasterDropdowns() {
            $('#audienceFilters select[data-master-type]').each(function () {
                const $select = $(this);
                const type = $select.data('master-type');
                $.get("{{ url('master-data/lookup') }}/" + type, function (response) {
                    response.data.forEach(function (option) { $select.append(`<option value="${esc(option.code)}">${esc(option.label)}</option>`); });
                });
            });
        }

        function collectFilters() {
            const filters = {};
            $('.audience-filter-input').each(function () { const value = $(this).val(); if (value) filters[$(this).data('key')] = value; });
            return filters;
        }

        $(document).on('change', '#campaign_audience_type', renderAudienceFilters);

        $(document).on('change', '#campaign_message_type', function () {
            const isTemplate = $(this).val() === 'template';
            $('#bodyField').toggle(!isTemplate);
            $('#templateField').toggle(isTemplate);
        });

        $(document).on('click', '#addCampaignBtn', function () {
            $('#campaignForm')[0].reset();
            $('#bodyField').show();
            $('#templateField').hide();
            renderAudienceFilters();
        });

        $(document).on('click', '#previewAudienceBtn', function () {
            $.post("{{ route('whatsapp.campaigns.preview_audience') }}", {
                audience_type: $('#campaign_audience_type').val(),
                filters: collectFilters(),
            }, function (response) {
                $('#audiencePreviewCount').text(response.count + ' recipient(s) match');
            }).fail(function (xhr) { toastr.error(xhr.responseJSON?.message || 'Could not check the audience'); });
        });

        $(document).on('submit', '#campaignForm', function (e) {
            e.preventDefault();

            const messageType = $('#campaign_message_type').val();
            if (messageType === 'text' && !$('#campaign_body').val().trim()) {
                toastr.error('Enter a message'); return;
            }
            if (messageType === 'template' && !$('#campaign_template_name').val().trim()) {
                toastr.error('Enter the approved template name'); return;
            }

            $.ajax({
                url: "{{ route('whatsapp.campaigns.store') }}",
                type: 'POST',
                data: {
                    name: $('#campaign_name').val(),
                    whatsapp_account_id: $('#campaign_account_id').val(),
                    message_type: messageType,
                    body: $('#campaign_body').val(),
                    template_name: $('#campaign_template_name').val(),
                    audience_type: $('#campaign_audience_type').val(),
                    filters: collectFilters(),
                    scheduled_at: $('#campaign_scheduled_at').val(),
                },
                success: function (response) {
                    if (response.status) { toastr.success(response.message); $('#campaignModal').modal('hide'); loadCampaigns(); }
                    else { toastr.error(response.message); }
                },
                error: function (xhr) { toastr.error(xhr.responseJSON?.message || 'Something went wrong'); }
            });
        });

        $(document).on('click', '.sendCampaignBtn', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');
            if (!confirm(`Send "${name}" now? This messages everyone who matches its audience filters.`)) return;

            const $btn = $(this).prop('disabled', true);
            $.post("{{ url('whatsapp/campaigns') }}/" + id + "/send", {}, function (response) {
                if (response.status) toastr.success(response.message); else toastr.error(response.message);
                loadCampaigns();
            }).fail(function (xhr) { toastr.error(xhr.responseJSON?.message || 'Send failed'); $btn.prop('disabled', false); });
        });

        $(document).on('click', '.deleteCampaignBtn', function () {
            if (!confirm('Delete this campaign?')) return;
            const id = $(this).data('id');
            $.ajax({ url: "{{ url('whatsapp/campaigns') }}/" + id, type: 'DELETE', success: function (response) { toastr.success(response.message); loadCampaigns(); } });
        });

        renderAudienceFilters();
        loadCampaigns();
    </script>
</body>
</html>
