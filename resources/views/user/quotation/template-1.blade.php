<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Quick Quote - CRM</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        body {
            background: #f4f6fb;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: #333;
        }

        .content-wrapper {
            padding: 25px;
            background: #f8f9fc;
        }

        .quotation-container {
            max-width: 1000px;
            margin: auto;
        }

        .quotation-card {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
        }

        /* HEADER */
        .quotation-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px dashed #e5e7eb;
            padding-bottom: 18px;
            margin-bottom: 25px;
        }

        .quotation-header h3 {
            margin: 0;
            color: #0d6efd;
        }

        .company-info {
            font-size: 13px;
            color: #6b7280;
        }

        .badge-quick {
            background: #0d6efd;
            color: #fff;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* INFO */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 16px;
            border-radius: 12px;
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
        }

        table td {
            padding: 12px;
            border: 1px solid #ddd;
        }

        /* ===== ESTIMATED PRICE CARD (QUICK QUOTE ONLY) ===== */

        .estimate-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .estimate-card {
            background: linear-gradient(135deg, #0d6efd, #2563eb);
            color: #fff;
            padding: 24px 30px;
            border-radius: 18px;
            min-width: 340px;
            box-shadow: 0 12px 35px rgba(13, 110, 253, 0.35);
        }

        .estimate-card span {
            font-size: 12px;
            letter-spacing: .6px;
            opacity: .9;
            text-transform: uppercase;
        }

        .estimate-card h2 {
            margin: 10px 0 8px;
            font-size: 30px;
            font-weight: 700;
        }

        .estimate-card p {
            margin: 0;
            font-size: 13px;
            opacity: .9;
            line-height: 1.5;
        }

        /* NOTE & SIGNATURE */
        .note {
            margin-top: 25px;
            font-size: 13px;
            color: #555;
        }

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

        .page-body-wrapper {
            padding-top: 65px !important;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
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


        @include('include.header')

        <div class="container-fluid page-body-wrapper">

            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="quotation-container">

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
                        <div class="quotation-header">
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
                                <span class="badge-quick">Quick Quote</span><br><br>
                                <small><strong>Quote No:</strong> QQ-{{ $lead->lead_number ?? '1001' }}</small><br>
                                <small><strong>Lead Type:</strong> {{ ucfirst($lead->lead_type ?? 'inquiry') }}</small><br>
                                <small><strong>Date:</strong> {{ now()->format('d M Y') }}</small>
                            </div>
                        </div>

                        <!-- CUSTOMER -->
                        <div class="info-grid">
                            <div class="info-box">
                                <strong>Customer</strong><br>
                                {{ $lead->name ?? 'John Doe' }}<br>
                                {{ $lead->company_name ?? 'ABC Pvt Ltd' }}
                            </div>

                            <div class="info-box">
                                <strong>Contact</strong><br>
                                {{ $lead->phone ?? '9876543210' }}<br>
                                {{ $lead->email ?? 'john@example.com' }}
                            </div>
                        </div>

                        <!-- LOCATION -->
                        <div class="info-grid">
                            <div class="info-box">
                                <strong>Location</strong><br>
                                {{ $lead->city ?? 'Delhi' }},
                                {{ $lead->state ?? 'Delhi' }},
                                {{ $lead->country ?? 'India' }}
                            </div>

                            <div class="info-box">
                                <strong>GST No</strong><br>
                                {{ $lead->gst_no ?? 'N/A' }}
                            </div>
                        </div>

                        <!-- PRODUCT TABLE -->
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product / Service</th>
                                    <th>Qty</th>
                                    <th width="160">Estimated Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>
                                        {{ $lead->product ?? $lead->service ?? 'CRM Software' }}<br>
                                        <small>{{ $lead->requirement ?? 'Initial CRM setup' }}</small>
                                    </td>
                                    <td>1</td>
                                    <td>₹{{ number_format($lead->budget ?? 120000, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- ESTIMATED PRICE -->
                        <div class="estimate-wrapper">
                            <div class="estimate-card">
                                <span>Estimated Quote Value</span>
                                <h2>₹{{ number_format($lead->budget ?? 120000, 2) }}</h2>
                                <p>
                                    This is an indicative price based on initial discussion.<br>
                                    Final pricing may change after detailed analysis.
                                </p>
                            </div>
                        </div>

                        <!-- NOTE -->
                        <div class="note">
                            <strong>Lead Source:</strong> {{ ucfirst(str_replace('_',' ', $lead->lead_source ?? 'website')) }}
                            |
                            <strong>Priority:</strong> {{ ucfirst($lead->priority ?? 'high') }}
                            |
                            <strong>Status:</strong> {{ ucfirst($lead->lead_status ?? 'new') }}
                        </div>

                        <!-- SIGNATURE -->
                        <div class="signature">
                            Regards,<br>
                            <strong>{{ auth()->user()->name ?? 'Sales Executive' }}</strong><br>
                            <small>Assigned On: {{ $lead->assigned_at ?? '—' }}</small>
                        </div>



                    </div>


                </div>

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