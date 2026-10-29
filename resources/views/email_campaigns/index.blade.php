<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Email Campaigns | CRM Admin</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">

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
        [data-theme="dark"] .crm-header-icon { color: #fff; }
        [data-theme="dark"] .status-pill.draft { background: rgba(154, 161, 181, 0.16); color: #9aa1b5; }
        [data-theme="dark"] .status-pill.scheduled { background: rgba(250, 204, 21, 0.16); color: #facc15; }
        [data-theme="dark"] .status-pill.sending { background: rgba(147, 164, 253, 0.16); color: #93a4fd; }
        [data-theme="dark"] .status-pill.sent { background: rgba(74, 222, 128, 0.16); color: #4ade80; }
        [data-theme="dark"] .status-pill.failed { background: rgba(239, 68, 68, 0.16); color: #fca5a5; }

        /* ---- campaign wizard ---- */
        .wiz .modal-body { min-height: 420px; max-height: calc(100vh - 230px); overflow-y: auto; }
        .wiz label { font-weight: 600; font-size: 13px; }
        .wiz-steps { list-style: none; display: flex; margin: 0; padding: 14px 22px; gap: 10px; border-bottom: 1px solid var(--border); background: rgba(0,0,0,.015); }
        .wiz-steps li { flex: 1; display: flex; align-items: center; gap: 10px; color: var(--text-muted); font-weight: 600; font-size: 13px; }
        .wiz-steps .n { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: #e5e7eb; color: #4b5563; font-size: 13px; flex-shrink: 0; }
        .wiz-steps li.active { color: var(--text-dark); }
        .wiz-steps li.active .n { background: var(--crm-accent, #1f6f63); color: #fff; }
        .wiz-steps li.done { cursor: pointer; }
        .wiz-steps li.done .n { background: #dcfce7; color: #15803d; }
        .wiz-when { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px; }
        .wiz-opt { display: flex; gap: 10px; align-items: flex-start; border: 1.5px solid var(--border); border-radius: 12px; padding: 12px; margin: 0; cursor: pointer; font-weight: 400 !important; }
        .wiz-opt:has(input:checked) { border-color: var(--crm-accent, #1f6f63); background: rgba(31,111,99,.07); }
        .wiz-opt input { margin-top: 4px; }
        .wiz-opt b { display: block; font-size: 13.5px; } .wiz-opt small { display: block; color: var(--text-muted); font-size: 12px; }
        .wiz-count { display: flex; align-items: center; gap: 16px; padding: 16px 18px; border: 1px solid var(--border); border-radius: 14px; flex-wrap: wrap; }
        .wiz-count #audienceBig { font-size: 34px; font-weight: 700; color: var(--crm-accent, #1f6f63); margin-right: 10px; line-height: 1; }
        .wiz-count small { color: var(--text-muted); font-size: 13px; }
        .wiz-tpls { display: flex; flex-direction: column; gap: 8px; max-height: 330px; overflow-y: auto; }
        .wiz-tpl { text-align: left; border: 1.5px solid var(--border); border-radius: 12px; padding: 10px 12px; background: transparent; color: inherit; cursor: pointer; }
        .wiz-tpl:hover { border-color: #9ca3af; }
        .wiz-tpl.sel { border-color: var(--crm-accent, #1f6f63); background: rgba(31,111,99,.08); }
        .wiz-tpl b { display: block; font-size: 13.5px; } .wiz-tpl small { display: block; color: var(--text-muted); font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .wiz-preview { border: 1px solid var(--border); border-radius: 12px; overflow: hidden; background: #fff; }
        .wiz-pv-head { padding: 10px 14px; border-bottom: 1px solid var(--border); font-size: 13px; background: rgba(0,0,0,.025); color: var(--text-dark); }
        #pvFrame { width: 100%; height: 300px; border: 0; background: #fff; }
        .wiz-pv-note { padding: 6px 14px; font-size: 11.5px; color: var(--text-muted); }
        .wiz-review { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 8px 22px; margin-top: 16px; padding: 14px 16px; border-radius: 12px; background: rgba(0,0,0,.03); font-size: 13px; }
        .wiz-review div span { display: block; color: var(--text-muted); font-size: 11.5px; } .wiz-review div b { word-break: break-word; }
        [data-theme="dark"] .wiz-steps .n { background: #2a2e40; color: #cbd0e0; }
        [data-theme="dark"] .wiz-preview { background: #1a1d2b; }
        @media (max-width: 576px) { .wiz-steps .t { display: none; } }
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
                                    <th>Opened</th>
                                    <th>Clicked</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="campaignTable">
                                <tr><td colspan="9" class="text-center text-muted py-4">Loading…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                @include('include.footer')
            </div>
        </div>
    </div>

    <!-- New Campaign wizard -->
    <div class="modal fade" id="campaignModal" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="wizTitle">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content wiz">
                <div class="modal-header">
                    <h5 class="modal-title" id="wizTitle">New campaign</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <ol class="wiz-steps" id="wizSteps">
                    <li data-s="1" class="active"><span class="n">1</span><span class="t">Basics</span></li>
                    <li data-s="2"><span class="n">2</span><span class="t">Recipients</span></li>
                    <li data-s="3"><span class="n">3</span><span class="t">Template &amp; review</span></li>
                </ol>
                <form id="campaignForm" autocomplete="off" novalidate>
                    <div class="modal-body">

                        {{-- Step 1 --}}
                        <section class="wiz-pane" data-s="1">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="campaign_name">Campaign name</label>
                                    <input type="text" id="campaign_name" class="form-control" maxlength="150" placeholder="e.g. November offer for new leads">
                                    <small class="text-muted">Only you see this name.</small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="campaign_subject">Email subject</label>
                                    <input type="text" id="campaign_subject" class="form-control" maxlength="255" placeholder="Leave blank to use the template's subject">
                                    <small class="text-muted">You can use variables such as @{{lead.name}} if your template does.</small>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="campaign_mail_setting">Send from (SMTP)</label>
                                <select id="campaign_mail_setting" class="form-control">
                                    <option value="">Default: the company's active SMTP</option>
                                    @foreach($mailSettings as $ms)
                                        <option value="{{ $ms->id }}">{{ $ms->name }}@if($ms->smtp_from_address) ({{ $ms->smtp_from_address }})@endif @if($ms->is_active) &middot; active @endif</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Add or edit SMTP accounts under Mail settings.</small>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="campaign_cc">CC <span class="text-muted">(optional)</span></label>
                                    <input type="text" id="campaign_cc" class="form-control" placeholder="name@company.com, other@company.com">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="campaign_bcc">BCC <span class="text-muted">(optional)</span></label>
                                    <input type="text" id="campaign_bcc" class="form-control" placeholder="manager@company.com">
                                </div>
                                <div class="col-12"><small class="text-muted d-block mb-2">CC and BCC addresses get a copy of every email in this campaign, so keep the list short.</small></div>
                            </div>

                            <label class="d-block mb-2">When should it go out?</label>
                            <div class="wiz-when">
                                @can('campaigns.send')
                                <label class="wiz-opt"><input type="radio" name="campaign_when" value="now"><span><b>Send right after review</b><small>Goes out as soon as you confirm in step 3.</small></span></label>
                                @endcan
                                <label class="wiz-opt"><input type="radio" name="campaign_when" value="schedule" @cannot('campaigns.send') checked @endcannot><span><b>Schedule</b><small>Sent automatically at the time you choose.</small></span></label>
                                <label class="wiz-opt"><input type="radio" name="campaign_when" value="draft" @can('campaigns.send') checked @endcan><span><b>Save as draft</b><small>Send it later from the list.</small></span></label>
                            </div>
                            <div class="form-group mt-3" id="scheduleWrap" style="display:none;">
                                <label for="campaign_scheduled_at">Send on</label>
                                <input type="datetime-local" id="campaign_scheduled_at" class="form-control" style="max-width:280px;">
                                <small class="text-muted">Uses your computer's time.</small>
                            </div>
                        </section>

                        {{-- Step 2 --}}
                        <section class="wiz-pane" data-s="2" style="display:none;">
                            <div class="form-group">
                                <label for="campaign_audience_type">Who should receive it?</label>
                                <select id="campaign_audience_type" class="form-control" style="max-width:320px;">
                                    <option value="leads">Leads</option>
                                    <option value="contacts">Contacts</option>
                                    <option value="companies">Companies</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Narrow it down <small class="text-muted">(leave a filter on "Any" to include everyone)</small></label>
                                <div id="audienceFilters" class="form-row"></div>
                            </div>
                            <div class="wiz-count">
                                <div><span id="audienceBig">&ndash;</span><small>people will get this email</small></div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="previewAudienceBtn"><i class="fa fa-refresh"></i> Recount</button>
                                <span id="audiencePreviewCount" class="text-muted small"></span>
                            </div>
                            <small class="text-muted d-block mt-2">Only records that have an email address are counted.</small>
                        </section>

                        {{-- Step 3 --}}
                        <section class="wiz-pane" data-s="3" style="display:none;">
                            <div class="row">
                                <div class="col-lg-4 mb-3">
                                    <label for="tplSearch">Choose a template</label>
                                    <input type="search" id="tplSearch" class="form-control form-control-sm mb-2" placeholder="Search templates">
                                    <div class="wiz-tpls" id="tplList" role="radiogroup" aria-label="Templates">
                                        @foreach($templates as $template)
                                            <button type="button" class="wiz-tpl" role="radio" aria-checked="false" data-id="{{ $template->id }}" data-name="{{ $template->name }}">
                                                <b>{{ $template->name }}</b><small>{{ $template->subject }}</small>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <label>Preview</label>
                                    <div class="wiz-preview">
                                        <div class="wiz-pv-head" id="pvHead">Pick a template to see how the email will look.</div>
                                        <iframe id="pvFrame" title="Email preview" sandbox="" style="display:none;"></iframe>
                                        <div class="wiz-pv-note" id="pvNote"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="wiz-review" id="wizReview"></div>
                        </section>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light mr-auto" id="wizBack" style="display:none;"><i class="fa fa-arrow-left"></i> Back</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="wizNext">Next <i class="fa fa-arrow-right"></i></button>
                        <button type="submit" class="btn btn-success" id="wizFinish" style="display:none;"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- jQuery + Bootstrap already load once via include.footer below (in
    the body) — loading them again here registered a second copy of
    Bootstrap's modal plugin, which double-fired on "New Campaign" and left
    an orphaned .modal-backdrop behind after the dialog closed. --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>

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

        // "12 (40%)" — unique people who opened/clicked, out of those the email was sent to.
        function pct(n, sent) {
            n = n || 0;
            return sent > 0 ? `${n} <span class="text-muted">(${Math.round(n / sent * 100)}%)</span>` : `${n}`;
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
                            <td>${pct(c.opened_count, c.sent_count)}</td>
                            <td>${pct(c.clicked_count, c.sent_count)}</td>
                            <td class="text-right text-nowrap">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ url('email-campaigns') }}/${c.id}/report" title="See who opened and clicked"><i class="fa fa-chart-line"></i> Report</a>
                                @can('campaigns.send')
                                ${canSend ? `<button class="btn btn-sm btn-outline-success sendCampaignBtn" data-id="${c.id}" data-name="${esc(c.name)}"><i class="fa fa-paper-plane"></i> Send</button>` : ''}
                                @endcan
                                @can('campaigns.delete')
                                ${c.status !== 'sending' && c.status !== 'sent' ? `<button class="btn btn-sm btn-outline-danger deleteCampaignBtn" data-id="${c.id}"><i class="fa fa-trash"></i></button>` : ''}
                                @endcan
                            </td>
                        </tr>`;
                });
                $('#campaignTable').html(rows || '<tr><td colspan="9" class="text-center text-muted py-4">No campaigns yet.</td></tr>');
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

        // ---------------------------------------------------------------
        // New-campaign wizard: 1 Basics -> 2 Recipients -> 3 Template & review
        // ---------------------------------------------------------------
        let wizStep = 1, wizTemplate = null, wizCount = null;

        const splitEmails = v => String(v || '').split(/[\s,;]+/).map(s => s.trim()).filter(Boolean);
        const whenValue = () => $('input[name=campaign_when]:checked').val();
        const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        function showStep(n) {
            wizStep = n;
            $('.wiz-pane').hide().filter('[data-s=' + n + ']').show();
            $('#wizSteps li').each(function () {
                const s = +$(this).data('s');
                $(this).toggleClass('active', s === n).toggleClass('done', s < n);
            });
            $('#wizBack').toggle(n > 1);
            $('#wizNext').toggle(n < 3);
            $('#wizFinish').toggle(n === 3);
            if (n === 2) recount();
            if (n === 3) renderReview();
        }

        function finishLabel() {
            const w = whenValue();
            return w === 'now' ? '<i class="fa fa-paper-plane"></i> Send campaign'
                : w === 'schedule' ? '<i class="fa fa-clock"></i> Schedule campaign'
                : '<i class="fa fa-save"></i> Save draft';
        }

        function validateStep(n) {
            if (n === 1) {
                if (!$('#campaign_name').val().trim()) { toastr.error('Give the campaign a name.'); $('#campaign_name').focus(); return false; }
                for (const id of ['#campaign_cc', '#campaign_bcc']) {
                    const bad = splitEmails($(id).val()).find(e => !emailRe.test(e));
                    if (bad) { toastr.error(`"${bad}" is not a valid email address.`); $(id).focus(); return false; }
                }
                if (whenValue() === 'schedule') {
                    const at = $('#campaign_scheduled_at').val();
                    if (!at) { toastr.error('Choose when to send it.'); $('#campaign_scheduled_at').focus(); return false; }
                    if (new Date(at) <= new Date()) { toastr.error('Pick a time in the future.'); return false; }
                }
            }
            if (n === 3 && !wizTemplate) { toastr.error('Pick a template.'); return false; }
            return true;
        }

        function recount() {
            $('#audienceBig').text('…'); $('#audiencePreviewCount').text('');
            $.post("{{ route('campaigns.preview_audience') }}", { audience_type: $('#campaign_audience_type').val(), filters: collectFilters() })
                .done(r => { wizCount = r.count; $('#audienceBig').text(r.count.toLocaleString()); })
                .fail(x => { wizCount = null; $('#audienceBig').text('–'); toastr.error(x.responseJSON?.message || 'Could not count recipients'); });
        }

        function loadPreview() {
            if (!wizTemplate) return;
            $('#pvHead').text('Loading preview…'); $('#pvFrame').hide(); $('#pvNote').text('');
            $.post("{{ route('campaigns.template_preview') }}", { email_template_id: wizTemplate.id, audience_type: $('#campaign_audience_type').val(), subject: $('#campaign_subject').val() })
                .done(r => {
                    $('#pvHead').html('<span class="text-muted">Subject:</span> <b></b>').find('b').text(r.subject || '(no subject)');
                    $('#pvFrame').attr('srcdoc', '<base target="_blank"><body style="font-family:Arial,sans-serif;margin:16px;">' + r.body + '</body>').show();
                    $('#pvNote').text((r.attachments.length ? 'Attachments: ' + r.attachments.join(', ') + '. ' : '') + r.note);
                })
                .fail(x => { $('#pvHead').text(x.responseJSON?.message || 'Could not load the preview.'); });
        }

        function renderReview() {
            const mailOpt = $('#campaign_mail_setting option:selected').text();
            const cc = splitEmails($('#campaign_cc').val()), bcc = splitEmails($('#campaign_bcc').val());
            const w = whenValue();
            const when = w === 'now' ? 'Right after you confirm' : w === 'schedule' ? new Date($('#campaign_scheduled_at').val()).toLocaleString() : 'Saved as a draft, not sent yet';
            const row = (k, v) => `<div><span>${k}</span><b>${esc(v)}</b></div>`;
            $('#wizReview').html(
                row('Campaign', $('#campaign_name').val()) +
                row('Template', wizTemplate ? wizTemplate.name : 'Not chosen yet') +
                row('Recipients', (wizCount ?? '?') + ' ' + $('#campaign_audience_type').val()) +
                row('Sent from', mailOpt) +
                (cc.length ? row('CC', cc.join(', ')) : '') + (bcc.length ? row('BCC', bcc.join(', ')) : '') +
                row('When', when));
            $('#wizFinish').html(finishLabel());
        }

        $(document).on('click', '#addCampaignBtn', function () {
            $('#campaignForm')[0].reset();
            wizTemplate = null; wizCount = null;
            $('.wiz-tpl').removeClass('sel').attr('aria-checked', 'false');
            $('#pvHead').text('Pick a template to see how the email will look.'); $('#pvFrame').hide(); $('#pvNote').text('');
            $('#scheduleWrap').toggle(whenValue() === 'schedule');
            renderAudienceFilters(); showStep(1);
        });
        $(document).on('change', 'input[name=campaign_when]', function () {
            $('#scheduleWrap').toggle(whenValue() === 'schedule');
            if (whenValue() === 'schedule' && !$('#campaign_scheduled_at').val()) {
                const d = new Date(Date.now() + 3600e3); d.setMinutes(0, 0, 0); d.setHours(d.getHours() + 1);
                const p = n => String(n).padStart(2, '0');
                $('#campaign_scheduled_at').val(`${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}T${p(d.getHours())}:00`);
            }
        });
        $(document).on('change', '#campaign_audience_type', function () { renderAudienceFilters(); recount(); });
        $(document).on('change', '#audienceFilters select', recount);
        $(document).on('click', '#previewAudienceBtn', recount);
        $(document).on('click', '#wizNext', function () { if (validateStep(wizStep)) showStep(wizStep + 1); });
        $(document).on('click', '#wizBack', function () { showStep(wizStep - 1); });
        $(document).on('click', '#wizSteps li.done', function () { showStep(+$(this).data('s')); });
        $(document).on('input', '#tplSearch', function () {
            const q = this.value.toLowerCase();
            $('.wiz-tpl').each(function () { $(this).toggle($(this).text().toLowerCase().includes(q)); });
        });
        $(document).on('click', '.wiz-tpl', function () {
            $('.wiz-tpl').removeClass('sel').attr('aria-checked', 'false');
            $(this).addClass('sel').attr('aria-checked', 'true');
            wizTemplate = { id: $(this).data('id'), name: $(this).data('name') };
            loadPreview(); renderReview();
        });

        $(document).on('submit', '#campaignForm', function (e) {
            e.preventDefault();
            if (wizStep !== 3) { $('#wizNext').trigger('click'); return; }
            if (!validateStep(1) || !validateStep(3)) return;

            const when = whenValue();
            const data = {
                name: $('#campaign_name').val().trim(),
                email_template_id: wizTemplate.id,
                subject: $('#campaign_subject').val(),
                audience_type: $('#campaign_audience_type').val(),
                filters: collectFilters(),
                mail_setting_id: $('#campaign_mail_setting').val(),
                cc: splitEmails($('#campaign_cc').val()),
                bcc: splitEmails($('#campaign_bcc').val()),
            };
            if (when === 'schedule') {
                // Sent in the server's timezone; the browser's local time is converted first.
                const d = new Date($('#campaign_scheduled_at').val());
                const p = n => String(n).padStart(2, '0');
                data.scheduled_at = `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}:00`;
            }

            const $finish = $('#wizFinish').prop('disabled', true);
            $.ajax({ url: "{{ route('campaigns.store') }}", type: 'POST', data })
                .done(function (response) {
                    if (!response.status) { toastr.error(response.message); $finish.prop('disabled', false); return; }
                    const done = msg => { toastr.success(msg); $('#campaignModal').modal('hide'); loadCampaigns(); $finish.prop('disabled', false); };
                    if (when !== 'now') { done(when === 'schedule' ? 'Campaign scheduled.' : 'Draft saved.'); return; }
                    $.post("{{ url('email-campaigns') }}/" + response.data.id + "/send", {})
                        .done(r => r.status ? done(r.message) : (toastr.error(r.message + ' It was saved as a draft.'), $('#campaignModal').modal('hide'), loadCampaigns(), $finish.prop('disabled', false)))
                        .fail(x => { toastr.error((x.responseJSON?.message || 'Send failed') + ' It was saved as a draft.'); $('#campaignModal').modal('hide'); loadCampaigns(); $finish.prop('disabled', false); });
                })
                .fail(function (xhr) {
                    const errs = xhr.responseJSON?.errors;
                    toastr.error((errs ? Object.values(errs).flat().join(' ') : xhr.responseJSON?.message) || 'Something went wrong');
                    $finish.prop('disabled', false);
                });
        });

        $(document).on('click', '.sendCampaignBtn', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const $btn = $(this);
            openConfirmModal({
                title: `Send "${name}" now?`,
                message: 'This emails everyone who matches its audience filters.',
                variant: 'info',
                confirmText: 'Send',
                onConfirm: function(done) {
                    $btn.prop('disabled', true);
                    $.post("{{ url('email-campaigns') }}/" + id + "/send", {}, function(response) {
                        if (response.status) {
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                        loadCampaigns();
                        done();
                    }).fail(function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Send failed');
                        $btn.prop('disabled', false);
                        done();
                    });
                }
            });
        });

        $(document).on('click', '.deleteCampaignBtn', function() {
            const id = $(this).data('id');
            openConfirmModal({ title: 'Delete this campaign?', message: 'This can\'t be undone.', confirmText: 'Delete', onConfirm: function(done) {
                $.ajax({
                    url: "{{ url('email-campaigns') }}/" + id,
                    type: 'POST',
                    data: { _method: 'DELETE' },
                    success: function(response) {
                        done();
                        if (response.status) {
                            toastr.success(response.message);
                            loadCampaigns();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                        done();
                    }
                });
            }});
        });

        $(document).ready(function() {
            loadCampaigns();
        });
    </script>

</body>

</html>
