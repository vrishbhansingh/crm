<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Project Details | CRM</title>

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />


    <style>
        .project-table-wrapper {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        }

        .project-table thead th {
            background: #f5f7fb;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
        }

        .project-table tbody td {
            font-size: 13px;
            text-align: center;
            vertical-align: middle;
        }

        .priority-badge {
            padding: 5px 12px;
            font-size: 11px;
            border-radius: 20px;
            text-transform: capitalize;
        }

        .priority-low {
            background: #e2e3e5;
            color: #383d41;
        }

        .priority-medium {
            background: #fff3cd;
            color: #856404;
        }

        .priority-high {
            background: #f8d7da;
            color: #721c24;
        }

        .priority-urgent {
            background: #dc3545;
            color: #fff;
        }

        .tech-badge {
            padding: 5px 10px;
            font-size: 11px;
            border-radius: 6px;
            background: #eef2ff;
            color: #4b49ac;
        }

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

        .project-edit-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;

            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;

            border-radius: 999px;
            border: 1px solid #2563eb;

            background: #e7f1ff;
            color: #2563eb;

            cursor: pointer;
            transition: all 0.2s ease;
        }

        .project-edit-btn i {
            font-size: 13px;
        }

        .project-edit-btn:hover {
            background: #2563eb;
            color: #fff;
        }

        .project-edit-btn:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.25);
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

    </style>
</head>

<body>

    <div class="container-scroller">

        @include('include.header')

        <div class="container-fluid page-body-wrapper">

            @include('include.sidebar')

            <div class="content-wrapper">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h4>Project Details</h4>
                        <p>Projects created from converted leads</p>
                    </div>
                </div>

                <!-- Project Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="project-table-wrapper">
                            <div class="table-responsive">
                                <table class="table project-table" id="projectTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Project</th>
                                            <th>Tech Stack</th>
                                            <th>Timeline</th>
                                            <th>Priority</th>
                                            <th>Description</th>
                                            @can('orders.edit')
                                            <th>Action</th>
                                            @endcan
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

    @can('orders.edit')
    <div class="modal fade" id="editProjectModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Project</h5>
                    <button type="button" class="close-btn" data-dismiss="modal">&times;</button>
                </div>

                <form method="post" id="editProjectForm">
                    @csrf
                    <div class="modal-body">

                        <input type="hidden" id="edit_project_id" name="id">

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Project Name</label>
                                <input type="text" id="edit_project_name" name="project_name" class="form-control">
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Tech Stack</label>
                                <input type="text" id="edit_tech_stack" name="tech_stack" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Expected Start Date</label>
                                <input type="date" id="edit_expected_start_date" name="expected_start_date" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Expected Delivery Date</label>
                                <input type="date" id="edit_expected_delivery_date" name="expected_delivery_date" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Actual Delivery Date</label>
                                <input type="date" id="edit_actual_delivery_date" name="actual_delivery_date" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Priority</label>
                                <select id="edit_priority" name="priority" class="form-control">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>

                            <div class="col-md-8 form-group">
                                <label>Description</label>
                                <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Project</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    @endcan

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>

    <!-- DataTables -->
    <script src="{{ asset('vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


    <script>
        function loadProjectList() {
            $.ajax({
                url: "{{ route('projects.data') }}",
                type: "GET",
                success: function(response) {

                    let tbody = '';

                    if (response.data && response.data.length > 0) {

                        response.data.forEach((item) => {

                            tbody += `
                    <tr>
                        <td>${item.sl_no}</td>

                        <td>
                            <strong>${item.project_name ?? 'Project #' + item.project_id}</strong><br>
                            <small class="text-muted">
                                Start: ${item.expected_start_date}
                            </small>
                        </td>

                        <td>
                            <span class="tech-badge">
                                ${item.tech_stack ?? '-'}
                            </span>
                        </td>

                        <td>
                            <small>
                                Expected: ${item.expected_delivery_date}<br>
                                Actual: ${item.actual_delivery_date ?? '-'}
                            </small>
                        </td>

                        <td>
                            <span class="priority-badge priority-${item.priority}">
                                ${item.priority}
                            </span>
                        </td>

                        <td>
                            <small>
                                ${item.description ?? '-'}
                            </small>
                        </td>

                        @can('orders.edit')
                        <td>
                            ${item.action}
                        </td>
                        @endcan
                    </tr>`;
                        });

                    } else {
                        tbody = `
                    <tr>
                        <td colspan="7">No projects found</td>
                    </tr>`;
                    }

                    $('#projectTable tbody').html(tbody);
                }
            });
        }

        $(document).ready(function() {
            loadProjectList();
        });

        $(document).on('click', '.editProjectBtn', function() {

            $('#edit_project_id').val($(this).data('id'));
            $('#edit_project_name').val($(this).data('project_name'));
            $('#edit_tech_stack').val($(this).data('tech_stack'));
            $('#edit_expected_start_date').val($(this).data('expected_start_date'));
            $('#edit_expected_delivery_date').val($(this).data('expected_delivery_date'));
            $('#edit_actual_delivery_date').val($(this).data('actual_delivery_date'));
            $('#edit_priority').val($(this).data('priority'));
            $('#edit_description').val($(this).data('description'));
            $('#edit_status').val($(this).data('status'));

            $('#editProjectModal').modal('show');
        });


        $(document).on('submit', '#editProjectForm', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: "{{ route('projects.update') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#editProjectModal').modal('hide');
                        loadProjectList();
                    }
                },
                error: function(err) {
                    toastr.error(err.message);
                }
            });
        })
    </script>

</body>

</html>
