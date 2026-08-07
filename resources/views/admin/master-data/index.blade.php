<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Master Data | CRM Admin</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        :root {
            --primary: #2563eb;
            --border: #e5e7eb;
            --text-dark: #111827;
            --text-muted: #6b7280;
        }

        .crm-page-header {
            background: #fff;
            padding: 18px 22px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            border-left: 4px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
        }

        .crm-header-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0d6efd, #00c6ff);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .crm-page-header h4 {
            font-weight: 700;
            font-size: 18px;
            color: var(--text-dark);
            margin: 0;
        }

        .crm-subtitle {
            color: var(--text-muted);
            font-size: 13px;
        }

        .type-list {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
            padding: 10px;
        }

        .type-list button {
            display: block;
            width: 100%;
            text-align: left;
            padding: 10px 14px;
            border: none;
            background: transparent;
            border-radius: 8px;
            font-size: 13.5px;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .type-list button:hover {
            background: #f1f5f9;
        }

        .type-list button.active {
            background: #eef2ff;
            color: var(--primary);
            font-weight: 600;
        }

        .values-panel {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
            padding: 20px;
            min-height: 300px;
        }

        .value-color-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
            border: 1px solid var(--border);
        }

        .status-pill {
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-pill.active {
            background: #dcfce7;
            color: #15803d;
        }

        .status-pill.inactive {
            background: #f3f4f6;
            color: #6b7280;
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
                    <div class="crm-header-icon"><i class="fa fa-list-alt"></i></div>
                    <div>
                        <h4>Master Data</h4>
                        <small class="crm-subtitle">Manage the dropdown values used across leads, orders, and payments — no code changes needed to add a new one.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="type-list" id="typeList">
                            <!-- AJAX: master types -->
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="values-panel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0" id="activeTypeName">Select a type</h5>
                                <button class="btn btn-primary btn-sm" id="addValueBtn" style="display:none;" data-toggle="modal" data-target="#valueModal">
                                    <i class="fa fa-plus"></i> Add Value
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table" id="valuesTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Label</th>
                                            <th>Status</th>
                                            <th>Scope</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Select a master type on the left to see its values.</td>
                                        </tr>
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

    <!-- Add/Edit Value Modal -->
    <div class="modal fade" id="valueModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="valueModalTitle">Add Value</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="valueForm">
                    <div class="modal-body">
                        <input type="hidden" id="value_id">
                        <div class="form-group">
                            <label>Code</label>
                            <input type="text" id="value_code" class="form-control" placeholder="e.g. trade_show">
                            <small class="text-muted">Stored value — used internally, not shown to users.</small>
                        </div>
                        <div class="form-group">
                            <label>Label</label>
                            <input type="text" id="value_label" class="form-control" placeholder="e.g. Trade Show">
                        </div>
                        <div class="form-group">
                            <label>Color (optional)</label>
                            <input type="text" id="value_color" class="form-control" placeholder="#2563eb">
                        </div>
                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" id="value_sort_order" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        let activeTypeId = null;

        function loadTypes() {
            $.get("{{ route('master_data.types') }}", function(response) {
                let html = '';
                response.data.forEach((type) => {
                    html += `<button type="button" class="type-btn" data-id="${type.id}" data-name="${type.name}">${type.name}</button>`;
                });
                $('#typeList').html(html || '<p class="text-muted p-2">No master types found.</p>');

                if (response.data.length > 0) {
                    $('.type-btn').first().click();
                }
            });
        }

        function loadValues(typeId) {
            $.get("{{ route('master_data.values') }}", {
                type_id: typeId
            }, function(response) {
                let rows = '';
                response.data.forEach((v, i) => {
                    const colorDot = v.color ? `<span class="value-color-dot" style="background:${v.color}"></span>` : '';
                    const statusClass = v.is_active ? 'active' : 'inactive';
                    const statusLabel = v.is_active ? 'Active' : 'Inactive';
                    const scope = v.tenant_id ? 'This tenant' : 'Global default';
                    rows += `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${v.code}</td>
                            <td>${colorDot}${v.label}</td>
                            <td><span class="status-pill ${statusClass}">${statusLabel}</span></td>
                            <td><small class="text-muted">${scope}</small></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary editValueBtn"
                                    data-id="${v.id}" data-code="${v.code}" data-label="${v.label}"
                                    data-color="${v.color ?? ''}" data-sort="${v.sort_order}">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary toggleStatusBtn" data-id="${v.id}">
                                    <i class="fa fa-power-off"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger deleteValueBtn" data-id="${v.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                });
                $('#valuesTable tbody').html(rows || '<tr><td colspan="6" class="text-center text-muted">No values yet — add the first one.</td></tr>');
            });
        }

        $(document).on('click', '.type-btn', function() {
            $('.type-btn').removeClass('active');
            $(this).addClass('active');
            activeTypeId = $(this).data('id');
            $('#activeTypeName').text($(this).data('name'));
            $('#addValueBtn').show();
            loadValues(activeTypeId);
        });

        $(document).on('click', '#addValueBtn', function() {
            $('#valueModalTitle').text('Add Value');
            $('#valueForm')[0].reset();
            $('#value_id').val('');
        });

        $(document).on('click', '.editValueBtn', function() {
            $('#valueModalTitle').text('Edit Value');
            $('#value_id').val($(this).data('id'));
            $('#value_code').val($(this).data('code')).prop('readonly', true);
            $('#value_label').val($(this).data('label'));
            $('#value_color').val($(this).data('color'));
            $('#value_sort_order').val($(this).data('sort'));
            $('#valueModal').modal('show');
        });

        $(document).on('submit', '#valueForm', function(e) {
            e.preventDefault();
            const id = $('#value_id').val();
            const payload = {
                master_type_id: activeTypeId,
                code: $('#value_code').val(),
                label: $('#value_label').val(),
                color: $('#value_color').val(),
                sort_order: $('#value_sort_order').val(),
            };

            const url = id ?
                "{{ url('master-data/values') }}/" + id :
                "{{ route('master_data.values.store') }}";

            $.ajax({
                url: url,
                type: 'POST',
                data: id ? Object.assign(payload, {
                    _method: 'PUT'
                }) : payload,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#valueModal').modal('hide');
                        $('#value_code').prop('readonly', false);
                        loadValues(activeTypeId);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });

        $(document).on('click', '.toggleStatusBtn', function() {
            const id = $(this).data('id');
            $.post("{{ url('master-data/values') }}/" + id + "/toggle-status", {}, function(response) {
                if (response.status) {
                    toastr.success('Status updated');
                    loadValues(activeTypeId);
                }
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Something went wrong');
            });
        });

        $(document).on('click', '.deleteValueBtn', function() {
            if (!confirm('Delete this value? Existing records using it will keep the raw code, just without a friendly label.')) {
                return;
            }
            const id = $(this).data('id');
            $.ajax({
                url: "{{ url('master-data/values') }}/" + id,
                type: 'POST',
                data: {
                    _method: 'DELETE'
                },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        loadValues(activeTypeId);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });

        $('#valueModal').on('hidden.bs.modal', function() {
            $('#value_code').prop('readonly', false);
        });

        $(document).ready(function() {
            loadTypes();
        });
    </script>

</body>

</html>
