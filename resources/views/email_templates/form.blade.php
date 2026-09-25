<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $template ? 'Edit Template' : 'New Template' }} | CRM Admin</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">

    <style>
        :root { --primary: #2563eb; --border: #e5e7eb; --text-dark: #111827; --text-muted: #6b7280; }

        .crm-page-header {
            background: #fff; padding: 18px 22px; border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06); border-left: 4px solid var(--primary);
            display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 20px; flex-wrap: wrap;
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
            border: 1px solid var(--border); padding: 24px 28px;
        }

        .field-label {
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
            color: var(--text-muted); margin-bottom: 6px; display: block;
        }
        .form-row-tight { margin-bottom: 20px; }
        .form-control { border-radius: 8px; border-color: var(--border); font-size: 13.5px; }

        .var-hint-bar {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            background: #eff6ff; border: 1px solid #dbeafe; border-radius: 10px;
            padding: 10px 14px; margin-bottom: 12px;
        }
        .var-hint-bar .var-hint-text { font-size: 12.5px; color: #1e40af; flex: 1 1 auto; min-width: 220px; }
        .var-hint-bar select#variableSelect { font-size: 13px; padding: 6px 10px; height: 34px; border-radius: 6px; max-width: 260px; }
        .var-hint-bar select#previewAudienceType { font-size: 13px; padding: 6px 10px; height: 34px; border-radius: 6px; width: auto; }
        .var-hint-bar .btn-insert-var { font-size: 13px; padding: 6px 16px; border-radius: 6px; }

        .variable-group-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin: 10px 0 4px; }
        .preview-pane { border: 1px dashed var(--border); border-radius: 10px; padding: 14px; background: #f9fafb; max-height: 320px; overflow: auto; }
        #previewResult { margin-top: 16px; }

        .tox-tinymce { border-radius: 8px !important; border-color: var(--border) !important; }
        .tox .tox-toolbar__primary { background: #f8fafc !important; }

        .form-actions {
            display: flex; justify-content: flex-end; gap: 10px;
            margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border);
        }

        [data-theme="dark"] {
            --border: #2a2e40;
            --text-dark: #eef0f6;
            --text-muted: #9aa1b5;
        }
        [data-theme="dark"] .crm-page-header,
        [data-theme="dark"] .crm-card {
            background: #1a1d2b;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }
        [data-theme="dark"] .var-hint-bar {
            background: #1e2233; border-color: #2a2e40;
        }
        [data-theme="dark"] .var-hint-bar .var-hint-text { color: #93a4fd; }
        [data-theme="dark"] .preview-pane { background: #232637; }
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
                            <h4>{{ $template ? 'Edit Template' : 'New Template' }}</h4>
                            <small class="crm-subtitle">Reusable emails with variables that auto-fill from the record they're sent to.</small>
                        </div>
                    </div>
                    <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Templates
                    </a>
                </div>

                <div class="crm-card">
                    <form id="templateForm">
                        <input type="hidden" id="template_id" value="{{ $template->id ?? '' }}">

                        <div class="row form-row-tight">
                            <div class="col-md-5">
                                <label class="field-label">Template Name</label>
                                <input type="text" id="template_name" class="form-control" value="{{ $template->name ?? '' }}" required>
                            </div>
                            <div class="col-md-7">
                                <label class="field-label">Subject</label>
                                <input type="text" id="template_subject" class="form-control" value="{{ $template->subject ?? '' }}" placeholder="e.g. Following up, @{{lead.name}}" required>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:10px;">
                            <label class="field-label mb-0">Template Content</label>
                            <div>
                                <label class="btn btn-outline-secondary btn-sm mb-0" for="htmlUploadInput" style="cursor:pointer;">
                                    <i class="fa fa-upload"></i> Upload HTML file
                                </label>
                                <input type="file" id="htmlUploadInput" accept=".html,.htm" style="display:none">
                            </div>
                        </div>
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
                        <p class="text-muted mt-1" style="font-size:12px;">Uploading a file replaces the content above. Use the image icon in the toolbar to insert an image — you can either upload a file or paste an external image URL; both work.</p>

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

                        <div class="form-row-tight mt-4">
                            <label class="field-label">File attachments <span class="text-muted" style="text-transform:none;font-weight:400;">— sent with every email that uses this template (e.g. a PDF brochure or price list)</span></label>

                            @if($template)
                                <div id="attachmentsList">
                                    @foreach($template->attachments as $attachment)
                                        <div class="attachment-row" data-id="{{ $attachment->id }}" style="display:flex;align-items:center;gap:10px;padding:8px 12px;border:1px solid var(--border);border-radius:8px;margin-bottom:6px;font-size:13px;">
                                            <i class="fa fa-paperclip text-muted"></i>
                                            <span style="flex:1;">{{ $attachment->original_name }}</span>
                                            <span class="text-muted">{{ number_format($attachment->size / 1024, 1) }} KB</span>
                                            <button type="button" class="btn btn-sm btn-outline-danger removeAttachmentBtn" data-id="{{ $attachment->id }}"><i class="fa fa-trash"></i></button>
                                        </div>
                                    @endforeach
                                </div>
                                <label class="btn btn-outline-primary btn-sm mt-2 mb-0" for="attachmentUploadInput" style="cursor:pointer;">
                                    <i class="fa fa-plus"></i> Add attachment
                                </label>
                                <input type="file" id="attachmentUploadInput" style="display:none">
                                <span class="text-muted" style="font-size:12px;margin-left:8px;">PDF, Word, Excel, CSV, or image — up to 10 MB.</span>
                            @else
                                <div class="text-muted" style="font-size:12.5px;">
                                    <i class="fa fa-info-circle"></i> Save this template first, then come back here to add attachments.
                                </div>
                            @endif
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('templates.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i id="saveSpinner" class="fa fa-spinner fa-spin" style="display:none"></i> Save Template
                            </button>
                        </div>
                    </form>
                </div>

                @include('include.footer')
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>
    <script src="{{ asset('vendors/tinymce/tinymce.min.js') }}"></script>

    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });

        const esc = value => $('<div>').text(value ?? '').html();

        // Built via fromCharCode rather than writing the double-brace token
        // markers directly in this file's source text — Blade scans the raw
        // file for that pattern regardless of JS string/template-literal
        // context, so writing them directly here would corrupt the
        // server-side compile.
        const BRACE_OPEN = String.fromCharCode(123, 123);
        const BRACE_CLOSE = String.fromCharCode(125, 125);

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

        const initialBody = @json($template->body ?? '');

        tinymce.init({
            selector: '#templateBody',
            height: 420,
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
            // Adds an "Upload" tab to the stock Insert Image dialog
            // alongside its existing "Source" (external URL) field — a
            // dropped-in/uploaded image is stored on this server and
            // inserted as a normal <img src="..."> pointing at it.
            automatic_uploads: true,
            images_upload_handler: function (blobInfo) {
                return new Promise(function (resolve, reject) {
                    const formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());
                    $.ajax({
                        url: "{{ route('templates.upload_image') }}",
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                    }).done(function (response) {
                        if (response.status) {
                            resolve(response.url);
                        } else {
                            reject(response.message || 'Image upload failed.');
                        }
                    }).fail(function (xhr) {
                        reject(xhr.responseJSON?.message || 'Image upload failed.');
                    });
                });
            },
        }).then(function() {
            bodyEditor().setContent(initialBody || '');
        });

        loadVariablePicker();

        // Uploaded files are often full HTML documents with a <style> block
        // in <head>. The editor only keeps body markup, and email clients
        // routinely strip <style> tags on send anyway — so before handing
        // the content to TinyMCE, bake every matching CSS rule into each
        // element's own inline style attribute. That survives both.
        function inlineCssFromUploadedHtml(rawHtml) {
            return new Promise(function (resolve) {
                const parsed = new DOMParser().parseFromString(rawHtml, 'text/html');
                const cssText = Array.from(parsed.querySelectorAll('style')).map(function (s) { return s.textContent; }).join('\n');
                const bodyHtml = parsed.body ? parsed.body.innerHTML : rawHtml;

                if (!cssText.trim()) {
                    resolve(bodyHtml);
                    return;
                }

                const iframe = document.createElement('iframe');
                iframe.style.cssText = 'position:absolute;width:0;height:0;border:0;visibility:hidden;';
                document.body.appendChild(iframe);

                const idoc = iframe.contentDocument;
                idoc.open();
                idoc.write('<!doctype html><html><head><style>' + cssText + '</style></head><body></body></html>');
                idoc.close();
                idoc.body.innerHTML = bodyHtml;

                const sheet = idoc.styleSheets[0];
                const rules = sheet ? Array.from(sheet.cssRules) : [];

                rules.forEach(function (rule) {
                    // rule.type === 1 is CSSRule.STYLE_RULE — checked by value
                    // (not instanceof CSSStyleRule) since these rules belong
                    // to the iframe's own realm, not this document's classes.
                    if (rule.type !== 1) return; // skip @media/@font-face/etc.
                    let matched;
                    try { matched = idoc.body.querySelectorAll(rule.selectorText); } catch (e) { return; }
                    matched.forEach(function (el) {
                        for (let i = 0; i < rule.style.length; i++) {
                            const prop = rule.style[i];
                            // An element's own inline style always wins in the
                            // real cascade too, so never clobber it here.
                            if (!el.style.getPropertyValue(prop)) {
                                el.style.setProperty(prop, rule.style.getPropertyValue(prop), rule.style.getPropertyPriority(prop));
                            }
                        }
                    });
                });

                const finalHtml = idoc.body.innerHTML;
                document.body.removeChild(iframe);
                resolve(finalHtml);
            });
        }

        // Upload HTML file — read it client-side, inline any stylesheet CSS,
        // and drop the result straight into the editor as its new content.
        $(document).on('change', '#htmlUploadInput', function() {
            const file = this.files[0];
            this.value = '';
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                inlineCssFromUploadedHtml(e.target.result).then(function (html) {
                    if (bodyEditor()) {
                        bodyEditor().setContent(html);
                        toastr.success('Loaded "' + file.name + '" into the editor with its CSS applied inline.');
                    }
                });
            };
            reader.onerror = function() { toastr.error('Could not read that file.'); };
            reader.readAsText(file);
        });

        // Attachments — only present once the template has an id (see the
        // template-exists guard in the markup above), so this input only
        // ever exists on the edit page.
        $(document).on('change', '#attachmentUploadInput', function() {
            const file = this.files[0];
            this.value = '';
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);
            const templateId = $('#template_id').val();

            $.ajax({
                url: "{{ url('email-templates') }}/" + templateId + "/attachments",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
            }).done(function(response) {
                if (!response.status) { toastr.error(response.message); return; }
                toastr.success(response.message);
                const row = $(
                    '<div class="attachment-row" data-id="' + response.data.id + '" style="display:flex;align-items:center;gap:10px;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:6px;font-size:13px;">' +
                        '<i class="fa fa-paperclip text-muted"></i>' +
                        '<span style="flex:1;">' + esc(response.data.original_name) + '</span>' +
                        '<span class="text-muted">' + (response.data.size / 1024).toFixed(1) + ' KB</span>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger removeAttachmentBtn" data-id="' + response.data.id + '"><i class="fa fa-trash"></i></button>' +
                    '</div>'
                );
                $('#attachmentsList').append(row);
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Attachment upload failed.');
            });
        });

        $(document).on('click', '.removeAttachmentBtn', function() {
            const btn = $(this);
            const id = btn.data('id');

            $.ajax({
                url: "{{ url('email-templates/attachments') }}/" + id,
                type: 'POST',
                data: { _method: 'DELETE' },
            }).done(function(response) {
                if (response.status) {
                    btn.closest('.attachment-row').remove();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Could not remove that attachment.');
            });
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
            $('#saveSpinner').show();

            $.ajax({
                url: url,
                type: 'POST',
                data: id ? Object.assign(payload, { _method: 'PUT' }) : payload,
                success: function(response) {
                    $('#saveSpinner').hide();
                    if (response.status) {
                        toastr.success(response.message);
                        setTimeout(() => { window.location.href = "{{ route('templates.index') }}"; }, 600);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    $('#saveSpinner').hide();
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                }
            });
        });
    </script>

</body>

</html>
