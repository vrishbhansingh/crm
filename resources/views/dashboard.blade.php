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

        .stat-card .value { font-size: 26px; font-weight: 700; color: var(--text-dark); line-height: 1.15; }
        .stat-card .label { font-size: 13px; color: var(--text-muted); margin-top: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; }

        .icon-blue { background: #eff6ff; color: #2563eb; }
        .icon-orange { background: #fff7ed; color: #ea580c; }
        .icon-red { background: #fef2f2; color: #dc2626; }
        .icon-green { background: #ecfdf5; color: #16a34a; }
        .icon-purple { background: #f5f3ff; color: #7c3aed; }
        .icon-teal { background: #f0fdfa; color: #0d9488; }
        .icon-pink { background: #fdf2f8; color: #db2777; }
        .icon-indigo { background: #eef2ff; color: #4338ca; }

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
        .dash-card h5 a { margin-left: auto; font-size: 12.5px; font-weight: 600; color: var(--primary); text-decoration: none; }
        .dash-card h5 a:hover { text-decoration: underline; }

        .dash-table th { font-size: 12px; text-transform: uppercase; color: var(--text-muted); border-top: none; }
        .dash-table td { font-size: 14px; vertical-align: middle; }

        .chart-box { position: relative; height: 260px; }
        .chart-box canvas { max-height: 260px; }

        .followup-list { list-style: none; margin: 0; padding: 0; }
        .followup-item {
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; padding: 11px 0; border-bottom: 1px solid #f1f5f9;
            text-decoration: none; color: inherit;
        }
        .followup-item:last-child { border-bottom: none; }
        .followup-item:hover { background: #fafbff; }
        .followup-main { display: flex; align-items: center; gap: 10px; min-width: 0; }
        .followup-badge {
            flex-shrink: 0; width: 30px; height: 30px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center; font-size: 12.5px;
        }
        .followup-title { font-size: 13.5px; font-weight: 600; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .followup-sub { font-size: 12px; color: var(--text-muted); }
        .followup-when { font-size: 12px; font-weight: 600; white-space: nowrap; flex-shrink: 0; }
        .followup-when.overdue { color: #dc2626; }
        .followup-empty { text-align: center; color: var(--text-muted); padding: 24px 0; font-size: 13.5px; }
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

                <div class="row" id="chartsRow" style="display:none">
                    <div class="col-lg-6">
                        <div class="dash-card">
                            <h5><i class="fa fa-line-chart"></i> Leads — last 14 days</h5>
                            <div class="chart-box"><canvas id="leadsTrendChart"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="dash-card">
                            <h5><i class="fa fa-pie-chart"></i> Leads by Status</h5>
                            <div class="chart-box"><canvas id="leadsStatusChart"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="dash-card">
                            <h5><i class="fa fa-bar-chart"></i> Deals by Stage</h5>
                            <div class="chart-box"><canvas id="dealsStageChart"></canvas></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="dash-card">
                            <h5><i class="fa fa-bell-o"></i> Upcoming Follow-ups &amp; Reminders @can('calendar.view')<a href="{{ route('calendar.index') }}">Open calendar</a>@endcan</h5>
                            <ul class="followup-list" id="followUpList"></ul>
                        </div>
                    </div>
                    <div class="col-lg-6" id="closingSoonWrap" style="display:none">
                        <div class="dash-card">
                            <h5><i class="fa fa-flag-checkered"></i> Deals Closing Soon</h5>
                            <ul class="followup-list" id="closingSoonList"></ul>
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
    <script src="{{ asset('vendors/chart.js/Chart.min.js') }}"></script>

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
            { key: 'openDeals', label: 'Open Deals', icon: 'fa-handshake-o', color: 'icon-indigo' },
            { key: 'pipelineValue', label: 'Pipeline Value', icon: 'fa-inr', color: 'icon-green', money: true },
            { key: 'totalCompanies', label: 'Companies', icon: 'fa-building-o', color: 'icon-orange' },
            { key: 'activeCampaigns', label: 'Active Campaigns', icon: 'fa-paper-plane-o', color: 'icon-pink' },
            { key: 'totalTemplates', label: 'Email Templates', icon: 'fa-file-text-o', color: 'icon-blue' },
            { key: 'totalUsers', label: 'Team Members', icon: 'fa-users', color: 'icon-teal' },
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

        let charts = {};
        function draw(id, config) {
            if (charts[id]) charts[id].destroy();
            const ctx = document.getElementById(id);
            if (!ctx) return;
            charts[id] = new Chart(ctx, config);
        }

        function renderCharts(data) {
            $('#chartsRow').show();

            draw('leadsTrendChart', {
                type: 'line',
                data: {
                    labels: data.leadsTrend.labels,
                    datasets: [{
                        label: 'Leads created',
                        data: data.leadsTrend.data,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,0.08)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 2,
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });

            const statusColors = ['#2563eb', '#7c3aed', '#0d9488', '#ea580c', '#db2777', '#16a34a', '#dc2626', '#64748b'];
            draw('leadsStatusChart', {
                type: 'doughnut',
                data: {
                    labels: data.leadsByStatus.labels,
                    datasets: [{ data: data.leadsByStatus.data, backgroundColor: statusColors }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } } }
            });

            draw('dealsStageChart', {
                type: 'bar',
                data: {
                    labels: data.dealsByStage.labels,
                    datasets: [{ label: 'Deals', data: data.dealsByStage.data, backgroundColor: '#4338ca', borderRadius: 6 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        }

        const followTypeMeta = {
            lead: { icon: 'fa-bullseye', color: 'icon-blue', label: 'Lead follow-up' },
            deal: { icon: 'fa-handshake-o', color: 'icon-indigo', label: 'Deal reminder' },
        };

        function renderClosingSoon(deals) {
            if (!deals || !deals.length) return;
            $('#closingSoonWrap').show();
            let html = '';
            deals.forEach(d => {
                html += `
                    <a class="followup-item" href="${d.url}">
                        <div class="followup-main">
                            <div class="followup-badge icon-green"><i class="fa fa-inr"></i></div>
                            <div>
                                <div class="followup-title">${d.name}</div>
                                <div class="followup-sub">${fmt(d.amount, true)}</div>
                            </div>
                        </div>
                        <div class="followup-when ${d.overdue ? 'overdue' : ''}">${d.expected_close_date ?? ''}</div>
                    </a>`;
            });
            $('#closingSoonList').html(html);
        }

        function renderFollowUps(items) {
            if (!items || !items.length) {
                $('#followUpList').html('<div class="followup-empty">Nothing due in the next few days.</div>');
                return;
            }
            let html = '';
            items.forEach(item => {
                const meta = followTypeMeta[item.type] || followTypeMeta.lead;
                html += `
                    <a class="followup-item" href="${item.url}">
                        <div class="followup-main">
                            <div class="followup-badge ${meta.color}"><i class="fa ${meta.icon}"></i></div>
                            <div>
                                <div class="followup-title">${item.title}</div>
                                <div class="followup-sub">${meta.label}</div>
                            </div>
                        </div>
                        <div class="followup-when ${item.overdue ? 'overdue' : ''}">${item.when ?? ''}</div>
                    </a>`;
            });
            $('#followUpList').html(html);
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

                renderFollowUps(response.followUps);

                if (response.scope === 'team' && response.charts) {
                    renderCharts(response.charts);
                    renderClosingSoon(response.closingSoon);
                }
            });
        }

        $(document).ready(function() {
            loadDashboardData();
        });
    </script>

</body>

</html>
