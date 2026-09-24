<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>Purchase Order</title>
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
        .head-actions{display:flex; gap:10px; flex-wrap:wrap;}
        .btn-accent{background:var(--accent); border:1px solid var(--accent); color:#fff; border-radius:10px; padding:10px 18px; font-weight:600; font-size:13.5px; display:inline-flex; align-items:center; gap:8px;}
        .btn-accent:hover{background:var(--accent-dark); border-color:var(--accent-dark); color:#fff;}
        .btn-ghost{background:var(--card); border:1px solid var(--border); color:var(--ink); border-radius:10px; padding:10px 16px; font-weight:600; font-size:13.5px; text-decoration:none; display:inline-flex; align-items:center; gap:8px;}
        .btn-ghost:hover{background:var(--line); color:var(--ink); text-decoration:none;}
        .btn-danger-ghost{background:var(--card); border:1px solid #fda29b; color:#b42318; border-radius:10px; padding:10px 16px; font-weight:600; font-size:13.5px;}

        .status-pill{padding:4px 12px; border-radius:999px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.03em;}
        .status-pill.draft{background:#f2f4f7; color:#667085;} .status-pill.sent{background:#eef2ff; color:#4338ca;}
        .status-pill.partially_received{background:#fef7e0; color:#93600a;} .status-pill.received{background:#e7f7ef; color:#087443;}
        .status-pill.cancelled{background:#fbe9e6; color:#a13327;}

        .paper{background:var(--card); border-radius:16px; border:1px solid var(--border); box-shadow:0 1px 2px rgba(16,24,40,.04); padding:24px 26px; margin-bottom:20px;}
        .paper .section-title{font-size:13px; font-weight:700; color:var(--ink); text-transform:uppercase; letter-spacing:.05em; margin-bottom:16px;}
        .meta-row{display:flex; gap:28px; flex-wrap:wrap; margin-bottom:8px;}
        .meta-row .m .l{font-size:11px; text-transform:uppercase; color:var(--faint); letter-spacing:.04em; margin-bottom:4px;}
        .meta-row .m .v{font-size:14px; color:var(--ink); font-weight:600;}

        table.items-table{width:100%; border-collapse:collapse;}
        table.items-table thead th{font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; color:var(--faint); font-weight:700; padding:0 10px 10px; text-align:left; border-bottom:2px solid var(--line);}
        table.items-table thead th.num{text-align:right;}
        table.items-table tbody td{padding:12px 10px; border-bottom:1px solid var(--line); font-size:13.5px; color:var(--ink);}
        table.items-table td.num{text-align:right; font-variant-numeric:tabular-nums;}
        .totals-wrap{display:flex; justify-content:flex-end; margin-top:14px;}
        .totals-box{width:260px;}
        .totals-box .t-line{display:flex; justify-content:space-between; padding:5px 0; font-size:13px; color:var(--muted);}
        .totals-box .t-line.grand{font-weight:800; font-size:17px; color:var(--ink); border-top:2px solid var(--ink); padding-top:10px; margin-top:4px;}

        .grn-form{display:grid; grid-template-columns:1fr; gap:10px; margin-top:6px;}
        .grn-line{display:grid; grid-template-columns:1.6fr .6fr .6fr; gap:10px; align-items:center; padding:8px 0; border-bottom:1px solid var(--line);}
        .grn-line:last-child{border-bottom:none;}
        .grn-line .desc{font-size:13.5px; color:var(--ink);}
        .grn-line .meta{font-size:11.5px; color:var(--muted);}
        .grn-line input{border-radius:8px; border:1px solid var(--border); font-size:13px; padding:7px 10px; background:var(--card); color:var(--ink); width:100%;}
        .grn-history{margin-top:16px;}
        .grn-history .g-row{padding:10px 0; border-bottom:1px solid var(--line); font-size:13px; color:var(--muted);}
        .grn-history .g-row strong{color:var(--ink);}
        .empty-state{padding:30px 20px; text-align:center; color:var(--muted); font-size:13.5px;}
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">

    <div class="page-head">
        <div>
            <h1 id="poTitle">Loading…</h1>
            <p>Purchase order detail and receiving.</p>
        </div>
        <div class="head-actions">
            <a href="{{ route('purchase_orders.pdf', $poId) }}" target="_blank" class="btn-ghost"><i class="fa fa-file-pdf-o"></i> PDF</a>
            <button class="btn-accent d-none" id="sendBtn"><i class="fa fa-paper-plane"></i> Mark Sent</button>
            <button class="btn-danger-ghost d-none" id="cancelBtn"><i class="fa fa-ban"></i> Cancel</button>
            <a href="{{ route('purchase_orders.index') }}" class="btn-ghost"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </div>

    <div class="paper">
        <div class="meta-row">
            <div class="m"><div class="l">Vendor</div><div class="v" id="metaVendor">—</div></div>
            <div class="m"><div class="l">Expected Delivery</div><div class="v" id="metaDelivery">—</div></div>
        </div>
        <table class="items-table">
            <thead><tr><th>Item</th><th>HSN/SAC</th><th>UOM</th><th class="num">Qty</th><th class="num">Received</th><th class="num">Unit Price</th><th class="num">Tax</th><th class="num">Line Total</th></tr></thead>
            <tbody id="itemsBody"></tbody>
        </table>
        <div class="totals-wrap"><div class="totals-box" id="totalsBox"></div></div>
    </div>

    <div class="paper d-none" id="grnCard">
        <div class="section-title"><i class="fa fa-truck"></i> Record Goods Receipt</div>
        <div class="meta-row"><div class="m" style="min-width:220px;"><div class="l">Received Date</div><input type="date" id="grnDate" class="form-control" value="{{ now()->format('Y-m-d') }}"></div></div>
        <div class="grn-form" id="grnLines"></div>
        <div style="margin-top:14px;"><button class="btn-accent" id="recordGrnBtn"><i class="fa fa-check"></i> Record Receipt</button></div>
    </div>

    <div class="paper">
        <div class="section-title"><i class="fa fa-history"></i> Goods Receipt History</div>
        <div class="grn-history" id="grnHistory"><div class="empty-state">No receipts recorded yet.</div></div>
    </div>

</div>@include('include.footer')</div></div></div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const poId = {{ $poId }};
    const esc = value => $('<div>').text(value ?? '').html();
    const money = value => Number(value ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const statusLabel = s => s.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
    const canEdit = @json(auth()->user()->can('purchase_orders.edit'));
    let po = null;

    function load() {
        $.get(`{{ url('/purchase-orders') }}/${poId}/detail`, response => { po = response.data; render(); });
    }

    function render() {
        $('#poTitle').html(`${esc(po.po_number || 'Draft Purchase Order')} <span class="status-pill ${po.status}">${statusLabel(po.status)}</span>`);
        $('#metaVendor').text(po.vendor?.name || '—');
        $('#metaDelivery').text(po.expected_delivery_date || '—');

        $('#itemsBody').html((po.items || []).map(item => `<tr>
            <td>${esc(item.description)}</td><td>${esc(item.hsn_sac || '—')}</td><td>${esc(item.uom || '—')}</td>
            <td class="num">${item.quantity}</td><td class="num">${item.received_qty}</td>
            <td class="num">${money(item.unit_price)}</td><td class="num">${item.tax_percent > 0 ? item.tax_percent + '%' : '—'}</td>
            <td class="num">${money(item.line_total)}</td>
        </tr>`).join('') || '<tr><td colspan="8" style="text-align:center;color:var(--muted);padding:24px;">No items on this order.</td></tr>');

        $('#totalsBox').html(`
            <div class="t-line"><span>Subtotal</span><span>${money(po.sub_total)}</span></div>
            <div class="t-line"><span>Tax</span><span>${money(po.tax_amount)}</span></div>
            <div class="t-line grand"><span>Total</span><span>${money(po.total_amount)}</span></div>`);

        $('#sendBtn').toggleClass('d-none', !(canEdit && po.status === 'draft'));
        $('#cancelBtn').toggleClass('d-none', !(canEdit && ['draft','sent'].includes(po.status)));

        const canReceive = canEdit && ['sent','partially_received'].includes(po.status);
        $('#grnCard').toggleClass('d-none', !canReceive);
        if (canReceive) {
            $('#grnLines').html((po.items || []).filter(i => parseFloat(i.received_qty) < parseFloat(i.quantity)).map(item => `
                <div class="grn-line" data-item-id="${item.id}">
                    <div><div class="desc">${esc(item.description)}</div><div class="meta">Ordered ${item.quantity} ${esc(item.uom || '')} · Received ${item.received_qty}</div></div>
                    <input type="number" step="0.01" min="0" class="grn-qty" placeholder="Qty received">
                    <input type="text" class="grn-remarks" placeholder="Remarks (optional)">
                </div>`).join('') || '<div class="empty-state">All items fully received.</div>');
        }

        const receipts = po.goods_receipts || [];
        $('#grnHistory').html(receipts.length ? receipts.map(g => `<div class="g-row"><strong>${esc(g.grn_number || 'GRN')}</strong> — ${g.received_date} — ${(g.items || []).length} line(s) received</div>`).join('') : '<div class="empty-state">No receipts recorded yet.</div>');
    }

    $('#sendBtn').on('click', function(){
        $.ajax({url:`{{ url('/purchase-orders') }}/${poId}/send`, method:'POST', headers:{'X-CSRF-TOKEN':csrf}}).done(load).fail(xhr=>alert(xhr.responseJSON?.message||'Unable to send.'));
    });
    $('#cancelBtn').on('click', function(){
        if (!confirm('Cancel this purchase order?')) return;
        $.ajax({url:`{{ url('/purchase-orders') }}/${poId}/cancel`, method:'POST', headers:{'X-CSRF-TOKEN':csrf}}).done(load).fail(xhr=>alert(xhr.responseJSON?.message||'Unable to cancel.'));
    });
    $('#recordGrnBtn').on('click', function(){
        const lines = $('.grn-line').map(function(){
            const qty = parseFloat($(this).find('.grn-qty').val());
            if (!qty || qty <= 0) return null;
            return {purchase_order_item_id: $(this).data('item-id'), received_qty: qty, remarks: $(this).find('.grn-remarks').val() || null};
        }).get().filter(Boolean);
        if (!lines.length) { alert('Enter at least one received quantity.'); return; }

        $.ajax({url:`{{ url('/purchase-orders') }}/${poId}/goods-receipts`, method:'POST', headers:{'X-CSRF-TOKEN':csrf}, data:{received_date: $('#grnDate').val(), lines}, traditional:false})
            .done(load)
            .fail(xhr => alert(xhr.responseJSON?.message || 'Unable to record receipt.'));
    });

    load();
})();
</script></body></html>
