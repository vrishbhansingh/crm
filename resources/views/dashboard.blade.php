<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard | CRM</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        :root {
            --primary: #2563eb;
            --border: #e5e7eb;
            --text-dark: #111827;
            --text-muted: #6b7280;
        }

        .crm-page-header {
            background: #fff;
            padding: 18px 22px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            border-left: 4px solid var(--primary);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .crm-page-header h4 {
            font-weight: 700;
            font-size: 19px;
            color: var(--text-dark);
            margin: 0;
        }

        .crm-subtitle {
            color: var(--text-muted);
            font-size: 13px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
            padding: 18px;
        }

        .stat-card .value {
            font-size: 26px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .stat-card .label {
            font-size: 12.5px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .stat-card .icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #eef2ff;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .card-box {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
            padding: 20px;
        }

        .card-box h5 {
            font-weight: 700;
            font-size: 15px;
            color: var(--text-dark);
            margin-bottom: 14px;
        }

        .checkin-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .checkin-status {
            font-size: 13px;
            color: var(--text-muted);
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
                    <div>
                        <h4>Welcome back, {{ Auth::guard('web')->user()->name }}</h4>
                        <small class="crm-subtitle">{{ Auth::guard('web')->user()->getRoleNames()->first() }} &middot; {{ now()->format('d M Y') }}</small>
                    </div>
                </div>

                <div class="stat-grid" id="statGrid"></div>

                <div class="row">
                    <div class="col-md-8" id="teamAttendanceWrap" style="display:none;">
                        <div class="card-box">
                            <h5><i class="fa fa-clock-o"></i> Today's Attendance</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                        </tr>
                                    </thead>
                                    <tbody id="attendanceBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4" id="checkInWrap" style="display:none;">
                        <div class="card-box">
                            <h5><i class="fa fa-check-square-o"></i> My Attendance</h5>
                            <div class="checkin-box">
                                <span class="checkin-status" id="checkinStatus">Checking…</span>
                                <button class="btn btn-primary btn-sm" id="checkInBtn">
                                    <i class="fa fa-sign-in"></i> Check In
                                </button>
                            </div>
                        </div>
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
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const teamCards = [
            { key: 'totalLead', label: 'Total Leads', icon: 'fa-bullseye' },
            { key: 'newLeadToday', label: 'New Today', icon: 'fa-star' },
            { key: 'hotLead', label: 'Hot Leads', icon: 'fa-fire' },
            { key: 'callLead', label: 'Cold Call Leads', icon: 'fa-phone' },
            { key: 'webLead', label: 'Website Leads', icon: 'fa-globe' },
            { key: 'facebook', label: 'Facebook/LinkedIn', icon: 'fa-thumbs-up' },
        ];

        const ownCards = [
            { key: 'my_leads', label: 'My Leads', icon: 'fa-bullseye' },
            { key: 'my_hot_leads', label: 'My Hot Leads', icon: 'fa-fire' },
            { key: 'today_followups', label: "Today's Follow-ups", icon: 'fa-phone' },
            { key: 'overdue_followups', label: 'Overdue Follow-ups', icon: 'fa-exclamation-triangle' },
            { key: 'my_orders', label: 'My Orders', icon: 'fa-shopping-cart' },
            { key: 'active_orders', label: 'Active Orders', icon: 'fa-refresh' },
            { key: 'payment_collected', label: 'Payment Collected', icon: 'fa-inr' },
            { key: 'pending_payment', label: 'Pending Payment', icon: 'fa-clock-o' },
        ];

        function loadDashboardData() {
            $.get("{{ route('dashboard.data') }}", function(response) {
                const cards = response.scope === 'team' ? teamCards : ownCards;
                let html = '';
                cards.forEach(c => {
                    html += `
                        <div class="stat-card">
                            <div class="icon"><i class="fa ${c.icon}"></i></div>
                            <div class="value">${response.data[c.key] ?? 0}</div>
                            <div class="label">${c.label}</div>
                        </div>`;
                });
                $('#statGrid').html(html);

                if (response.scope === 'team') {
                    $('#teamAttendanceWrap').show();
                    loadTeamAttendance();
                } else {
                    $('#checkInWrap').show();
                    loadCheckInStatus();
                }
            });
        }

        function loadTeamAttendance() {
            $.get("{{ route('dashboard.attendance.team') }}", function(response) {
                let rows = '';
                response.data.forEach(r => {
                    rows += `<tr><td>${r.name}</td><td>${r.check_in ?? '-'}</td><td>${r.check_out ?? 'Still in'}</td></tr>`;
                });
                $('#attendanceBody').html(rows || '<tr><td colspan="3" class="text-muted">No attendance yet today.</td></tr>');
            });
        }

        function loadCheckInStatus() {
            $.get("{{ route('dashboard.attendance.status') }}", function(response) {
                if (response.attendanceMarked) {
                    $('#checkinStatus').text('Checked in today');
                    $('#checkInBtn').prop('disabled', true).text('Checked In');
                } else {
                    $('#checkinStatus').text('Not checked in yet');
                }
            });
        }

        $(document).on('click', '#checkInBtn', function() {
            $.post("{{ route('dashboard.attendance.checkin') }}", {}, function(response) {
                toastr.success(response.message);
                loadCheckInStatus();
            });
        });

        $(document).ready(function() {
            loadDashboardData();
        });
    </script>

</body>

</html>
