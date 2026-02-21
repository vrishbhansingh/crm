<!DOCTYPE html>
<html lang="en">

<head>
    <!--  meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Crm Admin Panel</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{asset('vendors/feather/feather.css')}}">
    <link rel="stylesheet" href="{{asset('vendors/ti-icons/css/themify-icons.css')}}">
    <link rel="stylesheet" href="{{asset('vendors/css/vendor.bundle.base.css')}}">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="{{asset('vendors/datatables.net-bs4/dataTables.bootstrap4.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('js/select.dataTables.min.css')}}">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="{{asset('css/vertical-layout-light/style.css')}}">
    <link rel="stylesheet" href="{{asset('css/confirm.css')}}">
    <link rel="stylesheet" href="{{asset('css/toast.css')}}">
    <!-- endinject -->

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
            color: #f76473;
        }

        .status-block {
            background: #fdecea;
            color: #c80909;
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

        /* ===============================
   USER PAGE HEADER
================================ */
        .user-page-header {
            background: linear-gradient(135deg, #0d6efd, #00c6ff);
            padding: 20px 24px;
            border-radius: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            color: #ffffff;
        }

        .user-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .user-header-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .user-page-header h4 {
            font-weight: 700;
        }

        .add-user-btn {
            border-radius: 30px;
            padding: 6px 18px;
            font-weight: 600;
            transition: 0.3s ease;
        }

        .add-user-btn:hover {
            background: #ffffff;
            color: #0d6efd;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .user-page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .user-header-right {
                width: 100%;
            }

            .add-user-btn {
                width: 100%;
                text-align: center;
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

                <!-- Page Header -->
                <!-- 🔷 USER PAGE HEADER -->
                <div class="user-page-header mb-4">

                    <div class="user-header-left">
                        <div class="user-header-icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <div>
                            <h4 class="mb-0">User Management</h4>
                            <small class="text-light">Manage all CRM users and roles</small>
                        </div>
                    </div>

                    <div class="user-header-right">
                        <a href="javascript:void(0)"
                            class="btn btn-light btn-sm add-user-btn"
                            data-toggle="modal"
                            data-target="#addUserModal">
                            <i class="fa fa-plus mr-1"></i> Add User
                        </a>
                    </div>

                </div>


                <!-- User Table -->
                <div class="row">
                    <div class="col-12 grid-margin">
                        <div class="card">
                            <div class="card-body">

                                <div class="table-responsive user-table-wrapper">
                                    <table id="userTable" class="table user-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th class="text-center">User Info</th>
                                                <th class="text-center">Contact</th>
                                                <th class="text-center">Role</th>
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
                @include('admin.include.footer')
            </div>
        </div>
    </div>


    <!--Model -->


    <!-- Add User Modal -->
    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered add-user-modal-wrapper">
            <div class="modal-content add-user-modal">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="close-btn" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <!-- Body -->
                <form id="addUserForm" method="post">
                    @csrf
                    <div class="modal-body">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Full name" autocomplete="name-off" autocomplete="off">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Email address" autocomplete="email-off">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control" placeholder="Phone number" autocomplete="phone-off">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Role</label>
                                <select name="role" class="form-control">
                                    <option value="">Select role</option>
                                    <option value="team_leader">Team Leader</option>
                                    <option value="manager">Manager</option>
                                    <option value="caller">Caller</option>
                                    <option value="senior_caller">Senior Caller</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Password">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Block">Block</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light px-4" data-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-save-solid">
                            <i class="fa fa-save mr-2"></i> Save User
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <!-- Status Change Confirmation Modal -->
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
                        Are you sure you want to change this user’s status to <strong id="statusConfirmText">Active/Inactive/Block</strong>?
                    </p>
                    <small class="text-muted">
                        This action cannot be undone.
                    </small>
                </div>
                <div class="form-group col-md-12">
                    <label>Status</label>
                    <select name="status" id="edit_status_modal" class="form-control">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Block">Block</option>
                    </select>
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

    <!-- Edit model -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered add-user-modal-wrapper">
            <div class="modal-content add-user-modal">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="close-btn" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <!-- Body -->
                <form method="POST" id="edit_user">
                    @csrf
                    <input type="hidden" name="id" id="edit_user_id">

                    <div class="modal-body">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Name</label>
                                <input type="text" name="name" id="edit_name" class="form-control">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Phone</label>
                                <input type="text" name="phone" id="edit_phone" class="form-control">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Role</label>
                                <select name="role" id="edit_role" class="form-control">
                                    <option value="team_leader">Team Leader</option>
                                    <option value="manager">Manager</option>
                                    <option value="caller">Caller</option>
                                    <option value="senior_caller">Senior Caller</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Password</label>
                                <input type="text" name="password" id="edit_password" class="form-control">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <select name="status" id="edit_status" class="form-control">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Block">Block</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light px-4" data-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-save-solid">
                            <i class="fa fa-save mr-2"></i> Update User
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>


    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content add-user-modal">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fa fa-trash"></i> Confirm Delete
                    </h5>
                    <button type="button" class="close-btn" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body text-center">
                    <p class="mb-2">
                        Are you sure you want to delete this user?
                    </p>
                    <small class="text-muted">
                        This action cannot be undone.
                    </small>
                </div>

                <!-- Footer -->
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>

            </div>
        </div>
    </div>


    <!-- jQuery -->


    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{asset('vendors/chart.js/Chart.min.js')}}"></script>
    <script src="{{asset('vendors/datatables.net/jquery.dataTables.js')}}"></script>
    <script src="{{asset('vendors/datatables.net-bs4/dataTables.bootstrap4.js')}}"></script>
    <script src="{{asset('js/dataTables.select.min.js')}}"></script>
    <!-- <script src="{{ asset('/website/assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script> -->
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

        function loadUserList() {
            $.ajax({
                url: '{{route("admin.get_user_list")}}',
                type: 'Get',
                success: function(response) {
                    let tbody = '';

                    if (response && Array.isArray(response.data)) {
                        response.data.forEach(item => {
                            let statusClass =
                                item.status === 'Active' ? 'status-active' :
                                item.status === 'Inactive' ? 'status-inactive' :
                                item.status === 'Block' ? 'status-block' : '';
                            tbody += `
                            <tr>
                                <td class="text-center">${item.sl_no}</td>

                                <td class="text-center" style="cursor:pointer;" id="userCell"
                                data-id="${item.id}" data-status="${item.status}">
                                    <strong>${item.name}</strong><br>
                                    <small class="text-muted">${item.email}</small>
                                </td>

                                <td class="text-center">
                                    <small>${item.phone}</small>
                                </td>

                                 <td class="text-center">
                                    <span class="role-badge">
                                        ${
                                            item.role === 'team_leader' ? 'Team Leader' :
                                            item.role === 'manager' ? 'Manager' :
                                            item.role === 'caller' ? 'Caller' :
                                            item.role === 'senior_caller' ? 'Senior Caller' :
                                            item.role
                                        }
                                    </span>
                                </td>
                                 <td class="text-center">
                                    <span 
                                        class="status-badge ${statusClass}"
                                        data-id="${item.id}"
                                        data-status="${item.status}">
                                        <span class="status-dot"></span>
                                        ${item.status}
                                    </span>
                                </td>

                               

                                <td class="text-center">
                                    ${item.action}
                                </td>
                            </tr>
                            `;

                        });
                    } else {
                        tbody = `<tr><td colspan="10" class="text-center">No customer data found</td></tr>`;
                    }
                    $('#userTable tbody').html(tbody);

                },

                error: function(xhr) {
                    toast.error("Something went wrong while loading users");
                }
            });
        }
        $(document).ready(function() {
            loadUserList();
        });


        $(document).on('submit', '#addUserForm', function(e) {
            e.preventDefault();

            let form = this;
            let formData = new FormData(form);

            $.ajax({
                url: '{{ route("admin.add_user") }}',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',

                success: function(response) {
                    toastr.success(response.message);
                    $('#addUserModal').modal('hide');
                    form.reset();
                    loadUserList();
                },

                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Unexpected error occurred');
                    }
                }

            });
        });



        let selectedUserId = null;

        $(document).on('click', '.status-badge', function() {
            selectedUserId = $(this).data('id');
            const currentStatus = $(this).data('status');

            // Preselect current status in dropdown
            $('#edit_status_modal').val(currentStatus);

            // Set confirm text correctly
            $('#statusConfirmText').text(currentStatus);

            $('#statusConfirmModal').modal('show');
        });

        $('#confirmStatusBtn').on('click', function() {

            if (!selectedUserId) return;
            let edit_status_value = $('#edit_status_modal').val();

            $.ajax({
                url: '{{ route("admin.toggle_user_status") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: selectedUserId,
                    edit_status_value: edit_status_value,
                },
                success: function(res) {
                    if (res.status) {
                        showToast(res.message || 'Status updated successfully');
                        $('#statusConfirmModal').modal('hide');
                        loadUserList();
                    } else {
                        showToast('Failed to update status', 'error');
                    }
                },
                error: function() {
                    showToast('Server error', 'error');
                }
            });
        });

        $(document).ready(function() {

            $(document).on('click', '.editbtn', function() {

                // get data from button
                let id = $(this).data('id');
                let name = $(this).data('name');
                let email = $(this).data('email');
                let phone = $(this).data('phone');
                let role = $(this).data('role');
                let status = $(this).data('status');
                let password = $(this).data('backup');


                // set values into edit modal
                $('#edit_user_id').val(id);
                $('#edit_name').val(name);
                $('#edit_email').val(email);
                $('#edit_phone').val(phone);
                $('#edit_role').val(role);
                $('#edit_status').val(status);
                $('#edit_password').val(password);

                // open bootstrap modal
                $('#editUserModal').modal('show');
            });
        });

        $(document).on('submit', '#edit_user', function(e) {
            e.preventDefault();
            let form = this;
            let formData = new FormData(this);
            $.ajax({
                url: "{{route('admin.edit_user')}}",
                data: formData,
                type: "POST",
                contentType: false,
                processData: false,
                success: function(response) {
                    toastr.success(response.message);
                    $('#editUserModal').modal('hide');
                    form.reset();
                    loadUserList();
                },

                error: function(xhr) {

                    toastr.error('Unexpected error occurred');
                }


            });
        });

        let deleteId = 0;
        $(document).on('click', '.delete_data', function() {
            deleteId = $(this).data('id');
            $('#deleteConfirmModal').modal('show');

        })

        $(document).on('click', '#confirmDeleteBtn', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{route('admin.delete_user')}}",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: deleteId
                },
                type: 'POST',
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#deleteConfirmModal').modal('hide');
                        loadUserList();
                    }
                },
                error: function(error) {
                    toastr.error('Something went wrong');
                }

            })
        })

        $(document).on('click', '#userCell', function() {
            const id = $(this).data('id');

            $.ajax({
                url: '{{route("admin.user_login_through_admin")}}',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: id,
                },
                success: function(response) {
                    if (response.status) {
                        setTimeout(() => {}, 1000);
                        window.open("{{ route('user.dashboard') }}", "_blank");
                        window.location.reload();
                    }
                },
                error: function(err) {
                    toastr.error('Something went wrong');
                }
            })
        });
    </script>

</body>

</html>