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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

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
                                <a class="btn btn-sm btn-outline-primary" href="{{ url('email-templates') }}/${t.id}/edit">
                                    <i class="fa fa-pencil"></i>
                                </a>
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
