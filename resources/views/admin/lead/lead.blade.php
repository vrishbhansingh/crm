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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">

    <style>
        /* ===== User Table Wrapper — modernization pass: bigger type,
           roomier row padding, matching Roles/Dashboard. Structure and all
           IDs are untouched, this is a pure visual pass. ===== */
        .user-table-wrapper {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            padding: 18px;
        }

        /* ===== Table Base ===== */
        .user-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
            font-size: 14.5px;
        }

        .user-table thead th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 12.5px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            border: none;
            padding: 14px 16px;
        }

        .user-table tbody tr {
            background: #ffffff;
            transition: box-shadow 0.2s ease;
        }

        .user-table tbody tr:hover {
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
        }

        .user-table tbody td {
            padding: 16px;
            border-top: 1px solid #eef1f6;
            border-bottom: 1px solid #eef1f6;
            font-size: 14px;
            color: #374151;
        }

        .lead-name-link {
            color: #1f2937;
            text-decoration: none;
        }

        .lead-name-link:hover {
            color: #4b49ac;
            text-decoration: underline;
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

        /* Assigned-to cell: the name itself reads as a link (blue, underline
           on hover) rather than a plain label with an icon next to it — an
           icon didn't clearly signal "clickable", and this way it doesn't
           need one to. */
        .assign-user {
            padding: 4px 6px;
            border-radius: 6px;
        }
        .assign-user:hover { background: #f3f4f6; }
        .assign-user:hover .assignee-name { text-decoration: underline; }

        .assign-active .assignee-name { color: #2563eb; font-weight: 600; }
        .assign-unassigned .assignee-name { color: #9ca3af; font-style: italic; font-weight: 500; }

        .form-checkbox {
            width: 21px;
            height: 21px;
        }

        /* ===============================
   LEAD HERO HEADER (Like Track Lead)
================================ */
        /* Same modernization pattern as Roles & Permissions / Dashboard:
           a white, roomy header card with a colored icon circle, rather
           than the previous blue gradient banner — kept the same class
           names so none of this page's JS (which targets other IDs) needed
           to change. */
        .lead-hero-header {
            background: #fff;
            padding: 20px 22px;
            border-radius: 13px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #111827;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            flex-wrap: wrap;
            gap: 16px;
        }

        .lead-hero-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .lead-hero-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
        }

        .lead-hero-header h4 {
            font-weight: 700;
            font-size: 18px;
            color: #111827;
        }

        .lead-hero-header small {
            font-size: 14px;
            color: #6b7280;
        }

        /* Buttons */
        .lead-hero-right .btn {
            border-radius: 10px;
            padding: 8px 16px;
            font-weight: 600;
            transition: 0.2s ease;
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

        /* ===== Lead Stat Tiles ===== */
        .lead-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin: 20px 0 4px;
        }

        .lead-stat-card {
            background: #fff;
            border-radius: 13px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            padding: 20px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .lead-stat-card .lead-stat-value { font-size: 26px; font-weight: 700; color: #111827; line-height: 1.15; }
        .lead-stat-card .lead-stat-label { font-size: 12.5px; color: #6b7280; margin-top: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; }
        .lead-stat-card .lead-stat-icon {
            width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 17px;
        }

        /* ===== Assigned-to cell ===== */
        .assignee-cell { display: inline-flex; align-items: center; }

        /* ===== Row actions (3-dot menu) — shared pattern, reused across
           every list page being migrated off inline button pairs. ===== */
        .row-actions { position: relative; display: inline-block; }
        .row-actions-btn {
            width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent;
            color: #6b7280; display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 16px; line-height: 1;
        }
        .row-actions-btn:hover { background: #f1f3f9; color: #1f2937; }
        .row-actions-menu {
            position: absolute; right: 0; top: 100%; margin-top: 4px; min-width: 150px;
            background: #fff; border-radius: 12px; box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            padding: 6px; z-index: 50; display: none; text-align: left;
        }
        .row-actions-menu.is-open { display: block; }
        .row-actions-menu a, .row-actions-menu button {
            display: flex; align-items: center; gap: 10px; width: 100%; padding: 9px 12px; border-radius: 8px;
            font-size: 13px; color: #374151; text-decoration: none; border: none; background: transparent;
            text-align: left; cursor: pointer;
        }
        .row-actions-menu a:hover, .row-actions-menu button:hover { background: #f3f4f6; }
        .row-actions-menu i { width: 16px; text-align: center; color: #6b7280; }
        .row-actions-menu .text-danger { color: #dc2626; }
        .row-actions-menu .text-danger i { color: #dc2626; }
        .row-actions-menu .text-danger:hover { background: #fef2f2; }

        [data-theme="dark"] .assign-user:hover { background: #232637; }
        [data-theme="dark"] .assign-active .assignee-name { color: #93a4fd; }
        [data-theme="dark"] .assign-unassigned .assignee-name { color: #6b7280; }
        [data-theme="dark"] .row-actions-btn { color: #9aa1b5; }
        [data-theme="dark"] .row-actions-btn:hover { background: #232637; color: #eef0f6; }
        [data-theme="dark"] .row-actions-menu { background: #1e2233; box-shadow: 0 16px 36px rgba(0, 0, 0, 0.4); }
        [data-theme="dark"] .row-actions-menu a, [data-theme="dark"] .row-actions-menu button { color: #e2e8f5; }
        [data-theme="dark"] .row-actions-menu i { color: #93a4fd; }
        [data-theme="dark"] .row-actions-menu a:hover, [data-theme="dark"] .row-actions-menu button:hover { background: rgba(255, 255, 255, 0.08); color: #fff; }
        [data-theme="dark"] .row-actions-menu .text-danger { color: #fca5a5; }
        [data-theme="dark"] .row-actions-menu .text-danger i { color: #fca5a5; }
        [data-theme="dark"] .row-actions-menu .text-danger:hover { background: rgba(239, 68, 68, 0.18); }

        /* Dark mode — this page predates the CSS-variable pattern used on
           newer pages (Dashboard, Deal/Lead detail), so every card surface
           and text color needs an explicit override here rather than a
           handful of variable redefinitions. */
        [data-theme="dark"] .lead-hero-header,
        [data-theme="dark"] .lead-stat-card,
        [data-theme="dark"] .user-table-wrapper,
        [data-theme="dark"] .lead-expand-card,
        [data-theme="dark"] .add-user-modal {
            background: #1a1d2b;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }
        [data-theme="dark"] .lead-hero-header h4,
        [data-theme="dark"] .lead-stat-card .lead-stat-value,
        [data-theme="dark"] .user-table tbody td,
        [data-theme="dark"] .lead-name-link,
        [data-theme="dark"] .add-user-modal .modal-title {
            color: #eef0f6;
        }
        [data-theme="dark"] .lead-hero-header small,
        [data-theme="dark"] .lead-stat-card .lead-stat-label {
            color: #9aa1b5;
        }
        [data-theme="dark"] .user-table thead th {
            background: #232637; color: #9aa1b5;
        }
        [data-theme="dark"] .user-table tbody tr,
        [data-theme="dark"] .user-table tbody td:first-child,
        [data-theme="dark"] .user-table tbody td:last-child {
            background: #1a1d2b; border-color: #2a2e40;
        }
        [data-theme="dark"] .lead-expand-card { border-color: #2a2e40; }
        [data-theme="dark"] .lead-highlight { background: #232637; border-color: #2a2e40; }
        [data-theme="dark"] .lead-expand-label { color: #9aa1b5; }
        [data-theme="dark"] .lead-expand-value { color: #eef0f6; }
        [data-theme="dark"] .add-user-modal .modal-header,
        [data-theme="dark"] .add-user-modal .modal-footer { background: #171a26; border-color: #2a2e40; }
        [data-theme="dark"] .add-user-modal .form-control { background: #232637; border-color: #343850; color: #eef0f6; }

        /* Bulk-import results — summary tiles + detail table inside the upload modal */
        .import-summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
        .import-stat { background: #f8fafc; border-radius: 10px; padding: 12px 8px; text-align: center; }
        .import-stat-value { font-size: 22px; font-weight: 700; color: #1f2937; }
        .import-stat-label { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: .03em; margin-top: 2px; }
        .import-stat.is-success .import-stat-value { color: #16a34a; }
        .import-stat.is-skipped .import-stat-value { color: #b45309; }
        .import-stat.is-failed .import-stat-value { color: #dc2626; }
        .import-result-table th { font-size: 11px; text-transform: uppercase; color: #6b7280; }
        .import-result-table td { font-size: 12.5px; vertical-align: middle; }

        [data-theme="dark"] .import-stat { background: #232637; }
        [data-theme="dark"] .import-stat-value { color: #eef0f6; }
        [data-theme="dark"] .import-stat-label { color: #9aa1b5; }
        [data-theme="dark"] .import-stat.is-success .import-stat-value { color: #4ade80; }
        [data-theme="dark"] .import-stat.is-skipped .import-stat-value { color: #fbbf24; }
        [data-theme="dark"] .import-stat.is-failed .import-stat-value { color: #fca5a5; }
        [data-theme="dark"] .import-result-table th { color: #9aa1b5; border-color: #2a2e40; }
        [data-theme="dark"] .import-result-table td { color: #eef0f6; border-color: #2a2e40; }
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
                        @can('leads.import')
                        <button class="btn btn-outline-secondary mr-2"
                            data-toggle="modal"
                            data-target="#uploadLeadsModal">
                            <i class="fa fa-upload mr-1"></i> Upload Leads
                        </button>
                        @endcan

                        <a href="{{ route('leads.create') }}"
                            class="btn btn-primary">
                            <i class="fa fa-plus mr-1"></i> Add Lead
                        </a>
                    </div>

                </div>

                <div class="lead-stat-grid" id="leadStatGrid">
                    <div class="lead-stat-card">
                        <div>
                            <div class="lead-stat-value" id="statTotalLeads">0</div>
                            <div class="lead-stat-label">Total Leads</div>
                        </div>
                        <div class="lead-stat-icon" style="background:#eff6ff;color:#2563eb;"><i class="fa fa-bullseye"></i></div>
                    </div>
                    <div class="lead-stat-card">
                        <div>
                            <div class="lead-stat-value" id="statNewToday">0</div>
                            <div class="lead-stat-label">New Today</div>
                        </div>
                        <div class="lead-stat-icon" style="background:#f5f3ff;color:#7c3aed;"><i class="fa fa-user-plus"></i></div>
                    </div>
                    <div class="lead-stat-card">
                        <div>
                            <div class="lead-stat-value" id="statConversionRate">0%</div>
                            <div class="lead-stat-label">Conversion Rate</div>
                        </div>
                        <div class="lead-stat-icon" style="background:#ecfdf5;color:#16a34a;"><i class="fa fa-percent"></i></div>
                    </div>
                    <div class="lead-stat-card">
                        <div>
                            <div class="lead-stat-value" id="statFollowUpDue">0</div>
                            <div class="lead-stat-label">Follow-up Due</div>
                        </div>
                        <div class="lead-stat-icon" style="background:#fff7ed;color:#ea580c;"><i class="fa fa-exclamation-circle"></i></div>
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

                                <ul class="nav nav-tabs mb-3" id="leadTabs">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="javascript:void(0)" data-tab="active">
                                            Active Leads <span class="badge badge-light" id="activeLeadCount">0</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="javascript:void(0)" data-tab="converted">
                                            Converted Leads <span class="badge badge-light" id="convertedLeadCount">0</span>
                                        </a>
                                    </li>
                                </ul>

                                <div class="row mb-3" id="leadFilterBar">
                                    <div class="col-md-4 mb-2">
                                        <input type="text" id="leadSearchInput" class="form-control" placeholder="Search name, company, phone, email…">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <select id="leadFilterStatus" class="form-control" data-master-type="lead_status">
                                            <option value="">All statuses</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <select id="leadFilterPriority" class="form-control" data-master-type="lead_priority">
                                            <option value="">All priorities</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <select id="leadFilterSource" class="form-control" data-master-type="lead_source">
                                            <option value="">All sources</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <select id="leadFilterAssignee" class="form-control">
                                            <option value="">All owners</option>
                                        </select>
                                    </div>
                                </div>

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
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            {{-- AJAX DATA --}}
                                        </tbody>
                                    </table>
                                </div>

                                <div id="leadPagination" class="mt-3"></div>

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
                    <div id="uploadStep">
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
                                <br>
                                Name, Email, and Phone are required for every row. A row whose email or phone
                                already exists — in the system, or earlier in the same file — is skipped, not
                                imported twice.
                            </small>
                        </form>

                        <div id="uploadProgressWrap" style="display:none;" class="mt-3">
                            <div class="progress" style="height:8px;">
                                <div class="progress-bar bg-success" id="uploadProgressBar" role="progressbar" style="width:0%"></div>
                            </div>
                            <small class="text-muted" id="uploadProgressText">Uploading… 0%</small>
                        </div>
                    </div>

                    <div id="importResults" style="display:none;">
                        <h6 class="mb-3"><i class="fa fa-check-circle text-success"></i> Lead Import Completed</h6>

                        <div class="import-summary-grid mb-3">
                            <div class="import-stat">
                                <div class="import-stat-value" id="resTotal">0</div>
                                <div class="import-stat-label">Total rows</div>
                            </div>
                            <div class="import-stat is-success">
                                <div class="import-stat-value" id="resSuccess">0</div>
                                <div class="import-stat-label">Uploaded</div>
                            </div>
                            <div class="import-stat is-skipped">
                                <div class="import-stat-value" id="resSkipped">0</div>
                                <div class="import-stat-label">Skipped</div>
                            </div>
                            <div class="import-stat is-failed">
                                <div class="import-stat-value" id="resFailed">0</div>
                                <div class="import-stat-label">Failed</div>
                            </div>
                        </div>

                        <div id="importReportDownloadWrap" style="display:none;" class="mb-3">
                            <a href="#" id="importReportDownloadLink" class="btn btn-outline-primary btn-sm">
                                <i class="fa fa-download"></i> Download Import Report
                            </a>
                        </div>

                        <div id="importRowsWrap" style="display:none;">
                            <div class="table-responsive" style="max-height:280px;overflow-y:auto;">
                                <table class="table table-sm import-result-table">
                                    <thead>
                                        <tr><th>Row</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Reason</th></tr>
                                    </thead>
                                    <tbody id="importRowsBody"></tbody>
                                </table>
                            </div>
                            <small class="text-muted" id="importRowsTruncatedNote" style="display:none;">
                                Showing the first 200 problem rows — download the full report above for the rest.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" id="uploadCancelBtn">Cancel</button>
                    <button type="button" class="btn btn-success btn-sm" id="uploadSubmitBtn" onclick="uploadLeads()">
                        <i class="fa fa-upload"></i> Upload
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal" id="uploadDoneBtn" style="display:none;">Done</button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>
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

        const esc = value => $('<div>').text(value ?? '').html();

        const LEAD_PALETTE = ['#2563eb', '#7c3aed', '#0d9488', '#ea580c', '#db2777', '#16a34a', '#4338ca', '#0891b2'];
        function leadPaletteColor(seed) {
            let hash = 0;
            String(seed || '').split('').forEach(ch => { hash = (hash * 31 + ch.charCodeAt(0)) >>> 0; });
            return LEAD_PALETTE[hash % LEAD_PALETTE.length];
        }

        const LEAD_STATUS_COLORS = {
            'new': '#2563eb', 'hot': '#dc2626', 'warm': '#ea580c', 'cold': '#0891b2',
            'contacted': '#7c3aed', 'interested': '#7c3aed', 'follow_up': '#ea580c',
            'converted': '#16a34a', 'not_interested': '#6b7280', 'closed': '#6b7280',
        };
        function leadStatusColor(status) {
            return LEAD_STATUS_COLORS[String(status || '').toLowerCase()] || leadPaletteColor(status);
        }

        function renderLeadStats(counts) {
            if (!counts) return;
            const total = (counts.active || 0) + (counts.converted || 0);
            const rate = total ? Math.round((counts.converted / total) * 1000) / 10 : 0;
            $('#statTotalLeads').text(total);
            $('#statNewToday').text(counts.newToday || 0);
            $('#statConversionRate').text(rate + '%');
            $('#statFollowUpDue').text(counts.followUpDue || 0);
        }

        let allLeadsData = [];
        let activeLeadTab = 'active';
        let leadCurrentPage = 1;
        let leadSearchDebounce = null;

        function loadLeadList() {
            $.ajax({
                url: '{{route("leads.data")}}',
                type: 'GET',
                data: {
                    converted: activeLeadTab === 'converted' ? 1 : 0,
                    page: leadCurrentPage,
                    search: $('#leadSearchInput').val() || undefined,
                    lead_status: $('#leadFilterStatus').val() || undefined,
                    priority: $('#leadFilterPriority').val() || undefined,
                    lead_source: $('#leadFilterSource').val() || undefined,
                    assigned_to: $('#leadFilterAssignee').val() || undefined,
                },
                success: function(response) {
                    allLeadsData = (response && Array.isArray(response.data)) ? response.data : [];

                    if (response.counts) {
                        $('#activeLeadCount').text(response.counts.active);
                        $('#convertedLeadCount').text(response.counts.converted);
                        renderLeadStats(response.counts);
                    }

                    renderLeadTab();
                    renderCrmPagination('#leadPagination', response.meta, function(page) {
                        leadCurrentPage = page;
                        loadLeadList();
                    });
                },

                error: function() {
                    toastr.error('Something went wrong while loading leads');
                }
            });
        }

        function renderLeadTab() {
            // Tab, search, and filters are already applied server-side —
            // this just renders whatever the current page's response was.
            const filtered = allLeadsData;

            let tbody = '';

            if (filtered.length) {
                filtered.forEach(item => {

                    tbody += `
                            <tr class="lead-row" data-lead-id="${item.id}" style="cursor:pointer;">
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
                                        ${esc(formatLeadStatus(item.lead_type))}
                                    </span>
                                </td>

                                <!-- Contact -->
                                <td class="text-center">
                                    <a href="{{ url('leads') }}/${item.id}" class="lead-name-link"><strong>${esc(capitalizeFirst(item.name) ?? '-')}</strong></a><br>
                                    <small class="text-muted">${esc(item.phone ?? '-')}</small><br>
                                    <small class="text-muted">${esc(item.email ?? '')}</small>
                                </td>

                                <!-- Lead Source -->
                                <td class="text-center">
                                    ${esc(formatLeadStatus(item.lead_source) ?? '-')}
                                </td>

                             <td class="text-center">
                                    <span
                                        id="leadStatus_${item.id}"
                                        class="role-status"
                                        data-id="${item.id}"
                                        data-status="${item.lead_status}"
                                        style="cursor:pointer;background:${leadStatusColor(item.lead_status)}1a;color:${leadStatusColor(item.lead_status)};"
                                    >
                                        <span class="status-dot"></span>
                                        ${esc(formatLeadStatus(item.lead_status))}
                                    </span>
                                </td>

                                <!-- Priority -->
                                <td class="text-center">
                                    <span class="role-badge">
                                        ${esc(capitalizeFirst(item.priority) ?? '-')}
                                    </span>
                                </td>

                                <!-- Follow Up -->
                                <td class="text-center">
                                    ${
                                        item.follow_up_date
                                            ? `${esc(item.follow_up_date)}<br><small>${esc(item.follow_up_time ?? '')}</small>`
                                            : '-'
                                    }
                                </td>
                                 <td class="text-center">
                                    <span
                                        class="assign-user assignee-cell ${
                                            item.assigned_to ? 'assign-active' : 'assign-unassigned'
                                        }"
                                        data-lead_id="${item.id ?? ''}"
                                        data-assigned-id="${item.assigned_to_id ?? ''}"
                                        style="cursor:pointer;"
                                    >
                                        <span class="assignee-name">${esc(item.assigned_to ?? 'Unassigned')}</span>
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
                                                    <div class="lead-expand-value">${esc(item.alternate_phone ?? '-')}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">City</div>
                                                    <div class="lead-expand-value">${esc(item.city ?? '-')}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">State</div>
                                                    <div class="lead-expand-value">${esc(item.state ?? '-')}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Country</div>
                                                    <div class="lead-expand-value">${esc(item.country ?? '-')}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Product</div>
                                                    <div class="lead-expand-value">${esc(item.product ?? '-')}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Service</div>
                                                    <div class="lead-expand-value">${esc(item.service ?? '-')}</div>
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
                                                    <div class="lead-expand-value">${item.deal_id ? 'Yes' : 'No'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Converted At</div>
                                                    <div class="lead-expand-value">${item.converted_at ?? '-'}</div>
                                                </div>

                                                <div class="lead-highlight">
                                                    <div class="lead-expand-label">Status Reason</div>
                                                    <div class="lead-expand-value">${esc(item.status_reason ?? '-')}</div>
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
                                                    <div class="lead-expand-value">${esc(item.follow_up_note ?? '-')}</div>
                                                </div>

                                                <div class="lead-expand-full lead-highlight">
                                                    <div class="lead-expand-label">Remarks</div>
                                                    <div class="lead-expand-value">${esc(item.remarks ?? '-')}</div>
                                                </div>

                                                <div class="lead-expand-full lead-highlight">
                                                    <div class="lead-expand-label">Internal Note</div>
                                                    <div class="lead-expand-value">${esc(item.internal_note ?? '-')}</div>
                                                </div>

                                                <div class="lead-expand-full lead-highlight">
                                                    <div class="lead-expand-label">Requirement</div>
                                                    <div class="lead-expand-value">${esc(item.requirement ?? '-')}</div>
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
                            ${activeLeadTab === 'converted' ? 'No converted leads yet' : 'No lead data found'}
                        </td>
                    </tr>
                `;
            }

            $('#userTable tbody').html(tbody);
        }

        $(document).on('click', '#leadTabs [data-tab]', function() {
            $('#leadTabs .nav-link').removeClass('active');
            $(this).addClass('active');
            activeLeadTab = $(this).data('tab');
            leadCurrentPage = 1;
            loadLeadList();
        });

        $(document).on('input', '#leadSearchInput', function() {
            clearTimeout(leadSearchDebounce);
            leadSearchDebounce = setTimeout(function() {
                leadCurrentPage = 1;
                loadLeadList();
            }, 350);
        });

        $(document).on('change', '#leadFilterStatus, #leadFilterPriority, #leadFilterSource, #leadFilterAssignee', function() {
            leadCurrentPage = 1;
            loadLeadList();
        });

        function loadLeadAssigneeFilterOptions() {
            $.get("{{ route('leads.assignable_users') }}", function(response) {
                (response.users || []).forEach(function(user) {
                    $('#leadFilterAssignee').append(`<option value="${user.id}">${esc(user.name)}</option>`);
                });
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
                        $select.append(`<option value="${esc(option.code)}">${esc(option.label)}</option>`);
                    });
                });
            });
        }

        $(document).ready(function() {
            loadLeadList();
            loadMasterDropdowns();
            loadLeadAssigneeFilterOptions();
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

        // Clicking anywhere on a lead's row opens its detail page — except
        // when the click is on something interactive within the row
        // (checkbox, expand icon, status pill, assign badge, action menu),
        // which each already handle their own click.
        $(document).on('click', '#userTable tbody tr.lead-row', function(e) {
            if ($(e.target).closest('a, button, input, .toggle-row, .role-status, .assign-user, .row-actions').length) {
                return;
            }
            const id = $(this).data('lead-id');
            if (id) {
                window.location = "{{ url('leads') }}/" + id;
            }
        });

        // Row action menus (3-dot Edit/Delete) — shared pattern, one open
        // at a time, closes on any click elsewhere.
        $(document).on('click', '.row-actions-btn', function(e) {
            e.stopPropagation();
            const menu = $(this).siblings('.row-actions-menu');
            const opening = !menu.hasClass('is-open');
            $('.row-actions-menu').removeClass('is-open');
            if (opening) {
                // position:fixed from the button's own rect — .table-responsive's
                // overflow-x:auto implicitly clips overflow-y too, which would
                // otherwise cut the menu off mid-row.
                const rect = this.getBoundingClientRect();
                menu.css({ position: 'fixed', top: rect.bottom + 4, left: 'auto', right: window.innerWidth - rect.right }).addClass('is-open');
            }
        });
        $(document).on('click', '.row-actions-menu', function(e) { e.stopPropagation(); });
        $(document).on('click', function() { $('.row-actions-menu').removeClass('is-open'); });

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

        function resetUploadModal() {
            $('#uploadStep').show();
            $('#importResults').hide();
            $('#uploadProgressWrap').hide();
            $('#uploadProgressBar').css('width', '0%');
            $('#uploadProgressText').text('Uploading… 0%');
            $('#excelFile').val('');
            $('#uploadSubmitBtn').show().prop('disabled', false);
            $('#uploadCancelBtn').show();
            $('#uploadDoneBtn').hide();
            $('#importReportDownloadWrap').hide();
            $('#importRowsWrap').hide();
            $('#importRowsTruncatedNote').hide();
        }
        $('#uploadLeadsModal').on('show.bs.modal', resetUploadModal);

        function importStatusBadge(status) {
            const cls = status === 'Failed' ? 'badge-danger' : 'badge-warning';
            return `<span class="badge ${cls}">${esc(status)}</span>`;
        }

        function renderImportResults(response) {
            const s = response.summary;
            $('#resTotal').text(s.total);
            $('#resSuccess').text(s.success);
            $('#resSkipped').text(s.skipped);
            $('#resFailed').text(s.failed);

            if (response.report_url) {
                $('#importReportDownloadLink').attr('href', response.report_url);
                $('#importReportDownloadWrap').show();
            }

            if (response.rows && response.rows.length) {
                $('#importRowsBody').html(response.rows.map(r => `
                    <tr>
                        <td>${r.row}</td>
                        <td>${esc(r.name ?? '—')}</td>
                        <td>${esc(r.email ?? '—')}</td>
                        <td>${esc(r.phone ?? '—')}</td>
                        <td>${importStatusBadge(r.status)}</td>
                        <td>${esc(r.reason ?? '')}</td>
                    </tr>`).join(''));
                $('#importRowsWrap').show();
                if (response.rows_truncated) $('#importRowsTruncatedNote').show();
            }

            $('#uploadStep').hide();
            $('#importResults').show();
            $('#uploadSubmitBtn').hide();
            $('#uploadCancelBtn').hide();
            $('#uploadDoneBtn').show();

            toastr.success(response.message);
        }

        function uploadLeads() {

            let input = $('#excelFile')[0];

            if (!input.files.length) {
                toastr.error('Please select an Excel file');
                return;
            }

            let file = input.files[0];

            let formData = new FormData();
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');

            $('#uploadSubmitBtn').prop('disabled', true);
            $('#uploadProgressWrap').show();

            $.ajax({
                url: "{{ route('leads.import') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                // Genuine upload-progress feedback for large files — jQuery
                // doesn't expose this itself, so the underlying XHR's own
                // upload.progress event is wired up by hand here.
                xhr: function() {
                    const xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const pct = Math.round((e.loaded / e.total) * 100);
                                $('#uploadProgressBar').css('width', pct + '%');
                                $('#uploadProgressText').text(pct < 100 ? ('Uploading… ' + pct + '%') : 'Processing rows…');
                            }
                        });
                    }
                    return xhr;
                },

                success: function(response) {
                    if (!response.status) {
                        toastr.error(response.message || 'Upload failed.');
                        $('#uploadSubmitBtn').prop('disabled', false);
                        $('#uploadProgressWrap').hide();
                        return;
                    }

                    renderImportResults(response);
                    loadLeadList();
                },

                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                    $('#uploadSubmitBtn').prop('disabled', false);
                    $('#uploadProgressWrap').hide();
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
                        ${esc(user.name)}
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
                        options += `<option value="${user.id}">${esc(user.name)}</option>`;
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
