<style>
    .sidebar {
        min-width: 260px;
        min-height: 100vh;
        background: linear-gradient(180deg, #0c7bfe, #01bdff);
        padding: 18px 12px;
        font-family: 'Poppins', sans-serif;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .sidebar-profile {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 10px;
        margin-bottom: 22px;
        background: rgba(255, 255, 255, 0.06);
        border-radius: 14px;
    }

    .sidebar-profile img {
        width: 44px;
        height: 44px;
        border-radius: 50%;
    }

    .profile-info .name {
        font-size: 15px;
        font-weight: 600;
        color: #fff;
    }

    .profile-info .role {
        font-size: 12px;
        color: #ffffff;
    }

    .nav-sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .nav-sidebar-menu .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 14px;
        border-radius: 10px;
        font-size: 14px;
        color: #ffffff;
        text-decoration: none;
        transition: all 0.25s ease;
    }

    .nav-sidebar-menu .nav-link:hover {
        background: rgba(255, 255, 255, 0.07);
        color: #fff;
    }

    .nav-sidebar-menu .nav-link.active {
        background: linear-gradient(135deg, #1da1ff, #0a6cff);
        color: #ffffff;
        font-weight: 600;
        box-shadow:
            0 10px 24px rgba(13, 120, 255, 0.45),
            inset 0 0 0 1px rgba(255, 255, 255, 0.18);
    }

    .nav-sidebar-menu .nav-link.active i {
        color: #ffffff;
    }

    .nav-sidebar-menu i {
        font-size: 16px;
    }

    .dropdown-left {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .dropdown-arrow {
        transition: 0.3s;
    }

    .sidebar-submenu {
        list-style: none;
        padding-left: 16px;
        margin-top: 6px;
        display: none;
    }

    .sidebar-submenu .nav-link {
        font-size: 13px;
        padding: 9px 14px;
    }

    .sidebar-dropdown.open .sidebar-submenu {
        display: block;
    }

    .sidebar-dropdown.open .dropdown-arrow {
        transform: rotate(180deg);
    }

    .logout-btn {
        width: 100%;
        border: none;
        background: linear-gradient(135deg, #1da1ff, #0a6cff58);
        color: #faf5f5;
        padding: 11px 14px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        cursor: pointer;
        transition: 0.25s ease;
    }

    .logout-btn:hover {
        background: rgba(255, 0, 0, 0.77);
        color: #ffffff;
    }

    .logout-btn:hover i {
        color: #ffffff;
    }

    .sidebar .nav:not(.sub-menu) {
        margin-left: 0.5rem;
        margin-right: 0.5rem;
    }

    @media (max-width: 991px) {
        .sidebar {
            position: fixed;
            top: 64px;
            left: -260px;
            height: calc(100vh - 64px);
            transition: left 0.3s ease;
            z-index: 1000;
        }

        .sidebar.show {
            left: 0;
        }
    }
</style>

<nav class="sidebar" id="sidebar">

    @php $me = Auth::guard('web')->user(); @endphp

    <div class="sidebar-profile">
        <img src="{{ asset('images/user_1.png') }}">
        <div class="profile-info">
            <div class="name">{{ $me->name }}</div>
            <div class="role">{{ $me->getRoleNames()->first() }}</div>
        </div>
    </div>

    <ul class="nav nav-sidebar-menu" style="padding: 4px 0px;">

        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                href="{{ route('dashboard') }}">
                <i class="fa fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>

        @can('leads.view')
        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}"
                href="{{ route('leads.index') }}">
                <i class="fa fa-bullseye"></i>
                <span>Leads</span>
            </a>
        </li>
        @endcan

        @can('orders.view')
        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs(['admin.sales_orders', 'user.order_management']) ? 'active' : '' }}"
                href="{{ $me->hasElevatedAccess() ? route('admin.sales_orders') : route('user.order_management') }}">
                <i class="fa fa-shopping-cart"></i>
                <span>Orders</span>
            </a>
        </li>
        @endcan

        @if($me->hasElevatedAccess())
            @can('orders.view')
            <li class="mb-1">
                <a class="nav-link {{ request()->routeIs('admin.project_details') ? 'active' : '' }}"
                    href="{{ route('admin.project_details') }}">
                    <i class="fa fa-check-circle"></i>
                    <span>Projects Details</span>
                </a>
            </li>
            @endcan

            @can('leads.view')
            <li class="mb-1">
                <a class="nav-link {{ request()->routeIs('admin.track_lead') ? 'active' : '' }}"
                    href="{{ route('admin.track_lead') }}">
                    <i class="fa fa-tasks"></i>
                    <span>Track Lead</span>
                </a>
            </li>
            @endcan
        @endif

        @can('users.view')
        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('admin.user_profile') ? 'active' : '' }}"
                href="{{ route('admin.user_profile') }}">
                <i class="fa fa-users"></i>
                <span>User List</span>
            </a>
        </li>
        @endcan

        @canany(['company.view', 'masters.view'])
        <li class="mb-1 sidebar-dropdown {{ request()->routeIs(['admin.company_details', 'admin.master_data.*']) ? 'open' : '' }}">
            <a href="javascript:void(0)" class="nav-link dropdown-left">
                <i class="fa fa-cog"></i>
                <span>Settings</span>
                <i class="fa fa-angle-down ml-auto dropdown-arrow"></i>
            </a>

            <ul class="sidebar-submenu">
                @can('company.view')
                <li>
                    <a class="nav-link {{ request()->routeIs('admin.company_details') ? 'active' : '' }}"
                        href="{{ route('admin.company_details') }}">
                        <i class="fa fa-building"></i>
                        Company Details
                    </a>
                </li>
                @endcan
                @can('masters.view')
                <li>
                    <a class="nav-link {{ request()->routeIs('admin.master_data.*') ? 'active' : '' }}"
                        href="{{ route('admin.master_data.index') }}">
                        <i class="fa fa-list-alt"></i>
                        Master Data
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany

        @can('contacts.view')
        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('admin.customer_contact') ? 'active' : '' }}"
                href="{{ route('admin.customer_contact') }}">
                <i class="fa fa-address-book"></i>
                <span>Contacts</span>
            </a>
        </li>
        @endcan

        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('user.profile') ? 'active' : '' }}"
                href="{{ route('user.profile') }}">
                <i class="fa fa-user"></i>
                <span>My Profile</span>
            </a>
        </li>

        <li class="mt-2">
            <form method="post" action="{{ route('admin.logout') }}">
                @csrf
                <button class="logout-btn">
                    <i class="fa fa-sign-out"></i> Logout
                </button>
            </form>
        </li>

    </ul>
</nav>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggleBtn = document.querySelector(".crm-toggle");
        const sidebar = document.getElementById("sidebar");

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener("click", function(e) {
                e.preventDefault();
                sidebar.classList.toggle("show");
            });
        }

        document.querySelectorAll(".sidebar-dropdown > .nav-link").forEach(link => {
            link.addEventListener("click", function(e) {
                e.preventDefault();
                const parent = this.closest(".sidebar-dropdown");
                document.querySelectorAll(".sidebar-dropdown.open").forEach(item => {
                    if (item !== parent) {
                        item.classList.remove("open");
                    }
                });
                parent.classList.toggle("open");
            });
        });
    });
</script>
