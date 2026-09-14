<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Companies | CRM</title>
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">
    <style>
        /* Same modernization pattern as Roles & Permissions / Dashboard / Leads / Deals: bigger, roomier cards. */
        .crm-card,.crm-header{background:#fff;border:none;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06)}
        .crm-header{padding:20px 22px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;gap:16px}
        .crm-header h4{margin:0 0 6px;font-weight:700;font-size:18px}.crm-header p{margin:0;color:#6b7280;font-size:14px}
        .crm-header .btn{border-radius:10px;padding:8px 16px;font-weight:600}
        .crm-card{padding:20px}.crm-table{border-collapse:separate;border-spacing:0 10px;font-size:14.5px}
        .crm-table th{border:0;background:#f8fafc;color:#475569;font-size:12.5px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;padding:14px 16px}.crm-table td{vertical-align:middle;font-size:14px;background:#fff;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;padding:16px}
        .crm-table td:first-child{border-left:1px solid #e5e7eb;border-radius:9px 0 0 9px}.crm-table td:last-child{border-right:1px solid #e5e7eb;border-radius:0 9px 9px 0}
        .status-pill{display:inline-block;padding:6px 14px;border-radius:999px;background:#eef2ff;color:#4338ca;font-size:12px;font-weight:600;text-transform:capitalize}
        .metric{font-size:12px;color:#64748b;margin-right:8px}.company-link{font-weight:700;color:#1d4ed8}
        @media(max-width:768px){.crm-header{align-items:flex-start;flex-direction:column}}
        .row-actions{position:relative;display:inline-block}
        .row-actions-btn{width:32px;height:32px;border-radius:8px;border:none;background:transparent;color:#6b7280;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px}
        .row-actions-btn:hover{background:#f1f3f9;color:#1f2937}
        .row-actions-menu{position:absolute;right:0;top:100%;margin-top:4px;min-width:150px;background:#fff;border-radius:12px;box-shadow:0 12px 30px rgba(0,0,0,.15);padding:6px;z-index:50;display:none;text-align:left}
        .row-actions-menu.is-open{display:block}
        .row-actions-menu a,.row-actions-menu button{display:flex;align-items:center;gap:10px;width:100%;padding:9px 12px;border-radius:8px;font-size:13px;color:#374151;text-decoration:none;border:none;background:transparent;text-align:left;cursor:pointer}
        .row-actions-menu a:hover,.row-actions-menu button:hover{background:#f3f4f6}
        .row-actions-menu i{width:16px;text-align:center;color:#6b7280}
        .row-actions-menu .text-danger{color:#dc2626}.row-actions-menu .text-danger i{color:#dc2626}.row-actions-menu .text-danger:hover{background:#fef2f2}

        [data-theme="dark"] .crm-card,[data-theme="dark"] .crm-header{background:#1a1d2b;box-shadow:0 8px 24px rgba(0,0,0,.35)}
        [data-theme="dark"] .crm-header h4,[data-theme="dark"] .crm-table td{color:#eef0f6}
        [data-theme="dark"] .crm-header p{color:#9aa1b5}
        [data-theme="dark"] .crm-table th{background:#232637;color:#9aa1b5}
        [data-theme="dark"] .crm-table td{background:#1a1d2b;border-color:#2a2e40}
        [data-theme="dark"] .company-link{color:#93a4fd}
        [data-theme="dark"] .row-actions-btn{color:#9aa1b5}
        [data-theme="dark"] .row-actions-btn:hover{background:#232637;color:#eef0f6}
        [data-theme="dark"] .row-actions-menu{background:#1e2233;box-shadow:0 16px 36px rgba(0,0,0,.4)}
        [data-theme="dark"] .row-actions-menu a,[data-theme="dark"] .row-actions-menu button{color:#e2e8f5}
        [data-theme="dark"] .row-actions-menu i{color:#93a4fd}
        [data-theme="dark"] .row-actions-menu a:hover,[data-theme="dark"] .row-actions-menu button:hover{background:rgba(255,255,255,.08);color:#fff}
        [data-theme="dark"] .row-actions-menu .text-danger{color:#fca5a5}
        [data-theme="dark"] .row-actions-menu .text-danger i{color:#fca5a5}
        [data-theme="dark"] .row-actions-menu .text-danger:hover{background:rgba(239,68,68,.18)}
    </style>
</head>
<body>
<div class="container-scroller">
    @include('include.header')
    <div class="container-fluid page-body-wrapper">
        @include('include.sidebar')
        <div class="content-wrapper">
            <div class="crm-header">
                <div><h4><i class="fa fa-building-o text-primary mr-2"></i>Companies</h4><p>Customer and prospect organizations in your CRM</p></div>
                @can('companies.create')
                <button class="btn btn-primary btn-sm" id="newCompanyBtn"><i class="fa fa-plus"></i> New Company</button>
                @endcan
            </div>
            <div class="crm-card mb-3">
                <div class="form-row">
                    <div class="form-group col-md-8 mb-0"><input type="text" id="companySearchInput" class="form-control" placeholder="Search name, email, phone…"></div>
                    <div class="form-group col-md-4 mb-0">
                        <select id="companyFilterStatus" class="form-control">
                            <option value="">All statuses</option>
                            <option value="prospect">Prospect</option>
                            <option value="customer">Customer</option>
                            <option value="partner">Partner</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="crm-card">
                <div class="table-responsive">
                    <table class="table crm-table" id="companiesTable">
                        <thead><tr><th>Company</th><th>Contact</th><th>Location</th><th>Owner</th><th>Relationships</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody><tr><td colspan="7" class="text-center text-muted">Loading companies…</td></tr></tbody>
                    </table>
                </div>
                <div id="companyPagination"></div>
            </div>
            @include('include.footer')
        </div>
    </div>
</div>

<div class="modal fade" id="companyModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form id="companyForm">
            <div class="modal-header"><h5 class="modal-title">Company</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <input type="hidden" id="companyId">
                <div class="form-row">
                    <div class="form-group col-md-6"><label>Company Name *</label><input class="form-control" name="name" required maxlength="255"></div>
                    <div class="form-group col-md-6"><label>Legal Name</label><input class="form-control" name="legal_name" maxlength="255"></div>
                    <div class="form-group col-md-4"><label>Email</label><input type="email" class="form-control" name="email"></div>
                    <div class="form-group col-md-4"><label>Phone</label><input class="form-control" name="phone"></div>
                    <div class="form-group col-md-4"><label>Website</label><input type="url" class="form-control" name="website" placeholder="https://"></div>
                    <div class="form-group col-md-4"><label>Industry</label><input class="form-control" name="industry"></div>
                    <div class="form-group col-md-4"><label>Company Size</label><input class="form-control" name="company_size" placeholder="e.g. 51-200"></div>
                    <div class="form-group col-md-4"><label>Status</label><select class="form-control" name="status"><option value="prospect">Prospect</option><option value="customer">Customer</option><option value="partner">Partner</option><option value="inactive">Inactive</option></select></div>
                    <div class="form-group col-md-6"><label>GST Number</label><input class="form-control" name="gst_number"></div>
                    <div class="form-group col-md-6"><label>PAN Number</label><input class="form-control" name="pan_number"></div>
                    <div class="form-group col-md-4"><label>City</label><input class="form-control" name="city"></div>
                    <div class="form-group col-md-4"><label>State</label><input class="form-control" name="state"></div>
                    <div class="form-group col-md-4"><label>Country</label><input class="form-control" name="country"></div>
                    <div class="form-group col-md-6"><label>Owner</label><select class="form-control" name="owner_id"><option value="">Unassigned</option>@foreach($owners as $owner)<option value="{{ $owner->id }}">{{ $owner->name }}</option>@endforeach</select></div>
                    <div class="form-group col-md-6"><label>Pincode</label><input class="form-control" name="pincode"></div>
                    <div class="form-group col-12"><label>Address</label><textarea class="form-control" name="address" rows="2"></textarea></div>
                    <div class="form-group col-12"><label>Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save Company</button></div>
        </form>
    </div></div>
</div>

<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>
<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    const canEditCompanies = {{ Auth::guard('web')->user()->can('companies.edit') ? 'true' : 'false' }};
    const canDeleteCompanies = {{ Auth::guard('web')->user()->can('companies.delete') ? 'true' : 'false' }};
    let companiesById = {};
    let companyPage = 1;
    const esc = value => $('<div>').text(value ?? '').html();

    function loadCompanies() {
        $.get("{{ route('companies.data') }}", {
            search: $('#companySearchInput').val(),
            status: $('#companyFilterStatus').val(),
            page: companyPage,
        }, function(response) {
            companiesById = {};
            let html = '';
            response.data.forEach(company => {
                companiesById[company.id] = company;
                const menuItems = `${canEditCompanies ? `<button class="edit-company" data-id="${company.id}"><i class="fa fa-pencil"></i> Edit</button>` : ''}${canDeleteCompanies ? `<button class="delete-company text-danger" data-id="${company.id}"><i class="fa fa-trash"></i> Delete</button>` : ''}`;
                const actions = menuItems ? `<div class="row-actions"><button type="button" class="row-actions-btn" aria-label="Actions"><i class="fa fa-ellipsis-v"></i></button><div class="row-actions-menu">${menuItems}</div></div>` : '';
                html += `<tr>
                    <td><a class="company-link" href="{{ url('companies') }}/${company.id}">${esc(company.name)}</a><br><small class="text-muted">${esc(company.industry || company.legal_name || '')}</small></td>
                    <td>${esc(company.phone || '-')}<br><small class="text-muted">${esc(company.email || '')}</small></td>
                    <td>${esc([company.city, company.state].filter(Boolean).join(', ') || '-')}</td>
                    <td>${esc(company.owner?.name || 'Unassigned')}</td>
                    <td><span class="metric"><i class="fa fa-user"></i> ${company.contacts_count}</span><span class="metric"><i class="fa fa-filter"></i> ${company.leads_count}</span><span class="metric"><i class="fa fa-briefcase"></i> ${company.deals_count}</span></td>
                    <td><span class="status-pill">${esc(company.status)}</span></td><td>${actions}</td>
                </tr>`;
            });
            $('#companiesTable tbody').html(html || '<tr><td colspan="7" class="text-center text-muted">No companies found</td></tr>');
            renderCrmPagination('#companyPagination', response.meta, page => { companyPage = page; loadCompanies(); });
        }).fail(() => toastr.error('Could not load companies'));
    }

    let companySearchTimer;
    $('#companySearchInput').on('input', function() {
        clearTimeout(companySearchTimer);
        companySearchTimer = setTimeout(() => { companyPage = 1; loadCompanies(); }, 350);
    });
    $('#companyFilterStatus').on('change', function() { companyPage = 1; loadCompanies(); });

    function openCompany(company = null) {
        $('#companyForm')[0].reset(); $('#companyId').val(company?.id || '');
        if (company) Object.keys(company).forEach(key => $(`#companyForm [name="${key}"]`).val(company[key] ?? ''));
        $('#companyModal').modal('show');
    }
    $('#newCompanyBtn').on('click', () => openCompany());
    $(document).on('click', '.edit-company', function() { openCompany(companiesById[$(this).data('id')]); });
    $('#companyForm').on('submit', function(e) {
        e.preventDefault(); const id = $('#companyId').val(); const data = $(this).serialize();
        $.ajax({ url: id ? "{{ url('companies') }}/" + id : "{{ route('companies.store') }}", type: id ? 'PUT' : 'POST', data })
            .done(response => { toastr.success(response.message); $('#companyModal').modal('hide'); loadCompanies(); })
            .fail(xhr => toastr.error(xhr.responseJSON?.message || Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Could not save company'));
    });
    $(document).on('click', '.delete-company', function() {
        if (!confirm('Delete this company?')) return;
        $.ajax({url: "{{ url('companies') }}/" + $(this).data('id'), type:'DELETE'})
            .done(r => { toastr.success(r.message); loadCompanies(); }).fail(xhr => toastr.error(xhr.responseJSON?.message || 'Could not delete company'));
    });
    $(document).on('click', '.row-actions-btn', function(e) {
        e.stopPropagation();
        const menu = $(this).siblings('.row-actions-menu');
        const opening = !menu.hasClass('is-open');
        $('.row-actions-menu').removeClass('is-open');
        if (opening) {
            // position:fixed computed from the button's own rect, not
            // position:absolute relative to the row — .table-responsive's
            // overflow-x:auto implicitly clips overflow-y too (per CSS
            // spec, setting only one axis computes the other to auto),
            // which would otherwise cut the menu off mid-row near the
            // bottom of the table.
            const rect = this.getBoundingClientRect();
            menu.css({ position: 'fixed', top: rect.bottom + 4, left: 'auto', right: window.innerWidth - rect.right }).addClass('is-open');
        }
    });
    $(document).on('click', '.row-actions-menu', function(e) { e.stopPropagation(); });
    $(document).on('click', function() { $('.row-actions-menu').removeClass('is-open'); });

    $(document).ready(loadCompanies);
</script>
</body>
</html>
