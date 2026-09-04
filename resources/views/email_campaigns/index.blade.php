<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Email Campaigns | CRM Admin</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        :root { --primary: #2563eb; --border: #e5e7eb; --text-dark: #111827; --text-muted: #6b7280; }

        .crm-page-header {
            background: #fff; padding: 18px 22px; border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06); border-left: 4px solid var(--primary);
            display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 20px;
        }
        .crm-header-left { display: flex; align-items: center; gap: 14px; }
        .crm-header-icon {
            width: 42px; height: 42px; border-radius: 10px;
            background: linear-gradient(135deg, #0d6efd, #00c6ff); color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .crm-page-header h4 { font-weight: 700; font-size: 18px; color: var(--text-dark); margin: 0; }
        .crm-subtitle { color: var(--text-muted); font-size: 13px; }

        .crm-card {
            background: #fff; border-radius: 14px; box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border); padding: 4px;
        }

        .status-pill { padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
        .status-pill.draft { background: #f3f4f6; color: #6b7280; }
        .status-pill.scheduled { background: #fef3c7; color: #92400e; }
        .status-pill.sending { background: #dbeafe; color: #1d4ed8; }
        .status-pill.sent { background: #dcfce7; color: #15803d; }
        .status-pill.failed { background: #fee2e2; color: #b91c1c; }

        #audienceFilters .form-group { margin-bottom: 10px; }
        #audiencePreviewCount { font-weight: 700; color: var(--primary); }
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
                        <div class="crm-header-icon"><i class="fa fa-paper-plane"></i></div>
                        <div>
                            <h4>Email Campaigns</h4>
                            <small class="crm-subtitle">Send a template to a filtered list of leads, contacts, or companies.</small>
                        </div>
                    </div>
                    @can('campaigns.create')
                    <button class="btn btn-primary btn-sm" id="addCampaignBtn" data-toggle="modal" data-target="#campaignModal" @if($templates->isEmpty()) disabled title="Create a template first" @endif>
                        <i class="fa fa-plus"></i> New Campaign
                    </button>
                    @endcan
                </div>

                @if($templates->isEmpty())
                <div class="alert alert-warning">
                    You don't have any email templates yet. <a href="{{ route('templates.index') }}">Create one first</a> — a campaign always sends an existing template.
                </div>
                @endif

                <div class="crm-card">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Campaign</th>
                                    <th>Template</th>
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

    <!-- New Campaign Modal -->
    <div class="modal fade" id="campaignModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Campaign</h5>
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
                                <label>Template</label>
                                <select id="campaign_template_id" class="form-control" required>
                                    <option value="">-- Select a template --</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" data-subject="{{ $template->subject }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Subject <small class="text-muted">(optional — leave blank to use the template's own subject)</small></label>
                            <input type="text" id="campaign_subject" class="form-control" placeholder="">
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
                        <button type="submit" class="btn btn-primary">Save Campaign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });

        const esc = value => $('<div>').text(value ?? '').html();

        const FILTER_FIELDS = {
            leads: [
                { key: 'lead_status', label: 'Lead Status', masterType: 'lead_status' },
                { key: 'lead_source', label: 'Lead Source', masterType: 'lead_source' },
                { key: 'priority', label: 'Priority', masterType: 'priority' },
            ],
            contacts: [
                { key: 'status', label: 'Status', options: ['Active', 'Inactive'] },
            ],
            companies: [
                { key: 'status', label: 'Status', options: ['Active', 'Inactive'] },
            ],
        };

        function statusLabel(status) {
            return status.charAt(0).toUpperCase() + status.slice(1);
        }

        function loadCampaigns() {
            $.get("{{ route('campaigns.data') }}", function(response) {
                let rows = '';
                response.data.forEach((c) => {
                    const canSend = ['draft', 'scheduled', 'failed'].includes(c.status);
                    rows += `
                        <tr>
                            <td>${esc(c.name)}</td>
                            <td class="text-muted">${esc(c.template_name)}</td>
                            <td class="text-capitalize">${esc(c.audience_type)}</td>
                            <td><span class="status-pill ${c.status}">${statusLabel(c.status)}</span></td>
                            <td>${c.total_recipients}</td>
                            <td>${c.sent_count}${c.failed_count ? ` <span class="text-danger">(${c.failed_count} failed)</span>` : ''}</td>
                            <td class="text-right">
                                @can('campaigns.send')
                                ${canSend ? `<button class="btn btn-sm btn-outline-success sendCampaignBtn" data-id="${c.id}" data-name="${esc(c.name)}"><i class="fa fa-paper-plane"></i> Send</button>` : ''}
                                @endcan
                                @can('campaigns.delete')
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
                (field.options || []).forEach((opt) => {
                    html += `<option value="${esc(opt)}">${esc(opt)}</option>`;
                });
                html += `</select></div>`;
            });
            $('#audienceFilters').html(html);
            $('#audiencePreviewCount').text('');
            loadMasterDropdowns();
        }

        function loadMasterDropdowns() {
            $('#audienceFilters select[data-master-type]').each(function() {
                const $select = $(this);
                const type = $select.data('master-type');
                $.get("{{ url('master-data/lookup') }}/" + type, function(response) {
                    response.data.forEach(function(option) {
                        $select.append(`<option value="${esc(option.code)}">${esc(option.label)}</option>`);
                    });
                });
            });
        }

        function collectFilters() {
            const filters = {};
            $('.audience-filter-input').each(function() {
                const value = $(this).val();
                if (value) filters[$(this).data('key')] = value;
            });
            return filters;
        }

        $(document).on('change', '#campaign_audience_type', renderAudienceFilters);

        $(document).on('change', '#campaign_template_id', function() {
            const subject = $(this).find(':selected').data('subject');
            $('#campaign_subject').attr('placeholder', subject || '');
        });

        $(document).on('click', '#addCampaignBtn', function() {
            $('#campaignForm')[0].reset();
            renderAudienceFilters();
        });

        $(document).on('click', '#previewAudienceBtn', function() {
            $.post("{{ route('campaigns.preview_audience') }}", {
                audience_type: $('#campaign_audience_type').val(),
                filters: collectFilters(),
            }, function(response) {
                $('#audiencePreviewCount').text(response.count + ' recipient(s) match');
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Could not check the audience');
            });
        });

        $(document).on('submit', '#campaignForm', function(e) {
            e.preventDefault();

            if (!$('#campaign_template_id').val()) {
                toastr.error('Pick a template first');
                return;
            }

            $.ajax({
                url: "{{ route('campaigns.store') }}",
                type: 'POST',
                data: {
                    name: $('#campaign_name').val(),
                    email_template_id: $('#campaign_template_id').val(),
                    subject: $('#campaign_subject').val(),
                    audience_type: $('#campaign_audience_type').val(),
                    filters: collectFilters(),
                    scheduled_at: $('#campaign_scheduled_at').val(),
                },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#campaignModal').modal('hide');
                        loadCampaigns();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });

        $(document).on('click', '.sendCampaignBtn', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            if (!confirm(`Send "${name}" now? This emails everyone who matches its audience filters.`)) return;

            const $btn = $(this).prop('disabled', true);
            $.post("{{ url('email-campaigns') }}/" + id + "/send", {}, function(response) {
                if (response.status) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
                loadCampaigns();
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Send failed');
                $btn.prop('disabled', false);
            });
        });

        $(document).on('click', '.deleteCampaignBtn', function() {
            if (!confirm('Delete this campaign?')) return;
            const id = $(this).data('id');
            $.ajax({
                url: "{{ url('email-campaigns') }}/" + id,
                type: 'POST',
                data: { _method: 'DELETE' },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        loadCampaigns();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });

        $(document).ready(function() {
            loadCampaigns();
        });
    </script>

</body>

</html>
