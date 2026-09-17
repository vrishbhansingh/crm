<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>Quotations</title>
    <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}"><link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}"><link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}"><link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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

        .page-head{display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:20px;}
        .page-head h1{font-size:24px; font-weight:800; color:var(--ink); margin:0 0 4px; letter-spacing:-.01em;}
        .page-head p{color:var(--muted); font-size:14px; margin:0;}
        .btn-accent{
            background:var(--accent); border:1px solid var(--accent); color:#fff; border-radius:10px;
            padding:11px 20px; font-weight:600; font-size:14px; text-decoration:none; display:inline-flex; align-items:center; gap:8px;
        }
        .btn-accent:hover{background:var(--accent-dark); border-color:var(--accent-dark); color:#fff; text-decoration:none;}

        .filter-bar{display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px;}
        .filter-bar input, .filter-bar select{
            border-radius:10px; border:1px solid var(--border); font-size:13.5px; padding:9px 13px;
            background:var(--card); color:var(--ink);
        }
        .filter-bar input{flex:1; min-width:220px;}
        .filter-bar select{min-width:160px;}
        .filter-bar input:focus, .filter-bar select:focus{border-color:var(--accent); outline:none; box-shadow:0 0 0 3px var(--accent-soft);}

        .quote-shell{
            background:var(--card); border-radius:14px; border:1px solid var(--border);
            box-shadow:0 1px 2px rgba(16,24,40,.04); overflow:hidden;
        }
        .quote-row{
            display:grid; grid-template-columns:170px minmax(180px,1fr) 120px 140px 130px;
            gap:14px; align-items:center; padding:16px 22px; border-bottom:1px solid var(--line);
            text-decoration:none; color:inherit;
        }
        .quote-row:hover{background:var(--line); color:inherit; text-decoration:none;}
        .quote-row:last-child{border-bottom:none;}
        .quote-number{font-weight:700; color:var(--ink); font-variant-numeric:tabular-nums;}
        .quote-meta{font-size:12px; color:var(--faint);}
        .cell-label{font-size:11px; color:var(--faint); text-transform:uppercase; letter-spacing:.03em;}
        .amount{font-variant-numeric:tabular-nums; font-weight:600; color:var(--ink);}

        .status-pill{padding:5px 12px; border-radius:999px; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; display:inline-block;}
        .status-pill.draft{background:var(--draft-bg); color:var(--draft-fg);}
        .status-pill.sent{background:var(--sent-bg); color:var(--sent-fg);}
        .status-pill.accepted{background:var(--accepted-bg); color:var(--accepted-fg);}
        .status-pill.rejected{background:var(--rejected-bg); color:var(--rejected-fg);}
        .status-pill.expired{background:var(--expired-bg); color:var(--expired-fg);}

        .empty-state{padding:60px 20px; text-align:center; color:var(--muted);}
        .empty-state i{font-size:34px; color:var(--faint); display:block; margin-bottom:12px;}
        .empty-state .t{font-weight:600; color:var(--ink); margin-bottom:4px;}
        .empty-state .d{font-size:13.5px;}

        @media(max-width:900px){
            .quote-row{grid-template-columns:1fr; gap:4px;}
            .quote-row > div::before{content:attr(data-label); display:block; font-size:10px; text-transform:uppercase; color:var(--faint); letter-spacing:.03em;}
        }
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">
    <div class="page-head">
        <div>
            <h1>Quotations</h1>
            <p>Quote from a lead or a deal, version it, and email it to the customer.</p>
        </div>
        @can('quotations.create')
        <a href="{{ route('quotations.create_form') }}" class="btn-accent"><i class="fa fa-plus"></i> New Quotation</a>
        @endcan
    </div>

    <div class="filter-bar">
        <input type="text" id="filterSearch" placeholder="Search number, lead or deal…">
        <select id="filterStatus">
            <option value="">All statuses</option>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="accepted">Accepted</option>
            <option value="rejected">Rejected</option>
            <option value="expired">Expired</option>
        </select>
    </div>

    <div class="quote-shell" id="quoteList"><div class="empty-state"><i class="fa fa-spinner fa-spin"></i><div class="t">Loading quotations…</div></div></div>
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
        if (!quotations.length) {
            $('#quoteList').html(`<div class="empty-state"><i class="fa fa-file-text-o"></i><div class="t">No quotations yet</div><div class="d">Create one from a lead or a deal to get started.</div></div>`);
            return;
        }
        $('#quoteList').html(quotations.map(q => `<a class="quote-row" href="{{ url('/quotations') }}/${q.id}">
            <div data-label="Number"><div class="quote-number">${esc(q.quotation_number)}</div><div class="quote-meta">v${q.version}</div></div>
            <div data-label="Quoted to"><div>${esc(q.lead ? q.lead.name : (q.deal ? q.deal.name : '—'))}</div><div class="quote-meta">${q.lead ? 'Lead' : (q.deal ? 'Deal' : '')}</div></div>
            <div data-label="Status"><span class="status-pill ${esc(q.status)}">${esc(q.status)}</span></div>
            <div data-label="Total" class="amount">${esc(q.currency)} ${money(q.total_amount)}</div>
            <div data-label="Owner" class="quote-meta">${esc(q.owner?.name || '—')}</div>
        </a>`).join(''));
    }
    $('#filterStatus').on('change', loadQuotations);
    let searchTimer;
    $('#filterSearch').on('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(loadQuotations, 300); });
    loadQuotations();
})();
</script></body></html>
