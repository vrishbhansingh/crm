<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>New RFQ</title>
    <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}"><link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}"><link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --ink:#101828; --muted:#667085; --faint:#98a2b3; --border:#e4e7ec; --line:#eef1f5;
            --bg:#f5f6fa; --card:#fff; --accent:#4f46e5; --accent-soft:#eef2ff; --accent-dark:#4338ca;
        }
        [data-theme="dark"]{
            --ink:#eef0f6; --muted:#9aa1b5; --faint:#71798f; --border:#2a2e40; --line:#252838;
            --bg:#11131c; --card:#181b28; --accent:#818cf8; --accent-soft:#252a4a; --accent-dark:#a5b0ff;
        }
        body{font-family:"Inter",ui-sans-serif,system-ui,sans-serif;}
        .content-wrapper{background:var(--bg);}
        .page-head h1{font-size:24px; font-weight:800; color:var(--ink); margin:0 0 4px; letter-spacing:-.01em;}
        .page-head p{color:var(--muted); font-size:14px; margin:0 0 20px;}
        .paper{background:var(--card); border-radius:16px; border:1px solid var(--border); box-shadow:0 1px 2px rgba(16,24,40,.04); padding:26px 28px; margin-bottom:20px;}
        .section-title{font-size:13px; font-weight:700; color:var(--ink); text-transform:uppercase; letter-spacing:.05em; margin-bottom:16px;}
        table.items-table{width:100%; border-collapse:collapse;}
        table.items-table thead th{font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; color:var(--faint); font-weight:700; padding:0 10px 10px; text-align:left; border-bottom:2px solid var(--line);}
        table.items-table tbody td{padding:12px 10px; border-bottom:1px solid var(--line); font-size:13.5px; color:var(--ink);}
        .row-remove{width:28px; height:28px; border-radius:7px; border:none; background:transparent; color:var(--faint); cursor:pointer;}
        .row-remove:hover{background:#fef1f1; color:#b42318;}
        .add-row{display:grid; grid-template-columns:1.6fr 1fr .7fr auto; gap:10px; align-items:end; margin-top:14px; padding-top:14px; border-top:1px dashed var(--border);}
        .add-row label{font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; color:var(--faint); font-weight:700; margin-bottom:5px; display:block;}
        .add-row .form-control{border-radius:8px; border:1px solid var(--border); font-size:13.5px; padding:8px 10px; background:var(--card); color:var(--ink); height:auto;}
        .btn-add-line{width:36px; height:36px; border-radius:8px; border:none; background:var(--accent); color:#fff; cursor:pointer;}
        .vendor-checks{display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:10px;}
        .vendor-check{border:1.5px solid var(--border); border-radius:10px; padding:10px 12px; display:flex; align-items:center; gap:8px; cursor:pointer;}
        .vendor-check input{margin:0;}
        .btn-accent{background:var(--accent); border:1px solid var(--accent); color:#fff; border-radius:10px; padding:12px 24px; font-weight:600; font-size:14.5px;}
        .btn-ghost{background:var(--card); border:1px solid var(--border); color:var(--ink); border-radius:10px; padding:12px 20px; font-weight:600; font-size:14.5px; text-decoration:none;}
        .alert-inline{background:#fef1f1; border:1px solid #fda29b; color:#b42318; border-radius:10px; padding:10px 14px; font-size:13.5px; margin-bottom:16px;}
        .items-empty{padding:20px; text-align:center; color:var(--muted); font-size:13.5px;}
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">
    <div class="page-head"><h1>New RFQ</h1><p>Add the items you need priced, then invite vendors to quote.</p></div>

    <form id="createRfqForm">
        <div class="paper">
            <div class="section-title"><i class="fa fa-list-ul"></i> Items</div>
            <table class="items-table">
                <thead><tr><th>Description</th><th>UOM</th><th>Qty</th><th></th></tr></thead>
                <tbody id="itemsBody"><tr><td colspan="4"><div class="items-empty">No items yet — add the first one below.</div></td></tr></tbody>
            </table>
            <div class="add-row">
                <div><label>Product / Description</label><select class="form-control" id="newItemProduct" style="margin-bottom:6px;"><option value="">— Custom line —</option></select><input class="form-control" id="newItemDescription" placeholder="Line description"></div>
                <div><label>UOM</label><input class="form-control" id="newItemUom" placeholder="Nos"></div>
                <div><label>Qty</label><input type="number" step="0.01" class="form-control" id="newItemQty" value="1"></div>
                <div><button type="button" class="btn-add-line" id="addItemBtn"><i class="fa fa-plus"></i></button></div>
            </div>
        </div>

        <div class="paper">
            <div class="section-title"><i class="fa fa-building-o"></i> Invite Vendors</div>
            <div class="vendor-checks" id="vendorChecks"></div>
        </div>

        <div class="paper">
            <div class="section-title"><i class="fa fa-sticky-note-o"></i> Notes</div>
            <textarea class="form-control" id="notes" rows="3" placeholder="Any context for this RFQ"></textarea>
        </div>

        <div id="createError" class="alert-inline d-none"></div>
        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn-accent" id="createBtn"><i class="fa fa-paper-plane mr-1"></i> Create RFQ</button>
            <a href="{{ route('rfqs.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
</div>@include('include.footer')</div></div></div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const esc = value => $('<div>').text(value ?? '').html();
    let items = [];

    async function loadProducts() {
        const response = await $.get(`{{ route('products.options') }}`);
        $('#newItemProduct').html('<option value="">— Custom line —</option>' + response.data.map(p => `<option value="${p.id}" data-uom="${esc(p.uom || '')}">${esc(p.name)}</option>`).join(''));
    }
    $('#newItemProduct').on('change', function(){ const opt=$(this).find(':selected'); if(!opt.val())return; $('#newItemDescription').val(opt.text()); $('#newItemUom').val(opt.data('uom')); });

    async function loadVendors() {
        const response = await $.get(`{{ route('vendors.options') }}`);
        $('#vendorChecks').html(response.data.length ? response.data.map(v => `<label class="vendor-check"><input type="checkbox" value="${v.id}" class="vendorCheck"><span>${esc(v.name)}</span></label>`).join('') : '<div class="items-empty">No vendors yet — add one first.</div>');
    }

    function renderItems() {
        $('#itemsBody').html(items.length ? items.map((item, i) => `<tr><td>${esc(item.description)}</td><td>${esc(item.uom || '—')}</td><td>${item.quantity}</td><td><button type="button" class="row-remove removeItemBtn" data-i="${i}"><i class="fa fa-trash-o"></i></button></td></tr>`).join('') : '<tr><td colspan="4"><div class="items-empty">No items yet — add the first one below.</div></td></tr>');
    }
    $('#addItemBtn').on('click', function(){
        const description = $('#newItemDescription').val() || $('#newItemProduct option:selected').text();
        if (!description) { $('#newItemDescription').focus(); return; }
        items.push({product_id: $('#newItemProduct').val() || null, description, uom: $('#newItemUom').val() || null, quantity: parseFloat($('#newItemQty').val()) || 1});
        $('#newItemProduct').val(''); $('#newItemDescription').val(''); $('#newItemUom').val(''); $('#newItemQty').val(1);
        renderItems();
    });
    $(document).on('click', '.removeItemBtn', function(){ items.splice($(this).data('i'), 1); renderItems(); });

    $('#createRfqForm').on('submit', function(e){
        e.preventDefault();
        if (!items.length) { $('#createError').removeClass('d-none').text('Add at least one item.'); return; }
        const vendorIds = $('.vendorCheck:checked').map(function(){ return $(this).val(); }).get();
        if (!vendorIds.length) { $('#createError').removeClass('d-none').text('Invite at least one vendor.'); return; }

        $('#createBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Creating…');
        $.ajax({url:`{{ route('rfqs.store') }}`, method:'POST', headers:{'X-CSRF-TOKEN':csrf}, data:{items, vendor_ids: vendorIds, notes: $('#notes').val()}, traditional:false})
            .done(response => { window.location.href = `{{ url('/rfqs') }}/${response.id}`; })
            .fail(xhr => { $('#createBtn').prop('disabled', false).html('<i class="fa fa-paper-plane mr-1"></i> Create RFQ'); $('#createError').removeClass('d-none').text(xhr.responseJSON?.message || 'Unable to create RFQ.'); });
    });

    loadProducts();
    loadVendors();
})();
</script></body></html>
