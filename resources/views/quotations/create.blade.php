<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>New Quotation</title>
    <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}"><link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}"><link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .crm-page-header{background:#fff;padding:20px 22px;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06);margin-bottom:18px}
        .crm-page-header h3{margin:0;font-weight:700;font-size:18px;color:#111827}
        .card-box{background:#fff;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06);padding:22px;max-width:560px}
        .link-type-toggle{display:flex;gap:10px;margin-bottom:16px}
        .link-type-toggle label{flex:1;border:1px solid #e5e7eb;border-radius:10px;padding:12px;text-align:center;cursor:pointer;font-weight:600;color:#6b7280}
        .link-type-toggle input{display:none}
        .link-type-toggle input:checked + span{color:#fff}
        .link-type-toggle label:has(input:checked){background:#2563eb;border-color:#2563eb;color:#fff}
        [data-theme="dark"] .crm-page-header,[data-theme="dark"] .card-box{background:#1a1d2b;box-shadow:0 8px 24px rgba(0,0,0,.35)}
        [data-theme="dark"] .crm-page-header h3{color:#eef0f6}
        [data-theme="dark"] .link-type-toggle label{border-color:#2a2e40;color:#9aa1b5}
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">
    <div class="crm-page-header"><h3>New Quotation</h3></div>
    <form class="card-box" id="createQuoteForm">
        <div class="link-type-toggle">
            <label><input type="radio" name="link_type" value="lead" {{ $dealId ? '' : 'checked' }}><span>Quote a Lead</span></label>
            <label><input type="radio" name="link_type" value="deal" {{ $dealId ? 'checked' : '' }}><span>Quote a Deal</span></label>
        </div>
        <div class="form-group">
            <label id="recordLabel">Lead</label>
            <select class="form-control" id="recordSelect" required></select>
        </div>
        <div class="alert alert-danger d-none" id="createError"></div>
        <button type="submit" class="btn btn-primary">Create Draft</button>
        <a href="{{ route('quotations.index') }}" class="btn btn-light">Cancel</a>
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
        const response = await $.get(`{{ url('/quotations/link-options') }}/${type}`);
        const preselect = type === 'lead' ? preselectLead : preselectDeal;
        $('#recordSelect').html('<option value="">Select…</option>' + response.data.map(r => `<option value="${r.id}">${esc(r.label)}</option>`).join(''));
        if (preselect) $('#recordSelect').val(String(preselect));
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
