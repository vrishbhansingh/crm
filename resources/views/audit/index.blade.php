<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Audit Log</title>
<link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}"><link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}"><link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}"><link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    /* Same modernization pattern as the rest of this pass. */
    .crm-page-header{background:#fff;padding:26px 28px;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.06);margin-bottom:24px}
    .crm-page-header h3{margin:0 0 6px;font-weight:700;font-size:22px;color:#111827}.crm-page-header p{margin:0;color:#6b7280;font-size:14px}
    .audit-card{border:0;border-radius:16px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.audit-event{font-size:12px;font-weight:700;text-transform:uppercase}.change-json{white-space:pre-wrap;word-break:break-word;font-size:12px;max-height:180px;overflow:auto;background:#f8fafc;padding:10px;border-radius:8px}.filter-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(145px,1fr));gap:10px}
</style></head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">
<div class="crm-page-header"><h3>Audit Log</h3><p>Immutable history of important CRM data changes.</p></div>
<div class="card audit-card mb-3"><div class="card-body filter-grid"><select id="event" class="form-control"><option value="">All events</option><option value="created">Created</option><option value="updated">Updated</option><option value="deleted">Deleted</option></select><select id="type" class="form-control"><option value="">All record types</option>@foreach(['Lead','Deal','Order','Company','Contact','Task','PaymentDetails','Pipeline','ProjectInfo','User'] as $type)<option>{{ $type }}</option>@endforeach</select><select id="actor" class="form-control"><option value="">All users</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select><input id="from" type="date" class="form-control"><input id="to" type="date" class="form-control"></div></div>
<div class="card audit-card"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>When</th><th>User</th><th>Event</th><th>Record</th><th>Changes</th></tr></thead><tbody id="auditRows"><tr><td colspan="5" class="text-center p-4 text-muted">Loading…</td></tr></tbody></table></div><div class="p-3" id="auditPagination"></div></div>
</div>@include('include.footer')</div></div></div><script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script><script>
(() => {
    const esc = v => $('<div>').text(v ?? '').html();
    let currentPage = 1;

    function renderChanges(a) {
        // A create/delete event's "diff" is every field going from/to nothing —
        // not a meaningful field-by-field change list, so show the plain
        // one-line summary instead of a wall of "X: value → —" rows.
        if (a.event === 'created' || a.event === 'deleted') {
            return `<span class="text-muted">${a.event === 'created' ? 'Record created' : 'Record deleted'}</span>`;
        }
        if (a.changes && a.changes.length) {
            const rows = a.changes.map(c => `<div><b>${esc(c.field)}</b>: ${esc(c.from)} → ${esc(c.to)}</div>`).join('');
            return `<details><summary>${a.changes.length} field(s) changed</summary><div class="change-json">${rows}</div></details>`;
        }
        return '<span class="text-muted">No tracked field changes</span>';
    }

    function load() {
        const p = new URLSearchParams({ event: $('#event').val(), type: $('#type').val(), actor_id: $('#actor').val(), date_from: $('#from').val(), date_to: $('#to').val(), page: currentPage });
        $.get(`{{ route('audit.data') }}?${p}`, r => {
            const html = r.data.map(a => `<tr><td>${esc(new Date(a.created_at).toLocaleString())}</td><td>${esc(a.actor?.name || 'System')}</td><td><span class="audit-event text-${a.event === 'deleted' ? 'danger' : (a.event === 'created' ? 'success' : 'primary')}">${esc(a.event)}</span></td><td>${esc(a.auditable_type)} #${a.auditable_id}</td><td>${renderChanges(a)}</td></tr>`).join('');
            $('#auditRows').html(html || '<tr><td colspan="5" class="text-center p-4 text-muted">No audit events found.</td></tr>');
            renderCrmPagination('#auditPagination', r.meta, page => { currentPage = page; load(); });
        });
    }
    $('.filter-grid :input').on('change', function () { currentPage = 1; load(); });
    load();
})();
</script></body></html>
