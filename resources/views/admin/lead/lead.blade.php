<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CRM Admin Panel</title>

    <!-- plugins:css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{asset('vendors/feather/feather.css')}}">
    <link rel="stylesheet" href="{{asset('vendors/ti-icons/css/themify-icons.css')}}">
    <link rel="stylesheet" href="{{asset('vendors/css/vendor.bundle.base.css')}}">

    <!-- Plugin css -->
    <link rel="stylesheet" href="{{asset('vendors/datatables.net-bs4/dataTables.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('js/select.dataTables.min.css')}}">

    <!-- inject css -->
    <link rel="stylesheet" href="{{asset('css/vertical-layout-light/style.css')}}">
    <link rel="stylesheet" href="{{asset('css/confirm.css')}}">
    <link rel="stylesheet" href="{{asset('css/toast.css')}}">

    <link rel="stylesheet" href="{{asset('vendors/select2/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('vendors/select2-bootstrap-theme/select2-bootstrap.min.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        /* ===== User Table Wrapper ===== */
        .user-table-wrapper {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            padding: 15px;
        }

        /* ===== Table Base ===== */
        .user-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .user-table thead th {
            background: #f5f7fb;
            color: #4a4a4a;
            font-weight: 600;
            font-size: 13px;
            border: none;
            padding: 12px 14px;
        }

        .user-table tbody tr {
            background: #ffffff;
            transition: box-shadow 0.2s ease;
        }

        .user-table tbody tr:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .user-table tbody td {
            padding: 14px;
            border-top: 1px solid #eef1f6;
            border-bottom: 1px solid #eef1f6;
            font-size: 13px;
            color: #555;
        }

        .user-table tbody td:first-child {
            border-left: 1px solid #eef1f6;
            border-radius: 8px 0 0 8px;
        }

        .user-table tbody td:last-child {
            border-right: 1px solid #eef1f6;
            border-radius: 0 8px 8px 0;
        }

        /* ===== Role Badge ===== */
        .role-badge {
            padding: 5px 10px;
            font-size: 11px;
            border-radius: 5px;
            background: #eef2ff;
            color: #4b49ac;
            font-weight: 500;
        }

        .role-status {
            padding: 5px 10px;
            font-size: 11px;
            border-radius: 5px;
            font-weight: 500;
        }

        /* ===== Action Column (Image Match) ===== */
        .action-stack {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: center;
        }

        .action-stack .btn {
            width: 90px;
            font-size: 12px;
            font-weight: 500;
            padding: 6px 0;
            border-radius: 6px;
        }

        .action-stack .btn i {
            margin-right: 4px;
            font-size: 13px;
        }

        /* Subtle hover like image */
        .action-stack .btn-success:hover {
            background-color: #218838;
        }

        .action-stack .btn-danger:hover {
            background-color: #c82333;
        }


        /* ===== Status Badge ===== */
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
            background: #e9f7ef;
            color: #1e7e34;
        }

        .status-inactive {
            background: #fdf1f1;
            color: #c82333;
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

        /* ===== Add User Modal ===== */
        .add-user-modal {
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.15);
        }

        /* Header */
        .add-user-modal .modal-header {
            border-bottom: 1px solid #eef0f4;
            padding: 16px 20px;
            background: #f9fafc;
        }

        .add-user-modal .modal-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }

        /* Body */
        .add-user-modal .modal-body {
            padding: 20px;
        }

        /* Labels */
        .add-user-modal label {
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 4px;
        }

        /* Inputs */
        .add-user-modal .form-control {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            font-size: 13px;
            padding: 8px 12px;
            background: #fcfdff;
        }

        .add-user-modal .form-control:focus {
            border-color: #4b49ac;
            box-shadow: 0 0 0 2px rgba(75, 73, 172, 0.12);
        }

        /* Footer */
        .add-user-modal .modal-footer {
            border-top: 1px solid #eef0f4;
            padding: 14px 20px;
            background: #f9fafc;
        }

        /* Buttons */
        .add-user-modal .btn-light {
            background: #f1f3f7;
            border: none;
            color: #374151;
        }

        .add-user-modal .btn-light:hover {
            background: #e5e7eb;
        }

        /* Solid primary action button */
        .btn-save-solid {
            font-size: 14px;
            font-weight: 600;
            padding: 9px 22px;
            border-radius: 6px;
            background-color: #4B49AC;
            border: none;
            letter-spacing: 0.3px;
        }

        .btn-save-solid:hover {
            background-color: #3f3db5;
        }

        .btn-save-solid:focus,
        .btn-save-solid:active {
            background-color: #3f3db5;
            box-shadow: none;
        }


        @media (min-width: 768px) {
            .add-user-modal-wrapper {
                max-width: 720px;
                margin: auto;
            }
        }

        /* ===== Expanded Lead Row Styling ===== */
        .expand-row td {
            padding: 0 !important;
            border: none !important;
        }

        .lead-expand-card {
            background: #f9fafc;
            border-radius: 12px;
            padding: 20px;
            margin: 10px 0;
            border: 1px solid #eef1f6;
        }

        /* Grid */
        .lead-expand-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px 24px;
            font-size: 13px;
        }

        /* Label */
        .lead-expand-label {
            font-size: 11px;
            color: #6b7280;
            font-weight: 500;
        }

        /* Value */
        .lead-expand-value {
            font-size: 13px;
            color: #111827;
            font-weight: 500;
        }

        /* Full width blocks */
        .lead-expand-full {
            grid-column: span 4;
        }

        /* Highlight blocks */
        .lead-highlight {
            background: #ffffff;
            border-radius: 8px;
            padding: 10px 12px;
            border: 1px solid #eef1f6;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .lead-expand-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .lead-expand-full {
                grid-column: span 2;
            }
        }

        /* ===== Assigned User Badge ===== */

        .assign-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.25s ease;
            user-select: none;
        }

        /* Assigned */
        .assign-active {
            background: #e8f2ff;
            color: #0d6efd;
            border: 1px solid #cfe2ff;
        }

        .assign-active:hover {
            background: #dbeafe;
        }

        /* Unassigned */
        .assign-unassigned {
            background: #f1f3f5;
            color: #6c757d;
            border: 1px dashed #ced4da;
        }

        .assign-unassigned:hover {
            background: #e9ecef;
        }

        /* Icon dot */
        .assign-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            background: rgba(0, 0, 0, 0.05);
        }

        .assign-user {
            padding: 5px;
            border-radius: 5px;

        }

        /* Hover effect */
        .assign-user:hover {
            transform: translateY(-1px);
        }

        .form-checkbox {
            width: 21px;
            height: 21px;
        }

        /* ===============================
   LEAD HERO HEADER (Like Track Lead)
================================ */
        .lead-hero-header {
            background: linear-gradient(135deg, #1d4ed8, #0ea5e9);
            padding: 22px 28px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #ffffff;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .lead-hero-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .lead-hero-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .lead-hero-header h4 {
            font-weight: 700;
            font-size: 20px;
        }

        .lead-hero-header small {
            font-size: 13px;
            opacity: 0.9;
        }

        /* Buttons */
        .lead-hero-right .btn {
            border-radius: 30px;
            padding: 6px 18px;
            font-weight: 600;
            transition: 0.3s ease;
        }

        .lead-hero-right .btn:hover {
            transform: translateY(-1px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .lead-hero-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
            }

            .lead-hero-right {
                width: 100%;
            }

            .lead-hero-right .btn {
                width: 100%;
                margin-bottom: 8px;
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

                <!-- PAGE HEADER -->
                <!-- 🔷 LEAD LIST HERO HEADER -->
                <div class="lead-hero-header mb-4">

                    <div class="lead-hero-left">
                        <div class="lead-hero-icon">
                            <i class="fa fa-bullseye"></i>
                        </div>
                        <div>
                            <h4 class="mb-0">Lead List</h4>
                            <small>Manage all CRM leads</small>
                        </div>
                    </div>

                    <div class="lead-hero-right">
                        {{-- Bulk lead upload button — hidden from client for now. Uncomment to re-enable. --}}
                        {{--
                        <button class="btn btn-light btn-sm mr-2"
                            data-toggle="modal"
                            data-target="#uploadLeadsModal">
                            <i class="fa fa-upload mr-1"></i> Upload Leads
                        </button>
                        --}}

                        <a href="{{ route('leads.create') }}"
                            class="btn btn-dark btn-sm">
                            <i class="fa fa-plus mr-1"></i> Add Lead
                        </a>
                    </div>

                </div>


                <div class="row mb-3 d-none" id="bulkAssignBar">
                    <div class="col-md-8 d-flex align-items-center gap-2">
                        <strong style="padding: 0 10px;">Assign selected leads to:</strong>

                        <select id="bulkAssignedUser" class="form-control form-control-sm w-25">
                            <option value="">-- Select User --</option>
                        </select>

                        <button class="btn btn-primary btn-sm" style="margin-left: 10px;" id="bulkAssignBtn">
                            Assign
                        </button>
                    </div>
                </div>
                <!-- LEAD TABLE -->
                <div class="row">
                    <div class="col-12 grid-margin">
                        <div class="card">
                            <div class="card-body">

                                <div class="table-responsive user-table-wrapper">
                                    <table id="userTable" class="table user-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width:40px;"></th>
                                                <th class="text-center">#</th>
                                                <th class="text-center">Lead Type</th>
                                                <th class="text-center">Contact</th>
                                                <th class="text-center">Lead Source</th>
                                                <th class="text-center">Lead Status</th>
                                                <th class="text-center">Priority</th>
                                                <th class="text-center">Follow Up</th>
                                                <th class="text-center">Assigned To</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            {{-- AJAX DATA --}}
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                @include('include.footer')
            </div>
        </div>
    </div>


    <!-- ================= DELETE MODAL ================= -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content add-user-modal">

                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fa fa-trash"></i> Confirm Delete
                    </h5>
                    <button type="button" class="close-btn" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body text-center">
                    <p>Are you sure you want to delete this lead?</p>
                    <small class="text-muted">This action cannot be undone.</small>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="statusConfirmModal" tabindex="-1" aria-labelledby="statusConfirmLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">

                <div class="modal-header bg-light">
                    <h5 class="modal-title mb-0" id="statusConfirmLabel">Confirm Status Update</h5>

                    <button type="button" class="close-btn" data-dismiss="modal" aria-label="Close">
                        &times;
                    </button>
                </div>


                <div class="modal-body text-center">
                    <p class="mb-0">
                        Are you sure you want to change this user’s status to <strong id="statusConfirmText">Active/Inactive</strong>?
                    </p>
                    <small class="text-muted">
                        This action cannot be undone.
                    </small>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-dark btn-sm" data-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="confirmStatusBtn">
                        Confirm Update
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="changeLeadStatusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content add-user-modal">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-refresh"></i> Change Lead Status
                    </h5>
                    <button type="button" class="close-btn" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="status_lead_id">

                    <label class="mb-1">Select Status</label>
                    <select id="new_lead_status" class="form-control" data-master-type="lead_status">
                    </select>

                    <div id="lostReasonWrap" style="display:none; margin-top:12px;">
                        <label class="mb-1">Lost Reason</label>
                        <select id="lost_reason" class="form-control" data-master-type="lost_reason">
                        </select>
                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary btn-save-solid" id="saveLeadStatus">
                        <i class="fa fa-save"></i> Update
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Upload Leads Modal -->
    <div class="modal fade" id="uploadLeadsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-file-excel"></i> Upload Leads (Excel)
                    </h5>
                    <a href="{{route('leads.download_format')}}"
                        class="btn btn-info btn-sm" download>
                        <i class="fa fa-download"></i> Download Format
                    </a>
                    <button type="button" class="btn btn-danger rounded-circle" style="padding: 7px 10px;" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form id="uploadLeadsForm" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label>Select Excel File (.xlsx)</label>
                            <input type="file" name="file" id="excelFile" class="form-control" accept=".xlsx,.xls">
                        </div>

                        <small class="text-muted">
                            File must contain headers like:
                            <br>
                            <b>lead_type, name, phone, email, city, product, budget</b>
                        </small>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success btn-sm" onclick="uploadLeads()">
                        <i class="fa fa-upload"></i> Upload
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="assignUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Assign Lead</h5>
                    <button class="close text-white" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="leadId">

                    <div class="form-group">
                        <label>Assign To</label>
                        <select id="assignedUser" class="form-control">
                            <option value="">Loading...</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="saveAssignedUser">Save</button>
                </div>

            </div>
        </div>
    </div>


    <!-- ================= JS ================= -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{asset('vendors/chart.js/Chart.min.js')}}"></script>
    <script src="{{asset('vendors/datatables.net/jquery.dataTables.js')}}"></script>
    <script src="{{asset('vendors/datatables.net-bs4/dataTables.bootstrap4.js')}}"></script>
    <script src="{{asset('js/dataTables.select.min.js')}}"></script>
    <script src="{{asset('vendors/select2/select2.min.js')}}"></script>
    <script src="{{asset('js/select2.js')}}"></script>
    <script src="{{asset('js/off-canvas.js')}}"></script>
    <script src="{{asset('js/hoverable-collapse.js')}}"></script>
    <script src="{{asset('js/template.js')}}"></script>
    <script src="{{asset('js/settings.js')}}"></script>
    <script src="{{asset('js/todolist.js')}}"></script>
    <script src="{{asset('js/dashboard.js')}}"></script>
    <script src="{{asset('js/Chart.roundedBarCharts.js')}}"></script>
    <script src="{{asset('js/toast.js')}}"></script>
    <script src="{{asset('js/confirm.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script>
        function showToast(message, type = 'success') {
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "5000"
            };

            if (type === 'success') {
                toastr.success(message);
            } else if (type === 'error') {
                toastr.error(message);
            } else if (type === 'warning') {
                toastr.warning(message);
            } else {
                toastr.info(message);
            }
        }

        function capitalizeFirst(str) {
            if (!str) return '-';
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function loadLeadList() {
            $.ajax({
                url: '{{route("leads.data")}}', // keep your existing route
                type: 'GET',
                success: function(response) {

                    let tbody = '';

                    if (response && Array.isArray(response.data) && response.data.length) {
                        response.data.forEach(item => {

                            tbody += `
                            <tr>
                                <td class="text-center">
                                    <i class="fa fa-plus-circle text-success toggle-row"
                                    style="cursor:pointer;font-size:16px;"
                                    data-id="${item.id}"></i>
                                </td>
                        
                                <!-- # -->
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <input 
                                            type="checkbox"
                                            class="lead-checkbox form-checkbox"
                                            value="${item.id}"
                                            style="margin-bottom:4px;"
                                        >
                                        <span>${item.sl_no}</span>
                                    </div>
                                </td>

                                <!-- Lead Type -->
                                <td class="text-center">
                                    <span class="role-badge">
                                        ${formatLeadStatus(item.lead_type)}
                                    </span>
                                </td>

                                <!-- Contact -->
                                <td class="text-center">
                                    <strong>${capitalizeFirst(item.name) ?? '-'}</strong><br>
                                    <small class="text-muted">${item.phone ?? '-'}</small><br>
                                    <small class="text-muted">${item.email ?? ''}</small>
                                </td>

                                <!-- Lead Source -->
                                <td class="text-center">
                                    ${formatLeadStatus(item.lead_source) ?? '-'}
                                </td>

                             <td class="text-center">
                                    <span 
                                        id="leadStatus_${item.id}"
                                        class="role-status ${
                                            ['new','contacted','interested','follow_up','converted'].includes(item.lead_status)
                                                ? 'status-active'
                                                : 'status-inactive'
                                        }"
                                        data-id="${item.id}"
                                        data-status="${item.lead_status}"
                                        style="cursor:pointer;"
                                    >
                                        <span class="status-dot"></span>
                                        ${formatLeadStatus(item.lead_status)}
                                    </span>
                                </td>

                                <!-- Priority -->
                                <td class="text-center">
                                    <span class="role-badge">
                                        ${capitalizeFirst(item.priority) ?? '-'}
                                    </span>
                                </td>

                                <!-- Follow Up -->
                                <td class="text-center">
                                    ${
                                        item.follow_up_date
                                            ? `${item.follow_up_date}<br><small>${item.follow_up_time ?? ''}</small>`
                                            : '-'
                                    }
                                </td>
                                 <td class="text-center">
                                    <span 
                                        class="assign-user ${
                                            item.assigned_to ? 'assign-active' : 'assign-unassigned'
                                        }"
                                        data-lead_id="${item.id ?? ''}"
                                        data-assigned-id="${item.assigned_to_id ?? ''}"
                                        style="cursor:pointer;"
                                    >
                                        ${item.assigned_to ?? 'null'}
                                    </span>
                                </td>
                                <td class="text-center">
                                        <span 
                                            class="status-badge ${item.status === 'Active' ? 'status-active' : 'status-inactive'}"
                                            data-id="${item.id}"
                                            data-status="${item.status}">
                                            <span class="status-dot"></span>
                                            ${item.status}
                                        </span>
                                    </td>

                                <!-- Action -->
                                <td class="text-center">
                                    ${item.action}
                                </td>
                            </tr>
                            <tr class="expand-row" id="expand_${item.id}" style="display:none;">
                                    <td colspan="10">

                                        <div class="lead-expand-card">

                                            <div class="lead-expand-grid">

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Alternate Phone</div>
                                                    <div class="lead-expand-value">${item.alternate_phone ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">City</div>
                                                    <div class="lead-expand-value">${item.city ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">State</div>
                                                    <div class="lead-expand-value">${item.state ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Country</div>
                                                    <div class="lead-expand-value">${item.country ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Product</div>
                                                    <div class="lead-expand-value">${item.product ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Service</div>
                                                    <div class="lead-expand-value">${item.service ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Budget</div>
                                                    <div class="lead-expand-value">₹${item.budget ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Conversion Value</div>
                                                    <div class="lead-expand-value">₹${item.conversion_value ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Is Converted</div>
                                                    <div class="lead-expand-value">${item.is_converted}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Converted At</div>
                                                    <div class="lead-expand-value">${item.converted_at ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Status Reason</div>
                                                    <div class="lead-expand-value">${item.status_reason ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Last Contacted At</div>
                                                    <div class="lead-expand-value">${item.last_contacted_at ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Last Contacted By</div>
                                                    <div class="lead-expand-value">${item.last_contacted_by ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Assigned To (ID)</div>
                                                    <div class="lead-expand-value">${item.assigned_to ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Assigned By (ID)</div>
                                                    <div class="lead-expand-value">${item.assigned_by ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Assigned At</div>
                                                    <div class="lead-expand-value">${item.assigned_at ?? '-'}</div>
                                                </div>

                                                <div class="lead-expand-full lead-highlight">
                                                    <div class="lead-expand-label">Follow Up Note</div>
                                                    <div class="lead-expand-value">${item.follow_up_note ?? '-'}</div>
                                                </div>

                                                <div class="lead-expand-full lead-highlight">
                                                    <div class="lead-expand-label">Remarks</div>
                                                    <div class="lead-expand-value">${item.remarks ?? '-'}</div>
                                                </div>

                                                <div class="lead-expand-full lead-highlight">
                                                    <div class="lead-expand-label">Internal Note</div>
                                                    <div class="lead-expand-value">${item.internal_note ?? '-'}</div>
                                                </div>

                                                <div class="lead-expand-full lead-highlight">
                                                    <div class="lead-expand-label">Requirement</div>
                                                    <div class="lead-expand-value">${item.requirement ?? '-'}</div>
                                                </div>

                                            </div>

                                        </div>

                                    </td>
                                </tr>
                    `;
                        });

                    } else {
                        tbody = `
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            No lead data found
                        </td>
                    </tr>
                `;
                    }

                    $('#userTable tbody').html(tbody);
                },

                error: function() {
                    toastr.error('Something went wrong while loading leads');
                }
            });
        }

        // Populates every <select data-master-type="..."> from the Master Data
        // lookup endpoint instead of hardcoded <option> lists (Phase 2).
        function loadMasterDropdowns() {
            $('select[data-master-type]').each(function() {
                const $select = $(this);
                const type = $select.data('master-type');

                $.get("{{ url('master-data/lookup') }}/" + type, function(response) {
                    response.data.forEach(function(option) {
                        $select.append(`<option value="${option.code}">${option.label}</option>`);
                    });
                });
            });
        }

        $(document).ready(function() {
            loadLeadList();
            loadMasterDropdowns();
        });
        $(document).on('click', '.toggle-row', function() {

            let id = $(this).data('id');
            let row = $('#expand_' + id);

            if (row.is(':visible')) {
                row.slideUp();
                $(this).removeClass('fa-minus-circle text-danger')
                    .addClass('fa-plus-circle text-success');
            } else {
                row.slideDown();
                $(this).removeClass('fa-plus-circle text-success')
                    .addClass('fa-minus-circle text-danger');
            }
        });



        let selectedUserId = null;

        $(document).on('click', '.status-badge', function() {
            selectedUserId = $(this).data('id');
            const currentStatus = $(this).data('status');

            // Update modal text dynamically
            $('#statusConfirmText').text(
                ` ${
            currentStatus === 'Active' ? 'Inactive' : 'Active'
        }`
            );

            $('#statusConfirmModal').modal('show');
        });

        $('#confirmStatusBtn').on('click', function() {

            if (!selectedUserId) return;

            $.ajax({
                url: '{{ route("leads.toggle_status") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: selectedUserId
                },
                success: function(res) {
                    if (res.status) {
                        showToast(res.message || 'Status updated successfully');
                        $('#statusConfirmModal').modal('hide');
                        loadLeadList();
                    } else {
                        showToast('Failed to update status', 'error');
                    }
                },
                error: function() {
                    showToast('Server error', 'error');
                }
            });
        });

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
            toggleLostReason();

            $('#changeLeadStatusModal').modal('show');
        });

        function toggleLostReason() {
            if ($('#new_lead_status').val() === 'not_interested') {
                $('#lostReasonWrap').show();
            } else {
                $('#lostReasonWrap').hide();
            }
        }

        $(document).on('change', '#new_lead_status', toggleLostReason);

        $('#saveLeadStatus').on('click', function() {

            let leadId = $('#status_lead_id').val();
            let newStatus = $('#new_lead_status').val();
            let lostReason = $('#lostReasonWrap').is(':visible') ? $('#lost_reason').val() : null;

            $.ajax({
                url: '{{ route("leads.update_status") }}',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: leadId,
                    lead_status: newStatus,
                    status_reason: lostReason
                },
                success: function(res) {
                    if (res.status) {
                        toastr.success(res.message);
                        loadLeadList(); // refresh table
                        $('#changeLeadStatusModal').modal('hide');
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to update lead status');
                }
            });
        });

        let deleteId = 0;
        $(document).on('click', '.delete_data', function() {
            deleteId = $(this).data('id');
        });
        $(document).on('click', '#confirmDeleteBtn', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{route('leads.delete')}}",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: deleteId
                },
                type: 'POST',
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#deleteConfirmModal').modal('hide');
                        loadLeadList();
                    }
                },
                error: function(error) {
                    toastr.error('Something went wrong');
                }

            })

        })

        function uploadLeads() {

            let input = $('#excelFile')[0];

            if (!input.files.length) {
                toastr.error('Please select an Excel file');
                return;
            }

            let file = input.files[0];

            console.log("File Object:", file);
            console.log("File Name:", file.name);
            console.log("File Type:", file.type);
            console.log("File Size:", file.size);

            let formData = new FormData();
            formData.append('file', file); // ✅ send actual file
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: "{{ route('leads.import') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,

                success: function(response) {
                    console.log(response);

                    if (response.status) {
                        toastr.success(response.message);
                        $('#excelFile').val(''); // reset file
                        $('#uploadLeadsModal').modal('hide');
                        location.reload();
                    }
                },

                error: function(xhr) {
                    console.log(xhr.responseText);
                    toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                }
            });
        }


        $(document).on('click', '.assign-user', function() {
            const leadId = $(this).data('lead_id');
            $('#leadId').val(leadId);

            $('#assignedUser').html('<option value="">Loading...</option>');
            $.ajax({
                url: "{{ route('leads.assignable_users') }}",
                type: "GET",
                success: function(res) {
                    let options = '<option value="">-- Select User --</option>';

                    res.users.forEach(function(user) {
                        options += `
                    <option value="${user.id}">
                        ${user.name}
                    </option>
                `;
                    });

                    // Inject options into select
                    $('#assignedUser').html(options);

                    $('#assignUserModal').modal('show');
                }
            });
        });


        $(document).on('click', '#saveAssignedUser', function() {

            const leadId = $('#leadId').val(); // hidden input
            const userId = $('#assignedUser').val(); // selected user

            if (!userId) {
                toastr('Please select a user');
                return;
            }

            $.ajax({
                url: "{{ route('leads.assign') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    lead_id: leadId,
                    user_id: userId
                },
                beforeSend: function() {
                    $('#saveAssignedUser').prop('disabled', true).text('Saving...');
                },
                success: function(res) {

                    if (res.status) {

                        // ✅ Update table cell text
                        // $('#assignedUser_' + leadId)
                        //     .text(res.user_name)
                        //     .removeClass('assign-unassigned')
                        //     .addClass('assign-active');

                        // // ✅ Close modal
                        setTimeout(function() {
                            toastr.success(res.message);
                        }, 500);

                        loadLeadList();
                        $('#assignUserModal').modal('hide');
                    }
                },
                error: function() {
                    toastr('Something went wrong');
                },
                complete: function() {
                    $('#saveAssignedUser').prop('disabled', false).text('Save');
                }
            });
        });

        $(document).on('change', '.lead-checkbox', function() {

            const checkedCount = $('.lead-checkbox:checked').length;

            if (checkedCount > 0) {
                $('#bulkAssignBar').removeClass('d-none');
                loadBulkUsers(); // load users only when needed
            } else {
                $('#bulkAssignBar').addClass('d-none');
            }
        });

        function loadBulkUsers() {

            // Prevent reloading again & again
            if ($('#bulkAssignedUser option').length > 1) {
                return;
            }

            $.ajax({
                url: "{{ route('leads.assignable_users') }}",
                type: "GET",
                success: function(res) {

                    let options = '<option value="">-- Select User --</option>';

                    res.users.forEach(user => {
                        options += `<option value="${user.id}">${user.name}</option>`;
                    });

                    $('#bulkAssignedUser').html(options);
                }
            });
        }

        $(document).on('click', '#bulkAssignBtn', function() {

            const userId = $('#bulkAssignedUser').val();

            if (!userId) {
                alert('Please select a user');
                return;
            }

            let leadIds = [];

            $('.lead-checkbox:checked').each(function() {
                leadIds.push($(this).val());
            });

            $.ajax({
                url: "{{ route('leads.bulk_assign') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    user_id: userId,
                    lead_ids: leadIds
                },
                success: function(res) {

                    if (res.status) {
                        setTimeout(function() {
                            toastr.success(res.message);
                        }, 500);
                        $('#bulkAssignBar').addClass('d-none');
                        loadLeadList();
                    }
                },
                error: function() {
                    toastr.error('Something went wrong');
                },
            });
        });
    </script>

</body>

</html>