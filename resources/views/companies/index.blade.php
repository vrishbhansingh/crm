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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .crm-card,.crm-header{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.05)}
        .crm-header{padding:18px 22px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;gap:12px}
        .crm-header h4{margin:0;font-weight:700}.crm-header p{margin:3px 0 0;color:#6b7280;font-size:12px}
        .crm-card{padding:18px}.crm-table{border-collapse:separate;border-spacing:0 7px}
        .crm-table th{border:0;background:#f1f5f9;color:#475569;font-size:12px}.crm-table td{vertical-align:middle;font-size:13px;background:#fff;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb}
        .crm-table td:first-child{border-left:1px solid #e5e7eb;border-radius:9px 0 0 9px}.crm-table td:last-child{border-right:1px solid #e5e7eb;border-radius:0 9px 9px 0}
        .status-pill{display:inline-block;padding:4px 9px;border-radius:999px;background:#eef2ff;color:#4338ca;font-size:11px;text-transform:capitalize}
        .metric{font-size:11px;color:#64748b;margin-right:8px}.company-link{font-weight:700;color:#1d4ed8}
        @media(max-width:768px){.crm-header{align-items:flex-start;flex-direction:column}}
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
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
                const actions = `${canEditCompanies ? `<button class="btn btn-sm btn-outline-primary edit-company" data-id="${company.id}"><i class="fa fa-pencil"></i></button>` : ''} ${canDeleteCompanies ? `<button class="btn btn-sm btn-outline-danger delete-company" data-id="${company.id}"><i class="fa fa-trash"></i></button>` : ''}`;
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
    $(document).ready(loadCompanies);
</script>
</body>
</html>
