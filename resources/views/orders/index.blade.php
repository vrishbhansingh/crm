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
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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

        /* .order-table-wrapper/.order-table/.status-badge/.status-*/
           .payment-badge/.pay-* removed in V2 — this list now uses the
           shared .crm-table-wrap/.crm-table/.crm-badge/.crm-header
           components from crm-components.css (loaded globally). Order
           and payment status render via renderStatusBadge()
           (public/js/crm-status.js). */
    </style>
</head>

<body>

    <div class="container-scroller">

        @include('include.header')

        <div class="container-fluid page-body-wrapper">

            @include('include.sidebar')

            <div class="content-wrapper">

                @component('include.page-header', [
                    'icon' => 'fa fa-shopping-cart',
                    'title' => 'Orders',
                    'subtitle' => Auth::guard('web')->user()->hasElevatedAccess() ? 'Manage all customer orders' : 'Your orders',
                ])
                @endcomponent

                <!-- Order Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="crm-table-wrap">
                            <div class="table-responsive">
                                <table class="table crm-table" id="orderTable">
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
        // Order/payment status pills now come from the shared
        // renderStatusBadge() (public/js/crm-status.js) — payClass()/
        // safeToken() were only needed to build the old status-${token}
        // class names.

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

                        <td>${renderStatusBadge('payment', item.payment_status)}</td>

                        <td>${renderStatusBadge('order', item.order_status)}</td>

                        <td>
                            ${item.action}
                        </td>
                    </tr>`;
                        });

                    } else {
                        tbody = `
                    <tr>
                        <td colspan="8">
                            <div class="crm-empty">
                                <div class="crm-empty__icon"><i class="fa fa-shopping-cart"></i></div>
                                <p class="crm-empty__title">No orders yet</p>
                                <p class="crm-empty__desc">Orders created from won deals will show up here.</p>
                            </div>
                        </td>
                    </tr>`;
                    }

                    $('#orderTable tbody').html(tbody);
                },
                error: function() {
                    toastr.error('Something went wrong while loading orders');
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

        $(document).ready(function() {
            loadOrderList();
        });
    </script>

</body>

</html>
