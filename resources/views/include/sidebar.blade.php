<style>
    :root { --sidebar-w: 244px; }

    /* Pure spacer: reserves --sidebar-w of horizontal space in the flex row
       so .content-wrapper starts at the right x-offset, since the actual
       visible nav below is position:fixed and therefore out of flow. No
       background/content of its own. */
    .sidebar-rail {
        width: var(--sidebar-w);
        min-width: var(--sidebar-w);
        transition: width 0.2s ease, min-width 0.2s ease;
    }

    /* position:fixed (not sticky) so the sidebar is completely immune to
       page-content scrolling — it never moves except via its own internal
       overflow-y:auto scrollbar. Fixed also means it always exactly covers
       top:98px to the bottom of the current viewport, so there's no "runs
       out before the page ends" gap sticky+capped-height could hit on long
       pages — no separate full-height rail trick needed for that anymore. */
    .sidebar {
        position: fixed;
        top: 98px;
        left: 0;
        width: var(--sidebar-w);
        height: calc(100vh - 98px);
        background: linear-gradient(180deg, #0c7bfe, #01bdff);
        /* Generous bottom padding, not just the top's 12px: without it the
           last nav item sits flush against the very bottom pixel row of
           the viewport — exactly where an auto-hide OS taskbar overlaps
           when it pops up, making that item unreachable/unclickable. This
           keeps it scrollable to a comfortable position clear of that. */
        padding: 12px 8px 64px;
        font-family: 'Poppins', sans-serif;
        overflow-y: auto;
        overscroll-behavior: contain;
        /* Firefox thin scrollbar */
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.35) transparent;
    }

    /* Modern, minimal scrollbar — thin, subtle, rounded, unobtrusive. */
    .sidebar::-webkit-scrollbar { width: 5px; }
    .sidebar::-webkit-scrollbar-track { background: transparent; }
    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.28);
        border-radius: 999px;
    }
    .sidebar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.5); }

    /* The collapse toggle itself now lives in include/header.blade.php,
       anchored to .crm-brand-wrapper (whose width never changes with
       collapse state), not here — anchoring it to the nav meant it
       tracked --sidebar-w and visibly jumped left/right between the
       expanded (244px) and collapsed (72px) offsets. */

    .sidebar-search {
        margin-bottom: 8px;
    }
    .sidebar-search input {
        width: 100%;
        border: none;
        border-radius: 8px;
        padding: 6px 10px 6px 26px;
        font-size: 12px;
        background: rgba(255, 255, 255, 0.14);
        color: #fff;
        line-height: 1.4;
    }
    .sidebar-search input::placeholder { color: rgba(255,255,255,0.75); }
    .sidebar-search input:focus { outline: none; background: rgba(255, 255, 255, 0.22); }
    .sidebar-search-wrap { position: relative; }
    .sidebar-search-wrap i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255, 255, 255, 0.8);
        font-size: 11px;
        pointer-events: none;
    }
    .sidebar-no-results { display: none; color: rgba(255,255,255,0.75); font-size: 12px; padding: 6px 10px; }

    .nav-section { margin-top: 16px; }
    .nav-section:first-of-type { margin-top: 2px; }
    .nav-section-label {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.62);
        padding: 0 10px 4px;
    }
    .nav-section.has-active .nav-section-label { color: #ffffff; }

    .nav-sidebar-menu { list-style: none; padding: 0; margin: 0; }
    .nav-sidebar-menu li { margin-bottom: 5px; }
    .nav-sidebar-menu li:last-child { margin-bottom: 0; }

    .nav-sidebar-menu .nav-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 14px;
        border-radius: 7px;
        font-size: 13.5px;
        line-height: 1.3;
        color: rgba(255, 255, 255, 0.92);
        text-decoration: none;
        transition: background 0.15s ease, color 0.15s ease;
        white-space: nowrap;
    }

    .nav-sidebar-menu .nav-link:hover { background: rgba(255, 255, 255, 0.1); color: #fff; }
    .nav-sidebar-menu .nav-link:focus-visible { outline: 2px solid #fff; outline-offset: -2px; }

    /* Modern SaaS active state: one clean flat highlight, no gradient glow. */
    .nav-sidebar-menu .nav-link.active {
        background: rgba(255, 255, 255, 0.18);
        color: #ffffff;
        font-weight: 600;
    }

    .nav-sidebar-menu .nav-link.active i { color: #ffffff; }
    .nav-sidebar-menu i { font-size: 15px; width: 18px; text-align: center; flex-shrink: 0; }
    .nav-sidebar-menu .nav-link span { overflow: hidden; text-overflow: ellipsis; }

    /* Collapsed (icon-only) state */
    .sidebar.collapsed { padding-left: 6px; padding-right: 6px; }
    .sidebar.collapsed .nav-section-label,
    .sidebar.collapsed .nav-sidebar-menu .nav-link span,
    .sidebar.collapsed .sidebar-search { display: none; }
    .sidebar.collapsed .nav-sidebar-menu .nav-link { justify-content: center; padding: 10px 8px; }
    .sidebar.collapsed .nav-sidebar-menu i { font-size: 16px; }

    /* In collapsed mode, wayfinding falls back to the native title-attribute
       tooltip (set on every .nav-link) rather than a custom CSS tooltip,
       since a custom one built with position:absolute gets clipped by this
       sidebar's own overflow-y:auto (which forces overflow-x to clip too). */

    /* The vendor theme's own `.sidebar .nav` / `.sidebar .nav:not(.sub-menu)`
       rules (public/css/vertical-layout-light/style.css) set
       margin-top:1.45rem and margin-bottom:60px — both are MORE specific
       (two-to-three class selectors) than a plain `.nav-sidebar-menu{margin:0}`
       override, so they silently won regardless of source order. This is
       the actual root cause of the huge, inconsistent gaps between sidebar
       sections that kept surviving every padding/spacing pass — matching
       the vendor's own selector exactly (same specificity, later in the
       document) is what finally overrides it for real. */
    .sidebar .nav:not(.sub-menu) { margin: 0; }

    @media (max-width: 991px) {
        /* Below this width the sidebar becomes a fixed overlay drawer, so
           the full-height rail column is irrelevant — collapse it out of
           the layout entirely (display:contents keeps #sidebar itself
           rendering as if it were still a direct child) and let the nav
           carry its own background again since it no longer shows through
           from the rail underneath it. */
        .sidebar-rail { display: contents; }
        .sidebar {
            position: fixed;
            top: 64px;
            left: -244px;
            height: calc(100vh - 64px);
            width: 244px;
            min-width: 244px;
            background: linear-gradient(180deg, #0c7bfe, #01bdff);
            transition: left 0.3s ease;
            z-index: 1000;
        }
        .sidebar.show { left: 0; }
        .sidebar.collapsed .nav-section-label,
        .sidebar.collapsed .nav-sidebar-menu .nav-link span,
        .sidebar.collapsed .sidebar-search { display: block; }
    }

    @media (max-width: 768px) {
        .sidebar { top: 56px; height: calc(100vh - 56px); }
    }
</style>

<div class="sidebar-rail">
<nav class="sidebar" id="sidebar">

    <div class="sidebar-search">
        <div class="sidebar-search-wrap">
            <i class="fa fa-search"></i>
            <input type="text" id="sidebarSearch" placeholder="Search pages…" autocomplete="off">
        </div>
    </div>

    <div class="sidebar-nav" style="padding: 4px 0px;">

        <ul class="nav nav-sidebar-menu">
        <li class="mb-1" data-nav-label="Dashboard">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                href="{{ route('dashboard') }}" title="Dashboard">
                <i class="fa fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>
        </ul>

        @can('masters.view')
        <div class="nav-section {{ request()->routeIs('master_data.*') ? 'has-active' : '' }}">
            <div class="nav-section-label">Master Data</div>
            <ul class="nav nav-sidebar-menu">
                <li class="mb-1" data-nav-label="Master Data">
                    <a class="nav-link {{ request()->routeIs('master_data.*') ? 'active' : '' }}" href="{{ route('master_data.index') }}" title="Master Data">
                        <i class="fa fa-list-alt"></i><span>Master Data</span>
                    </a>
                </li>
            </ul>
        </div>
        @endcan

        @canany(['templates.view', 'campaigns.view'])
        <div class="nav-section {{ request()->routeIs(['templates.*','campaigns.*']) ? 'has-active' : '' }}">
            <div class="nav-section-label">Marketing</div>
            <ul class="nav nav-sidebar-menu">
                @can('templates.view')
                <li class="mb-1" data-nav-label="Email Templates">
                    <a class="nav-link {{ request()->routeIs('templates.*') ? 'active' : '' }}" href="{{ route('templates.index') }}" title="Email Templates">
                        <i class="fa fa-file-text-o"></i><span>Email Templates</span>
                    </a>
                </li>
                @endcan
                @can('campaigns.view')
                <li class="mb-1" data-nav-label="Email Campaigns">
                    <a class="nav-link {{ request()->routeIs('campaigns.*') ? 'active' : '' }}" href="{{ route('campaigns.index') }}" title="Email Campaigns">
                        <i class="fa fa-paper-plane"></i><span>Email Campaigns</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        @endcanany

        @canany(['leads.view', 'deals.view', 'companies.view', 'contacts.view'])
        <div class="nav-section {{ request()->routeIs(['leads.*','deals.*','companies.*','contacts.*']) ? 'has-active' : '' }}">
            <div class="nav-section-label">Sales</div>
            <ul class="nav nav-sidebar-menu">
                @can('leads.view')
                <li class="mb-1" data-nav-label="Leads">
                    <a class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}" href="{{ route('leads.index') }}" title="Leads">
                        <i class="fa fa-bullseye"></i><span>Leads</span>
                    </a>
                </li>
                @endcan
                @can('deals.view')
                <li class="mb-1" data-nav-label="Deals">
                    <a class="nav-link {{ request()->routeIs('deals.*') ? 'active' : '' }}" href="{{ route('deals.list') }}" title="Deals">
                        <i class="fa fa-briefcase"></i><span>Deals</span>
                    </a>
                </li>
                @endcan
                @can('companies.view')
                <li class="mb-1" data-nav-label="Companies">
                    <a class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}" title="Companies">
                        <i class="fa fa-building-o"></i><span>Companies</span>
                    </a>
                </li>
                @endcan
                @can('contacts.view')
                <li class="mb-1" data-nav-label="Contacts">
                    <a class="nav-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}" href="{{ route('contacts.index') }}" title="Contacts">
                        <i class="fa fa-address-book"></i><span>Contacts</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        @endcanany

        @can('orders.view')
        <div class="nav-section {{ request()->routeIs(['orders.*','projects.*','invoice.*','quotation.*']) ? 'has-active' : '' }}">
            <div class="nav-section-label">Delivery &amp; Billing</div>
            <ul class="nav nav-sidebar-menu">
                <li class="mb-1" data-nav-label="Orders">
                    <a class="nav-link {{ request()->routeIs(['orders.*','invoice.*','quotation.*']) ? 'active' : '' }}" href="{{ route('orders.index') }}" title="Orders">
                        <i class="fa fa-shopping-cart"></i><span>Orders</span>
                    </a>
                </li>
                <li class="mb-1" data-nav-label="Projects">
                    <a class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}" href="{{ route('projects.index') }}" title="Projects">
                        <i class="fa fa-check-circle"></i><span>Projects</span>
                    </a>
                </li>
            </ul>
        </div>
        @endcan

        @canany(['tasks.view', 'calendar.view'])
        <div class="nav-section {{ request()->routeIs(['tasks.*','calendar.*']) ? 'has-active' : '' }}">
            <div class="nav-section-label">Activity</div>
            <ul class="nav nav-sidebar-menu">
                @can('tasks.view')
                <li class="mb-1" data-nav-label="Tasks &amp; Reminders">
                    <a class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}" href="{{ route('tasks.index') }}" title="Tasks">
                        <i class="fa fa-check-square-o"></i><span>Tasks &amp; Reminders</span>
                    </a>
                </li>
                @endcan
                @can('calendar.view')
                <li class="mb-1" data-nav-label="Calendar">
                    <a class="nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}" href="{{ route('calendar.index') }}" title="Calendar">
                        <i class="fa fa-calendar"></i><span>Calendar</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        @endcanany

        @canany(['reports.view', 'audit.view'])
        <div class="nav-section {{ request()->routeIs(['reports.*','audit.*']) ? 'has-active' : '' }}">
            <div class="nav-section-label">Insights</div>
            <ul class="nav nav-sidebar-menu">
                @can('reports.view')
                <li class="mb-1" data-nav-label="Reports & Analytics">
                    <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}" title="Reports">
                        <i class="fa fa-line-chart"></i><span>Reports &amp; Analytics</span>
                    </a>
                </li>
                @endcan
                @can('audit.view')
                <li class="mb-1" data-nav-label="Audit Log">
                    <a class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}" title="Audit Log">
                        <i class="fa fa-history"></i><span>Audit Log</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        @endcanany

        @canany(['users.view', 'roles.view', 'company.view', 'masters.view', 'deals.manage-settings', 'company.manage-settings'])
        <div class="nav-section {{ request()->routeIs(['users.*','roles.*','company.*','pipelines.*','stages.*','settings.mail.*']) ? 'has-active' : '' }}">
            <div class="nav-section-label">Administration</div>
            <ul class="nav nav-sidebar-menu">
                @can('users.view')
                <li class="mb-1" data-nav-label="Users">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}" title="Users">
                        <i class="fa fa-users"></i><span>Users</span>
                    </a>
                </li>
                @endcan
                @can('roles.view')
                <li class="mb-1" data-nav-label="Roles &amp; Permissions">
                    <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}" title="Roles &amp; Permissions">
                        <i class="fa fa-lock"></i><span>Roles &amp; Permissions</span>
                    </a>
                </li>
                @endcan
                @can('company.view')
                <li class="mb-1" data-nav-label="Organization Profile">
                    <a class="nav-link {{ request()->routeIs('company.*') ? 'active' : '' }}" href="{{ route('company.show') }}" title="Organization Profile">
                        <i class="fa fa-building"></i><span>Organization Profile</span>
                    </a>
                </li>
                @endcan
                @can('deals.manage-settings')
                <li class="mb-1" data-nav-label="Pipelines">
                    <a class="nav-link {{ request()->routeIs(['pipelines.*','stages.*']) ? 'active' : '' }}" href="{{ route('pipelines.index') }}" title="Pipelines">
                        <i class="fa fa-random"></i><span>Pipelines</span>
                    </a>
                </li>
                @endcan
                @can('company.manage-settings')
                <li class="mb-1" data-nav-label="Mail Settings">
                    <a class="nav-link {{ request()->routeIs('settings.mail.*') ? 'active' : '' }}" href="{{ route('settings.mail.edit') }}" title="Mail Settings">
                        <i class="fa fa-envelope"></i><span>Mail Settings</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        @endcanany

    </div>

    <div class="sidebar-no-results" id="sidebarNoResults">No matching pages.</div>
</nav>
</div>

<script>
    (function () {
        var sidebar = document.getElementById("sidebar");
        if (!sidebar) return;

        // --sidebar-w lives on :root (not scoped to #sidebar) so the header's
        // brand column can stay the same width as the sidebar, collapsed or
        // not, without a separate sync mechanism.
        function setSidebarWidth(collapsed) {
            document.documentElement.style.setProperty("--sidebar-w", collapsed ? "72px" : "244px");
            document.documentElement.classList.toggle("sidebar-collapsed", collapsed);
        }

        // Apply the saved collapsed preference as early as possible to avoid a flash.
        // The toggle button itself lives in include/header.blade.php (included
        // earlier in the page than this file), so it's already in the DOM here.
        var startCollapsed = window.innerWidth > 991 && localStorage.getItem("crm_sidebar_collapsed") === "1";
        if (startCollapsed) {
            sidebar.classList.add("collapsed");
            setSidebarWidth(true);
            var earlyBtn = document.getElementById("sidebarCollapseBtn");
            if (earlyBtn) earlyBtn.classList.add("is-collapsed");
        }

        document.addEventListener("DOMContentLoaded", function () {
            var toggleBtn = document.querySelector(".crm-toggle");
            var collapseBtn = document.getElementById("sidebarCollapseBtn");

            if (toggleBtn) {
                toggleBtn.addEventListener("click", function (e) {
                    e.preventDefault();
                    sidebar.classList.toggle("show");
                });
            }

            if (collapseBtn) {
                collapseBtn.addEventListener("click", function () {
                    var collapsed = sidebar.classList.toggle("collapsed");
                    setSidebarWidth(collapsed);
                    collapseBtn.classList.toggle("is-collapsed", collapsed);
                    localStorage.setItem("crm_sidebar_collapsed", collapsed ? "1" : "0");
                });
            }

            // Sidebar search: filters items by label, hides empty sections.
            var searchInput = document.getElementById("sidebarSearch");
            var noResults = document.getElementById("sidebarNoResults");
            if (searchInput) {
                searchInput.addEventListener("input", function () {
                    var term = this.value.trim().toLowerCase();
                    var anyVisible = false;
                    document.querySelectorAll("#sidebar li[data-nav-label]").forEach(function (li) {
                        var label = li.getAttribute("data-nav-label").toLowerCase();
                        var match = term === "" || label.indexOf(term) !== -1;
                        li.style.display = match ? "" : "none";
                        if (match) anyVisible = true;
                    });
                    document.querySelectorAll("#sidebar .nav-section").forEach(function (section) {
                        var hasVisible = section.querySelector("li[data-nav-label]:not([style*='display: none'])");
                        section.style.display = (term === "" || hasVisible) ? "" : "none";
                    });
                    if (noResults) noResults.style.display = (term !== "" && !anyVisible) ? "block" : "none";
                });
            }
        });
    })();
</script>
