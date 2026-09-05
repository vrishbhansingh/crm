<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Email Templates | CRM Admin</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        :root { --primary: #2563eb; --border: #e5e7eb; --text-dark: #111827; --text-muted: #6b7280; }

        .crm-page-header {
            background: #fff; padding: 18px 22px; border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06); border-left: 4px solid var(--primary);
            display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 20px;
        }
        .crm-header-left { display: flex; align-items: center; gap: 14px; }
        .crm-header-icon {
            width: 42px; height: 42px; border-radius: 10px;
            background: linear-gradient(135deg, #0d6efd, #00c6ff); color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .crm-page-header h4 { font-weight: 700; font-size: 18px; color: var(--text-dark); margin: 0; }
        .crm-subtitle { color: var(--text-muted); font-size: 13px; }

        .crm-card {
            background: #fff; border-radius: 14px; box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border); padding: 4px;
        }

        .variable-group-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin: 10px 0 4px; }
        .preview-pane { border: 1px dashed var(--border); border-radius: 10px; padding: 14px; background: #f9fafb; max-height: 320px; overflow: auto; }

        /* -- Template editor modal -- */
        #templateModal .modal-dialog { max-width: 900px; }
        #templateModal .modal-header { padding: 16px 24px; border-bottom: 1px solid var(--border); }
        #templateModal .modal-title { font-size: 16px; font-weight: 700; }
        #templateModal .modal-body { padding: 20px 24px; }
        #templateModal .modal-footer { padding: 12px 24px; }
        #templateModal .field-label {
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
            color: var(--text-muted); margin-bottom: 6px; display: block;
        }
        #templateModal .form-row-tight { margin-bottom: 16px; }
        #templateModal .form-control { border-radius: 8px; border-color: var(--border); font-size: 13.5px; }

        .var-hint-bar {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            background: #eff6ff; border: 1px solid #dbeafe; border-radius: 10px;
            padding: 8px 12px; margin-bottom: 10px;
        }
        .var-hint-bar .var-hint-text { font-size: 12px; color: #1e40af; flex: 1 1 auto; min-width: 180px; }
        .var-hint-bar select#variableSelect { font-size: 12.5px; padding: 4px 8px; height: 30px; border-radius: 6px; max-width: 220px; }
        .var-hint-bar select#previewAudienceType { font-size: 12.5px; padding: 4px 8px; height: 30px; border-radius: 6px; width: auto; }
        .var-hint-bar .btn-insert-var { font-size: 12px; padding: 4px 12px; border-radius: 6px; }

        .tox-tinymce { border-radius: 8px !important; border-color: var(--border) !important; }
        .tox .tox-toolbar__primary { background: #f8fafc !important; }

        #previewResult { margin-top: 14px; }
    </style>
</head>

<body>

    <div class="container-scroller">
        @include('include.header')

        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="crm-page-header">
                    <div class="crm-header-left">
                        <div class="crm-header-icon"><i class="fa fa-file-text-o"></i></div>
                        <div>
                            <h4>Email Templates</h4>
                            <small class="crm-subtitle">Reusable emails with variables that auto-fill from the lead, deal, company, or contact they're sent to.</small>
                        </div>
                    </div>
                    @can('templates.create')
                    <button class="btn btn-primary btn-sm" id="addTemplateBtn" data-toggle="modal" data-target="#templateModal">
                        <i class="fa fa-plus"></i> New Template
                    </button>
                    @endcan
                </div>

                <div class="crm-card">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Subject</th>
                                    <th>Used by</th>
                                    <th>Updated</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="templateTable">
                                <tr><td colspan="5" class="text-center text-muted py-4">Loading…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                @include('include.footer')
            </div>
        </div>
    </div>

    <!-- Add/Edit Template Modal -->
    <div class="modal fade" id="templateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="templateModalTitle">New Template</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="templateForm">
                    <div class="modal-body">
                        <input type="hidden" id="template_id">

                        <div class="row form-row-tight">
                            <div class="col-md-6">
                                <label class="field-label">Template Name</label>
                                <input type="text" id="template_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="field-label">Subject</label>
                                <input type="text" id="template_subject" class="form-control" placeholder="e.g. Following up, @{{lead.name}}" required>
                            </div>
                        </div>

                        <label class="field-label">Template Content</label>
                        <div class="var-hint-bar">
                            <span class="var-hint-text"><i class="fa fa-info-circle"></i> Pick a variable, then <strong>Insert</strong> to drop it into the subject or content as a tag.</span>
                            <select id="previewAudienceType" class="form-control">
                                <option value="leads">For: Lead</option>
                                <option value="contacts">For: Contact</option>
                                <option value="companies">For: Company</option>
                            </select>
                            <select id="variableSelect" class="form-control"></select>
                            <button type="button" class="btn btn-primary btn-insert-var" id="insertVariableBtn">
                                <i class="fa fa-plus"></i> Insert
                            </button>
                        </div>

                        <textarea id="templateBody" class="form-control"></textarea>

                        <div class="form-row-tight mt-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="previewTemplateBtn">
                                <i class="fa fa-eye"></i> Preview with a real record
                            </button>
                        </div>

                        <div id="previewResult" style="display:none">
                            <div class="variable-group-label">Subject</div>
                            <div class="preview-pane mb-2" id="previewSubject"></div>
                            <div class="variable-group-label">Body</div>
                            <div class="preview-pane" id="previewBody"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('vendors/tinymce/tinymce.min.js') }}"></script>

    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });

        const esc = value => $('<div>').text(value ?? '').html();

        function loadTemplates() {
            $.get("{{ route('templates.data') }}", function(response) {
                let rows = '';
                response.data.forEach((t) => {
                    rows += `
                        <tr>
                            <td>${esc(t.name)}</td>
                            <td class="text-muted">${esc(t.subject)}</td>
                            <td>${t.campaigns_count} campaign(s)</td>
                            <td class="text-muted">${t.updated_at ? new Date(t.updated_at).toLocaleDateString() : '-'}</td>
                            <td class="text-right">
                                @can('templates.edit')
                                <button class="btn btn-sm btn-outline-primary editTemplateBtn"
                                    data-id="${t.id}" data-name="${encodeURIComponent(t.name)}" data-subject="${encodeURIComponent(t.subject)}" data-body="${encodeURIComponent(t.body)}">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                @endcan
                                @can('templates.delete')
                                <button class="btn btn-sm btn-outline-danger deleteTemplateBtn" data-id="${t.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                                @endcan
                            </td>
                        </tr>`;
                });
                $('#templateTable').html(rows || '<tr><td colspan="5" class="text-center text-muted py-4">No templates yet — create the first one.</td></tr>');
            });
        }

        // Built via fromCharCode rather than writing the double-brace
        // token markers directly in this file's source text — Blade scans
        // the raw file for that pattern regardless of JS string/template-
        // literal context, so writing them directly here would corrupt
        // the server-side compile.
        const BRACE_OPEN = String.fromCharCode(123, 123);
        const BRACE_CLOSE = String.fromCharCode(125, 125);

        // The picker doubles as the "insert" mechanism for both fields: a
        // dropdown (instead of a whole wall of pill buttons) keeps the
        // screen compact, and the same list drives the subject field (plain
        // text) and the body editor (a styled pill tag) depending on which
        // one was last focused.
        function loadVariablePicker() {
            $.get("{{ route('templates.variables') }}", { audience_type: $('#previewAudienceType').val() }, function(response) {
                let html = '';
                let currentGroup = '';
                response.tokens.forEach((token) => {
                    const group = token.split('.')[0];
                    if (group !== currentGroup) {
                        if (currentGroup) html += '</optgroup>';
                        currentGroup = group;
                        html += `<optgroup label="${esc(group.charAt(0).toUpperCase() + group.slice(1))}">`;
                    }
                    html += `<option value="${token}">${BRACE_OPEN}${token}${BRACE_CLOSE}</option>`;
                });
                if (currentGroup) html += '</optgroup>';
                $('#variableSelect').html(html);
            });
        }

        $(document).on('change', '#previewAudienceType', loadVariablePicker);

        // Tracks which field a variable should land in: TinyMCE reports its
        // own focus (the underlying textarea never gets native focus once
        // the editor replaces it), the subject input reports its own.
        // 'focusin' (not 'focus') — the latter doesn't bubble, so jQuery's
        // delegated binding on `document` never sees it fire.
        let lastFocusedField = 'body';
        $(document).on('focusin', '#template_subject', function() {
            lastFocusedField = 'subject';
        });

        function bodyEditor() {
            return tinymce.get('templateBody');
        }

        $(document).on('click', '#insertVariableBtn', function() {
            const token = $('#variableSelect').val();
            if (!token) return;
            const raw = BRACE_OPEN + token + BRACE_CLOSE;

            if (lastFocusedField === 'subject') {
                const field = document.getElementById('template_subject');
                const start = field.selectionStart ?? field.value.length;
                const end = field.selectionEnd ?? field.value.length;
                field.value = field.value.slice(0, start) + raw + field.value.slice(end);
                field.focus();
                field.selectionStart = field.selectionEnd = start + raw.length;
                return;
            }

            const editor = bodyEditor();
            if (!editor) return;
            editor.insertContent(`<span class="var-token" contenteditable="false">${raw}</span>&nbsp;`);
            editor.focus();
        });

        // TinyMCE only renders once its container is visible, so it's
        // (re)created on every modal open and torn down on close — rather
        // than initialized once on page load — to avoid a blank editor.
        let pendingBodyContent = '';

        function initBodyEditor() {
            if (bodyEditor()) return Promise.resolve();
            return tinymce.init({
                selector: '#templateBody',
                height: 300,
                menubar: false,
                branding: false,
                statusbar: false,
                toolbar_mode: 'wrap',
                plugins: 'lists link image code fullscreen textcolor colorpicker autolink charmap',
                toolbar: 'undo redo | styleselect | bold italic underline strikethrough | subscript superscript | forecolor backcolor | bullist numlist outdent indent | alignleft aligncenter alignright alignjustify | blockquote link image | removeformat | code fullscreen',
                content_style: "body{font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:14px;color:#1f2937;} .var-token{display:inline-block;background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe;border-radius:6px;padding:1px 8px;font-family:Consolas,monospace;font-size:12.5px;font-weight:600;margin:0 1px;}",
                setup: function(editor) {
                    editor.on('focus', function() { lastFocusedField = 'body'; });
                },
            }).then(function() {
                bodyEditor().setContent(pendingBodyContent || '');
            });
        }

        $('#templateModal').on('shown.bs.modal', function() {
            initBodyEditor();
        });

        $('#templateModal').on('hidden.bs.modal', function() {
            if (bodyEditor()) bodyEditor().remove();
        });

        $(document).on('click', '#addTemplateBtn', function() {
            $('#templateModalTitle').text('New Template');
            $('#templateForm')[0].reset();
            $('#template_id').val('');
            $('#previewResult').hide();
            pendingBodyContent = '';
            loadVariablePicker();
        });

        $(document).on('click', '.editTemplateBtn', function() {
            $('#templateModalTitle').text('Edit Template');
            $('#template_id').val($(this).data('id'));
            $('#template_name').val(decodeURIComponent($(this).attr('data-name') || ''));
            $('#template_subject').val(decodeURIComponent($(this).attr('data-subject') || ''));
            pendingBodyContent = decodeURIComponent($(this).attr('data-body') || '');
            $('#previewResult').hide();
            loadVariablePicker();
            $('#templateModal').modal('show');
        });

        $(document).on('click', '#previewTemplateBtn', function() {
            $.post("{{ route('templates.preview') }}", {
                subject: $('#template_subject').val(),
                body: bodyEditor() ? bodyEditor().getContent() : '',
                audience_type: $('#previewAudienceType').val(),
            }, function(response) {
                $('#previewSubject').text(response.subject);
                $('#previewBody').html(response.body);
                if (response.note) toastr.info(response.note);
                $('#previewResult').show();
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Preview failed');
            });
        });

        $(document).on('submit', '#templateForm', function(e) {
            e.preventDefault();
            const id = $('#template_id').val();
            const payload = {
                name: $('#template_name').val(),
                subject: $('#template_subject').val(),
                body: bodyEditor() ? bodyEditor().getContent() : '',
            };

            const url = id ? "{{ url('email-templates') }}/" + id : "{{ route('templates.store') }}";

            $.ajax({
                url: url,
                type: 'POST',
                data: id ? Object.assign(payload, { _method: 'PUT' }) : payload,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#templateModal').modal('hide');
                        loadTemplates();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });

        $(document).on('click', '.deleteTemplateBtn', function() {
            if (!confirm('Delete this template?')) return;
            const id = $(this).data('id');
            $.ajax({
                url: "{{ url('email-templates') }}/" + id,
                type: 'POST',
                data: { _method: 'DELETE' },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        loadTemplates();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });

        $(document).ready(function() {
            loadTemplates();
        });
    </script>

</body>

</html>
