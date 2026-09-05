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

        /* Manage Permissions modal — wider than a typical form modal on
           purpose, so each module card has room to lay its checkboxes out
           without wrapping awkwardly. Create Role stays a plain, narrow
           modal (default Bootstrap width) since it's just two text fields. */
        .perm-modal .modal-dialog { max-width: 920px; }

        .perm-toolbar { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .perm-toolbar .perm-search { font-size: 13px; height: 32px; padding: 4px 10px; flex: 1; }
        .perm-toolbar .btn-link { font-size: 12.5px; white-space: nowrap; padding: 4px 2px; }

        .perm-summary { display: flex; align-items: center; gap: 16px; font-size: 12.5px; color: var(--text-muted); margin-bottom: 10px; }
        .perm-summary strong { color: var(--text-dark); }

        /* A real checkbox, not a text link — its checked/indeterminate
           state always shows whether everything is currently selected,
           and clicking it works both directions (select all / deselect
           all), same affordance as each module's own "Select all". */
        .perm-select-all-toggle { display: flex; align-items: center; gap: 6px; font-weight: 600; color: var(--text-dark); cursor: pointer; }
        .perm-select-all-toggle input { width: 14px; height: 14px; }

        .perm-footer-count { margin-left: auto; }
        .perm-footer-count strong { color: var(--primary); }

        /* Compact module cards, three per row on this wider modal, each
           card's own checkboxes flowing inline (not one-per-line) —
           matches the target mockup instead of a tall single column or a
           cramped, oversized grid. */
        .perm-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 10px; max-height: 50vh; overflow-y: auto; padding-right: 6px; }
        .perm-group { background: var(--surface); border-radius: 8px; padding: 10px 12px; align-self: start; }
        .perm-group-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
        .perm-group-head .perm-group-title { font-weight: 700; font-size: 12.5px; color: var(--text-dark); }
        .perm-group-head .perm-group-count { font-size: 10.5px; color: var(--text-muted); background: #fff; border: 1px solid var(--border); border-radius: 999px; padding: 1px 7px; }

        .perm-select-all-row { display: flex; align-items: center; gap: 6px; font-size: 11.5px; color: var(--primary); font-weight: 600; padding: 2px 0 6px; margin: 0; border-bottom: 1px dashed var(--border); margin-bottom: 6px; cursor: pointer; }
        .perm-select-all-row input { width: 13px; height: 13px; }
        .perm-select-all-row input:disabled { cursor: not-allowed; }
        .perm-select-all-row:has(input:disabled) { color: #92400e; cursor: default; }

        .perm-items-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(95px, 1fr)); gap: 4px 8px; }
        .perm-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #374151; padding: 2px 0; white-space: nowrap; }
        .perm-item input { width: 13px; height: 13px; flex-shrink: 0; }
        .perm-item.perm-hidden { display: none; }
        .perm-group.perm-hidden { display: none; }

        /* A locked permission (only ever roles.* on the protected Admin
           role) — visibly "always on" rather than a checkbox that would
           silently do nothing if unchecked. */
        .perm-item-locked { color: #92400e; font-weight: 600; }
        .perm-item-locked input:disabled { cursor: not-allowed; }
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

    <!-- Create Role Modal — name + description only. Permissions are
         assigned afterward from the role's own "Manage Permissions"
         action, exactly like editing an existing role works. -->
    <div class="modal fade" id="createRoleModal" tabindex="-1" role="dialog" aria-hidden="true">
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
                        <div class="form-group mb-0">
                            <label>Description <small class="text-muted">(optional)</small></label>
                            <input type="text" id="create_description" class="form-control" placeholder="What this role is for">
                        </div>
                        <p class="text-muted mt-3 mb-0" style="font-size:12.5px">
                            <i class="fa fa-info-circle"></i> You'll assign permissions next, from this role's own "Manage Permissions" action.
                        </p>
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

    <!-- Manage Permissions Modal -->
    <div class="modal fade perm-modal" id="manageAccessModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0">Manage Permissions</h5>
                        <small class="text-muted">Assign permissions for <strong id="manageAccessRoleName"></strong></small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="manageAccessForm">
                    <input type="hidden" id="manage_access_role_id">
                    <div class="modal-body">
                        <div class="perm-toolbar">
                            <input type="text" class="form-control perm-search" data-scope="manageAccessModal" placeholder="Search permissions…">
                            <button type="button" class="btn btn-link select-all-visible-btn" data-scope="manageAccessModal">Select all visible</button>
                        </div>
                        <div class="perm-summary">
                            <label class="perm-select-all-toggle">
                                <input type="checkbox" id="selectAllPerms"> Select all
                            </label>
                            <span class="perm-footer-count"><strong id="manageSelectedCount">0</strong> / <span id="manageTotalCount">0</span> permissions selected</span>
                        </div>
                        <div class="perm-grid" id="manageAccessGrid"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Permissions</button>
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
                    // Admin's name/deletion stay off-limits (no Edit Info,
                    // no Delete), but its permissions are genuinely
                    // editable now — Manage Permissions is available here
                    // too, just without those other two actions.
                    let items = '';
                    if (canEdit && !role.is_protected) {
                        items += `<a class="dropdown-item editInfoBtn" href="#" data-id="${role.id}" data-name="${esc(role.name)}" data-description="${esc(role.description)}"><i class="fa fa-pencil"></i> Edit Info</a>`;
                    }
                    if (canEdit) {
                        items += `<a class="dropdown-item manageAccessBtn" href="#" data-id="${role.id}" data-name="${esc(role.name)}" data-protected="${role.is_protected ? 1 : 0}"><i class="fa fa-lock"></i> Manage Permissions</a>`;
                    }
                    if (canDelete && !role.is_protected) {
                        items += `<a class="dropdown-item text-danger deleteRoleBtn" href="#" data-id="${role.id}" data-name="${esc(role.name)}"><i class="fa fa-trash"></i> Delete</a>`;
                    }
                    const actions = items ? `
                        <div class="dropdown">
                            <button class="rp-actions-btn" type="button" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></button>
                            <div class="dropdown-menu dropdown-menu-right rp-dropdown-menu">${items}</div>
                        </div>` : '<span class="text-muted">—</span>';

                    rows += `
                        <tr>
                            <td>
                                <div class="rp-role-name">${esc(role.name)} ${role.is_protected ? '<span class="rp-pill protected"><i class="fa fa-shield"></i> Protected</span>' : ''}</div>
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

        // Each module is a compact card: title + count, a "Select all" row
        // for that module, then its own permissions flowing inline (2-3
        // per row) rather than one per line — keeps 13 modules readable
        // without a tall single column or an oversized, cramped grid.
        // `locked` (only ever non-empty when managing the protected Admin
        // role) names permissions that are always granted and can't be
        // unchecked here — the backend force-re-adds them regardless, this
        // is just making that visible instead of letting a click silently
        // do nothing.
        function renderPermGrid($container, groups, granted, locked) {
            locked = locked || [];
            let html = '';
            groups.forEach((group) => {
                const groupGranted = group.permissions.filter(p => granted.includes(p.name)).length;
                const groupLocked = group.permissions.every(p => locked.includes(p.name)) && group.permissions.length > 0;
                html += `<div class="perm-group" data-group="${group.module}">
                    <div class="perm-group-head">
                        <span class="perm-group-title">${esc(group.label)}</span>
                        <span class="perm-group-count">${groupGranted}/${group.permissions.length}</span>
                    </div>
                    <label class="perm-select-all-row"><input type="checkbox" class="perm-group-select-all" ${groupLocked ? 'checked disabled' : ''}> <span>${groupLocked ? '<i class="fa fa-lock"></i> Always on' : 'Select all'}</span></label>
                    <div class="perm-items-grid">`;
                group.permissions.forEach((p) => {
                    const isLocked = locked.includes(p.name);
                    const checked = (granted.includes(p.name) || isLocked) ? 'checked' : '';
                    const disabled = isLocked ? 'disabled' : '';
                    html += `<label class="perm-item ${isLocked ? 'perm-item-locked' : ''}" data-search="${esc(group.label + ' ' + p.label)}"><input type="checkbox" class="perm-checkbox" value="${p.name}" ${checked} ${disabled}> ${esc(p.label)}</label>`;
                });
                html += `</div></div>`;
            });
            $container.html(html);

            syncGroupSelectAll($container.closest('.modal-body'));
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

            syncGroupSelectAll($scope);
        }

        // Keeps every "Select all" checkbox in sync with the individual
        // checkboxes underneath it — checked when all are on,
        // indeterminate when some are, unchecked when none are. Applies
        // to each module's own toggle and the modal-wide one, so both are
        // always an honest reflection of the current selection and both
        // work in either direction (select all / deselect all).
        function syncGroupSelectAll($scope) {
            $scope.find('.perm-group').each(function() {
                const $boxes = $(this).find('.perm-checkbox');
                const checkedCount = $boxes.filter(':checked').length;
                const $selectAll = $(this).find('.perm-group-select-all');
                $selectAll.prop('checked', checkedCount > 0 && checkedCount === $boxes.length);
                $selectAll.prop('indeterminate', checkedCount > 0 && checkedCount < $boxes.length);
            });

            const $all = $scope.find('.perm-checkbox');
            const allCheckedCount = $all.filter(':checked').length;
            const $topToggle = $scope.find('#selectAllPerms');
            $topToggle.prop('checked', allCheckedCount > 0 && allCheckedCount === $all.length);
            $topToggle.prop('indeterminate', allCheckedCount > 0 && allCheckedCount < $all.length);
        }

        $(document).on('change', '.perm-group-select-all', function() {
            const checked = $(this).is(':checked');
            $(this).closest('.perm-group').find('.perm-checkbox').prop('checked', checked).trigger('change');
        });

        // Search filters individual permission rows by module+label text;
        // a module card hides entirely once none of its rows still match.
        $(document).on('input', '.perm-search', function() {
            const scope = $(this).data('scope');
            const term = $(this).val().trim().toLowerCase();
            const $modal = $('#' + scope);

            $modal.find('.perm-item').each(function() {
                const matches = !term || ($(this).data('search') || '').toLowerCase().includes(term);
                $(this).toggleClass('perm-hidden', !matches);
            });
            $modal.find('.perm-group').each(function() {
                const anyVisible = $(this).find('.perm-item:not(.perm-hidden)').length > 0;
                $(this).toggleClass('perm-hidden', !anyVisible);
            });
        });

        $(document).on('click', '.select-all-visible-btn', function() {
            const scope = $(this).data('scope');
            const $modal = $('#' + scope);
            $modal.find('.perm-item:not(.perm-hidden) .perm-checkbox').prop('checked', true).trigger('change');
        });

        // ---- Create Role ---- (name + description only; permissions are
        // assigned afterward via this role's own "Manage Permissions" action)
        $(document).on('click', '#createRoleBtn', function() {
            $('#createRoleForm')[0].reset();
        });

        $(document).on('submit', '#createRoleForm', function(e) {
            e.preventDefault();
            $.post("{{ route('roles.store') }}", {
                name: $('#create_name').val(),
                description: $('#create_description').val(),
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

        // ---- Manage Permissions ----
        $(document).on('click', '.manageAccessBtn', function() {
            const id = $(this).data('id');
            const isProtected = $(this).data('protected') == 1;
            $('#manage_access_role_id').val(id);
            $('#manageAccessRoleName').text($(this).data('name') + (isProtected ? ' (protected — Roles & Permissions access always stays on)' : ''));
            $('#manageAccessModal .perm-search').val('');

            $.get("{{ url('roles') }}/" + id + "/permissions", function(response) {
                renderPermGrid($('#manageAccessGrid'), response.groups, response.granted, response.locked);
                updatePermCounts($('#manageAccessModal .modal-body'), $('#manageSelectedCount'), $('#manageTotalCount'));
                $('#manageAccessModal').modal('show');
            });
        });

        $(document).on('change', '#manageAccessGrid', function() {
            updatePermCounts($('#manageAccessModal .modal-body'), $('#manageSelectedCount'), $('#manageTotalCount'));
        });

        // A real checkbox — works both directions on its own (ticking it
        // selects everything, unticking deselects everything), no need to
        // compute a toggle direction by hand. Locked (disabled) boxes are
        // excluded — "deselect all" must never even visually uncheck the
        // permission that's permanently guaranteed.
        $(document).on('change', '#selectAllPerms', function() {
            const checked = $(this).is(':checked');
            $('#manageAccessModal .modal-body .perm-checkbox:not(:disabled)').prop('checked', checked);
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
