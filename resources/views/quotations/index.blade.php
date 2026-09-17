<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>Quotations</title>
    <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}"><link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}"><link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}"><link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .crm-page-header{background:#fff;padding:20px 22px;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06);margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px}
        .crm-page-header h3{margin:0 0 6px;font-weight:700;font-size:18px;color:#111827}.crm-page-header p{margin:0;color:#6b7280;font-size:14px}
        .crm-page-header .btn{border-radius:10px;padding:8px 16px;font-weight:600}
        .quote-shell{background:#fff;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06);overflow:hidden}
        .quote-row{display:grid;grid-template-columns:150px minmax(180px,1fr) 110px 130px 110px;gap:14px;align-items:center;padding:16px 22px;border-bottom:1px solid #edf2f7;font-size:14.5px;text-decoration:none;color:inherit}
        .quote-row:hover{background:#f8fbff;color:inherit;text-decoration:none}
        .quote-number{font-weight:700;color:#1f2937}.quote-meta{font-size:12.5px;color:#6b7280}
        .filter-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px}
        .status-pill{padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase}
        .status-pill.draft{background:#f3f4f6;color:#6b7280}
        .status-pill.sent{background:#dbeafe;color:#1d4ed8}
        .status-pill.accepted{background:#dcfce7;color:#15803d}
        .status-pill.rejected{background:#fee2e2;color:#b91c1c}
        .status-pill.expired{background:#fef3c7;color:#92400e}
        @media(max-width:900px){.quote-row{grid-template-columns:1fr}}

        [data-theme="dark"] .crm-page-header,[data-theme="dark"] .quote-shell,[data-theme="dark"] .card{background:#1a1d2b;box-shadow:0 8px 24px rgba(0,0,0,.35)}
        [data-theme="dark"] .crm-page-header h3{color:#eef0f6}
        [data-theme="dark"] .crm-page-header p{color:#9aa1b5}
        [data-theme="dark"] .quote-row{border-bottom-color:#2a2e40}
        [data-theme="dark"] .quote-row:hover{background:#20233a}
        [data-theme="dark"] .quote-number{color:#eef0f6}
        [data-theme="dark"] .quote-meta{color:#9aa1b5}
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">
    <div class="crm-page-header"><div><h3>Quotations</h3><p>Quote from a lead or a deal, version it, and email it to the customer.</p></div>@can('quotations.create')<a href="{{ route('quotations.create_form') }}" class="btn btn-primary"><i class="fa fa-plus"></i> New Quotation</a>@endcan</div>
    <div class="card mb-3" style="border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06);border:none"><div class="card-body filter-grid">
        <input type="text" id="filterSearch" class="form-control" placeholder="Search number, lead or deal…">
        <select id="filterStatus" class="form-control"><option value="">All statuses</option><option value="draft">Draft</option><option value="sent">Sent</option><option value="accepted">Accepted</option><option value="rejected">Rejected</option><option value="expired">Expired</option></select>
    </div></div>
    <div class="quote-shell" id="quoteList"><div class="p-4 text-center text-muted">Loading quotations…</div></div>
</div>@include('include.footer')</div></div></div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script>
(() => {
    const esc = value => $('<div>').text(value ?? '').html();
    const money = value => Number(value ?? 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

    function loadQuotations() {
        const params = new URLSearchParams({search: $('#filterSearch').val(), status: $('#filterStatus').val()});
        $.get(`{{ route('quotations.data') }}?${params}`, response => render(response.data));
    }
    function render(quotations) {
        if (!quotations.length) { $('#quoteList').html('<div class="p-5 text-center text-muted"><i class="fa fa-file-text-o fa-2x mb-2"></i><br>No quotations match these filters.</div>'); return; }
        $('#quoteList').html(quotations.map(q => `<a class="quote-row" href="{{ url('/quotations') }}/${q.id}">
            <div><div class="quote-number">${esc(q.quotation_number)}</div><div class="quote-meta">v${q.version}</div></div>
            <div><div>${esc(q.lead ? q.lead.name : (q.deal ? q.deal.name : '—'))}</div><div class="quote-meta">${q.lead ? 'Lead' : (q.deal ? 'Deal' : '')}</div></div>
            <div><span class="status-pill ${esc(q.status)}">${esc(q.status)}</span></div>
            <div>${esc(q.currency)} ${money(q.total_amount)}</div>
            <div class="quote-meta">${esc(q.owner?.name || '—')}</div>
        </a>`).join(''));
    }
    $('.filter-grid select').on('change', loadQuotations);
    let searchTimer;
    $('#filterSearch').on('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(loadQuotations, 300); });
    loadQuotations();
})();
</script></body></html>
