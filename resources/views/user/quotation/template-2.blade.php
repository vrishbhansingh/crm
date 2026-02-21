<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Standard Quotation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">


    <style>
        body {
            background: #f4f6fb;
            font-family: 'Segoe UI', sans-serif;
            font-size: 14px;
            color: #333;
        }

        .content-wrapper {
            padding: 30px;
            background: #f8f9fc;
        }

        .quotation-container {
            min-width: 1100px;
            margin: auto;
        }

        .quotation-card {
            background: #fff;
            border-radius: 14px;
            padding: 30px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, .08);
        }

        /* HEADER */
        .quote-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .company-name {
            font-size: 26px;
            font-weight: 700;
            color: #0d6efd;
        }

        .quote-meta {
            text-align: right;
            font-size: 13px;
            line-height: 1.7;
        }

        /* INFO GRID */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            line-height: 1.6;
        }

        .info-box h6 {
            font-weight: 600;
            margin-bottom: 10px;
            color: #0d6efd;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th {
            background: #f1f5ff;
            padding: 12px;
            border: 1px solid #ddd;
            font-weight: 600;
            text-align: left;
        }

        table td {
            padding: 12px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        /* TOTAL BOX */
        .total-box {
            display: flex;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .total-card {
            background: #0d6efd;
            color: #fff;
            padding: 18px 30px;
            border-radius: 12px;
            text-align: right;
        }

        .total-card p {
            margin: 0;
            font-size: 14px;
        }

        .total-card h3 {
            margin-top: 6px;
            font-size: 22px;
        }

        /* TERMS */
        .terms {
            margin-top: 30px;
            font-size: 13px;
            line-height: 1.7;
        }

        /* SIGNATURE */
        .signature {
            margin-top: 35px;
            font-size: 14px;
        }

        @media print {

            header,
            nav,
            .sidebar {
                display: none !important;
            }

            .content-wrapper {
                padding: 0 !important;
            }

            body {
                background: #fff;
            }
        }

        /* ===== FINANCIAL REDESIGN ===== */

        .finance-wrapper {
            margin-top: 35px;
            max-width: 420px;
            margin-left: auto;
            /* right aligned like real invoices */
        }

        .finance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .finance-table td {
            padding: 10px 0;
            text-align: center;

        }

        .finance-table .label {
            background: #f8fafc;
            color: #374151;
            font-weight: 500;
            text-align: center;

        }

        .finance-table .amount {
            text-align: right;
            font-weight: 600;
            color: #111827;
            text-align: center;

        }

        .finance-table .negative {
            color: #b91c1c;
        }

        .divider {
            background-color: aliceblue;
        }

        .finance-table .divider td {
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 0;
            text-align: center;

        }

        .finance-table .grand-row .label {
            font-size: 15px;
            font-weight: 700;
            text-align: center;
        }

        .finance-table .grand-row .amount {
            font-size: 16px;
            font-weight: 700;
            text-align: center;

        }

        .finance-table.secondary {
            margin-top: 12px;
        }

        .finance-table .due {
            color: #92400e;
            font-weight: 700;
        }

        .payment-meta {
            margin-top: 22px;
            padding-top: 12px;
            border-top: 1px dashed #d1d5db;
            font-size: 13px;
            color: #374151;
        }

        .payment-meta div {
            margin-bottom: 6px;
        }

        /* ===== FINANCE FOOTER ===== */

        .finance-footer {
            margin-top: 25px;
            padding-top: 18px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
        }

        .payment-info {
            font-size: 13px;
            color: #374151;
            line-height: 1.8;
        }

        .payment-info strong {
            font-weight: 600;
        }

        .grand-total-box {
            text-align: right;
            background: #0079ff;
            padding: 16px 22px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }

        .grand-total-box span {
            font-size: 12px;
            color: #ffffff;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .grand-total-box h2 {
            margin-top: 6px;
            font-size: 22px;
            font-weight: 700;
            color: #fdfdff;
        }

        .back-btn {
            border-radius: 8px;
            font-size: 13px;
            padding: 6px 14px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            color: #374151;
            transition: all .2s ease;
        }

        .back-btn:hover {
            background: #f1f5f9;
            color: #111827;
        }

        /* Generate PDF Button */
        .btn-generate-pdf {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: #fff;
            font-weight: 500;
            border-radius: 10px;
            padding: 7px 16px;
            border: none;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 6px 18px rgba(34, 197, 94, 0.35);
            transition: all 0.2s ease;
        }

        .btn-generate-pdf i {
            font-size: 14px;
        }

        /* Hover */
        .btn-generate-pdf:hover {
            background: linear-gradient(135deg, #15803d, #16a34a);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(34, 197, 94, 0.45);
        }

        /* Active */
        .btn-generate-pdf:active {
            transform: scale(0.97);
        }

        /* Mobile Responsive */
        @media (max-width: 576px) {
            .action-bar {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }

            .btn-generate-pdf,
            .back-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <div class="container-scroller">

        @include('user.include.header')

        <div class="container-fluid page-body-wrapper">

            @include('user.include.sidebar')

            <div class="content-wrapper">

                <div class="quotation-container mb-3">

                    <div class="mb-3" style="display: flex; justify-content: end; gap:10px;">
                        <button class="btn btn-light btn-sm back-btn" onclick="goBackToLead()">
                            <i class="fa fa-arrow-left mr-1"></i> Back to View
                        </button>

                        <button type="button"
                            onclick="generatePDF()"
                            class="btn btn-sm btn-generate-pdf">
                            <i class="fa fa-file-pdf-o mr-1"></i> Generate & Save PDF
                        </button>
                    </div>
                    <div class="quotation-card" id="invoice">

                        <!-- HEADER -->
                        <div class="quote-header">
                            <div class="d-flex flex-column justify-content-center" style="gap:3px;">
                                <h3>{{ $company_details->company_name }}</h3>

                                <div class="company-info">
                                    <small>
                                        {{ $company_details->address }},
                                        {{ $company_details->pincode }},
                                        {{ $company_details->city }}
                                    </small>
                                    <br>
                                    <small>
                                        <strong>GST:</strong> {{ $company_details->gst_number }}
                                    </small>
                                </div>
                            </div>


                            <div class="quote-meta">
                                <strong>Quotation No:</strong> SQ-{{ $lead->lead_number ?? '0001' }}<br>
                                <strong>Date:</strong> {{ now()->format('d M Y') }}<br>
                                <strong>Lead Type:</strong> {{ ucfirst($lead->lead_type ?? 'inquiry') }}
                            </div>
                        </div>

                        <!-- INFO -->
                        <div class="info-grid">
                            <div class="info-box">
                                <h6>Customer Details</h6>
                                <strong>Name:</strong> {{ $lead->name }}<br>
                                <strong>Company:</strong> {{ $lead->company_name }}<br>
                                <strong>Email:</strong> {{ $lead->email }}<br>
                                <strong>Phone:</strong> {{ $lead->phone }}
                            </div>

                            <div class="info-box">
                                <h6>Additional Info</h6>
                                <strong>GST No:</strong> {{ $lead->gst_no ?? 'N/A' }}<br>
                                <strong>Location:</strong>
                                {{ $lead->city }}, {{ $lead->state }}, {{ $lead->country }}<br>
                                <strong>Priority:</strong> {{ ucfirst($lead->priority) }}
                            </div>
                        </div>

                        <!-- TABLE -->
                        <table>
                            <thead>
                                <tr>
                                    <th>Product / Service</th>
                                    <th>Description</th>
                                    <th width="160">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $lead->product ?? $lead->service }}</td>
                                    <td>{{ $lead->requirement }}</td>
                                    <td>₹{{ number_format($lead->budget, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>


                        <!-- FINANCIAL SUMMARY -->
                        <!-- FINANCIAL BREAKDOWN -->
                        <div class="finance-wrapper">

                            <table class="finance-table">
                                <tr>
                                    <td class="label">Sub Total</td>
                                    <td class="amount">{{ $order->currency }} {{ number_format($order->sub_total, 2) }}</td>
                                </tr>

                                <tr>
                                    <td class="label">Discount</td>
                                    <td class="amount negative">
                                        - {{ $order->currency }} {{ number_format($order->discount, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label">GST ({{ $order->gst }}%)</td>
                                    <td class="amount">
                                        {{ $order->currency }}
                                        {{ number_format(($order->sub_total - $order->discount) * $order->gst / 100, 2) }}
                                    </td>
                                </tr>

                                <tr class="divider">
                                    <td colspan="2"></td>
                                </tr>

                                <tr class="grand-row">
                                    <td class="label">Total Amount</td>
                                    <td class="amount">
                                        {{ $order->currency }} {{ number_format($order->total_amount, 2) }}
                                    </td>
                                </tr>
                            </table>

                            <table class="finance-table secondary">
                                <tr>
                                    <td class="label">Paid Amount</td>
                                    <td class="amount">
                                        {{ $order->currency }} {{ number_format($order->paid_amount, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label">Due Amount</td>
                                    <td class="amount due">
                                        {{ $order->currency }} {{ number_format($order->due_amount, 2) }}
                                    </td>
                                </tr>
                            </table>



                            <!-- FINANCE FOOTER -->
                            <div class="finance-footer">

                                <div class="payment-info">
                                    <div><strong>Payment Terms:</strong> {{ ucfirst(str_replace('_',' ', $order->payment_terms)) }}</div>
                                    <div><strong>Payment Mode:</strong> {{ ucfirst(str_replace('_',' ', $order->payment_mode)) }}</div>
                                    <div><strong>Status:</strong> {{ ucfirst($order->payment_status) }}</div>
                                    <div><strong>Invoice Date:</strong> {{ optional($order->invoice_date)->format('d M Y') ?? 'N/A' }}</div>
                                </div>

                                <div class="grand-total-box">
                                    <span>Grand Total Payable</span>
                                    <h2>{{ $order->currency }} {{ number_format($order->total_amount, 2) }}</h2>
                                </div>

                            </div>


                        </div>

                        <!-- SIGNATURE -->
                        <div class="signature">
                            Regards,<br>
                            <strong> {{ Auth::guard('user')->user()->name }}</strong><br>
                            TechWebMantra
                        </div>




                    </div>

                </div>

                @include('user.include.footer')

            </div>
        </div>
    </div>


    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <!-- jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>


    <script>
        function goBackToLead() {
            const pathParts = window.location.pathname.split('/');
            const encodedId = pathParts[pathParts.length - 1];

            window.location.href = `/crm/user/closed-lead/${encodedId}`;
        }

        async function generatePDF() {

            const {
                jsPDF
            } = window.jspdf;

            const invoice = document.getElementById("invoice");

            html2canvas(invoice, {
                scale: 2,
                useCORS: true
            }).then(canvas => {

                const imgData = canvas.toDataURL("image/png");

                const pdf = new jsPDF("p", "mm", "a4");

                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

                pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);

                pdf.save("invoice-tech-web-mantra.pdf");

                const phone = "{{ $lead->phone }}";
                const message = encodeURIComponent(
                    "Hello {{ $lead->name }},\n\n" +
                    "Please find attached your quotation (Order No: {{ $order->order_number }}).\n\n" +
                    "Thank you,\n{{ $company_details->company_name }}"
                );

                window.open(`https://wa.me/${phone}?text=${message}`, "_blank");
            });
        }
    </script>
</body>

</html>