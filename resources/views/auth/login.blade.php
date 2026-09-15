<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CRM Login</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Inter", sans-serif;
    }

    :root {
      --bg: #f5f7fb;
      --panel: #ffffff;
      --text: #1f2937;
      --muted: #6b7280;
      --border: #d1d5db;
      --primary: #2563eb;
      --primary-dark: #1d4ed8;
    }

    html[data-theme="dark"] {
      --bg: #0f1117;
      --panel: #1a1d2b;
      --text: #eef0f6;
      --muted: #9aa1b5;
      --border: #2a2e40;
      --primary: #6366f1;
      --primary-dark: #93a4fd;
    }

    html, body { min-height: 100vh; background: var(--bg); }

    .auth-shell {
      min-height: 100vh;
      display: grid;
      grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
    }

    /* ===== LEFT: brand / visual panel ===== */
    .auth-visual {
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 56px 64px;
      color: #fff;
    }
    .auth-visual-bg {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .auth-visual-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(160deg, rgba(15, 23, 42, 0.88), rgba(37, 64, 175, 0.72));
    }
    html[data-theme="dark"] .auth-visual-overlay {
      background: linear-gradient(160deg, rgba(10, 12, 20, 0.92), rgba(30, 33, 74, 0.82));
    }
    .auth-visual-content { position: relative; z-index: 1; max-width: 460px; }

    .auth-visual-logo {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 40px;
    }
    .auth-visual-logo img { height: 30px; width: auto; }
    .auth-visual-logo span { font-size: 20px; font-weight: 800; letter-spacing: 0.02em; }

    .auth-visual-content h2 {
      font-size: 32px;
      font-weight: 800;
      line-height: 1.28;
      margin-bottom: 14px;
    }
    .auth-visual-content > p {
      font-size: 15px;
      color: rgba(255, 255, 255, 0.82);
      line-height: 1.6;
      margin-bottom: 32px;
    }

    .auth-visual-points { list-style: none; display: flex; flex-direction: column; gap: 14px; }
    .auth-visual-points li {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 14px;
      color: rgba(255, 255, 255, 0.92);
    }
    .auth-visual-points i { color: #4ade80; font-size: 15px; }

    /* ===== RIGHT: form panel ===== */
    .auth-form-panel {
      position: relative;
      background: var(--panel);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 28px;
    }

    .auth-theme-toggle {
      position: absolute;
      top: 24px;
      right: 28px;
      width: 38px;
      height: 38px;
      border-radius: 50%;
      border: 1px solid var(--border);
      background: transparent;
      color: var(--muted);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 15px;
      transition: background 0.15s ease, color 0.15s ease;
    }
    .auth-theme-toggle:hover { background: rgba(99, 102, 241, 0.1); color: var(--primary); }

    .auth-form-wrap {
      width: 100%;
      max-width: 380px;
      animation: slideUp 0.5s ease;
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(18px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .auth-form-logo {
      display: none;
      align-items: center;
      gap: 8px;
      margin-bottom: 28px;
    }
    .auth-form-logo img { height: 26px; }
    .auth-form-logo span { font-size: 17px; font-weight: 800; color: var(--text); }

    .auth-title {
      font-size: 26px;
      font-weight: 800;
      color: var(--text);
      margin-bottom: 6px;
    }

    .auth-subtitle {
      font-size: 14px;
      color: var(--muted);
      margin-bottom: 28px;
    }

    .form-group { margin-bottom: 18px; }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 6px;
    }

    .form-control {
      width: 100%;
      height: 46px;
      border-radius: 10px;
      border: 1px solid var(--border);
      background: var(--panel);
      color: var(--text);
      padding: 0 14px;
      font-size: 14.5px;
      transition: 0.2s;
    }
    .form-control::placeholder { color: var(--muted); }

    .form-control:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    .login-btn {
      width: 100%;
      height: 48px;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      border: none;
      color: #fff;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 6px;
      transition: 0.2s;
    }

    .login-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 22px rgba(99, 102, 241, 0.3);
    }
    .login-btn i { margin-right: 6px; }

    .auth-signup-line {
      text-align: center;
      margin-top: 20px;
      font-size: 13.5px;
      color: var(--muted);
    }
    .auth-signup-line a { color: var(--primary); font-weight: 600; text-decoration: none; }

    .footer-text {
      text-align: center;
      font-size: 12px;
      color: var(--muted);
      margin-top: 28px;
    }

    .password-wrapper { position: relative; }
    .password-wrapper .form-control { padding-right: 42px; }

    .eye-btn {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: transparent;
      border: none;
      cursor: pointer;
      color: var(--muted);
      font-size: 14px;
    }
    .eye-btn:hover { color: var(--text); }
    .eye-btn:focus { outline: none; }

    .forgot-link { text-align: right; margin-top: 8px; }
    .forgot-link a { font-size: 12.5px; color: var(--primary); text-decoration: none; }

    @media (max-width: 900px) {
      .auth-shell { grid-template-columns: 1fr; }
      .auth-visual { display: none; }
      .auth-form-logo { display: flex; }
    }
  </style>
</head>

<body>

  <div class="auth-shell">

    <div class="auth-visual">
      <img class="auth-visual-bg" src="{{ asset('images/login_bg.avif') }}" alt="">
      <div class="auth-visual-overlay"></div>
      <div class="auth-visual-content">
        <div class="auth-visual-logo">
          <img src="{{ asset('images/logo.svg') }}" alt="Logo" onerror="this.style.display='none'">
          <span>CRMS</span>
        </div>
        <h2>Run your whole sales pipeline from one place.</h2>
        <p>Leads, deals, orders and your team — organized, tracked and always up to date.</p>
        <ul class="auth-visual-points">
          <li><i class="fa fa-circle-check"></i> A private, secure workspace for your company</li>
          <li><i class="fa fa-circle-check"></i> Pipelines, tasks and follow-up reminders built in</li>
          <li><i class="fa fa-circle-check"></i> Dashboards that stay up to date in real time</li>
        </ul>
      </div>
    </div>

    <div class="auth-form-panel">
      <button type="button" class="auth-theme-toggle" id="authThemeToggleBtn" title="Toggle dark / light mode" aria-label="Toggle dark / light mode">
        <i class="fa fa-moon" id="authThemeToggleIcon"></i>
      </button>

      <div class="auth-form-wrap">

        <div class="auth-form-logo">
          <img src="{{ asset('images/logo.svg') }}" alt="Logo" onerror="this.style.display='none'">
          <span>CRMS</span>
        </div>

        <h1 class="auth-title">Welcome back</h1>
        <p class="auth-subtitle">Sign in to access your CRM dashboard</p>

        <form id="loginSubmit" method="POST" action="{{ $submitRoute }}">
          @csrf
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="Enter your email" required autofocus>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <div class="password-wrapper">
              <input
                type="password"
                name="password"
                id="password"
                class="form-control"
                placeholder="Enter your password"
                required>
              <button type="button" class="eye-btn" id="togglePassword">
                <i class="fa fa-eye"></i>
              </button>
            </div>
            <div class="forgot-link">
              <a href="{{ route('password.request') }}">Forgot password?</a>
            </div>
          </div>

          <button type="submit" class="login-btn">
            <i id="fa_spinner" class="fa fa-spinner fa-spin" style="display:none;"></i>
            <span id="btn-text">Sign in</span>
          </button>
        </form>

        <p class="auth-signup-line">New company? <a href="{{ route('register') }}">Create a workspace</a></p>

        <div class="footer-text">
          &copy; {{ date('Y') }} {{ config('app.name', 'CRM') }}. All rights reserved.
        </div>

      </div>
    </div>

  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>

  <script>
    // Applied immediately (not inside DOMContentLoaded) so the page paints
    // in the right theme on first load instead of flashing light-then-dark
    // — same pattern used on every page after login (include/header.blade.php).
    (function() {
      if (localStorage.getItem('crm-theme') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
      }
    })();

    function showToast(message, type = 'success') {
      toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000"
      };
      toastr[type] ? toastr[type](message) : toastr.info(message);
    }

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });

    $(document).on('submit', '#loginSubmit', function(e) {
      e.preventDefault();
      $('#fa_spinner').show();

      let formData = new FormData(this);

      $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
          $('#fa_spinner').hide();
          if (response.status) {
            toastr.success(response.message);
            setTimeout(() => {
              window.location.href = response.location;
            }, 800);
          } else {
            toastr.error(response.message);
          }
        },
        error: function(xhr) {
          $('#fa_spinner').hide();
          const message = xhr.responseJSON?.message || 'Something went wrong!';
          toastr.error(message);
        }
      });
    });

    document.getElementById('togglePassword').addEventListener('click', function() {
      const input = document.getElementById('password');
      const icon = this.querySelector('i');

      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    });

    (function() {
      const btn = document.getElementById('authThemeToggleBtn');
      const icon = document.getElementById('authThemeToggleIcon');

      function sync() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        icon.className = isDark ? 'fa fa-sun' : 'fa fa-moon';
      }
      sync();

      btn.addEventListener('click', function() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        if (isDark) {
          document.documentElement.removeAttribute('data-theme');
          localStorage.setItem('crm-theme', 'light');
        } else {
          document.documentElement.setAttribute('data-theme', 'dark');
          localStorage.setItem('crm-theme', 'dark');
        }
        sync();
      });
    })();
  </script>

</body>

</html>
