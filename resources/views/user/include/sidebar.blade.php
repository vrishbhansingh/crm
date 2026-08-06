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

    /* ================= MOBILE SIDEBAR BEHAVIOR ================= */
    @media (max-width: 991px) {

        /* Sidebar hidden by default */
        .sidebar {
            position: fixed;
            top: 64px;
            /* navbar height */
            left: -260px;
            height: calc(100vh - 64px);
            z-index: 1200;
            transition: left 0.3s ease;
        }

        /* Sidebar visible */
        .sidebar.show {
            left: 0;
        }

        /* Overlay */
        .sidebar-overlay {
            position: fixed;
            top: 64px;
            left: 0;
            width: 100%;
            height: calc(100vh - 64px);
            background: rgba(0, 0, 0, 0.45);
            z-index: 1100;
            display: none;
        }

        .sidebar-overlay.show {
            display: block;
        }

        .sidebar {
            overflow-y: auto;
            /* ✅ ENABLE SCROLL */
            overscroll-behavior: contain;
        }
    }
</style>

<nav class="sidebar" id="sidebar">

    <div class="sidebar-profile">
        <!-- <img src="{{ asset('images/profile_img.jpg') }}"> -->
        <img src="https://techwebmantra.com/crm/public/images/user_1.png">
        <div class="profile-info">
            <div class="name">{{ Auth::guard('web')->user()->name }}</div>
            <div class="role" style="font-size:12px;">{{ Auth::guard('web')->user()->getRoleNames()->first() }}</div>
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
        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('user.profile') ? 'active' : '' }}"
                href="{{ route('user.profile') }}">
                <i class="fa fa-user"></i>
                <span>Profile</span>
            </a>
        </li>
        @can('leads.view')
        <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}"
                href="{{ route('leads.index') }}">
                <i class="fa fa-briefcase"></i>
                <span>Leads</span>
            </a>
        </li>
        @endcan

        @can('orders.view')
         <li class="mb-1">
            <a class="nav-link {{ request()->routeIs('user.order_management') ? 'active' : '' }}"
                href="{{ route('user.order_management') }}">
                <i class="fa fa-user"></i>
                <span>Order Management</span>
            </a>
        </li>
        @endcan

        <li class="mt-2">
            <form method="post" action="{{ route('user.logout') }}">
                @csrf
                <button class="logout-btn">
                    <i class="fa fa-sign-out"></i> Logout
                </button>
            </form>
        </li>

    </ul>
</nav>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Handle ALL sidebar dropdowns
        document.querySelectorAll('.sidebar-dropdown').forEach(function(dropdown) {

            const toggle = dropdown.querySelector('.dropdown-toggle-custom');

            toggle.addEventListener('click', function(e) {
                e.preventDefault();

                // Close other dropdowns (optional – remove if not needed)
                document.querySelectorAll('.sidebar-dropdown').forEach(function(item) {
                    if (item !== dropdown) {
                        item.classList.remove('open');
                    }
                });

                dropdown.classList.toggle('open');
            });

            // Auto-open dropdown if any child is active
            if (dropdown.querySelector('.sidebar-submenu .nav-link.active')) {
                dropdown.classList.add('open');
            }
        });
    });


        document.addEventListener("DOMContentLoaded", function() {

            const toggleBtn = document.querySelector(".crm-toggle");
            const sidebar = document.getElementById("sidebar");
            const overlay = document.getElementById("sidebarOverlay");

            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener("click", function(e) {
                    e.preventDefault();
                    sidebar.classList.toggle("show");
                    overlay?.classList.toggle("show");
                });
            }
            // Close sidebar when overlay clicked
            overlay?.addEventListener("click", function() {
                sidebar.classList.remove("show");
                overlay.classList.remove("show");
            });
        });
</script>