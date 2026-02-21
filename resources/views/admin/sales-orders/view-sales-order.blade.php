<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order View | CRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        body {
            background: #f4f6fb;
        }

        /* ===== HEADER ===== */
        .order-hero {
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: #fff;
            padding: 26px 32px;
            border-radius: 18px;
            margin-bottom: 26px;
        }

        .order-hero h2 {
            margin: 0;
            font-weight: 700;
        }

        .order-hero span {
            font-size: 13px;
            opacity: .9;
        }

        /* ===== CARD ===== */
        .card-box {
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(0, 0, 0, .06);
            margin-bottom: 26px;
        }

        /* ===== GRID ===== */
        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        @media(max-width:992px) {
            .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }

            .grid-3 {
                grid-template-columns: 1fr;
            }
        }

        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 16px;
        }

        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 16px;
            font-weight: 700;
            margin-top: 4px;
        }

        /* ===== AMOUNT ===== */
        .amount-box {
            text-align: center;
            padding: 20px;
            border-radius: 16px;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        }

        .amount-box h3 {
            font-size: 24px;
            margin: 0;
            font-weight: 800;
        }

        .amount-box span {
            font-size: 12px;
            color: #6b7280;
        }

        /* ===== BADGES ===== */
        .badge-pill {
            padding: 6px 16px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-new {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #e9f7ef;
            color: #1e7e34;
        }

        .status-pending {
            background: #eef2ff;
            color: #4b49ac;
        }

        /* ===== FOOT ACTION ===== */
        .action-row {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .action-btn {
            padding: 10px 18px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #2563eb;
            color: #fff;
        }

        .btn-outline {
            background: #fff;
            border: 1px solid #d1d5db;
            color: #111827;
        }
    </style>
</head>

<body>

    <div class="container-scroller">
        @include('admin.include.header')

        <div class="container-fluid page-body-wrapper">
            @include('admin.include.sidebar')

            <div class="content-wrapper">

                <!-- HERO -->
                <div class="order-hero">
                    <h2 id="orderTitle">Order</h2>
                    <span id="orderSubTitle">Loading order details...</span>
                </div>

                <!-- ORDER INFO -->
                <div class="card-box">
                    <div class="grid-4">
                        <div class="info-box">
                            <div class="info-label">Customer</div>
                            <div class="info-value" id="customerName">-</div>
                        </div>

                        <div class="info-box">
                            <div class="info-label">Project</div>
                            <div class="info-value" id="projectName">-</div>
                        </div>

                        <div class="info-box">
                            <div class="info-label">Order Status</div>
                            <span class="badge-pill status-new" id="orderStatus">-</span>
                        </div>

                        <div class="info-box">
                            <div class="info-label">Payment Status</div>
                            <span class="badge-pill status-pending" id="paymentStatus">-</span>
                        </div>
                    </div>
                </div>

                <!-- AMOUNT SUMMARY -->
                <div class="card-box">
                    <div class="grid-3">
                        <div class="amount-box">
                            <h3 id="totalAmount">0</h3>
                            <span>Total Amount</span>
                        </div>

                        <div class="amount-box">
                            <h3 id="paidAmount">0</h3>
                            <span>Paid</span>
                        </div>

                        <div class="amount-box">
                            <h3 id="dueAmount">0</h3>
                            <span>Due</span>
                        </div>
                    </div>
                </div>

                <!-- PAYMENT DETAILS -->
                <div class="card-box">
                    <div class="grid-3">
                        <div class="info-box">
                            <div class="info-label">Invoice ID</div>
                            <div class="info-value" id="invoiceId">-</div>
                        </div>

                        <div class="info-box">
                            <div class="info-label">Invoice Date</div>
                            <div class="info-value" id="invoiceDate">-</div>
                        </div>

                        <div class="info-box">
                            <div class="info-label">Currency</div>
                            <div class="info-value" id="currency">-</div>
                        </div>
                    </div>

                    <div class="action-row">
                        <a class="action-btn btn-outline" href="{{route('admin.sales_orders')}}">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                        <button class="action-btn btn-primary">
                            <i class="fa fa-print"></i> Print
                        </button>
                    </div>
                </div>

                @include('admin.include.footer')

            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>

    <script>
        $(document).ready(function() {

            const id = window.location.search.replace('?', '');

            $.ajax({
                url: "{{ route('admin.get_view_sales_order_list') }}",
                type: "GET",
                data: {
                    id: id
                },

                success: function(res) {

                    const o = res.data[0];

                    $('#orderTitle').text(`Order #${o.order_number}`);
                    $('#orderSubTitle').text(`Invoice ${o.invoice_id} • ${o.invoice_date}`);

                    $('#customerName').text(o.user_name);
                    $('#projectName').text(o.project_name ?? '-');

                    $('#orderStatus').text(o.order_status);
                    $('#paymentStatus').text(o.payment_status);

                    $('#totalAmount').text(`${o.currency} ${o.total_amount}`);
                    $('#paidAmount').text(`${o.currency} ${o.paid_amount}`);
                    $('#dueAmount').text(`${o.currency} ${o.due_amount}`);

                    $('#invoiceId').text(o.invoice_id);
                    $('#invoiceDate').text(o.invoice_date);
                    $('#currency').text(o.currency);
                }
            });
        });
    </script>

</body>

</html>