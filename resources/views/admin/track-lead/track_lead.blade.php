<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Profile | CRM</title>

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        /* HEADER */
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

        .crm-title i {
            margin-right: 8px;
        }

        .crm-subtitle {
            font-size: 13px;
            opacity: 0.9;
            margin-top: 4px;
        }

        .crm-header-actions button {
            margin-left: 8px;
        }

        /* SUMMARY CARDS */
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
            color: #212529;
        }

        /* TABLE CARD */
        .crm-table-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .crm-table-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .crm-table-header h4 {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
        }

        .crm-table {
            margin: 0;
        }

        .crm-table thead {
            background: #f8f9fa;
        }

        .crm-table thead th {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            color: #495057;
            border-bottom: none;
        }

        .crm-table tbody td {
            vertical-align: middle;
            font-size: 14px;
        }

        .crm-table tbody tr:hover {
            background: #f9fbfd;
        }

        /* ACTION BUTTONS */
        .crm-table .btn {
            padding: 4px 8px;
            font-size: 12px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            user-select: none;
        }

        .status-active {
            background: #b1fad1;
            color: #1e7e34;
        }

        .status-inactive {
            background: #ff1616;
            color: #faeff0;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .close-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(180deg, #f58b6a, #f44040);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
            font-size: 1.4rem;
            line-height: 32px;
            text-align: center;
            cursor: pointer;
            padding: 0;
            transition: all 0.2s ease-in-out;
        }

        .close-btn:hover {
            background: linear-gradient(180deg, #f44040, #fd2c2c);
            transform: scale(1.05);
        }

        .close-btn:active {
            transform: scale(0.95);
        }

        .role-status {
            cursor: pointer;
            padding: 5px;
            border-radius: 5px;
        }

        /* Action buttons */
        /* Action column */
        .action-cell {
            display: flex;
            gap: 8px;
            width: 100%;
            flex-direction: column;
        }

        /* Base capsule button */
        .action-btn {
            flex: 1;
            /* take equal space */
            border: none;
            padding: 8px 0px;
            border-radius: 999px;
            /* capsule */
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        /* View button */
        .action-btn.view {
            background: #e7f1ff;
            color: #0d6efd;
        }

        .action-btn.view:hover {
            background: #0d6efd;
            color: #fff;
        }

        /* Delete button */
        .action-btn.delete {
            background: #fdecea;
            color: #dc3545;
        }

        .action-btn.delete:hover {
            background: #dc3545;
            color: #fff;
        }

        .call-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .call-connected {
            background: #d4edda;
            color: #155724;
        }

        .call-not_reachable,
        .call-switched_off {
            background: #f8d7da;
            color: #721c24;
        }

        .call-callback {
            background: #fff3cd;
            color: #856404;
        }

        .response-badge {
            background: #eef2ff;
            color: #4b49ac;
            padding: 5px 10px;
            font-size: 11px;
            border-radius: 6px;
            text-transform: capitalize;
        }

        /* ===============================
   LEAD LIST HEADER
================================ */
        .lead-card-header {
            background: #ffffff;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top-left-radius: 14px;
            border-top-right-radius: 14px;
        }

        .lead-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .lead-icon {
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

        .lead-header-left h5 {
            font-weight: 600;
        }

        .search-wrapper {
            position: relative;
            width: 260px;
        }

        .search-wrapper input {
            padding-left: 35px;
            border-radius: 30px;
            border: 1px solid #d1d5db;
            transition: 0.3s ease;
        }

        .search-wrapper input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 13px;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .lead-card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .search-wrapper {
                width: 100%;
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

                <!-- PAGE HEADER -->
                <div class="crm-header">
                    <div class="crm-header-left">
                        <h2 class="crm-title">
                            <i class="fa fa-bullseye"></i> Lead Tracks
                        </h2>
                        <p class="crm-subtitle">
                            View and track your assigned leads & follow-ups
                        </p>
                    </div>

                    <div class="crm-header-actions">
                        <button class="btn btn-primary btn-sm" id="refreshLeads">
                            <i class="fa fa-refresh"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- SUMMARY CARDS -->
                <!-- LEAD TABLE -->
                <div class="card shadow-sm">
                    <div class="lead-card-header">

                        <div class="lead-header-left">
                            <div class="lead-icon">
                                <i class="fa fa-users"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Lead List</h5>
                                <small class="text-muted">Manage and track all assigned leads</small>
                            </div>
                        </div>

                        <div class="lead-header-right">
                           
                        </div>

                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">

                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Lead No</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Phone</th>
                                        <th class="text-center">Lead Status</th>
                                        <th class="text-center">Priority</th>
                                        <th class="text-center">Follow Up</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody id="leadTableBody">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Loading call history...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @include('admin.include.footer')

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


    <script>
        $(document).ready(function() {
            loadTrackLead();

            $('#refreshLeads').on('click', function() {
                loadTrackLead();
            });
        });

        function loadTrackLead() {
            $.ajax({
                url: "{{ route('admin.get_track_leads') }}",
                type: "GET",
                dataType: "json",

                beforeSend: function() {
                    $('#leadTableBody').html(`
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        Loading leads...
                    </td>
                </tr>
            `);
                },

                success: function(response) {

                    if (!response.status || response.data.length === 0) {
                        $('#leadTableBody').html(`
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No leads found
                        </td>
                    </tr>
                `);
                        return;
                    }

                    let html = '';
                    let sl = 1;

                    $.each(response.data, function(index, lead) {

                        html += `
                <tr>
                    <td class="text-center">${sl++}</td>

                    <td class="text-center">
                        <strong>#${lead.lead_number}</strong>
                    </td>

                    <td class="text-center">
                        ${lead.name ?? '-'}
                    </td>

                    <td class="text-center">
                        ${lead.phone ?? '-'}
                    </td>

                    <td class="text-center">
                        <span class="response-badge">
                            ${lead.lead_status}
                        </span>
                    </td>

                    <td class="text-center">
                        ${lead.priority}
                    </td>

                    <td class="text-center">
                        <small>
                            ${lead.follow_up_date ?? '-'}<br>
                            ${lead.follow_up_time ?? ''}
                        </small>
                    </td>

                    <td class="text-center">
                        ${lead.action}
                    </td>
                </tr>
                `;
                    });

                    $('#leadTableBody').html(html);
                },

                error: function(xhr) {
                    toastr.error('Failed to load leads');
                    console.error(xhr.responseText);
                }
            });
        }
    </script>



</body>

</html>