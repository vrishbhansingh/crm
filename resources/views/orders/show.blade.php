<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Detail | CRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

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
        .order-hero .hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }

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
            justify-content: space-between;
            gap: 10px;
        }

        .card-title .card-title-left { display: flex; align-items: center; gap: 10px; }
        .card-title i { color: #4f46e5; }

        .layout-2col {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 22px;
            align-items: start;
        }

        .layout-2col > * { min-width: 0; }

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
        .pay-amounts {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 16px;
        }
        .pay-amounts .col-r { text-align: right; }
        .pay-amounts .lbl {
            font-size: 11px;
            letter-spacing: .04em;
            text-transform: uppercase;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        .pay-amounts .pp-amount {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
            white-space: nowrap;
        }
        .pay-amounts .pp-amount .rupee {
            font-weight: 500;
            font-size: .72em;
            color: #94a3b8;
            margin-right: 3px;
            vertical-align: 1px;
        }
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

        /* ===== EDIT FORM ===== */
        label { font-size: 12px; font-weight: 600; color: #6b7280; }

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
        @include('include.header')

        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')

            <div class="content-wrapper">

                <!-- HERO -->
                <div class="order-hero">
                    <div>
                        <h2 id="orderTitle">Order</h2>
                        <div class="sub" id="orderSubTitle">Loading order details...</div>
                    </div>
                    <div class="hero-actions no-print">
                        @can('tasks.create')
                        <button type="button" class="hero-btn hero-btn-light" onclick="openQuickTask('order', ORDER_ID, document.getElementById('orderTitle') ? document.getElementById('orderTitle').textContent : null)">
                            <i class="fa fa-check-square-o"></i> Add Task
                        </button>
                        @endcan
                        <a class="hero-btn hero-btn-light" href="#" id="invoiceBtn" style="display:none;">
                            <i class="fa fa-print"></i> View Invoice
                        </a>
                        <a class="hero-btn hero-btn-white" href="{{ route('orders.index') }}">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
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
                                <div class="lbl">Paid</div>
                                <div class="pp-amount" id="paidBig">0</div>
                            </div>
                            <div class="col-r">
                                <div class="lbl">Net</div>
                                <div class="pp-amount" id="totalBig">0</div>
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
                            <td>Net Amount</td>
                            <td id="netAmount">-</td>
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

                @can('orders.edit')
                <!-- EDIT ORDER (elevated / orders.edit only) -->
                <div class="card-box no-print">
                    <div class="card-title"><i class="fa fa-pencil"></i> Edit Order</div>

                    <form id="edit_sales_order">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Order Number</label>
                                <input type="text" id="order_number" name="order_number" class="form-control">
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Invoice Date</label>
                                <input type="date" id="invoice_date" name="invoice_date" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Sub Total</label>
                                <input type="number" step="0.01" id="edit_sub_total" name="sub_total" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Discount</label>
                                <input type="number" step="0.01" id="edit_discount" name="discount" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>GST (%)</label>
                                <input type="number" id="edit_gst" name="gst" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Total Amount</label>
                                <input type="number" step="0.01" id="edit_total_amount" name="total_amount" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Paid Amount</label>
                                <input type="number" step="0.01" id="edit_paid_amount" name="paid_amount" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Due Amount</label>
                                <input type="number" step="0.01" id="edit_due_amount" name="due_amount" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Order Status</label>
                                <select id="order_status" name="order_status" class="form-control">
                                    <option value="new">New</option>
                                    <option value="approved">Approved</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="on_hold">On Hold</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="closed">Closed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Payment Status</label>
                                <select id="payment_status" name="payment_status" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="partial">Partial</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Currency</label>
                                <select id="currency_edit" name="currency" class="form-control">
                                    <option value="INR">INR</option>
                                    <option value="EURO">EURO</option>
                                    <option value="DOLLOR">DOLLOR</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-right mt-2">
                            <button type="submit" class="btn btn-primary">Update Order</button>
                        </div>
                    </form>
                </div>
                @endcan

                <!-- PAYMENT HISTORY + ADD PAYMENT -->
                <div class="card-box">
                    <div class="card-title">
                        <div class="card-title-left"><i class="fa fa-history"></i> Payment History</div>
                        @can('orders.edit')
                        <button id="addPaymentBtn" class="btn btn-primary btn-sm no-print"
                            data-toggle="modal" data-target="#addPaymentModal">
                            <i class="fa fa-plus"></i> Add Payment
                        </button>
                        @endcan
                    </div>
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

                @include('include.footer')

            </div>
        </div>
    </div>

    @can('orders.edit')
    <!-- ADD PAYMENT MODAL -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius:0.8rem;">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-credit-card"></i> Add New Payment
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;">
                        &times;
                    </button>
                </div>

                <div class="modal-body p-4">
                    <div class="row mb-3">

                        <div class="col-md-6">
                            <div class="alert alert-light border">
                                <small class="text-muted">Net Amount</small>
                                <h5 class="mb-0 font-weight-bold text-dark" id="modal_net_amount">₹0.00</h5>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="alert alert-danger border">
                                <small class="text-muted">Due Amount</small>
                                <h5 class="mb-0 font-weight-bold" id="modal_due_amount">₹0.00</h5>
                            </div>
                        </div>

                    </div>

                    <form id="addPaymentForm" method="post">
                        @csrf
                        <input type="hidden" name="order_id" id="modalOrderId" value="{{ $orderId }}">

                        <div class="form-group">
                            <label class="font-weight-semibold">Payment Mode</label>
                            <select class="form-control" name="payment_mode" required>
                                <option value="">Select Payment Mode</option>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-semibold">Payment Amount</label>
                            <input type="number"
                                class="form-control"
                                name="paid_amount"
                                id="paid_amount"
                                placeholder="Enter Payment Amount"
                                min="1"
                                step="0.01"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-semibold">Payment Date</label>
                            <input type="datetime-local"
                                class="form-control"
                                id="payment_date"
                                name="payment_date"
                                value="<?= date('Y-m-d\TH:i'); ?>">
                        </div>

                        <button type="submit" class="btn btn-success btn-block mt-3">
                            <i class="fa fa-check"></i> Add Payment
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
    @endcan

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    @include('include.quick-task-modal')

    <script>
        const ORDER_ID = {{ (int) $orderId }};
        const esc = value => $('<div>').text(value ?? '').html();
        const CURRENCY_SYMBOL = { 'INR': '₹', 'EURO': '€', 'DOLLOR': '$' };

        function sym(cur) { return CURRENCY_SYMBOL[cur] || (cur ? cur + ' ' : ''); }

        function money(cur, v) {
            const n = Number(v || 0);
            return sym(cur) + n.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        function moneyBig(cur, v) {
            const n = Number(v || 0).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            return '<span class="rupee">' + esc(cur || '') + '</span> ' + n;
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

        function loadOrder() {
            $.ajax({
                url: "{{ route('orders.payments.data', $orderId) }}",
                type: "GET",
                success: function(res) {
                    if (!res || !res.order) {
                        $('#orderSubTitle').text('Order not found');
                        $('#paymentHistory').html('<tr><td colspan="3" class="empty-row">No data</td></tr>');
                        return;
                    }

                    const o = res.order;
                    const cur = o.currency;

                    $('#orderTitle').text(`Order #${o.order_number}`);
                    $('#orderSubTitle').text(`Invoice ${o.invoice_id ?? '-'} • ${o.invoice_date ?? '-'}`);

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
                    $('#netAmount').text(money(cur, o.net_amount));
                    $('#paidAmount').text(money(cur, o.paid_amount));
                    $('#dueAmount').text(money(cur, o.due_amount));

                    // progress
                    const total = Number(o.net_amount || 0);
                    const paid = Number(o.paid_amount || 0);
                    const pct = total > 0 ? Math.min(100, Math.round((paid / total) * 100)) : 0;
                    $('#paidBig').html(moneyBig(cur, paid));
                    $('#totalBig').html(moneyBig(cur, total));
                    $('#progressFill').css('width', pct + '%');
                    $('#paidPercent').text(pct + '% paid');
                    $('#dueChip').text('Due: ' + money(cur, o.due_amount));

                    // edit form (only rendered/prefillable when orders.edit gave us the fields)
                    $('#order_number').val(o.order_number ?? '');
                    $('#invoice_date').val(o.invoice_date ? o.invoice_date.substring(0, 10) : '');
                    $('#edit_sub_total').val(o.sub_total ?? '');
                    $('#edit_discount').val(o.discount ?? '');
                    $('#edit_gst').val(o.gst ?? '');
                    $('#edit_total_amount').val(o.total_amount ?? '');
                    $('#edit_paid_amount').val(o.paid_amount ?? '');
                    $('#edit_due_amount').val(o.due_amount ?? '');
                    $('#order_status').val(o.order_status ?? '');
                    $('#payment_status').val(o.payment_status ?? '');
                    $('#currency_edit').val(o.currency ?? '');

                    // add-payment modal
                    $('#modal_net_amount').text(money(cur, o.net_amount));
                    $('#modal_due_amount').text(money(cur, o.due_amount));

                    // due <= 0: fully paid, disable Add Payment (nothing left to collect).
                    const due = Number(o.due_amount || 0);
                    const paidAmt = Number(o.paid_amount || 0);
                    if (due <= 0) {
                        $('#addPaymentBtn').prop('disabled', true).removeClass('btn-primary').addClass('btn-secondary')
                            .attr('data-toggle', '').attr('data-target', '');
                    } else {
                        $('#addPaymentBtn').prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary')
                            .attr('data-toggle', 'modal').attr('data-target', '#addPaymentModal');
                    }
                    // Any payment recorded — even partial — gets a receipt. Previously this
                    // link only appeared once the order was 100% paid, leaving partial payers
                    // with no proof of payment at all.
                    if (paidAmt > 0) {
                        $('#invoiceBtn').css('display', 'inline-flex')
                            .html('<i class="fa fa-print"></i> ' + (due <= 0 ? 'View Invoice' : 'View Receipt'));
                    } else {
                        $('#invoiceBtn').css('display', 'none');
                    }

                    // payment history
                    const pays = res.data || [];
                    if (!pays.length) {
                        $('#paymentHistory').html('<tr><td colspan="3" class="empty-row">No payments recorded yet.</td></tr>');
                    } else {
                        let rows = '';
                        pays.forEach(p => {
                            rows += `
                                <tr>
                                    <td>${esc(p.payment_date ?? '-')}</td>
                                    <td><span class="pay-mode">${esc(pretty(p.payment_mode))}</span></td>
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
        }

        $(document).ready(function() {
            loadOrder();

            $('#invoiceBtn').attr('href', "{{ route('invoice.show', $orderId) }}");
        });

        $(document).on('submit', '#edit_sales_order', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: "{{ route('orders.update', $orderId) }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message ?? 'Order updated successfully');
                        loadOrder();
                    } else {
                        toastr.error(response.message ?? 'Something went wrong');
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error('Server error occurred');
                    }
                }
            });
        });

        $(document).on('submit', '#addPaymentForm', function(e) {
            e.preventDefault();

            let due_amount = document.getElementById('modal_due_amount').innerText.replace(/[^\d.]/g, '');
            due_amount = parseFloat(due_amount);
            let paid_amount = parseFloat(document.getElementById('paid_amount').value);

            if (paid_amount > due_amount) {
                toastr.error('Paid amount cannot be greater than the due amount');
                return;
            }

            let form = $(this);

            $.ajax({
                url: "{{ route('orders.payments.store') }}",
                type: "POST",
                data: form.serialize(),
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message || 'Payment added successfully');
                        $('#addPaymentModal').modal('hide');
                        form[0].reset();
                        $('#modalOrderId').val(ORDER_ID);
                        loadOrder();
                    } else {
                        toastr.error(response.message || 'Something went wrong');
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Server error occurred');
                    }
                }
            });
        });
    </script>

</body>

</html>
