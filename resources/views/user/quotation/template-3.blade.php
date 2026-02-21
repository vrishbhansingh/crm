<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Final Quotation / Proforma Invoice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- CRM CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">

    <style>
        body {
            background: #f4f6fb;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: #111827;
        }

        .content-wrapper {
            padding: 30px;
            background: #f8f9fc;
        }

        .invoice-container {
            min-width: 1150px;
            margin: auto;
        }

        .invoice-card {
            background: #fff;
            border-radius: 14px;
            padding: 32px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, .08);
        }

        /* HEADER */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 18px;
            margin-bottom: 25px;
        }

        .company-name {
            font-size: 26px;
            font-weight: 700;
            color: #0d6efd;
        }

        .invoice-badge {
            background: #16a34a;
            color: #fff;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* INFO */
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

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th {
            background: #f1f5ff;
            padding: 12px;
            border: 1px solid #ddd;
            font-weight: 600;
        }

        table td {
            padding: 12px;
            border: 1px solid #ddd;
        }

        /* TOTAL SECTION */
        .amount-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .amount-card {
            width: 380px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 18px;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .amount-row.total {
            font-weight: 700;
            font-size: 16px;
            border-top: 1px dashed #d1d5db;
            padding-top: 12px;
        }

        /* PAYMENT */
        .payment-box {
            margin-top: 25px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            padding: 18px;
            border-radius: 10px;
        }

        /* FOOTER */
        .terms {
            margin-top: 30px;
            font-size: 13px;
            line-height: 1.7;
        }

        .signature {
            margin-top: 35px;
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
        }

        .section-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 22px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #1f2937;
        }

        .content-wrapper {
            padding: 30px;
            background: linear-gradient(180deg, #f8f9fc, #eef2ff);
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

        .back-btn {
            box-shadow: 0 4px 5px rgba(0, 0, 0, 0.45);

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

                <div class="invoice-container mb-3">

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
                    <div class="invoice-card" id="invoice">

                        <!-- HEADER -->
                        <div class="invoice-header">
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
                            <div style="text-align:right">
                                <span class="invoice-badge">Final Quote / Proforma</span><br><br>
                                <strong>Order No:</strong> {{ $order->order_number }}<br>
                                <strong>Date:</strong> {{ optional($order->invoice_date)->format('d M Y') ?? now()->format('d M Y') }}
                            </div>
                        </div>

                        <!-- INFO -->
                        <div class="info-grid">
                            <div class="info-box">
                                <strong>Bill To</strong><br>
                                {{ $lead->name }}<br>
                                {{ $lead->company_name }}<br>
                                {{ $lead->email }}<br>
                                {{ $lead->phone }}<br>
                                GST: {{ $lead->gst_no ?? 'N/A' }}
                            </div>

                            <div class="info-box">
                                <strong>Project / Order Info</strong><br>
                                Lead Type: {{ ucfirst($lead->lead_type) }}<br>
                                Priority: {{ ucfirst($lead->priority) }}<br>
                                Status: {{ ucfirst($order->order_status) }}<br>
                                Currency: {{ $order->currency }}
                            </div>
                        </div>

                        <!-- PRODUCT -->
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product / Service</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>{{ $lead->product ?? $lead->service }}</td>
                                    <td>{{ $lead->requirement }}</td>
                                    <td>{{ $order->currency }} {{ number_format($order->sub_total,2) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- TOTAL -->
                        <div class="section-card mt-4" style="background:#eef2ff;border-color:#c7d2fe;">
                            <div class="section-title">Invoice Summary</div>

                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                <span>Sub Total</span>
                                <strong>{{ $order->currency }}&nbsp;{{ number_format($order->sub_total,2) }}</strong>
                            </div>

                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                <span>Discount</span>
                                <strong style="color:#b91c1c;">- {{ $order->currency }}&nbsp;{{ number_format($order->discount,2) }}</strong>
                            </div>

                            <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                                <span>GST (18%)</span>
                                <strong>{{ $order->currency }}&nbsp; {{ $order->gst }}</strong>
                            </div>

                            <hr>

                            <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:700;color:#1e3a8a;">
                                <span>Total Amount</span>
                                <span>{{ $order->currency }}&nbsp;{{ number_format($order->sub_total - $order->discount + $order->gst, 2) }}</span>
                            </div>
                        </div>


                        <!-- PAYMENT -->
                        <div class="section-card">
                            <div class="section-title">Payment Details</div>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:14px;">
                                <div><strong>Payment Terms:</strong> {{ucfirst($order->payment_terms) }}</div>
                                <div><strong>Payment Mode:</strong> {{ ucfirst($order->payment_mode) }}</div>
                                <div><strong>Status:</strong> {{ ucfirst($order->payment_status) }}</div>
                                <div><strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($order->invoice_date)->format('d-m-Y') }}</div>
                                <div><strong>Paid Amount:</strong> {{ $order->currency }}&nbsp;{{ number_format($order->paid_amount,2) }}</div>
                                <div><strong>Due Amount:</strong> <span style="color:#92400e;font-weight:600;">{{ $order->currency }}&nbsp;{{ number_format($order->due_amount,2) }}</span></div>
                            </div>
                        </div>


                        <!-- TERMS -->
                        <div class="terms">
                            <strong>Terms & Conditions</strong><br>
                            • This is a final quotation and valid for 15 days<br>
                            • Payment as per agreed terms<br>
                            • Any scope change may affect pricing<br>
                            • Subject to jurisdiction only
                        </div>

                        <!-- SIGN -->
                        <div style="margin-top:30px;">
                            Regards,<br>
                            <strong style="font-size:15px;">{{ Auth::guard('user')->user()->name }}</strong><br>
                            <span style="color:#6b7280;">{{ $company_details->company_name }}</span>
                        </div>

                    </div>
                </div>
                @include('user.include.footer')

            </div>
        </div>
    </div>


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