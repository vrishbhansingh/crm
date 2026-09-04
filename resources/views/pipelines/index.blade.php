<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pipelines | CRM Admin</title>

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

        /* Same modernization pattern as Roles & Permissions / Dashboard /
           Leads / Deals: bigger, roomier white header card. */
        .crm-page-header {
            background: #fff;
            padding: 26px 28px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .crm-header-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: #eff6ff;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
        }

        .crm-page-header h4 {
            font-weight: 700;
            font-size: 22px;
            color: var(--text-dark);
            margin: 0 0 6px;
        }

        .crm-subtitle {
            color: var(--text-muted);
            font-size: 14px;
        }

        .type-list {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
            padding: 10px;
        }

        .pipeline-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            text-align: left;
            padding: 10px 14px;
            border: none;
            background: transparent;
            border-radius: 8px;
            font-size: 13.5px;
            color: var(--text-dark);
            margin-bottom: 4px;
            cursor: pointer;
        }

        .pipeline-item:hover {
            background: #f1f5f9;
        }

        .pipeline-item.active {
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

        .status-pill {
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-pill.active { background: #dcfce7; color: #15803d; }
        .status-pill.inactive { background: #f3f4f6; color: #6b7280; }
        .status-pill.default { background: #eef2ff; color: #4338ca; }
        .status-pill.is-won { background: #dcfce7; color: #15803d; }
        .status-pill.is-lost { background: #fee2e2; color: #b91c1c; }

        .color-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
            border: 1px solid var(--border);
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
                    <div class="crm-header-icon"><i class="fa fa-random"></i></div>
                    <div>
                        <h4>Pipelines</h4>
                        <small class="crm-subtitle">Manage sales pipelines and their stages used on the Deals Kanban board.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="type-list" id="pipelineList">
                            <!-- AJAX: pipelines -->
                        </div>
                        <button class="btn btn-primary btn-sm btn-block mt-2" id="addPipelineBtn" data-toggle="modal" data-target="#pipelineModal">
                            <i class="fa fa-plus"></i> Add Pipeline
                        </button>
                    </div>

                    <div class="col-md-8">
                        <div class="values-panel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0" id="activePipelineName">Select a pipeline</h5>
                                <button class="btn btn-primary btn-sm" id="addStageBtn" style="display:none;" data-toggle="modal" data-target="#stageModal">
                                    <i class="fa fa-plus"></i> Add Stage
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table" id="stagesTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Sort</th>
                                            <th>Flags</th>
                                            <th>Color</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Select a pipeline on the left to see its stages.</td>
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

    <!-- Add/Edit Pipeline Modal -->
    <div class="modal fade" id="pipelineModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pipelineModalTitle">Add Pipeline</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="pipelineForm">
                    <div class="modal-body">
                        <input type="hidden" id="pipeline_id">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" id="pipeline_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" id="pipeline_sort_order" class="form-control" value="0">
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="pipeline_is_default">
                            <label class="form-check-label" for="pipeline_is_default">Default pipeline</label>
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

    <!-- Add/Edit Stage Modal -->
    <div class="modal fade" id="stageModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stageModalTitle">Add Stage</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="stageForm">
                    <div class="modal-body">
                        <input type="hidden" id="stage_id">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" id="stage_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" id="stage_sort_order" class="form-control" value="0">
                        </div>
                        <div class="form-group">
                            <label>Probability (%)</label>
                            <input type="number" min="0" max="100" id="stage_probability" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Color</label>
                            <input type="text" id="stage_color" class="form-control" placeholder="#2563eb">
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="stage_is_won">
                            <label class="form-check-label" for="stage_is_won">This is a Won stage</label>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="stage_is_lost">
                            <label class="form-check-label" for="stage_is_lost">This is a Lost stage</label>
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

        let activePipelineId = null;
        const esc = value => $('<div>').text(value ?? '').html();
        const safeColor = value => /^#[0-9a-f]{3,8}$/i.test(value || '') ? value : '#64748b';

        function loadPipelines() {
            $.get("{{ route('pipelines.data') }}", function(response) {
                let html = '';
                response.data.forEach((p) => {
                    html += `
                        <div class="pipeline-item" data-id="${p.id}" data-name="${esc(p.name)}" data-sort="${p.sort_order}" data-default="${p.is_default ? 1 : 0}">
                            <span>
                                ${esc(p.name)}
                                ${p.is_default ? '<span class="status-pill default">Default</span>' : ''}
                                ${!p.is_active ? '<span class="status-pill inactive">Inactive</span>' : ''}
                            </span>
                            <span>
                                <button class="btn btn-sm btn-outline-primary editPipelineBtn" data-id="${p.id}" data-name="${esc(p.name)}" data-sort="${p.sort_order}" data-default="${p.is_default ? 1 : 0}">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary togglePipelineBtn" data-id="${p.id}">
                                    <i class="fa fa-power-off"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger deletePipelineBtn" data-id="${p.id}" data-deals="${p.deals_count}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </span>
                        </div>`;
                });
                $('#pipelineList').html(html || '<p class="text-muted p-2">No pipelines yet.</p>');

                if (response.data.length > 0 && !activePipelineId) {
                    selectPipeline(response.data[0].id, response.data[0].name);
                }
            });
        }

        function selectPipeline(id, name) {
            activePipelineId = id;
            $('.pipeline-item').removeClass('active');
            $(`.pipeline-item[data-id="${id}"]`).addClass('active');
            $('#activePipelineName').text(name);
            $('#addStageBtn').show();
            loadStages(id);
        }

        function loadStages(pipelineId) {
            $.get("{{ url('pipelines') }}/" + pipelineId + "/stages", function(response) {
                let rows = '';
                response.data.forEach((s, i) => {
                    const colorDot = s.color ? `<span class="color-dot" style="background:${safeColor(s.color)}"></span>${esc(s.color)}` : '-';
                    let flags = '';
                    if (s.is_won) flags += '<span class="status-pill is-won">Won</span> ';
                    if (s.is_lost) flags += '<span class="status-pill is-lost">Lost</span> ';
                    if (!flags) flags = '-';

                    rows += `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${esc(s.name)}</td>
                            <td>${s.sort_order}</td>
                            <td>${flags}</td>
                            <td>${colorDot}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary editStageBtn"
                                    data-id="${s.id}" data-name="${esc(s.name)}" data-sort="${s.sort_order}"
                                    data-probability="${s.probability ?? ''}" data-color="${esc(s.color ?? '')}"
                                    data-won="${s.is_won ? 1 : 0}" data-lost="${s.is_lost ? 1 : 0}">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger deleteStageBtn" data-id="${s.id}" data-deals="${s.deals_count}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                });
                $('#stagesTable tbody').html(rows || '<tr><td colspan="6" class="text-center text-muted">No stages yet — add the first one.</td></tr>');
            });
        }

        $(document).on('click', '.pipeline-item', function(e) {
            if ($(e.target).closest('button').length) {
                return; // let the row's own action buttons (edit/toggle/delete) handle their own click
            }
            selectPipeline($(this).data('id'), $(this).data('name'));
        });

        $(document).on('click', '#addPipelineBtn', function() {
            $('#pipelineModalTitle').text('Add Pipeline');
            $('#pipelineForm')[0].reset();
            $('#pipeline_id').val('');
        });

        $(document).on('click', '.editPipelineBtn', function() {
            $('#pipelineModalTitle').text('Edit Pipeline');
            $('#pipeline_id').val($(this).data('id'));
            $('#pipeline_name').val($(this).data('name'));
            $('#pipeline_sort_order').val($(this).data('sort'));
            $('#pipeline_is_default').prop('checked', $(this).data('default') == 1);
            $('#pipelineModal').modal('show');
        });

        $(document).on('submit', '#pipelineForm', function(e) {
            e.preventDefault();
            const id = $('#pipeline_id').val();
            const payload = {
                name: $('#pipeline_name').val(),
                sort_order: $('#pipeline_sort_order').val(),
                is_default: $('#pipeline_is_default').is(':checked') ? 1 : 0,
            };

            const url = id ? "{{ url('pipelines') }}/" + id : "{{ route('pipelines.store') }}";

            $.ajax({
                url: url,
                type: 'POST',
                data: id ? Object.assign(payload, { _method: 'PUT' }) : payload,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#pipelineModal').modal('hide');
                        loadPipelines();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });

        $(document).on('click', '.togglePipelineBtn', function() {
            const id = $(this).data('id');
            $.post("{{ url('pipelines') }}/" + id + "/toggle-status", {}, function(response) {
                if (response.status) {
                    toastr.success('Status updated');
                    loadPipelines();
                }
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Something went wrong');
            });
        });

        $(document).on('click', '.deletePipelineBtn', function() {
            if (!confirm('Delete this pipeline?')) return;
            const id = $(this).data('id');
            $.ajax({
                url: "{{ url('pipelines') }}/" + id,
                type: 'POST',
                data: { _method: 'DELETE' },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        activePipelineId = null;
                        loadPipelines();
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });

        $(document).on('click', '#addStageBtn', function() {
            $('#stageModalTitle').text('Add Stage');
            $('#stageForm')[0].reset();
            $('#stage_id').val('');
        });

        $(document).on('click', '.editStageBtn', function() {
            $('#stageModalTitle').text('Edit Stage');
            $('#stage_id').val($(this).data('id'));
            $('#stage_name').val($(this).data('name'));
            $('#stage_sort_order').val($(this).data('sort'));
            $('#stage_probability').val($(this).data('probability'));
            $('#stage_color').val($(this).data('color'));
            $('#stage_is_won').prop('checked', $(this).data('won') == 1);
            $('#stage_is_lost').prop('checked', $(this).data('lost') == 1);
            $('#stageModal').modal('show');
        });

        $(document).on('submit', '#stageForm', function(e) {
            e.preventDefault();
            const id = $('#stage_id').val();
            const payload = {
                name: $('#stage_name').val(),
                sort_order: $('#stage_sort_order').val(),
                probability: $('#stage_probability').val(),
                color: $('#stage_color').val(),
                is_won: $('#stage_is_won').is(':checked') ? 1 : 0,
                is_lost: $('#stage_is_lost').is(':checked') ? 1 : 0,
            };

            const url = id
                ? "{{ url('stages') }}/" + id
                : "{{ url('pipelines') }}/" + activePipelineId + "/stages";

            $.ajax({
                url: url,
                type: 'POST',
                data: id ? Object.assign(payload, { _method: 'PUT' }) : payload,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#stageModal').modal('hide');
                        loadStages(activePipelineId);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });

        $(document).on('click', '.deleteStageBtn', function() {
            const dealsCount = $(this).data('deals');
            if (dealsCount > 0) {
                if (!confirm('This stage has deals in it — the server will reject this delete. Try anyway?')) return;
            } else if (!confirm('Delete this stage?')) {
                return;
            }
            const id = $(this).data('id');
            $.ajax({
                url: "{{ url('stages') }}/" + id,
                type: 'POST',
                data: { _method: 'DELETE' },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        loadStages(activePipelineId);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });

        $(document).ready(function() {
            loadPipelines();
        });
    </script>

</body>

</html>
