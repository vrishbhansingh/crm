<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>New Purchase Order</title>
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

        .paper{background:var(--card); border-radius:16px; border:1px solid var(--border); box-shadow:0 1px 2px rgba(16,24,40,.04), 0 8px 28px rgba(16,24,40,.05); padding:26px 28px; margin-bottom:20px;}
        .paper .section-title{font-size:13px; font-weight:700; color:var(--ink); text-transform:uppercase; letter-spacing:.05em; margin-bottom:16px; display:flex; align-items:center; gap:8px;}
        .paper .section-title i{color:var(--accent); font-size:13px;}

        .record-field label{font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px; display:block;}
        .record-field select{border-radius:10px; border:1px solid var(--border); font-size:14px; padding:11px 14px; height:auto; background:var(--card); color:var(--ink); width:100%;}
        .record-field select:focus{border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-soft); outline:none;}
        .record-hint{font-size:12.5px; color:var(--faint); margin-top:8px;}

        .items-wrap{overflow-x:auto; margin:0 -4px;}
        table.items-table{width:100%; border-collapse:collapse; min-width:820px;}
        table.items-table thead th{font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; color:var(--faint); font-weight:700; padding:0 10px 10px; text-align:left; border-bottom:2px solid var(--line); white-space:nowrap;}
        table.items-table thead th.num{text-align:right;}
        table.items-table tbody td{padding:13px 10px; border-bottom:1px solid var(--line); font-size:14px; color:var(--ink); vertical-align:middle;}
        table.items-table tbody tr:last-child td{border-bottom:none;}
        table.items-table td.num{text-align:right; font-variant-numeric:tabular-nums;}
        table.items-table .item-desc{font-weight:600;}
        table.items-table .pill-tag{display:inline-block; font-size:11px; font-weight:600; color:var(--muted); background:var(--line); border-radius:6px; padding:2px 8px;}
        .row-remove{width:28px; height:28px; border-radius:7px; border:none; background:transparent; color:var(--faint); display:inline-flex; align-items:center; justify-content:center; cursor:pointer;}
        .row-remove:hover{background:#fef1f1; color:#b42318;}
        .items-empty{padding:30px 10px; text-align:center; color:var(--muted); font-size:13.5px;}
        .items-empty i{font-size:22px; color:var(--faint); display:block; margin-bottom:8px;}

        .add-row{display:grid; grid-template-columns:1.6fr .9fr .8fr .7fr .9fr .9fr 1fr auto; gap:10px; align-items:end; margin-top:16px; padding-top:16px; border-top:1px dashed var(--border);}
        @media(max-width:1150px){.add-row{grid-template-columns:repeat(2,1fr);}}
        .add-row label{font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; color:var(--faint); font-weight:700; margin-bottom:5px; display:block;}
        .add-row .form-control{border-radius:8px; border:1px solid var(--border); font-size:13.5px; padding:8px 10px; background:var(--card); color:var(--ink); height:auto;}
        .add-row .form-control:focus{border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-soft);}
        .btn-add-line{width:36px; height:36px; border-radius:8px; border:none; background:var(--accent); color:#fff; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; font-size:15px;}
        .btn-add-line:hover{background:var(--accent-dark);}

        .totals-wrap{display:flex; justify-content:flex-end; margin-top:18px;}
        .totals-box{width:280px;}
        .totals-box .t-line{display:flex; justify-content:space-between; padding:6px 0; font-size:13.5px; color:var(--muted); font-variant-numeric:tabular-nums;}
        .totals-box .t-line.grand{font-weight:800; font-size:19px; color:var(--ink); border-top:2px solid var(--ink); padding-top:12px; margin-top:6px;}

        textarea#termsConditions{border-radius:10px; border:1px solid var(--border); font-size:13.5px; background:var(--card); color:var(--ink); line-height:1.6;}
        .meta-grid{display:grid; grid-template-columns:1fr 1fr; gap:16px;}
        .meta-grid .field label{font-size:11.5px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px; display:block;}
        .meta-grid .field .form-control{border-radius:9px; border:1px solid var(--border); font-size:14px; background:var(--card); color:var(--ink);}

        .wizard-actions{display:flex; gap:10px; align-items:center; margin-top:4px;}
        .btn-accent{background:var(--accent); border:1px solid var(--accent); color:#fff; border-radius:10px; padding:12px 24px; font-weight:600; font-size:14.5px;}
        .btn-accent:hover{background:var(--accent-dark); border-color:var(--accent-dark); color:#fff;}
        .btn-ghost{background:var(--card); border:1px solid var(--border); color:var(--ink); border-radius:10px; padding:12px 20px; font-weight:600; font-size:14.5px; text-decoration:none;}
        .btn-ghost:hover{background:var(--line); color:var(--ink); text-decoration:none;}
        .alert-inline{background:#fef1f1; border:1px solid #fda29b; color:#b42318; border-radius:10px; padding:10px 14px; font-size:13.5px; margin-bottom:16px;}
        [data-theme="dark"] .alert-inline{background:#3a1f20; border-color:#7a2f2a; color:#ff9c94;}
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">

    <div class="page-head">
        <div>
            <h1>New Purchase Order</h1>
            <p>Pick a vendor, add items and terms, then generate the PO — all in one go.</p>
        </div>
    </div>

    <form id="createPoForm">
        <div class="paper">
            <div class="section-title"><i class="fa fa-building-o"></i> Vendor</div>
            <div class="record-field">
                <label>Vendor</label>
                <select id="vendorSelect" required><option value="">Select…</option></select>
                <div class="record-hint" id="vendorHint"></div>
            </div>
        </div>

        <div class="paper">
            <div class="section-title"><i class="fa fa-list-ul"></i> Line Items</div>
            <div class="items-wrap">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width:26%">Item</th><th>HSN/SAC</th><th>UOM</th><th class="num">Qty</th>
                            <th class="num">Unit Price</th><th class="num">Tax</th>
                            <th class="num">Amount</th><th></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                </table>
            </div>
            <div class="add-row">
                <div>
                    <label>Product / Description</label>
                    <select class="form-control" id="newItemProduct" style="margin-bottom:6px;"><option value="">— Custom line —</option></select>
                    <input class="form-control" id="newItemDescription" placeholder="Line description">
                </div>
                <div><label>HSN/SAC</label><input class="form-control" id="newItemHsn"></div>
                <div><label>UOM</label><input class="form-control" id="newItemUom" placeholder="Nos"></div>
                <div><label>Qty</label><input type="number" step="0.01" class="form-control" id="newItemQty" value="1"></div>
                <div><label>Unit Price</label><input type="number" step="0.01" class="form-control" id="newItemPrice" placeholder="0.00"></div>
                <div><label>Tax</label><select class="form-control" id="newItemTax"><option value="">None</option></select></div>
                <div></div>
                <div><button type="button" class="btn-add-line" id="addItemBtn" title="Add line"><i class="fa fa-plus"></i></button></div>
            </div>
            <div class="totals-wrap"><div class="totals-box" id="totalsBox"></div></div>
        </div>

        <div class="paper">
            <div class="section-title"><i class="fa fa-cog"></i> Order Details</div>
            <div class="meta-grid">
                <div class="field"><label>Expected Delivery</label><input type="date" class="form-control" id="expectedDeliveryDate"></div>
            </div>
        </div>

        <div class="paper">
            <div class="section-title"><i class="fa fa-file-text-o"></i> Terms &amp; Conditions</div>
            <textarea class="form-control" id="termsConditions" rows="5" placeholder="Delivery schedule, payment terms, inspection…"></textarea>
        </div>

        <div id="createError" class="alert-inline d-none"></div>

        <div class="wizard-actions">
            <button type="submit" class="btn-accent" id="generateBtn"><i class="fa fa-magic mr-1"></i> Generate Purchase Order</button>
            <a href="{{ route('purchase_orders.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>

</div>@include('include.footer')</div></div></div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const esc = value => $('<div>').text(value ?? '').html();
    const money = value => Number(value ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const preselectVendor = {{ $vendorId ?? 'null' }};
    let items = [];
    let taxRates = [];

    async function loadVendors() {
        $('#vendorHint').text('Loading…');
        const response = await $.get(`{{ route('vendors.options') }}`);
        $('#vendorSelect').html('<option value="">Select…</option>' + response.data.map(v => `<option value="${v.id}">${esc(v.name)}</option>`).join(''));
        if (preselectVendor) $('#vendorSelect').val(String(preselectVendor));
        $('#vendorHint').text(response.data.length ? `${response.data.length} vendor(s) available` : 'No vendors yet — add one first.');
    }

    async function loadTaxRates() {
        const response = await $.get(`{{ route('master_data.tax_rates.data') }}`);
        taxRates = response.data.filter(t => t.is_active);
        $('#newItemTax').html('<option value="">None</option>' + taxRates.map(t => `<option value="${t.id}">${esc(t.name)} (${t.rate_percent}%)</option>`).join(''));
    }
    async function loadProducts() {
        const response = await $.get(`{{ route('products.options') }}`);
        $('#newItemProduct').html('<option value="">— Custom line —</option>' + response.data.map(p => `<option value="${p.id}" data-uom="${esc(p.uom || '')}" data-hsn="${esc(p.hsn_sac || '')}" data-price="${p.unit_price}" data-tax="${p.tax_rate_id || ''}">${esc(p.name)}</option>`).join(''));
    }
    $('#newItemProduct').on('change', function() {
        const opt = $(this).find(':selected');
        if (!opt.val()) return;
        $('#newItemDescription').val(opt.text());
        $('#newItemUom').val(opt.data('uom'));
        $('#newItemHsn').val(opt.data('hsn'));
        $('#newItemPrice').val(opt.data('price'));
        $('#newItemTax').val(opt.data('tax') || '');
    });

    function lineTotal(item) { return item.quantity * item.unit_price * (1 + item.tax_percent / 100); }

    function renderItems() {
        if (!items.length) {
            $('#itemsBody').html(`<tr><td colspan="8"><div class="items-empty"><i class="fa fa-inbox"></i>No line items yet — add the first one below.</div></td></tr>`);
        } else {
            $('#itemsBody').html(items.map((item, i) => `
                <tr>
                    <td><div class="item-desc">${esc(item.description)}</div></td>
                    <td>${esc(item.hsn_sac || '—')}</td>
                    <td>${item.uom ? `<span class="pill-tag">${esc(item.uom)}</span>` : ''}</td>
                    <td class="num">${item.quantity}</td>
                    <td class="num">${money(item.unit_price)}</td>
                    <td class="num">${item.tax_percent > 0 ? item.tax_percent + '%' : '—'}</td>
                    <td class="num" style="font-weight:600;">${money(lineTotal(item))}</td>
                    <td><button type="button" class="row-remove removeItemBtn" data-i="${i}" title="Remove"><i class="fa fa-trash-o"></i></button></td>
                </tr>`).join(''));
        }

        const subTotal = items.reduce((s, i) => s + i.quantity * i.unit_price, 0);
        const taxAmount = items.reduce((s, i) => s + (i.quantity * i.unit_price) * i.tax_percent / 100, 0);

        $('#totalsBox').html(`
            <div class="t-line"><span>Subtotal</span><span>${money(subTotal)}</span></div>
            <div class="t-line"><span>Tax</span><span>${money(taxAmount)}</span></div>
            <div class="t-line grand"><span>Total</span><span>${money(subTotal + taxAmount)}</span></div>`);
    }

    $('#addItemBtn').on('click', function() {
        const description = $('#newItemDescription').val() || $('#newItemProduct option:selected').text();
        if (!description) { $('#newItemDescription').focus(); return; }
        items.push({
            product_id: $('#newItemProduct').val() || null,
            description,
            uom: $('#newItemUom').val() || null,
            hsn_sac: $('#newItemHsn').val() || null,
            quantity: parseFloat($('#newItemQty').val()) || 1,
            unit_price: parseFloat($('#newItemPrice').val()) || 0,
            discount_percent: 0,
            tax_rate_id: $('#newItemTax').val() || null,
            tax_percent: (() => { const t = taxRates.find(r => String(r.id) === $('#newItemTax').val()); return t ? parseFloat(t.rate_percent) : 0; })(),
        });
        $('#newItemProduct').val(''); $('#newItemDescription').val(''); $('#newItemUom').val(''); $('#newItemHsn').val('');
        $('#newItemQty').val(1); $('#newItemPrice').val(''); $('#newItemTax').val('');
        renderItems();
    });
    $(document).on('click', '.removeItemBtn', function() { items.splice($(this).data('i'), 1); renderItems(); });

    $('#createPoForm').on('submit', function(e) {
        e.preventDefault();
        const vendorId = $('#vendorSelect').val();
        if (!vendorId) { $('#createError').removeClass('d-none').text('Choose a vendor.'); window.scrollTo(0, 0); return; }

        const payload = {
            vendor_id: vendorId,
            expected_delivery_date: $('#expectedDeliveryDate').val(),
            terms_conditions: $('#termsConditions').val(),
            items: items.map(i => ({
                product_id: i.product_id, description: i.description, uom: i.uom, hsn_sac: i.hsn_sac,
                quantity: i.quantity, unit_price: i.unit_price, tax_rate_id: i.tax_rate_id,
            })),
        };

        $('#generateBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Generating…');
        $.ajax({url: `{{ route('purchase_orders.store') }}`, method: 'POST', headers: {'X-CSRF-TOKEN': csrf}, data: payload, traditional: false})
            .done(response => { window.location.href = `{{ url('/purchase-orders') }}/${response.id}`; })
            .fail(xhr => {
                $('#generateBtn').prop('disabled', false).html('<i class="fa fa-magic mr-1"></i> Generate Purchase Order');
                $('#createError').removeClass('d-none').text(xhr.responseJSON?.message || 'Unable to create purchase order.');
                window.scrollTo(0, 0);
            });
    });

    loadVendors();
    loadTaxRates();
    loadProducts();
    renderItems();
})();
</script></body></html>
