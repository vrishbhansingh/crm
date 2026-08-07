<style>
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
    gap: 10px;
    cursor: pointer;
  }

  .crm-profile img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid #e0e7ff;
  }

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

    .crm-profile-text {
      display: none;
    }

    .crm-profile img {
      width: 32px;
      height: 32px;
    }

    .crm-dropdown {
      right: 10px;
      top: 60px;
    }
  }
</style>

<div class="crm-navbar">
  <div class="crm-brand-wrapper">
    <a href="{{ route('dashboard') }}">
      <img src="{{ asset('images/logo.svg') }}" alt="Logo">
    </a>
  </div>

  <div class="crm-menu-wrapper">
    <div class="crm-left">
      <i class="fa fa-bars crm-toggle" data-toggle="minimize"></i>
    </div>

    <div class="crm-right">
      <div class="crm-profile" id="profileToggle">
        <img src="{{ asset('images/user_1.png') }}">
        <div class="crm-profile-text">
          <span>{{ Auth::guard('web')->user()->name }}</span>
          <small>{{ Auth::guard('web')->user()->getRoleNames()->first() }}</small>
        </div>
      </div>

      <div class="crm-dropdown" id="profileDropdown">
        <a href="{{ route('profile.show') }}"><i class="fa fa-user"></i> Profile</a>
        <a href="{{ route('security.show') }}">
          <i class="fa fa-shield"></i> Security
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
