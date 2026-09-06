<style>
  .crm-navbar-wrap {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
  }

  /* Compensate the page content for the fixed header + breadcrumb bar.
     Overrides the vendor template's .page-body-wrapper{padding-top:60px}. */
  .page-body-wrapper { padding-top: 98px !important; }
  @media (max-width: 991px) { .page-body-wrapper { padding-top: 64px !important; } }
  @media (max-width: 768px) { .page-body-wrapper { padding-top: 56px !important; } }

  .crm-navbar {
    height: 64px;
    background: linear-gradient(90deg, #f9fafb 0%, #eef2ff 50%, #f0f9ff 100%);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    display: flex;
    align-items: center;
  }

  /* The breadcrumb bar's left edge stays the sidebar's own blue so the
     brand column and sidebar read as one continuous panel from the top of
     the page down, instead of a white header sitting disconnected above a
     blue sidebar. Breadcrumb content is pushed clear of that blue strip. */
  .crm-breadcrumb {
    height: 34px;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 24px 0 calc(var(--sidebar-w, 244px) + 24px);
    background: linear-gradient(to right, #0c7bfe 0, #0c7bfe var(--sidebar-w, 244px), #ffffff var(--sidebar-w, 244px), #ffffff 100%);
    border-top: 1px solid #edf0f5;
    font-size: 12.5px;
    color: #6b7280;
    overflow-x: auto;
    white-space: nowrap;
    transition: padding-left 0.2s ease, background 0.2s ease;
  }
  .crm-breadcrumb a { color: #6b7280; text-decoration: none; }
  .crm-breadcrumb a:hover { color: #4b49ac; text-decoration: underline; }
  .crm-breadcrumb .sep { color: #cbd1db; font-size: 11px; }
  .crm-breadcrumb .current { color: #1f2937; font-weight: 600; }
  .crm-breadcrumb i.fa-home { color: #9ca3af; font-size: 12px; }
  @media (max-width: 991px) { .crm-breadcrumb { display: none; } }

  .crm-brand-wrapper {
    /* Tracks the sidebar's own width exactly, so the blue brand column and
       the icon rail beneath it shrink together instead of the column
       staying wide while the rail below it goes narrow. The full wordmark
       doesn't fit in the collapsed 72px, so .crm-brand-chip crops down to
       just the logo's icon mark instead (see below) rather than either
       overflowing or squishing the whole lockup illegibly. */
    width: var(--sidebar-w, 244px);
    height: 64px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(180deg, #0c7bfe, #01bdff);
    transition: width 0.2s ease;
  }

  .sidebar-collapse-btn {
    position: absolute;
    top: 50%;
    right: -11px;
    transform: translateY(-50%);
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #fff;
    color: #0a6cff;
    border: 1px solid #dde9ff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 10px;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.18);
    z-index: 5;
    transition: background 0.15s ease;
  }
  .sidebar-collapse-btn:hover { background: #eef6ff; }
  .sidebar-collapse-btn.is-collapsed i { transform: rotate(180deg); }

  .crm-brand-chip {
    background: #ffffff;
    border-radius: 8px;
    padding: 5px 12px;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: width 0.2s ease, padding 0.2s ease;
  }
  .crm-brand-wrapper img {
    display: block;
    height: 22px;
    width: auto;
    flex-shrink: 0;
    transition: margin-left 0.2s ease;
  }

  /* Collapsed: the full "icon + CRMS" lockup doesn't fit in 72px, so the
     chip clips down to a window sized to the logo's leading icon mark
     (measured from the source image's actual opaque pixels — it starts
     right at the image's left edge and runs about half the full width,
     with "CRMS" beginning well clear of this window) instead of squishing
     the whole wordmark or cutting the icon off mid-shape. */
  html.sidebar-collapsed .crm-brand-chip {
    width: 64px;
    padding: 5px 4px;
  }

  /* A tenant-uploaded company logo has no known internal layout to crop
     to (unlike the static app wordmark above) — scale it to fit instead,
     both expanded and collapsed, rather than applying the same crop. */
  .crm-brand-chip.has-custom-logo {
    max-width: 200px;
    padding: 6px 10px;
  }
  .crm-brand-chip.has-custom-logo img {
    height: 32px;
    width: auto;
    max-width: 100%;
    object-fit: contain;
  }
  html.sidebar-collapsed .crm-brand-chip.has-custom-logo {
    width: 48px;
    max-width: 48px;
    padding: 6px;
  }
  html.sidebar-collapsed .crm-brand-chip.has-custom-logo img {
    height: 100%;
    width: 100%;
  }

  /* Global content-area padding — overrides the vendor template's
     .content-wrapper{padding:1.375rem 2.375rem}, which ran wider (38px)
     than the app's spacing scale. */
  .content-wrapper { padding: 24px 28px !important; }
  @media (max-width: 767px) { .content-wrapper { padding: 18px !important; } }

  .crm-menu-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
  }

  .crm-left {
    display: flex;
    align-items: center;
    gap: 18px;
    visibility: hidden;
  }

  .crm-toggle {
    font-size: 18px;
    color: #4b49ac;
    cursor: pointer;
  }

  .crm-right {
    display: flex;
    align-items: center;
    gap: 18px;
  }

  .crm-profile {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 8px;
    transition: background 0.15s ease;
  }
  .crm-profile:hover { background: rgba(75, 73, 172, 0.06); }

  .crm-profile-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    object-fit: cover; flex-shrink: 0; border: 1px solid #e5e7eb;
  }

  .notification-link { position: relative; color:#475569; font-size:19px; }
  .notification-count { position:absolute; top:-8px; right:-10px; min-width:17px; height:17px; border-radius:10px; background:#ef4444; color:#fff; font-size:10px; line-height:17px; text-align:center; }

  .crm-profile-caret { font-size: 10px; color: #9ca3af; margin-left: 2px; }

  .crm-profile-text {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
  }

  .crm-profile-text span {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
  }

  .crm-profile-text small {
    font-size: 10.5px;
    color: #6b7280;
  }

  .crm-dropdown {
    position: absolute;
    top: 104px; /* below navbar (64px) + breadcrumb (34px) */
    right: 20px;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    width: 180px;
    display: none;
    overflow: hidden;
  }

  .crm-dropdown a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    font-size: 13px;
    color: #374151;
    text-decoration: none;
  }

  .crm-dropdown a:hover {
    background: #f3f4f6;
  }

  .crm-dropdown i {
    color: #4b49ac;
  }

  .crm-dropdown form {
    margin: 0;
  }

  .crm-dropdown .logout-btn {
    width: 100%;
    background: transparent;
    border: none;
    padding: 10px 15px;
    font-size: 14px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    text-align: left;
  }

  .crm-dropdown .logout-btn:hover {
    background: #f8f9fa;
    color: #dc3545;
  }

  .crm-dropdown .logout-btn:hover i {
    color: #dc3545;
  }

  @media (max-width: 945px) {
    .crm-left {
      visibility: visible;
      gap: 12px;
    }
  }

  @media (max-width: 991px) {
    /* Breadcrumb bar is hidden below this width, so the dropdown only
       needs to clear the 64px navbar, not the breadcrumb too. */
    .crm-dropdown { top: 70px; }
    /* Sidebar becomes a fixed overlay drawer below this width, toggled by
       .crm-toggle instead — the desktop collapse pill has nothing to do. */
    .sidebar-collapse-btn { display: none; }
  }

  @media (max-width: 768px) {
    .crm-navbar {
      height: 56px;
      padding: 0 12px;
    }

    .crm-brand-wrapper {
      width: auto;
      padding: 0 10px;
    }

    .crm-brand-wrapper img {
      height: 28px;
    }

    .crm-toggle {
      font-size: 20px;
    }

    .crm-profile-text span { font-size: 12px; }
    .crm-profile-text small { display: none; }
    .crm-profile-caret { display: none; }

    .crm-dropdown {
      right: 10px;
      top: 60px;
    }
  }
</style>

@php
  // Route-name -> breadcrumb map. Kept deliberately explicit (not
  // auto-derived from arbitrary route-name patterns) so every crumb is a
  // label a user actually recognizes, not a guess from a URL segment.
  $crumbMap = [
    'dashboard' => [null, null],
    'leads.index' => ['Sales', 'Leads'],
    'leads.create' => ['Sales', 'Leads', 'New Lead'],
    'leads.show' => ['Sales', 'Leads', 'Lead Details'],
    'leads.edit' => ['Sales', 'Leads', 'Edit Lead'],
    'deals.index' => ['Sales', 'Deals'],
    'deals.list' => ['Sales', 'Deals'],
    'deals.create' => ['Sales', 'Deals', 'New Deal'],
    'deals.show' => ['Sales', 'Deals', 'Deal Details'],
    'deals.edit' => ['Sales', 'Deals', 'Edit Deal'],
    'companies.index' => ['Sales', 'Companies'],
    'companies.show' => ['Sales', 'Companies', 'Company Details'],
    'contacts.index' => ['Sales', 'Contacts'],
    'orders.index' => ['Delivery & Billing', 'Orders'],
    'orders.show' => ['Delivery & Billing', 'Orders', 'Order Details'],
    'invoice.show' => ['Delivery & Billing', 'Orders', 'Invoice'],
    'quotation.template1' => ['Delivery & Billing', 'Orders', 'Quotation'],
    'quotation.template2' => ['Delivery & Billing', 'Orders', 'Quotation'],
    'quotation.template3' => ['Delivery & Billing', 'Orders', 'Quotation'],
    'projects.index' => ['Delivery & Billing', 'Projects'],
    'tasks.index' => ['Activity', 'Tasks & Reminders'],
    'notifications.index' => [null, 'Notifications'],
    'reports.index' => ['Insights', 'Reports & Analytics'],
    'audit.index' => ['Insights', 'Audit Log'],
    'users.index' => ['Administration', 'Users'],
    'roles.index' => ['Administration', 'Roles & Permissions'],
    'company.show' => ['Administration', 'Organization Profile'],
    'company.edit' => ['Administration', 'Organization Profile', 'Edit'],
    'master_data.index' => ['Administration', 'Master Data'],
    'pipelines.index' => ['Administration', 'Pipelines'],
    'settings.mail.edit' => ['Administration', 'Mail Settings'],
    'profile.show' => [null, 'My Profile'],
    'security.show' => [null, 'Security'],
    'api_tokens.index' => [null, 'API Tokens'],
  ];
  $crumbRouteLinks = [
    'Leads' => 'leads.index', 'Deals' => 'deals.list', 'Companies' => 'companies.index', 'Contacts' => 'contacts.index',
    'Orders' => 'orders.index', 'Projects' => 'projects.index', 'Tasks & Reminders' => 'tasks.index',
    'Reports & Analytics' => 'reports.index', 'Audit Log' => 'audit.index', 'Users' => 'users.index',
    'Roles & Permissions' => 'roles.index', 'Organization Profile' => 'company.show', 'Master Data' => 'master_data.index',
    'Pipelines' => 'pipelines.index',
  ];
  $currentRouteName = request()->route()?->getName();
  $crumb = $crumbMap[$currentRouteName] ?? null;
  $crumbParts = $crumb ? array_values(array_filter($crumb)) : [];
  if (empty($crumbParts)) {
    $prefix = $currentRouteName ? explode('.', $currentRouteName)[0] : 'dashboard';
    $crumbParts = [ucwords(str_replace('_', ' ', $prefix))];
  }
  // Each tenant can upload its own logo (Organization Profile); fall back
  // to the generic app mark for tenants that haven't set one yet.
  $companyLogo = \App\Models\CompanyDetails::first()?->company_logo;
  $authUser = Auth::guard('web')->user();
  $avatarUrl = $authUser->avatar ? asset($authUser->avatar) : asset('images/profile_img.jpg');
@endphp

<div class="crm-navbar-wrap">
<div class="crm-navbar">
  <div class="crm-brand-wrapper">
    <a href="{{ route('dashboard') }}" class="crm-brand-chip {{ $companyLogo ? 'has-custom-logo' : '' }}">
      <img src="{{ $companyLogo ? asset($companyLogo) : asset('images/logo.svg') }}" alt="Logo">
    </a>
    <button type="button" class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Collapse sidebar" aria-label="Collapse sidebar">
        <i class="fa fa-angle-left"></i>
    </button>
  </div>

  <div class="crm-menu-wrapper">
    <div class="crm-left">
      <i class="fa fa-bars crm-toggle" data-toggle="minimize"></i>
    </div>

    <div class="crm-right">
      @if(session('impersonator_id'))
        <form method="post" action="{{ route('impersonation.stop') }}">@csrf<button class="btn btn-sm btn-warning">End support session</button></form>
      @endif
      <a class="notification-link" href="{{ route('notifications.index') }}" title="Notifications">
        <i class="fa fa-bell-o"></i>
        @php $unreadNotificationCount = Auth::guard('web')->user()->unreadNotifications()->count(); @endphp
        @if($unreadNotificationCount)<span class="notification-count">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>@endif
      </a>
      <div class="crm-profile" id="profileToggle">
        <img src="{{ $avatarUrl }}" class="crm-profile-avatar" alt="">
        <div class="crm-profile-text">
          <span>{{ Auth::guard('web')->user()->name }}</span>
          <small>{{ Auth::guard('web')->user()->getRoleNames()->first() }}</small>
        </div>
        <i class="fa fa-angle-down crm-profile-caret"></i>
      </div>

      <div class="crm-dropdown" id="profileDropdown">
        <a href="{{ route('profile.show') }}"><i class="fa fa-user"></i> Profile</a>
        <a href="{{ route('security.show') }}">
          <i class="fa fa-shield"></i> Security
        </a>
        <a href="{{ route('api_tokens.index') }}">
          <i class="fa fa-key"></i> API Tokens
        </a>
        <form method="post" action="{{ route('admin.logout') }}">
          @csrf
          <button class="logout-btn">
            <i class="fa fa-sign-out"></i> Logout
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<nav class="crm-breadcrumb" aria-label="Breadcrumb">
  <a href="{{ route('dashboard') }}"><i class="fa fa-home"></i></a>
  @foreach($crumbParts as $i => $label)
    <span class="sep">/</span>
    @if($i === count($crumbParts) - 1)
      <span class="current">{{ $label }}</span>
    @elseif(isset($crumbRouteLinks[$label]))
      <a href="{{ route($crumbRouteLinks[$label]) }}">{{ $label }}</a>
    @else
      <span>{{ $label }}</span>
    @endif
  @endforeach
</nav>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const profile = document.getElementById("profileToggle");
    const dropdown = document.getElementById("profileDropdown");

    profile.addEventListener("click", function(e) {
      e.stopPropagation();
      dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    });

    dropdown.addEventListener("click", function(e) {
      e.stopPropagation();
    });

    document.addEventListener("click", function() {
      dropdown.style.display = "none";
    });
  });
</script>
