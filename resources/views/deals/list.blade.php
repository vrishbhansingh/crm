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
        .order-table-wrapper {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        }

        .order-table thead th {
            background: #f5f7fb;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
        }

        .order-table tbody td {
            font-size: 13px;
            text-align: center;
            vertical-align: middle;
        }

        .status-badge {
            padding: 5px 12px;
            font-size: 11px;
            border-radius: 20px;
            display: inline-block;
        }

        .status-open { background: #eef2ff; color: #4338ca; }
        .status-won { background: #dcfce7; color: #15803d; }
        .status-lost { background: #fee2e2; color: #b91c1c; }

        .page-header {
            background: #ffffff;
            padding: 18px 22px;
            border-radius: 14px;
            margin-bottom: 18px;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h4 {
            font-weight: 600;
            margin: 0;
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
                    <div>
                        <h4>Deals</h4>
                        <p class="text-muted mb-0" style="font-size:12px;">{{ Auth::guard('web')->user()->hasElevatedAccess() ? 'Every deal in the pipeline' : 'Your deals' }}</p>
                    </div>
                    <a href="{{ route('deals.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-columns"></i> Kanban View
                    </a>
                </div>

                <div id="pipelineFilterBanner" class="alert alert-info d-flex justify-content-between align-items-center" style="display:none;font-size:13px;">
                    <span>Showing deals in pipeline: <strong id="pipelineFilterName"></strong></span>
                    <a href="{{ route('deals.list') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-times"></i> Clear filter
                    </a>
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

        function loadDealList() {
            const pipelineId = getQueryParam('pipeline_id');

            $.ajax({
                url: "{{ route('deals.data') }}",
                type: "GET",
                data: pipelineId ? { pipeline_id: pipelineId } : {},
                success: function(response) {
                    let tbody = '';

                    if (response.data && response.data.length > 0) {
                        if (pipelineId) {
                            $('#pipelineFilterName').text(response.data[0].pipeline_name);
                            $('#pipelineFilterBanner').show();
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
                            $('#pipelineFilterBanner').show();
                            $('#pipelineFilterName').text('this pipeline');
                        }
                        tbody = `<tr><td colspan="10">No deals found</td></tr>`;
                    }

                    $('#dealTable tbody').html(tbody);
                }
            });
        }

        $(document).ready(function() {
            loadDealList();
        });
    </script>

</body>

</html>
