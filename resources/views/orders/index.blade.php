<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Orders | CRM</title>

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">

    <style>
        /* --text-dark/--text-muted were referenced below but never defined
           in this file — silently fell back to the browser default rather
           than the intended color. Defining them properly here. */
        :root {
            --text-dark: #111827;
            --text-muted: #6b7280;
        }

        /* Same modernization pattern as the rest of this pass. */
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
            padding: 5px 12px;
            font-size: 11px;
            border-radius: 20px;
            display: inline-block;
        }

        .status-approved {
            background: #e9f7ef;
            color: #1e7e34;
        }

        .status-new {
            background: #fff3cd;
            color: #856404;
        }

        .payment-badge {
            padding: 5px 10px;
            font-size: 11px;
            border-radius: 6px;
            background: #eef2ff;
            color: #4b49ac;
        }

        /* payment status colors */
        .pay-paid    { background: #e7f7ee; color: #1f9254; }
        .pay-partial { background: #fef3e2; color: #c77700; }
        .pay-pending { background: #fdecec; color: #d64545; }

        /* order status colors */
        .status-in_progress { background: #eceafe; color: #6366f1; }
        .status-on_hold     { background: #eef1f5; color: #64748b; }
        .status-delivered   { background: #e7f7ee; color: #1f9254; }
        .status-closed      { background: #eef1f5; color: #64748b; }
        .status-cancelled   { background: #fdecec; color: #d64545; }

        /* Same modernization pattern as the rest of this pass — plain
           white card, no accent stripe, matching every other page. */
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
            color: var(--text-dark);
            margin: 0 0 6px;
        }

        .page-header p {
            font-size: 14px;
            color: var(--text-muted);
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

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h4>Orders</h4>
                        <p>{{ Auth::guard('web')->user()->hasElevatedAccess() ? 'Manage all customer orders' : 'Your orders' }}</p>
                    </div>
                </div>

                <!-- Order Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="order-table-wrapper">
                            <div class="table-responsive">
                                <table class="table order-table" id="orderTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Order</th>
                                            <th>Invoice</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Payment</th>
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

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>

    <!-- DataTables -->
    <script src="{{ asset('vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>

    <script>
        const esc = value => $('<div>').text(value ?? '').html();
        const safeToken = value => /^[a-z0-9_-]+$/i.test(value || '') ? value : 'unknown';

        function loadOrderList() {
            $.ajax({
                url: "{{ route('orders.data') }}",
                type: "GET",
                success: function(response) {

                    let tbody = '';

                    if (response.data && response.data.length > 0) {

                        response.data.forEach((item, index) => {

                            tbody += `
                    <tr>
                        <td>${item.sl_no}</td>

                        <td>
                            <strong>${esc(item.order_number)}</strong><br>
                            <small class="text-muted">${esc(item.invoice_date)}</small>
                        </td>

                        <td>${esc(dash(item.invoice_id))}</td>

                        <td>
                            <small>User Name: ${esc(dash(item.user_name))}</small><br>
                            <small>Project Name: ${esc(dash(item.project_name))}</small>
                        </td>

                        <td>
                            <strong>${esc(item.currency ?? '')} ${money(item.total_amount)}</strong><br>
                            <small class="text-muted">
                                Paid: ${money(item.paid_amount)} | Due: ${money(item.due_amount)}
                            </small>
                        </td>

                        <td>
                            <span class="payment-badge ${payClass(item.payment_status)}">
                                ${esc(pretty(item.payment_status))}
                            </span>
                        </td>

                        <td>
                            <span class="status-badge status-${safeToken(item.order_status)}">
                                ${esc(pretty(item.order_status))}
                            </span>
                        </td>

                        <td>
                            ${item.action}
                        </td>
                    </tr>`;
                        });

                    } else {
                        tbody = `
                    <tr>
                        <td colspan="8">No orders found</td>
                    </tr>`;
                    }

                    $('#orderTable tbody').html(tbody);
                }
            });
        }

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

        function payClass(s) {
            const map = { paid: 'pay-paid', partial: 'pay-partial', pending: 'pay-pending' };
            return map[s] || '';
        }

        $(document).ready(function() {
            loadOrderList();
        });
    </script>

</body>

</html>
