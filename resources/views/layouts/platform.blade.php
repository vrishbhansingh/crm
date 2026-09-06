<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Platform') · {{ config('app.name', 'CRM') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">

    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --border: #e6e9f0;
            --bg: #f4f6fb;
            --accent: #4338ca;
            --accent-dark: #1e1b4b;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: "Inter", system-ui, sans-serif;
            font-size: 14px;
        }

        .platform-shell {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .platform-nav {
            background: var(--accent-dark);
            color: #fff;
            padding: 22px 16px;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .platform-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 8px 26px;
        }

        .platform-brand .mark {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .platform-brand .label {
            font-size: 14.5px;
            font-weight: 800;
            line-height: 1.25;
        }

        .platform-brand .label small {
            display: block;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .5);
        }

        .platform-nav a {
            display: flex;
            align-items: center;
            gap: 11px;
            color: rgba(255, 255, 255, .68);
            text-decoration: none;
            padding: 10px 13px;
            border-radius: 9px;
            margin: 3px 0;
            font-size: 13.5px;
            font-weight: 600;
            transition: .15s;
        }

        .platform-nav a i { width: 16px; text-align: center; font-size: 13px; }

        .platform-nav a:hover { background: rgba(255, 255, 255, .07); color: #fff; }

        .platform-nav a.active {
            background: var(--accent);
            color: #fff;
        }

        /* Main column */
        .platform-main {
            padding: 28px 32px;
            /* A grid item defaults to min-width:auto, so a wide table inside
               would grow this whole track instead of letting
               .table-responsive's own overflow-x:auto contain it. */
            min-width: 0;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .topbar h3 {
            margin: 0 0 4px;
            font-size: 21px;
            font-weight: 800;
            color: var(--ink);
        }

        .topbar .subtitle {
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .topbar form button {
            border-radius: 9px;
            font-weight: 600;
            font-size: 13px;
        }

        .alert { border-radius: 10px; border: none; font-size: 13.5px; }

        /* Cards */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(15, 23, 42, .05);
            margin-bottom: 22px;
        }

        .card-body { padding: 22px 24px; }

        .card h5 {
            font-size: 15.5px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 4px;
        }

        /* Stat grid */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 14px;
        }

        .stat {
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .stat .text-muted {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: #94a3b8 !important;
        }

        .stat strong {
            font-size: 27px;
            font-weight: 800;
            color: var(--ink);
        }

        /* Forms */
        label, .form-label { font-size: 12px !important; font-weight: 700 !important; color: #475569 !important; text-transform: uppercase; letter-spacing: .02em; }

        .form-control {
            border-radius: 9px;
            border: 1px solid var(--border);
            font-size: 13.5px;
            height: 42px;
            padding: 0 13px;
        }

        select.form-control { height: 42px; }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(67, 56, 202, .12);
        }

        /* Buttons */
        .btn { border-radius: 9px; font-weight: 600; font-size: 13px; padding: 8px 16px; }
        .btn-sm { padding: 5px 12px; font-size: 12.5px; }
        .btn-primary { background: var(--accent); border-color: var(--accent); }
        .btn-primary:hover { background: #372f9e; border-color: #372f9e; }
        .btn-outline-primary { color: var(--accent); border-color: var(--accent); }
        .btn-outline-primary:hover { background: var(--accent); border-color: var(--accent); }
        .btn-outline-secondary { color: #475569; border-color: var(--border); }
        .btn-outline-secondary:hover { background: #f1f5f9; color: var(--ink); border-color: var(--border); }

        /* Tables */
        .table { margin-bottom: 0; font-size: 13.5px; }

        .table thead th {
            border: none;
            border-bottom: 1px solid var(--border);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #94a3b8;
            padding: 10px 14px;
            white-space: nowrap;
        }

        .table tbody td {
            border-top: 1px solid #f1f5f9;
            padding: 13px 14px;
            vertical-align: middle;
            color: #334155;
        }

        .table tbody tr:hover { background: #fafbff; }

        /* Badges */
        .badge {
            border-radius: 999px;
            font-weight: 700;
            font-size: 11px;
            padding: 4px 10px;
            letter-spacing: .02em;
        }

        .badge-success, .badge-ready { background: #dcfce7; color: #166534; }
        .badge-warning, .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-danger, .badge-failed { background: #fee2e2; color: #991b1b; }
        .badge-secondary { background: #e2e8f0; color: #475569; }

        .platform-main a:not(.btn) { color: var(--accent); text-decoration: none; font-weight: 600; }
        .platform-main a:not(.btn):hover { text-decoration: underline; }

        @media (max-width: 860px) {
            .platform-shell { display: block; }
            .platform-nav { position: static; height: auto; }
            .platform-main { padding: 18px; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="platform-shell">
    <aside class="platform-nav">
        <div class="platform-brand">
            <div class="mark"><i class="fa-solid fa-shield-halved"></i></div>
            <div class="label">CRM<br><small>Control Plane</small></div>
        </div>
        <a href="{{ route('superadmin.dashboard') }}" class="{{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}"><i class="fa-solid fa-gauge-high"></i> Overview</a>
        <a href="{{ route('superadmin.tenants.index') }}" class="{{ request()->routeIs('superadmin.tenants.*','superadmin.users.*') ? 'active' : '' }}"><i class="fa-solid fa-building"></i> Companies & Signups</a>
        <a href="{{ route('superadmin.audit.index') }}" class="{{ request()->routeIs('superadmin.audit.*') ? 'active' : '' }}"><i class="fa-solid fa-clock-rotate-left"></i> Audit Log</a>
        <a href="{{ route('superadmin.settings.mail.edit') }}" class="{{ request()->routeIs('superadmin.settings.mail.*') ? 'active' : '' }}"><i class="fa-solid fa-envelope"></i> Mail Settings</a>
    </aside>
    <main class="platform-main">
        <div class="topbar">
            <div>
                <h3>@yield('heading')</h3>
                <div class="subtitle">Super Admin · Master Database</div>
            </div>
            <form method="post" action="{{ route('superadmin.logout') }}">@csrf<button class="btn btn-outline-secondary"><i class="fa-solid fa-right-from-bracket"></i> Sign out</button></form>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        @yield('content')
    </main>
</div>
<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
@stack('scripts')
</body>
</html>
