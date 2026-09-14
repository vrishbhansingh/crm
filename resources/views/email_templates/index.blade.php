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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">

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

        .row-actions { position: relative; display: inline-block; }
        .row-actions-btn {
            width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent;
            color: #6b7280; display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 16px;
        }
        .row-actions-btn:hover { background: #f1f3f9; color: #1f2937; }
        .row-actions-menu {
            position: absolute; right: 0; top: 100%; margin-top: 4px; min-width: 150px;
            background: #fff; border-radius: 12px; box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            padding: 6px; z-index: 50; display: none; text-align: left;
        }
        .row-actions-menu.is-open { display: block; }
        .row-actions-menu a, .row-actions-menu button {
            display: flex; align-items: center; gap: 10px; width: 100%; padding: 9px 12px; border-radius: 8px;
            font-size: 13px; color: #374151; text-decoration: none; border: none; background: transparent;
            text-align: left; cursor: pointer;
        }
        .row-actions-menu a:hover, .row-actions-menu button:hover { background: #f3f4f6; }
        .row-actions-menu i { width: 16px; text-align: center; color: #6b7280; }
        .row-actions-menu .text-danger { color: #dc2626; }
        .row-actions-menu .text-danger i { color: #dc2626; }
        .row-actions-menu .text-danger:hover { background: #fef2f2; }

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
        [data-theme="dark"] .table td { color: var(--text-dark); border-color: #2a2e40; }
        [data-theme="dark"] .row-actions-btn { color: #9aa1b5; }
        [data-theme="dark"] .row-actions-btn:hover { background: #232637; color: #eef0f6; }
        [data-theme="dark"] .row-actions-menu { background: #1e2233; box-shadow: 0 16px 36px rgba(0, 0, 0, 0.4); }
        [data-theme="dark"] .row-actions-menu a, [data-theme="dark"] .row-actions-menu button { color: #e2e8f5; }
        [data-theme="dark"] .row-actions-menu i { color: #93a4fd; }
        [data-theme="dark"] .row-actions-menu a:hover, [data-theme="dark"] .row-actions-menu button:hover { background: rgba(255, 255, 255, 0.08); color: #fff; }
        [data-theme="dark"] .row-actions-menu .text-danger { color: #fca5a5; }
        [data-theme="dark"] .row-actions-menu .text-danger i { color: #fca5a5; }
        [data-theme="dark"] .row-actions-menu .text-danger:hover { background: rgba(239, 68, 68, 0.18); }
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
                    <a href="{{ route('templates.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> New Template
                    </a>
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>

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
                                <div class="row-actions">
                                    <button type="button" class="row-actions-btn" aria-label="Actions"><i class="fa fa-ellipsis-v"></i></button>
                                    <div class="row-actions-menu">
                                        @can('templates.edit')
                                        <a href="{{ url('email-templates') }}/${t.id}/edit"><i class="fa fa-pencil"></i> Edit</a>
                                        @endcan
                                        @can('templates.delete')
                                        <button class="deleteTemplateBtn text-danger" data-id="${t.id}"><i class="fa fa-trash"></i> Delete</button>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>`;
                });
                $('#templateTable').html(rows || '<tr><td colspan="5" class="text-center text-muted py-4">No templates yet — create the first one.</td></tr>');
            });
        }

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

        $(document).on('click', '.row-actions-btn', function(e) {
            e.stopPropagation();
            const menu = $(this).siblings('.row-actions-menu');
            const opening = !menu.hasClass('is-open');
            $('.row-actions-menu').removeClass('is-open');
            if (opening) {
                const rect = this.getBoundingClientRect();
                menu.css({ position: 'fixed', top: rect.bottom + 4, left: 'auto', right: window.innerWidth - rect.right }).addClass('is-open');
            }
        });
        $(document).on('click', '.row-actions-menu', function(e) { e.stopPropagation(); });
        $(document).on('click', function() { $('.row-actions-menu').removeClass('is-open'); });

        $(document).ready(function() {
            loadTemplates();
        });
    </script>

</body>

</html>
