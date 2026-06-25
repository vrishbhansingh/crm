<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Orders | CRM Admin</title>

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- DataTables -->
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

        .page-header {
            background: #ffffff;
            padding: 18px 22px;
            border-radius: 14px;
            margin-bottom: 18px;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .page-header::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 5px;
            height: 100%;
            border-radius: 14px 0 0 14px;
            background: linear-gradient(180deg, #2563eb, #4f46e5);
        }

        .page-header h4 {
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
        }

        .page-header p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 2px 0 0;
        }
    </style>
</head>

<body>

    <div class="container-scroller">

        @include('admin.include.header')

        <div class="container-fluid page-body-wrapper">

            @include('admin.include.sidebar')

            <div class="content-wrapper">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h4>Order List</h4>
                        <p>Manage all customer orders</p>
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

                @include('admin.include.footer')

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
        function loadOrderList() {
            $.ajax({
                url: "{{ route('admin.get_order_list') }}",
                type: "GET",
                success: function(response) {

                    let tbody = '';

                    if (response.data && response.data.length > 0) {

                        response.data.forEach((item, index) => {

                            tbody += `
                    <tr>
                        <td>${item.sl_no}</td>

                        <td>
                            <strong>${item.order_number}</strong><br>
                            <small class="text-muted">${item.invoice_date}</small>
                        </td>

                        <td>${dash(item.invoice_id)}</td>

                        <td>
                            <small>User Name: ${dash(item.user_name)}</small><br>
                            <small>Project Name: ${dash(item.project_name)}</small>
                        </td>

                        <td>
                            <strong>${item.currency ?? ''} ${money(item.total_amount)}</strong><br>
                            <small class="text-muted">
                                Paid: ${money(item.paid_amount)} | Due: ${money(item.due_amount)}
                            </small>
                        </td>

                        <td>
                            <span class="payment-badge ${payClass(item.payment_status)}">
                                ${pretty(item.payment_status)}
                            </span>
                        </td>

                        <td>
                            <span class="status-badge status-${item.order_status}">
                                ${pretty(item.order_status)}
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