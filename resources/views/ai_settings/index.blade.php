<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Assistant Settings | CRM</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">

    <style>
        .aiset-head { background: var(--crm-surface, #fff); border: 1px solid var(--crm-border, #e5e7eb); border-radius: 14px; padding: 18px 20px; margin-bottom: 16px; display: flex; flex-wrap: wrap; gap: 14px; align-items: center; justify-content: space-between; }
        .aiset-head h4 { margin: 0 0 4px; font-weight: 700; font-size: 18px; color: var(--crm-ink, #171a17); }
        .aiset-head p { margin: 0; font-size: 13px; color: var(--crm-ink-muted, #585f57); max-width: 640px; }
        .aiset-note { background: var(--crm-surface-2, #f3f4f6); border: 1px solid var(--crm-border, #e5e7eb); border-radius: 12px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px; color: var(--crm-ink, #171a17); line-height: 1.55; }
        .aiset-note strong { display: block; margin-bottom: 2px; }
        .aiset-card { background: var(--crm-surface, #fff); border: 1px solid var(--crm-border, #e5e7eb); border-radius: 14px; padding: 16px 18px; margin-bottom: 12px; display: flex; flex-wrap: wrap; gap: 14px; align-items: center; }
        .aiset-card.off { opacity: .6; }
        .aiset-rank { width: 30px; height: 30px; border-radius: 50%; background: var(--crm-accent, #1f6f63); color: #fff; font-weight: 700; font-size: 13px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .aiset-main { flex: 1; min-width: 220px; }
        .aiset-name { font-weight: 700; font-size: 15px; color: var(--crm-ink, #171a17); display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .aiset-meta { font-size: 12.5px; color: var(--crm-ink-muted, #585f57); margin-top: 3px; }
        .aiset-err { font-size: 12.5px; color: #b42318; margin-top: 4px; }
        .aiset-pill { font-size: 11.5px; font-weight: 600; padding: 2px 9px; border-radius: 999px; }
        .aiset-pill.ok { background: #dcfce7; color: #166534; }
        .aiset-pill.failing { background: #fee2e2; color: #991b1b; }
        .aiset-pill.untested { background: #e5e7eb; color: #374151; }
        .aiset-pill.off { background: #e5e7eb; color: #6b7280; }
        .aiset-actions { display: flex; gap: 6px; flex-wrap: wrap; }
        .aiset-actions .btn { min-width: 38px; }
        .aiset-empty { text-align: center; padding: 38px 16px; color: var(--crm-ink-muted, #585f57); background: var(--crm-surface, #fff); border: 1px dashed var(--crm-border, #d1d5db); border-radius: 14px; }
        [data-theme="dark"] .aiset-pill.ok { background: rgba(34,197,94,.18); color: #86efac; }
        [data-theme="dark"] .aiset-pill.failing { background: rgba(239,68,68,.18); color: #fca5a5; }
        [data-theme="dark"] .aiset-pill.untested, [data-theme="dark"] .aiset-pill.off { background: #2a2e40; color: #c3c8d6; }
        [data-theme="dark"] .aiset-err { color: #fca5a5; }
    </style>
</head>

<body>
    <div class="container-scroller">
        @include('include.header')

        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="aiset-head">
                    <div>
                        <h4>&#10024; AI Assistant</h4>
                        <p>Add your company's own AI API keys. The chat assistant, lead summaries, draft replies and the WhatsApp auto-reply all use them. Keys are stored encrypted and are never shown again.</p>
                    </div>
                    <button type="button" class="btn btn-primary" id="addBtn"><i class="fa fa-plus"></i> Add configuration</button>
                </div>

                @if (! $enabled)
                    <div class="aiset-note" style="border-color:#f59e0b;"><strong>The assistant is switched off on this server</strong>(AI_ASSISTANT_ENABLED=false). Saved keys will not be used until it is turned on.</div>
                @endif

                <div class="aiset-note">
                    <strong>How automatic switching works</strong>
                    Configurations are used from #1 downwards. If a key expires, runs out of credit or hits its rate limit, the assistant moves to the next one by itself, marks the failed key, and emails the alert address you set for it.
                    A failed key is retried after 15 minutes. Use the arrows to change the order.
                    @if ($serverDefault)
                        <br>This server also has a default key, used only by companies that have saved no configuration of their own.
                    @endif
                </div>

                <div id="list"></div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="cfgModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="cfgForm" autocomplete="off">
                    <input type="hidden" id="cfgId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cfgTitle">Add configuration</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" id="cfgName" class="form-control" maxlength="100" placeholder="e.g. Main key, Backup key" required>
                        </div>
                        <div class="form-group">
                            <label>Anthropic API key</label>
                            <input type="password" id="cfgKey" class="form-control" autocomplete="new-password" placeholder="sk-ant-...">
                            <small class="text-muted" id="cfgKeyHint">Create one at console.anthropic.com. It is encrypted when saved.</small>
                        </div>
                        <div class="form-group">
                            <label>Alert email <span class="text-muted">(optional)</span></label>
                            <input type="email" id="cfgEmail" class="form-control" maxlength="190" placeholder="who should be told if this key fails">
                        </div>
                        <div class="form-group">
                            <label>Model <span class="text-muted">(optional)</span></label>
                            <input type="text" id="cfgModel" class="form-control" maxlength="60" placeholder="Leave empty for the default">
                        </div>
                        <div class="form-check">
                            <label style="display:flex;align-items:center;gap:8px;margin:0;cursor:pointer;"><input type="checkbox" id="cfgActive" checked style="position:static;opacity:1;appearance:auto;-webkit-appearance:checkbox;width:18px;height:18px;margin:0;"> Active (used by the assistant)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="cfgSave">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        const base = `{{ url('settings/ai') }}`;
        let rows = [];
        const esc = v => $('<div>').text(v ?? '').html();
        const label = { ok: 'Working', failing: 'Failing', untested: 'Not tested yet' };

        function render() {
            if (!rows.length) {
                $('#list').html('<div class="aiset-empty"><div style="font-size:30px;">&#10024;</div><strong>No configuration yet</strong><p class="mb-2">Add your first API key to switch the AI assistant on for your company.</p></div>');
                return;
            }
            $('#list').html(rows.map((c, i) => `
                <div class="aiset-card ${c.is_active ? '' : 'off'}" data-id="${c.id}">
                    <div class="aiset-rank" title="Priority">${i + 1}</div>
                    <div class="aiset-main">
                        <div class="aiset-name">${esc(c.name)}
                            <span class="aiset-pill ${c.is_active ? c.status : 'off'}">${c.is_active ? label[c.status] : 'Turned off'}</span>
                        </div>
                        <div class="aiset-meta">Key ${esc(c.key_hint || '')}${c.model ? ' &middot; ' + esc(c.model) : ''}${c.notify_email ? ' &middot; alerts to ' + esc(c.notify_email) : ''}</div>
                        <div class="aiset-meta">${c.last_used_at ? 'Last used ' + esc(c.last_used_at) : 'Not used yet'}${c.last_tested_at ? ' &middot; tested ' + esc(c.last_tested_at) : ''}</div>
                        ${c.status === 'failing' && c.last_error ? `<div class="aiset-err"><i class="fa fa-triangle-exclamation"></i> ${esc(c.last_error)}</div>` : ''}
                    </div>
                    <div class="aiset-actions">
                        <button class="btn btn-outline-secondary btn-sm act-up" title="Move up" ${i === 0 ? 'disabled' : ''}><i class="fa fa-arrow-up"></i></button>
                        <button class="btn btn-outline-secondary btn-sm act-down" title="Move down" ${i === rows.length - 1 ? 'disabled' : ''}><i class="fa fa-arrow-down"></i></button>
                        <button class="btn btn-success btn-sm act-test"><i class="fa fa-plug"></i> Test</button>
                        <button class="btn btn-outline-secondary btn-sm act-edit" title="Edit"><i class="fa fa-pen"></i></button>
                        <button class="btn btn-outline-danger btn-sm act-del" title="Delete"><i class="fa fa-trash"></i></button>
                    </div>
                </div>`).join(''));
        }

        function load() { return $.get(base + '/data', res => { rows = res.data; render(); }); }
        const find = el => rows.find(r => r.id === $(el).closest('.aiset-card').data('id'));
        const fail = x => toastr.error(x.responseJSON?.message || Object.values(x.responseJSON?.errors || {}).flat().join(' ') || 'Something went wrong.');

        $('#addBtn').on('click', function () {
            $('#cfgForm')[0].reset(); $('#cfgId').val(''); $('#cfgActive').prop('checked', true);
            $('#cfgTitle').text('Add configuration'); $('#cfgKey').prop('required', true);
            $('#cfgKeyHint').text('Create one at console.anthropic.com. It is encrypted when saved.');
            $('#cfgModal').modal('show');
        });

        $(document).on('click', '.act-edit', function () {
            const c = find(this);
            $('#cfgId').val(c.id); $('#cfgName').val(c.name); $('#cfgKey').val('').prop('required', false);
            $('#cfgEmail').val(c.notify_email || ''); $('#cfgModel').val(c.model || ''); $('#cfgActive').prop('checked', c.is_active);
            $('#cfgKeyHint').text('A key ending ' + (c.key_hint || '') + ' is saved. Leave blank to keep it, or paste a new one to replace it.');
            $('#cfgTitle').text('Edit configuration'); $('#cfgModal').modal('show');
        });

        $('#cfgForm').on('submit', function (e) {
            e.preventDefault();
            const id = $('#cfgId').val();
            const data = { name: $('#cfgName').val(), api_key: $('#cfgKey').val(), notify_email: $('#cfgEmail').val(), model: $('#cfgModel').val(), is_active: $('#cfgActive').is(':checked') ? 1 : 0 };
            $('#cfgSave').prop('disabled', true);
            $.ajax({ url: id ? `${base}/${id}` : base, method: id ? 'PUT' : 'POST', data })
                .done(res => { toastr.success(res.message); $('#cfgModal').modal('hide'); load(); })
                .fail(fail).always(() => $('#cfgSave').prop('disabled', false));
        });

        $(document).on('click', '.act-test', function () {
            const c = find(this), $b = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Testing');
            $.post(`${base}/${c.id}/test`)
                .done(res => res.status ? toastr.success(res.message) : toastr.error(res.message))
                .fail(x => toastr.error(x.responseJSON?.message || 'Test failed.'))
                .always(() => load());
        });

        $(document).on('click', '.act-up, .act-down', function () {
            const c = find(this);
            $.post(`${base}/${c.id}/move`, { direction: $(this).hasClass('act-up') ? 'up' : 'down' }).done(load).fail(fail);
        });

        $(document).on('click', '.act-del', function () {
            const c = find(this);
            if (!confirm(`Remove "${c.name}"? This cannot be undone.`)) return;
            $.ajax({ url: `${base}/${c.id}`, method: 'DELETE' }).done(res => { toastr.success(res.message); load(); }).fail(fail);
        });

        load();
    </script>
</body>
</html>
