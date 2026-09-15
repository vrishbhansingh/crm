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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">

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
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.07);
            margin-bottom: 40px;
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
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #fff;
            border-radius: 13px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.07);
            padding: 18px 20px;
        }

        .stat-card .stat-top {
            display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;
        }

        .stat-card .label { font-size: 12.5px; color: var(--text-muted); font-weight: 600; }

        .stat-card .icon {
            width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
        }

        .stat-card .stat-bottom {
            display: flex; align-items: baseline; gap: 8px; margin-top: 10px;
        }

        .stat-card .value { font-size: 22px; font-weight: 700; color: var(--text-dark); line-height: 1.15; }
        .stat-card .change { font-size: 12.5px; font-weight: 700; }
        .stat-card .change.is-up { color: #16a34a; }
        .stat-card .change.is-down { color: #dc2626; }

        .icon-blue { background: #eff6ff; color: #2563eb; }
        .icon-orange { background: #fff7ed; color: #ea580c; }
        .icon-red { background: #fef2f2; color: #dc2626; }
        .icon-green { background: #ecfdf5; color: #16a34a; }
        .icon-purple { background: #f5f3ff; color: #7c3aed; }
        .icon-teal { background: #f0fdfa; color: #0d9488; }
        .icon-pink { background: #fdf2f8; color: #db2777; }
        .icon-indigo { background: #eef2ff; color: #4338ca; }

        /* Row-to-row spacing lives on the .row itself, not on .dash-card's
           own margin-bottom: .content-wrapper is a column flexbox (see
           include/footer.blade.php), so each .row is a flex item, and
           .dash-card's height:100% (for equal-height side-by-side cards)
           gets cross-axis stretched to fill its column — that stretch
           resolution silently drops a percentage-height item's own
           margin-bottom, so it never reaches the row's outer edge even
           though the card's own computed margin is genuinely 40px. Putting
           the gap on .row (which has no percentage-height self-reference)
           sidesteps that entirely; row-gap covers cards that wrap onto a
           second line at narrow widths, where .dash-card's margin-bottom
           would hit the same stretch-collapse issue between the two lines. */
        .dash-wrap > .row {
            margin-bottom: 40px;
            row-gap: 40px;
        }

        .dash-card {
            background: #fff;
            border-radius: 13px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.07);
            padding: 24px 26px;
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

        /* Upcoming Follow-ups & Reminders — grouped by urgency (Overdue /
           Today / Tomorrow / This Week / Later) with a countdown pill per
           row instead of a flat list + plain red-text date, so the admin
           can tell what needs attention right now at a glance rather than
           having to read every date. */
        .followup-list { list-style: none; margin: 0; padding: 0; }
        .followup-group + .followup-group { margin-top: 16px; }
        .followup-group-label {
            font-size: 10.5px; font-weight: 700; letter-spacing: 0.07em; text-transform: uppercase;
            color: var(--text-muted); padding: 0 0 8px; display: flex; align-items: center; gap: 6px;
        }
        .followup-group-label .grp-dot { width: 6px; height: 6px; border-radius: 50%; }
        .followup-group.grp-overdue .followup-group-label { color: #dc2626; }
        .followup-group.grp-overdue .grp-dot { background: #dc2626; }
        .followup-group.grp-today .followup-group-label { color: #b45309; }
        .followup-group.grp-today .grp-dot { background: #f59e0b; }
        .followup-group.grp-soon .followup-group-label { color: var(--text-muted); }
        .followup-group.grp-soon .grp-dot { background: #94a3b8; }

        .followup-item {
            display: flex; align-items: center; justify-content: space-between;
            gap: 10px; padding: 10px 8px; border-radius: 10px;
            transition: background .12s ease;
        }
        .followup-item:hover { background: var(--surface); }
        .followup-group.grp-overdue .followup-item { background: #fef2f2; }
        .followup-group.grp-overdue .followup-item:hover { background: #fee2e2; }
        .followup-main { display: flex; align-items: center; gap: 11px; min-width: 0; flex: 1; text-decoration: none; color: inherit; }
        .followup-badge {
            flex-shrink: 0; width: 34px; height: 34px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 13px;
        }
        .followup-title { font-size: 13.5px; font-weight: 600; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .followup-sub { font-size: 11.5px; color: var(--text-muted); }
        .followup-item-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .followup-pill {
            font-size: 11px; font-weight: 700; white-space: nowrap; flex-shrink: 0;
            padding: 4px 10px; border-radius: 999px; background: #f1f5f9; color: var(--text-muted);
        }
        .followup-pill.overdue { background: #fee2e2; color: #b91c1c; }
        .followup-pill.today { background: #fef3c7; color: #92400e; }
        .followup-pill.is-done { background: #dcfce7; color: #15803d; display: inline-flex; align-items: center; gap: 5px; }
        .followup-done-btn {
            flex-shrink: 0; width: 26px; height: 26px; border-radius: 50%;
            border: 1px solid var(--border); background: #fff; color: var(--text-muted);
            display: flex; align-items: center; justify-content: center; font-size: 11px; cursor: pointer;
            transition: background .12s ease, color .12s ease, border-color .12s ease;
        }
        .followup-done-btn:hover { background: #16a34a; border-color: #16a34a; color: #fff; }
        .followup-done-btn:disabled { opacity: .5; cursor: default; }
        .followup-empty { text-align: center; color: var(--text-muted); padding: 28px 0; font-size: 13.5px; }
        .followup-empty i { font-size: 24px; opacity: .5; display: block; margin-bottom: 8px; }

        /* Current / Upcoming / Past tabs */
        .followup-tabs { display: flex; gap: 4px; background: var(--surface); border-radius: 10px; padding: 4px; margin: -6px 0 14px; }
        .followup-tab-btn {
            flex: 1; border: none; background: transparent; padding: 7px 10px; border-radius: 8px;
            font-size: 12.5px; font-weight: 600; color: var(--text-muted); cursor: pointer;
        }
        .followup-tab-btn:hover { color: var(--text-dark); }
        .followup-tab-btn.active { background: #fff; color: var(--primary); box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08); }

        /* Mini calendar beside the list */
        .followup-layout { display: flex; gap: 18px; align-items: flex-start; }
        .followup-mini-cal { flex: 0 0 176px; }
        .followup-list { flex: 1; min-width: 0; }
        .mini-cal-header { font-size: 12px; font-weight: 700; color: var(--text-dark); text-align: center; margin-bottom: 8px; }
        .mini-cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
        .mini-cal-weekday { font-size: 9.5px; font-weight: 700; color: var(--text-muted); text-align: center; padding-bottom: 4px; }
        .mini-cal-cell {
            position: relative; aspect-ratio: 1; display: flex; align-items: center; justify-content: center;
            font-size: 10.5px; color: var(--text-dark); border-radius: 50%;
        }
        .mini-cal-cell.is-today { background: var(--primary); color: #fff; font-weight: 700; }
        .mini-cal-cell.has-due::after {
            content: ''; position: absolute; bottom: 1px; left: 50%; transform: translateX(-50%);
            width: 4px; height: 4px; border-radius: 50%; background: #f59e0b;
        }
        .mini-cal-cell.is-today.has-due::after { background: #fff; }
        @media (max-width: 480px) {
            .followup-layout { flex-direction: column; }
            .followup-mini-cal { flex: 0 0 auto; width: 100%; }
        }

        [data-theme="dark"] .followup-group.grp-overdue .followup-item { background: rgba(220, 38, 38, 0.12); }
        [data-theme="dark"] .followup-group.grp-overdue .followup-item:hover { background: rgba(220, 38, 38, 0.2); }
        [data-theme="dark"] .followup-pill { background: #232637; color: var(--text-muted); }
        [data-theme="dark"] .followup-pill.overdue { background: rgba(220, 38, 38, 0.22); color: #fca5a5; }
        [data-theme="dark"] .followup-pill.today { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
        [data-theme="dark"] .followup-pill.is-done { background: rgba(74, 222, 128, 0.16); color: #4ade80; }
        [data-theme="dark"] .followup-done-btn { background: #232637; border-color: #2a2e40; color: #9aa1b5; }
        [data-theme="dark"] .followup-done-btn:hover { background: #16a34a; border-color: #16a34a; color: #fff; }
        [data-theme="dark"] .followup-tabs { background: #232637; }
        [data-theme="dark"] .followup-tab-btn { color: #9aa1b5; }
        [data-theme="dark"] .followup-tab-btn:hover { color: #eef0f6; }
        [data-theme="dark"] .followup-tab-btn.active { background: #1a1d2b; color: #93a4fd; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3); }
        [data-theme="dark"] .mini-cal-header { color: #eef0f6; }
        [data-theme="dark"] .mini-cal-weekday { color: #9aa1b5; }
        [data-theme="dark"] .mini-cal-cell { color: #d7dbe4; }
        [data-theme="dark"] .mini-cal-cell.is-today { background: #93a4fd; color: #0f1117; }

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

        /* Dark mode — variables cover most text color via var(--text-dark)/
           var(--text-muted) already used throughout above; only the
           hardcoded white card backgrounds and light hairlines need
           explicit overrides here. */
        [data-theme="dark"] {
            --border: #2a2e40;
            --text-dark: #eef0f6;
            --text-muted: #9aa1b5;
            --surface: #232637;
        }
        [data-theme="dark"] .dash-header,
        [data-theme="dark"] .stat-card,
        [data-theme="dark"] .dash-card,
        [data-theme="dark"] .lead-metric-tile,
        [data-theme="dark"] .closing-card {
            background: #1a1d2b;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }
        [data-theme="dark"] .dash-action-btn { background: #1a1d2b; color: var(--text-dark); }
        [data-theme="dark"] .dash-action-btn.is-primary { background: var(--primary); color: #fff; }
        [data-theme="dark"] .followup-item, [data-theme="dark"] .performer-row { border-bottom-color: #2a2e40; }
        [data-theme="dark"] .followup-item:hover, [data-theme="dark"] .closing-card:hover { background: #202333; }
        [data-theme="dark"] .lead-row-actions a, [data-theme="dark"] .performer-actions a { background: #232637; }
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
                            <div class="chart-box" id="revenueChartBox"><canvas id="revenueChart"></canvas></div>
                            <div class="pipeline-empty" id="revenueEmpty" style="display:none;">No revenue recorded yet.</div>
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

                            <div class="followup-tabs" id="followupTabs">
                                <button type="button" class="followup-tab-btn active" data-tab="current">Current</button>
                                <button type="button" class="followup-tab-btn" data-tab="upcoming">Upcoming</button>
                                <button type="button" class="followup-tab-btn" data-tab="past">Past</button>
                            </div>

                            <div class="followup-layout">
                                <div class="followup-mini-cal" id="followupMiniCal"></div>
                                <ul class="followup-list" id="followUpList"></ul>
                            </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>
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

        // Chart.js draws to a <canvas> — CSS can't reach its text/gridline
        // colors, so they're set here from the current theme instead.
        function applyChartTheme() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            Chart.defaults.global.defaultFontColor = isDark ? '#9aa1b5' : '#6b7280';
            Chart.defaults.scale.gridLines.color = isDark ? '#2a2e40' : 'rgba(0,0,0,.1)';
            Chart.defaults.scale.gridLines.zeroLineColor = isDark ? '#2a2e40' : 'rgba(0,0,0,.25)';
        }
        applyChartTheme();
        document.addEventListener('crm-theme-changed', function() {
            applyChartTheme();
            loadDashboardData();
        });

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

            const hasData = revenue.revenue.some(v => v > 0) || revenue.cash.some(v => v > 0);
            if (!hasData) {
                $('#revenueChartBox').hide();
                $('#revenueEmpty').show();
                return;
            }
            $('#revenueChartBox').show();
            $('#revenueEmpty').hide();

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

        function parseWhen(when) {
            if (!when) return null;
            const hasTime = when.includes(':');
            const d = new Date(when.replace(' ', 'T'));
            return isNaN(d) ? null : { date: d, hasTime };
        }

        function formatRelative(parsed, overdue) {
            if (!parsed) return '';
            const { date, hasTime } = parsed;
            const now = new Date();
            const timeStr = hasTime ? date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }) : '';

            if (overdue) {
                const hours = Math.floor((now - date) / 3600000);
                if (hours < 1) return 'Overdue';
                if (hours < 24) return 'Overdue ' + hours + 'h';
                return 'Overdue ' + Math.floor(hours / 24) + 'd';
            }
            if (date.toDateString() === now.toDateString()) return timeStr ? ('Today, ' + timeStr) : 'Today';
            const tomorrow = new Date(now);
            tomorrow.setDate(now.getDate() + 1);
            if (date.toDateString() === tomorrow.toDateString()) return timeStr ? ('Tomorrow, ' + timeStr) : 'Tomorrow';
            const dayLabel = date.toLocaleDateString([], { weekday: 'short', day: 'numeric', month: 'short' });
            return timeStr ? (dayLabel + ', ' + timeStr) : dayLabel;
        }

        const FOLLOWUP_GROUPS = [
            { key: 'overdue', cls: 'grp-overdue', label: 'Needs attention' },
            { key: 'today', cls: 'grp-today', label: 'Today' },
            { key: 'soon', cls: 'grp-soon', label: 'Coming up' },
        ];

        let currentFollowUps = [];
        let pastFollowUps = [];
        let activeFollowTab = 'current';

        function followUpItemHtml(item) {
            const meta = followTypeMeta[item.type] || followTypeMeta.lead;
            const parsed = parseWhen(item.when);
            const pillCls = item.overdue ? 'overdue' : (parsed && parsed.date.toDateString() === new Date().toDateString() ? 'today' : '');
            return `
                <li class="followup-item">
                    <a class="followup-main" href="${item.url}">
                        <div class="followup-badge ${meta.color}"><i class="fa ${meta.icon}"></i></div>
                        <div>
                            <div class="followup-title">${esc(item.title)}</div>
                            <div class="followup-sub">${meta.label}</div>
                        </div>
                    </a>
                    <div class="followup-item-actions">
                        <div class="followup-pill ${pillCls}">${formatRelative(parsed, item.overdue)}</div>
                        <button type="button" class="followup-done-btn" title="Mark done"
                            data-source="${item.source}" data-id="${item.id}" data-task-id="${item.task_id ?? ''}">
                            <i class="fa fa-check"></i>
                        </button>
                    </div>
                </li>`;
        }

        function formatPastWhen(when) {
            const parsed = parseWhen(when);
            if (!parsed) return '';
            const { date, hasTime } = parsed;
            const now = new Date();
            const timeStr = hasTime ? date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }) : '';
            if (date.toDateString() === now.toDateString()) return timeStr ? ('Today, ' + timeStr) : 'Today';
            const yesterday = new Date(now);
            yesterday.setDate(now.getDate() - 1);
            if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';
            return date.toLocaleDateString([], { weekday: 'short', day: 'numeric', month: 'short' });
        }

        function pastItemHtml(item) {
            const meta = followTypeMeta[item.type] || followTypeMeta.lead;
            return `
                <li class="followup-item">
                    <a class="followup-main" href="${item.url}">
                        <div class="followup-badge ${meta.color}"><i class="fa ${meta.icon}"></i></div>
                        <div>
                            <div class="followup-title">${esc(item.title)}</div>
                            <div class="followup-sub">${meta.label}</div>
                        </div>
                    </a>
                    <div class="followup-item-actions">
                        <div class="followup-pill is-done"><i class="fa fa-check"></i> ${formatPastWhen(item.when)}</div>
                    </div>
                </li>`;
        }

        function bucketFollowUps(items) {
            const now = new Date();
            const buckets = { overdue: [], today: [], soon: [] };
            (items || []).forEach(item => {
                const parsed = parseWhen(item.when);
                if (item.overdue) buckets.overdue.push(item);
                else if (parsed && parsed.date.toDateString() === now.toDateString()) buckets.today.push(item);
                else buckets.soon.push(item);
            });
            return buckets;
        }

        function renderFollowTab() {
            if (activeFollowTab === 'past') {
                $('#followUpList').html(
                    pastFollowUps.length
                        ? pastFollowUps.map(pastItemHtml).join('')
                        : '<div class="followup-empty"><i class="fa fa-history"></i>Nothing marked done in the last 7 days yet.</div>'
                );
                return;
            }

            const buckets = bucketFollowUps(currentFollowUps);
            const groupKeys = activeFollowTab === 'current' ? ['overdue', 'today'] : ['soon'];
            const groups = FOLLOWUP_GROUPS.filter(g => groupKeys.includes(g.key) && buckets[g.key].length);

            if (!groups.length) {
                const msg = activeFollowTab === 'current'
                    ? "Nothing needs attention right now — you're all caught up."
                    : 'Nothing coming up in the next few days.';
                $('#followUpList').html(`<div class="followup-empty"><i class="fa fa-check-circle-o"></i>${msg}</div>`);
                return;
            }

            $('#followUpList').html(groups.map(g => `
                <div class="followup-group ${g.cls}">
                    <div class="followup-group-label"><span class="grp-dot"></span>${g.label} &middot; ${buckets[g.key].length}</div>
                    ${buckets[g.key].map(followUpItemHtml).join('')}
                </div>`).join(''));
        }

        function renderMiniCalendar() {
            const now = new Date();
            const year = now.getFullYear();
            const month = now.getMonth();
            const firstDay = new Date(year, month, 1);
            const startWeekday = firstDay.getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            const dueDays = new Set();
            currentFollowUps.forEach(item => {
                const parsed = parseWhen(item.when);
                if (parsed && parsed.date.getMonth() === month && parsed.date.getFullYear() === year) {
                    dueDays.add(parsed.date.getDate());
                }
            });

            let html = `<div class="mini-cal-header">${firstDay.toLocaleDateString([], { month: 'long', year: 'numeric' })}</div><div class="mini-cal-grid">`;
            ['S', 'M', 'T', 'W', 'T', 'F', 'S'].forEach(w => html += `<div class="mini-cal-weekday">${w}</div>`);
            for (let i = 0; i < startWeekday; i++) html += `<div class="mini-cal-cell"></div>`;
            for (let d = 1; d <= daysInMonth; d++) {
                const cls = [d === now.getDate() ? 'is-today' : '', dueDays.has(d) ? 'has-due' : ''].filter(Boolean).join(' ');
                html += `<div class="mini-cal-cell ${cls}">${d}</div>`;
            }
            html += `</div>`;
            $('#followupMiniCal').html(html);
        }

        function renderFollowUps(items) {
            currentFollowUps = items || [];
            renderMiniCalendar();
            renderFollowTab();
        }

        function renderPastFollowUps(items) {
            pastFollowUps = items || [];
            if (activeFollowTab === 'past') renderFollowTab();
        }

        $('#followupTabs').on('click', '.followup-tab-btn', function() {
            activeFollowTab = $(this).data('tab');
            $('.followup-tab-btn').removeClass('active');
            $(this).addClass('active');
            renderFollowTab();
        });

        $(document).on('click', '.followup-done-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $btn = $(this).prop('disabled', true);
            const source = $btn.data('source');
            const id = $btn.data('id');
            const taskId = $btn.data('task-id');
            const url = source === 'task'
                ? "{{ url('tasks') }}/" + taskId + "/complete"
                : "{{ url('leads') }}/" + id + "/follow-up/complete";

            $.post(url, {}).done(function(res) {
                toastr.success(res.message || 'Marked done');
                loadDashboardData();
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Something went wrong');
                $btn.prop('disabled', false);
            });
        });

        function loadDashboardData() {
            $.get("{{ route('dashboard.data') }}", function(response) {
                const cards = response.scope === 'team' ? teamCards : ownCards;
                let html = '';
                cards.forEach(c => {
                    let changeHtml = '';
                    if (c.key === 'newLeadToday' && response.data.newLeadYesterday !== undefined) {
                        const today = Number(response.data.newLeadToday || 0);
                        const yesterday = Number(response.data.newLeadYesterday || 0);
                        if (yesterday > 0) {
                            const pct = Math.round(((today - yesterday) / yesterday) * 100);
                            const up = pct >= 0;
                            changeHtml = `<span class="change ${up ? 'is-up' : 'is-down'}"><i class="fa fa-arrow-${up ? 'up' : 'down'}"></i> ${Math.abs(pct)}%</span>`;
                        } else if (today > 0) {
                            changeHtml = `<span class="change is-up"><i class="fa fa-arrow-up"></i> new</span>`;
                        }
                    }
                    html += `
                        <div class="stat-card">
                            <div class="stat-top">
                                <div class="label">${c.label}</div>
                                <div class="icon ${c.color}"><i class="fa ${c.icon}"></i></div>
                            </div>
                            <div class="stat-bottom">
                                <div class="value">${fmt(response.data[c.key], c.money)}</div>
                                ${changeHtml}
                            </div>
                        </div>`;
                });
                $('#statGrid').html(html);

                renderFollowUps(response.followUps);
                renderPastFollowUps(response.pastFollowUps);

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
