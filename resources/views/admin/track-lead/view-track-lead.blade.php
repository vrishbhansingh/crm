<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lead Follow Ups | CRM</title>

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        .crm-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #007bff, #00c6ff);
            padding: 22px 26px;
            border-radius: 14px;
            margin-bottom: 28px;
            color: #fff;
        }

        .crm-title {
            font-size: 22px;
            font-weight: 700;
        }

        .crm-subtitle {
            font-size: 13px;
            opacity: 0.9;
        }

        .action-btn {
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .action-btn.view {
            background: #e7f1ff;
            color: #0d6efd;
        }

        .action-btn.view:hover {
            background: #0d6efd;
            color: #fff;
        }

        .call-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .call-call_connected {
            background: #d4edda;
            color: #155724;
        }

        .call-not_reachable,
        .call-switched_off {
            background: #f8d7da;
            color: #721c24;
        }

        .call-callback {
            background: #fff3cd;
            color: #856404;
        }

        .response-badge {
            background: #eef2ff;
            color: #4b49ac;
            padding: 5px 10px;
            font-size: 11px;
            border-radius: 6px;
            text-transform: capitalize;
        }
    </style>
</head>

<body>

    <div class="container-scroller">
        @include('admin.include.header')

        <div class="container-fluid page-body-wrapper">
            @include('admin.include.sidebar')

            <div class="content-wrapper">

                <!-- HEADER -->
                <div class="crm-header">
                    <div>
                        <h2 class="crm-title">
                            <i class="fa fa-phone"></i> Lead Follow Ups
                        </h2>
                        <p class="crm-subtitle">
                            Latest follow-up activity for assigned leads
                        </p>
                    </div>

                    <div class="d-flex" style="gap:10px;">
                        <a href="{{ route('admin.track_lead') }}" class="btn btn-light btn-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                        <button class="btn btn-light btn-sm" id="refreshLeads">
                            <i class="fa fa-refresh"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Lead No</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Call Status</th>
                                        <th class="text-center">Response</th>
                                        <th class="text-center">Follow Up</th>
                                        <th class="text-center">Note</th>
                                    </tr>
                                </thead>

                                <tbody id="leadTableBody">
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            Loading follow-ups...
                                        </td>
                                    </tr>
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>

                @include('admin.include.footer')
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            loadTrackLead();

            $('#refreshLeads').on('click', function() {
                loadTrackLead();
            });
        });

        function loadTrackLead() {
            const urlParts = window.location.pathname.split('/');
            const leadId = urlParts[urlParts.length - 1];
            $.ajax({
                url: "{{route('admin.get_followups_detials')}}",
                type: "GET",
                dataType: "json",
                data: {
                    lead_id: leadId
                },

                beforeSend: function() {
                    $('#leadTableBody').html(`
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Loading follow-ups...
                    </td>
                </tr>
            `);
                },

                success: function(response) {

                    if (!response.status || response.data.length === 0) {
                        $('#leadTableBody').html(`
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            No follow-up data found
                        </td>
                    </tr>
                `);
                        return;
                    }

                    let html = '';

                    $.each(response.data, function(index, lead) {

                        html += `
                <tr>
                    <td class="text-center">${lead.sl_no}</td>

                    <td class="text-center"><strong>#${lead.lead_id}</strong></td>
                    <td class="text-center">${lead.user_name ?? '-'}</td>

                    <td class="text-center">
                        <span class="call-status call-${lead.call_status}">
                            ${lead.call_status.replaceAll('_', ' ')}
                        </span>
                    </td>

                    <td class="text-center">
                        <span class="response-badge">
                            ${lead.lead_response}
                        </span>
                    </td>

                    <td class="text-center">
                        <small>
                            ${lead.follow_up_date ?? '-'}<br>
                            ${lead.follow_up_time ?? ''}
                        </small>
                    </td>

                    <td class="text-center">
                        <small>${lead.call_note ?? '-'}</small>
                    </td>

                   
                </tr>
                `;
                    });

                    $('#leadTableBody').html(html);
                },

                error: function() {
                    toastr.error('Failed to load follow-ups');
                }
            });
        }
    </script>

</body>

</html>