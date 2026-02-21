<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Payment Management | CRM</title>

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        .crm-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #007bff, #00c6ff);
            padding: 22px 26px;
            border-radius: 14px;
            margin-bottom: 28px;
            color: #fff;
        }

        .crm-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .crm-summary-row {
            margin-bottom: 28px;
        }

        .crm-summary-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .crm-summary-card::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 4px;
            bottom: 0;
            left: 0;
            background: #007bff;
        }

        .crm-summary-card.success::after {
            background: #28a745;
        }

        .crm-summary-card.warning::after {
            background: #ffc107;
        }

        .crm-summary-card.info::after {
            background: #17a2b8;
        }

        .crm-summary-card .label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
        }

        .crm-summary-card .value {
            font-size: 26px;
            font-weight: 700;
            margin-top: 6px;
        }

        .badge-paid {
            background: #28a745;
            color: #fff;
        }

        .badge-partial {
            background: #ffc107;
            color: #000;
        }

        .badge-pending {
            background: #dc3545;
            color: #fff;
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
                    <div>
                        <h2 class="crm-title">
                            <i class="fa fa-credit-card"></i> <span> Order Payment Management </span>
                        </h2>
                    </div>

                    <button class="btn btn-light btn-sm" id="refreshOrders">
                        <i class="fa fa-refresh"></i> Refresh
                    </button>
                </div>


                <!-- ORDER TABLE -->
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fa fa-list"></i> Order Payment List
                        </h5>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">

                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Order No</th>
                                        <th class="text-center">Net Amount</th>
                                        <th class="text-center">Paid Amount</th>
                                        <th class="text-center">Due Amount</th>
                                        <th class="text-center">Payment Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody id="orderTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
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

    <!-- jQuery (ONLY ONCE) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS (REQUIRED for modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <script>

        
        $(document).ready(function() {
            leadOrder();
            $("#refreshOrders").click(function() {
                leadOrder();
            });
        })

        function leadOrder() {

            $.ajax({
                url: "{{route('user.get_order_payments')}}", // 🔥 change this to your route
                type: "GET",
                success: function(response) {
                    let orders = response.data; // adjust if needed
                    let tableHtml = "";

                    let total = orders.length;
                    let paid = 0;
                    let partial = 0;
                    let pending = 0;

                    if (orders.length === 0) {
                        tableHtml = `
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            No Orders Found
                        </td>
                    </tr>
                `;
                    }

                    orders.forEach((order, index) => {


                        tableHtml += `
                    <tr>
                        <td  class="text-center">${index + 1}</td>
                        <td  class="text-center">${order.order_number}</td>
                        <td  class="text-center">₹${parseFloat(order.net_amount).toFixed(2)}</td>
                        <td  class="text-center text-success">
                            ₹${parseFloat(order.paid_amount).toFixed(2)}
                        </td>
                        <td  class="text-center text-danger">
                            ₹${parseFloat(order.due_amount).toFixed(2)}
                        </td>
                        <td  class="text-center">
                            <span class="badge">
                                ${order.payment_status.toUpperCase()}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge">
                                ${order.action}
                            </span>
                        </td>
                    </tr>
                `;
                    });

                    $("#orderTableBody").html(tableHtml);

                    // Update summary cards
                    $("#totalOrders").text(total);
                    $("#paidOrders").text(paid);
                    $("#partialOrders").text(partial);
                    $("#pendingOrders").text(pending);
                },
                error: function() {
                    $("#orderTableBody").html(`
                <tr>
                    <td colspan="8" class="text-center text-danger py-4">
                        Failed to load orders
                    </td>
                </tr>
            `);
                }
            });
        }
    </script>
</body>

</html>