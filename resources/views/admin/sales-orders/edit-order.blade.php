<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Order | CRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />


    <style>
        .order-card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .section-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #1f2937;
        }

        label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
        }

        /* ===============================
   EDIT ORDER HEADER
================================ */
        .edit-order-header {
            background: linear-gradient(135deg, #0d6efd, #00c6ff);
            padding: 20px 24px;
            border-radius: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            color: #ffffff;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .edit-order-header h3 {
            font-weight: 700;
            font-size: 20px;
        }

        .back-btn {
            border-radius: 30px;
            padding: 6px 18px;
            font-weight: 600;
            transition: 0.3s ease;
        }

        .back-btn:hover {
            background: #ffffff;
            color: #0d6efd;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .edit-order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .header-right {
                width: 100%;
            }

            .back-btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <div class="container-scroller">

        @include('admin.include.header')

        <div class="container-fluid page-body-wrapper">

            @include('admin.include.sidebar')

            <div class="content-wrapper">

                <!-- 🔷 PAGE HEADER -->
                <div class="edit-order-header mb-4">

                    <div class="header-left">
                        <div class="header-icon">
                            <i class="fa fa-file-text"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">Edit Sales Order</h3>
                            <small class="text-light">Update order and financial details</small>
                        </div>
                    </div>

                    <div class="header-right">
                        <a href="{{ route('admin.sales_orders') }}" class="btn btn-light btn-sm back-btn">
                            <i class="fa fa-arrow-left mr-1"></i> Back to Orders
                        </a>
                    </div>

                </div>

                <!-- FORM CARD -->
                <div class="card order-card">
                    <div class="card-body">

                        <form method="POST" id="edit_sales_order">
                            @csrf

                            <input type="hidden" id="id" name="id">

                            <!-- ================= ORDER INFO ================= -->
                            <div class="section-title">Order Information</div>
                            <div class="row">

                                <div class="col-md-6 form-group">
                                    <label>Order Number</label>
                                    <input type="text" id="order_number" name="order_number" class="form-control">
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Invoice Date</label>
                                    <input type="date" id="invoice_date" name="invoice_date" class="form-control">
                                </div>


                            </div>

                            <!-- ================= AMOUNTS ================= -->
                            <div class="section-title mt-3">Financial Details</div>
                            <div class="row">

                                <div class="col-md-4 form-group">
                                    <label>Sub Total</label>
                                    <input type="number" step="0.01" id="sub_total" name="sub_total" class="form-control">
                                </div>

                                <div class="col-md-4 form-group">
                                    <label>Discount</label>
                                    <input type="number" step="0.01" id="discount" name="discount" class="form-control">
                                </div>

                                <div class="col-md-4 form-group">
                                    <label>GST (%)</label>
                                    <input type="number" id="gst" name="gst" class="form-control">
                                </div>

                                <div class="col-md-4 form-group">
                                    <label>Total Amount</label>
                                    <input type="number" step="0.01" id="total_amount" name="total_amount" class="form-control">
                                </div>

                                <div class="col-md-4 form-group">
                                    <label>Paid Amount</label>
                                    <input type="number" step="0.01" id="paid_amount" name="paid_amount" class="form-control">
                                </div>

                                <div class="col-md-4 form-group">
                                    <label>Due Amount</label>
                                    <input type="number" step="0.01" id="due_amount" name="due_amount" class="form-control">
                                </div>

                            </div>

                            <!-- ================= STATUS ================= -->
                            <div class="section-title mt-3">Status & Payment</div>
                            <div class="row">

                                <div class="col-md-4 form-group">
                                    <label>Order Status</label>
                                    <select id="order_status" name="order_status" class="form-control">
                                        <option value="new">New</option>
                                        <option value="approved">Approved</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="on_hold">On Hold</option>
                                        <option value="completed">Completed</option>
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
                                    <select id="currency" name="currency" class="form-control">
                                        <option value="INR">INR</option>
                                        <option value="EURO">EURO</option>
                                        <option value="DOLLOR">DOLLOR</option>
                                    </select>
                                </div>

                            </div>

                            <!-- ACTION -->
                            <div class="text-right mt-4">
                                <button type="submit" class="btn btn-primary">Update Order</button>
                            </div>

                        </form>

                    </div>
                </div>

                @include('admin.include.footer')

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>

    <!-- DataTables -->
    <script src="{{ asset('vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


    <!-- ================= JS ================= -->
    <script>
        $(document).ready(function() {

            // 1️⃣ Get ID from URL (?1)
            const query = window.location.search; // "?1"

            if (!query) {
                alert('Order ID missing');
                return;
            }

            const orderId = query.replace('?', '');

            // 2️⃣ AJAX call to fetch order data
            $.ajax({
                url: "{{ route('admin.get_order_by_id') }}",
                type: "GET",
                dataType: "json",
                data: {
                    id: orderId
                },

                success: function(res) {

                    if (!res.status || !res.data) {
                        alert('Order not found');
                        return;
                    }

                    const o = res.data;

                    // 3️⃣ Explicit field-by-field assignment
                    $('#id').val(o.id ?? '');
                    $('#order_number').val(o.order_number ?? '');
                    $('#invoice_date').val(o.invoice_date ?? '');
                    $('#sub_total').val(o.sub_total ?? '');
                    $('#discount').val(o.discount ?? '');
                    $('#gst').val(o.gst ?? '');
                    $('#total_amount').val(o.total_amount ?? '');
                    $('#paid_amount').val(o.paid_amount ?? '');
                    $('#due_amount').val(o.due_amount ?? '');
                    $('#order_status').val(o.order_status ?? '');
                    $('#payment_status').val(o.payment_status ?? '');
                    $('#payment_mode').val(o.payment_mode ?? '');
                    $('#currency').val(o.currency ?? '');
                },

                error: function(xhr) {
                    toastr.error(xhr);
                }
            });

        });


        $(document).on('submit', '#edit_sales_order', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: "{{ route('admin.update_sales_order') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {

                    $('.btn-primary').prop('disabled', false).text('Update Order');

                    if (response.status) {
                        toastr.success(response.message ?? 'Order updated successfully');

                        // Optional redirect
                        setTimeout(() => {
                            window.location.href = "{{ route('admin.sales_orders') }}";
                        }, 1200);

                    } else {
                        toastr.error(response.message ?? 'Something went wrong');
                    }
                },

                error: function(xhr) {
                    $('.btn-primary').prop('disabled', false).text('Update Order');

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
    </script>



</body>

</html>