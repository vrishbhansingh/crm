<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer Contacts | CRM Admin</title>

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">

    <style>
        .table-wrapper {
            background: #ffffff;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
        }

        table thead th {
            background: #f5f7fb;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
        }

        table tbody td {
            font-size: 13px;
            text-align: center;
            vertical-align: middle;
        }

        .contact-name {
            font-weight: 600;
            color: #111827;
        }

        .muted {
            font-size: 11.5px;
            color: #6b7280;
            display: block;
        }

        .budget-badge {
            background: #eef2ff;
            color: #4b49ac;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        :root {
            --primary: #2563eb;
            --primary-light: #e0e7ff;
            --bg-soft: #f8fafc;
            --text-dark: #111827;
            --text-muted: #6b7280;
            --border: #e5e7eb;
        }

        body {
            background: var(--bg-soft);
        }

        /* ===== PAGE HEADER ===== */
        .page-header {
            background: #ffffff;
            padding: 18px 22px;
            border-radius: 14px;
            margin-bottom: 18px;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .page-header::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 5px;
            height: 100%;
            border-radius: 14px 0 0 14px;
            background: linear-gradient(180deg, #2563eb, #4f46e5);
        }

        .page-header h4 {
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
        }

        .page-header p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 2px 0 0;
        }

        /* ===== TABLE CARD ===== */
        .table-wrapper {
            background: #ffffff;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border);
        }

        table {
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        table thead th {
            background: #f1f5f9;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            text-align: center;
            border: none;
            padding: 12px;
        }

        table tbody tr {
            background: #ffffff;
            transition: all 0.2s ease;
        }

        table tbody tr:hover {
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.12);
            transform: translateY(-1px);
        }

        table tbody td {
            font-size: 13px;
            color: #374151;
            text-align: center;
            vertical-align: middle;
            padding: 14px;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        table tbody td:first-child {
            border-left: 1px solid var(--border);
            border-radius: 10px 0 0 10px;
        }

        table tbody td:last-child {
            border-right: 1px solid var(--border);
            border-radius: 0 10px 10px 0;
        }

        /* ===== CONTACT NAME ===== */
        .contact-name {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 13.5px;
        }

        .muted {
            font-size: 11.5px;
            color: var(--text-muted);
            display: block;
            margin-top: 2px;
        }

        /* ===== BADGES ===== */
        .budget-badge {
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            color: #4338ca;
            padding: 5px 14px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        /* ===== EMPTY STATE ===== */
        .empty-row td {
            color: var(--text-muted);
            font-size: 13px;
            padding: 20px;
        }

        /* ===============================
   CRM PAGE HEADER
================================ */
        .crm-page-header {
            background: #ffffff;
            padding: 18px 22px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            border-left: 4px solid #0d6efd;
        }

        .crm-header-content {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .crm-header-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0d6efd, #00c6ff);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .crm-page-header h4 {
            font-weight: 700;
            font-size: 18px;
            color: #1f2937;
        }

        .crm-subtitle {
            color: #6b7280;
            font-size: 13px;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .crm-page-header {
                padding: 14px 16px;
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

                <!-- Page Header -->
                <div class="crm-page-header mb-4">
                    <div class="crm-header-content">
                        <div class="crm-header-icon">
                            <i class="fa fa-address-book"></i>
                        </div>
                        <div>
                            <h4 class="mb-0">Customer Contacts</h4>
                            <small class="crm-subtitle">All customer contact records</small>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-wrapper">
                            <div class="table-responsive">
                                <table class="table" id="customerContactTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Designation</th>
                                            <th>City</th>
                                            <th>Budget</th>
                                            <th>Lead ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- AJAX DATA -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                @include('include.footer')

            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>

    <script>
        function loadCustomerContacts() {
            $.ajax({
                url: "{{ route('contacts.data') }}",
                type: "GET",
                success: function(response) {

                    let tbody = '';

                    if (response.data && response.data.length > 0) {

                        response.data.forEach((row, index) => {
                            tbody += `
                        <tr>
                            <td>${index + 1}</td>

                            <td>
                                <span class="contact-name">${row.name}</span>
                            </td>

                            <td>
                                ${row.phone}
                            </td>

                            <td>
                                <span class="muted">${row.email}</span>
                            </td>

                            <td>
                                ${row.designation}
                            </td>

                            <td>
                                ${row.city}
                            </td>

                            <td>
                                <span class="budget-badge">
                                    ₹ ${row.budget}
                                </span>
                            </td>

                            <td>
                                ${row.lead_id}
                            </td>
                        </tr>
                        `;
                        });

                    } else {
                        tbody = `
                        <tr>
                            <td colspan="8" class="text-center">No customer contacts found</td>
                        </tr>`;
                    }

                    $('#customerContactTable tbody').html(tbody);
                }
            });
        }

        $(document).ready(function() {
            loadCustomerContacts();
        });
    </script>

</body>

</html>