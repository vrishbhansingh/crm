<link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

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
  .page-body-wrapper { padding-top: 80px !important; }
  @media (max-width: 991px) { .page-body-wrapper { padding-top: 52px !important; } }
  @media (max-width: 768px) { .page-body-wrapper { padding-top: 46px !important; } }

  .crm-navbar {
    height: 52px;
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
    height: 28px;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 24px 0 calc(var(--sidebar-w, 200px) + 24px);
    background: linear-gradient(to right, #0c7bfe 0, #0c7bfe var(--sidebar-w, 200px), #ffffff var(--sidebar-w, 200px), #ffffff 100%);
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

  [data-theme="dark"] .crm-navbar {
    background: linear-gradient(90deg, #12141d 0%, #161a2c 50%, #12141d 100%);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
  }
  [data-theme="dark"] .crm-breadcrumb {
    background: linear-gradient(to right, #14172e 0, #14172e var(--sidebar-w, 200px), #12141d var(--sidebar-w, 200px), #12141d 100%);
    border-top-color: #232637;
    color: #9aa1b5;
  }
  [data-theme="dark"] .crm-brand-wrapper {
    background: linear-gradient(180deg, #14172e, #1a1e3d);
  }
  [data-theme="dark"] .sidebar-collapse-btn {
    background: #232637;
    color: #93a4fd;
    border-color: #2a2e40;
  }
  [data-theme="dark"] .sidebar-collapse-btn:hover { background: #2a2e40; }
  [data-theme="dark"] .crm-breadcrumb a { color: #9aa1b5; }
  [data-theme="dark"] .crm-breadcrumb .current { color: #eef0f6; }
  [data-theme="dark"] .crm-toggle { color: #93a4fd; }
  [data-theme="dark"] .icon-btn { background: #232637; color: #b8bed2; }
  [data-theme="dark"] .icon-btn:hover { background: #2c3049; color: #ffffff; }
  [data-theme="dark"] .crm-profile:hover { background: rgba(255, 255, 255, 0.06); }
  [data-theme="dark"] .crm-profile-text span { color: #eef0f6; }
  [data-theme="dark"] .crm-profile-text small { color: #9aa1b5; }
  [data-theme="dark"] .crm-profile-avatar { border-color: #343850; }

  .crm-brand-wrapper {
    /* Tracks the sidebar's own width exactly, so the blue brand column and
       the icon rail beneath it shrink together instead of the column
       staying wide while the rail below it goes narrow. The full wordmark
       doesn't fit in the collapsed 72px, so .crm-brand-chip crops down to
       just the logo's icon mark instead (see below) rather than either
       overflowing or squishing the whole lockup illegibly. */
    width: var(--sidebar-w, 200px);
    height: 52px;
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
    gap: 14px;
    flex: 1;
    min-width: 0;
  }

  .crm-toggle {
    display: none;
    font-size: 18px;
    color: #4b49ac;
    cursor: pointer;
    flex-shrink: 0;
  }

  @media (max-width: 945px) {
    .crm-toggle { display: block; }
  }

  /* Universal search — left side of the top bar, next to the sidebar
     toggle. A single input rather than a full search page, in keeping
     with this app's low-click-count design: type, see grouped results,
     click one. */
  .crm-search-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 280px;
    max-width: 100%;
    background: #ffffff;
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 10px;
    padding: 0 12px;
    height: 36px;
    transition: width 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
  }
  .crm-search-wrapper:focus-within {
    width: 340px;
    border-color: #4b49ac;
    box-shadow: 0 0 0 3px rgba(75, 73, 172, 0.12);
  }
  .crm-search-icon { color: #9ca3af; font-size: 13px; flex-shrink: 0; }
  .crm-search-input {
    border: none;
    outline: none;
    background: transparent;
    flex: 1;
    min-width: 0;
    margin-left: 8px;
    font-size: 13px;
    color: #374151;
  }
  .crm-search-input::placeholder { color: #9ca3af; }
  .crm-search-clear {
    display: none;
    border: none;
    background: transparent;
    color: #9ca3af;
    cursor: pointer;
    font-size: 12px;
    padding: 4px;
    flex-shrink: 0;
  }
  .crm-search-wrapper.has-value .crm-search-clear { display: block; }

  .crm-search-results {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    width: 380px;
    max-width: calc(100vw - 32px);
    max-height: 420px;
    overflow-y: auto;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.16);
    display: none;
    z-index: 1200;
  }
  .crm-search-results.is-open { display: block; }

  .crm-search-group-label {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #9ca3af;
    padding: 10px 14px 4px;
  }
  .crm-search-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 9px 14px;
    text-decoration: none;
    color: inherit;
  }
  .crm-search-item:hover { background: #f5f6fb; }
  .crm-search-item-icon {
    width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
    background: #eef0fb; color: #4b49ac;
    display: flex; align-items: center; justify-content: center; font-size: 13px;
  }
  .crm-search-item-title { font-size: 13px; font-weight: 600; color: #1f2937; line-height: 1.3; }
  .crm-search-item-subtitle { font-size: 11.5px; color: #9ca3af; line-height: 1.3; }
  .crm-search-empty, .crm-search-hint {
    padding: 24px 14px;
    text-align: center;
    color: #9ca3af;
    font-size: 12.5px;
  }

  [data-theme="dark"] .crm-search-wrapper { background: #232637; border-color: #2a2e40; }
  [data-theme="dark"] .crm-search-input { color: #eef0f6; }
  [data-theme="dark"] .crm-search-results { background: #1e2233; box-shadow: 0 16px 36px rgba(0, 0, 0, 0.4); }
  [data-theme="dark"] .crm-search-item:hover { background: rgba(255, 255, 255, 0.06); }
  [data-theme="dark"] .crm-search-item-title { color: #eef0f6; }
  [data-theme="dark"] .crm-search-item-icon { background: rgba(147, 164, 253, 0.16); color: #93a4fd; }

  @media (max-width: 768px) {
    .crm-search-wrapper { display: none; }
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

  .icon-btn {
    width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: #f1f3f9; color: #475569; border: none; font-size: 15px;
    cursor: pointer; transition: background 0.15s ease, color 0.15s ease;
    text-decoration: none;
  }
  .icon-btn:hover { background: #e5e9f5; color: #1e293b; }

  .notification-link { position: relative; }
  .notification-count { position:absolute; top:-2px; right:-4px; min-width:17px; height:17px; border-radius:10px; background:#ef4444; color:#fff; font-size:10px; line-height:17px; text-align:center; padding:0 3px; }

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
    top: 88px; /* below navbar (52px) + breadcrumb (28px), plus a small gap */
    right: 20px;
    background: #1e2233;
    border-radius: 14px;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.28);
    width: 200px;
    padding: 8px;
    display: none;
    overflow: hidden;
  }

  .crm-dropdown a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 12px;
    margin: 2px 0;
    border-radius: 10px;
    font-size: 13.5px;
    color: #e2e8f5;
    text-decoration: none;
  }

  .crm-dropdown a:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
  }

  .crm-dropdown i {
    color: #93a4fd;
    width: 16px;
    text-align: center;
  }

  .crm-dropdown form {
    margin: 6px 0 0;
    padding-top: 6px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
  }

  .crm-dropdown .logout-btn {
    width: 100%;
    background: transparent;
    border: none;
    padding: 11px 12px;
    margin: 2px 0;
    border-radius: 10px;
    font-size: 13.5px;
    color: #e2e8f5;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    text-align: left;
  }

  .crm-dropdown .logout-btn i { color: #93a4fd; width: 16px; text-align: center; }

  .crm-dropdown .logout-btn:hover {
    background: rgba(239, 68, 68, 0.15);
    color: #fca5a5;
  }

  .crm-dropdown .logout-btn:hover i {
    color: #fca5a5;
  }

  /* Notification bell dropdown — a light content-dense panel (unlike the
     always-dark profile menu above) since it needs to carry an icon,
     title, message and timestamp per row rather than a single link label. */
  .crm-notif-dropdown {
    position: absolute;
    top: 88px;
    right: 68px;
    width: 340px;
    max-width: calc(100vw - 32px);
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.16);
    border: 1px solid #eef0f5;
    display: none;
    overflow: hidden;
    z-index: 1001;
  }
  .crm-notif-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px 12px;
    border-bottom: 1px solid #f1f3f9;
  }
  .crm-notif-head strong { font-size: 14px; color: #111827; }
  .crm-notif-head button {
    background: none; border: none; padding: 0;
    font-size: 12px; font-weight: 600; color: #2563eb; cursor: pointer;
  }
  .crm-notif-head button:hover { text-decoration: underline; }
  .crm-notif-list { max-height: 360px; overflow-y: auto; }
  .crm-notif-item {
    display: flex; gap: 12px; padding: 12px 16px;
    border-bottom: 1px solid #f6f7fb;
    cursor: pointer; text-decoration: none; color: inherit;
  }
  .crm-notif-item:last-child { border-bottom: none; }
  .crm-notif-item:hover { background: #f8fafc; }
  .crm-notif-item.is-unread { background: #f5f8ff; }
  .crm-notif-item.is-unread:hover { background: #eef3ff; }
  .crm-notif-icon {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 13px;
  }
  .crm-notif-icon.type-lead { background: #f3e8ff; color: #9333ea; }
  .crm-notif-icon.type-task { background: #e0e7ff; color: #4338ca; }
  .crm-notif-body { min-width: 0; flex: 1; }
  .crm-notif-title {
    font-size: 13px; font-weight: 700; color: #1f2937;
    display: flex; align-items: center; gap: 6px;
  }
  .crm-notif-dot { width: 7px; height: 7px; border-radius: 50%; background: #2563eb; flex-shrink: 0; }
  .crm-notif-message {
    font-size: 12.5px; color: #4b5563; margin-top: 1px;
    overflow: hidden; text-overflow: ellipsis; display: -webkit-box;
    -webkit-line-clamp: 2; -webkit-box-orient: vertical;
  }
  .crm-notif-time { font-size: 11px; color: #9ca3af; margin-top: 4px; }
  .crm-notif-empty { padding: 36px 16px; text-align: center; color: #9ca3af; font-size: 13px; }
  .crm-notif-foot {
    padding: 10px 16px; text-align: center; border-top: 1px solid #f1f3f9;
  }
  .crm-notif-foot a { font-size: 12.5px; font-weight: 600; color: #2563eb; text-decoration: none; }
  .crm-notif-foot a:hover { text-decoration: underline; }

  [data-theme="dark"] .crm-notif-dropdown { background: #1a1d2b; border-color: #262a3a; box-shadow: 0 16px 36px rgba(0, 0, 0, 0.4); }
  [data-theme="dark"] .crm-notif-head { border-bottom-color: #262a3a; }
  [data-theme="dark"] .crm-notif-head strong { color: #eef0f6; }
  [data-theme="dark"] .crm-notif-item { border-bottom-color: #232637; }
  [data-theme="dark"] .crm-notif-item:hover { background: #202333; }
  [data-theme="dark"] .crm-notif-item.is-unread { background: rgba(147, 164, 253, 0.08); }
  [data-theme="dark"] .crm-notif-item.is-unread:hover { background: rgba(147, 164, 253, 0.14); }
  [data-theme="dark"] .crm-notif-title { color: #eef0f6; }
  [data-theme="dark"] .crm-notif-message { color: #b8bed2; }
  [data-theme="dark"] .crm-notif-time { color: #6b7280; }
  [data-theme="dark"] .crm-notif-icon.type-lead { background: rgba(147, 51, 234, 0.18); color: #c084fc; }
  [data-theme="dark"] .crm-notif-icon.type-task { background: rgba(67, 56, 202, 0.22); color: #93a4fd; }
  [data-theme="dark"] .crm-notif-foot { border-top-color: #262a3a; }

  @media (max-width: 768px) {
    .crm-notif-dropdown { right: 10px; top: 60px; width: calc(100vw - 20px); }
  }

  @media (max-width: 991px) {
    /* Breadcrumb bar is hidden below this width, so the dropdown only
       needs to clear the 64px navbar, not the breadcrumb too. */
    .crm-dropdown { top: 70px; }
    .crm-notif-dropdown { top: 70px; }
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
    'lead_assignment.index' => ['Administration', 'Lead Assignment'],
    'deal_assignment.index' => ['Administration', 'Deal Assignment'],
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
      <div class="crm-search-wrapper" id="globalSearchWrapper">
        <i class="fa fa-search crm-search-icon"></i>
        <input type="text" class="crm-search-input" id="globalSearchInput" placeholder="Search leads, deals, companies…" autocomplete="off">
        <button type="button" class="crm-search-clear" id="globalSearchClear" title="Clear" aria-label="Clear search"><i class="fa fa-times"></i></button>
        <div class="crm-search-results" id="globalSearchResults"></div>
      </div>
    </div>

    <div class="crm-right">
      @if(session('impersonator_id'))
        <form method="post" action="{{ route('impersonation.stop') }}">@csrf<button class="btn btn-sm btn-warning">End support session</button></form>
      @endif
      <button type="button" class="icon-btn" id="themeToggleBtn" title="Toggle dark / light mode" aria-label="Toggle dark / light mode">
        <i class="fa fa-moon-o" id="themeToggleIcon"></i>
      </button>
      <button type="button" class="icon-btn notification-link" id="notifToggle" title="Notifications" aria-label="Notifications">
        <i class="fa fa-bell-o"></i>
        @php $unreadNotificationCount = Auth::guard('web')->user()->unreadNotifications()->count(); @endphp
        @if($unreadNotificationCount)<span class="notification-count" id="notifCount">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>@endif
      </button>

      <div class="crm-notif-dropdown" id="notifDropdown">
        <div class="crm-notif-head">
          <strong>Notifications</strong>
          <button type="button" id="notifReadAll">Mark all read</button>
        </div>
        <div class="crm-notif-list" id="notifList">
          <div class="crm-notif-empty">Loading&hellip;</div>
        </div>
        <div class="crm-notif-foot"><a href="{{ route('notifications.index') }}">View all notifications</a></div>
      </div>
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
  // Applied synchronously (not inside DOMContentLoaded) so everything the
  // parser hasn't reached yet (sidebar, content-wrapper) picks up the right
  // theme on first paint instead of flashing light-then-dark. Only the
  // header markup already parsed above this point can't benefit — there's
  // no shared <head> across pages in this app to run this earlier.
  (function() {
    const stored = localStorage.getItem('crm-theme');
    if (stored === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  })();

  document.addEventListener("DOMContentLoaded", function() {
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const themeToggleIcon = document.getElementById('themeToggleIcon');

    function syncThemeIcon() {
      const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      themeToggleIcon.className = isDark ? 'fa fa-sun-o' : 'fa fa-moon-o';
    }
    syncThemeIcon();

    themeToggleBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      if (isDark) {
        document.documentElement.removeAttribute('data-theme');
        localStorage.setItem('crm-theme', 'light');
      } else {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('crm-theme', 'dark');
      }
      syncThemeIcon();
      // Lets pages with Chart.js canvases (dashboard, reports) re-draw with
      // theme-appropriate text/gridline colors — canvas content can't be
      // themed with CSS alone.
      document.dispatchEvent(new CustomEvent('crm-theme-changed', { detail: { dark: !isDark } }));
    });

    const profile = document.getElementById("profileToggle");
    const dropdown = document.getElementById("profileDropdown");

    profile.addEventListener("click", function(e) {
      e.stopPropagation();
      const notifPanel = document.getElementById("notifDropdown");
      if (notifPanel) notifPanel.style.display = "none";
      dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    });

    dropdown.addEventListener("click", function(e) {
      e.stopPropagation();
    });

    document.addEventListener("click", function() {
      dropdown.style.display = "none";
    });

    // --- Notification bell dropdown ---
    const notifToggle = document.getElementById("notifToggle");
    const notifDropdown = document.getElementById("notifDropdown");
    const notifList = document.getElementById("notifList");
    const notifReadAll = document.getElementById("notifReadAll");
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    function escapeHtml(value) {
      const div = document.createElement("div");
      div.textContent = value ?? "";
      return div.innerHTML;
    }

    function timeAgo(dateStr) {
      const diffMs = Date.now() - new Date(dateStr).getTime();
      const mins = Math.round(diffMs / 60000);
      if (mins < 1) return "just now";
      if (mins < 60) return mins + "m ago";
      const hours = Math.round(mins / 60);
      if (hours < 24) return hours + "h ago";
      const days = Math.round(hours / 24);
      if (days < 7) return days + "d ago";
      return new Date(dateStr).toLocaleDateString();
    }

    function setNotifBadge(count) {
      let badge = document.getElementById("notifCount");
      if (count > 0) {
        if (!badge) {
          badge = document.createElement("span");
          badge.className = "notification-count";
          badge.id = "notifCount";
          notifToggle.appendChild(badge);
        }
        badge.textContent = count > 99 ? "99+" : String(count);
      } else if (badge) {
        badge.remove();
      }
    }

    function renderNotifications(items) {
      if (!items.length) {
        notifList.innerHTML = '<div class="crm-notif-empty"><i class="fa fa-bell-slash-o" style="font-size:22px;"></i><div style="margin-top:8px;">Nothing yet — reminders and updates will show up here.</div></div>';
        return;
      }
      notifList.innerHTML = items.slice(0, 8).map(function (n) {
        const isTask = !!n.data.task_id;
        const iconClass = isTask ? "type-task" : "type-lead";
        const icon = isTask ? "fa-check-square-o" : "fa-phone";
        const unread = !n.read_at;
        return '<a href="#" class="crm-notif-item' + (unread ? ' is-unread' : '') + '" data-id="' + n.id + '" data-url="' + escapeHtml(n.data.url || '') + '">'
          + '<div class="crm-notif-icon ' + iconClass + '"><i class="fa ' + icon + '"></i></div>'
          + '<div class="crm-notif-body">'
          + '<div class="crm-notif-title">' + (unread ? '<span class="crm-notif-dot"></span>' : '') + escapeHtml(n.data.title || 'Notification') + '</div>'
          + '<div class="crm-notif-message">' + escapeHtml(n.data.message || '') + '</div>'
          + '<div class="crm-notif-time">' + timeAgo(n.created_at) + '</div>'
          + '</div></a>';
      }).join("");
    }

    function loadNotifications() {
      fetch("{{ route('notifications.data') }}")
        .then(function (r) { return r.json(); })
        .then(function (r) {
          renderNotifications(r.data || []);
          setNotifBadge(r.unread || 0);
        })
        .catch(function () {
          notifList.innerHTML = '<div class="crm-notif-empty">Could not load notifications.</div>';
        });
    }

    if (notifToggle) {
      notifToggle.addEventListener("click", function (e) {
        e.stopPropagation();
        dropdown.style.display = "none";
        const opening = notifDropdown.style.display !== "block";
        notifDropdown.style.display = opening ? "block" : "none";
        if (opening) loadNotifications();
      });

      notifDropdown.addEventListener("click", function (e) { e.stopPropagation(); });

      notifDropdown.addEventListener("click", function (e) {
        const item = e.target.closest(".crm-notif-item");
        if (!item) return;
        e.preventDefault();
        const id = item.dataset.id;
        const url = item.dataset.url || "{{ route('notifications.index') }}";
        fetch("{{ url('/notifications') }}/" + id + "/read", {
          method: "POST",
          headers: { "X-CSRF-TOKEN": csrfToken },
        }).finally(function () { window.location.href = url; });
      });

      notifReadAll.addEventListener("click", function (e) {
        e.stopPropagation();
        fetch("{{ route('notifications.read_all') }}", {
          method: "POST",
          headers: { "X-CSRF-TOKEN": csrfToken },
        }).then(loadNotifications);
      });

      document.addEventListener("click", function () {
        notifDropdown.style.display = "none";
      });

      // Keep the badge fresh without a full reload — cheap enough (count
      // only) to poll while the tab is open.
      setInterval(function () {
        fetch("{{ route('notifications.data') }}")
          .then(function (r) { return r.json(); })
          .then(function (r) { setNotifBadge(r.unread || 0); })
          .catch(function () {});
      }, 60000);
    }

    // --- Universal search ---
    const searchWrapper = document.getElementById("globalSearchWrapper");
    const searchInput = document.getElementById("globalSearchInput");
    const searchClear = document.getElementById("globalSearchClear");
    const searchResults = document.getElementById("globalSearchResults");

    if (searchWrapper && searchInput) {
      let searchTimer = null;
      let searchSeq = 0;

      function closeSearchResults() {
        searchResults.classList.remove("is-open");
      }

      function renderSearchResults(items, term) {
        if (!items.length) {
          searchResults.innerHTML = '<div class="crm-search-empty">No matches for "' + escapeHtml(term) + '"</div>';
          searchResults.classList.add("is-open");
          return;
        }

        const groups = {};
        items.forEach(function (item) {
          (groups[item.type] = groups[item.type] || []).push(item);
        });

        searchResults.innerHTML = Object.keys(groups).map(function (type) {
          return '<div class="crm-search-group-label">' + escapeHtml(type) + (groups[type].length > 1 ? 's' : '') + '</div>'
            + groups[type].map(function (item) {
              return '<a href="' + item.url + '" class="crm-search-item">'
                + '<div class="crm-search-item-icon"><i class="fa ' + item.icon + '"></i></div>'
                + '<div><div class="crm-search-item-title">' + escapeHtml(item.title || '') + '</div>'
                + (item.subtitle ? '<div class="crm-search-item-subtitle">' + escapeHtml(item.subtitle) + '</div>' : '')
                + '</div></a>';
            }).join("");
        }).join("");
        searchResults.classList.add("is-open");
      }

      function runSearch(term) {
        const seq = ++searchSeq;
        fetch("{{ route('search') }}?q=" + encodeURIComponent(term))
          .then(function (r) { return r.json(); })
          .then(function (r) {
            if (seq !== searchSeq) return; // a newer keystroke already fired
            renderSearchResults(r.results || [], term);
          })
          .catch(function () {
            if (seq !== searchSeq) return;
            searchResults.innerHTML = '<div class="crm-search-empty">Search failed — try again.</div>';
            searchResults.classList.add("is-open");
          });
      }

      searchInput.addEventListener("input", function () {
        const term = searchInput.value.trim();
        searchWrapper.classList.toggle("has-value", term.length > 0);
        clearTimeout(searchTimer);

        if (term.length < 2) {
          closeSearchResults();
          return;
        }

        searchTimer = setTimeout(function () { runSearch(term); }, 300);
      });

      searchInput.addEventListener("focus", function () {
        if (searchInput.value.trim().length >= 2 && searchResults.innerHTML) {
          searchResults.classList.add("is-open");
        }
      });

      searchInput.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
          searchInput.value = "";
          searchWrapper.classList.remove("has-value");
          closeSearchResults();
          searchInput.blur();
        }
      });

      searchClear.addEventListener("click", function () {
        searchInput.value = "";
        searchWrapper.classList.remove("has-value");
        closeSearchResults();
        searchInput.focus();
      });

      searchWrapper.addEventListener("click", function (e) { e.stopPropagation(); });

      document.addEventListener("click", closeSearchResults);
    }
  });
</script>
