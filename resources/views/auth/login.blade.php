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
      /* ~66/34 split, matching the reference layout's proportions. */
      grid-template-columns: minmax(0, 2fr) minmax(360px, 1fr);
    }

    /* ===== LEFT: brand / visual panel — photo slideshow =====
       Each slide is a single pre-composed image (headline, KPI cards, all
       baked into the picture) — the panel just crossfades between them.
       Only one image exists today; add more <div class="auth-slide"> blocks
       with their own <img> to extend the rotation later. */
    .auth-visual {
      position: relative;
      overflow: hidden;
      background: #cfe2f3;
    }

    .auth-slide {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.9s ease;
    }
    .auth-slide.is-active { opacity: 1; visibility: visible; }

    /* Responsive strategy for an image with text baked in: the picture is
       always shown WHOLE (never cropped, so no screen size cuts words off),
       scaled to fit whichever dimension is tighter. Whatever space is left
       over — which varies with every window shape — is filled by a blurred,
       enlarged copy of the same photo, so it reads as the scene continuing
       rather than empty bars. The sharp image's edges are feathered into it. */
    .auth-slide-backdrop {
      position: absolute;
      inset: -60px;
      background-size: cover;
      background-position: center;
      filter: blur(32px) saturate(1.05);
    }
    .auth-slide img {
      position: relative;
      display: block;
      width: min(100%, 150vh);
      height: auto;
      -webkit-mask-image: linear-gradient(to bottom, transparent, #000 5%, #000 95%, transparent),
                          linear-gradient(to right, transparent, #000 4%, #000 96%, transparent);
      -webkit-mask-composite: source-in;
      mask-image: linear-gradient(to bottom, transparent, #000 5%, #000 95%, transparent),
                  linear-gradient(to right, transparent, #000 4%, #000 96%, transparent);
      mask-composite: intersect;
    }

    /* ===== RIGHT: form panel ===== */
    .auth-form-panel {
      position: relative;
      overflow: hidden;
      background: var(--panel);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 28px;
    }
    /* Soft brand-colored glows top-right and bottom-left so the panel
       reads as designed rather than a flat white rectangle. */
    .auth-form-panel::before,
    .auth-form-panel::after {
      content: "";
      position: absolute;
      z-index: 0;
      border-radius: 50%;
      pointer-events: none;
      filter: blur(60px);
    }
    .auth-form-panel::before {
      width: 280px;
      height: 280px;
      top: -90px;
      right: -70px;
      background: radial-gradient(circle, rgba(37, 99, 235, 0.16), transparent 70%);
    }
    .auth-form-panel::after {
      width: 320px;
      height: 320px;
      bottom: -110px;
      left: -90px;
      background: radial-gradient(circle, rgba(99, 102, 241, 0.12), transparent 70%);
    }

    .auth-form-wrap {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 380px;
      animation: slideUp 0.5s ease;
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(18px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .auth-form-logo {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 6px;
    }
    .auth-form-logo img { height: 26px; }
    .auth-form-tagline { font-size: 12px; color: var(--muted); margin-bottom: 24px; }

    .auth-title {
      font-size: 26px;
      font-weight: 800;
      color: var(--text);
      margin-bottom: 6px;
    }

    .auth-subtitle {
      font-size: 14px;
      color: var(--muted);
      margin-bottom: 22px;
    }

    .form-group { margin-bottom: 16px; }

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
      background: var(--primary);
      border: none;
      color: #fff;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 2px;
      transition: 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .login-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 22px rgba(99, 102, 241, 0.3);
    }

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

    .form-input-icon { position: relative; }
    .form-input-icon .form-control { padding-left: 42px; }
    .form-input-icon .field-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted);
      font-size: 14px;
      pointer-events: none;
    }

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

    .form-options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 10px;
      margin-bottom: 4px;
    }
    .remember-check {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12.5px;
      color: var(--text);
      cursor: pointer;
      user-select: none;
    }
    .remember-check input { width: 14px; height: 14px; accent-color: var(--primary); cursor: pointer; }

    .forgot-link { margin: 0; }
    .forgot-link a { font-size: 12.5px; color: var(--primary); text-decoration: none; }

    /* Tablet: stack, image becomes a banner above the form (its baked-in
       text is still legible at this width). */
    @media (max-width: 900px) {
      .auth-shell { grid-template-columns: 1fr; }
      .auth-visual { aspect-ratio: 3 / 2; max-height: 60vh; }
      .auth-slide img { width: min(100%, 90vh); }
    }
    /* Phone: the picture's text would shrink to unreadable, so show just the form. */
    @media (max-width: 599px) {
      .auth-visual { display: none; }
    }
  </style>
</head>

<body>

  <div class="auth-shell">

    <div class="auth-visual">
      <div class="auth-slide is-active">
        <div class="auth-slide-backdrop" style="background-image: url('{{ asset('images/login_bg_1.jpg') }}');"></div>
        <img src="{{ asset('images/login_bg_1.jpg') }}" alt="" fetchpriority="high">
      </div>
    </div>

    <div class="auth-form-panel">
      <div class="auth-form-wrap">

        <div class="auth-form-logo">
          <img src="{{ asset('images/logo.svg') }}" alt="CRMS" onerror="this.style.display='none'">
        </div>
        <p class="auth-form-tagline">Smarter and Simpler CRM</p>

        <h1 class="auth-title">Welcome Back</h1>
        <p class="auth-subtitle">Sign in to continue to your CRM account.</p>

        <form id="loginSubmit" method="POST" action="{{ $submitRoute }}">
          @csrf
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <div class="form-input-icon">
              <i class="fa fa-envelope field-icon"></i>
              <input type="email" name="email" class="form-control" placeholder="you@company.com" required autofocus>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <div class="password-wrapper form-input-icon">
              <i class="fa fa-lock field-icon"></i>
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
          </div>

          <div class="form-options">
            <label class="remember-check">
              <input type="checkbox">
              Remember me
            </label>
            <div class="forgot-link">
              <a href="{{ route('password.request') }}">Forgot password?</a>
            </div>
          </div>

          <button type="submit" class="login-btn">
            <i id="fa_spinner" class="fa fa-spinner fa-spin" style="display:none;"></i>
            <span id="btn-text">Sign In</span>
            <i class="fa fa-arrow-right"></i>
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
      const slides = document.querySelectorAll('.auth-slide');
      if (!slides.length) return;

      let index = 0;

      function show(i) {
        index = (i + slides.length) % slides.length;
        slides.forEach((s, n) => s.classList.toggle('is-active', n === index));
      }

      setInterval(() => show(index + 1), 5000);
    })();

  </script>

</body>

</html>
