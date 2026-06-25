<style>
    /* ============ SIDEBAR WRAPPER ============ */
    .sidebar {
        min-width: 260px;
        min-height: 100vh;
        background: linear-gradient(180deg, #0c7bfe, #01bdff);
        padding: 18px 12px;
        font-family: 'Poppins', sans-serif;
    }

    /* ============ PROFILE CARD ============ */
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

    /* ============ MENU ============ */
    .nav-sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    /* NORMAL LINKS */
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

    /* HOVER */
    .nav-sidebar-menu .nav-link:hover {
        background: rgba(255, 255, 255, 0.07);
        color: #fff;
    }

    /* ACTIVE = ONLY BUTTON STYLE */
    .nav-sidebar-menu .nav-link.active {
        background: linear-gradient(135deg, #1da1ff, #0a6cff);
        color: #ffffff;
        font-weight: 600;
        box-shadow:
            0 10px 24px rgba(13, 120, 255, 0.45),
            inset 0 0 0 1px rgba(255, 255, 255, 0.18);
    }

    /* icon color also white */
    .nav-sidebar-menu .nav-link.active i {
        color: #ffffff;
    }

    /* ICON */
    .nav-sidebar-menu i {
        font-size: 16px;
    }

    /* ============ DROPDOWN ============ */
    .dropdown-left {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .dropdown-arrow {
        transition: 0.3s;
    }

    /* SUBMENU */
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

    /* OPEN */
    .sidebar-dropdown.open .sidebar-submenu {
        display: block;
    }

    .sidebar-dropdown.open .dropdown-arrow {
        transform: rotate(180deg);
    }

    /* ============ LOGOUT ============ */
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
            /* header height */
            left: -260px;
            height: calc(100vh - 64px);
            transition: left 0.3s ease;
            z-index: 1000;
        }

        .sidebar.show {
            left: 0;
        }
    }
    

    .sidebar {
        overflow-y: auto;
        /* ✅ ENABLE SCROLL */
        overscroll-behavior: contain;
    }
</style>

<nav class="sidebar" id="sidebar">

    <div class="sidebar-profile">
        <!-- <img src="{{ asset('images/profile_img.jpg') }}"> -->
        <img src="https://techwebmantra.com/crm/public/images/user_1.png">
        <div class="profile-info">
            <div class="name">Admin</div>
            <div class="role" style="font-size:12px;">Active</div>
        </div>
    </div>

    <ul class="nav nav-sidebar-menu" style="padding: 4px 0px;">

        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                href="{{ route('admin.dashboard') }}">
                <i class="fa fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('admin.lead') ? 'active' : '' }}"
                href="{{ route('admin.lead') }}">
                <i class="fa fa-bullseye"></i>
                <span>Leads</span>
            </a>
        </li>
        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('admin.sales_orders') ? 'active' : '' }}"
                href="{{ route('admin.sales_orders') }}">
                <i class="fa fa-shopping-cart"></i>
                <span>Sales Orders</span>
            </a>
        </li>
        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('admin.project_details') ? 'active' : '' }}"
                href="{{ route('admin.project_details') }}">
                <i class="fa fa-check-circle"></i>
                <span>Projects Details</span>
            </a>
        </li>
        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('admin.track_lead') ? 'active' : '' }}"
                href="{{ route('admin.track_lead') }}">
                <i class="fa fa-tasks"></i>
                <span>Track Lead</span>
            </a>
        </li>

        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('admin.user_profile') ? 'active' : '' }}"
                href="{{ route('admin.user_profile') }}">
                <i class="fa fa-users"></i>
                <span>User List</span>
            </a>
        </li>
        <li class="mb-1 sidebar-dropdown {{ request()->routeIs('admin.company_details') ? 'open' : '' }}">
            <a href="javascript:void(0)" class="nav-link dropdown-left">
                <i class="fa fa-cog"></i>
                <span>Settings</span>
                <i class="fa fa-angle-down ml-auto dropdown-arrow"></i>
            </a>

            <ul class="sidebar-submenu">
                <li>
                    <a class="nav-link {{ request()->routeIs('admin.company_details') ? 'active' : '' }}"
                        href="{{ route('admin.company_details') }}">
                        <i class="fa fa-building"></i>
                        Company Details
                    </a>
                </li>
            </ul>
        </li>

        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('admin.customer_contact') ? 'active' : '' }}"
                href="{{ route('admin.customer_contact') }}">
                <i class="fa fa-address-book"></i>
                <span>Contacts</span>
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

        /* ===== MOBILE SIDEBAR TOGGLE (already yours) ===== */
        const toggleBtn = document.querySelector(".crm-toggle");
        const sidebar = document.getElementById("sidebar");

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener("click", function(e) {
                e.preventDefault();
                sidebar.classList.toggle("show");
            });
        }

        /* ===== SIDEBAR DROPDOWNS (MULTIPLE SUPPORT) ===== */
        document.querySelectorAll(".sidebar-dropdown > .nav-link").forEach(link => {

            link.addEventListener("click", function(e) {
                e.preventDefault();

                const parent = this.closest(".sidebar-dropdown");

                /* Close other dropdowns (accordion behavior) */
                document.querySelectorAll(".sidebar-dropdown.open").forEach(item => {
                    if (item !== parent) {
                        item.classList.remove("open");
                    }
                });

                /* Toggle current */
                parent.classList.toggle("open");
            });

        });

    });
</script>