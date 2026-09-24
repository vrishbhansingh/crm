<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>RFQ</title>
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
        .page-head h1{font-size:24px; font-weight:800; color:var(--ink); margin:0 0 4px; letter-spacing:-.01em; display:flex; align-items:center; gap:10px;}
        .page-head p{color:var(--muted); font-size:14px; margin:0;}
        .btn-ghost{background:var(--card); border:1px solid var(--border); color:var(--ink); border-radius:10px; padding:10px 16px; font-weight:600; font-size:13.5px; text-decoration:none;}
        .status-pill{padding:4px 12px; border-radius:999px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.03em;}
        .status-pill.draft{background:#f2f4f7; color:#667085;} .status-pill.sent{background:#eef2ff; color:#4338ca;}
        .status-pill.closed{background:#fef7e0; color:#93600a;} .status-pill.converted{background:#e7f7ef; color:#087443;}
        .paper{background:var(--card); border-radius:16px; border:1px solid var(--border); box-shadow:0 1px 2px rgba(16,24,40,.04); padding:24px 26px; margin-bottom:20px;}
        .section-title{font-size:13px; font-weight:700; color:var(--ink); text-transform:uppercase; letter-spacing:.05em; margin-bottom:16px;}
        table.items-table{width:100%; border-collapse:collapse;}
        table.items-table thead th{font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; color:var(--faint); font-weight:700; padding:0 10px 10px; text-align:left; border-bottom:2px solid var(--line);}
        table.items-table tbody td{padding:12px 10px; border-bottom:1px solid var(--line); font-size:13.5px; color:var(--ink);}
        .vendor-row{display:grid; grid-template-columns:1.4fr 1fr 1fr auto; gap:12px; align-items:center; padding:12px 0; border-bottom:1px solid var(--line);}
        .vendor-row:last-child{border-bottom:none;}
        .vendor-name{font-weight:600; color:var(--ink); font-size:13.5px;}
        .vendor-row input{border-radius:8px; border:1px solid var(--border); font-size:13px; padding:7px 10px; background:var(--card); color:var(--ink); width:100%;}
        .btn-sm-accent{background:var(--accent); border:1px solid var(--accent); color:#fff; border-radius:8px; padding:7px 12px; font-size:12.5px; font-weight:600;}
        .btn-sm-accent:hover{background:var(--accent-dark); color:#fff;}
        .quoted-chip{font-size:12px; font-weight:700; color:#087443;}
        .empty-state{padding:24px; text-align:center; color:var(--muted); font-size:13.5px;}
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">

    <div class="page-head">
        <div><h1 id="rfqTitle">Loading…</h1><p>Vendor responses and conversion to a purchase order.</p></div>
        <a href="{{ route('rfqs.index') }}" class="btn-ghost"><i class="fa fa-arrow-left"></i> Back</a>
    </div>

    <div class="paper">
        <div class="section-title"><i class="fa fa-list-ul"></i> Items requested</div>
        <table class="items-table"><thead><tr><th>Description</th><th>UOM</th><th>Qty</th></tr></thead><tbody id="itemsBody"></tbody></table>
    </div>

    <div class="paper">
        <div class="section-title"><i class="fa fa-building-o"></i> Vendor Responses</div>
        <div id="vendorRows"></div>
    </div>

</div>@include('include.footer')</div></div></div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const rfqId = {{ $rfqId }};
    const esc = value => $('<div>').text(value ?? '').html();
    const money = value => Number(value ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const canEdit = @json(auth()->user()->can('purchase_orders.edit'));
    let rfq = null;

    function load() {
        $.get(`{{ url('/rfqs') }}/${rfqId}/detail`, response => { rfq = response.data; render(); });
    }

    function render() {
        $('#rfqTitle').html(`${esc(rfq.rfq_number || 'Draft RFQ')} <span class="status-pill ${rfq.status}">${rfq.status.charAt(0).toUpperCase() + rfq.status.slice(1)}</span>`);
        $('#itemsBody').html((rfq.items || []).map(i => `<tr><td>${esc(i.description)}</td><td>${esc(i.uom || '—')}</td><td>${i.quantity}</td></tr>`).join('') || '<tr><td colspan="3" style="text-align:center;color:var(--muted);">No items.</td></tr>');

        const converted = rfq.status === 'converted';
        $('#vendorRows').html((rfq.invited_vendors || []).map(row => `
            <div class="vendor-row" data-row-id="${row.id}" data-vendor-id="${row.vendor_id}">
                <div class="vendor-name">${esc(row.vendor?.name || '—')}</div>
                ${row.quoted_amount ? `<div class="quoted-chip">₹${money(row.quoted_amount)} quoted</div>` : `<input type="number" step="0.01" class="quoteAmount" placeholder="Quoted amount">`}
                <input type="text" class="quoteNotes" placeholder="Notes (optional)" value="${esc(row.notes || '')}">
                <div>
                    ${!row.quoted_amount && canEdit ? `<button type="button" class="btn-sm-accent saveQuoteBtn">Save</button>` : ''}
                    ${row.quoted_amount && canEdit && !converted ? `<button type="button" class="btn-sm-accent convertBtn">Convert to PO</button>` : ''}
                </div>
            </div>`).join('') || '<div class="empty-state">No vendors invited.</div>');
    }

    $(document).on('click', '.saveQuoteBtn', function(){
        const row = $(this).closest('.vendor-row');
        const amount = row.find('.quoteAmount').val();
        if (!amount) { alert('Enter a quoted amount.'); return; }
        $.ajax({url:`{{ url('/rfqs') }}/${rfqId}/vendors/${row.data('row-id')}/quote`, method:'POST', headers:{'X-CSRF-TOKEN':csrf}, data:{quoted_amount: amount, notes: row.find('.quoteNotes').val()}})
            .done(load).fail(xhr => alert(xhr.responseJSON?.message || 'Unable to save quote.'));
    });
    $(document).on('click', '.convertBtn', function(){
        const row = $(this).closest('.vendor-row');
        if (!confirm('Create a draft purchase order from this vendor\'s quote?')) return;
        $.ajax({url:`{{ url('/rfqs') }}/${rfqId}/convert-to-po`, method:'POST', headers:{'X-CSRF-TOKEN':csrf}, data:{vendor_id: row.data('vendor-id')}})
            .done(response => { window.location.href = `{{ url('/purchase-orders') }}/${response.purchase_order_id}`; })
            .fail(xhr => alert(xhr.responseJSON?.message || 'Unable to convert.'));
    });

    load();
})();
</script></body></html>
