<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Super Admin | Control Plane</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Inter", sans-serif;
    }

    html, body {
      height: 100%;
    }

    body {
      display: flex;
      min-height: 100vh;
      background: #0b1120;
    }

    .visual-panel {
      position: relative;
      flex: 1 1 50%;
      min-height: 100vh;
      background: linear-gradient(160deg, rgba(15, 23, 42, .88), rgba(15, 23, 42, .55)),
        url("{{ asset('images/login_bg.avif') }}");
      background-size: cover;
      background-position: center;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 48px;
      color: #fff;
    }

    .visual-brand {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .visual-brand .mark {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      background: rgba(255, 255, 255, .12);
      border: 1px solid rgba(255, 255, 255, .25);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 17px;
    }

    .visual-brand span {
      font-weight: 800;
      font-size: 14px;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, .85);
    }

    .visual-copy h1 {
      font-size: 34px;
      font-weight: 800;
      line-height: 1.25;
      max-width: 460px;
      margin-bottom: 16px;
    }

    .visual-copy p {
      font-size: 15px;
      color: rgba(255, 255, 255, .75);
      max-width: 420px;
      line-height: 1.6;
    }

    .visual-features {
      list-style: none;
      margin-top: 26px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .visual-features li {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 14px;
      color: rgba(255, 255, 255, .85);
    }

    .visual-features i {
      width: 26px;
      height: 26px;
      border-radius: 8px;
      background: rgba(255, 255, 255, .12);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      flex-shrink: 0;
    }

    .visual-foot {
      font-size: 12px;
      color: rgba(255, 255, 255, .5);
    }

    .form-panel {
      flex: 1 1 50%;
      min-height: 100vh;
      background: #f8fafc;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px;
    }

    .auth-wrapper {
      width: 100%;
      max-width: 380px;
    }

    .mark-chip {
      display: inline-block;
      background: #e0e7ff;
      color: #3730a3;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: .08em;
      margin-bottom: 18px;
    }

    .auth-title {
      font-size: 26px;
      font-weight: 800;
      color: #0f172a;
      margin-bottom: 6px;
    }

    .auth-subtitle {
      font-size: 14px;
      color: #64748b;
      margin-bottom: 28px;
    }

    .form-group {
      margin-bottom: 18px;
    }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 700;
      color: #374151;
      margin-bottom: 6px;
    }

    .form-control {
      width: 100%;
      height: 48px;
      border-radius: 10px;
      border: 1px solid #d1d5db;
      padding: 0 14px;
      font-size: 15px;
      background: #fff;
      transition: .2s;
    }

    .form-control:focus {
      outline: none;
      border-color: #4338ca;
      box-shadow: 0 0 0 3px rgba(67, 56, 202, .15);
    }

    .password-wrapper {
      position: relative;
    }

    .password-wrapper .form-control {
      padding-right: 42px;
    }

    .eye-btn {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: transparent;
      border: none;
      cursor: pointer;
      color: #6b7280;
      font-size: 15px;
      padding: 4px;
    }

    .eye-btn:hover { color: #111827; }
    .eye-btn:focus { outline: none; }

    .login-btn {
      width: 100%;
      height: 48px;
      border-radius: 10px;
      background: #1e1b4b;
      border: none;
      color: #fff;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 6px;
      transition: .2s;
    }

    .login-btn:hover {
      background: #312e81;
    }

    .error-box {
      background: #fee2e2;
      color: #991b1b;
      padding: 10px 12px;
      border-radius: 8px;
      font-size: 13px;
      margin-bottom: 18px;
    }

    .auth-links {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
      font-size: 13px;
    }

    .auth-links a {
      color: #4338ca;
      text-decoration: none;
      font-weight: 600;
    }

    .auth-links a:hover { text-decoration: underline; }

    @media (max-width: 860px) {
      .visual-panel { display: none; }
      .form-panel { flex: 1 1 100%; }
    }
  </style>
</head>

<body>

  <div class="visual-panel">
    <div class="visual-brand">
      <div class="mark"><i class="fa-solid fa-shield-halved"></i></div>
      <span>Control Plane</span>
    </div>

    <div class="visual-copy">
      <h1>Manage every company from one place.</h1>
      <p>Approve signups, provision tenant databases, and oversee subscriptions across the whole platform.</p>
      <ul class="visual-features">
        <li><i class="fa-solid fa-building"></i> Company &amp; signup approvals</li>
        <li><i class="fa-solid fa-database"></i> Tenant database provisioning</li>
        <li><i class="fa-solid fa-user-shield"></i> Impersonation with full audit trail</li>
      </ul>
    </div>

    <div class="visual-foot">&copy; {{ date('Y') }} {{ config('app.name', 'CRM') }} — restricted access</div>
  </div>

  <div class="form-panel">
    <div class="auth-wrapper">
      <span class="mark-chip">CONTROL PLANE</span>
      <h1 class="auth-title">Super Admin</h1>
      <p class="auth-subtitle">Sign in with your platform administrator account.</p>

      @if ($errors->any())
        <div class="error-box">{{ $errors->first() }}</div>
      @endif

      <form method="post" action="{{ route('superadmin.login.submit') }}">
        @csrf
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="you@company.com" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="password-wrapper">
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
            <button type="button" class="eye-btn" id="togglePassword">
              <i class="fa fa-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="login-btn">Open Super Admin</button>
      </form>

      <div class="auth-links">
        <a href="{{ route('password.request') }}">Forgot password?</a>
      </div>
    </div>
  </div>

  <script>
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
  </script>

</body>

</html>
