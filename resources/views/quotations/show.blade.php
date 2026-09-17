<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>Quotation</title>
    <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}"><link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}"><link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        :root{--border:#e6e9f0;--text-muted:#6b7280;--text-dark:#1f2937}
        .crm-page-header{background:#fff;padding:20px 22px;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06);margin-bottom:18px;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px}
        .crm-page-header h3{margin:0 0 4px;font-weight:700;font-size:20px;color:#111827}
        .card-box{background:#fff;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06);padding:20px;margin-bottom:18px}
        .card-box h5{font-weight:700;font-size:15px;margin-bottom:14px}
        .status-pill{padding:4px 12px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase}
        .status-pill.draft{background:#f3f4f6;color:#6b7280}
        .status-pill.sent{background:#dbeafe;color:#1d4ed8}
        .status-pill.accepted{background:#dcfce7;color:#15803d}
        .status-pill.rejected{background:#fee2e2;color:#b91c1c}
        .status-pill.expired{background:#fef3c7;color:#92400e}
        table.items-table{width:100%}
        table.items-table th{font-size:11px;text-transform:uppercase;color:var(--text-muted);border-bottom:1px solid var(--border);padding:8px 6px;text-align:left}
        table.items-table td{padding:8px 6px;border-bottom:1px solid var(--border);vertical-align:middle}
        table.items-table input,table.items-table select{font-size:13px;padding:5px 7px}
        .totals-box{max-width:320px;margin-left:auto;margin-top:12px}
        .totals-box .row-line{display:flex;justify-content:space-between;padding:4px 0;font-size:13.5px}
        .totals-box .row-line.grand{font-weight:700;font-size:16px;border-top:2px solid var(--text-dark);padding-top:8px;margin-top:4px}
        .field-row{display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:13.5px}
        .field-row:last-child{border-bottom:none}
        .field-row .label{color:var(--text-muted)}
        [data-theme="dark"]{--border:#2a2e40;--text-dark:#eef0f6;--text-muted:#9aa1b5}
        [data-theme="dark"] .crm-page-header,[data-theme="dark"] .card-box{background:#1a1d2b;box-shadow:0 8px 24px rgba(0,0,0,.35)}
        [data-theme="dark"] .crm-page-header h3{color:#eef0f6}
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">

    <div class="crm-page-header">
        <div>
            <h3 id="quoteNumber">Loading…</h3>
            <span class="status-pill" id="statusPill"></span>
        </div>
        <div>
            <a href="{{ route('quotations.index') }}" class="btn btn-light"><i class="fa fa-arrow-left"></i> Back</a>
            <a href="#" target="_blank" id="downloadPdfBtn" class="btn btn-outline-primary"><i class="fa fa-file-pdf-o"></i> Download PDF</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card-box">
                <h5><i class="fa fa-list"></i> Line Items</h5>
                <div class="table-responsive">
                    <table class="items-table">
                        <thead><tr><th style="width:26%">Product / Description</th><th>UOM</th><th>Qty</th><th>Unit Price</th><th>Disc %</th><th>Tax</th><th>Line Total</th><th></th></tr></thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>
                <div id="addItemRow" class="form-row mt-3" style="align-items:end;">
                    <div class="form-group col-md-3"><label>Product</label><select class="form-control form-control-sm" id="newItemProduct"><option value="">Custom line</option></select></div>
                    <div class="form-group col-md-2"><label>Description</label><input class="form-control form-control-sm" id="newItemDescription"></div>
                    <div class="form-group col-md-1"><label>UOM</label><input class="form-control form-control-sm" id="newItemUom"></div>
                    <div class="form-group col-md-1"><label>Qty</label><input type="number" step="0.01" class="form-control form-control-sm" id="newItemQty" value="1"></div>
                    <div class="form-group col-md-2"><label>Unit Price</label><input type="number" step="0.01" class="form-control form-control-sm" id="newItemPrice"></div>
                    <div class="form-group col-md-1"><label>Disc %</label><input type="number" step="0.01" class="form-control form-control-sm" id="newItemDiscount" value="0"></div>
                    <div class="form-group col-md-1"><label>Tax</label><select class="form-control form-control-sm" id="newItemTax"><option value="">None</option></select></div>
                    <div class="form-group col-md-1"><button type="button" class="btn btn-sm btn-primary" id="addItemBtn"><i class="fa fa-plus"></i></button></div>
                </div>
                <div class="totals-box" id="totalsBox"></div>
            </div>

            <div class="card-box">
                <h5><i class="fa fa-file-text-o"></i> Terms &amp; Conditions</h5>
                <textarea class="form-control" id="termsConditions" rows="5"></textarea>
                <button type="button" class="btn btn-sm btn-primary mt-2" id="saveDetailsBtn">Save Changes</button>
                <span class="text-muted ml-2" id="saveStatus"></span>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-box">
                <h5><i class="fa fa-info-circle"></i> Details</h5>
                <div class="field-row"><span class="label">Linked to</span><span id="linkedTo">—</span></div>
                <div class="field-row"><span class="label">Owner</span><span id="ownerName">—</span></div>
                <div class="form-group mt-3"><label>Valid Until</label><input type="date" class="form-control form-control-sm" id="validUntil"></div>
                <div class="form-group"><label>Currency</label><input type="text" class="form-control form-control-sm" id="currency" maxlength="10"></div>
                <div class="form-group mb-0"><label>Internal Notes</label><textarea class="form-control form-control-sm" id="notes" rows="3"></textarea></div>
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
    let taxRates = [];

    async function loadTaxRates() {
        const response = await $.get(`{{ route('master_data.tax_rates.data') }}`);
        taxRates = response.data.filter(t => t.is_active);
        $('#newItemTax').html('<option value="">None</option>' + taxRates.map(t => `<option value="${t.id}">${esc(t.name)} (${t.rate_percent}%)</option>`).join(''));
    }

    async function loadProducts() {
        const response = await $.get(`{{ route('products.options') }}`);
        $('#newItemProduct').html('<option value="">Custom line</option>' + response.data.map(p => `<option value="${p.id}" data-uom="${esc(p.uom || '')}" data-price="${p.unit_price}" data-tax="${p.tax_rate_id || ''}">${esc(p.name)}</option>`).join(''));
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
        $('#itemsBody').html((quotation.items || []).map(item => `
            <tr data-id="${item.id}">
                <td>${esc(item.description)}</td>
                <td>${esc(item.uom || '')}</td>
                <td>${esc(item.quantity)}</td>
                <td>${money(item.unit_price)}</td>
                <td>${item.discount_percent}%</td>
                <td>${item.tax_percent}%</td>
                <td>${money(item.line_total)}</td>
                <td>${quotation.is_editable ? `<button type="button" class="btn btn-sm btn-link text-danger removeItemBtn"><i class="fa fa-trash"></i></button>` : ''}</td>
            </tr>`).join('') || '<tr><td colspan="8" class="text-center text-muted py-3">No items yet — add the first line below.</td></tr>');

        $('#totalsBox').html(`
            <div class="row-line"><span>Sub Total</span><span>${quotation.currency} ${money(quotation.sub_total)}</span></div>
            <div class="row-line"><span>Discount</span><span>- ${quotation.currency} ${money(quotation.discount_amount)}</span></div>
            <div class="row-line"><span>Tax</span><span>${quotation.currency} ${money(quotation.tax_amount)}</span></div>
            <div class="row-line grand"><span>Total</span><span>${quotation.currency} ${money(quotation.total_amount)}</span></div>`);

        $('#addItemRow').toggle(!!quotation.is_editable);
        $('#saveDetailsBtn').toggle(!!quotation.is_editable);
    }

    function render() {
        $('#quoteNumber').text(`${quotation.quotation_number} (v${quotation.version})`);
        $('#statusPill').attr('class', `status-pill ${quotation.status}`).text(quotation.status);
        $('#downloadPdfBtn').attr('href', `{{ url('/quotations') }}/${quotationId}/pdf`);
        $('#linkedTo').text(quotation.lead ? `Lead: ${quotation.lead.name}` : (quotation.deal ? `Deal: ${quotation.deal.name}` : '—'));
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
        if (!description) return;
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
            .done(() => { $('#saveStatus').text('Saved ✓').fadeIn(); setTimeout(() => $('#saveStatus').text(''), 2000); loadQuotation(); })
            .fail(xhr => alert(xhr.responseJSON?.message || 'Unable to save.'));
    });

    loadTaxRates();
    loadProducts();
    loadQuotation();
})();
</script></body></html>
