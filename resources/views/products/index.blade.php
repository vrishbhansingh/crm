<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>Products</title>
    <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}"><link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}"><link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}"><link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .crm-page-header{background:#fff;padding:20px 22px;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06);margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px}
        .crm-page-header h3{margin:0 0 6px;font-weight:700;font-size:18px;color:#111827}.crm-page-header p{margin:0;color:#6b7280;font-size:14px}
        .crm-page-header .btn{border-radius:10px;padding:8px 16px;font-weight:600}
        .product-shell{background:#fff;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06);overflow:hidden}
        .product-row{display:grid;grid-template-columns:minmax(200px,1fr) 110px 130px 120px 110px 90px;gap:14px;align-items:center;padding:16px 22px;border-bottom:1px solid #edf2f7;font-size:14.5px}
        .product-row:hover{background:#f8fbff}.product-name{font-weight:600;color:#1f2937;font-size:15px}.product-meta{font-size:12.5px;color:#6b7280}
        .filter-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px}
        .status-pill{padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600}
        .status-pill.active{background:#dcfce7;color:#15803d}.status-pill.inactive{background:#f3f4f6;color:#6b7280}
        @media(max-width:900px){.product-row{grid-template-columns:1fr}.product-cell-secondary{grid-column:1}}
        .row-actions{position:relative;display:inline-block}
        .row-actions-btn{width:32px;height:32px;border-radius:8px;border:none;background:transparent;color:#6b7280;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px}
        .row-actions-btn:hover{background:#f1f3f9;color:#1f2937}
        .row-actions-menu{position:absolute;right:0;top:100%;margin-top:4px;min-width:150px;background:#fff;border-radius:12px;box-shadow:0 12px 30px rgba(0,0,0,.15);padding:6px;z-index:50;display:none;text-align:left}
        .row-actions-menu.is-open{display:block}
        .row-actions-menu a,.row-actions-menu button{display:flex;align-items:center;gap:10px;width:100%;padding:9px 12px;border-radius:8px;font-size:13px;color:#374151;text-decoration:none;border:none;background:transparent;text-align:left;cursor:pointer}
        .row-actions-menu a:hover,.row-actions-menu button:hover{background:#f3f4f6}
        .row-actions-menu i{width:16px;text-align:center;color:#6b7280}
        .row-actions-menu .text-danger{color:#dc2626}.row-actions-menu .text-danger i{color:#dc2626}.row-actions-menu .text-danger:hover{background:#fef2f2}

        [data-theme="dark"] .crm-page-header,[data-theme="dark"] .product-shell,[data-theme="dark"] .card{background:#1a1d2b;box-shadow:0 8px 24px rgba(0,0,0,.35)}
        [data-theme="dark"] .crm-page-header h3{color:#eef0f6}
        [data-theme="dark"] .crm-page-header p{color:#9aa1b5}
        [data-theme="dark"] .product-row{border-bottom-color:#2a2e40}
        [data-theme="dark"] .product-row:hover{background:#20233a}
        [data-theme="dark"] .product-name{color:#eef0f6}
        [data-theme="dark"] .product-meta{color:#9aa1b5}
        [data-theme="dark"] .status-pill.active{background:rgba(21,128,61,.25);color:#4ade80}
        [data-theme="dark"] .status-pill.inactive{background:#232637;color:#9aa1b5}
        [data-theme="dark"] .row-actions-btn{color:#9aa1b5}
        [data-theme="dark"] .row-actions-btn:hover{background:#232637;color:#eef0f6}
        [data-theme="dark"] .row-actions-menu{background:#1e2233;box-shadow:0 16px 36px rgba(0,0,0,.4)}
        [data-theme="dark"] .row-actions-menu a,[data-theme="dark"] .row-actions-menu button{color:#e2e8f5}
        [data-theme="dark"] .row-actions-menu i{color:#93a4fd}
        [data-theme="dark"] .row-actions-menu a:hover,[data-theme="dark"] .row-actions-menu button:hover{background:rgba(255,255,255,.08);color:#fff}
        [data-theme="dark"] .row-actions-menu .text-danger{color:#fca5a5}
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">
    <div class="crm-page-header"><div><h3>Products</h3><p>Catalog used to build quotations — price, tax and unit are set once here.</p></div>@can('products.create')<button class="btn btn-primary" id="newProductBtn"><i class="fa fa-plus"></i> New Product</button>@endcan</div>
    <div class="card mb-3" style="border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06);border:none"><div class="card-body filter-grid">
        <input type="text" id="filterSearch" class="form-control" placeholder="Search name or SKU…">
        <select id="filterStatus" class="form-control"><option value="">All statuses</option><option value="Active">Active</option><option value="Inactive">Inactive</option></select>
    </div></div>
    <div class="product-shell" id="productList"><div class="p-4 text-center text-muted">Loading products…</div></div>
</div>@include('include.footer')</div></div></div>

<div class="modal fade" id="productModal"><div class="modal-dialog modal-lg"><form class="modal-content" id="productForm"><div class="modal-header"><h5 id="productModalTitle">New Product</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><input type="hidden" id="productId"><div class="form-row">
    <div class="form-group col-md-8"><label>Name</label><input class="form-control" name="name" id="productName" maxlength="255" required></div>
    <div class="form-group col-md-4"><label>SKU</label><input class="form-control" name="sku" id="productSku" maxlength="100"></div>
    <div class="form-group col-md-4"><label>Category</label><select class="form-control" name="category" id="productCategory"><option value="">None</option></select></div>
    <div class="form-group col-md-4"><label>Unit of Measure</label><select class="form-control" name="uom" id="productUom"><option value="">None</option></select></div>
    <div class="form-group col-md-4"><label>HSN/SAC</label><input class="form-control" name="hsn_sac" id="productHsnSac" maxlength="50"></div>
    <div class="form-group col-md-4"><label>Unit Price</label><input type="number" step="0.01" min="0" class="form-control" name="unit_price" id="productUnitPrice" required></div>
    <div class="form-group col-md-4"><label>Tax Rate</label><select class="form-control" name="tax_rate_id" id="productTaxRate"><option value="">No tax</option></select></div>
    <div class="form-group col-md-8"><label>Status</label><select class="form-control" name="status" id="productStatus"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div>
    <div class="form-group col-md-12"><label>Description</label><textarea class="form-control" name="description" id="productDescription" rows="3"></textarea></div>
</div><div class="alert alert-danger d-none" id="productError"></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Product</button></div></form></div></div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let products = [];
    const canEdit = @json(auth()->user()->can('products.edit'));
    const canDelete = @json(auth()->user()->can('products.delete'));
    const esc = value => $('<div>').text(value ?? '').html();
    const money = value => Number(value ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

    function loadProducts() {
        const params = new URLSearchParams({search: $('#filterSearch').val(), status: $('#filterStatus').val()});
        $.get(`{{ route('products.data') }}?${params}`, response => { products = response.data; render(); });
    }
    function render() {
        if (!products.length) { $('#productList').html('<div class="p-5 text-center text-muted"><i class="fa fa-cubes fa-2x mb-2"></i><br>No products match these filters.</div>'); return; }
        $('#productList').html(products.map(p => `<div class="product-row" data-id="${p.id}">
            <div><div class="product-name">${esc(p.name)}</div><div class="product-meta">${p.sku ? 'SKU: '+esc(p.sku) : ''}${p.category ? (p.sku ? ' · ' : '')+esc(p.category) : ''}</div></div>
            <div class="product-cell-secondary">${esc(p.uom || '—')}</div>
            <div class="product-cell-secondary">₹${money(p.unit_price)}</div>
            <div class="product-cell-secondary">${p.tax_rate ? esc(p.tax_rate.name) : 'No tax'}</div>
            <div class="product-cell-secondary"><span class="status-pill ${p.status === 'Active' ? 'active' : 'inactive'}">${esc(p.status)}</span></div>
            <div class="product-cell-secondary">${(canEdit || canDelete) ? `<div class="row-actions"><button type="button" class="row-actions-btn" aria-label="Actions"><i class="fa fa-ellipsis-v"></i></button><div class="row-actions-menu">${canEdit ? '<button class="editProduct"><i class="fa fa-pencil"></i> Edit</button>' : ''}${canDelete ? '<button class="deleteProduct text-danger"><i class="fa fa-trash"></i> Delete</button>' : ''}</div></div>` : ''}</div>
        </div>`).join(''));
    }

    async function loadPickerOptions() {
        const [categories, uoms, taxRates] = await Promise.all([
            $.get(`{{ url('/master-data/lookup') }}/product_category`),
            $.get(`{{ url('/master-data/lookup') }}/uom`),
            $.get(`{{ route('master_data.tax_rates.data') }}`),
        ]);
        $('#productCategory').html('<option value="">None</option>' + categories.data.map(c => `<option value="${esc(c.code)}">${esc(c.label)}</option>`).join(''));
        $('#productUom').html('<option value="">None</option>' + uoms.data.map(u => `<option value="${esc(u.code)}">${esc(u.label)}</option>`).join(''));
        $('#productTaxRate').html('<option value="">No tax</option>' + taxRates.data.filter(t => t.is_active).map(t => `<option value="${t.id}">${esc(t.name)} (${t.rate_percent}%)</option>`).join(''));
    }

    function openProduct(product = null) {
        $('#productForm')[0].reset(); $('#productError').addClass('d-none'); $('#productId').val(product?.id || ''); $('#productModalTitle').text(product ? 'Edit Product' : 'New Product');
        if (product) {
            $('#productName').val(product.name); $('#productSku').val(product.sku); $('#productCategory').val(product.category || '');
            $('#productUom').val(product.uom || ''); $('#productHsnSac').val(product.hsn_sac); $('#productUnitPrice').val(product.unit_price);
            $('#productTaxRate').val(product.tax_rate_id || ''); $('#productStatus').val(product.status); $('#productDescription').val(product.description);
        }
        $('#productModal').modal('show');
    }
    $('#newProductBtn').on('click', () => openProduct());
    $('.filter-grid #filterStatus').on('change', loadProducts);
    let searchTimer;
    $('#filterSearch').on('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(loadProducts, 300); });
    $(document).on('click', '.editProduct', function(){ openProduct(products.find(p => p.id === Number($(this).closest('.product-row').data('id')))); });
    $(document).on('click', '.deleteProduct', function(){ if(confirm('Delete this product?')) $.ajax({url:`{{ url('/products') }}/${$(this).closest('.product-row').data('id')}`,method:'DELETE',headers:{'X-CSRF-TOKEN':csrf}}).done(loadProducts); });
    $(document).on('click', '.row-actions-btn', function(e){ e.stopPropagation(); const menu=$(this).siblings('.row-actions-menu'); const opening=!menu.hasClass('is-open'); $('.row-actions-menu').removeClass('is-open'); if(opening){ const rect=this.getBoundingClientRect(); menu.css({position:'fixed',top:rect.bottom+4,left:'auto',right:window.innerWidth-rect.right}).addClass('is-open'); } });
    $(document).on('click', '.row-actions-menu', function(e){ e.stopPropagation(); });
    $(document).on('click', function(){ $('.row-actions-menu').removeClass('is-open'); });
    $('#productForm').on('submit', function(e){
        e.preventDefault();
        const id = $('#productId').val();
        const data = Object.fromEntries(new FormData(this));
        if (id) data._method = 'PUT';
        $.ajax({url: id ? `{{ url('/products') }}/${id}` : `{{ route('products.store') }}`, method: 'POST', headers: {'X-CSRF-TOKEN': csrf}, data})
            .done(() => { $('#productModal').modal('hide'); loadProducts(); })
            .fail(xhr => $('#productError').removeClass('d-none').text(xhr.responseJSON?.message || 'Unable to save product.'));
    });

    loadPickerOptions();
    loadProducts();
})();
</script></body></html>
