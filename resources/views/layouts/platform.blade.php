<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Platform') · {{ config('app.name', 'CRM') }}</title>
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <style>
        body{margin:0;background:#f6f8fc;color:#172033;font-family:Inter,Arial,sans-serif}.platform-shell{display:grid;grid-template-columns:240px 1fr;min-height:100vh}.platform-nav{background:#101827;color:#fff;padding:26px 18px}.platform-brand{font-size:20px;font-weight:800;margin:0 10px 28px}.platform-nav a{display:block;color:#cbd5e1;text-decoration:none;padding:11px 13px;border-radius:9px;margin:4px 0}.platform-nav a.active,.platform-nav a:hover{background:#2563eb;color:#fff}.platform-main{padding:30px}.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px}.card{border:0;border-radius:14px;box-shadow:0 5px 24px rgba(15,23,42,.07)}.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px}.stat{padding:20px}.stat strong{font-size:28px;display:block}.table td,.table th{vertical-align:middle}.badge-ready{background:#dcfce7;color:#166534}.badge-pending{background:#fef3c7;color:#92400e}.badge-failed{background:#fee2e2;color:#991b1b}@media(max-width:760px){.platform-shell{display:block}.platform-nav{min-height:auto}.platform-main{padding:18px}}
    </style>
    @stack('styles')
</head>
<body>
<div class="platform-shell">
    <aside class="platform-nav">
        <div class="platform-brand">CRM Control Plane</div>
        <a href="{{ route('superadmin.dashboard') }}" class="{{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">Overview</a>
        <a href="{{ route('superadmin.tenants.index') }}" class="{{ request()->routeIs('superadmin.tenants.*','superadmin.users.*') ? 'active' : '' }}">Companies & Signups</a>
    </aside>
    <main class="platform-main">
        <div class="topbar"><div><h3 class="mb-1">@yield('heading')</h3><div class="text-muted">Super Admin · master database</div></div><form method="post" action="{{ route('superadmin.logout') }}">@csrf<button class="btn btn-outline-secondary">Sign out</button></form></div>
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
