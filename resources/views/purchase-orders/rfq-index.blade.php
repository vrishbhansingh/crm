<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>Requests for Quotation</title>
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

        .rfq-shell{background:var(--card); border-radius:14px; border:1px solid var(--border); box-shadow:0 1px 2px rgba(16,24,40,.04); overflow:hidden;}
        .rfq-row{display:grid; grid-template-columns:150px 1fr 140px 120px; gap:14px; align-items:center; padding:15px 22px; border-bottom:1px solid var(--line); cursor:pointer;}
        .rfq-row:last-child{border-bottom:none;} .rfq-row:hover{background:var(--line);}
        .rfq-number{font-weight:700; color:var(--ink); font-size:14px;}
        .cell{color:var(--muted); font-size:13.5px;}
        .status-pill{padding:4px 11px; border-radius:999px; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.03em;}
        .status-pill.draft{background:#f2f4f7; color:#667085;} .status-pill.sent{background:#eef2ff; color:#4338ca;}
        .status-pill.closed{background:#fef7e0; color:#93600a;} .status-pill.converted{background:#e7f7ef; color:#087443;}
        .empty-state{padding:60px 20px; text-align:center; color:var(--muted);}
        .empty-state i{font-size:34px; color:var(--faint); display:block; margin-bottom:12px;}
        .empty-state .t{font-weight:600; color:var(--ink); margin-bottom:4px;} .empty-state .d{font-size:13.5px;}
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">
    <div class="page-head">
        <div>
            <h1>Requests for Quotation</h1>
            <p>Ask vendors for pricing, then convert the winning response into a purchase order.</p>
        </div>
        <div class="head-actions">
            <a href="{{ route('purchase_orders.index') }}" class="btn-ghost"><i class="fa fa-truck"></i> Purchase Orders</a>
            @can('purchase_orders.create')
            <a href="{{ route('rfqs.create_form') }}" class="btn-accent"><i class="fa fa-plus"></i> New RFQ</a>
            @endcan
        </div>
    </div>

    <div class="rfq-shell" id="rfqList"><div class="empty-state"><i class="fa fa-spinner fa-spin"></i><div class="t">Loading…</div></div></div>
</div>@include('include.footer')</div></div></div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script>
(() => {
    const esc = value => $('<div>').text(value ?? '').html();
    function load() {
        $.get(`{{ route('rfqs.data') }}`, response => {
            const rfqs = response.data;
            if (!rfqs.length) {
                $('#rfqList').html(`<div class="empty-state"><i class="fa fa-envelope-o"></i><div class="t">No RFQs yet</div><div class="d">Create one to invite vendors to quote on a set of items.</div></div>`);
                return;
            }
            $('#rfqList').html(rfqs.map(r => `<div class="rfq-row" onclick="window.location.href='{{ url('/rfqs') }}/${r.id}'">
                <div class="rfq-number">${esc(r.rfq_number || 'Draft')}</div>
                <div class="cell">${new Date(r.created_at).toLocaleDateString()}</div>
                <div><span class="status-pill ${r.status}">${r.status.charAt(0).toUpperCase() + r.status.slice(1)}</span></div>
                <div class="cell">${r.invited_vendors_count} vendor(s) invited</div>
            </div>`).join(''));
        });
    }
    load();
})();
</script></body></html>
