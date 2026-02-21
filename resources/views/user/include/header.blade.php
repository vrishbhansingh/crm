<style>
  /* ===== MAIN NAVBAR ===== */
  .crm-navbar {
    height: 64px;
    background: linear-gradient(90deg, #f9fafb 0%, #eef2ff 50%, #f0f9ff 100%);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    display: flex;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
  }

  /* ===== LOGO / BRAND AREA ===== */
  .crm-brand-wrapper {
    width: 230px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .crm-brand-wrapper img {
    height: 32px;
  }

  /* ===== RIGHT MENU AREA ===== */
  .crm-menu-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
  }

  /* ===== LEFT CONTROLS ===== */
  .crm-left {
    display: flex;
    align-items: center;
    gap: 18px;
    visibility: hidden;
  }

  /* Toggle */
  .crm-toggle {
    font-size: 18px;
    color: #4b49ac;
    cursor: pointer;
  }

  /* Search */
  .crm-search {
    position: relative;
  }

  .crm-search input {
    height: 36px;
    padding: 0 14px 0 36px;
    border-radius: 18px;
    border: 1px solid #e5e7eb;
    font-size: 13px;
    background: #ffffff;
    width: 260px;
  }

  .crm-search i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 14px;
  }

  /* ===== RIGHT CONTROLS ===== */
  .crm-right {
    display: flex;
    align-items: center;
    gap: 18px;
  }

  /* Icon buttons */
  .crm-icon-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4b49ac;
    font-size: 15px;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: all 0.2s ease;
  }

  .crm-icon-btn:hover {
    background: #4b49ac;
    color: #ffffff;
  }

  /* Profile */
  .crm-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
  }

  .crm-profile img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid #e0e7ff;
  }

  .crm-profile span {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
  }

  /* Dropdown */
  .crm-dropdown {
    position: absolute;
    top: 70px;
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

  /* LOGOUT BUTTON INSIDE DROPDOWN */
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

  /* ICON */
  .crm-dropdown .logout-btn:hover i {
    color: #dc3545;
  }

  /* HOVER (SOFT – NOT DARK) */
  .crm-dropdown .logout-btn:hover {
    background: #f8f9fa;
    color: #dc3545;
  }

  /* ================= MOBILE HEADER ================= */
  @media (max-width: 768px) {

    .crm-navbar {
      height: 56px;
      padding: 0 12px;
    }

    /* BRAND */
    .crm-brand-wrapper {
      width: auto;
      padding: 0 10px;
      border-right: none;
    }

    .crm-brand-wrapper img {
      height: 28px;
    }

   

    /* SHOW TOGGLE */
    .crm-toggle {
      font-size: 20px;
    }

    /* HIDE SEARCH */
    .crm-search {
      display: none !important;
    }

    /* RIGHT */
    .crm-right {
      gap: 10px;
    }

    /* HIDE NOTIFICATION + SETTINGS */
    .crm-icon-btn {
      display: none;
    }

    /* PROFILE */
    .crm-profile span {
      display: none;
    }

    .crm-profile img {
      width: 32px;
      height: 32px;
    }

    /* DROPDOWN POSITION */
    .crm-dropdown {
      right: 10px;
      top: 60px;
    }
  }

    @media (max-width: 945px) {
       .crm-left {
      visibility: visible;
      gap: 12px;
    }

    }
</style>



<div class="crm-navbar">

  <!-- LOGO / BRAND -->
  <div class="crm-brand-wrapper">
    <a href="#">
      <img src="{{ asset('images/logo.svg') }}" alt="Logo">
    </a>
  </div>

  <!-- MENU -->
  <div class="crm-menu-wrapper">

    <!-- LEFT -->
    <div class="crm-left">
      <i class="fa fa-bars crm-toggle" data-toggle="minimize"></i>
    </div>

    <!-- RIGHT -->
    <div class="crm-right">

      <div class="crm-icon-btn">
        <i class="fa fa-bell"></i>
      </div>

      <div class="crm-icon-btn">
        <i class="fa fa-cog"></i>
      </div>

      <div class="crm-profile" id="profileToggle">
        <img src="{{ asset('images/user_1.png') }}">
        <span>Admin</span>
      </div>

      <!-- Dropdown -->
      <div class="crm-dropdown" id="profileDropdown">
        <a href="#"><i class="fa fa-user"></i> Profile</a>
        <a href="#"><i class="fa fa-cog"></i> Settings</a>
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

<script>
 document.addEventListener("DOMContentLoaded", function() {

    const profile = document.getElementById("profileToggle");
    const dropdown = document.getElementById("profileDropdown");

    /* ===== PROFILE DROPDOWN ===== */
    profile.addEventListener("click", function(e) {
      e.stopPropagation();
      dropdown.style.display =
        dropdown.style.display === "block" ? "none" : "block";
    });

    dropdown.addEventListener("click", function(e) {
      e.stopPropagation(); // prevent auto close
    });

  });
</script>