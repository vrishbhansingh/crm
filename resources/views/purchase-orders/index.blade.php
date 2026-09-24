<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>Purchase Orders</title>
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
        .page-head{display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:20px;}
        .page-head h1{font-size:24px; font-weight:800; color:var(--ink); margin:0 0 4px; letter-spacing:-.01em;}
        .page-head p{color:var(--muted); font-size:14px; margin:0;}
        .head-actions{display:flex; gap:10px; flex-wrap:wrap;}
        .btn-accent{background:var(--accent); border:1px solid var(--accent); color:#fff; border-radius:10px; padding:11px 20px; font-weight:600; font-size:14px; display:inline-flex; align-items:center; gap:8px;}
        .btn-accent:hover{background:var(--accent-dark); border-color:var(--accent-dark); color:#fff;}
        .btn-ghost{background:var(--card); border:1px solid var(--border); color:var(--ink); border-radius:10px; padding:11px 18px; font-weight:600; font-size:14px; text-decoration:none; display:inline-flex; align-items:center; gap:8px;}
        .btn-ghost:hover{background:var(--line); color:var(--ink); text-decoration:none;}

        .filter-bar{display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px;}
        .filter-bar input, .filter-bar select{border-radius:10px; border:1px solid var(--border); font-size:13.5px; padding:9px 13px; background:var(--card); color:var(--ink);}
        .filter-bar input{flex:1; min-width:220px;} .filter-bar select{min-width:160px;}
        .filter-bar input:focus, .filter-bar select:focus{border-color:var(--accent); outline:none; box-shadow:0 0 0 3px var(--accent-soft);}

        .po-shell{background:var(--card); border-radius:14px; border:1px solid var(--border); box-shadow:0 1px 2px rgba(16,24,40,.04); overflow:hidden;}
        .po-row{display:grid; grid-template-columns:150px minmax(180px,1.4fr) 140px 120px 120px 40px; gap:14px; align-items:center; padding:15px 22px; border-bottom:1px solid var(--line); cursor:pointer;}
        .po-row:last-child{border-bottom:none;} .po-row:hover{background:var(--line);}
        .po-number{font-weight:700; color:var(--ink); font-size:14px;}
        .po-date{font-size:12px; color:var(--faint);}
        .cell{color:var(--muted); font-size:13.5px;}
        .cell.amount{font-variant-numeric:tabular-nums; font-weight:600; color:var(--ink);}
        .status-pill{padding:4px 11px; border-radius:999px; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; white-space:nowrap;}
        .status-pill.draft{background:#f2f4f7; color:#667085;}
        .status-pill.sent{background:#eef2ff; color:#4338ca;}
        .status-pill.partially_received{background:#fef7e0; color:#93600a;}
        .status-pill.received{background:#e7f7ef; color:#087443;}
        .status-pill.cancelled{background:#fbe9e6; color:#a13327;}

        .row-actions{position:relative; display:inline-block;}
        .row-actions-btn{width:32px; height:32px; border-radius:8px; border:none; background:transparent; color:var(--faint); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; font-size:15px;}
        .row-actions-btn:hover{background:var(--line); color:var(--ink);}
        .row-actions-menu{position:absolute; right:0; top:100%; margin-top:4px; min-width:160px; background:var(--card); border:1px solid var(--border); border-radius:10px; box-shadow:0 12px 30px rgba(16,24,40,.15); padding:6px; z-index:50; display:none; text-align:left;}
        .row-actions-menu.is-open{display:block;}
        .row-actions-menu a, .row-actions-menu button{display:flex; align-items:center; gap:10px; width:100%; padding:9px 12px; border-radius:8px; font-size:13px; color:var(--ink); border:none; background:transparent; text-align:left; cursor:pointer; text-decoration:none;}
        .row-actions-menu a:hover, .row-actions-menu button:hover{background:var(--line); color:var(--ink); text-decoration:none;}
        .row-actions-menu .text-danger{color:#dc2626;} .row-actions-menu .text-danger:hover{background:#fef1f1;}

        .empty-state{padding:60px 20px; text-align:center; color:var(--muted);}
        .empty-state i{font-size:34px; color:var(--faint); display:block; margin-bottom:12px;}
        .empty-state .t{font-weight:600; color:var(--ink); margin-bottom:4px;} .empty-state .d{font-size:13.5px;}

        @media(max-width:900px){
            .po-row{grid-template-columns:1fr; gap:4px;}
            .po-row > div::before{content:attr(data-label); display:block; font-size:10px; text-transform:uppercase; color:var(--faint); letter-spacing:.03em;}
        }
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">
    <div class="page-head">
        <div>
            <h1>Purchase Orders</h1>
            <p>Orders raised against your vendors, with receiving tracked line by line.</p>
        </div>
        <div class="head-actions">
            @can('purchase_orders.view')
            <a href="{{ route('rfqs.index') }}" class="btn-ghost"><i class="fa fa-envelope-o"></i> RFQs</a>
            @endcan
            @can('purchase_orders.create')
            <a href="{{ route('purchase_orders.create_form') }}" class="btn-accent"><i class="fa fa-plus"></i> New Purchase Order</a>
            @endcan
        </div>
    </div>

    <div class="filter-bar">
        <input type="text" id="filterSearch" placeholder="Search PO number or vendor…">
        <select id="filterStatus">
            <option value="">All statuses</option>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="partially_received">Partially Received</option>
            <option value="received">Received</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <div class="po-shell" id="poList"><div class="empty-state"><i class="fa fa-spinner fa-spin"></i><div class="t">Loading purchase orders…</div></div></div>
</div>@include('include.footer')</div></div></div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let orders = [];
    const canEdit = @json(auth()->user()->can('purchase_orders.edit'));
    const canDelete = @json(auth()->user()->can('purchase_orders.delete'));
    const esc = value => $('<div>').text(value ?? '').html();
    const money = value => Number(value ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const statusLabel = s => s.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());

    function loadOrders() {
        const params = new URLSearchParams({search: $('#filterSearch').val(), status: $('#filterStatus').val()});
        $.get(`{{ route('purchase_orders.data') }}?${params}`, response => { orders = response.data; render(); });
    }
    function render() {
        if (!orders.length) {
            $('#poList').html(`<div class="empty-state"><i class="fa fa-truck"></i><div class="t">No purchase orders yet</div><div class="d">Create one from a vendor, or convert an RFQ once a vendor responds.</div></div>`);
            return;
        }
        $('#poList').html(orders.map(po => `<div class="po-row" data-id="${po.id}">
            <div data-label="PO #"><div class="po-number">${esc(po.po_number || 'Draft')}</div><div class="po-date">${new Date(po.created_at).toLocaleDateString()}</div></div>
            <div class="cell" data-label="Vendor">${esc(po.vendor?.name || '—')}</div>
            <div data-label="Status"><span class="status-pill ${po.status}">${statusLabel(po.status)}</span></div>
            <div class="cell amount" data-label="Total">₹${money(po.total_amount)}</div>
            <div class="cell" data-label="Delivery">${po.expected_delivery_date || '—'}</div>
            <div onclick="event.stopPropagation()"><div class="row-actions"><button type="button" class="row-actions-btn" aria-label="Actions"><i class="fa fa-ellipsis-v"></i></button><div class="row-actions-menu">
                <a href="{{ url('/purchase-orders') }}/${po.id}"><i class="fa fa-eye"></i> View</a>
                <a href="{{ url('/purchase-orders') }}/${po.id}/pdf" target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a>
                ${canEdit && po.status === 'draft' ? `<button class="sendPo"><i class="fa fa-paper-plane"></i> Mark Sent</button>` : ''}
                ${canEdit && ['draft','sent'].includes(po.status) ? `<button class="cancelPo text-danger"><i class="fa fa-ban"></i> Cancel</button>` : ''}
                ${canDelete && po.status === 'draft' ? `<button class="deletePo text-danger"><i class="fa fa-trash"></i> Delete</button>` : ''}
            </div></div></div>
        </div>`).join(''));
    }

    $('.po-shell').on('click', '.po-row', function(){ window.location.href = `{{ url('/purchase-orders') }}/${$(this).data('id')}`; });
    $('#filterStatus').on('change', loadOrders);
    let searchTimer;
    $('#filterSearch').on('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(loadOrders, 300); });
    $(document).on('click', '.sendPo', function(){ const id=$(this).closest('.po-row').data('id'); $.ajax({url:`{{ url('/purchase-orders') }}/${id}/send`,method:'POST',headers:{'X-CSRF-TOKEN':csrf}}).done(loadOrders).fail(xhr=>alert(xhr.responseJSON?.message||'Unable to send.')); });
    $(document).on('click', '.cancelPo', function(){ if(!confirm('Cancel this purchase order?')) return; const id=$(this).closest('.po-row').data('id'); $.ajax({url:`{{ url('/purchase-orders') }}/${id}/cancel`,method:'POST',headers:{'X-CSRF-TOKEN':csrf}}).done(loadOrders).fail(xhr=>alert(xhr.responseJSON?.message||'Unable to cancel.')); });
    $(document).on('click', '.deletePo', function(){ if(!confirm('Delete this purchase order?')) return; const id=$(this).closest('.po-row').data('id'); $.ajax({url:`{{ url('/purchase-orders') }}/${id}`,method:'DELETE',headers:{'X-CSRF-TOKEN':csrf}}).done(loadOrders).fail(xhr=>alert(xhr.responseJSON?.message||'Unable to delete.')); });
    $(document).on('click', '.row-actions-btn', function(e){ e.stopPropagation(); const menu=$(this).siblings('.row-actions-menu'); const opening=!menu.hasClass('is-open'); $('.row-actions-menu').removeClass('is-open'); if(opening){ const rect=this.getBoundingClientRect(); menu.css({position:'fixed',top:rect.bottom+4,left:'auto',right:window.innerWidth-rect.right}).addClass('is-open'); } });
    $(document).on('click', '.row-actions-menu', function(e){ e.stopPropagation(); });
    $(document).on('click', function(){ $('.row-actions-menu').removeClass('is-open'); });

    loadOrders();
})();
</script></body></html>
