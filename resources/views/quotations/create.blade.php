<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>New Quotation</title>
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
        .page-head{margin-bottom:22px;}
        .page-head h1{font-size:24px; font-weight:800; color:var(--ink); margin:0 0 4px; letter-spacing:-.01em;}
        .page-head p{color:var(--muted); font-size:14px; margin:0;}

        .wizard{
            background:var(--card); border-radius:16px; border:1px solid var(--border);
            box-shadow:0 1px 2px rgba(16,24,40,.04), 0 8px 28px rgba(16,24,40,.05);
            padding:30px; max-width:620px;
        }
        .field-label{font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; margin-bottom:12px;}

        .type-choice{display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:26px;}
        .type-choice input{display:none;}
        .type-choice .choice-card{
            border:1.5px solid var(--border); border-radius:12px; padding:18px 16px; cursor:pointer;
            display:flex; flex-direction:column; align-items:flex-start; gap:8px; transition:border-color .12s, background .12s;
        }
        .type-choice .choice-card i{font-size:20px; color:var(--faint);}
        .type-choice .choice-card .t{font-weight:700; color:var(--ink); font-size:14.5px;}
        .type-choice .choice-card .d{font-size:12.5px; color:var(--muted);}
        .type-choice input:checked + .choice-card{border-color:var(--accent); background:var(--accent-soft);}
        .type-choice input:checked + .choice-card i,
        .type-choice input:checked + .choice-card .t{color:var(--accent-dark);}

        .record-field label{font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px; display:block;}
        .record-field select{
            border-radius:10px; border:1px solid var(--border); font-size:14px; padding:11px 14px; height:auto;
            background:var(--card); color:var(--ink); width:100%;
        }
        .record-field select:focus{border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-soft); outline:none;}
        .record-hint{font-size:12.5px; color:var(--faint); margin-top:8px;}

        .wizard-actions{display:flex; gap:10px; margin-top:26px;}
        .btn-accent{
            background:var(--accent); border:1px solid var(--accent); color:#fff;
            border-radius:10px; padding:11px 22px; font-weight:600; font-size:14px;
        }
        .btn-accent:hover{background:var(--accent-dark); border-color:var(--accent-dark); color:#fff;}
        .btn-ghost{
            background:var(--card); border:1px solid var(--border); color:var(--ink);
            border-radius:10px; padding:11px 20px; font-weight:600; font-size:14px; text-decoration:none;
        }
        .btn-ghost:hover{background:var(--line); color:var(--ink); text-decoration:none;}
        .alert-inline{
            background:#fef1f1; border:1px solid #fda29b; color:#b42318; border-radius:10px;
            padding:10px 14px; font-size:13.5px; margin-top:18px;
        }
        [data-theme="dark"] .alert-inline{background:#3a1f20; border-color:#7a2f2a; color:#ff9c94;}
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">

    <div class="page-head">
        <h1>New Quotation</h1>
        <p>Pick what you're quoting — you'll add line items, pricing and terms on the next screen.</p>
    </div>

    <form class="wizard" id="createQuoteForm">
        <div class="field-label">What are you quoting?</div>
        <div class="type-choice">
            <label>
                <input type="radio" name="link_type" value="lead" {{ $dealId ? '' : 'checked' }}>
                <div class="choice-card"><i class="fa fa-bullseye"></i><span class="t">A Lead</span><span class="d">Quote a prospect before they become a deal</span></div>
            </label>
            <label>
                <input type="radio" name="link_type" value="deal" {{ $dealId ? 'checked' : '' }}>
                <div class="choice-card"><i class="fa fa-briefcase"></i><span class="t">A Deal</span><span class="d">Quote an active opportunity in your pipeline</span></div>
            </label>
        </div>

        <div class="record-field">
            <label id="recordLabel">Lead</label>
            <select id="recordSelect" required>
                <option value="">Select…</option>
            </select>
            <div class="record-hint" id="recordHint"></div>
        </div>

        <div class="alert-inline d-none" id="createError"></div>

        <div class="wizard-actions">
            <button type="submit" class="btn-accent"><i class="fa fa-plus mr-1"></i> Create Draft</button>
            <a href="{{ route('quotations.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>

</div>@include('include.footer')</div></div></div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const esc = value => $('<div>').text(value ?? '').html();
    const preselectLead = {{ $leadId ?? 'null' }};
    const preselectDeal = {{ $dealId ?? 'null' }};

    async function loadRecords() {
        const type = $('input[name="link_type"]:checked').val();
        $('#recordLabel').text(type === 'lead' ? 'Lead' : 'Deal');
        $('#recordHint').text('Loading…');
        const response = await $.get(`{{ url('/quotations/link-options') }}/${type}`);
        const preselect = type === 'lead' ? preselectLead : preselectDeal;
        $('#recordSelect').html('<option value="">Select…</option>' + response.data.map(r => `<option value="${r.id}">${esc(r.label)}</option>`).join(''));
        if (preselect) $('#recordSelect').val(String(preselect));
        $('#recordHint').text(response.data.length ? `${response.data.length} available` : `No ${type}s to quote yet.`);
    }
    $('input[name="link_type"]').on('change', loadRecords);
    loadRecords();

    $('#createQuoteForm').on('submit', function(e) {
        e.preventDefault();
        const type = $('input[name="link_type"]:checked').val();
        const recordId = $('#recordSelect').val();
        if (!recordId) { $('#createError').removeClass('d-none').text('Choose a record to quote.'); return; }

        const data = { [type === 'lead' ? 'lead_id' : 'deal_id']: recordId };
        $.ajax({url: `{{ route('quotations.store') }}`, method: 'POST', headers: {'X-CSRF-TOKEN': csrf}, data})
            .done(response => { window.location.href = `{{ url('/quotations') }}/${response.id}`; })
            .fail(xhr => $('#createError').removeClass('d-none').text(xhr.responseJSON?.message || 'Unable to create quotation.'));
    });
})();
</script></body></html>
