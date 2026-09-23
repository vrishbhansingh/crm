<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>Products</title>
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
        .btn-accent{
            background:var(--accent); border:1px solid var(--accent); color:#fff; border-radius:10px;
            padding:11px 20px; font-weight:600; font-size:14px; display:inline-flex; align-items:center; gap:8px;
        }
        .btn-accent:hover{background:var(--accent-dark); border-color:var(--accent-dark); color:#fff;}

        .filter-bar{display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px;}
        .filter-bar input, .filter-bar select{
            border-radius:10px; border:1px solid var(--border); font-size:13.5px; padding:9px 13px;
            background:var(--card); color:var(--ink);
        }
        .filter-bar input{flex:1; min-width:220px;}
        .filter-bar select{min-width:160px;}
        .filter-bar input:focus, .filter-bar select:focus{border-color:var(--accent); outline:none; box-shadow:0 0 0 3px var(--accent-soft);}

        .product-shell{background:var(--card); border-radius:14px; border:1px solid var(--border); box-shadow:0 1px 2px rgba(16,24,40,.04); overflow:hidden;}
        .product-row{
            display:grid; grid-template-columns:minmax(200px,1.4fr) 90px 120px 150px 90px 40px;
            gap:14px; align-items:center; padding:15px 22px; border-bottom:1px solid var(--line);
        }
        .product-row:last-child{border-bottom:none;}
        .product-row:hover{background:var(--line);}
        .product-icon{
            width:38px; height:38px; border-radius:9px; background:var(--accent-soft); color:var(--accent-dark);
            display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0;
        }
        .product-main{display:flex; align-items:center; gap:12px; min-width:0;}
        .product-name{font-weight:600; color:var(--ink); font-size:14.5px;}
        .product-meta{font-size:12px; color:var(--muted);}
        .cell{color:var(--muted); font-size:13.5px;}
        .cell.amount{font-variant-numeric:tabular-nums; font-weight:600; color:var(--ink);}
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
        .row-actions-menu .text-danger{color:#dc2626;}
        .row-actions-menu .text-danger:hover{background:#fef1f1;}

        .empty-state{padding:60px 20px; text-align:center; color:var(--muted);}
        .empty-state i{font-size:34px; color:var(--faint); display:block; margin-bottom:12px;}
        .empty-state .t{font-weight:600; color:var(--ink); margin-bottom:4px;}
        .empty-state .d{font-size:13.5px;}

        /* modal */
        #productModal .modal-content{border-radius:16px; border:none; overflow:hidden;}
        #productModal .modal-header{border-bottom:1px solid var(--border); padding:20px 24px;}
        #productModal .modal-header h5{font-weight:700; font-size:17px; color:var(--ink);}
        #productModal .modal-body{padding:24px; background:var(--card);}
        #productModal .modal-footer{border-top:1px solid var(--border); padding:16px 24px;}
        #productModal label{font-size:11.5px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.03em; margin-bottom:6px;}
        #productModal .form-control{border-radius:9px; border:1px solid var(--border); font-size:14px; padding:9px 12px; height:auto; background:var(--card); color:var(--ink);}
        #productModal .form-control:focus{border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-soft); outline:none;}
        #productModal .section-divider{border-top:1px solid var(--line); margin:6px 0 16px; grid-column:1/-1;}
        [data-theme="dark"] #productModal .modal-content{background:var(--card);}

        .select-with-add{display:flex; gap:6px; align-items:center;}
        .select-with-add select{flex:1;}
        .btn-quick-add{
            width:34px; height:34px; flex:none; border-radius:9px; border:1px dashed var(--border);
            background:var(--accent-soft); color:var(--accent); display:inline-flex; align-items:center;
            justify-content:center; cursor:pointer; font-size:13px;
        }
        .btn-quick-add:hover{background:var(--accent); color:#fff; border-style:solid;}
        .quick-add-row{display:flex; gap:6px; align-items:center; margin-top:8px;}
        .quick-add-row input{flex:2;}
        .quick-add-row .btn{border-radius:7px; white-space:nowrap;}

        @media(max-width:900px){
            .product-row{grid-template-columns:1fr; gap:4px;}
            .product-row > div::before{content:attr(data-label); display:block; font-size:10px; text-transform:uppercase; color:var(--faint); letter-spacing:.03em;}
        }
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">
    <div class="page-head">
        <div>
            <h1>Products</h1>
            <p>Catalog used to build quotations — price, tax and unit are set once here.</p>
        </div>
        @can('products.create')
        <button class="btn-accent" id="newProductBtn"><i class="fa fa-plus"></i> New Product</button>
        @endcan
    </div>

    <div class="filter-bar">
        <input type="text" id="filterSearch" placeholder="Search name or SKU…">
        <select id="filterStatus">
            <option value="">All statuses</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
    </div>

    <div class="product-shell" id="productList"><div class="empty-state"><i class="fa fa-spinner fa-spin"></i><div class="t">Loading products…</div></div></div>
</div>@include('include.footer')</div></div></div>

<div class="modal fade" id="productModal"><div class="modal-dialog modal-lg"><form class="modal-content" id="productForm">
    <div class="modal-header"><h5 id="productModalTitle">New Product</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
        <input type="hidden" id="productId">
        <div class="form-row">
            <div class="form-group col-md-8"><label>Name</label><input class="form-control" name="name" id="productName" maxlength="255" required placeholder="e.g. Enterprise License"></div>
            <div class="form-group col-md-4"><label>SKU</label><input class="form-control" name="sku" id="productSku" maxlength="100" placeholder="Optional"></div>
            <div class="form-group col-md-4">
                <label>Category</label>
                <div class="select-with-add"><select class="form-control" name="category" id="productCategory"><option value="">None</option></select><button type="button" class="btn-quick-add" data-toggle-quick="category" title="Add new category"><i class="fa fa-plus"></i></button></div>
                <div class="quick-add-row d-none" id="quickAddCategory"><input type="text" class="form-control form-control-sm" id="quickAddCategoryInput" placeholder="New category name" maxlength="150"><button type="button" class="btn btn-sm btn-primary quick-add-save" data-quick="category">Add</button><button type="button" class="btn btn-sm btn-light quick-add-cancel" data-quick="category">✕</button></div>
            </div>
            <div class="form-group col-md-4">
                <label>Unit of Measure</label>
                <div class="select-with-add"><select class="form-control" name="uom" id="productUom"><option value="">None</option></select><button type="button" class="btn-quick-add" data-toggle-quick="uom" title="Add new unit"><i class="fa fa-plus"></i></button></div>
                <div class="quick-add-row d-none" id="quickAddUom"><input type="text" class="form-control form-control-sm" id="quickAddUomInput" placeholder="e.g. Roll, Bundle" maxlength="150"><button type="button" class="btn btn-sm btn-primary quick-add-save" data-quick="uom">Add</button><button type="button" class="btn btn-sm btn-light quick-add-cancel" data-quick="uom">✕</button></div>
            </div>
            <div class="form-group col-md-4"><label>HSN / SAC</label><input class="form-control" name="hsn_sac" id="productHsnSac" maxlength="50" placeholder="Optional"></div>
            <div class="form-group col-md-4"><label>Unit Price</label><input type="number" step="0.01" min="0" class="form-control" name="unit_price" id="productUnitPrice" required placeholder="0.00"></div>
            <div class="form-group col-md-4">
                <label>Tax Rate</label>
                <div class="select-with-add"><select class="form-control" name="tax_rate_id" id="productTaxRate"><option value="">No tax</option></select><button type="button" class="btn-quick-add" data-toggle-quick="taxrate" title="Add new tax rate"><i class="fa fa-plus"></i></button></div>
                <div class="quick-add-row d-none" id="quickAddTaxrate"><input type="text" class="form-control form-control-sm" id="quickAddTaxrateName" placeholder="e.g. GST 12%" maxlength="100" style="flex:1.4;"><input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm" id="quickAddTaxrateRate" placeholder="%" style="flex:.6;"><button type="button" class="btn btn-sm btn-primary quick-add-save" data-quick="taxrate">Add</button><button type="button" class="btn btn-sm btn-light quick-add-cancel" data-quick="taxrate">✕</button></div>
            </div>
            <div class="form-group col-md-4"><label>Status</label><select class="form-control" name="status" id="productStatus"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div>
            <div class="form-group col-md-12"><label>Description</label><textarea class="form-control" name="description" id="productDescription" rows="3" placeholder="Shown on quotations when relevant"></textarea></div>
        </div>
        <div class="alert alert-danger d-none" id="productError"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-primary" style="background:#4f46e5;border-color:#4f46e5;">Save Product</button></div>
</form></div></div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let products = [];
    const canEdit = @json(auth()->user()->can('products.edit'));
    const canDelete = @json(auth()->user()->can('products.delete'));
    const esc = value => $('<div>').text(value ?? '').html();
    const money = value => Number(value ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const initials = name => (name || '?').trim().split(/\s+/).slice(0, 2).map(w => w[0]?.toUpperCase() || '').join('');

    function loadProducts() {
        const params = new URLSearchParams({search: $('#filterSearch').val(), status: $('#filterStatus').val()});
        $.get(`{{ route('products.data') }}?${params}`, response => { products = response.data; render(); });
    }
    function render() {
        if (!products.length) {
            $('#productList').html(`<div class="empty-state"><i class="fa fa-cubes"></i><div class="t">No products yet</div><div class="d">Add your catalog to start building quotations faster.</div></div>`);
            return;
        }
        $('#productList').html(products.map(p => `<div class="product-row" data-id="${p.id}">
            <div class="product-main" data-label="Product">
                <div class="product-icon">${esc(initials(p.name))}</div>
                <div style="min-width:0;">
                    <div class="product-name">${esc(p.name)}</div>
                    <div class="product-meta">${p.sku ? 'SKU '+esc(p.sku) : ''}${p.category ? (p.sku ? ' · ' : '')+esc(p.category) : ''}</div>
                </div>
            </div>
            <div class="cell" data-label="UOM">${esc(p.uom || '—')}</div>
            <div class="cell amount" data-label="Price">₹${money(p.unit_price)}</div>
            <div class="cell" data-label="Tax">${p.tax_rate ? esc(p.tax_rate.name) : 'No tax'}</div>
            <div data-label="Status"><span class="status-pill ${p.status === 'Active' ? 'active' : 'inactive'}">${esc(p.status)}</span></div>
            <div>${(canEdit || canDelete) ? `<div class="row-actions"><button type="button" class="row-actions-btn" aria-label="Actions"><i class="fa fa-ellipsis-v"></i></button><div class="row-actions-menu">${canEdit ? '<button class="editProduct"><i class="fa fa-pencil"></i> Edit</button>' : ''}${canDelete ? '<button class="deleteProduct text-danger"><i class="fa fa-trash"></i> Delete</button>' : ''}</div></div>` : ''}</div>
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

    // Quick-add: "+" next to Category/UOM/Tax Rate lets an admin create a
    // new value without leaving the product form — reuses the same
    // Master Data / Tax Rate tables, so anything added here also shows up
    // on the Master Data settings page.
    $(document).on('click', '[data-toggle-quick]', function(){
        const key = $(this).data('toggle-quick');
        $(`#quickAdd${key.charAt(0).toUpperCase()}${key.slice(1)}`).removeClass('d-none').find('input').first().focus();
    });
    $(document).on('click', '.quick-add-cancel', function(){
        const key = $(this).data('quick');
        $(`#quickAdd${key.charAt(0).toUpperCase()}${key.slice(1)}`).addClass('d-none');
    });
    $(document).on('click', '.quick-add-save', function(){
        const key = $(this).data('quick');
        const btn = $(this);

        if (key === 'taxrate') {
            const name = $('#quickAddTaxrateName').val().trim();
            const rate = $('#quickAddTaxrateRate').val();
            if (!name || rate === '') { alert('Enter a name and a rate.'); return; }
            btn.prop('disabled', true);
            $.ajax({url: `{{ route('products.tax_rates.store') }}`, method: 'POST', headers: {'X-CSRF-TOKEN': csrf}, data: {name, rate_percent: rate}})
                .done(response => {
                    const t = response.data;
                    $('#productTaxRate').append(`<option value="${t.id}">${esc(t.name)} (${t.rate_percent}%)</option>`).val(t.id);
                    $('#quickAddTaxrate').addClass('d-none');
                    $('#quickAddTaxrateName').val(''); $('#quickAddTaxrateRate').val('');
                })
                .fail(xhr => alert(xhr.responseJSON?.message || 'Unable to add tax rate.'))
                .always(() => btn.prop('disabled', false));
            return;
        }

        const inputId = key === 'category' ? '#quickAddCategoryInput' : '#quickAddUomInput';
        const label = $(inputId).val().trim();
        if (!label) { alert('Enter a name.'); return; }
        const endpoint = key === 'category' ? `{{ route('products.categories.store') }}` : `{{ route('products.uoms.store') }}`;
        const selectId = key === 'category' ? '#productCategory' : '#productUom';

        btn.prop('disabled', true);
        $.ajax({url: endpoint, method: 'POST', headers: {'X-CSRF-TOKEN': csrf}, data: {label}})
            .done(response => {
                const v = response.data;
                if (!$(`${selectId} option[value="${v.code}"]`).length) {
                    $(selectId).append(`<option value="${esc(v.code)}">${esc(v.label)}</option>`);
                }
                $(selectId).val(v.code);
                $(`#quickAdd${key.charAt(0).toUpperCase()}${key.slice(1)}`).addClass('d-none');
                $(inputId).val('');
            })
            .fail(xhr => alert(xhr.responseJSON?.message || 'Unable to add value.'))
            .always(() => btn.prop('disabled', false));
    });
    $(document).on('keydown', '.quick-add-row input', function(e){
        if (e.key === 'Enter') { e.preventDefault(); $(this).closest('.quick-add-row').find('.quick-add-save').click(); }
    });

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
    $('#filterStatus').on('change', loadProducts);
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
