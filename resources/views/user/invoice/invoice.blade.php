<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice | CRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        body {
            background: #e5e7eb;
        }

        .invoice-a4 {
            width: 210mm;
            background: #ffffff;
            margin: 30px auto;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
            font-size: 14px;
        }

        .invoice-header {
            border-bottom: 2px solid #111827;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .company-details {
            font-size: 14px;
            line-height: 1.6;
        }

        .section-title {
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 15px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 5px;
        }

        .info-block {
            line-height: 1.8;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .invoice-table th,
        .invoice-table td {
            border: 1px solid #d1d5db;
            padding: 10px;
            text-align: left;
        }

        .invoice-table th {
            background: #f3f4f6;
            font-weight: 600;
        }

        .summary-table {
            width: 100%;
            margin-top: 20px;
        }

        .summary-table td {
            padding: 6px 0;
        }

        .summary-total {
            font-weight: 700;
            font-size: 16px;
            border-top: 2px solid #111827;
            padding-top: 10px;
        }

        .invoice-footer {
            margin-top: 60px;
            border-top: 1px solid #d1d5db;
            padding-top: 15px;
            font-size: 13px;
            text-align: center;
        }

        .print-btn {
            margin-bottom: 20px;
            padding: 6px 18px;
            background: #111827;
            color: #fff;
            border: none;
            cursor: pointer;

        }

        @media print {

            body {
                background: #ffffff !important;
            }

            /* Hide CRM Layout */
            .navbar,
            .crm-navbar,
            header,
            .sidebar,
            .page-body-wrapper>nav,
            .container-scroller>nav,
            .container-fluid.page-body-wrapper>nav,
            footer,
            .print-btn {
                display: none !important;
                visibility: hidden !important;
            }

            /* Remove spacing caused by layout */
            .container-scroller,
            .container-fluid,
            .page-body-wrapper,
            .content-wrapper {
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Make invoice full page */
            .invoice-a4 {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                min-height: auto !important;
            }
        }



        .print-btn {
            padding: 8px 22px;
            border-radius: 30px;
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .print-btn:hover {
            opacity: 0.9;
        }

        .invoice-action-bar {
            width: 210mm;
            margin: 30px auto 0 auto;
            text-align: right;
        }
    </style>
</head>

<body>

    <div class="container-scroller">

        @include('user.include.header')

        <div class="container-fluid page-body-wrapper">

            @include('user.include.sidebar')

            <div class="content-wrapper">

                <div class="invoice-a4">

                    <!-- Header -->
                    <div class="invoice-header d-flex justify-content-between">

                        <div class="company-details">
                            <strong style="font-size:18px;">CRM SYSTEM</strong><br>
                            Delhi, India<br>
                            support@crm.com
                        </div>

                        <div style="text-align:right;">
                            <div class="invoice-title">INVOICE</div>
                            Invoice ID: {{ $order->invoice_id }} <br>
                            Invoice Date: {{ \Carbon\Carbon::parse($invoiceDate)->format('d M Y H:i') }} <br>
                            Order #: {{ $order->order_number }}
                        </div>

                    </div>

                    <!-- Billing + Order -->
                    <div class="row mb-4">

                        <div class="col-md-6">
                            <div class="section-title">Bill To</div>
                            <div class="info-block">
                                {{ $order->lead->name ?? '-' }} <br>
                                {{ $order->lead->company_name ?? '-' }} <br>
                                Phone: {{ $order->lead->phone ?? '-' }} <br>
                                Email: {{ $order->lead->email ?? '-' }} <br>
                                GST: {{ $order->lead->gst_no ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="section-title">Order Details</div>
                            <div class="info-block">
                                Order Number: {{ $order->order_number }} <br>
                                Project ID: {{ $order->project_id }} <br>
                                Payment Terms: {{ ucfirst($order->payment_terms) }} <br>
                                Order Status: {{ ucfirst($order->order_status) }}
                            </div>
                        </div>

                    </div>

                    <!-- Project Info Table -->
                    <div class="section-title">Project Information</div>
                    <table class="invoice-table">
                        <tr>
                            <th>Project Name</th>
                            <th>Technology</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                        </tr>
                        <tr>
                            <td>{{ optional($order->project)->project_name ?? '-' }}</td>
                            <td>{{ optional($order->project)->tech_stack ?? '-' }}</td>
                            <td>
                                {{ optional($order->project)->expected_start_date 
                                ? \Carbon\Carbon::parse($order->project->expected_start_date)->format('d M Y') 
                                : '-' }}
                            </td>
                            <td>
                                {{ optional($order->project)->actual_delivery_date
                                ? \Carbon\Carbon::parse($order->project->actual_delivery_date)->format('d M Y') 
                                : '-' }}
                            </td>
                        </tr>
                    </table>

                    <!-- Financial Summary -->
                    <div class="section-title mt-4">Billing Summary</div>

                    <table class="summary-table">
                        <tr>
                            <td>Sub Total</td>
                            <td class="text-right">₹{{ number_format($order->sub_total, 2) }}</td>
                        </tr>

                        <tr>
                            <td>Discount</td>
                            <td class="text-right text-danger">
                                - ₹{{ number_format($order->discount, 2) }}
                            </td>
                        </tr>

                        <tr>
                            <td>GST ({{ $order->gst }}%)</td>
                            <td class="text-right">
                                ₹{{ number_format(($order->sub_total - $order->discount) * $order->gst / 100, 2) }}
                            </td>
                        </tr>

                        <tr class="summary-total">
                            <td>Total Amount</td>
                            <td class="text-right">
                                ₹{{ number_format($order->net_amount, 2) }}
                            </td>
                        </tr>

                        <tr>
                            <td>Paid Amount</td>
                            <td class="text-right text-success">
                                ₹{{ number_format($order->paid_amount, 2) }}
                            </td>
                        </tr>

                        <tr>
                            <td>Due Amount</td>
                            <td class="text-right text-danger">
                                ₹{{ number_format($order->due_amount, 2) }}
                            </td>
                        </tr>
                    </table>

                    <div class="d-flex justify-content-end mt-5">
                        <button onclick="window.print()" class="print-btn">
                            <i class="fa fa-print"></i> Print Invoice
                        </button>
                    </div>

                </div>

                @include('user.include.footer')

            </div>
        </div>
    </div>

</body>

</html>