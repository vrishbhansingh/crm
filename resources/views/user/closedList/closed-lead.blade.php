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
            padding: 8px 0;
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
    </style>
</head>

<body>
    @php
    $lastLogin = Auth::guard('web')->user()->last_login;
    @endphp

    <div class="container-scroller">
        @include('user.include.header')

        <div class="container-fluid page-body-wrapper">
            @include('user.include.sidebar')

            <div class="content-wrapper">

                <!-- PAGE HEADER -->
                <div class="crm-header">
                    <div class="crm-header-left">
                        <h2 class="crm-title">
                            <i class="fa fa-bullseye"></i> Lead Management
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
                <div class="row crm-summary-row">

                    <div class="col-md-3">
                        <div class="crm-summary-card">
                            <span class="label">Total Leads</span>
                            <h3 class="value" id="totalLeads">0</h3>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="crm-summary-card success">
                            <span class="label">New Leads</span>
                            <h3 class="value" id="newLeads">0</h3>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="crm-summary-card warning">
                            <span class="label">Follow Ups</span>
                            <h3 class="value" id="followUps">0</h3>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="crm-summary-card info">
                            <span class="label">Converted</span>
                            <h3 class="value" id="convertedLeads">0</h3>
                        </div>
                    </div>

                </div>
                <!-- LEAD TABLE -->
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fa fa-list"></i> Lead List
                        </h5>

                        <input type="text"
                            class="form-control form-control-sm w-25"
                            placeholder="Search by name / phone">
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">

                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Lead Number</th>
                                        <th class="text-center">Lead Type</th>
                                        <th class="text-center">Contact</th>
                                        <th class="text-center">Lead Source</th>
                                        <th class="text-center">Lead Status</th>
                                        <th class="text-center">Priority</th>
                                        <th class="text-center">Follow Up</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody id="leadTableBody">
                                    <tr>
                                        <th colspan="9" class="text-center text-muted py-4">
                                            Loading leads...
                                        </th>
                                    </tr>
                                </tbody>

                            </table>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>




    <div class="modal fade" id="quotationModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Send Quotation</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="quotation_lead_id">

                    <div class="form-group">
                        <label>Client Name</label>
                        <input type="text" class="form-control" id="quotation_name" disabled>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" id="quotation_email" disabled>
                    </div>

                    <div class="form-group">
                        <label>Select Template</label>
                        <select class="form-control" id="quotation_template">
                            <option value="">-- Select Template --</option>
                            <option value="basic">Basic Quotation</option>
                            <option value="premium">Premium Quotation</option>
                            <option value="custom">Custom Offer</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="sendQuotationConfirm">
                        Send Quotation
                    </button>
                </div>

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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            getLeadList();
        });

        function getLeadList() {
            const viewLeadUrlTemplate = "{{ route('user.closed_lead_list', ':id') }}";
            $.ajax({
                url: '{{ route("user.get_closed_lead_list") }}',
                type: 'GET',
                success: function(response) {

                    const leads = response.leads ?? response; // safety
                    let html = '';
                    let total = leads.length;
                    let newLeads = 0;
                    let followUps = 0;
                    let converted = 0;

                    if (total === 0) {
                        $('#leadTableBody').html(`
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            No leads found
                        </td>
                    </tr>
                  `);
                        return;
                    }


                    leads.forEach((lead, index) => {

                        // Count summary
                        if (lead.lead_status === 'new') newLeads++;
                        if (lead.follow_up_date) followUps++;
                        if (lead.is_converted === 'Yes') converted++;



                        // 🔒 Privacy logic

                        html += `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center">${lead.lead_number}</td>
                         <td class="text-center">${capitalizeFirst(lead.lead_type) ?? '-'}</td>

                      <td class="text-center">
                                    <strong>${capitalizeFirst(lead.name) ?? '-'}</strong><br>
                                    <small class="text-muted">${lead.phone ?? '-'}</small><br>
                                    <small class="text-muted">${lead.email ?? ''}</small>
                                </td>
                        <td class="text-center">${capitalizeFirst(lead.lead_source) ?? '-'}</td>
                        <td class="text-center">
                                 <span style="padding: 5px;
                                    border-radius: 5px;"
                                    id="leadStatus_${lead.id}"
                                    class="${
                                        ['new','contacted','interested','follow_up','converted'].includes(lead.lead_status)
                                            ? 'status-active'
                                            : 'status-inactive'
                                        }"
                                    data-id="${lead.id}"
                                    data-status="${lead.lead_status}"
                                    style="cursor:pointer;">
                                    <span class="status-dot"></span>
                                    ${formatLeadStatus(lead.lead_status)}
                                    </span>
                        </td>
                        <td class="text-center">${capitalizeFirst(lead.priority) ?? '-'}</td>
                        <td class="text-center">${lead.follow_up_date ?? '-'}</td>
                        <td class="action-cell">
                               ${lead.action}
                        </td>
                    </tr>
                `;
                    });

                    // Inject table rows
                    $('#leadTableBody').html(html);

                    // Update summary
                    $('#totalLeads').text(total);
                    $('#newLeads').text(newLeads);
                    $('#followUps').text(followUps);
                    $('#convertedLeads').text(converted);
                },
                error: function() {
                    toastr.error('Failed to load leads');
                }
            });
        }

        function capitalizeFirst(str) {
            if (!str) return '-';
            return str.charAt(0).toUpperCase() + str.slice(1);
        }


        function formatLeadStatus(status) {
            if (!status) return '-';

            return status
                .replace(/_/g, ' ') // remove underscore
                .toLowerCase() // normalize
                .replace(/\b\w/g, char => char.toUpperCase()); // capitalize words
        }

        $(document).on('click', '.role-status', function() {
            let leadId = $(this).data('id');
            let currentStatus = $(this).data('status');

            $('#status_lead_id').val(leadId);
            $('#new_lead_status').val(currentStatus);

            $('#changeLeadStatusModal').modal('show');
        });
    </script>

</body>

</html>