<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create company workspace</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Inter", sans-serif;
    }

    body {
      min-height: 100vh;
      background: linear-gradient(rgba(15, 23, 42, 0.55), rgba(15, 23, 42, 0.55)),
        url("{{ asset('images/login_bg.avif') }}");
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 8px 0;
    }

    .auth-wrapper {
      width: 100%;
      max-width: 600px;
      padding: 8px;
    }

    .auth-card {
      background: #ffffff;
      border-radius: 20px;
      padding: 20px 36px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
      animation: slideUp 0.6s ease;
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .logo {
      width: 88px;
      margin: 0 auto 10px;
      display: block;
    }

    .auth-title {
      text-align: center;
      font-size: 21px;
      font-weight: 700;
      color: #1f2937;
      margin-bottom: 4px;
    }

    .auth-subtitle {
      text-align: center;
      font-size: 13px;
      color: #6b7280;
      margin-bottom: 16px;
      line-height: 1.4;
    }

    .error-summary {
      background: #fef2f2;
      color: #991b1b;
      padding: 10px 14px;
      border-radius: 10px;
      margin-bottom: 14px;
      font-size: 13px;
    }

    .section-label {
      display: flex; align-items: center; gap: 10px;
      font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
      color: #94a3b8; margin-bottom: 10px;
    }

    .section-label:not(:first-child) { margin-top: 14px; }

    .section-label::after {
      content: ""; flex: 1; height: 1px; background: #e5e7eb;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .form-grid .full {
      grid-column: 1 / -1;
    }

    .form-group {
      margin-bottom: 12px;
    }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #374151;
      margin-bottom: 5px;
    }

    .form-control {
      width: 100%;
      height: 42px;
      border-radius: 10px;
      border: 1px solid #d1d5db;
      padding: 0 13px;
      font-size: 14px;
      transition: 0.25s;
    }

    .form-control:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .form-control.is-invalid {
      border-color: #dc2626;
    }

    .field-error {
      color: #dc2626;
      font-size: 12.5px;
      margin-top: 5px;
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
      color: #6c757d;
      font-size: 15px;
    }

    .eye-btn:hover { color: #000; }
    .eye-btn:focus { outline: none; }

    .register-btn {
      width: 100%;
      height: 46px;
      border-radius: 12px;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      border: none;
      color: #fff;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 4px;
      transition: 0.3s;
    }

    .register-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 25px rgba(37, 99, 235, 0.3);
    }

    .signin-row {
      text-align: center;
      margin-top: 12px;
      font-size: 13px;
      color: #6b7280;
    }

    .signin-row a {
      color: #2563eb;
      text-decoration: none;
      font-weight: 600;
    }

    .signin-row a:hover { text-decoration: underline; }

    .footer-text {
      text-align: center;
      font-size: 11px;
      color: #9ca3af;
      margin-top: 12px;
    }

    @media (max-width: 640px) {
      .form-grid { grid-template-columns: 1fr; }
      .form-grid .full { grid-column: auto; }
      .auth-card { padding: 30px 22px; }
    }
  </style>
</head>

<body>

  <div class="auth-wrapper">
    <div class="auth-card">

      <img src="{{ asset('images/logo.svg') }}" class="logo" alt="Logo" onerror="this.style.display='none'">

      <h2 class="auth-title">Create your company workspace</h2>
      <p class="auth-subtitle">Your company gets its own private database and an Admin account you can sign in with right away.</p>

      @if ($errors->any())
        <div class="error-summary">{{ $errors->first() }}</div>
      @endif

      <form method="post" action="{{ route('register.store') }}">
        @csrf

        <div class="section-label">Company details</div>
        <div class="form-grid">
          <div class="form-group full">
            <label class="form-label">Company name</label>
            <input name="organization_name" value="{{ old('organization_name') }}"
              class="form-control @error('organization_name') is-invalid @enderror" required autofocus>
            @error('organization_name') <div class="field-error">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="section-label">Your account</div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Your name</label>
            <input name="name" value="{{ old('name') }}"
              class="form-control @error('name') is-invalid @enderror" required>
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label">Phone (optional)</label>
            <input name="phone" value="{{ old('phone') }}"
              class="form-control @error('phone') is-invalid @enderror">
            @error('phone') <div class="field-error">{{ $message }}</div> @enderror
          </div>

          <div class="form-group full">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}"
              class="form-control @error('email') is-invalid @enderror" required>
            @error('email') <div class="field-error">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label">Password</label>
            <div class="password-wrapper">
              <input type="password" name="password" id="password"
                class="form-control @error('password') is-invalid @enderror" required>
              <button type="button" class="eye-btn" data-toggle="password"><i class="fa fa-eye"></i></button>
            </div>
            @error('password') <div class="field-error">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label">Confirm password</label>
            <div class="password-wrapper">
              <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
              <button type="button" class="eye-btn" data-toggle="password_confirmation"><i class="fa fa-eye"></i></button>
            </div>
          </div>

          <div class="form-group full" style="margin-bottom:0">
            <button type="submit" class="register-btn">Create workspace</button>
          </div>
        </div>
      </form>

      <p class="signin-row">Already have a workspace? <a href="{{ url('/') }}">Sign in</a></p>

      <div class="footer-text">
        &copy; {{ date('Y') }} {{ config('app.name', 'CRM') }}. All rights reserved.
      </div>

    </div>
  </div>

  <script>
    document.querySelectorAll('[data-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var input = document.getElementById(btn.getAttribute('data-toggle'));
        var icon = btn.querySelector('i');
        var showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        icon.classList.toggle('fa-eye', showing);
        icon.classList.toggle('fa-eye-slash', !showing);
      });
    });
  </script>

</body>

</html>
