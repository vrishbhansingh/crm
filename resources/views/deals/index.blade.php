<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Deals | CRM Admin</title>

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

        /* Only the Kanban board itself should ever scroll sideways — never
           the page. Without this, the board's unconstrained flex children
           (5 fixed-width columns) can widen its container past the
           viewport, silently pushing the page-header's buttons off-screen
           and making the whole page require horizontal scroll to reach
           them, instead of just the board scrolling internally. */
        html, body {
            overflow-x: hidden;
        }

        /* Same modernization pattern as Roles & Permissions / Dashboard /
           Leads / Deals list: bigger, roomier white header card. */
        .page-header {
            background: #ffffff;
            padding: 26px 28px;
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        /* Title and toolbar each get their own row instead of sharing one
           flex line — that's what let the toolbar's contents get squeezed
           and clipped when the pipeline switcher had extra options. Two
           stacked rows can never fight each other for horizontal space. */
        .page-header-top {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h4 {
            font-weight: 700;
            font-size: 22px;
            color: var(--text-dark);
            margin: 0;
        }

        .page-header p {
            font-size: 14px;
            color: var(--text-muted);
            margin: 4px 0 0;
        }

        .board-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--border);
        }

        #pipelineSwitcher {
            width: auto;
            min-width: 160px;
            max-width: 260px;
        }

        .kanban-board {
            display: flex;
            width: 100%;
            max-width: 100%;
            gap: 14px;
            overflow-x: auto;
            padding-bottom: 12px;
            align-items: flex-start;
        }

        .kanban-column {
            /* Grows to fill the board when there's room for every stage
               (the common case — 5 stages easily fit a normal screen),
               shrinks down to 220px before the board falls back to its own
               horizontal scroll, and never grows past 320px so a 2-stage
               pipeline doesn't stretch into absurdly wide columns. */
            flex: 1 1 240px;
            min-width: 220px;
            max-width: 320px;
            background: #f5f7fb;
            border-radius: 14px;
            padding: 12px;
        }

        .kanban-column-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 10px;
            border-radius: 10px;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 13px;
            background: #eef2ff;
            color: #3730a3;
        }

        .kanban-column-header.is-won {
            background: #dcfce7;
            color: #15803d;
        }

        .kanban-column-header.is-lost {
            background: #fee2e2;
            color: #b91c1c;
        }

        .kanban-column-count {
            font-size: 11px;
            background: rgba(255, 255, 255, 0.6);
            padding: 1px 8px;
            border-radius: 999px;
        }

        .kanban-cards {
            min-height: 60px;
        }

        .kanban-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border);
            padding: 12px;
            margin-bottom: 10px;
            cursor: grab;
            font-size: 12.5px;
        }

        .kanban-card:active {
            cursor: grabbing;
        }

        .kanban-card.sortable-ghost {
            opacity: 0.4;
        }

        .kanban-card .deal-name {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .kanban-card .deal-amount {
            color: #15803d;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .kanban-card .deal-meta {
            color: var(--text-muted);
            font-size: 11.5px;
            display: flex;
            justify-content: space-between;
        }

        .lead-badge {
            font-size: 10px;
            background: #eef2ff;
            color: var(--primary);
            border-radius: 999px;
            padding: 1px 7px;
        }

        .empty-column-msg {
            font-size: 11.5px;
            color: var(--text-muted);
            text-align: center;
            padding: 14px 4px;
        }

        .empty-pipeline-state {
            width: 100%;
            background: #fff;
            border: 1px dashed var(--border);
            border-radius: 14px;
            padding: 48px 24px;
            text-align: center;
        }

        .empty-pipeline-state i {
            font-size: 32px;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .empty-pipeline-state h5 {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .empty-pipeline-state p {
            color: var(--text-muted);
            font-size: 13px;
            margin-bottom: 16px;
        }
    </style>
</head>

<body>

    <div class="container-scroller">

        @include('include.header')

        <div class="container-fluid page-body-wrapper">

            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="page-header">
                    <div class="page-header-top">
                        <div>
                            <h4>Deals</h4>
                            <p>{{ Auth::guard('web')->user()->hasElevatedAccess() ? 'Every deal in the pipeline' : 'Your deals' }}</p>
                        </div>
                        <a href="{{ route('deals.list') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-table"></i> List View
                        </a>
                        @can('deals.create')
                        <a href="{{ route('deals.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> New Deal
                        </a>
                        @endcan
                    </div>
                    <div class="board-toolbar" id="boardToolbar" style="display:none;">
                        <label class="mb-0 mr-1" style="font-size:12px;color:var(--text-muted);">Pipeline:</label>
                        <select id="pipelineSwitcher" class="form-control form-control-sm"></select>
                    </div>
                </div>

                <div class="kanban-board" id="kanbanBoard">
                    <p class="text-muted">Loading pipeline…</p>
                </div>

                @include('include.footer')

            </div>
        </div>
    </div>

    <!-- Lost Reason Modal -->
    <div class="modal fade" id="lostReasonModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Why was this deal lost?</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Lost Reason</label>
                        <select id="lostReasonSelect" class="form-control"></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal" id="lostReasonCancel">Cancel</button>
                    <button type="button" class="btn btn-danger" id="lostReasonConfirm">Mark as Lost</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Won Payment Modal -->
    <div class="modal fade" id="wonPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Deal won — record a payment?</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" style="font-size:12.5px;">This creates the order. Optionally record an initial payment now — you can always add more later from the order.</p>
                    <div class="form-group">
                        <label>Payment Amount <span class="text-muted">(optional)</span></label>
                        <input type="number" min="0" step="0.01" id="wonPaidAmount" class="form-control" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Payment Mode</label>
                        <select id="wonPaymentMode" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date" id="wonPaymentDate" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal" id="wonPaymentCancel">Cancel</button>
                    <button type="button" class="btn btn-success" id="wonPaymentConfirm">Mark as Won</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('vendors/sortablejs/Sortable.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        let currentPipelineId = null;
        let pendingMove = null; // { dealId, toStageId, cardEl, fromColumn }
        const canManagePipelines = {{ Auth::guard('web')->user()->can('deals.manage-settings') ? 'true' : 'false' }};

        function money(v) {
            if (v === null || v === undefined || v === '') return '0';
            return Number(v).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        const esc = value => $('<div>').text(value ?? '').html();
        const safeColor = value => /^#[0-9a-f]{3,8}$/i.test(value || '') ? value : '#64748b';

        function dash(v) {
            return (v === null || v === undefined || v === '') ? '-' : v;
        }

        function renderCard(deal) {
            const leadBadge = deal.lead_id
                ? `<a href="{{ url('leads') }}/${deal.lead_id}" class="lead-badge" title="Linked lead" onclick="event.stopPropagation();"><i class="fa fa-bullseye"></i> Lead</a>`
                : '';

            return `
                <div class="kanban-card" draggable="true" data-deal-id="${deal.id}" onclick="window.location='{{ url('deals') }}/${deal.id}'">
                    <div class="deal-name"><span>${esc(deal.name)}</span> ${leadBadge}</div>
                    <div class="deal-amount">${esc(deal.currency ?? '')} ${money(deal.amount)}</div>
                    <div class="deal-meta">
                        <span><i class="fa fa-user-o"></i> ${dash(deal.owner_name)}</span>
                        <span><i class="fa fa-calendar-o"></i> ${dash(deal.expected_close_date)}</span>
                    </div>
                </div>`;
        }

        function renderBoard(data) {
            currentPipelineId = data.pipeline_id;

            if (!data.pipeline_id) {
                $('#boardToolbar').hide();
                $('#kanbanBoard').html(`
                    <div class="empty-pipeline-state">
                        <i class="fa fa-random"></i>
                        <h5>No pipeline yet</h5>
                        <p>Deals are tracked through a pipeline's stages — create one to get started.</p>
                        ${canManagePipelines
                            ? `<a href="{{ route('pipelines.index') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Create a Pipeline</a>`
                            : `<p class="text-muted" style="font-size:12px;">Ask an admin to set up a pipeline in Settings.</p>`}
                    </div>`);
                return;
            }

            // Pipeline switcher — only shown when there's an actual choice to make
            if (data.pipelines && data.pipelines.length > 1) {
                let options = '';
                data.pipelines.forEach(p => {
                    options += `<option value="${p.id}" ${p.id === data.pipeline_id ? 'selected' : ''}>${esc(p.name)}</option>`;
                });
                $('#pipelineSwitcher').html(options);
                $('#boardToolbar').show();
            } else {
                $('#boardToolbar').hide();
            }

            let html = '';
            data.stages.forEach(stage => {
                let headerClass = '';
                let headerStyle = '';
                if (stage.is_won) headerClass = 'is-won';
                if (stage.is_lost) headerClass = 'is-lost';
                if (stage.color) {
                    const color = safeColor(stage.color);
                    headerStyle = `style="background:${color}22;color:${color};"`;
                }

                let cardsHtml = '';
                stage.deals.forEach(deal => cardsHtml += renderCard(deal));
                if (!stage.deals.length) {
                    cardsHtml = '<div class="empty-column-msg">No deals</div>';
                }

                html += `
                    <div class="kanban-column">
                        <div class="kanban-column-header ${headerClass}" ${headerStyle}>
                            <span>${esc(stage.name)}</span>
                            <span class="kanban-column-count">${stage.deals.length}</span>
                        </div>
                        <div class="kanban-cards" id="stage-${stage.id}" data-stage-id="${stage.id}" data-is-won="${stage.is_won ? 1 : 0}" data-is-lost="${stage.is_lost ? 1 : 0}">
                            ${cardsHtml}
                        </div>
                    </div>`;
            });

            $('#kanbanBoard').html(html);
            initSortable();
        }

        function loadBoard(pipelineId) {
            $.get("{{ route('deals.board_data') }}", pipelineId ? { pipeline_id: pipelineId } : {}, function(response) {
                if (response.status) {
                    renderBoard(response.data);
                }
            }).fail(function() {
                // The requested pipeline no longer exists (e.g. deleted in
                // another tab) — fall back to whatever pipeline is actually
                // still there instead of leaving the board frozen on stale data.
                if (pipelineId) {
                    loadBoard();
                }
            });
        }

        function initSortable() {
            document.querySelectorAll('.kanban-cards').forEach(function(el) {
                new Sortable(el, {
                    group: 'deals',
                    animation: 150,
                    onEnd: function(evt) {
                        const dealId = evt.item.getAttribute('data-deal-id');
                        const toColumn = evt.to;
                        const toStageId = toColumn.getAttribute('data-stage-id');
                        const isLost = toColumn.getAttribute('data-is-lost') === '1';
                        const isWon = toColumn.getAttribute('data-is-won') === '1';

                        if (evt.from === evt.to && evt.oldIndex === evt.newIndex) {
                            return;
                        }

                        if (isLost) {
                            pendingMove = { dealId, toStageId, item: evt.item, from: evt.from, oldIndex: evt.oldIndex };
                            loadLostReasons();
                            $('#lostReasonModal').modal('show');
                            return;
                        }

                        if (isWon) {
                            pendingMove = { dealId, toStageId, item: evt.item, from: evt.from, oldIndex: evt.oldIndex };
                            loadPaymentModes();
                            $('#wonPaidAmount').val('');
                            $('#wonPaymentDate').val(new Date().toISOString().slice(0, 10));
                            $('#wonPaymentModal').modal('show');
                            return;
                        }

                        submitMove(dealId, toStageId, {});
                    }
                });
            });
        }

        function loadLostReasons() {
            $.get("{{ url('master-data/lookup') }}/lost_reason", function(response) {
                let options = '<option value="">-- Select a reason --</option>';
                response.data.forEach(o => options += `<option value="${o.id}">${esc(o.label)}</option>`);
                $('#lostReasonSelect').html(options);
            });
        }

        function loadPaymentModes() {
            $.get("{{ url('master-data/lookup') }}/payment_mode", function(response) {
                let options = '<option value="">-- Select a mode --</option>';
                response.data.forEach(o => options += `<option value="${esc(o.code)}">${esc(o.label)}</option>`);
                $('#wonPaymentMode').html(options);
            });
        }

        function submitMove(dealId, toStageId, extra) {
            const payload = Object.assign({ to_stage_id: toStageId }, extra || {});

            $.post("{{ url('deals') }}/" + dealId + "/move-stage", payload, function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    loadBoard(currentPipelineId);
                }
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Could not move this deal');
                loadBoard(currentPipelineId);
            });
        }

        $(document).on('click', '#lostReasonConfirm', function() {
            const reasonId = $('#lostReasonSelect').val();
            if (!reasonId) {
                toastr.error('Please select a lost reason');
                return;
            }
            $('#lostReasonModal').modal('hide');
            submitMove(pendingMove.dealId, pendingMove.toStageId, { lost_reason_id: reasonId });
            pendingMove = null;
        });

        $('#lostReasonModal').on('hidden.bs.modal', function() {
            if (pendingMove) {
                // Cancelled — snap the board back to reality.
                loadBoard(currentPipelineId);
                pendingMove = null;
            }
        });

        $(document).on('click', '#wonPaymentConfirm', function() {
            const paidAmount = $('#wonPaidAmount').val();
            const payload = {};
            if (paidAmount && Number(paidAmount) > 0) {
                payload.paid_amount = paidAmount;
                payload.payment_mode = $('#wonPaymentMode').val();
                payload.payment_date = $('#wonPaymentDate').val();
            }
            $('#wonPaymentModal').modal('hide');
            submitMove(pendingMove.dealId, pendingMove.toStageId, payload);
            pendingMove = null;
        });

        $('#wonPaymentModal').on('hidden.bs.modal', function() {
            if (pendingMove) {
                // Cancelled — snap the board back to reality.
                loadBoard(currentPipelineId);
                pendingMove = null;
            }
        });

        $(document).on('change', '#pipelineSwitcher', function() {
            loadBoard($(this).val());
        });

        $(document).ready(function() {
            const requestedPipelineId = new URLSearchParams(window.location.search).get('pipeline_id');
            loadBoard(requestedPipelineId);
        });
    </script>

</body>

</html>
