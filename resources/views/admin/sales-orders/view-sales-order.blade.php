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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { background: #eef1f7; font-family: 'Inter', sans-serif; }

        /* ===== HERO ===== */
        .order-hero {
            position: relative;
            background: linear-gradient(135deg, #2563eb, #4f46e5 70%, #7c3aed);
            color: #fff;
            padding: 28px 32px;
            border-radius: 20px;
            margin-bottom: 22px;
            box-shadow: 0 18px 40px rgba(79, 70, 229, .28);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .order-hero h2 { margin: 0; font-weight: 800; font-size: 26px; letter-spacing: .3px; }
        .order-hero .sub { font-size: 13px; opacity: .9; margin-top: 6px; }
        .order-hero .hero-actions { display: flex; gap: 10px; }

        .hero-btn {
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            transition: all .2s ease;
        }

        .hero-btn-light { background: rgba(255, 255, 255, .15); color: #fff; }
        .hero-btn-light:hover { background: rgba(255, 255, 255, .28); color: #fff; }
        .hero-btn-white { background: #fff; color: #4f46e5; }
        .hero-btn-white:hover { box-shadow: 0 8px 18px rgba(0, 0, 0, .15); color: #4f46e5; }

        /* ===== CARD ===== */
        .card-box {
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .05);
            margin-bottom: 22px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i { color: #4f46e5; }

        .layout-2col {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 22px;
        }

        @media (max-width: 992px) {
            .layout-2col { grid-template-columns: 1fr; }
        }

        /* ===== INFO ROWS ===== */
        .info-list { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 24px; }
        .info-item .info-label {
            font-size: 11px;
            letter-spacing: .04em;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .info-item .info-value { font-size: 15px; font-weight: 600; color: #1e293b; }

        /* ===== BADGES ===== */
        .badge-pill {
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            text-transform: capitalize;
        }
        .b-green  { background: #e7f7ee; color: #1f9254; }
        .b-amber  { background: #fef3e2; color: #c77700; }
        .b-red    { background: #fdecec; color: #d64545; }
        .b-blue   { background: #e8effd; color: #2563eb; }
        .b-indigo { background: #eceafe; color: #6366f1; }
        .b-gray   { background: #eef1f5; color: #64748b; }

        /* ===== PAYMENT PROGRESS ===== */
        .pay-amounts { display: flex; justify-content: space-between; margin-bottom: 12px; }
        .pay-amounts .big { font-size: 22px; font-weight: 800; color: #0f172a; }
        .pay-amounts .lbl { font-size: 12px; color: #64748b; }
        .progress-track {
            height: 12px;
            border-radius: 999px;
            background: #eef1f5;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            width: 0;
            transition: width .6s ease;
        }
        .progress-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 12px;
            color: #64748b;
        }
        .due-chip { font-weight: 700; color: #d64545; }

        /* ===== INVOICE BREAKDOWN ===== */
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 11px 0; font-size: 14px; }
        .summary-table td:last-child { text-align: right; font-weight: 600; color: #1e293b; }
        .summary-table td:first-child { color: #64748b; }
        .summary-table tr.divider td { border-top: 1px dashed #e2e8f0; }
        .summary-table tr.grand td {
            border-top: 2px solid #e2e8f0;
            padding-top: 14px;
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
        }
        .summary-table .neg { color: #d64545 !important; }

        /* ===== PAYMENT HISTORY ===== */
        .pay-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        .pay-table th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #94a3b8;
            font-weight: 600;
            text-align: left;
            padding: 0 14px;
        }
        .pay-table td {
            background: #f8fafc;
            padding: 13px 14px;
            font-size: 14px;
            color: #1e293b;
        }
        .pay-table td:first-child { border-radius: 10px 0 0 10px; }
        .pay-table td:last-child { border-radius: 0 10px 10px 0; text-align: right; font-weight: 700; }
        .pay-mode {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 999px;
            background: #eceafe;
            color: #6366f1;
            text-transform: capitalize;
        }
        .empty-row { text-align: center; color: #94a3b8; padding: 20px; font-size: 14px; }

        /* ===== PRINT ===== */
        @media print {
            body { background: #fff; }
            .crm-navbar, .sidebar, .footer, .no-print { display: none !important; }
            .content-wrapper { margin: 0 !important; padding: 0 !important; width: 100% !important; }
            .page-body-wrapper { display: block !important; }
            .order-hero { box-shadow: none; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .card-box { box-shadow: none; border: 1px solid #e5e7eb; }
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
                    <div>
                        <h2 id="orderTitle">Order</h2>
                        <div class="sub" id="orderSubTitle">Loading order details...</div>
                    </div>
                    <div class="hero-actions no-print">
                        <a class="hero-btn hero-btn-light" href="{{ route('admin.sales_orders') }}">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                        <button class="hero-btn hero-btn-white" onclick="window.print()">
                            <i class="fa fa-print"></i> Print
                        </button>
                    </div>
                </div>

                <!-- TOP: ORDER INFO + PAYMENT PROGRESS -->
                <div class="layout-2col">

                    <div class="card-box">
                        <div class="card-title"><i class="fa fa-file-text-o"></i> Order Information</div>
                        <div class="info-list">
                            <div class="info-item">
                                <div class="info-label">Customer</div>
                                <div class="info-value" id="customerName">-</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Project</div>
                                <div class="info-value" id="projectName">-</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Order Status</div>
                                <span class="badge-pill b-gray" id="orderStatus">-</span>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Payment Status</div>
                                <span class="badge-pill b-gray" id="paymentStatus">-</span>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Payment Terms</div>
                                <div class="info-value" id="paymentTerms">-</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Currency</div>
                                <div class="info-value" id="currency">-</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Invoice ID</div>
                                <div class="info-value" id="invoiceId">-</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Invoice Date</div>
                                <div class="info-value" id="invoiceDate">-</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-box">
                        <div class="card-title"><i class="fa fa-credit-card"></i> Payment Progress</div>
                        <div class="pay-amounts">
                            <div>
                                <div class="big" id="paidBig">0</div>
                                <div class="lbl">Paid</div>
                            </div>
                            <div style="text-align:right;">
                                <div class="big" id="totalBig">0</div>
                                <div class="lbl">Total</div>
                            </div>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <div class="progress-meta">
                            <span id="paidPercent">0% paid</span>
                            <span class="due-chip" id="dueChip">Due: 0</span>
                        </div>
                    </div>
                </div>

                <!-- AMOUNT BREAKDOWN -->
                <div class="card-box">
                    <div class="card-title"><i class="fa fa-calculator"></i> Amount Breakdown</div>
                    <table class="summary-table">
                        <tr>
                            <td>Sub Total</td>
                            <td id="subTotal">-</td>
                        </tr>
                        <tr>
                            <td>Discount</td>
                            <td class="neg" id="discount">-</td>
                        </tr>
                        <tr>
                            <td>GST</td>
                            <td id="gst">-</td>
                        </tr>
                        <tr class="grand">
                            <td>Total Amount</td>
                            <td id="totalAmount">-</td>
                        </tr>
                        <tr class="divider">
                            <td>Paid</td>
                            <td id="paidAmount" style="color:#1f9254;">-</td>
                        </tr>
                        <tr>
                            <td>Due</td>
                            <td id="dueAmount" style="color:#d64545;">-</td>
                        </tr>
                    </table>
                </div>

                <!-- PAYMENT HISTORY -->
                <div class="card-box">
                    <div class="card-title"><i class="fa fa-history"></i> Payment History</div>
                    <table class="pay-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Mode</th>
                                <th style="text-align:right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="paymentHistory">
                            <tr><td colspan="3" class="empty-row">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>

                @include('admin.include.footer')

            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>

    <script>
        const CURRENCY_SYMBOL = { 'INR': '₹', 'EURO': '€', 'DOLLOR': '$' };

        function sym(cur) { return CURRENCY_SYMBOL[cur] || (cur ? cur + ' ' : ''); }

        function money(cur, v) {
            const n = Number(v || 0);
            return sym(cur) + n.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        function orderBadge(status) {
            const map = {
                new: 'b-amber', approved: 'b-blue', in_progress: 'b-indigo',
                on_hold: 'b-gray', delivered: 'b-green', closed: 'b-gray', cancelled: 'b-red'
            };
            return map[status] || 'b-gray';
        }

        function payBadge(status) {
            const map = { paid: 'b-green', partial: 'b-amber', pending: 'b-red' };
            return map[status] || 'b-gray';
        }

        function pretty(s) {
            if (!s) return '-';
            return s.toString().replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        }

        $(document).ready(function() {

            const id = window.location.search.replace('?', '');

            $.ajax({
                url: "{{ route('admin.get_view_sales_order_list') }}",
                type: "GET",
                data: { id: id },
                success: function(res) {
                    if (!res || !res.data || !res.data.length) {
                        $('#orderSubTitle').text('Order not found');
                        $('#paymentHistory').html('<tr><td colspan="3" class="empty-row">No data</td></tr>');
                        return;
                    }

                    const o = res.data[0];
                    const cur = o.currency;

                    $('#orderTitle').text(`Order #${o.order_number}`);
                    $('#orderSubTitle').text(`Invoice ${o.invoice_id} • ${o.invoice_date}`);

                    $('#customerName').text(o.user_name ?? '-');
                    $('#projectName').text(o.project_name ?? '-');
                    $('#paymentTerms').text(pretty(o.payment_terms));
                    $('#currency').text(cur ?? '-');
                    $('#invoiceId').text(o.invoice_id ?? '-');
                    $('#invoiceDate').text(o.invoice_date ?? '-');

                    $('#orderStatus').text(pretty(o.order_status)).attr('class', 'badge-pill ' + orderBadge(o.order_status));
                    $('#paymentStatus').text(pretty(o.payment_status)).attr('class', 'badge-pill ' + payBadge(o.payment_status));

                    // breakdown
                    $('#subTotal').text(money(cur, o.sub_total));
                    $('#discount').text('- ' + money(cur, o.discount));
                    $('#gst').text((o.gst ?? 0) + '%');
                    $('#totalAmount').text(money(cur, o.total_amount));
                    $('#paidAmount').text(money(cur, o.paid_amount));
                    $('#dueAmount').text(money(cur, o.due_amount));

                    // progress
                    const total = Number(o.total_amount || 0);
                    const paid = Number(o.paid_amount || 0);
                    const pct = total > 0 ? Math.min(100, Math.round((paid / total) * 100)) : 0;
                    $('#paidBig').text(money(cur, paid));
                    $('#totalBig').text(money(cur, total));
                    $('#progressFill').css('width', pct + '%');
                    $('#paidPercent').text(pct + '% paid');
                    $('#dueChip').text('Due: ' + money(cur, o.due_amount));

                    // payment history
                    const pays = o.payments || [];
                    if (!pays.length) {
                        $('#paymentHistory').html('<tr><td colspan="3" class="empty-row">No payments recorded yet.</td></tr>');
                    } else {
                        let rows = '';
                        pays.forEach(p => {
                            rows += `
                                <tr>
                                    <td>${p.payment_date ?? '-'}</td>
                                    <td><span class="pay-mode">${pretty(p.payment_mode)}</span></td>
                                    <td>${money(cur, p.paid_amount)}</td>
                                </tr>`;
                        });
                        $('#paymentHistory').html(rows);
                    }
                },
                error: function() {
                    $('#orderSubTitle').text('Failed to load order');
                    $('#paymentHistory').html('<tr><td colspan="3" class="empty-row">Something went wrong.</td></tr>');
                }
            });
        });
    </script>

</body>

</html>
