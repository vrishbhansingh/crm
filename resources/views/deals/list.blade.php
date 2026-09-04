<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Deals | CRM</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">

    <style>
        /* Same modernization pattern as Roles & Permissions / Dashboard /
           Leads: bigger type, roomier cards. Pure visual pass — IDs and
           structure this page's JS depends on are untouched. */
        .order-table-wrapper {
            background: #fff;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .order-table { font-size: 14.5px; }

        .order-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 12.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            text-align: center;
            padding: 14px 12px;
        }

        .order-table tbody td {
            font-size: 14px;
            text-align: center;
            vertical-align: middle;
            padding: 14px 12px;
        }

        .status-badge {
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 999px;
            display: inline-block;
        }

        .status-open { background: #eef2ff; color: #4338ca; }
        .status-won { background: #dcfce7; color: #15803d; }
        .status-lost { background: #fee2e2; color: #b91c1c; }

        .page-header {
            background: #ffffff;
            padding: 26px 28px;
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-header h4 {
            font-weight: 700;
            font-size: 22px;
            margin: 0 0 6px;
            color: #111827;
        }

        .page-header .text-muted { font-size: 14px !important; }

        .page-header .btn { border-radius: 10px; padding: 10px 18px; font-weight: 600; }
    </style>
</head>

<body>

    <div class="container-scroller">

        @include('include.header')

        <div class="container-fluid page-body-wrapper">

            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="page-header">
                    <div>
                        <h4>Deals</h4>
                        <p class="text-muted mb-0" style="font-size:12px;">{{ Auth::guard('web')->user()->hasElevatedAccess() ? 'Every deal in the pipeline' : 'Your deals' }}</p>
                    </div>
                    <div class="d-flex" style="gap:8px;">
                        <a href="{{ route('deals.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-columns"></i> Kanban View
                        </a>
                        @can('deals.create')
                        <a href="{{ route('deals.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> New Deal
                        </a>
                        @endcan
                    </div>
                </div>

                <div id="pipelineFilterBanner" class="alert alert-info d-none justify-content-between align-items-center" style="font-size:13px;">
                    <span>Showing deals in pipeline: <strong id="pipelineFilterName"></strong></span>
                    <a href="{{ route('deals.list') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-times"></i> Clear filter
                    </a>
                </div>

                <div class="row mb-3">
                    <div class="col-md-5 mb-2">
                        <input type="text" id="dealSearchInput" class="form-control form-control-sm" placeholder="Search deal name…">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select id="dealFilterStatus" class="form-control form-control-sm">
                            <option value="">All statuses</option>
                            <option value="open">Open</option>
                            <option value="won">Won</option>
                            <option value="lost">Lost</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="order-table-wrapper">
                            <div class="table-responsive">
                                <table class="table order-table" id="dealTable">
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>

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
        const safeToken = value => /^[a-z0-9_-]+$/i.test(value || '') ? value : 'unknown';

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

                            tbody += `
                    <tr>
                        <td>${item.sl_no}</td>
                        <td><a href="{{ url('deals') }}/${item.id}"><strong>${esc(item.name)}</strong></a></td>
                        <td>${esc(item.currency ?? '')} ${money(item.amount)}</td>
                        <td>${pipelineCell}</td>
                        <td>${esc(dash(item.stage_name))}</td>
                        <td>${esc(dash(item.owner_name))}</td>
                        <td>${leadCell}</td>
                        <td>${esc(dash(item.expected_close_date))}</td>
                        <td><span class="status-badge status-${safeToken(item.status)}">${esc(pretty(item.status))}</span></td>
                        <td>${item.action}</td>
                    </tr>`;
                        });
                    } else {
                        if (pipelineId) {
                            $('#pipelineFilterBanner').removeClass('d-none').addClass('d-flex');
                            $('#pipelineFilterName').text('this pipeline');
                        }
                        tbody = `<tr><td colspan="10">No deals found</td></tr>`;
                    }

                    $('#dealTable tbody').html(tbody);
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

        $(document).ready(function() {
            loadDealList();
        });
    </script>

</body>

</html>
