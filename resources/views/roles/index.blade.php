<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Roles & Permissions | CRM Admin</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --border: #e5e7eb;
            --text-dark: #111827;
            --text-muted: #6b7280;
            --surface: #f8fafc;
        }

        /* ---- Compact baseline, matching the reference density exactly:
           small type, tight padding — not the larger scale used
           elsewhere in this app. Roles & Permissions is deliberately
           denser since it's a control panel people scan quickly, not a
           landing page. ---- */
        .rp-wrap { font-size: 13.5px; }

        .rp-header {
            background: #fff;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        .rp-header h1 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0 0 3px;
        }

        .rp-header p {
            font-size: 12.5px;
            color: var(--text-muted);
            margin: 0;
            max-width: 560px;
        }

        .rp-create-btn {
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            transition: 0.2s;
        }

        .rp-create-btn:hover { background: var(--primary-dark); color: #fff; transform: translateY(-1px); }

        .rp-note {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-bottom: 16px;
            font-size: 12.5px;
            color: #1e3a8a;
        }

        .rp-note i { font-size: 16px; color: #2563eb; margin-top: 1px; }

        /* Deliberately no overflow:hidden on this card or the table inside
           it — that would clip the actions dropdown on the last row, since
           Bootstrap positions the menu as a descendant of whichever
           ancestor here has non-visible overflow. Corners stay square where
           the table meets the card edge; a minor cosmetic trade for a menu
           that always renders fully. */
        .rp-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        }

        .rp-table { width: 100%; border-collapse: collapse; }
        .rp-table th {
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-muted);
            padding: 10px 16px;
            border-bottom: 1px solid var(--border);
        }
        .rp-table td {
            padding: 10px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            font-size: 13px;
        }
        .rp-table tr:last-child td { border-bottom: none; }

        .rp-role-name { font-weight: 700; color: var(--text-dark); font-size: 13.5px; }
        .rp-role-desc { color: var(--text-muted); font-size: 11.5px; margin-top: 2px; }

        .rp-pill {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }
        .rp-pill.protected { background: #dcfce7; color: #15803d; }
        .rp-pill.count { background: #f1f5f9; color: #475569; }

        .rp-actions-btn {
            width: 28px; height: 28px; border-radius: 7px; border: 1px solid var(--border);
            background: #fff; color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; font-size: 12px;
        }
        .rp-actions-btn:hover { background: var(--surface); }

        .rp-dropdown-menu { font-size: 12.5px; border-radius: 10px; box-shadow: 0 12px 32px rgba(15,23,42,.12); border: none; padding: 4px; min-width: 165px; }
        .rp-dropdown-menu .dropdown-item { border-radius: 6px; padding: 7px 10px; display: flex; align-items: center; gap: 8px; }
        .rp-dropdown-menu .dropdown-item i { width: 14px; text-align: center; }
        .rp-dropdown-menu .dropdown-item.text-danger:hover { background: #fef2f2; }

        /* Manage Access modal */
        .perm-modal .modal-dialog { max-width: 760px; }
        .perm-summary { display: flex; justify-content: space-between; align-items: center; font-size: 12.5px; color: var(--text-muted); margin-bottom: 10px; }
        .perm-summary strong { color: var(--text-dark); }

        .perm-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; max-height: 48vh; overflow-y: auto; padding-right: 4px; }
        .perm-group { background: var(--surface); border-radius: 8px; padding: 10px; align-self: start; }
        .perm-group-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
        .perm-group-head .perm-group-title { font-weight: 700; font-size: 12.5px; color: var(--text-dark); }
        .perm-group-head .perm-group-count { font-size: 10.5px; color: var(--text-muted); background: #fff; border: 1px solid var(--border); border-radius: 999px; padding: 1px 7px; }
        .perm-item { display: flex; align-items: center; gap: 7px; font-size: 12px; color: #374151; padding: 3px 0; }
        .perm-item input { width: 13px; height: 13px; }

        .perm-sensitive { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; margin-top: 10px; }
        .perm-sensitive-title { color: #b91c1c; font-weight: 700; font-size: 12px; margin-bottom: 5px; }
        .perm-sensitive .perm-item { color: #7f1d1d; }
        .perm-sensitive small { display: block; color: #991b1b; margin-left: 20px; }

        .perm-footer { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .perm-footer .perm-footer-count { font-size: 12.5px; color: var(--text-muted); }
        .perm-footer .perm-footer-count strong { color: var(--primary); }
    </style>
</head>

<body>

    <div class="container-scroller">
        @include('include.header')

        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')

            <div class="content-wrapper rp-wrap">

                <div class="rp-header">
                    <div>
                        <h1>Roles & Permissions</h1>
                        <p>Create roles and control exactly what each one can see and do. The protected Admin role always has full access.</p>
                    </div>
                    @can('roles.create')
                    <button class="rp-create-btn" id="createRoleBtn" data-toggle="modal" data-target="#createRoleModal">
                        <i class="fa fa-plus"></i>&nbsp; Create role
                    </button>
                    @endcan
                </div>

                <div class="rp-note">
                    <i class="fa fa-info-circle"></i>
                    <div>
                        A new role needs at least one permission to be useful — set that up right when you create it. Renaming or deleting a role never happens for the protected Admin account.
                    </div>
                </div>

                <div class="rp-card">
                    <table class="rp-table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Users</th>
                                <th>Permissions</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="roleTable">
                            <tr><td colspan="4" class="text-center text-muted py-5">Loading…</td></tr>
                        </tbody>
                    </table>
                </div>

                @include('include.footer')
            </div>
        </div>
    </div>

    <!-- Create Role Modal -->
    <div class="modal fade perm-modal" id="createRoleModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create role</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="createRoleForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Role name</label>
                            <input type="text" id="create_name" class="form-control" placeholder="e.g. Sales Manager, Support Agent" required>
                        </div>
                        <div class="form-group">
                            <label>Description <small class="text-muted">(optional)</small></label>
                            <input type="text" id="create_description" class="form-control" placeholder="What this role is for">
                        </div>
                        <label class="d-block mb-2">Permissions</label>
                        <div class="perm-summary">
                            <span>Choose what this role can access</span>
                            <span class="perm-footer-count"><strong id="createSelectedCount">0</strong> / <span id="createTotalCount">0</span> selected</span>
                        </div>
                        <div class="perm-grid" id="createPermGrid"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Info Modal -->
    <div class="modal fade" id="editInfoModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit role</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="editInfoForm">
                    <input type="hidden" id="edit_info_role_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Role name</label>
                            <input type="text" id="edit_info_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description <small class="text-muted">(optional)</small></label>
                            <input type="text" id="edit_info_description" class="form-control">
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

    <!-- Manage Access Modal -->
    <div class="modal fade perm-modal" id="manageAccessModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0">Manage Access</h5>
                        <small class="text-muted">Assign permissions for <strong id="manageAccessRoleName"></strong></small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="manageAccessForm">
                    <input type="hidden" id="manage_access_role_id">
                    <div class="modal-body">
                        <div class="perm-summary">
                            <button type="button" class="btn btn-link p-0" id="selectAllPerms">Select all</button>
                            <span class="perm-footer-count"><strong id="manageSelectedCount">0</strong> / <span id="manageTotalCount">0</span> permissions selected</span>
                        </div>
                        <div class="perm-grid" id="manageAccessGrid"></div>
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
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });

        const esc = value => $('<div>').text(value ?? '').html();
        const canEdit = @json(auth('web')->user()->can('roles.edit'));
        const canDelete = @json(auth('web')->user()->can('roles.delete'));

        function loadRoles() {
            $.get("{{ route('roles.data') }}", function(response) {
                let rows = '';
                response.data.forEach((role) => {
                    const menuId = 'roleMenu' + role.id;
                    let actions = '';
                    if (role.is_protected) {
                        actions = '<span class="rp-pill protected"><i class="fa fa-shield"></i> Protected</span>';
                    } else {
                        let items = '';
                        if (canEdit) {
                            items += `<a class="dropdown-item editInfoBtn" href="#" data-id="${role.id}" data-name="${esc(role.name)}" data-description="${esc(role.description)}"><i class="fa fa-pencil"></i> Edit Info</a>`;
                            items += `<a class="dropdown-item manageAccessBtn" href="#" data-id="${role.id}" data-name="${esc(role.name)}"><i class="fa fa-lock"></i> Manage Access</a>`;
                        }
                        if (canDelete) {
                            items += `<a class="dropdown-item text-danger deleteRoleBtn" href="#" data-id="${role.id}" data-name="${esc(role.name)}"><i class="fa fa-trash"></i> Delete</a>`;
                        }
                        actions = items ? `
                            <div class="dropdown">
                                <button class="rp-actions-btn" type="button" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></button>
                                <div class="dropdown-menu dropdown-menu-right rp-dropdown-menu">${items}</div>
                            </div>` : '<span class="text-muted">—</span>';
                    }

                    rows += `
                        <tr>
                            <td>
                                <div class="rp-role-name">${esc(role.name)}</div>
                                ${role.description ? `<div class="rp-role-desc">${esc(role.description)}</div>` : ''}
                            </td>
                            <td><span class="rp-pill count">${role.users_count} user${role.users_count === 1 ? '' : 's'}</span></td>
                            <td><span class="rp-pill count">${role.permissions_count} / ${role.total_permissions}</span></td>
                            <td class="text-right">${actions}</td>
                        </tr>`;
                });
                $('#roleTable').html(rows || '<tr><td colspan="4" class="text-center text-muted py-5">No roles yet.</td></tr>');
            });
        }

        function renderPermGrid($container, groups, sensitive, granted) {
            let html = '';
            groups.forEach((group) => {
                const groupGranted = group.permissions.filter(p => granted.includes(p.name)).length;
                html += `<div class="perm-group" data-group="${group.module}">
                    <div class="perm-group-head">
                        <span class="perm-group-title">${esc(group.label)}</span>
                        <span class="perm-group-count">${groupGranted}/${group.permissions.length}</span>
                    </div>`;
                group.permissions.forEach((p) => {
                    const checked = granted.includes(p.name) ? 'checked' : '';
                    html += `<label class="perm-item"><input type="checkbox" class="perm-checkbox" value="${p.name}" ${checked}> ${esc(p.label)}</label>`;
                });
                html += `</div>`;
            });
            $container.html(html);

            if (sensitive && sensitive.length) {
                let sHtml = `<div class="perm-sensitive"><div class="perm-sensitive-title"><i class="fa fa-exclamation-triangle"></i> Sensitive permissions</div>`;
                sensitive.forEach((p) => {
                    const checked = granted.includes(p.name) ? 'checked' : '';
                    sHtml += `<label class="perm-item"><input type="checkbox" class="perm-checkbox" value="${p.name}" ${checked}> <b>${esc(p.label)}</b></label>`;
                    if (p.note) sHtml += `<small>${esc(p.note)}</small>`;
                });
                sHtml += `</div>`;
                $container.after(sHtml);
            }
        }

        function updatePermCounts($scope, $selectedEl, $totalEl) {
            const $checkboxes = $scope.find('.perm-checkbox');
            $selectedEl.text($checkboxes.filter(':checked').length);
            $totalEl.text($checkboxes.length);

            $scope.find('.perm-group').each(function() {
                const $group = $(this);
                const $boxes = $group.find('.perm-checkbox');
                $group.find('.perm-group-count').text($boxes.filter(':checked').length + '/' + $boxes.length);
            });
        }

        // ---- Create Role ----
        $(document).on('click', '#createRoleBtn', function() {
            $('#createRoleForm')[0].reset();
            $.get("{{ route('roles.permissions.catalog') }}", function(response) {
                $('#manageAccessModal .perm-sensitive').remove();
                $('#createPermGrid').next('.perm-sensitive').remove();
                renderPermGrid($('#createPermGrid'), response.groups, response.sensitive, []);
                updatePermCounts($('#createRoleModal .modal-body'), $('#createSelectedCount'), $('#createTotalCount'));
            });
        });

        $(document).on('change', '#createPermGrid, #createRoleModal .perm-sensitive', function() {
            updatePermCounts($('#createRoleModal .modal-body'), $('#createSelectedCount'), $('#createTotalCount'));
        });

        $(document).on('submit', '#createRoleForm', function(e) {
            e.preventDefault();
            const permissions = $('#createRoleModal .modal-body .perm-checkbox:checked').map((i, el) => el.value).get();
            if (permissions.length === 0) {
                toastr.error('Select at least one permission');
                return;
            }
            $.post("{{ route('roles.store') }}", {
                name: $('#create_name').val(),
                description: $('#create_description').val(),
                permissions: permissions,
            }, function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    $('#createRoleModal').modal('hide');
                    loadRoles();
                } else {
                    toastr.error(response.message);
                }
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || xhr.responseJSON?.errors?.name?.[0] || 'Something went wrong');
            });
        });

        // ---- Edit Info ----
        $(document).on('click', '.editInfoBtn', function() {
            $('#edit_info_role_id').val($(this).data('id'));
            $('#edit_info_name').val($(this).data('name'));
            $('#edit_info_description').val($(this).data('description'));
            $('#editInfoModal').modal('show');
        });

        $(document).on('submit', '#editInfoForm', function(e) {
            e.preventDefault();
            const id = $('#edit_info_role_id').val();
            $.ajax({
                url: "{{ url('roles') }}/" + id,
                type: 'POST',
                data: { _method: 'PUT', name: $('#edit_info_name').val(), description: $('#edit_info_description').val() },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#editInfoModal').modal('hide');
                        loadRoles();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || xhr.responseJSON?.errors?.name?.[0] || 'Something went wrong');
                }
            });
        });

        // ---- Manage Access ----
        $(document).on('click', '.manageAccessBtn', function() {
            const id = $(this).data('id');
            $('#manage_access_role_id').val(id);
            $('#manageAccessRoleName').text($(this).data('name'));
            $('#manageAccessGrid').next('.perm-sensitive').remove();

            $.get("{{ url('roles') }}/" + id + "/permissions", function(response) {
                renderPermGrid($('#manageAccessGrid'), response.groups, response.sensitive, response.granted);
                updatePermCounts($('#manageAccessModal .modal-body'), $('#manageSelectedCount'), $('#manageTotalCount'));
                $('#manageAccessModal').modal('show');
            });
        });

        $(document).on('change', '#manageAccessGrid, #manageAccessModal .perm-sensitive', function() {
            updatePermCounts($('#manageAccessModal .modal-body'), $('#manageSelectedCount'), $('#manageTotalCount'));
        });

        $(document).on('click', '#selectAllPerms', function() {
            const $boxes = $('#manageAccessModal .modal-body .perm-checkbox');
            const allChecked = $boxes.filter(':checked').length === $boxes.length;
            $boxes.prop('checked', !allChecked);
            updatePermCounts($('#manageAccessModal .modal-body'), $('#manageSelectedCount'), $('#manageTotalCount'));
        });

        $(document).on('submit', '#manageAccessForm', function(e) {
            e.preventDefault();
            const id = $('#manage_access_role_id').val();
            const permissions = $('#manageAccessModal .modal-body .perm-checkbox:checked').map((i, el) => el.value).get();

            $.ajax({
                url: "{{ url('roles') }}/" + id + "/permissions",
                type: 'POST',
                data: { _method: 'PUT', permissions: permissions },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#manageAccessModal').modal('hide');
                        loadRoles();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });

        // ---- Delete ----
        $(document).on('click', '.deleteRoleBtn', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            if (!confirm(`Delete the "${name}" role? Users assigned to it must be reassigned first.`)) return;

            $.ajax({
                url: "{{ url('roles') }}/" + id,
                type: 'POST',
                data: { _method: 'DELETE' },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        loadRoles();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });

        $(document).ready(function() {
            loadRoles();
        });
    </script>

</body>

</html>
