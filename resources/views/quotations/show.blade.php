<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>Quotation</title>
    <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}"><link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}"><link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
    <style>
        :root{
            --ink:#101828; --muted:#667085; --faint:#98a2b3; --border:#e4e7ec; --line:#eef1f5;
            --bg:#f5f6fa; --card:#fff; --accent:#4f46e5; --accent-soft:#eef2ff; --accent-dark:#4338ca;
            --draft-bg:#f2f4f7; --draft-fg:#475467;
            --sent-bg:#eaf1ff; --sent-fg:#175cd3;
            --accepted-bg:#e7f7ef; --accepted-fg:#087443;
            --rejected-bg:#fef1f1; --rejected-fg:#b42318;
            --expired-bg:#fef6e7; --expired-fg:#b25e09;
        }
        [data-theme="dark"]{
            --ink:#eef0f6; --muted:#9aa1b5; --faint:#71798f; --border:#2a2e40; --line:#252838;
            --bg:#11131c; --card:#181b28; --accent:#818cf8; --accent-soft:#252a4a; --accent-dark:#a5b0ff;
            --draft-bg:#242838; --draft-fg:#9aa1b5;
            --sent-bg:#1c2a4a; --sent-fg:#93b6ff;
            --accepted-bg:#173428; --accepted-fg:#5fd394;
            --rejected-bg:#3a1f20; --rejected-fg:#ff9c94;
            --expired-bg:#3a2c14; --expired-fg:#f0b95c;
        }
        body{font-family:"Inter",ui-sans-serif,system-ui,sans-serif;}
        .content-wrapper{background:var(--bg);}

        /* ---- page header ---- */
        .q-header{
            display:flex; justify-content:space-between; align-items:flex-start; gap:18px;
            flex-wrap:wrap; margin-bottom:20px;
        }
        .q-header .titleblock{display:flex; align-items:center; gap:14px; flex-wrap:wrap;}
        .q-header h1{
            font-size:24px; font-weight:800; color:var(--ink); margin:0; letter-spacing:-.01em;
            font-variant-numeric:tabular-nums;
        }
        .q-header .version-tag{font-size:13px; color:var(--faint); font-weight:600;}
        .q-actions{display:flex; gap:10px; align-items:center;}
        .btn-ghost{
            background:var(--card); border:1px solid var(--border); color:var(--ink);
            border-radius:10px; padding:9px 16px; font-weight:600; font-size:13.5px;
            display:inline-flex; align-items:center; gap:8px; text-decoration:none;
        }
        .btn-ghost:hover{background:var(--line); color:var(--ink); text-decoration:none;}
        .btn-accent{
            background:var(--accent); border:1px solid var(--accent); color:#fff;
            border-radius:10px; padding:9px 18px; font-weight:600; font-size:13.5px;
            display:inline-flex; align-items:center; gap:8px; text-decoration:none;
        }
        .btn-accent:hover{background:var(--accent-dark); border-color:var(--accent-dark); color:#fff; text-decoration:none;}

        /* ---- status pill ---- */
        .status-pill{
            padding:5px 13px; border-radius:999px; font-size:11px; font-weight:700;
            text-transform:uppercase; letter-spacing:.04em; display:inline-block;
        }
        .status-pill.draft{background:var(--draft-bg); color:var(--draft-fg);}
        .status-pill.sent{background:var(--sent-bg); color:var(--sent-fg);}
        .status-pill.accepted{background:var(--accepted-bg); color:var(--accepted-fg);}
        .status-pill.rejected{background:var(--rejected-bg); color:var(--rejected-fg);}
        .status-pill.expired{background:var(--expired-bg); color:var(--expired-fg);}

        /* ---- the "document" ---- */
        .q-layout{display:grid; grid-template-columns:1fr 300px; gap:22px; align-items:start;}
        @media(max-width:1100px){.q-layout{grid-template-columns:1fr;}}

        .paper{
            background:var(--card); border-radius:16px; border:1px solid var(--border);
            box-shadow:0 1px 2px rgba(16,24,40,.04), 0 8px 28px rgba(16,24,40,.05);
            padding:26px 28px; margin-bottom:22px;
        }
        .paper .section-title{
            font-size:13px; font-weight:700; color:var(--ink); text-transform:uppercase;
            letter-spacing:.05em; margin-bottom:16px; display:flex; align-items:center; gap:8px;
        }
        .paper .section-title i{color:var(--accent); font-size:13px;}

        /* ---- line items ---- */
        .items-wrap{overflow-x:auto; margin:0 -4px;}
        table.items-table{width:100%; border-collapse:collapse; min-width:760px;}
        table.items-table thead th{
            font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; color:var(--faint);
            font-weight:700; padding:0 10px 10px; text-align:left; border-bottom:2px solid var(--line);
            white-space:nowrap;
        }
        table.items-table thead th.num{text-align:right;}
        table.items-table tbody td{
            padding:13px 10px; border-bottom:1px solid var(--line); font-size:14px; color:var(--ink);
            vertical-align:middle;
        }
        table.items-table tbody tr:last-child td{border-bottom:none;}
        table.items-table td.num{text-align:right; font-variant-numeric:tabular-nums;}
        table.items-table .item-desc{font-weight:600;}
        table.items-table .item-sub{font-size:12px; color:var(--muted); font-weight:400;}
        table.items-table .pill-tag{
            display:inline-block; font-size:11px; font-weight:600; color:var(--muted);
            background:var(--line); border-radius:6px; padding:2px 8px;
        }
        .row-remove{
            width:28px; height:28px; border-radius:7px; border:none; background:transparent;
            color:var(--faint); display:inline-flex; align-items:center; justify-content:center; cursor:pointer;
        }
        .row-remove:hover{background:var(--rejected-bg); color:var(--rejected-fg);}
        .items-empty{padding:34px 10px; text-align:center; color:var(--muted); font-size:14px;}
        .items-empty i{font-size:26px; color:var(--faint); display:block; margin-bottom:8px;}

        /* ---- add-item row ---- */
        .add-row{
            display:grid; grid-template-columns:1.6fr 1fr .7fr .8fr 1fr .9fr 1fr auto;
            gap:10px; align-items:end; margin-top:16px; padding-top:16px; border-top:1px dashed var(--border);
        }
        @media(max-width:1100px){.add-row{grid-template-columns:repeat(2,1fr);}}
        .add-row label{
            font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; color:var(--faint);
            font-weight:700; margin-bottom:5px; display:block;
        }
        .add-row .form-control{
            border-radius:8px; border:1px solid var(--border); font-size:13.5px; padding:8px 10px;
            background:var(--card); color:var(--ink); height:auto;
        }
        .add-row .form-control:focus{border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-soft);}
        .btn-add-line{
            width:36px; height:36px; border-radius:8px; border:none; background:var(--accent); color:#fff;
            display:inline-flex; align-items:center; justify-content:center; cursor:pointer; font-size:15px;
        }
        .btn-add-line:hover{background:var(--accent-dark);}

        /* ---- totals ---- */
        .totals-wrap{display:flex; justify-content:flex-end; margin-top:18px;}
        .totals-box{width:280px;}
        .totals-box .t-line{
            display:flex; justify-content:space-between; padding:6px 0; font-size:13.5px; color:var(--muted);
            font-variant-numeric:tabular-nums;
        }
        .totals-box .t-line.grand{
            font-weight:800; font-size:19px; color:var(--ink); border-top:2px solid var(--ink);
            padding-top:12px; margin-top:6px;
        }

        /* ---- sidebar cards ---- */
        .side-card{
            background:var(--card); border-radius:14px; border:1px solid var(--border);
            box-shadow:0 1px 2px rgba(16,24,40,.04); padding:20px; margin-bottom:18px;
        }
        .side-card .section-title{
            font-size:12px; font-weight:700; color:var(--faint); text-transform:uppercase;
            letter-spacing:.05em; margin-bottom:14px;
        }
        .kv-row{display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px solid var(--line); font-size:13.5px;}
        .kv-row:last-child{border-bottom:none;}
        .kv-row .k{color:var(--muted);}
        .kv-row .v{color:var(--ink); font-weight:600; text-align:right;}
        .side-field{margin-top:14px;}
        .side-field label{
            font-size:10.5px; text-transform:uppercase; letter-spacing:.04em; color:var(--faint);
            font-weight:700; margin-bottom:5px; display:block;
        }
        .side-field .form-control{
            border-radius:8px; border:1px solid var(--border); font-size:13.5px; background:var(--card); color:var(--ink);
        }
        .side-field .form-control:disabled{background:var(--line); color:var(--muted);}

        textarea#termsConditions{
            border-radius:10px; border:1px solid var(--border); font-size:13.5px; background:var(--card);
            color:var(--ink); line-height:1.6;
        }
        textarea#termsConditions:disabled{background:var(--line); color:var(--muted);}

        .save-bar{display:flex; align-items:center; gap:10px; margin-top:14px;}
        #saveStatus{font-size:12.5px; color:var(--accepted-fg); font-weight:600;}

        [data-theme="dark"] .row-remove:hover{background:rgba(180,35,24,.22);}
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">

    <div class="q-header">
        <div class="titleblock">
            <h1 id="quoteNumber">Loading…</h1>
            <span class="version-tag" id="versionTag"></span>
            <span class="status-pill" id="statusPill"></span>
        </div>
        <div class="q-actions">
            <a href="{{ route('quotations.index') }}" class="btn-ghost"><i class="fa fa-arrow-left"></i> Back</a>
            <a href="#" target="_blank" id="downloadPdfBtn" class="btn-accent"><i class="fa fa-download"></i> Download PDF</a>
        </div>
    </div>

    <div class="q-layout">
        <div>
            <div class="paper">
                <div class="section-title"><i class="fa fa-list-ul"></i> Line Items</div>

                <div class="items-wrap">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width:30%">Item</th>
                                <th>UOM</th>
                                <th class="num">Qty</th>
                                <th class="num">Unit Price</th>
                                <th class="num">Discount</th>
                                <th class="num">Tax</th>
                                <th class="num">Amount</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>

                <div id="addItemRow" class="add-row">
                    <div>
                        <label>Product / Description</label>
                        <select class="form-control" id="newItemProduct" style="margin-bottom:6px;"><option value="">— Custom line —</option></select>
                        <input class="form-control" id="newItemDescription" placeholder="Line description">
                    </div>
                    <div><label>UOM</label><input class="form-control" id="newItemUom" placeholder="Nos"></div>
                    <div><label>Qty</label><input type="number" step="0.01" class="form-control" id="newItemQty" value="1"></div>
                    <div><label>Unit Price</label><input type="number" step="0.01" class="form-control" id="newItemPrice" placeholder="0.00"></div>
                    <div><label>Discount %</label><input type="number" step="0.01" class="form-control" id="newItemDiscount" value="0"></div>
                    <div><label>Tax</label><select class="form-control" id="newItemTax"><option value="">None</option></select></div>
                    <div></div>
                    <div><button type="button" class="btn-add-line" id="addItemBtn" title="Add line"><i class="fa fa-plus"></i></button></div>
                </div>

                <div class="totals-wrap">
                    <div class="totals-box" id="totalsBox"></div>
                </div>
            </div>

            <div class="paper">
                <div class="section-title"><i class="fa fa-file-text-o"></i> Terms &amp; Conditions</div>
                <textarea class="form-control" id="termsConditions" rows="5" placeholder="Payment terms, delivery schedule, warranty…"></textarea>
                <div class="save-bar">
                    <button type="button" class="btn-accent" id="saveDetailsBtn">Save Changes</button>
                    <span id="saveStatus"></span>
                </div>
            </div>
        </div>

        <div>
            <div class="side-card">
                <div class="section-title">Quote Details</div>
                <div class="kv-row"><span class="k">Linked to</span><span class="v" id="linkedTo">—</span></div>
                <div class="kv-row"><span class="k">Owner</span><span class="v" id="ownerName">—</span></div>
                <div class="side-field"><label>Valid Until</label><input type="date" class="form-control" id="validUntil"></div>
                <div class="side-field"><label>Currency</label><input type="text" class="form-control" id="currency" maxlength="10"></div>
            </div>

            <div class="side-card">
                <div class="section-title">Internal Notes</div>
                <textarea class="form-control" id="notes" rows="4" placeholder="Not shown to the customer"></textarea>
            </div>
        </div>
    </div>

</div>@include('include.footer')</div></div></div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const quotationId = {{ $quotationId }};
    const esc = value => $('<div>').text(value ?? '').html();
    const money = value => Number(value ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    let quotation = null;

    async function loadTaxRates() {
        const response = await $.get(`{{ route('master_data.tax_rates.data') }}`);
        const taxRates = response.data.filter(t => t.is_active);
        $('#newItemTax').html('<option value="">None</option>' + taxRates.map(t => `<option value="${t.id}">${esc(t.name)} (${t.rate_percent}%)</option>`).join(''));
    }

    async function loadProducts() {
        const response = await $.get(`{{ route('products.options') }}`);
        $('#newItemProduct').html('<option value="">— Custom line —</option>' + response.data.map(p => `<option value="${p.id}" data-uom="${esc(p.uom || '')}" data-price="${p.unit_price}" data-tax="${p.tax_rate_id || ''}">${esc(p.name)}</option>`).join(''));
    }

    $('#newItemProduct').on('change', function() {
        const opt = $(this).find(':selected');
        if (!opt.val()) return;
        $('#newItemDescription').val(opt.text());
        $('#newItemUom').val(opt.data('uom'));
        $('#newItemPrice').val(opt.data('price'));
        $('#newItemTax').val(opt.data('tax') || '');
    });

    function renderItems() {
        const rows = quotation.items || [];
        if (!rows.length) {
            $('#itemsBody').html(`<tr><td colspan="8"><div class="items-empty"><i class="fa fa-inbox"></i>No line items yet — add the first one below.</div></td></tr>`);
        } else {
            $('#itemsBody').html(rows.map(item => `
                <tr data-id="${item.id}">
                    <td><div class="item-desc">${esc(item.description)}</div>${item.product?.name && item.product.name !== item.description ? `<div class="item-sub">${esc(item.product.name)}</div>` : ''}</td>
                    <td>${item.uom ? `<span class="pill-tag">${esc(item.uom)}</span>` : ''}</td>
                    <td class="num">${esc(item.quantity)}</td>
                    <td class="num">${money(item.unit_price)}</td>
                    <td class="num">${item.discount_percent > 0 ? item.discount_percent + '%' : '—'}</td>
                    <td class="num">${item.tax_percent > 0 ? item.tax_percent + '%' : '—'}</td>
                    <td class="num" style="font-weight:600;">${money(item.line_total)}</td>
                    <td>${quotation.is_editable ? `<button type="button" class="row-remove removeItemBtn" title="Remove"><i class="fa fa-trash-o"></i></button>` : ''}</td>
                </tr>`).join(''));
        }

        $('#totalsBox').html(`
            <div class="t-line"><span>Subtotal</span><span>${quotation.currency} ${money(quotation.sub_total)}</span></div>
            <div class="t-line"><span>Discount</span><span>− ${quotation.currency} ${money(quotation.discount_amount)}</span></div>
            <div class="t-line"><span>Tax</span><span>${quotation.currency} ${money(quotation.tax_amount)}</span></div>
            <div class="t-line grand"><span>Total</span><span>${quotation.currency} ${money(quotation.total_amount)}</span></div>`);

        $('#addItemRow').toggle(!!quotation.is_editable);
        $('#saveDetailsBtn').toggle(!!quotation.is_editable);
    }

    function render() {
        $('#quoteNumber').text(quotation.quotation_number);
        $('#versionTag').text(`Version ${quotation.version}`);
        $('#statusPill').attr('class', `status-pill ${quotation.status}`).text(quotation.status);
        $('#downloadPdfBtn').attr('href', `{{ url('/quotations') }}/${quotationId}/pdf`);
        $('#linkedTo').text(quotation.lead ? quotation.lead.name : (quotation.deal ? quotation.deal.name : '—'));
        $('#ownerName').text(quotation.owner?.name || '—');
        $('#validUntil').val(quotation.valid_until ? quotation.valid_until.slice(0, 10) : '').prop('disabled', !quotation.is_editable);
        $('#currency').val(quotation.currency).prop('disabled', !quotation.is_editable);
        $('#notes').val(quotation.notes).prop('disabled', !quotation.is_editable);
        $('#termsConditions').val(quotation.terms_conditions).prop('disabled', !quotation.is_editable);
        renderItems();
    }

    function loadQuotation() {
        $.get(`{{ url('/quotations') }}/${quotationId}/detail`, response => { quotation = response.data; render(); });
    }

    $('#addItemBtn').on('click', function() {
        const description = $('#newItemDescription').val() || $('#newItemProduct option:selected').text();
        if (!description) { $('#newItemDescription').focus(); return; }
        const data = {
            product_id: $('#newItemProduct').val() || '',
            description,
            uom: $('#newItemUom').val(),
            quantity: $('#newItemQty').val() || 1,
            unit_price: $('#newItemPrice').val() || 0,
            discount_percent: $('#newItemDiscount').val() || 0,
            tax_rate_id: $('#newItemTax').val() || '',
        };
        $.ajax({url: `{{ url('/quotations') }}/${quotationId}/items`, method: 'POST', headers: {'X-CSRF-TOKEN': csrf}, data})
            .done(() => {
                $('#newItemProduct').val(''); $('#newItemDescription').val(''); $('#newItemUom').val('');
                $('#newItemQty').val(1); $('#newItemPrice').val(''); $('#newItemDiscount').val(0); $('#newItemTax').val('');
                loadQuotation();
            })
            .fail(xhr => alert(xhr.responseJSON?.message || 'Unable to add item.'));
    });

    $(document).on('click', '.removeItemBtn', function() {
        const itemId = $(this).closest('tr').data('id');
        if (!confirm('Remove this line item?')) return;
        $.ajax({url: `{{ url('/quotations') }}/${quotationId}/items/${itemId}`, method: 'DELETE', headers: {'X-CSRF-TOKEN': csrf}}).done(loadQuotation);
    });

    $('#saveDetailsBtn').on('click', function() {
        const data = {
            valid_until: $('#validUntil').val(),
            currency: $('#currency').val(),
            notes: $('#notes').val(),
            terms_conditions: $('#termsConditions').val(),
        };
        $.ajax({url: `{{ url('/quotations') }}/${quotationId}`, method: 'PUT', headers: {'X-CSRF-TOKEN': csrf}, data})
            .done(() => { $('#saveStatus').text('Saved ✓'); setTimeout(() => $('#saveStatus').text(''), 2000); loadQuotation(); })
            .fail(xhr => alert(xhr.responseJSON?.message || 'Unable to save.'));
    });

    loadTaxRates();
    loadProducts();
    loadQuotation();
})();
</script></body></html>
