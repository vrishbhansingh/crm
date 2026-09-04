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
            --surface: #f8fafc;
        }

        /* ---- Same modernization pass as Roles & Permissions: larger
           type, roomier cards, more breathing room. ---- */
        .dash-wrap { font-size: 15px; }

        .dash-header {
            background: #fff;
            padding: 26px 28px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .dash-greeting { font-size: 24px; font-weight: 700; color: var(--text-dark); margin: 0 0 6px; }
        .dash-greeting .emoji { margin-right: 6px; }
        .dash-subtitle { color: var(--text-muted); font-size: 14.5px; margin: 0; }
        .dash-date { font-size: 13.5px; color: var(--text-muted); background: var(--surface); border-radius: 999px; padding: 8px 16px; white-space: nowrap; }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            padding: 20px 22px;
        }

        .stat-card .icon {
            width: 42px; height: 42px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px; margin-bottom: 14px;
        }

        .stat-card .value { font-size: 28px; font-weight: 700; color: var(--text-dark); line-height: 1.15; }
        .stat-card .label { font-size: 13.5px; color: var(--text-muted); margin-top: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; }

        .icon-blue { background: #eff6ff; color: #2563eb; }
        .icon-orange { background: #fff7ed; color: #ea580c; }
        .icon-red { background: #fef2f2; color: #dc2626; }
        .icon-green { background: #ecfdf5; color: #16a34a; }
        .icon-purple { background: #f5f3ff; color: #7c3aed; }
        .icon-teal { background: #f0fdfa; color: #0d9488; }

        .dash-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            padding: 22px 24px;
            margin-bottom: 24px;
            height: 100%;
        }

        .dash-card h5 { font-weight: 700; font-size: 15.5px; color: var(--text-dark); margin: 0 0 18px; display: flex; align-items: center; gap: 8px; }
        .dash-card h5 i { color: var(--primary); }

        .dash-table th { font-size: 12px; text-transform: uppercase; color: var(--text-muted); border-top: none; }
        .dash-table td { font-size: 14px; vertical-align: middle; }

        /* Attendance donut rings — pure CSS conic-gradient, no chart lib */
        .donut-row { display: flex; justify-content: space-around; flex-wrap: wrap; gap: 20px; }
        .donut-item { text-align: center; }
        .donut {
            width: 92px; height: 92px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            position: relative; margin: 0 auto 10px;
        }
        .donut::before { content: ''; position: absolute; inset: 11px; background: #fff; border-radius: 50%; }
        .donut span { position: relative; font-weight: 700; font-size: 17px; color: var(--text-dark); }
        .donut-label { font-size: 13px; font-weight: 600; color: var(--text-dark); }
        .donut-sub { font-size: 12px; color: var(--text-muted); }

        .checkin-box { display: flex; align-items: center; justify-content: space-between; gap: 14px; }
        .checkin-status { font-size: 14px; color: var(--text-muted); }
    </style>
</head>

<body>

    <div class="container-scroller">

        @include('include.header')

        <div class="container-fluid page-body-wrapper">

            @include('include.sidebar')

            <div class="content-wrapper dash-wrap">

                <div class="dash-header">
                    <div>
                        <h1 class="dash-greeting"><span class="emoji" id="greetingEmoji">👋</span><span id="greetingText">Welcome back</span>, {{ Auth::guard('web')->user()->name }}</h1>
                        <p class="dash-subtitle">{{ Auth::guard('web')->user()->getRoleNames()->first() }} &middot; Here's your overview</p>
                    </div>
                    <div class="dash-date">{{ now()->format('l, d F Y') }}</div>
                </div>

                <div class="stat-grid" id="statGrid"></div>

                <div class="row">
                    <div class="col-lg-8" id="teamAttendanceWrap" style="display:none;">
                        <div class="dash-card">
                            <h5><i class="fa fa-clock-o"></i> Today's Attendance</h5>
                            <div class="table-responsive">
                                <table class="table dash-table">
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

                    <div class="col-lg-4" id="attendanceOverviewWrap" style="display:none;">
                        <div class="dash-card">
                            <h5><i class="fa fa-pie-chart"></i> Attendance Overview</h5>
                            <div class="donut-row" id="attendanceDonuts"></div>
                        </div>
                    </div>

                    <div class="col-lg-4" id="checkInWrap" style="display:none;">
                        <div class="dash-card">
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

        (function greetByTime() {
            const hour = new Date().getHours();
            let text = 'Good evening', emoji = '🌙';
            if (hour < 12) { text = 'Good morning'; emoji = '☀️'; }
            else if (hour < 17) { text = 'Good afternoon'; emoji = '🌤️'; }
            document.getElementById('greetingText').textContent = text;
            document.getElementById('greetingEmoji').textContent = emoji;
        })();

        const teamCards = [
            { key: 'totalLead', label: 'Total Leads', icon: 'fa-bullseye', color: 'icon-blue' },
            { key: 'newLeadToday', label: 'New Today', icon: 'fa-star', color: 'icon-purple' },
            { key: 'hotLead', label: 'Hot Leads', icon: 'fa-fire', color: 'icon-red' },
            { key: 'tasksDueToday', label: 'Tasks Due Today', icon: 'fa-check-square-o', color: 'icon-teal' },
            { key: 'webLead', label: 'Website Leads', icon: 'fa-globe', color: 'icon-green' },
            { key: 'facebook', label: 'Facebook/LinkedIn', icon: 'fa-thumbs-up', color: 'icon-orange' },
        ];

        const ownCards = [
            { key: 'my_leads', label: 'My Leads', icon: 'fa-bullseye', color: 'icon-blue' },
            { key: 'my_hot_leads', label: 'My Hot Leads', icon: 'fa-fire', color: 'icon-red' },
            { key: 'today_followups', label: "Today's Follow-ups", icon: 'fa-phone', color: 'icon-purple' },
            { key: 'overdue_followups', label: 'Overdue Follow-ups', icon: 'fa-exclamation-triangle', color: 'icon-orange' },
            { key: 'my_orders', label: 'My Orders', icon: 'fa-shopping-cart', color: 'icon-teal' },
            { key: 'active_orders', label: 'Active Orders', icon: 'fa-refresh', color: 'icon-green' },
            { key: 'payment_collected', label: 'Payment Collected', icon: 'fa-inr', color: 'icon-blue', money: true },
            { key: 'pending_payment', label: 'Pending Payment', icon: 'fa-clock-o', color: 'icon-red', money: true },
        ];

        function fmt(value, money) {
            if (!money) return value ?? 0;
            return '₹' + Number(value ?? 0).toLocaleString('en-IN');
        }

        function loadDashboardData() {
            $.get("{{ route('dashboard.data') }}", function(response) {
                const cards = response.scope === 'team' ? teamCards : ownCards;
                let html = '';
                cards.forEach(c => {
                    html += `
                        <div class="stat-card">
                            <div class="icon ${c.color}"><i class="fa ${c.icon}"></i></div>
                            <div class="value">${fmt(response.data[c.key], c.money)}</div>
                            <div class="label">${c.label}</div>
                        </div>`;
                });
                $('#statGrid').html(html);

                if (response.scope === 'team') {
                    $('#teamAttendanceWrap, #attendanceOverviewWrap').show();
                    loadTeamAttendance();
                } else {
                    $('#checkInWrap').show();
                    loadCheckInStatus();
                }
            });
        }

        function donut(pct, color, value, label, sub) {
            return `
                <div class="donut-item">
                    <div class="donut" style="background:conic-gradient(${color} ${pct}%, #e5e7eb 0)"><span>${value}</span></div>
                    <div class="donut-label">${label}</div>
                    <div class="donut-sub">${sub}</div>
                </div>`;
        }

        function loadTeamAttendance() {
            $.get("{{ route('dashboard.attendance.team') }}", function(response) {
                let rows = '';
                response.data.forEach(r => {
                    rows += `<tr><td>${r.name}</td><td>${r.check_in ?? '-'}</td><td>${r.check_out ?? '<span class="text-success">Still in</span>'}</td></tr>`;
                });
                $('#attendanceBody').html(rows || '<tr><td colspan="3" class="text-muted">No attendance marked yet today.</td></tr>');

                const s = response.summary;
                const total = s.total || 1;
                let html = '';
                html += donut(Math.round(s.present / total * 100), '#16a34a', s.present, 'Present', 'checked in');
                html += donut(Math.round(s.checked_out / total * 100), '#2563eb', s.checked_out, 'Checked out', 'done for today');
                html += donut(Math.round(s.not_marked / total * 100), '#e11d48', s.not_marked, 'Not marked', 'no record yet');
                $('#attendanceDonuts').html(html);
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
