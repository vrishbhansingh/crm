<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Deals | CRM</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="{{asset('vendors/select2/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('vendors/select2-bootstrap-theme/select2-bootstrap.min.css')}}">

    <style>
        /* .order-table-wrapper/.order-table/.status-badge/.status-open/
           .status-won/.status-lost/.page-header removed in V2 — this list
           now uses the shared .crm-table-wrap/.crm-table/.crm-badge/
           .crm-header components from crm-components.css (loaded globally
           via include/header.blade.php) instead. */

        .deal-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 18px;
            margin-bottom: 20px;
        }

        /* Background/border/radius/shadow now come from the shared
           .crm-card class (added alongside .deal-stat-card in the markup)
           — this rule only supplies the tile's own flex layout. */
        .deal-stat-card {
            padding: 20px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .deal-stat-card .deal-stat-value { font-size: 26px; font-weight: 700; color: #111827; line-height: 1.15; }
        .deal-stat-card .deal-stat-label { font-size: 12.5px; color: #6b7280; margin-top: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; }
        .deal-stat-card .deal-stat-icon {
            width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 17px;
        }

        .owner-cell { display: inline-flex; align-items: center; gap: 8px; padding: 4px 6px; border-radius: 6px; }
        .owner-cell.assign-deal:hover { background: #f3f4f6; }
        .owner-cell.assign-deal:hover .owner-name { text-decoration: underline; }
        .owner-avatar {
            width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 10.5px;
        }
        .owner-name { color: #2563eb; font-weight: 600; }
        .owner-cell.is-unassigned .owner-name { color: #9ca3af; font-style: italic; font-weight: 500; }

        .stage-pill { display: inline-block; padding: 4px 11px; border-radius: 999px; font-size: 12px; font-weight: 700; }

        /* .deal-stat-card's background/shadow now come from .crm-card,
           which already has its own [data-theme="dark"] handling. */
        [data-theme="dark"] .owner-cell.assign-deal:hover { background: #232637; }
        [data-theme="dark"] .owner-name { color: #93a4fd; }
        [data-theme="dark"] .owner-cell.is-unassigned .owner-name { color: #6b7280; }
        [data-theme="dark"] .deal-stat-card .deal-stat-value { color: #eef0f6; }
        [data-theme="dark"] .deal-stat-card .deal-stat-label { color: #9aa1b5; }
    </style>
</head>

<body>

    <div class="container-scroller">

        @include('include.header')

        <div class="container-fluid page-body-wrapper">

            @include('include.sidebar')

            <div class="content-wrapper">

                @component('include.page-header', [
                    'icon' => 'fa fa-handshake-o',
                    'title' => 'Deals',
                    'subtitle' => Auth::guard('web')->user()->hasElevatedAccess() ? 'Every deal in the pipeline' : 'Your deals',
                ])
                    @slot('actions')
                        <a href="{{ route('deals.index') }}" class="crm-btn crm-btn--secondary">
                            <i class="fa fa-columns"></i> Kanban View
                        </a>
                        @can('deals.create')
                        <a href="{{ route('deals.create') }}" class="crm-btn crm-btn--primary">
                            <i class="fa fa-plus"></i> New Deal
                        </a>
                        @endcan
                    @endslot
                @endcomponent

                <div class="deal-stat-grid" id="dealStatGrid">
                    <div class="deal-stat-card crm-card">
                        <div>
                            <div class="deal-stat-value" id="statTotalDeals">0</div>
                            <div class="deal-stat-label">Total Deals</div>
                        </div>
                        <div class="deal-stat-icon" style="background:#eff6ff;color:#2563eb;"><i class="fa fa-handshake-o"></i></div>
                    </div>
                    <div class="deal-stat-card crm-card">
                        <div>
                            <div class="deal-stat-value" id="statOpenDeals">0</div>
                            <div class="deal-stat-label">Open Deals</div>
                        </div>
                        <div class="deal-stat-icon" style="background:#eef2ff;color:#4338ca;"><i class="fa fa-folder-open-o"></i></div>
                    </div>
                    <div class="deal-stat-card crm-card">
                        <div>
                            <div class="deal-stat-value" id="statPipelineValue">₹0</div>
                            <div class="deal-stat-label">Pipeline Value</div>
                        </div>
                        <div class="deal-stat-icon" style="background:#ecfdf5;color:#16a34a;"><i class="fa fa-inr"></i></div>
                    </div>
                    <div class="deal-stat-card crm-card">
                        <div>
                            <div class="deal-stat-value" id="statWonDeals">0</div>
                            <div class="deal-stat-label">Won Deals</div>
                        </div>
                        <div class="deal-stat-icon" style="background:#fff7ed;color:#ea580c;"><i class="fa fa-trophy"></i></div>
                    </div>
                </div>

                <div id="pipelineFilterBanner" class="alert alert-info d-none justify-content-between align-items-center" style="font-size:13px;">
                    <span>Showing deals in pipeline: <strong id="pipelineFilterName"></strong></span>
                    <a href="{{ route('deals.list') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-times"></i> Clear filter
                    </a>
                </div>

                <div class="crm-filters">
                    <input type="text" id="dealSearchInput" class="crm-input" style="flex:2 1 260px;" placeholder="Search deal name…">
                    <select id="dealFilterStatus" class="crm-select">
                        <option value="">All statuses</option>
                        <option value="open">Open</option>
                        <option value="won">Won</option>
                        <option value="lost">Lost</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="crm-table-wrap">
                            <div class="table-responsive">
                                <table class="table crm-table" id="dealTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Deal</th>
                                            <th>Amount</th>
                                            <th>Pipeline</th>
                                            <th>Stage</th>
                                            <th>Owner</th>
                                            <th>Lead</th>
                                            <th>Expected Close</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- AJAX DATA -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="dealPagination" class="mt-3"></div>
                        </div>
                    </div>
                </div>

                @include('include.footer')

            </div>
        </div>
    </div>

    <div class="modal fade crm-modal" id="assignDealModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <div class="crm-modal__icon"><i class="fa fa-user-plus"></i></div>
                    <div class="crm-modal__heading">
                        <h5 class="modal-title">Assign Deal</h5>
                        <p class="crm-modal__subtitle">Choose a team member to take ownership of this deal.</p>
                    </div>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="assignDealId">

                    <div class="form-group mb-0">
                        <label class="crm-label">Assign To</label>
                        <select id="assignedDealUser" class="form-control">
                            <option value="">Loading...</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="crm-btn crm-btn--secondary crm-btn--sm" data-dismiss="modal">Cancel</button>
                    <button class="crm-btn crm-btn--primary crm-btn--sm" id="saveAssignedDealUser"><i class="fa fa-check"></i> Save</button>
                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>
    <script src="{{asset('vendors/select2/select2.min.js')}}"></script>

    <script>
        function pretty(s) {
            if (!s) return '-';
            return s.toString().replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        }

        function dash(v) {
            return (v === null || v === undefined || v === '' || v === 'null') ? '-' : v;
        }

        function money(v) {
            if (v === null || v === undefined || v === '') return '0';
            return Number(v).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        const esc = value => $('<div>').text(value ?? '').html();
        // Deal status pill now comes from the shared renderStatusBadge()
        // (public/js/crm-status.js) — safeToken() was only needed to build
        // the old per-status "status-open/status-won/status-lost" class name.

        const DEAL_PALETTE = ['#2563eb', '#7c3aed', '#0d9488', '#ea580c', '#db2777', '#16a34a', '#4338ca', '#0891b2'];
        function dealPaletteColor(seed) {
            let hash = 0;
            String(seed || '').split('').forEach(ch => { hash = (hash * 31 + ch.charCodeAt(0)) >>> 0; });
            return DEAL_PALETTE[hash % DEAL_PALETTE.length];
        }
        function dealInitials(name) {
            const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
            if (!parts.length) return '?';
            return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
        }
        function stagePillColor(name, color) {
            if (color && /^#[0-9a-f]{3,8}$/i.test(color)) return color;
            const lower = String(name || '').toLowerCase();
            if (lower.includes('won')) return '#16a34a';
            if (lower.includes('lost')) return '#dc2626';
            return dealPaletteColor(name);
        }

        function renderDealStats(summary) {
            if (!summary) return;
            $('#statTotalDeals').text(summary.total || 0);
            $('#statOpenDeals').text(summary.open || 0);
            $('#statWonDeals').text(summary.won || 0);
            $('#statPipelineValue').text('₹' + money(summary.pipelineValue));
        }

        function getQueryParam(name) {
            return new URLSearchParams(window.location.search).get(name);
        }

        let dealCurrentPage = 1;
        let dealSearchDebounce = null;

        function loadDealList() {
            const pipelineId = getQueryParam('pipeline_id');

            $.ajax({
                url: "{{ route('deals.data') }}",
                type: "GET",
                data: {
                    pipeline_id: pipelineId || undefined,
                    page: dealCurrentPage,
                    search: $('#dealSearchInput').val() || undefined,
                    status: $('#dealFilterStatus').val() || undefined,
                },
                success: function(response) {
                    let tbody = '';

                    if (response.data && response.data.length > 0) {
                        if (pipelineId) {
                            $('#pipelineFilterName').text(response.data[0].pipeline_name);
                            $('#pipelineFilterBanner').removeClass('d-none').addClass('d-flex');
                        }

                        response.data.forEach((item) => {
                            const leadCell = item.lead_id
                                ? `<a href="{{ url('leads') }}/${item.lead_id}">${esc(dash(item.lead_name))}</a>`
                                : '-';
                            const pipelineCell = item.pipeline_id
                                ? `<a href="{{ url('deals') }}?pipeline_id=${item.pipeline_id}">${esc(dash(item.pipeline_name))}</a>`
                                : '-';

                            const stageColor = stagePillColor(item.stage_name, item.stage_color);
                            const ownerCell = item.owner_name
                                ? `<span class="owner-cell assign-deal" data-deal_id="${item.id}" style="cursor:pointer;"><span class="owner-avatar" style="background:${dealPaletteColor(item.owner_name)}">${dealInitials(item.owner_name)}</span><span class="owner-name">${esc(item.owner_name)}</span></span>`
                                : `<span class="owner-cell assign-deal is-unassigned" data-deal_id="${item.id}" style="cursor:pointer;"><span class="owner-name">Unassigned</span></span>`;

                            tbody += `
                    <tr>
                        <td>${item.sl_no}</td>
                        <td><a href="{{ url('deals') }}/${item.id}"><strong>${esc(item.name)}</strong></a></td>
                        <td>${esc(item.currency ?? '')} ${money(item.amount)}</td>
                        <td>${pipelineCell}</td>
                        <td><span class="stage-pill" style="background:${stageColor}1a;color:${stageColor}">${esc(dash(item.stage_name))}</span></td>
                        <td>${ownerCell}</td>
                        <td>${leadCell}</td>
                        <td>${esc(dash(item.expected_close_date))}</td>
                        <td>${renderStatusBadge('deal', item.status)}</td>
                        <td>${item.action}</td>
                    </tr>`;
                        });
                    } else {
                        if (pipelineId) {
                            $('#pipelineFilterBanner').removeClass('d-none').addClass('d-flex');
                            $('#pipelineFilterName').text('this pipeline');
                        }
                        tbody = `<tr><td colspan="10">
                            <div class="crm-empty">
                                <div class="crm-empty__icon"><i class="fa fa-handshake-o"></i></div>
                                <p class="crm-empty__title">No deals found</p>
                                <p class="crm-empty__desc">Deals you create, or leads that convert, will show up here.</p>
                            </div>
                        </td></tr>`;
                    }

                    $('#dealTable tbody').html(tbody);
                    renderDealStats(response.summary);
                    renderCrmPagination('#dealPagination', response.meta, function(page) {
                        dealCurrentPage = page;
                        loadDealList();
                    });
                }
            });
        }

        $(document).on('input', '#dealSearchInput', function() {
            clearTimeout(dealSearchDebounce);
            dealSearchDebounce = setTimeout(function() {
                dealCurrentPage = 1;
                loadDealList();
            }, 350);
        });

        $(document).on('change', '#dealFilterStatus', function() {
            dealCurrentPage = 1;
            loadDealList();
        });

        $(document).on('click', '.assign-deal', function() {
            const dealId = $(this).data('deal_id');
            $('#assignDealId').val(dealId);

            $('#assignedDealUser').html('<option value="">Loading...</option>');
            $.ajax({
                url: "{{ route('leads.assignable_users') }}",
                type: "GET",
                success: function(res) {
                    let options = '<option value="">-- Select User --</option>';
                    res.users.forEach(function(user) {
                        options += `<option value="${user.id}">${esc(user.name)}</option>`;
                    });
                    $('#assignedDealUser').html(options);

                    if ($('#assignedDealUser').data('select2')) {
                        $('#assignedDealUser').select2('destroy');
                    }
                    $('#assignedDealUser').select2({
                        dropdownParent: $('#assignDealModal'),
                        width: '100%',
                        placeholder: '-- Select User --',
                    });

                    $('#assignDealModal').modal('show');
                }
            });
        });

        $(document).on('click', '#saveAssignedDealUser', function() {
            const dealId = $('#assignDealId').val();
            const ownerId = $('#assignedDealUser').val();

            if (!ownerId) {
                toastr.error('Please select a user');
                return;
            }

            const $btn = $(this).prop('disabled', true).text('Saving...');

            $.ajax({
                url: "{{ route('deals.assign') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    deal_id: dealId,
                    owner_id: ownerId
                },
                success: function(res) {
                    if (res.status) {
                        toastr.success(res.message);
                        $('#assignDealModal').modal('hide');
                        loadDealList();
                    } else {
                        toastr.error(res.message || 'Something went wrong');
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Save');
                }
            });
        });

        $(document).ready(function() {
            loadDealList();
        });
    </script>

</body>

</html>
