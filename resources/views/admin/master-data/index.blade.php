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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root{
            --ink:#101828; --muted:#667085; --faint:#98a2b3; --border:#e4e7ec; --line:#eef1f5;
            --bg:#f5f6fa; --card:#fff; --accent:#4f46e5; --accent-soft:#eef2ff; --accent-dark:#4338ca;
            --active-bg:#e7f7ef; --active-fg:#087443; --inactive-bg:#f2f4f7; --inactive-fg:#667085;
        }
        [data-theme="dark"]{
            --ink:#eef0f6; --muted:#9aa1b5; --faint:#71798f; --border:#2a2e40; --line:#252838;
            --bg:#11131c; --card:#181b28; --accent:#818cf8; --accent-soft:#252a4a; --accent-dark:#a5b0ff;
            --active-bg:#173428; --active-fg:#5fd394; --inactive-bg:#242838; --inactive-fg:#9aa1b5;
        }
        body{font-family:"Inter",ui-sans-serif,system-ui,sans-serif;}
        .content-wrapper{background:var(--bg);}

        .page-head{display:flex; align-items:center; gap:16px; margin-bottom:20px;}
        .page-head-icon{
            width:46px; height:46px; border-radius:12px; background:var(--accent-soft); color:var(--accent-dark);
            display:flex; align-items:center; justify-content:center; font-size:19px; flex:none;
        }
        .page-head h1{font-size:22px; font-weight:800; color:var(--ink); margin:0 0 3px; letter-spacing:-.01em;}
        .page-head p{color:var(--muted); font-size:13.5px; margin:0;}

        /* domain row */
        .domain-row{display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;}
        .domain-pill{
            display:flex; align-items:center; gap:9px; padding:10px 18px; border-radius:12px;
            border:1.5px solid var(--border); background:var(--card); cursor:pointer; font-weight:600;
            font-size:13.5px; color:var(--muted); transition:border-color .12s, background .12s, color .12s;
        }
        .domain-pill i{font-size:14px;}
        .domain-pill .count{
            background:var(--line); color:var(--muted); border-radius:999px; padding:1px 8px; font-size:11px;
            font-weight:700; font-variant-numeric:tabular-nums;
        }
        .domain-pill:hover{border-color:var(--accent);}
        .domain-pill.active{border-color:var(--accent); background:var(--accent-soft); color:var(--accent-dark);}
        .domain-pill.active .count{background:var(--accent); color:#fff;}

        .layout-grid{display:grid; grid-template-columns:270px 1fr; gap:20px; align-items:start;}
        @media(max-width:900px){.layout-grid{grid-template-columns:1fr;}}

        .type-list{
            background:var(--card); border-radius:14px; border:1px solid var(--border);
            box-shadow:0 1px 2px rgba(16,24,40,.04); padding:10px;
        }
        .type-list .domain-label{
            font-size:10.5px; font-weight:700; color:var(--faint); text-transform:uppercase;
            letter-spacing:.05em; padding:10px 12px 6px;
        }
        .type-list button{
            display:flex; justify-content:space-between; align-items:center; width:100%; text-align:left;
            padding:10px 12px; border:none; background:transparent; border-radius:9px; font-size:13.5px;
            color:var(--ink); margin-bottom:2px; gap:8px;
        }
        .type-list button .tl-count{font-size:11px; color:var(--faint); font-variant-numeric:tabular-nums;}
        .type-list button:hover{background:var(--line);}
        .type-list button.active{background:var(--accent-soft); color:var(--accent-dark); font-weight:600;}
        .type-list button.active .tl-count{color:var(--accent-dark);}
        .type-list button.virtual i{color:var(--faint); margin-right:6px;}

        .values-panel{
            background:var(--card); border-radius:14px; border:1px solid var(--border);
            box-shadow:0 1px 2px rgba(16,24,40,.04); padding:22px; min-height:340px;
        }
        .values-panel h5{font-size:15.5px; font-weight:700; color:var(--ink); margin:0;}
        .values-panel .sub{font-size:12.5px; color:var(--muted); margin-top:2px;}

        table.table{color:var(--ink); font-size:13.5px;}
        table.table th{
            font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; color:var(--faint);
            font-weight:700; border-top:none; border-bottom:2px solid var(--line) !important;
        }
        table.table td{border-color:var(--line); vertical-align:middle;}

        .value-color-dot{width:12px; height:12px; border-radius:50%; display:inline-block; margin-right:6px; border:1px solid var(--border);}
        .status-pill{padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700;}
        .status-pill.active{background:var(--active-bg); color:var(--active-fg);}
        .status-pill.inactive{background:var(--inactive-bg); color:var(--inactive-fg);}

        .row-actions{position:relative; display:inline-block;}
        .row-actions-btn{width:32px; height:32px; border-radius:8px; border:none; background:transparent; color:var(--faint); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; font-size:16px;}
        .row-actions-btn:hover{background:var(--line); color:var(--ink);}
        .row-actions-menu{position:absolute; right:0; top:100%; margin-top:4px; min-width:170px; background:var(--card); border:1px solid var(--border); border-radius:12px; box-shadow:0 12px 30px rgba(16,24,40,.15); padding:6px; z-index:50; display:none; text-align:left;}
        .row-actions-menu.is-open{display:block;}
        .row-actions-menu a, .row-actions-menu button{display:flex; align-items:center; gap:10px; width:100%; padding:9px 12px; border-radius:8px; font-size:13px; color:var(--ink); text-decoration:none; border:none; background:transparent; text-align:left; cursor:pointer;}
        .row-actions-menu a:hover, .row-actions-menu button:hover{background:var(--line);}
        .row-actions-menu i{width:16px; text-align:center; color:var(--faint);}
        .row-actions-menu .text-danger{color:#dc2626;}
        .row-actions-menu .text-danger:hover{background:#fef1f1;}

        .btn-accent{background:var(--accent); border:1px solid var(--accent); color:#fff; border-radius:9px; padding:8px 16px; font-weight:600; font-size:13.5px;}
        .btn-accent:hover{background:var(--accent-dark); border-color:var(--accent-dark); color:#fff;}

        #valueModal .modal-content, #taxRateModal .modal-content{background:var(--card); color:var(--ink); border-radius:14px; border:none;}
        #valueModal label, #taxRateModal label{font-size:11.5px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.03em;}
        #valueModal .form-control, #taxRateModal .form-control{border-radius:9px; border:1px solid var(--border); background:var(--card); color:var(--ink);}
    </style>
</head>

<body>

    <div class="container-scroller">

        @include('include.header')

        <div class="container-fluid page-body-wrapper">

            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="page-head">
                    <div class="page-head-icon"><i class="fa fa-list-alt"></i></div>
                    <div>
                        <h1>Master Data</h1>
                        <p>Dropdown values and tax rates used across leads, products, orders and payments.</p>
                    </div>
                </div>

                <div class="domain-row" id="domainRow"></div>

                <div class="layout-grid">
                    <div class="type-list" id="typeList"><div class="p-3 text-center text-muted">Loading…</div></div>

                    <div class="values-panel">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 id="activeTypeName">Select a type</h5>
                                <div class="sub" id="activeTypeSub"></div>
                            </div>
                            <button class="btn-accent" id="addValueBtn" style="display:none;">
                                <i class="fa fa-plus"></i> <span id="addValueBtnLabel">Add Value</span>
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table" id="valuesTable">
                                <thead>
                                    <tr>
                                        <th>#</th><th>Code</th><th>Label</th><th>Status</th><th>Scope</th><th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="6" class="text-center text-muted py-4">Pick a category above, then a type on the left.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @include('include.footer')

            </div>
        </div>
    </div>

    <!-- Add/Edit Value Modal (dropdown values) -->
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
                        <div class="form-group mb-0">
                            <label>Sort Order</label>
                            <input type="number" id="value_sort_order" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-accent">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add/Edit Tax Rate Modal — same "type" concept, different shape (needs a % field) -->
    <div class="modal fade" id="taxRateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taxRateModalTitle">Add Tax Rate</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="taxRateForm">
                    <div class="modal-body">
                        <input type="hidden" id="tax_rate_id">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" id="tax_rate_name" class="form-control" placeholder="e.g. GST 18%">
                        </div>
                        <div class="form-group mb-0">
                            <label>Rate (%)</label>
                            <input type="number" step="0.01" min="0" max="100" id="tax_rate_percent" class="form-control" placeholder="18">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-accent">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>

    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });

        const esc = value => $('<div>').text(value ?? '').html();
        const safeColor = value => /^#[0-9a-f]{3,8}$/i.test(value || '') ? value : '#64748b';

        // Every master type is grouped into one of these business domains so
        // the left list only ever shows what's relevant — an admin looking
        // for "Category" doesn't have to scan lead/payment dropdowns to find
        // it. Tax Rates isn't a real MasterType row (it needs a numeric %
        // field the generic system doesn't have) but is injected as a
        // virtual entry into the Products domain so it lives where users
        // actually look for it, not off in a separate top-level tab.
        const DOMAINS = [
            { key: 'leads', label: 'Leads', icon: 'fa-bullseye', types: ['lead_type', 'lead_source', 'lead_status', 'lead_priority', 'lost_reason'] },
            { key: 'products', label: 'Products', icon: 'fa-cubes', types: ['product_category', 'uom'], virtual: [{ key: 'tax_rate', name: 'Tax Rates' }] },
            { key: 'orders', label: 'Orders & Payments', icon: 'fa-shopping-cart', types: ['order_status', 'payment_terms', 'payment_status', 'payment_mode', 'currency', 'project_priority'] },
        ];
        const MAPPED_CODES = DOMAINS.flatMap(d => d.types);

        let allTypes = [];
        let activeDomain = null;
        let activeSelection = null; // { kind: 'type', id, name } | { kind: 'tax_rate' }

        // Any master type this session doesn't know how to categorize (e.g.
        // one added later without updating DOMAINS above) still needs a
        // home — otherwise it would quietly vanish from this screen instead
        // of just being unsorted.
        function domainsWithOther() {
            const hasUnmapped = allTypes.some(t => !MAPPED_CODES.includes(t.code));
            return hasUnmapped ? [...DOMAINS, { key: 'other', label: 'Other', icon: 'fa-ellipsis-h', types: allTypes.filter(t => !MAPPED_CODES.includes(t.code)).map(t => t.code) }] : DOMAINS;
        }

        function renderDomainRow() {
            const html = domainsWithOther().map(d => {
                const count = allTypes.filter(t => d.types.includes(t.code)).length + (d.virtual ? d.virtual.length : 0);
                return `<div class="domain-pill ${d.key === activeDomain ? 'active' : ''}" data-domain="${d.key}">
                    <i class="fa ${d.icon}"></i><span>${esc(d.label)}</span><span class="count">${count}</span>
                </div>`;
            }).join('');
            $('#domainRow').html(html);
        }

        function renderTypeList() {
            const domain = domainsWithOther().find(d => d.key === activeDomain);
            if (!domain) { $('#typeList').html('<div class="p-3 text-center text-muted">No category selected.</div>'); return; }

            const types = allTypes.filter(t => domain.types.includes(t.code));
            let html = `<div class="domain-label">${esc(domain.label)}</div>`;
            types.forEach(t => {
                const isActive = activeSelection?.kind === 'type' && activeSelection.id === t.id;
                html += `<button type="button" class="type-btn ${isActive ? 'active' : ''}" data-id="${t.id}" data-name="${esc(t.name)}"><span>${esc(t.name)}</span></button>`;
            });
            (domain.virtual || []).forEach(v => {
                const isActive = activeSelection?.kind === 'tax_rate';
                html += `<button type="button" class="type-btn virtual ${isActive ? 'active' : ''}" data-virtual="${v.key}"><span><i class="fa fa-percent"></i>${esc(v.name)}</span></button>`;
            });
            $('#typeList').html(html || '<div class="p-3 text-center text-muted">Nothing in this category yet.</div>');
        }

        function selectDomain(key) {
            activeDomain = key;
            activeSelection = null;
            renderDomainRow();
            renderTypeList();
            $('#activeTypeName').text('Select a type');
            $('#activeTypeSub').text('');
            $('#addValueBtn').hide();
            $('#valuesTable tbody').html('<tr><td colspan="6" class="text-center text-muted py-4">Pick a type on the left.</td></tr>');

            // Auto-select the first item in the domain so users land on
            // something useful instead of an empty panel every time.
            const first = $('#typeList .type-btn').first();
            if (first.length) first.click();
        }

        function loadTypes() {
            $.get("{{ route('master_data.types') }}", function(response) {
                allTypes = response.data;
                renderDomainRow();
                selectDomain(DOMAINS[0].key);
            });
        }

        $(document).on('click', '.domain-pill', function() { selectDomain($(this).data('domain')); });

        $(document).on('click', '.type-btn', function() {
            $('.type-btn').removeClass('active');
            $(this).addClass('active');

            if ($(this).data('virtual') === 'tax_rate') {
                activeSelection = { kind: 'tax_rate' };
                $('#activeTypeName').text('Tax Rates');
                $('#activeTypeSub').text('Percentage rates used on products and quotation line items.');
                $('#addValueBtnLabel').text('Add Tax Rate');
                $('#addValueBtn').show();
                renderTaxRatesTable();
                loadTaxRates();
                return;
            }

            const id = $(this).data('id');
            activeSelection = { kind: 'type', id, name: $(this).data('name') };
            $('#activeTypeName').text($(this).data('name'));
            $('#activeTypeSub').text('');
            $('#addValueBtnLabel').text('Add Value');
            $('#addValueBtn').show();
            renderValuesTableHeader();
            loadValues(id);
        });

        function renderValuesTableHeader() {
            $('#valuesTable thead').html('<tr><th>#</th><th>Code</th><th>Label</th><th>Status</th><th>Scope</th><th>Action</th></tr>');
        }

        function loadValues(typeId) {
            $.get("{{ route('master_data.values') }}", { type_id: typeId }, function(response) {
                let rows = '';
                response.data.forEach((v, i) => {
                    const colorDot = v.color ? `<span class="value-color-dot" style="background:${safeColor(v.color)}"></span>` : '';
                    const statusClass = v.is_active ? 'active' : 'inactive';
                    const statusLabel = v.is_active ? 'Active' : 'Inactive';
                    const scope = v.tenant_id ? 'This tenant' : 'Global default';
                    rows += `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${esc(v.code)}</td>
                            <td>${colorDot}${esc(v.label)}</td>
                            <td><span class="status-pill ${statusClass}">${statusLabel}</span></td>
                            <td><small class="text-muted">${scope}</small></td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="row-actions-btn" aria-label="Actions"><i class="fa fa-ellipsis-v"></i></button>
                                    <div class="row-actions-menu">
                                        <button class="editValueBtn"
                                            data-id="${v.id}" data-code="${esc(v.code)}" data-label="${esc(v.label)}"
                                            data-color="${esc(v.color ?? '')}" data-sort="${v.sort_order}">
                                            <i class="fa fa-pencil"></i> Edit
                                        </button>
                                        <button class="toggleStatusBtn" data-id="${v.id}" data-active="${v.is_active ? '1' : '0'}">
                                            <i class="fa fa-power-off"></i> ${v.is_active ? 'Deactivate' : 'Activate'}
                                        </button>
                                        <button class="deleteValueBtn text-danger" data-id="${v.id}">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>`;
                });
                $('#valuesTable tbody').html(rows || '<tr><td colspan="6" class="text-center text-muted py-4">No values yet — add the first one.</td></tr>');
            });
        }

        $('#addValueBtn').on('click', function() {
            if (activeSelection?.kind === 'tax_rate') {
                $('#taxRateModalTitle').text('Add Tax Rate');
                $('#taxRateForm')[0].reset();
                $('#tax_rate_id').val('');
                $('#taxRateModal').modal('show');
                return;
            }
            $('#valueModalTitle').text('Add Value');
            $('#valueForm')[0].reset();
            $('#value_id').val('');
            $('#valueModal').modal('show');
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
                master_type_id: activeSelection?.id,
                code: $('#value_code').val(),
                label: $('#value_label').val(),
                color: $('#value_color').val(),
                sort_order: $('#value_sort_order').val(),
            };
            const url = id ? "{{ url('master-data/values') }}/" + id : "{{ route('master_data.values.store') }}";

            $.ajax({
                url: url, type: 'POST', data: id ? Object.assign(payload, { _method: 'PUT' }) : payload,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#valueModal').modal('hide');
                        $('#value_code').prop('readonly', false);
                        loadValues(activeSelection.id);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: xhr => toastr.error(xhr.responseJSON?.message || 'Something went wrong')
            });
        });

        $(document).on('click', '.toggleStatusBtn', function() {
            const id = $(this).data('id');
            if ($(this).data('active') == '1' && !confirm('Deactivate this value? It will stop appearing in dropdowns using it elsewhere in the app.')) return;
            $.post("{{ url('master-data/values') }}/" + id + "/toggle-status", {}, function(response) {
                if (response.status) { toastr.success('Status updated'); loadValues(activeSelection.id); }
            }).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Something went wrong'));
        });

        $(document).on('click', '.deleteValueBtn', function() {
            if (!confirm('Delete this value? Existing records using it will keep the raw code, just without a friendly label.')) return;
            const id = $(this).data('id');
            $.ajax({
                url: "{{ url('master-data/values') }}/" + id, type: 'POST', data: { _method: 'DELETE' },
                success: function(response) { if (response.status) { toastr.success(response.message); loadValues(activeSelection.id); } },
                error: xhr => toastr.error(xhr.responseJSON?.message || 'Something went wrong')
            });
        });

        $('#valueModal').on('hidden.bs.modal', function() { $('#value_code').prop('readonly', false); });

        // ---- Tax Rates (virtual "type" inside the Products domain) ----
        function renderTaxRatesTable() {
            $('#valuesTable thead').html('<tr><th>#</th><th>Name</th><th>Rate</th><th>Status</th><th>Action</th></tr>');
            $('#valuesTable tbody').html('<tr><td colspan="5" class="text-center text-muted py-4">Loading…</td></tr>');
        }

        function loadTaxRates() {
            $.get("{{ route('master_data.tax_rates.data') }}", function(response) {
                let rows = '';
                response.data.forEach((r, i) => {
                    const statusClass = r.is_active ? 'active' : 'inactive';
                    const statusLabel = r.is_active ? 'Active' : 'Inactive';
                    rows += `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${esc(r.name)}</td>
                            <td>${esc(r.rate_percent)}%</td>
                            <td><span class="status-pill ${statusClass}">${statusLabel}</span></td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="row-actions-btn" aria-label="Actions"><i class="fa fa-ellipsis-v"></i></button>
                                    <div class="row-actions-menu">
                                        <button class="editTaxRateBtn" data-id="${r.id}" data-name="${esc(r.name)}" data-rate="${r.rate_percent}">
                                            <i class="fa fa-pencil"></i> Edit
                                        </button>
                                        <button class="toggleTaxRateBtn" data-id="${r.id}" data-active="${r.is_active ? '1' : '0'}">
                                            <i class="fa fa-power-off"></i> ${r.is_active ? 'Deactivate' : 'Activate'}
                                        </button>
                                        <button class="deleteTaxRateBtn text-danger" data-id="${r.id}">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>`;
                });
                $('#valuesTable tbody').html(rows || '<tr><td colspan="5" class="text-center text-muted py-4">No tax rates yet — add the first one.</td></tr>');
            });
        }

        $(document).on('click', '.editTaxRateBtn', function() {
            $('#taxRateModalTitle').text('Edit Tax Rate');
            $('#tax_rate_id').val($(this).data('id'));
            $('#tax_rate_name').val($(this).data('name'));
            $('#tax_rate_percent').val($(this).data('rate'));
            $('#taxRateModal').modal('show');
        });

        $(document).on('submit', '#taxRateForm', function(e) {
            e.preventDefault();
            const id = $('#tax_rate_id').val();
            const payload = { name: $('#tax_rate_name').val(), rate_percent: $('#tax_rate_percent').val() };
            const url = id ? "{{ url('master-data/tax-rates') }}/" + id : "{{ route('master_data.tax_rates.store') }}";

            $.ajax({
                url: url, type: 'POST', data: id ? Object.assign(payload, { _method: 'PUT' }) : payload,
                success: function(response) {
                    if (response.status) { toastr.success(response.message); $('#taxRateModal').modal('hide'); loadTaxRates(); }
                    else { toastr.error(response.message); }
                },
                error: xhr => toastr.error(xhr.responseJSON?.message || 'Something went wrong')
            });
        });

        $(document).on('click', '.toggleTaxRateBtn', function() {
            const id = $(this).data('id');
            if ($(this).data('active') == '1' && !confirm('Deactivate this tax rate? It will stop appearing in product/quotation pickers.')) return;
            $.post("{{ url('master-data/tax-rates') }}/" + id + "/toggle-status", {}, function(response) {
                if (response.status) { toastr.success('Status updated'); loadTaxRates(); }
            }).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Something went wrong'));
        });

        $(document).on('click', '.deleteTaxRateBtn', function() {
            if (!confirm('Delete this tax rate?')) return;
            const id = $(this).data('id');
            $.ajax({
                url: "{{ url('master-data/tax-rates') }}/" + id, type: 'POST', data: { _method: 'DELETE' },
                success: function(response) { if (response.status) { toastr.success(response.message); loadTaxRates(); } },
                error: xhr => toastr.error(xhr.responseJSON?.message || 'Something went wrong')
            });
        });

        // shared row-actions dropdown (both tables)
        $(document).on('click', '.row-actions-btn', function(e) {
            e.stopPropagation();
            const menu = $(this).siblings('.row-actions-menu');
            const opening = !menu.hasClass('is-open');
            $('.row-actions-menu').removeClass('is-open');
            if (opening) {
                const rect = this.getBoundingClientRect();
                menu.css({ position: 'fixed', top: rect.bottom + 4, left: 'auto', right: window.innerWidth - rect.right }).addClass('is-open');
            }
        });
        $(document).on('click', '.row-actions-menu', function(e) { e.stopPropagation(); });
        $(document).on('click', function() { $('.row-actions-menu').removeClass('is-open'); });

        $(document).ready(function() { loadTypes(); });
    </script>

</body>

</html>
