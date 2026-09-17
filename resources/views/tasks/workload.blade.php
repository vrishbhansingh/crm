<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>Task Workload</title>
    <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}"><link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}"><link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}"><link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .crm-page-header{background:#fff;padding:20px 22px;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06);margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px}
        .crm-page-header h3{margin:0 0 6px;font-weight:700;font-size:18px;color:#111827}.crm-page-header p{margin:0;color:#6b7280;font-size:14px}
        .crm-page-header .btn{border-radius:10px;padding:8px 16px;font-weight:600}
        .workload-shell{background:#fff;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06);overflow:hidden}
        .workload-row{display:grid;grid-template-columns:minmax(160px,1fr) 90px 90px 90px 130px;gap:14px;align-items:center;padding:16px 22px;border-bottom:1px solid #edf2f7;font-size:14.5px}
        .workload-row.head{font-size:12px;text-transform:uppercase;letter-spacing:.03em;color:#6b7280;font-weight:700;background:#f8fafc}
        .workload-row:last-child{border-bottom:none}
        .workload-name{font-weight:600;color:#1f2937}
        .num{font-variant-numeric:tabular-nums;font-weight:600}
        .num.overdue{color:#dc2626}
        .unassigned-banner{padding:14px 22px;background:#fffbeb;border-bottom:1px solid #fde68a;color:#92400e;font-size:14px}
        @media(max-width:760px){.workload-row{grid-template-columns:1fr;gap:4px;padding:14px 18px}.workload-row.head{display:none}.workload-row > div::before{content:attr(data-label);display:inline-block;min-width:110px;color:#6b7280;font-size:11px;text-transform:uppercase}}

        [data-theme="dark"] .crm-page-header,[data-theme="dark"] .workload-shell{background:#1a1d2b;box-shadow:0 8px 24px rgba(0,0,0,.35)}
        [data-theme="dark"] .crm-page-header h3{color:#eef0f6}
        [data-theme="dark"] .crm-page-header p{color:#9aa1b5}
        [data-theme="dark"] .workload-row{border-bottom-color:#2a2e40}
        [data-theme="dark"] .workload-row.head{background:#20233a;color:#9aa1b5}
        [data-theme="dark"] .workload-name{color:#eef0f6}
        [data-theme="dark"] .unassigned-banner{background:#2c2413;border-bottom-color:#4a3c1c;color:#e8b95c}
    </style>
</head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">
    <div class="crm-page-header"><div><h3>Task Workload</h3><p>Who's carrying what, right now.</p></div><a href="{{ route('tasks.index') }}" class="btn btn-light"><i class="fa fa-arrow-left"></i> Back to Tasks</a></div>
    <div class="workload-shell">
        <div id="unassignedBanner"></div>
        <div class="workload-row head"><div>Assignee</div><div>Open</div><div>Overdue</div><div>Due today</div><div>Completed (7d)</div></div>
        <div id="workloadRows"><div class="p-4 text-center text-muted">Loading…</div></div>
    </div>
</div>@include('include.footer')</div></div></div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
<script>
(() => {
    const esc = value => $('<div>').text(value ?? '').html();
    $.get(`{{ route('tasks.workload_data') }}`, response => {
        if (response.unassigned > 0) {
            $('#unassignedBanner').html(`<div class="unassigned-banner"><i class="fa fa-inbox"></i> ${response.unassigned} unassigned task(s) waiting to be claimed.</div>`);
        }
        if (!response.data.length) {
            $('#workloadRows').html('<div class="p-4 text-center text-muted">No active users to show.</div>');
            return;
        }
        $('#workloadRows').html(response.data.map(row => `
            <div class="workload-row">
                <div class="workload-name" data-label="Assignee">${esc(row.name)}</div>
                <div class="num" data-label="Open">${row.open}</div>
                <div class="num ${row.overdue > 0 ? 'overdue' : ''}" data-label="Overdue">${row.overdue}</div>
                <div class="num" data-label="Due today">${row.due_today}</div>
                <div class="num" data-label="Completed (7d)">${row.completed_this_week}</div>
            </div>`).join(''));
    });
})();
</script></body></html>
