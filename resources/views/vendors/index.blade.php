<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>Vendors</title>
    <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}"><link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}"><link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}"><link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
        .page-head{display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:20px;}
        .page-head h1{font-size:24px; font-weight:800; color:var(--ink); margin:0 0 4px; letter-spacing:-.01em;}
        .page-head p{color:var(--muted); font-size:14px; margin:0;}
        .btn-accent{background:var(--accent); border:1px solid var(--accent); color:#fff; border-radius:10px; padding:11px 20px; font-weight:600; font-size:14px; display:inline-flex; align-items:center; gap:8px;}
        .btn-accent:hover{background:var(--accent-dark); border-color:var(--accent-dark); color:#fff;}

        .filter-bar{display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px;}
        .filter-bar input, .filter-bar select{border-radius:10px; border:1px solid var(--border); font-size:13.5px; padding:9px 13px; background:var(--card); color:var(--ink);}
        .filter-bar input{flex:1; min-width:220px;} .filter-bar select{min-width:160px;}
        .filter-bar input:focus, .filter-bar select:focus{border-color:var(--accent); outline:none; box-shadow:0 0 0 3px var(--accent-soft);}

        .vendor-shell{background:var(--card); border-radius:14px; border:1px solid var(--border); box-shadow:0 1px 2px rgba(16,24,40,.04); overflow:hidden;}
        .vendor-row{display:grid; grid-template-columns:minmax(200px,1.4fr) 140px 160px 120px 90px 40px; gap:14px; align-items:center; padding:15px 22px; border-bottom:1px solid var(--line);}
        .vendor-row:last-child{border-bottom:none;} .vendor-row:hover{background:var(--line);}
        .vendor-icon{width:38px; height:38px; border-radius:9px; background:var(--accent-soft); color:var(--accent-dark); display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0;}
        .vendor-main{display:flex; align-items:center; gap:12px; min-width:0;}
        .vendor-name{font-weight:600; color:var(--ink); font-size:14.5px;}
        .vendor-meta{font-size:12px; color:var(--muted);}
        .cell{color:var(--muted); font-size:13.5px;}
        .status-pill{padding:4px 11px; border-radius:999px; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.03em;}
        .status-pill.active{background:var(--active-bg); color:var(--active-fg);}
        .status-pill.inactive{background:var(--inactive-bg); color:var(--inactive-fg);}

        .row-actions{position:relative; display:inline-block;}
        .row-actions-btn{width:32px; height:32px; border-radius:8px; border:none; background:transparent; color:var(--faint); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; font-size:15px;}
        .row-actions-btn:hover{background:var(--line); color:var(--ink);}
        .row-actions-menu{position:absolute; right:0; top:100%; margin-top:4px; min-width:150px; background:var(--card); border:1px solid var(--border); border-radius:10px; box-shadow:0 12px 30px rgba(16,24,40,.15); padding:6px; z-index:50; display:none; text-align:left;}
        .row-actions-menu.is-open{display:block;}
        .row-actions-menu button{display:flex; align-items:center; gap:10px; width:100%; padding:9px 12px; border-radius:8px; font-size:13px; color:var(--ink); border:none; background:transparent; text-align:left; cursor:pointer;}
        .row-actions-menu button:hover{background:var(--line);}
        .row-actions-menu .text-danger{color:#dc2626;} .row-actions-menu .text-danger:hover{background:#fef1f1;}

        .empty-state{padding:60px 20px; text-align:center; color:var(--muted);}
        .empty-state i{font-size:34px; color:var(--faint); display:block; margin-bottom:12px;}
        .empty-state .t{font-weight:600; color:var(--ink); margin-bottom:4px;} .empty-state .d{font-size:13.5px;}

        #vendorModal .modal-content{border-radius:16px; border:none; overflow:hidden;}
        #vendorModal .modal-header{border-bottom:1px solid var(--border); padding:20px 24px;}
        #vendorModal .modal-header h5{font-weight:700; font-size:17px; color:var(--ink);}
        #vendorModal .modal-body{padding:24px; background:var(--card);}
        #vendorModal .modal-footer{border-top:1px solid var(--border); padding:16px 24px;}
        #vendorModal label{font-size:11.5px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.03em; margin-bottom:6px;}
        #vendorModal .form-control{border-radius:9px; border:1px solid var(--border); font-size:14px; padding:9px 12px; height:auto; background:var(--card); color:var(--ink);}
        #vendorModal .form-control:focus{border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-soft); outline:none;}
        [data-theme="dark"] #vendorModal .modal-content{background:var(--card);}

        @media(max-width:900px){
            .vendor-row{grid-template-columns:1fr; gap:4px;}
            .vendor-row > div::before{content:attr(data-label); display:block; font-size:10px; text-transform:uppercase; color:var(--faint); letter-spacing:.03em;}
        }
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">
    <div class="page-head">
        <div>
            <h1>Vendors</h1>
            <p>Suppliers you buy from — used to build purchase orders and RFQs.</p>
        </div>
        @can('vendors.create')
        <button class="btn-accent" id="newVendorBtn"><i class="fa fa-plus"></i> New Vendor</button>
        @endcan
    </div>

    <div class="filter-bar">
        <input type="text" id="filterSearch" placeholder="Search name, contact or GSTIN…">
        <select id="filterStatus">
            <option value="">All statuses</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
    </div>

    <div class="vendor-shell" id="vendorList"><div class="empty-state"><i class="fa fa-spinner fa-spin"></i><div class="t">Loading vendors…</div></div></div>
</div>@include('include.footer')</div></div></div>

<div class="modal fade" id="vendorModal"><div class="modal-dialog modal-lg"><form class="modal-content" id="vendorForm">
    <div class="modal-header"><h5 id="vendorModalTitle">New Vendor</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
        <input type="hidden" id="vendorId">
        <div class="form-row">
            <div class="form-group col-md-6"><label>Name</label><input class="form-control" name="name" id="vendorName" maxlength="255" required placeholder="e.g. Acme Supplies Pvt Ltd"></div>
            <div class="form-group col-md-6"><label>Contact Person</label><input class="form-control" name="contact_person" id="vendorContactPerson" maxlength="255"></div>
            <div class="form-group col-md-6"><label>Email</label><input type="email" class="form-control" name="email" id="vendorEmail" maxlength="255"></div>
            <div class="form-group col-md-6"><label>Phone</label><input class="form-control" name="phone" id="vendorPhone" maxlength="50"></div>
            <div class="form-group col-md-6"><label>GSTIN</label><input class="form-control" name="gst_number" id="vendorGstNumber" maxlength="50"></div>
            <div class="form-group col-md-6"><label>State</label><input class="form-control" name="state" id="vendorState" maxlength="255"></div>
            <div class="form-group col-md-8"><label>Address</label><input class="form-control" name="address" id="vendorAddress" maxlength="255"></div>
            <div class="form-group col-md-4"><label>City</label><input class="form-control" name="city" id="vendorCity" maxlength="255"></div>
            <div class="form-group col-md-4"><label>Pincode</label><input class="form-control" name="pincode" id="vendorPincode" maxlength="20"></div>
            <div class="form-group col-md-4"><label>Payment Terms</label><input class="form-control" name="payment_terms" id="vendorPaymentTerms" maxlength="255" placeholder="e.g. Net 30"></div>
            <div class="form-group col-md-4"><label>Status</label><select class="form-control" name="status" id="vendorStatus"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div>
        </div>
        <div class="alert alert-danger d-none" id="vendorError"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-primary" style="background:#4f46e5;border-color:#4f46e5;">Save Vendor</button></div>
</form></div></div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let vendors = [];
    const canEdit = @json(auth()->user()->can('vendors.edit'));
    const canDelete = @json(auth()->user()->can('vendors.delete'));
    const esc = value => $('<div>').text(value ?? '').html();
    const initials = name => (name || '?').trim().split(/\s+/).slice(0, 2).map(w => w[0]?.toUpperCase() || '').join('');

    function loadVendors() {
        const params = new URLSearchParams({search: $('#filterSearch').val(), status: $('#filterStatus').val()});
        $.get(`{{ route('vendors.data') }}?${params}`, response => { vendors = response.data; render(); });
    }
    function render() {
        if (!vendors.length) {
            $('#vendorList').html(`<div class="empty-state"><i class="fa fa-building-o"></i><div class="t">No vendors yet</div><div class="d">Add a vendor to start creating purchase orders.</div></div>`);
            return;
        }
        $('#vendorList').html(vendors.map(v => `<div class="vendor-row" data-id="${v.id}">
            <div class="vendor-main" data-label="Vendor">
                <div class="vendor-icon">${esc(initials(v.name))}</div>
                <div style="min-width:0;">
                    <div class="vendor-name">${esc(v.name)}</div>
                    <div class="vendor-meta">${v.contact_person ? esc(v.contact_person) : ''}</div>
                </div>
            </div>
            <div class="cell" data-label="GSTIN">${esc(v.gst_number || '—')}</div>
            <div class="cell" data-label="Contact">${esc(v.email || v.phone || '—')}</div>
            <div class="cell" data-label="Terms">${esc(v.payment_terms || '—')}</div>
            <div data-label="Status"><span class="status-pill ${v.status === 'Active' ? 'active' : 'inactive'}">${esc(v.status)}</span></div>
            <div>${(canEdit || canDelete) ? `<div class="row-actions"><button type="button" class="row-actions-btn" aria-label="Actions"><i class="fa fa-ellipsis-v"></i></button><div class="row-actions-menu">${canEdit ? '<button class="editVendor"><i class="fa fa-pencil"></i> Edit</button>' : ''}${canDelete ? '<button class="deleteVendor text-danger"><i class="fa fa-trash"></i> Delete</button>' : ''}</div></div>` : ''}</div>
        </div>`).join(''));
    }

    function openVendor(vendor = null) {
        $('#vendorForm')[0].reset(); $('#vendorError').addClass('d-none'); $('#vendorId').val(vendor?.id || ''); $('#vendorModalTitle').text(vendor ? 'Edit Vendor' : 'New Vendor');
        if (vendor) {
            $('#vendorName').val(vendor.name); $('#vendorContactPerson').val(vendor.contact_person); $('#vendorEmail').val(vendor.email);
            $('#vendorPhone').val(vendor.phone); $('#vendorGstNumber').val(vendor.gst_number); $('#vendorState').val(vendor.state);
            $('#vendorAddress').val(vendor.address); $('#vendorCity').val(vendor.city); $('#vendorPincode').val(vendor.pincode);
            $('#vendorPaymentTerms').val(vendor.payment_terms); $('#vendorStatus').val(vendor.status);
        }
        $('#vendorModal').modal('show');
    }
    $('#newVendorBtn').on('click', () => openVendor());
    $('#filterStatus').on('change', loadVendors);
    let searchTimer;
    $('#filterSearch').on('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(loadVendors, 300); });
    $(document).on('click', '.editVendor', function(){ openVendor(vendors.find(v => v.id === Number($(this).closest('.vendor-row').data('id')))); });
    $(document).on('click', '.deleteVendor', function(){ if(confirm('Delete this vendor?')) $.ajax({url:`{{ url('/vendors') }}/${$(this).closest('.vendor-row').data('id')}`,method:'DELETE',headers:{'X-CSRF-TOKEN':csrf}}).done(loadVendors); });
    $(document).on('click', '.row-actions-btn', function(e){ e.stopPropagation(); const menu=$(this).siblings('.row-actions-menu'); const opening=!menu.hasClass('is-open'); $('.row-actions-menu').removeClass('is-open'); if(opening){ const rect=this.getBoundingClientRect(); menu.css({position:'fixed',top:rect.bottom+4,left:'auto',right:window.innerWidth-rect.right}).addClass('is-open'); } });
    $(document).on('click', '.row-actions-menu', function(e){ e.stopPropagation(); });
    $(document).on('click', function(){ $('.row-actions-menu').removeClass('is-open'); });
    $('#vendorForm').on('submit', function(e){
        e.preventDefault();
        const id = $('#vendorId').val();
        const data = Object.fromEntries(new FormData(this));
        if (id) data._method = 'PUT';
        $.ajax({url: id ? `{{ url('/vendors') }}/${id}` : `{{ route('vendors.store') }}`, method: 'POST', headers: {'X-CSRF-TOKEN': csrf}, data})
            .done(() => { $('#vendorModal').modal('hide'); loadVendors(); })
            .fail(xhr => $('#vendorError').removeClass('d-none').text(xhr.responseJSON?.message || 'Unable to save vendor.'));
    });

    loadVendors();
})();
</script></body></html>
