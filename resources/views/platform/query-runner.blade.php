@extends('layouts.platform')
@section('title','Query Runner')
@section('heading','Query Runner')
@section('content')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">
<style>
    .qr-shell { display: grid; grid-template-columns: 220px 1fr 280px; gap: 16px; align-items: start; }
    @media (max-width: 1100px) { .qr-shell { grid-template-columns: 1fr; } }

    .qr-panel { background: #fff; border-radius: 13px; box-shadow: 0 6px 20px rgba(15,23,42,.05); padding: 16px; }
    .qr-panel h6 { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; margin-bottom: 10px; }

    .table-link { display: block; padding: 6px 8px; border-radius: 7px; font-size: 12.5px; color: #334155 !important; text-decoration: none !important; font-weight: 500; }
    .table-link:hover { background: #f1f5f9; }

    .CodeMirror { height: 220px; border-radius: 10px; font-size: 13.5px; }

    .qr-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
    .kbd { background: rgba(255,255,255,.15); border-radius: 4px; padding: 1px 6px; font-size: 10.5px; margin-left: 6px; }

    .history-item { display: block; width: 100%; text-align: left; border: none; background: transparent; padding: 8px; border-radius: 8px; cursor: pointer; }
    .history-item:hover { background: #f8fafc; }
    .history-item code { display: block; font-size: 11px; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .history-item .meta { font-size: 10.5px; color: #94a3b8; margin-top: 2px; }
    .history-item .meta .fail { color: #dc2626; font-weight: 700; }

    #resultArea table { font-size: 12.5px; }
    #resultArea td, #resultArea th { white-space: nowrap; max-width: 320px; overflow: hidden; text-overflow: ellipsis; }
</style>
@endpush

<div class="qr-shell">

    <div class="qr-panel">
        <h6>Tables</h6>
        <div id="tablesList" class="text-muted" style="font-size:12px;">Loading…</div>
    </div>

    <div>
        <div class="qr-panel mb-3">
            <div class="qr-toolbar">
                <select id="connectionSelect" class="form-control" style="max-width:320px;">
                    <option value="master">Master Database</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}">{{ $tenant->name }} (#{{ $tenant->id }})</option>
                    @endforeach
                </select>
                <button id="runBtn" class="btn btn-primary" type="button"><i class="fa-solid fa-play"></i> Run <span class="kbd">Ctrl+Enter</span></button>
            </div>
            <textarea id="sqlEditor">SELECT 1;</textarea>
        </div>

        <div class="qr-panel">
            <h6 class="mb-3">Result</h6>
            <div id="resultArea" class="text-muted" style="font-size:13px;">Run a query to see results here.</div>
        </div>
    </div>

    <div class="qr-panel">
        <h6>Recent queries</h6>
        <div>
            @forelse($history as $log)
                <button type="button" class="history-item"
                    data-sql="{{ $log->metadata['sql'] ?? '' }}"
                    data-connection="{{ $log->tenant_id ?? 'master' }}">
                    <code>{{ $log->metadata['sql'] ?? '' }}</code>
                    <div class="meta">
                        {{ $log->metadata['connection'] ?? '' }} · {{ $log->created_at->diffForHumans() }}
                        @if(!($log->metadata['success'] ?? true))<span class="fail">· failed</span>@endif
                    </div>
                </button>
            @empty
                <div class="text-muted" style="font-size:12.5px;">Nothing run yet.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/sql/sql.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cm = CodeMirror.fromTextArea(document.getElementById('sqlEditor'), {
        mode: 'text/x-sql',
        theme: 'dracula',
        lineNumbers: true,
        indentWithTabs: true,
        smartIndent: true,
        matchBrackets: true,
        extraKeys: {
            'Ctrl-Enter': function () { runQuery(false); },
            'Cmd-Enter': function () { runQuery(false); },
        },
    });

    const connectionSelect = document.getElementById('connectionSelect');
    const runBtn = document.getElementById('runBtn');
    const resultArea = document.getElementById('resultArea');
    const tablesList = document.getElementById('tablesList');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let lastResult = null;

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function loadTables() {
        tablesList.innerHTML = 'Loading…';
        fetch("{{ route('superadmin.query_runner.tables') }}?connection=" + encodeURIComponent(connectionSelect.value))
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    tablesList.innerHTML = '<div style="color:#dc2626;">' + escapeHtml(data.error) + '</div>';
                    return;
                }
                if (!data.tables.length) {
                    tablesList.innerHTML = 'No tables.';
                    return;
                }
                tablesList.innerHTML = '';
                data.tables.forEach(function (table) {
                    const a = document.createElement('a');
                    a.href = '#';
                    a.className = 'table-link';
                    a.textContent = table;
                    a.addEventListener('click', function (e) {
                        e.preventDefault();
                        cm.setValue('SELECT * FROM `' + table + '` LIMIT 100;');
                        cm.focus();
                    });
                    tablesList.appendChild(a);
                });
            })
            .catch(() => { tablesList.innerHTML = 'Could not load tables.'; });
    }

    function runQuery(confirmed) {
        const sql = cm.getValue().trim();
        if (!sql) return;

        runBtn.disabled = true;
        runBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Running…';

        fetch("{{ route('superadmin.query_runner.run') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ connection: connectionSelect.value, sql: sql, confirm: confirmed }),
        })
            .then(r => r.json())
            .then(data => {
                runBtn.disabled = false;
                runBtn.innerHTML = '<i class="fa-solid fa-play"></i> Run <span class="kbd">Ctrl+Enter</span>';

                if (data.needs_confirmation) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'This changes data',
                        text: "This isn't a read-only query. Run it anyway?",
                        showCancelButton: true,
                        confirmButtonText: 'Run it',
                        confirmButtonColor: '#dc2626',
                    }).then(res => { if (res.isConfirmed) runQuery(true); });
                    return;
                }

                renderResult(data);
            })
            .catch(err => {
                runBtn.disabled = false;
                runBtn.innerHTML = '<i class="fa-solid fa-play"></i> Run <span class="kbd">Ctrl+Enter</span>';
                renderResult({ success: false, error: String(err) });
            });
    }

    function renderResult(data) {
        if (!data.success) {
            resultArea.innerHTML = '<div class="alert alert-danger mb-0">' + escapeHtml(data.error || 'Query failed.') + '</div>';
            lastResult = null;
            return;
        }

        if (data.type === 'write') {
            resultArea.innerHTML = '<div class="alert alert-success mb-0">' + data.affected + ' row(s) affected · ' + data.duration_ms + 'ms</div>';
            lastResult = null;
            return;
        }

        lastResult = data;

        if (!data.columns.length) {
            resultArea.innerHTML = '<div class="alert alert-secondary mb-0">Query ran — 0 rows · ' + data.duration_ms + 'ms</div>';
            return;
        }

        let html = '<div class="d-flex justify-content-between align-items-center mb-2">';
        html += '<span class="text-muted" style="font-size:12px;">' + data.total + ' row(s)' + (data.truncated ? ' — showing first ' + data.rows.length : '') + ' · ' + data.duration_ms + 'ms</span>';
        html += '<button class="btn btn-sm btn-outline-secondary" id="csvBtn" type="button"><i class="fa-solid fa-download"></i> CSV</button></div>';
        html += '<div class="table-responsive" style="max-height:420px;"><table class="table table-sm"><thead><tr>';
        data.columns.forEach(c => { html += '<th>' + escapeHtml(c) + '</th>'; });
        html += '</tr></thead><tbody>';
        data.rows.forEach(row => {
            html += '<tr>' + row.map(v => '<td>' + (v === null ? '<span class="text-muted">NULL</span>' : escapeHtml(String(v))) + '</td>').join('') + '</tr>';
        });
        html += '</tbody></table></div>';
        resultArea.innerHTML = html;
        document.getElementById('csvBtn').addEventListener('click', exportCsv);
    }

    function csvCell(value) {
        return '"' + String(value).replace(/"/g, '""') + '"';
    }

    function exportCsv() {
        if (!lastResult) return;
        let csv = lastResult.columns.map(csvCell).join(',') + '\n';
        lastResult.rows.forEach(row => {
            csv += row.map(v => csvCell(v === null ? '' : v)).join(',') + '\n';
        });
        const blob = new Blob([csv], { type: 'text/csv' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'query-result.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
    }

    document.querySelectorAll('.history-item').forEach(function (el) {
        el.addEventListener('click', function () {
            cm.setValue(el.dataset.sql || '');
            connectionSelect.value = el.dataset.connection || 'master';
            loadTables();
            cm.focus();
        });
    });

    connectionSelect.addEventListener('change', loadTables);
    runBtn.addEventListener('click', function () { runQuery(false); });

    loadTables();
    cm.focus();
});
</script>
@endpush
