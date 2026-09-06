<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Forgot Password · CRM</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }
    body {
      min-height: 100vh;
      background: linear-gradient(rgba(15, 23, 42, 0.55), rgba(15, 23, 42, 0.55)), url("{{ asset('images/login_bg.avif') }}");
      background-size: cover; background-position: center; background-repeat: no-repeat;
      display: flex; align-items: center; justify-content: center;
    }
    .auth-wrapper { width: 100%; max-width: 420px; padding: 20px; }
    .auth-card { background: #ffffff; border-radius: 20px; padding: 35px 30px; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08); animation: slideUp 0.6s ease; }
    @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .logo { width: 120px; margin: 0 auto 20px; display: block; }
    .icon-badge {
      width: 52px; height: 52px; border-radius: 14px; margin: 0 auto 18px;
      background: rgba(37, 99, 235, 0.1); color: #2563eb;
      display: flex; align-items: center; justify-content: center; font-size: 21px;
    }
    .auth-title { text-align: center; font-size: 26px; font-weight: 700; color: #1f2937; margin-bottom: 6px; }
    .auth-subtitle { text-align: center; font-size: 14px; color: #6b7280; margin-bottom: 28px; line-height: 1.5; }
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .form-control { width: 100%; height: 48px; border-radius: 12px; border: 1px solid #d1d5db; padding: 0 14px; font-size: 15px; transition: 0.25s; }
    .form-control:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }
    .login-btn {
      width: 100%; height: 50px; border-radius: 14px; background: linear-gradient(135deg, #2563eb, #1d4ed8);
      border: none; color: #fff; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 8px; transition: 0.3s;
    }
    .login-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(37, 99, 235, 0.3); }
    .footer-text { text-align: center; font-size: 12px; color: #9ca3af; margin-top: 25px; }
    .status-banner { background: #ecfdf5; color: #065f46; border-radius: 12px; padding: 12px 14px; font-size: 14px; margin-bottom: 18px; }
    .error-banner { background: #fef2f2; color: #991b1b; border-radius: 12px; padding: 12px 14px; font-size: 14px; margin-bottom: 18px; }
    .back-link {
      display: flex; align-items: center; justify-content: center; gap: 8px;
      width: 100%; height: 46px; margin-top: 18px; border-radius: 12px;
      border: 1px solid #e2e8f0; background: #f8fafc;
      color: #374151; font-size: 14px; font-weight: 600; text-decoration: none;
      transition: 0.2s;
    }
    .back-link:hover { background: #eff6ff; border-color: #93c5fd; color: #2563eb; }
    .back-link i { font-size: 12px; }
  </style>
</head>

<body>
  <div class="auth-wrapper">
    <div class="auth-card">
      <img src="{{ asset('images/logo.svg') }}" class="logo" alt="Logo" onerror="this.style.display='none'">
      <div class="icon-badge"><i class="fa-solid fa-key"></i></div>

      <h2 class="auth-title">Forgot Password</h2>
      <p class="auth-subtitle">Enter your email and we'll send you a reset link</p>

      @if(session('status'))
        <div class="status-banner">{{ session('status') }}</div>
      @endif
      @if($errors->any())
        <div class="error-banner">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="Enter your account email" value="{{ old('email') }}" required autofocus>
        </div>
        <button type="submit" class="login-btn">Send reset link</button>
      </form>

      <a class="back-link" href="{{ route('user.login') }}"><i class="fa-solid fa-arrow-left"></i> Back to login</a>

      <div class="footer-text">
        &copy; {{ date('Y') }} {{ config('app.name', 'CRM') }}. All rights reserved.
      </div>
    </div>
  </div>
</body>

</html>
