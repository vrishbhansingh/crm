<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Deal Details | CRM Admin</title>

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

        .deal-title {
            font-weight: 700;
            font-size: 20px;
            color: var(--text-dark);
            margin: 0;
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

        .badge-open { background: #eef2ff; color: #4338ca; }
        .badge-won { background: #dcfce7; color: #15803d; }
        .badge-lost { background: #fee2e2; color: #b91c1c; }

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
                        <a href="{{ route('deals.list') }}" class="text-muted" style="font-size:12px;">
                            <i class="fa fa-arrow-left"></i> Back to Deals
                        </a>
                        <h4 class="deal-title mt-1" id="dealName">Loading…</h4>
                    </div>
                    <div id="dealBadges" class="text-right"></div>
                </div>

                <div class="row">
                    <!-- MAIN COLUMN -->
                    <div class="col-md-8">

                        <div class="card-box">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fa fa-briefcase"></i> Deal Info</h5>
                                @can('deals.edit')
                                <button class="btn btn-outline-primary btn-sm" id="editInfoBtn"><i class="fa fa-pencil"></i> Edit</button>
                                @endcan
                            </div>

                            <div id="dealInfoView" class="mt-3"></div>

                            @can('deals.edit')
                            <form id="dealInfoForm" class="mt-3" style="display:none;">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Deal Name</label>
                                        <input type="text" name="name" id="edit_name" class="form-control form-control-sm">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Amount</label>
                                        <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control form-control-sm">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Currency</label>
                                        <select name="currency" id="edit_currency" class="form-control form-control-sm"></select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Owner</label>
                                        <select name="owner_id" id="edit_owner_id" class="form-control form-control-sm"></select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Expected Close Date</label>
                                        <input type="date" name="expected_close_date" id="edit_expected_close_date" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save</button>
                                <button type="button" class="btn btn-light btn-sm" id="cancelEditInfoBtn">Cancel</button>
                            </form>
                            @endcan
                        </div>

                        <div class="card-box" id="leadCard" style="display:none;">
                            <h5><i class="fa fa-bullseye"></i> Linked Lead</h5>
                            <div id="leadCardBody"></div>
                        </div>

                        <div class="card-box">
                            <h5><i class="fa fa-clock-o"></i> Stage Timeline</h5>
                            <div id="timelineList">
                                <p class="text-muted">Loading…</p>
                            </div>
                        </div>

                    </div>

                    <!-- SIDEBAR -->
                    <div class="col-md-4">

                        <div class="card-box">
                            <h5><i class="fa fa-flag"></i> Stage</h5>
                            <div class="field-row"><span class="label">Current Stage</span><span class="value" id="currentStageName">-</span></div>

                            @can('deals.edit')
                            <div class="mt-3">
                                <label>Move to another stage</label>
                                <select id="moveStageSelect" class="form-control form-control-sm mb-2"></select>
                                <div id="lostReasonWrap" style="display:none;" class="mb-2">
                                    <label>Lost Reason</label>
                                    <select id="moveLostReason" class="form-control form-control-sm"></select>
                                </div>
                                <div id="wonPaymentWrap" style="display:none;" class="mb-2">
                                    <label>Initial Payment <span class="text-muted">(optional)</span></label>
                                    <input type="number" min="0" step="0.01" id="moveWonPaidAmount" class="form-control form-control-sm mb-2" placeholder="0.00">
                                    <select id="moveWonPaymentMode" class="form-control form-control-sm mb-2"></select>
                                    <input type="date" id="moveWonPaymentDate" class="form-control form-control-sm">
                                </div>
                                <button class="btn btn-primary btn-sm btn-block" id="moveStageBtn">
                                    <i class="fa fa-exchange"></i> Move Stage
                                </button>
                            </div>
                            @endcan
                        </div>

                        <div class="card-box" id="orderCard" style="display:none;">
                            <h5><i class="fa fa-trophy"></i> Order</h5>
                            <div id="orderCardBody"></div>
                        </div>

                        <div class="card-box">
                            <h5><i class="fa fa-sticky-note"></i> Notes</h5>
                            <textarea id="notesBody" class="form-control" rows="4"></textarea>
                            @can('deals.edit')
                            <button class="btn btn-primary btn-sm mt-2" id="saveNotesBtn"><i class="fa fa-save"></i> Save Notes</button>
                            @endcan
                        </div>

                        @can('tasks.create')
                        <div class="card-box">
                            <h5><i class="fa fa-check-square-o"></i> Follow-up</h5>
                            <p class="text-muted" style="font-size:12.5px;">Add a task or reminder linked to this deal.</p>
                            <button type="button" class="btn btn-primary btn-sm btn-block" onclick="var t=document.getElementById('dealName'); openQuickTask('deal', {{ (int) $dealId }}, t ? t.textContent : null)">
                                <i class="fa fa-plus"></i> Add Task
                            </button>
                        </div>
                        @endcan

                        @can('deals.delete')
                        <div class="card-box">
                            <button class="btn btn-outline-danger btn-sm btn-block" id="deleteDealBtn">
                                <i class="fa fa-trash"></i> Delete Deal
                            </button>
                        </div>
                        @endcan
                    </div>
                </div>

                @include('include.footer')

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    @include('include.quick-task-modal')

    <script>
        const dealId = {{ (int) $dealId }};
        let currentDeal = null;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        function money(v) {
            if (v === null || v === undefined || v === '') return '0';
            return Number(v).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        const esc = value => $('<div>').text(value ?? '').html();

        function dash(v) {
            return (v === null || v === undefined || v === '') ? '-' : v;
        }

        function statusBadgeClass(status) {
            return { open: 'badge-open', won: 'badge-won', lost: 'badge-lost' }[status] || 'badge-open';
        }

        function loadDetail() {
            $.get("{{ url('deals') }}/" + dealId + "/detail", function(response) {
                const d = response.data;
                currentDeal = d;

                $('#dealName').text(d.name);
                $('#dealBadges').html(`<span class="badge-pill ${statusBadgeClass(d.status)}">${esc((d.status || '').toUpperCase())}</span>`);

                const pipelineLink = d.pipeline
                    ? '<a href="' + "{{ url('deals') }}" + '?pipeline_id=' + d.pipeline.id + '">' + esc(d.pipeline.name) + '</a>'
                    : '-';

                $('#dealInfoView').html(`
                    <div class="field-row"><span class="label">Amount</span><span class="value">${esc(d.currency ?? '')} ${money(d.amount)}</span></div>
                    <div class="field-row"><span class="label">Owner</span><span class="value">${esc(d.owner ? d.owner.name : 'Unassigned')}</span></div>
                    <div class="field-row"><span class="label">Pipeline</span><span class="value">${pipelineLink}</span></div>
                    <div class="field-row"><span class="label">Expected Close Date</span><span class="value">${esc(dash(d.expected_close_date))}</span></div>
                    <div class="field-row"><span class="label">Created By</span><span class="value">${esc(d.created_by ? d.created_by.name : '-')}</span></div>
                    ${d.status === 'lost' && d.lost_reason ? `<div class="field-row"><span class="label">Lost Reason</span><span class="value">${esc(d.lost_reason.label)}</span></div>` : ''}
                `);

                $('#edit_name').val(d.name);
                $('#edit_amount').val(d.amount);
                $('#edit_expected_close_date').val(d.expected_close_date);

                $('#currentStageName').text(d.stage ? d.stage.name : '-');
                $('#notesBody').val(d.notes ?? '');

                if (d.lead) {
                    $('#leadCard').show();
                    $('#leadCardBody').html(`
                        <div class="field-row"><span class="label">Name</span><span class="value"><a href="{{ url('leads') }}/${d.lead.id}">${esc(d.lead.name)}</a></span></div>
                        <div class="field-row"><span class="label">Phone</span><span class="value">${esc(dash(d.lead.phone))}</span></div>
                        <div class="field-row"><span class="label">Email</span><span class="value">${esc(dash(d.lead.email))}</span></div>
                    `);
                } else {
                    $('#leadCard').hide();
                }

                if (d.order) {
                    $('#orderCard').show();
                    $('#orderCardBody').html(`
                        <div class="field-row"><span class="label">Order</span><span class="value"><a href="{{ url('orders') }}/${d.order.id}">${esc(d.order.order_number)}</a></span></div>
                        <div class="field-row"><span class="label">Total</span><span class="value">${money(d.order.total_amount)}</span></div>
                        <div class="field-row"><span class="label">Paid</span><span class="value">${money(d.order.paid_amount)}</span></div>
                        <div class="field-row"><span class="label">Due</span><span class="value">${money(d.order.due_amount)}</span></div>
                    `);
                }

                loadMoveStageOptions(d);
                loadEditDropdowns(d);
            });
        }

        function loadMoveStageOptions(d) {
            if (!d.pipeline || !d.pipeline.stages) return;

            let options = '';
            d.pipeline.stages.forEach(s => {
                if (s.id === d.stage_id) return;
                options += `<option value="${s.id}" data-is-lost="${s.is_lost ? 1 : 0}" data-is-won="${s.is_won ? 1 : 0}">${esc(s.name)}</option>`;
            });
            $('#moveStageSelect').html(options);
            toggleStageExtras();
        }

        function toggleStageExtras() {
            const selected = $('#moveStageSelect option:selected');
            const isLost = selected.data('is-lost') === 1;
            const isWon = selected.data('is-won') === 1;

            if (isLost) {
                $('#lostReasonWrap').show();
                $.get("{{ url('master-data/lookup') }}/lost_reason", function(response) {
                    let options = '<option value="">-- Select a reason --</option>';
                    response.data.forEach(o => options += `<option value="${o.id}">${esc(o.label)}</option>`);
                    $('#moveLostReason').html(options);
                });
            } else {
                $('#lostReasonWrap').hide();
            }

            if (isWon) {
                $('#wonPaymentWrap').show();
                $('#moveWonPaidAmount').val('');
                $('#moveWonPaymentDate').val(new Date().toISOString().slice(0, 10));
                $.get("{{ url('master-data/lookup') }}/payment_mode", function(response) {
                    let options = '<option value="">-- Select a mode --</option>';
                    response.data.forEach(o => options += `<option value="${esc(o.code)}">${esc(o.label)}</option>`);
                    $('#moveWonPaymentMode').html(options);
                });
            } else {
                $('#wonPaymentWrap').hide();
            }
        }

        $(document).on('change', '#moveStageSelect', toggleStageExtras);

        $(document).on('click', '#moveStageBtn', function() {
            const toStageId = $('#moveStageSelect').val();
            if (!toStageId) return;

            const payload = { to_stage_id: toStageId };
            const lostReasonId = $('#moveLostReason').val();
            if ($('#lostReasonWrap').is(':visible')) {
                if (!lostReasonId) {
                    toastr.error('Please select a lost reason');
                    return;
                }
                payload.lost_reason_id = lostReasonId;
            }

            if ($('#wonPaymentWrap').is(':visible')) {
                const paidAmount = $('#moveWonPaidAmount').val();
                if (paidAmount && Number(paidAmount) > 0) {
                    payload.paid_amount = paidAmount;
                    payload.payment_mode = $('#moveWonPaymentMode').val();
                    payload.payment_date = $('#moveWonPaymentDate').val();
                }
            }

            $.post("{{ url('deals') }}/" + dealId + "/move-stage", payload, function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    loadDetail();
                    loadTimeline();
                }
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Could not move this deal');
            });
        });

        function loadEditDropdowns(d) {
            $.get("{{ route('leads.assignable_users') }}", function(response) {
                let options = '<option value="">-- Unassigned --</option>';
                response.users.forEach(u => options += `<option value="${u.id}" ${u.id === d.owner_id ? 'selected' : ''}>${esc(u.name)}</option>`);
                $('#edit_owner_id').html(options);
            });

            $.get("{{ url('master-data/lookup') }}/currency", function(response) {
                let options = '<option value="">-- Select --</option>';
                response.data.forEach(o => options += `<option value="${esc(o.code)}" ${o.code === d.currency ? 'selected' : ''}>${esc(o.label)}</option>`);
                $('#edit_currency').html(options);
            });
        }

        function timelineDescription(item) {
            const from = item.from_stage ? item.from_stage.name : 'created';
            const to = item.to_stage ? item.to_stage.name : '-';
            return `${from} → ${to}`;
        }

        function loadTimeline() {
            $.get("{{ url('deals') }}/" + dealId + "/timeline", function(response) {
                let html = '';
                response.data.forEach(item => {
                    html += `
                        <div class="timeline-item">
                            <div class="timeline-icon"><i class="fa fa-exchange"></i></div>
                            <div>
                                <div class="timeline-desc">${esc(timelineDescription(item))}</div>
                                <div class="timeline-meta">${esc(item.changed_by ? item.changed_by.name : 'System')} · ${esc(item.created_at)}</div>
                            </div>
                        </div>`;
                });
                $('#timelineList').html(html || '<p class="text-muted">No stage changes yet.</p>');
            });
        }

        $(document).on('click', '#editInfoBtn', function() {
            $('#dealInfoView').hide();
            $('#dealInfoForm').show();
        });

        $(document).on('click', '#cancelEditInfoBtn', function() {
            $('#dealInfoForm').hide();
            $('#dealInfoView').show();
        });

        $(document).on('submit', '#dealInfoForm', function(e) {
            e.preventDefault();
            const data = {};
            $(this).serializeArray().forEach(f => data[f.name] = f.value);

            $.post("{{ url('deals') }}/" + dealId, data, function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    $('#dealInfoForm').hide();
                    $('#dealInfoView').show();
                    loadDetail();
                }
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Something went wrong');
            });
        });

        $(document).on('click', '#saveNotesBtn', function() {
            const data = {
                name: currentDeal.name,
                amount: currentDeal.amount,
                currency: currentDeal.currency,
                owner_id: currentDeal.owner_id,
                lead_id: currentDeal.lead_id,
                expected_close_date: currentDeal.expected_close_date,
                notes: $('#notesBody').val(),
            };
            $.post("{{ url('deals') }}/" + dealId, data, function(response) {
                if (response.status) {
                    toastr.success('Notes saved');
                    loadDetail();
                }
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Something went wrong');
            });
        });

        $(document).on('click', '#deleteDealBtn', function() {
            if (!confirm('Delete this deal? This cannot be undone.')) return;
            $.ajax({
                url: "{{ url('deals') }}/" + dealId + "/delete",
                type: 'POST',
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        setTimeout(() => window.location = "{{ route('deals.list') }}", 600);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });

        $(document).ready(function() {
            loadDetail();
            loadTimeline();
        });
    </script>

</body>

</html>
