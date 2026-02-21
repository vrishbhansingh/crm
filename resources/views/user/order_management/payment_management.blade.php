<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management | CRM</title>

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .crm-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            padding: 22px 26px;
            border-radius: 14px;
            margin-bottom: 25px;
            color: #fff;
        }

        .crm-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .summary-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: 0.3s;
        }

        .summary-card:hover {
            transform: translateY(-4px);
        }

        .summary-title {
            font-size: 13px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
        }

        .summary-value {
            font-size: 24px;
            font-weight: 700;
            margin-top: 6px;
        }

        .badge-mode {
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
        }

        .mode-upi {
            background: #e3f2fd;
            color: #007bff;
        }

        .mode-cash {
            background: #e8f5e9;
            color: #28a745;
        }

        .mode-bank_transfer {
            background: #fff3cd;
            color: #856404;
        }

        .mode-cheque {
            background: #f8d7da;
            color: #721c24;
        }

        .table thead {
            background: #f8f9fc;
        }

        .search-box {
            max-width: 250px;
        }

        .modal-header {
            background: linear-gradient(135deg, #4e73df, #5a54c4);
            border-top-left-radius: 0.8rem;
            border-top-right-radius: 0.8rem;
        }

        .modal-close-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 2px solid #ffffff;
            background: transparent;
            color: #ffffff;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-close-btn:hover {
            background: #ffffff;
            color: #4e73df;
        }

        .order-pro-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        }

        .status-badge {
            background: #eef2ff;
            color: #4f46e5;
            font-weight: 600;
        }

        .info-card {
            background: #f8fafc;
            padding: 16px;
            border-radius: 12px;
        }

        .info-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }

        .money-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
            transition: 0.3s ease;
        }

        .money-card:hover {
            transform: translateY(-4px);
        }

        .money-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 600;
        }

        .money-value {
            font-size: 22px;
            margin-top: 6px;
            font-weight: 700;
            color: #0f172a;
        }

        .highlight-card {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
        }

        .highlight-card .money-value,
        .money-label-net {
            color: white;
        }

        .bottom-info {
            background: #f8fafc;
            padding: 16px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .card-header:first-child {
            border-radius: calc(1.25rem - 1px) calc(1.25rem - 1px) 0 0 !important;
        }

        .card-header {
            border: 1px solid #e3e3e3;
        }

        .invoice-btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 30px;
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            color: #ffffff;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            transition: 0.3s ease;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .invoice-btn-modern:hover {
            transform: translateY(-2px);
            color: #ffffff;
        }
    </style>
</head>

<body>

    <div class="container-scroller">
        @include('user.include.header')

        <div class="container-fluid page-body-wrapper">
            @include('user.include.sidebar')

            <div class="content-wrapper">

                <!-- HEADER -->
                <div class="crm-header">
                    <h2 class="crm-title">
                        <i class="fa fa-credit-card"></i> Payment Management
                    </h2>

                    <a href="{{route('user.order_management')}}" class="btn btn-light btn-sm">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>


                <!-- ORDER DETAILS SUMMARY -->
                <!-- ORDER DETAILS SECTION -->
                <div class="card order-pro-card mb-4">
                    <div class="card-body p-4">

                        <!-- Top Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="mb-1 font-weight-bold text-dark">
                                    Order Summary
                                </h4>
                                <small class="text-muted">
                                    <span id="od_order_number">-</span> •
                                    <span id="od_invoice_date">-</span>
                                </small>
                            </div>

                            <div>
                                <a href="#"
                                    id="invoiceBtn"
                                    class="invoice-btn-modern"
                                    style="display: none;">
                                    <i class="fa fa-print"></i>
                                    <span>Print Invoice</span>
                                </a>
                                <span class="badge badge-pill px-4 py-2 status-badge"
                                    id="od_order_status">-</span>
                            </div>
                        </div>

                        <!-- Info Grid -->
                        <div class="row mb-4">

                            <div class="col-md-4 mb-3">
                                <div class="info-card">
                                    <div class="info-label">Invoice ID</div>
                                    <div class="info-value" id="od_invoice_id">-</div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="info-card">
                                    <div class="info-label">Project</div>
                                    <div class="info-value" id="od_project_id">-</div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="info-card">
                                    <div class="info-label">Assigned User</div>
                                    <div class="info-value" id="od_user_id">-</div>
                                </div>
                            </div>

                        </div>

                        <!-- Financial Cards -->
                        <div class="row text-center">

                            <div class="col-md-3 mb-3">
                                <div class="money-card">
                                    <div class="money-label">Sub Total</div>
                                    <div class="money-value" id="od_sub_total">₹0.00</div>
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <div class="money-card">
                                    <div class="money-label">Discount</div>
                                    <div class="money-value text-danger"
                                        id="od_discount">₹0.00</div>
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <div class="money-card">
                                    <div class="money-label">GST</div>
                                    <div class="money-value" id="od_gst">0%</div>
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <div class="money-card highlight-card">
                                    <div class="money-label money-label-net">Net Amount</div>
                                    <div class="money-value font-weight-bold"
                                        id="od_net_amount">₹0.00</div>
                                </div>
                            </div>

                        </div>

                        <!-- Bottom Info -->
                        <div class="row mt-3">

                            <div class="col-md-4 mb-3">
                                <div class="bottom-info">
                                    <span>Total Amount</span>
                                    <strong id="od_total_amount">₹0.00</strong>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="bottom-info text-danger">
                                    <span>Due Amount</span>
                                    <strong id="od_due_amount">₹0.00</strong>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="bottom-info">
                                    <span>Payment Status</span>
                                    <strong id="od_payment_status">-</strong>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>


                <!-- SUMMARY CARDS -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-title">Total Payments</div>
                            <div class="summary-value" id="totalPayments">0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-title">Total Paid Amount</div>
                            <div class="summary-value text-success" id="totalAmount">₹0.00</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-title">Total Due</div>
                            <div class="summary-value text-primary" id="totalDue">0</div>
                        </div>
                    </div>
                </div>

                <!-- PAYMENT TABLE -->
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fa fa-list"></i> Payment List
                        </h5>

                        <button id="addPaymentBtn"
                            class="btn btn-primary btn-sm"
                            data-toggle="modal"
                            data-target="#addPaymentModal">
                            <i class="fa fa-plus"></i> Add Payment
                        </button>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">

                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Order Number</th>
                                        <th class="text-center">Payment Mode</th>
                                        <th class="text-center">Payment Date</th>
                                        <th class="text-center">Paid Amount</th>
                                        <!-- <th class="text-center">Action</th> -->
                                    </tr>
                                </thead>

                                <tbody id="paymentTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No Data Loaded
                                        </td>
                                    </tr>
                                </tbody>

                            </table>

                        </div>
                    </div>
                </div>

                @include('user.include.footer')

            </div>
        </div>
    </div>


    <!-- ADD PAYMENT MODAL -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius:0.8rem;">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-credit-card"></i> Add New Payment
                    </h5>
                    <button type="button" class="modal-close-btn" data-dismiss="modal">
                        &times;
                    </button>
                </div>

                <div class="modal-body p-4">
                    <div class="row mb-3">

                        <div class="col-md-6">
                            <div class="alert alert-light border">
                                <small class="text-muted">Net Amount</small>
                                <h5 class="mb-0 font-weight-bold text-dark"
                                    id="modal_net_amount">₹0.00</h5>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="alert alert-danger border">
                                <small class="text-muted">Due Amount</small>
                                <h5 class="mb-0 font-weight-bold"
                                    id="modal_due_amount">₹0.00</h5>
                            </div>
                        </div>

                    </div>

                    <form id="addPaymentForm" method="post">
                        @csrf
                        <!-- Payment Mode -->
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

                        <!-- Payment Amount -->
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

                        <!-- Payment Date -->
                        <div class="form-group">
                            <label class="font-weight-semibold">Payment Date</label>
                            <input type="datetime-local"
                                class="form-control"
                                id="payment_date"
                                name="payment_date"
                                value="<?= date('Y-m-d\TH:i'); ?>">

                            <input type="hidden" name="order_id" id="modalOrderId">

                            <button type="submit" class="btn btn-success btn-block mt-3">
                                <i class="fa fa-check"></i> Add Payment
                            </button>

                    </form>

                </div>
            </div>
        </div>
    </div>




    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>


    <script>
        $(document).ready(function() {
            loadPayments();

            $("#refreshPayments").click(function() {
                loadPayments();
            });


        });

        function loadPayments() {
            let url = window.location.pathname;
            let segments = url.split('/');
            let id = segments[segments.length - 1];
            $.ajax({
                url: "{{route('user.get_payment_data')}}",
                type: "GET",
                data: {
                    'id': id
                },
                success: function(response) {

                    let payments = response.data; // ✅ now it's array
                    let tableHtml = "";
                    let totalAmount = 0;

                    if (!payments || payments.length === 0) {
                        tableHtml = `
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No Payment Found
                            </td>
                        </tr>
                    `;
                        $("#paymentTableBody").html(tableHtml);
                        return;
                    }

                    payments.forEach((payment, index) => {

                        totalAmount += parseFloat(payment.paid_amount);

                        tableHtml += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td class="text-center">${payment.order_number}</td>
                            <td class="text-center">
                                <span class="badge-mode mode-${payment.payment_mode}">
                                    ${formatText(payment.payment_mode)}
                                </span>
                            </td>
                            <td class="text-center">
                                ${payment.payment_date ?? '-'}
                            </td>
                            <td class="text-center text-success font-weight-bold">
                                ₹${parseFloat(payment.paid_amount).toFixed(2)}
                            </td>
                            
                        </tr>
                    `;
                    });

                    // 🔥 ORDER DETAILS (from first row)
                    if (payments.length > 0) {

                        let order = payments[0]; // order data comes with every row

                        $("#od_order_number").text(order.order_number ?? '-');
                        $("#od_invoice_id").text(order.invoice_id ?? '-');
                        $("#od_invoice_date").text(order.invoice_date ?? '-');
                        $("#od_currency").text(order.currency ?? '-');
                        $("#od_lead_id").text(order.lead_number ?? '-');
                        $("#od_project_id").text(order.project_name ?? '-');
                        $("#od_user_id").text(order.user_name ?? '-');
                        $("#od_order_status").text(formatText(order.order_status ?? '-'));

                        $("#od_sub_total").text("₹" + parseFloat(order.sub_total ?? 0).toFixed(2));
                        $("#od_discount").text("₹" + parseFloat(order.discount ?? 0).toFixed(2));
                        $("#od_gst").text((order.gst ?? 0) + "%");
                        $("#od_total_amount").text("₹" + parseFloat(order.total_amount ?? 0).toFixed(2));
                        $("#od_net_amount").text("₹" + parseFloat(order.net_amount ?? 0).toFixed(2));
                        $("#od_due_amount").text("₹" + parseFloat(order.due_amount ?? 0).toFixed(2));
                        $("#od_payment_terms").text(formatText(order.payment_terms ?? '-'));
                        $("#od_payment_status").text(formatText(order.payment_status ?? '-'));


                        $("#modal_due_amount").text("₹" + parseFloat(order.due_amount ?? 0).toFixed(2));
                        $("#modal_net_amount").text("₹" + parseFloat(order.net_amount ?? 0).toFixed(2));
                    }
                    $("#paymentTableBody").html(tableHtml);

                    // ✅ Summary Section
                    $("#totalPayments").text(payments.length);
                    $("#totalAmount").text("₹" + totalAmount.toFixed(2));

                    // since due_amount same for all rows
                    let dueAmount = parseFloat(payments[0].due_amount);

                    $("#totalDue").text("₹" + dueAmount.toFixed(2));

                    // 🔥 Disable button if due is 0
                    if (dueAmount <= 0) {
                        $("#addPaymentBtn")
                            .prop("disabled", true)
                            .removeClass("btn-primary")
                            .addClass("btn-secondary")
                            .attr("data-toggle", "") // prevent modal opening
                            .attr("data-target", "");
                        $("#invoiceBtn").css("display", "inline-flex");
                    } else {
                        $("#addPaymentBtn")
                            .prop("disabled", false)
                            .removeClass("btn-secondary")
                            .addClass("btn-primary")
                            .attr("data-toggle", "modal")
                            .attr("data-target", "#addPaymentModal");
                        $("#invoiceBtn").css("display", "none");
                    }
                },

                error: function() {
                    $("#paymentTableBody").html(`
                    <tr>
                        <td colspan="6" class="text-center text-danger py-4">
                            Failed to load payments
                        </td>
                    </tr>
                `);
                }
            });
        }


        $(document).on('submit', '#addPaymentForm', function(e) {

            e.preventDefault(); // stop normal form submit

            let due_amount = document
                .getElementById("modal_due_amount")
                .innerText
                .replace(/[^\d.]/g, ""); // remove ₹ and anything else

            due_amount = parseFloat(due_amount);
            paid_amount = document.getElementById("paid_amount").value;

            if (due_amount < paid_amount) {
                toastr.error('Paid amount is equal to due anount');
                return;
            }

            let form = $(this);

            // get order id from URL
            let segments = window.location.pathname.split('/').filter(Boolean);
            let order_id = segments.pop();


            // convert form data to object
            let formData = form.serializeArray();
            formData.push({
                name: "order_id",
                value: order_id
            });

            $.ajax({
                url: "{{ route('user.add_order_payment') }}",
                type: "POST",
                data: $.param(formData),
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {

                    if (response.status) {

                        toastr.success("Payment added successfully");

                        $('#addPaymentModal').modal('hide');

                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        toastr.error(response.message || "Something went wrong");
                    }
                },
                error: function(xhr) {

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error("Server error occurred");
                    }
                }
            });


        });

        function formatText(text) {
            return text
                .replace(/[^a-zA-Z0-9]+/g, ' ') // replace special chars with space
                .trim() // remove extra spaces
                .toLowerCase() // make all lowercase first
                .replace(/\b\w/g, function(char) {
                    return char.toUpperCase(); // capitalize first letter of each word
                });
        }

        $(document).on('click', "#invoiceBtn", function() {
            let url = window.location.pathname;
            let segments = url.split('/');
            let id = segments[segments.length - 1];

            let invoiceUrl = "{{ route('user.invoice', ':id') }}";
            invoiceUrl = invoiceUrl.replace(':id', id);

            window.location.href = invoiceUrl;
        })
    </script>

</body>

</html>