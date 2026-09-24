@extends('layouts.platform')
@section('title','Query runner')
@section('heading','Query Runner')
@push('styles')
<style>
    .qr-mode-toggle{display:flex;border:1px solid var(--border);border-radius:9px;overflow:hidden;}
    .qr-mode-toggle button{flex:1;border:none;background:#fff;color:#475569;font-weight:700;font-size:13px;padding:10px 14px;cursor:pointer;}
    .qr-mode-toggle button.active{background:var(--accent);color:#fff;}
    .qr-samples{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px;}
    .qr-samples button{border:1px solid var(--border);background:#fff;border-radius:8px;font-size:12.5px;font-weight:600;color:#334155;padding:7px 12px;cursor:pointer;}
    .qr-samples button:hover{border-color:var(--accent);color:var(--accent);}
    #qrSql{width:100%;min-height:160px;border-radius:10px;border:1px solid var(--border);font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:13px;padding:14px;resize:vertical;}
    #qrSql:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(67,56,202,.12);}
    .qr-run-row{display:flex;align-items:center;gap:22px;flex-wrap:wrap;margin-top:14px;}
    .qr-check{display:flex;align-items:center;gap:7px;font-size:13px;font-weight:700;color:#b91c1c;cursor:pointer;}
    .qr-check input{width:15px;height:15px;}
    .qr-rowlimit{display:flex;align-items:center;gap:8px;font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.03em;}
    .qr-rowlimit input{width:90px;}
    .qr-results-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:10px;}
    .qr-error{background:#fee2e2;color:#991b1b;border-radius:10px;padding:12px 16px;font-size:13.5px;margin-top:16px;}
    .qr-empty{padding:40px 20px;text-align:center;color:#94a3b8;font-size:13.5px;}
    #qrTenantSearch{margin-bottom:8px;}
</style>
@endpush
@section('content')

<div class="card"><div class="card-body">
    <div class="row">
        <div class="col-md-6">
            <label class="text-muted small mb-2 d-block">Mode</label>
            <div class="qr-mode-toggle">
                <button type="button" id="qrModeSingle" class="active">Single DB</button>
                <button type="button" id="qrModeAll">All Tenants</button>
            </div>
        </div>
        <div class="col-md-6" id="qrTargetWrap">
            <label class="text-muted small mb-2 d-block">Target Database</label>
            <input type="text" class="form-control" id="qrTenantSearch" placeholder="Search tenant…">
            <select class="form-control" id="qrTenantSelect">
                <option value="">Master ({{ $masterDatabase }})</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" data-name="{{ strtolower($tenant->name) }}">{{ $tenant->name }} ({{ $tenant->database_name ?? 'shared' }})</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="qr-samples">
        <span class="text-muted small" style="align-self:center;font-weight:700;">SAMPLES:</span>
        <button type="button" class="qrSample" data-sql="SELECT provision_status, COUNT(*) AS total FROM tenants GROUP BY provision_status ORDER BY total DESC;">Tenants by provision status</button>
        <button type="button" class="qrSample" data-sql="SELECT created_at, event, tenant_id, actor_id FROM platform_audit_logs ORDER BY created_at DESC LIMIT 20;">Recent platform audit events</button>
        <button type="button" class="qrSample" data-sql="SELECT id, name, database_name, created_at FROM tenants WHERE provision_status = 'ready' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY created_at DESC;">Ready tenants (last 7 days)</button>
    </div>
</div></div>

<div class="card"><div class="card-body">
    <label class="text-muted small mb-2 d-block">SQL</label>
    <textarea id="qrSql" spellcheck="false" placeholder="SELECT * FROM leads LIMIT 10;"></textarea>
    <div class="qr-run-row">
        <label class="qr-check"><input type="checkbox" id="qrAllowWrite"> Allow write (INSERT / UPDATE / DELETE)</label>
        <label class="qr-check"><input type="checkbox" id="qrAllowDdl"> Allow DDL (ALTER / CREATE / DROP / TRUNCATE)</label>
        <div class="qr-rowlimit">Row limit <input type="number" class="form-control form-control-sm" id="qrRowLimit" value="1000" min="1" max="10000"></div>
        <button class="btn btn-primary" id="qrRunBtn" style="margin-left:auto;"><i class="fa-solid fa-play mr-1"></i> Run query</button>
    </div>
    <div id="qrError" class="qr-error d-none"></div>
</div></div>

<div class="card" id="qrResultsCard" style="display:none;"><div class="card-body">
    <div class="qr-results-head">
        <strong id="qrResultsSummary"></strong>
        <button class="btn btn-outline-secondary btn-sm" id="qrExportBtn"><i class="fa-solid fa-download mr-1"></i> Export CSV</button>
    </div>
    <div class="table-responsive"><table class="table"><thead id="qrResultsHead"></thead><tbody id="qrResultsBody"></tbody></table></div>
</div></div>

@endsection
@push('scripts')
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const esc = value => $('<div>').text(value ?? '').html();
    let mode = 'single';
    let lastResult = null;

    $('#qrModeSingle').on('click', function(){ mode = 'single'; $(this).addClass('active'); $('#qrModeAll').removeClass('active'); $('#qrTargetWrap').show(); });
    $('#qrModeAll').on('click', function(){ mode = 'all'; $(this).addClass('active'); $('#qrModeSingle').removeClass('active'); $('#qrTargetWrap').hide(); });

    $('#qrTenantSearch').on('input', function(){
        const term = $(this).val().toLowerCase();
        $('#qrTenantSelect option').each(function(){
            const name = $(this).data('name');
            $(this).toggle(!name || name.includes(term));
        });
    });

    $('.qrSample').on('click', function(){ $('#qrSql').val($(this).data('sql')).focus(); });

    function targetLabel() {
        if (mode === 'all') return 'ALL tenant databases';
        const opt = $('#qrTenantSelect option:selected');
        return opt.val() ? opt.text() : 'the Master database';
    }

    $('#qrRunBtn').on('click', function(){
        const sql = $('#qrSql').val().trim();
        if (!sql) { $('#qrSql').focus(); return; }
        const allowWrite = $('#qrAllowWrite').is(':checked');
        const allowDdl = $('#qrAllowDdl').is(':checked');

        if (allowWrite || allowDdl) {
            const kind = allowDdl ? 'DDL' : 'WRITE';
            if (!confirm(`This will run a ${kind} statement against ${targetLabel()}. Continue?`)) return;
        }

        $('#qrError').addClass('d-none');
        const btn = $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-1"></i> Running…');

        $.ajax({
            url: `{{ route('superadmin.query_runner.run') }}`, method: 'POST', headers: {'X-CSRF-TOKEN': csrf},
            data: {
                sql, mode, tenant_id: $('#qrTenantSelect').val() || null,
                allow_write: allowWrite ? 1 : 0, allow_ddl: allowDdl ? 1 : 0,
                row_limit: $('#qrRowLimit').val() || 1000,
            },
        })
            .done(response => { lastResult = response; renderResults(response); })
            .fail(xhr => { $('#qrError').removeClass('d-none').text(xhr.responseJSON?.message || 'Query failed.'); $('#qrResultsCard').hide(); })
            .always(() => btn.prop('disabled', false).html('<i class="fa-solid fa-play mr-1"></i> Run query'));
    });

    function renderResults(response) {
        $('#qrResultsCard').show();
        const truncated = response.truncated ? ' (truncated)' : '';
        $('#qrResultsSummary').text(`${response.row_count} row(s)${truncated} — ${response.duration_ms}ms`);

        if (!response.columns.length) {
            $('#qrResultsHead').html('');
            $('#qrResultsBody').html('<tr><td class="qr-empty">No rows returned.</td></tr>');
            return;
        }

        $('#qrResultsHead').html('<tr>' + response.columns.map(c => `<th>${esc(c)}</th>`).join('') + '</tr>');
        $('#qrResultsBody').html(response.rows.map(row => '<tr>' + response.columns.map(c => `<td>${esc(row[c] ?? '')}</td>`).join('') + '</tr>').join(''));
    }

    $('#qrExportBtn').on('click', function(){
        if (!lastResult || !lastResult.columns.length) return;
        const lines = [lastResult.columns.join(',')];
        lastResult.rows.forEach(row => {
            lines.push(lastResult.columns.map(c => `"${String(row[c] ?? '').replace(/"/g, '""')}"`).join(','));
        });
        const blob = new Blob([lines.join('\n')], {type: 'text/csv'});
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'query-runner-results.csv';
        link.click();
    });
})();
</script>
@endpush
