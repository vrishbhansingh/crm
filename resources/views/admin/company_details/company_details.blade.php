<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Details | CRM</title>

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">

    <style>
        :root {
            --bg: #f5f7fb;
            --card: #ffffff;
            --border: #e6e9f0;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #2563eb;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* Same modernization pattern as the rest of this pass. */
        .crm-page-header {
            background: var(--card);
            padding: 26px 28px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            margin-bottom: 24px;
        }

        .crm-page-header h3 {
            margin: 0;
            font-weight: 700;
            font-size: 22px;
        }

        /* LAYOUT */
        .company-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 24px;
        }

        @media (max-width: 992px) {
            .company-layout {
                grid-template-columns: 1fr;
            }
        }

        /* COMMON PANEL */
        .panel {
            background: var(--card);
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 22px;
        }

        /* LEFT PANEL */
        .company-panel {
            text-align: center;
        }

        .company-logo {
            width: 120px;
            height: 120px;
            border-radius: 16px;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            color: var(--primary);
            margin: 0 auto 16px;
        }

        .company-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .company-status {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            background: #e6fffa;
            color: #047857;
            margin-bottom: 18px;
        }

        .company-actions a {
            display: block;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid var(--border);
            text-decoration: none;
            font-size: 14px;
            margin-top: 10px;
            color: var(--text);
        }

        .company-actions a.primary {
            background: var(--primary);
            color: #fff;
            border: none;
        }

        /* RIGHT CONTENT */
        .section {
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 8px;
        }

        .data-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px 30px;
        }

        @media (max-width: 768px) {
            .data-grid {
                grid-template-columns: 1fr;
            }
        }

        .data-item span {
            font-size: 12px;
            color: var(--muted);
            display: block;
            margin-bottom: 4px;
        }

        .data-item strong {
            font-size: 14px;
            font-weight: 500;
        }

        .company-logo-wrapper {
            width: 120px;
            height: 120px;
            margin: 0 auto 16px;
            border-radius: 16px;
            overflow: hidden;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Actual image */
        .company-logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 16px;
        }

        /* Placeholder letter */
        .company-logo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            color: var(--primary);
        }
    </style>
</head>

<body>

    <div class="container-scroller">
        @include('include.header')

        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="crm-page-header">
                    <h3>Organization Profile</h3>
                </div>

                <div class="company-layout">

                    <!-- LEFT COMPANY PANEL -->
                    <div class="panel company-panel">

                        <div class="company-logo-wrapper">
                            @if(!empty($company->company_logo))
                            <img
                                src="{{ asset($company->company_logo) }}"
                                alt="Company Logo"
                                class="company-logo-img">
                            @else
                            <div class="company-logo-placeholder">
                                {{ strtoupper(substr($company->company_name, 0, 1)) }}
                            </div>
                            @endif
                        </div>

                        <div class="company-name">
                            {{ $company->company_name }}
                        </div>

                        <div class="company-status">
                            {{ $company->status }}
                        </div>

                        <div class="company-actions">
                            <a href="{{route('company.edit')}}" class="primary">Edit Company Details</a>
                        </div>
                    </div>

                    <!-- RIGHT DETAILS -->
                    <div>

                        <!-- CONTACT INFO -->
                        <div class="panel section">
                            <div class="section-title">Contact Information</div>

                            <div class="data-grid">
                                <div class="data-item">
                                    <span>Email</span>
                                    <strong>{{ $company->email }}</strong>
                                </div>

                                <div class="data-item">
                                    <span>Phone</span>
                                    <strong>{{ $company->phone }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- ADDRESS -->
                        <div class="panel section">
                            <div class="section-title">Address</div>

                            <div class="data-grid">
                                <div class="data-item">
                                    <span>Location</span>
                                    <strong>
                                        {{ $company->address }}<br>
                                        {{ $company->city }}, {{ $company->state }}<br>
                                        {{ $company->country }} - {{ $company->pincode }}
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <!-- TAX INFO -->
                        <div class="panel section">
                            <div class="section-title">Tax Information</div>

                            <div class="data-grid">
                                <div class="data-item">
                                    <span>GST Number</span>
                                    <strong>{{ $company->gst_number }}</strong>
                                </div>

                                <div class="data-item">
                                    <span>PAN Number</span>
                                    <strong>{{ $company->pan_number }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- BANK INFO -->
                        <div class="panel section">
                            <div class="section-title">Bank Details</div>

                            <div class="data-grid">
                                <div class="data-item">
                                    <span>Bank Name</span>
                                    <strong>{{ $company->bank_name }}</strong>
                                </div>

                                <div class="data-item">
                                    <span>Account Name</span>
                                    <strong>{{ $company->account_name }}</strong>
                                </div>

                                <div class="data-item">
                                    <span>Account Number</span>
                                    <strong>{{ $company->account_number }}</strong>
                                </div>

                                <div class="data-item">
                                    <span>IFSC Code</span>
                                    <strong>{{ $company->ifsc_code }}</strong>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                @include('include.footer')
            </div>
        </div>
    </div>

</body>

</html>