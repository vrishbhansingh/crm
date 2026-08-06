<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lead Details | CRM Admin</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        :root {
            --primary: #2563eb;
            --border: #e5e7eb;
            --text-dark: #111827;
            --text-muted: #6b7280;
        }

        .crm-page-header {
            background: #fff;
            padding: 18px 22px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            border-left: 4px solid var(--primary);
            margin-bottom: 20px;
        }

        .lead-title {
            font-weight: 700;
            font-size: 20px;
            color: var(--text-dark);
            margin: 0;
        }

        .lead-company {
            color: var(--text-muted);
            font-size: 13px;
        }

        .badge-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            margin-right: 6px;
            background: #eef2ff;
            color: #4338ca;
        }

        .badge-score-hot { background: #fee2e2; color: #b91c1c; }
        .badge-score-warm { background: #fef3c7; color: #92400e; }
        .badge-score-cold { background: #e0f2fe; color: #075985; }

        .card-box {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-box h5 {
            font-weight: 700;
            font-size: 15px;
            color: var(--text-dark);
            margin-bottom: 14px;
        }

        .field-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px dashed var(--border);
            font-size: 13px;
        }

        .field-row:last-child { border-bottom: none; }
        .field-row .label { color: var(--text-muted); }
        .field-row .value { color: var(--text-dark); font-weight: 500; text-align: right; }

        .tag-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f1f5f9;
            color: #334155;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            margin: 0 6px 6px 0;
        }

        .tag-chip button {
            border: none;
            background: transparent;
            color: #94a3b8;
            font-size: 11px;
            padding: 0;
        }

        .tag-input-row {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .timeline-item {
            display: flex;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .timeline-item:last-child { border-bottom: none; }

        .timeline-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #eef2ff;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .timeline-desc {
            font-size: 13px;
            color: var(--text-dark);
        }

        .timeline-meta {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .attachment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
        }

        .attachment-item:last-child { border-bottom: none; }

        .duplicate-warning {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            color: #92400e;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 12.5px;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <div class="container-scroller">

        @include('include.header')

        <div class="container-fluid page-body-wrapper">

            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="crm-page-header d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('leads.index') }}" class="text-muted" style="font-size:12px;">
                            <i class="fa fa-arrow-left"></i> Back to Leads
                        </a>
                        <h4 class="lead-title mt-1" id="leadName">Loading…</h4>
                        <div class="lead-company" id="leadCompany"></div>
                    </div>
                    <div id="leadBadges" class="text-right"></div>
                </div>

                <div class="row">
                    <!-- MAIN COLUMN -->
                    <div class="col-md-8">

                        <div class="card-box">
                            <h5><i class="fa fa-address-book"></i> Customer Contact</h5>
                            <div id="contactCard">
                                <p class="text-muted">Loading…</p>
                            </div>
                        </div>

                        <div class="card-box">
                            <h5><i class="fa fa-tags"></i> Tags</h5>
                            <div id="tagList"></div>
                            <div class="tag-input-row">
                                <input type="text" id="newTagInput" class="form-control form-control-sm" placeholder="Add a tag and press Enter">
                            </div>
                        </div>

                        @can('leads.edit')
                        <div class="card-box">
                            <h5><i class="fa fa-phone"></i> Log a Follow-up Call</h5>
                            <form id="followUpForm">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Call Status</label>
                                        <select name="call_status" id="call_status" class="form-control form-control-sm">
                                            <option value="call_connected">Call Connected</option>
                                            <option value="not_reachable">Not Reachable</option>
                                            <option value="switched_off">Switched Off</option>
                                            <option value="busy">Busy</option>
                                            <option value="wrong_number">Wrong Number</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Lead Response</label>
                                        <select name="lead_response" id="lead_response" class="form-control form-control-sm">
                                            <option value="">-- Optional --</option>
                                            <option value="interested">Interested</option>
                                            <option value="callback">Callback</option>
                                            <option value="not_interested">Not Interested</option>
                                            <option value="meeting_scheduled">Meeting Scheduled</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Next Follow-up Date</label>
                                        <input type="date" name="followup_date" id="followup_date" class="form-control form-control-sm">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Time</label>
                                        <input type="time" name="followup_time" id="followup_time" class="form-control form-control-sm">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>Notes</label>
                                        <textarea name="call_notes" id="call_notes" class="form-control form-control-sm" rows="2"></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fa fa-save"></i> Save Follow-up
                                </button>
                            </form>
                        </div>
                        @endcan

                        <div class="card-box">
                            <h5><i class="fa fa-clock-o"></i> Timeline</h5>

                            <div class="mb-3">
                                <textarea id="newNoteBody" class="form-control" rows="2" placeholder="Add a note…"></textarea>
                                <button class="btn btn-primary btn-sm mt-2" id="addNoteBtn">
                                    <i class="fa fa-plus"></i> Add Note
                                </button>
                            </div>

                            <div id="timelineList">
                                <p class="text-muted">Loading…</p>
                            </div>
                        </div>

                        <div class="card-box">
                            <h5><i class="fa fa-paperclip"></i> Attachments</h5>
                            <form id="attachmentForm">
                                <input type="file" id="attachmentFile" class="form-control form-control-sm mb-2">
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-upload"></i> Upload
                                </button>
                            </form>
                            <div id="attachmentList" class="mt-3"></div>
                        </div>

                    </div>

                    <!-- SIDEBAR -->
                    <div class="col-md-4">
                        <div class="card-box">
                            <h5><i class="fa fa-info-circle"></i> Lead Info</h5>
                            <div id="leadInfoCard">
                                <p class="text-muted">Loading…</p>
                            </div>
                        </div>

                        <div class="card-box" id="conversionCard" style="display:none;">
                            <h5><i class="fa fa-trophy"></i> Conversion</h5>
                            <div id="conversionBody"></div>
                        </div>

                        @can('orders.create')
                        <div class="card-box" id="convertCard">
                            <h5><i class="fa fa-shopping-cart"></i> Convert to Order</h5>
                            <p class="text-muted" style="font-size:12.5px;">Create an order for this lead and mark it converted.</p>
                            <button class="btn btn-success btn-sm btn-block" data-toggle="modal" data-target="#convertModal">
                                <i class="fa fa-check"></i> Convert to Order
                            </button>
                        </div>
                        @endcan
                    </div>
                </div>

                @include('include.footer')

            </div>
        </div>
    </div>

    @can('orders.create')
    <div class="modal fade" id="convertModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Convert Lead to Order</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="convertForm">
                    <div class="modal-body">
                        <h6 class="text-muted">Project</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Project Name</label>
                                <input type="text" name="project_name" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Tech Stack</label>
                                <input type="text" name="tech_stack" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Expected Start</label>
                                <input type="date" name="expected_start_date" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Expected Delivery</label>
                                <input type="date" name="expected_delivery_date" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Priority</label>
                                <select name="project_priority" class="form-control form-control-sm" data-master-type="project_priority"></select>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Description</label>
                                <textarea name="project_description" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                        </div>

                        <h6 class="text-muted mt-2">Order</h6>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Sub Total</label>
                                <input type="number" step="0.01" name="sub_total" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Discount</label>
                                <input type="number" step="0.01" name="discount" class="form-control form-control-sm" value="0">
                            </div>
                            <div class="form-group col-md-4">
                                <label>GST</label>
                                <input type="number" step="0.01" name="gst" class="form-control form-control-sm" value="0">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Total Amount</label>
                                <input type="number" step="0.01" name="total_amount" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Net Amount</label>
                                <input type="number" step="0.01" name="net_amount" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Due Amount</label>
                                <input type="number" step="0.01" name="due_amount" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Currency</label>
                                <select name="currency" class="form-control form-control-sm" data-master-type="currency"></select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Payment Terms</label>
                                <select name="payment_terms" class="form-control form-control-sm" data-master-type="payment_terms"></select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Order Status</label>
                                <select name="order_status" class="form-control form-control-sm" data-master-type="order_status"></select>
                            </div>
                        </div>

                        <h6 class="text-muted mt-2">Initial Payment (optional)</h6>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Paid Amount</label>
                                <input type="number" step="0.01" name="paid_amount" class="form-control form-control-sm" value="0">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Payment Mode</label>
                                <select name="payment_mode" class="form-control form-control-sm" data-master-type="payment_mode"></select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Payment Date</label>
                                <input type="date" name="payment_date" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-check"></i> Place Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        const leadId = {{ (int) $leadId }};

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        function scoreBand(score) {
            if (score === null || score === undefined) return { label: 'Unscored', cls: '' };
            if (score >= 70) return { label: `Hot · ${score}`, cls: 'badge-score-hot' };
            if (score >= 40) return { label: `Warm · ${score}`, cls: 'badge-score-warm' };
            return { label: `Cold · ${score}`, cls: 'badge-score-cold' };
        }

        function loadDetail() {
            $.get("{{ url('leads') }}/" + leadId + "/detail", function(response) {
                const d = response.data;
                $('#leadName').text(d.name || '(No name)');
                $('#leadCompany').text(d.company_name || '');

                const band = scoreBand(d.score);
                $('#leadBadges').html(`
                    <span class="badge-pill">${d.lead_type ?? '-'}</span>
                    <span class="badge-pill">${d.lead_source ?? '-'}</span>
                    <span class="badge-pill">${d.lead_status ?? '-'}</span>
                    <span class="badge-pill">${d.priority ?? '-'}</span>
                    <span class="badge-pill ${band.cls}">${band.label}</span>
                `);

                $('#leadInfoCard').html(`
                    <div class="field-row"><span class="label">Phone</span><span class="value">${d.phone ?? '-'}</span></div>
                    <div class="field-row"><span class="label">Email</span><span class="value">${d.email ?? '-'}</span></div>
                    <div class="field-row"><span class="label">Budget</span><span class="value">${d.budget ?? '-'}</span></div>
                    <div class="field-row"><span class="label">Assigned To</span><span class="value">${d.assigned_user ? d.assigned_user.name : 'Unassigned'}</span></div>
                    <div class="field-row"><span class="label">Follow-up</span><span class="value">${d.follow_up_date ?? '-'} ${d.follow_up_time ?? ''}</span></div>
                    <div class="field-row"><span class="label">City / State</span><span class="value">${d.city ?? '-'} / ${d.state ?? '-'}</span></div>
                `);

                if (d.customer_contact) {
                    const c = d.customer_contact;
                    $('#contactCard').html(`
                        <div class="field-row"><span class="label">Name</span><span class="value">${c.name ?? '-'}</span></div>
                        <div class="field-row"><span class="label">Phone</span><span class="value">${c.phone ?? '-'}</span></div>
                        <div class="field-row"><span class="label">Email</span><span class="value">${c.email ?? '-'}</span></div>
                        <div class="field-row"><span class="label">Designation</span><span class="value">${c.designation ?? '-'}</span></div>
                    `);
                } else {
                    $('#contactCard').html('<p class="text-muted">No contact on file.</p>');
                }

                if (d.tags && d.tags.length) {
                    renderTags(d.tags);
                } else {
                    renderTags([]);
                }

                if (d.is_converted === 'Yes' || d.order) {
                    $('#conversionCard').show();
                    let body = `<div class="field-row"><span class="label">Converted</span><span class="value">${d.converted_at ?? 'Yes'}</span></div>`;
                    if (d.order) {
                        body += `<div class="field-row"><span class="label">Order</span><span class="value">${d.order.order_number}</span></div>`;
                    }
                    $('#conversionBody').html(body);
                }
            });
        }

        function renderTags(tags) {
            let html = '';
            tags.forEach(tag => {
                html += `<span class="tag-chip">${tag.name} <button class="removeTagBtn" data-id="${tag.id}">&times;</button></span>`;
            });
            $('#tagList').html(html || '<span class="text-muted" style="font-size:12px;">No tags yet.</span>');
        }

        function timelineIcon(type) {
            const icons = {
                created: 'fa-star',
                note: 'fa-sticky-note',
                assigned: 'fa-user',
                status_changed: 'fa-refresh',
                tag_added: 'fa-tag',
                tag_removed: 'fa-tag',
                lost: 'fa-times-circle',
                converted: 'fa-trophy',
                follow_up: 'fa-phone',
                attachment: 'fa-paperclip',
            };
            return icons[type] || 'fa-circle';
        }

        function loadTimeline() {
            $.get("{{ url('leads') }}/" + leadId + "/timeline", function(response) {
                let html = '';
                response.data.forEach(item => {
                    html += `
                        <div class="timeline-item">
                            <div class="timeline-icon"><i class="fa ${timelineIcon(item.type)}"></i></div>
                            <div>
                                <div class="timeline-desc">${item.description ?? ''}</div>
                                <div class="timeline-meta">${item.user_name ?? ''} ${item.user_name ? '·' : ''} ${item.created_at}</div>
                            </div>
                        </div>`;
                });
                $('#timelineList').html(html || '<p class="text-muted">No activity yet.</p>');
            });
        }

        function loadAttachments() {
            $.get("{{ url('leads') }}/" + leadId + "/detail", function() {});
        }

        $(document).on('click', '#addNoteBtn', function() {
            const body = $('#newNoteBody').val().trim();
            if (!body) return;
            $.post("{{ url('leads') }}/" + leadId + "/notes", { body }, function(response) {
                if (response.status) {
                    $('#newNoteBody').val('');
                    toastr.success('Note added');
                    loadTimeline();
                }
            });
        });

        $(document).on('keypress', '#newTagInput', function(e) {
            if (e.which !== 13) return;
            e.preventDefault();
            const name = $(this).val().trim();
            if (!name) return;
            $.post("{{ url('leads') }}/" + leadId + "/tags", { name }, function(response) {
                if (response.status) {
                    $('#newTagInput').val('');
                    toastr.success('Tag added');
                    loadDetail();
                    loadTimeline();
                }
            });
        });

        $(document).on('click', '.removeTagBtn', function() {
            const tagId = $(this).data('id');
            $.ajax({
                url: "{{ url('leads') }}/" + leadId + "/tags/" + tagId,
                type: 'POST',
                data: { _method: 'DELETE' },
                success: function(response) {
                    if (response.status) {
                        loadDetail();
                        loadTimeline();
                    }
                }
            });
        });

        function loadAttachmentList() {
            $.get("{{ url('leads') }}/" + leadId + "/timeline", function(response) {
                let html = '';
                response.data.filter(i => i.kind === 'attachment').forEach(a => {
                    html += `
                        <div class="attachment-item">
                            <span><i class="fa fa-file-o"></i> ${a.description}</span>
                            <a href="{{ url('attachments') }}/${a.attachment_id}/download" class="btn btn-sm btn-outline-secondary">
                                <i class="fa fa-download"></i>
                            </a>
                        </div>`;
                });
                $('#attachmentList').html(html || '<p class="text-muted">No attachments yet.</p>');
            });
        }

        $(document).on('submit', '#attachmentForm', function(e) {
            e.preventDefault();
            const fileInput = $('#attachmentFile')[0];
            if (!fileInput.files.length) return;

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            $.ajax({
                url: "{{ url('leads') }}/" + leadId + "/attachments",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status) {
                        toastr.success('File uploaded');
                        $('#attachmentFile').val('');
                        loadAttachmentList();
                        loadTimeline();
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Upload failed');
                }
            });
        });

        // Populates every <select data-master-type="..."> in the Convert-to-Order
        // modal from the Master Data lookup endpoint, same pattern used on the
        // Leads list/add/edit pages.
        function loadMasterDropdowns() {
            $('select[data-master-type]').each(function() {
                const $select = $(this);
                const type = $select.data('master-type');

                $.get("{{ url('admin/master-data/lookup') }}/" + type, function(response) {
                    response.data.forEach(function(option) {
                        $select.append(`<option value="${option.code}">${option.label}</option>`);
                    });
                });
            });
        }

        $(document).on('submit', '#followUpForm', function(e) {
            e.preventDefault();
            const data = {};
            $(this).serializeArray().forEach(f => data[f.name] = f.value);

            $.post("{{ url('leads') }}/" + leadId + "/follow-up", data, function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    $('#followUpForm')[0].reset();
                    loadTimeline();
                }
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Something went wrong');
            });
        });

        $(document).on('submit', '#convertForm', function(e) {
            e.preventDefault();
            const data = {};
            $(this).serializeArray().forEach(f => data[f.name] = f.value);

            $.post("{{ url('leads') }}/" + leadId + "/convert-to-order", data, function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    $('#convertModal').modal('hide');
                    loadDetail();
                    loadTimeline();
                } else {
                    toastr.error(response.message);
                }
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Something went wrong');
            });
        });

        $(document).ready(function() {
            loadDetail();
            loadTimeline();
            loadAttachmentList();
            loadMasterDropdowns();
        });
    </script>

</body>

</html>
