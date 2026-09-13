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
            padding: 20px 22px;
            border-radius: 13px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .dash-greeting { font-size: 24px; font-weight: 700; color: var(--text-dark); margin: 0 0 6px; }
        .dash-greeting .emoji { margin-right: 6px; }
        .dash-subtitle { color: var(--text-muted); font-size: 14.5px; margin: 0; }

        .dash-header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .dash-date { font-size: 13.5px; color: var(--text-muted); background: var(--surface); border-radius: 999px; padding: 8px 16px; white-space: nowrap; }
        .dash-action-btn {
            font-size: 13.5px; font-weight: 600; border-radius: 10px; padding: 9px 16px;
            border: 1px solid var(--border); background: #fff; color: var(--text-dark);
            text-decoration: none; display: inline-flex; align-items: center; gap: 7px;
            transition: background .15s, border-color .15s;
        }
        .dash-action-btn:hover { background: var(--surface); text-decoration: none; }
        .dash-action-btn.is-primary { background: var(--primary); border-color: var(--primary); color: #fff; }
        .dash-action-btn.is-primary:hover { background: #1d4ed8; }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 18px;
            margin-bottom: 18px;
        }

        .stat-card {
            background: #fff;
            border-radius: 13px;
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
            border-radius: 13px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            padding: 22px 24px;
            margin-bottom: 18px;
            height: 100%;
        }

        .dash-card h5 { font-weight: 700; font-size: 15.5px; color: var(--text-dark); margin: 0 0 18px; display: flex; align-items: center; gap: 8px; }
        .dash-card h5 i { color: var(--primary); }
        .dash-card h5 a { margin-left: auto; font-size: 12.5px; font-weight: 600; color: var(--primary); text-decoration: none; }
        .dash-card h5 a:hover { text-decoration: underline; }
        .dash-card .dash-card-sub { font-size: 12.5px; color: var(--text-muted); margin: -14px 0 16px; }

        .dash-table th { font-size: 12px; text-transform: uppercase; color: var(--text-muted); border-top: none; }
        .dash-table td { font-size: 14px; vertical-align: middle; }

        .chart-box { position: relative; height: 260px; }
        .chart-box canvas { max-height: 260px; }
        .chart-box.is-short { height: 190px; }
        .chart-box.is-short canvas { max-height: 190px; }

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

        /* Sales Pipeline */
        .pipeline-list { display: flex; flex-direction: column; gap: 12px; }
        .pipeline-row {
            display: flex; align-items: center; justify-content: space-between;
            gap: 14px; border-radius: 12px; padding: 13px 16px;
        }
        .pipeline-row .pipeline-name { font-weight: 700; font-size: 13.5px; }
        .pipeline-row .pipeline-count { opacity: .75; font-weight: 600; font-size: 12.5px; margin-left: 6px; }
        .pipeline-row .pipeline-value { font-weight: 700; font-size: 13.5px; }
        .pipeline-empty, .performer-empty, .leads-empty { text-align: center; color: var(--text-muted); padding: 30px 0; font-size: 13.5px; }

        /* Top Performers */
        .performer-list { display: flex; flex-direction: column; gap: 4px; }
        .performer-row { display: flex; align-items: center; gap: 12px; padding: 11px 0; border-bottom: 1px solid #f1f5f9; }
        .performer-row:last-child { border-bottom: none; }
        .performer-rank {
            width: 22px; text-align: center; font-size: 12px; font-weight: 700; color: var(--text-muted); flex-shrink: 0;
        }
        .performer-avatar {
            width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 13px; object-fit: cover;
        }
        .performer-info { min-width: 0; flex: 1; }
        .performer-name { font-size: 13.5px; font-weight: 700; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .performer-role { font-size: 11.5px; color: var(--text-muted); }
        .performer-stats { text-align: right; flex-shrink: 0; }
        .performer-won-value { font-size: 13.5px; font-weight: 700; color: #16a34a; }
        .performer-deals { font-size: 11.5px; color: var(--text-muted); }
        .performer-actions { display: flex; gap: 4px; flex-shrink: 0; }
        .performer-actions a {
            width: 28px; height: 28px; border-radius: 8px; background: var(--surface);
            display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 12px;
        }
        .performer-actions a:hover { background: #eff6ff; color: var(--primary); }

        /* Recent Leads */
        .lead-cell { display: flex; align-items: center; gap: 10px; }
        .lead-avatar {
            width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 12px;
        }
        .lead-name { font-size: 13.5px; font-weight: 700; color: var(--text-dark); }
        .lead-email { font-size: 12px; color: var(--text-muted); }
        .status-pill { display: inline-block; padding: 4px 11px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
        .lead-row-actions { display: flex; gap: 6px; }
        .lead-row-actions a {
            width: 30px; height: 30px; border-radius: 8px; background: var(--surface);
            display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 12.5px;
        }
        .lead-row-actions a:hover { background: #eff6ff; color: var(--primary); }

        /* Lead sources chips */
        .source-chip-list { display: flex; flex-direction: column; gap: 10px; }
        .source-chip-row { display: flex; align-items: center; gap: 10px; }
        .source-chip-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .source-chip-label { font-size: 13px; color: var(--text-dark); font-weight: 600; flex: 1; }
        .source-chip-total { font-size: 13px; font-weight: 700; color: var(--text-dark); }

        /* Closing-soon cards */
        .closing-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
        .closing-card { border: 1px solid var(--border); border-radius: 12px; padding: 14px 16px; text-decoration: none; color: inherit; display: block; }
        .closing-card:hover { border-color: var(--primary); background: #fafbff; }
        .closing-card .closing-name { font-size: 13.5px; font-weight: 700; color: var(--text-dark); margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .closing-card .closing-amount { font-size: 15px; font-weight: 700; color: #16a34a; margin-bottom: 8px; }
        .closing-card .closing-date { font-size: 11.5px; font-weight: 700; padding: 3px 9px; border-radius: 999px; display: inline-block; }
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
                    <div class="dash-header-actions">
                        @can('leads.create')<a href="{{ route('leads.create') }}" class="dash-action-btn is-primary"><i class="fa fa-plus"></i> New Lead</a>@endcan
                        @can('deals.create')<a href="{{ route('deals.create') }}" class="dash-action-btn"><i class="fa fa-handshake-o"></i> New Deal</a>@endcan
                        @can('reports.view')<a href="{{ route('reports.index') }}" class="dash-action-btn"><i class="fa fa-bar-chart"></i> View Reports</a>@endcan
                        <div class="dash-date">{{ now()->format('l, d F Y') }}</div>
                    </div>
                </div>

                <div class="stat-grid" id="statGrid"></div>

                <div class="row" id="pipelinePerformersRow" style="display:none">
                    <div class="col-lg-7" id="pipelineCol">
                        <div class="dash-card">
                            <h5><i class="fa fa-filter"></i> Sales Pipeline</h5>
                            <div class="pipeline-list" id="pipelineList"></div>
                        </div>
                    </div>
                    <div class="col-lg-5" id="performerCol" style="display:none">
                        <div class="dash-card">
                            <h5><i class="fa fa-trophy"></i> Top Performers</h5>
                            <div class="performer-list" id="performerList"></div>
                        </div>
                    </div>
                </div>

                <div class="row" id="recentLeadsRow" style="display:none">
                    <div class="col-lg-12">
                        <div class="dash-card">
                            <h5><i class="fa fa-bullseye"></i> Recent Leads @can('leads.view')<a href="{{ route('leads.index') }}">View all</a>@endcan</h5>
                            <div class="table-responsive">
                                <table class="table dash-table">
                                    <thead>
                                        <tr><th>Lead</th><th>Source</th><th>Status</th><th>Date</th><th class="text-right">Action</th></tr>
                                    </thead>
                                    <tbody id="recentLeadsBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" id="revenueSourcesRow" style="display:none">
                    <div class="col-lg-8">
                        <div class="dash-card">
                            <h5><i class="fa fa-inr"></i> Revenue Overview</h5>
                            <div class="chart-box"><canvas id="revenueChart"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="dash-card">
                            <h5><i class="fa fa-share-alt"></i> Lead Sources</h5>
                            <div class="source-chip-list" id="sourceChipList"></div>
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
                            <h5><i class="fa fa-clock-o"></i> Deals Closing Soon</h5>
                            <div class="closing-grid" id="closingSoonList"></div>
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

        const PALETTE = ['#2563eb', '#7c3aed', '#0d9488', '#ea580c', '#db2777', '#16a34a', '#4338ca', '#0891b2'];

        function paletteColor(seed) {
            let hash = 0;
            String(seed || '').split('').forEach(ch => { hash = (hash * 31 + ch.charCodeAt(0)) >>> 0; });
            return PALETTE[hash % PALETTE.length];
        }

        function initials(name) {
            const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
            if (!parts.length) return '?';
            return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
        }

        function stageColor(stage) {
            if (stage.color && /^#[0-9a-f]{3,8}$/i.test(stage.color)) return stage.color;
            const name = (stage.name || '').toLowerCase();
            if (name.includes('won')) return '#16a34a';
            if (name.includes('lost')) return '#dc2626';
            return paletteColor(stage.name);
        }

        const STATUS_COLORS = {
            'new': '#2563eb', 'hot': '#dc2626', 'warm': '#ea580c', 'cold': '#0891b2',
            'contacted': '#7c3aed', 'qualified': '#16a34a', 'closed': '#4338ca', 'lost': '#6b7280',
        };
        function statusColor(label) {
            return STATUS_COLORS[String(label || '').toLowerCase()] || paletteColor(label);
        }

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
        function esc(value) { return $('<div>').text(value ?? '').html(); }

        let charts = {};
        function draw(id, config) {
            if (charts[id]) charts[id].destroy();
            const ctx = document.getElementById(id);
            if (!ctx) return;
            charts[id] = new Chart(ctx, config);
        }

        function renderPipeline(stages) {
            if (!stages || !stages.length) {
                $('#pipelineList').html('<div class="pipeline-empty">No open deals in the pipeline yet.</div>');
                return;
            }
            let html = '';
            stages.forEach(stage => {
                const color = stageColor(stage);
                html += `
                    <div class="pipeline-row" style="background:${color}1a;">
                        <div>
                            <span class="pipeline-name" style="color:${color}">${esc(stage.name)}</span>
                            <span class="pipeline-count" style="color:${color}">${stage.count}</span>
                        </div>
                        <div class="pipeline-value" style="color:${color}">${fmt(stage.value, true)}</div>
                    </div>`;
            });
            $('#pipelineList').html(html);
        }

        function renderPerformers(performers) {
            if (!performers || !performers.length) {
                $('#performerList').html('<div class="performer-empty">No won deals yet — performance will show up here.</div>');
                return;
            }
            let html = '';
            performers.forEach((p, i) => {
                const avatar = p.avatar
                    ? `<img src="${p.avatar}" class="performer-avatar" alt="">`
                    : `<div class="performer-avatar" style="background:${paletteColor(p.name)}">${initials(p.name)}</div>`;
                html += `
                    <div class="performer-row">
                        <div class="performer-rank">#${i + 1}</div>
                        ${avatar}
                        <div class="performer-info">
                            <div class="performer-name">${esc(p.name)}</div>
                            <div class="performer-role">${esc(p.role || 'Team member')}</div>
                        </div>
                        <div class="performer-stats">
                            <div class="performer-won-value">${fmt(p.wonValue, true)}</div>
                            <div class="performer-deals">${p.won} won &middot; ${p.deals} deals</div>
                        </div>
                        <div class="performer-actions">
                            ${p.phone ? `<a href="tel:${esc(p.phone)}" title="Call"><i class="fa fa-phone"></i></a>` : ''}
                        </div>
                    </div>`;
            });
            $('#performerList').html(html);
        }

        function renderRecentLeads(leads) {
            if (!leads || !leads.length) {
                $('#recentLeadsBody').html('<tr><td colspan="5" class="leads-empty">No leads yet.</td></tr>');
                return;
            }
            let html = '';
            leads.forEach(lead => {
                const color = paletteColor(lead.name);
                const sc = statusColor(lead.status);
                html += `
                    <tr>
                        <td>
                            <div class="lead-cell">
                                <div class="lead-avatar" style="background:${color}">${initials(lead.name)}</div>
                                <div>
                                    <div class="lead-name">${esc(lead.name)}</div>
                                    <div class="lead-email">${esc(lead.email || lead.phone || '')}</div>
                                </div>
                            </div>
                        </td>
                        <td>${esc(lead.source)}</td>
                        <td><span class="status-pill" style="background:${sc}1a;color:${sc}">${esc(lead.status)}</span></td>
                        <td>${esc(lead.created_at)}</td>
                        <td class="text-right">
                            <div class="lead-row-actions justify-content-end">
                                ${lead.phone ? `<a href="tel:${esc(lead.phone)}" title="Call"><i class="fa fa-phone"></i></a>` : ''}
                                <a href="${lead.url}" title="View"><i class="fa fa-eye"></i></a>
                            </div>
                        </td>
                    </tr>`;
            });
            $('#recentLeadsBody').html(html);
        }

        function renderRevenue(revenue) {
            if (!revenue) return;
            draw('revenueChart', {
                type: 'bar',
                data: {
                    labels: revenue.labels,
                    datasets: [
                        { label: 'Booked revenue', data: revenue.revenue, backgroundColor: '#2563eb', borderRadius: 6 },
                        { label: 'Cash collected', data: revenue.cash, backgroundColor: '#16a34a', borderRadius: 6 },
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        function renderSourceChips(sources) {
            if (!sources || !sources.length) {
                $('#sourceChipList').html('<div class="pipeline-empty">No leads yet.</div>');
                return;
            }
            let html = '';
            sources.forEach(s => {
                html += `
                    <div class="source-chip-row">
                        <span class="source-chip-dot" style="background:${paletteColor(s.label)}"></span>
                        <span class="source-chip-label">${esc(s.label)}</span>
                        <span class="source-chip-total">${s.total}</span>
                    </div>`;
            });
            $('#sourceChipList').html(html);
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
                const overdue = d.overdue;
                html += `
                    <a class="closing-card" href="${d.url}">
                        <div class="closing-name">${esc(d.name)}</div>
                        <div class="closing-amount">${fmt(d.amount, true)}</div>
                        <div class="closing-date" style="background:${overdue ? '#fef2f2' : '#ecfdf5'};color:${overdue ? '#dc2626' : '#16a34a'}">${esc(d.expected_close_date ?? '')}</div>
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
                                <div class="followup-title">${esc(item.title)}</div>
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

                if (response.pipeline) {
                    $('#pipelinePerformersRow').show();
                    renderPipeline(response.pipeline);
                    if (response.topPerformers) {
                        $('#performerCol').show();
                        $('#pipelineCol').removeClass('col-lg-12').addClass('col-lg-7');
                        renderPerformers(response.topPerformers);
                    } else {
                        $('#performerCol').hide();
                        $('#pipelineCol').removeClass('col-lg-7').addClass('col-lg-12');
                    }
                }
                if (response.recentLeads) {
                    $('#recentLeadsRow').show();
                    renderRecentLeads(response.recentLeads);
                }
                if (response.revenue || response.leadSources) {
                    $('#revenueSourcesRow').show();
                    renderRevenue(response.revenue);
                    renderSourceChips(response.leadSources);
                }
                if (response.scope === 'team' && response.closingSoon) {
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
