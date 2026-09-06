<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Set New Password | Control Plane</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }
    html, body { height: 100%; }
    body { display: flex; min-height: 100vh; background: #0b1120; }

    .visual-panel {
      position: relative; flex: 1 1 50%; min-height: 100vh;
      background: linear-gradient(160deg, rgba(15, 23, 42, .88), rgba(15, 23, 42, .55)), url("{{ asset('images/login_bg.avif') }}");
      background-size: cover; background-position: center;
      display: flex; flex-direction: column; justify-content: space-between; padding: 48px; color: #fff;
    }
    .visual-brand { display: flex; align-items: center; gap: 12px; }
    .visual-brand .mark {
      width: 40px; height: 40px; border-radius: 10px; background: rgba(255, 255, 255, .12);
      border: 1px solid rgba(255, 255, 255, .25); display: flex; align-items: center; justify-content: center; font-size: 17px;
    }
    .visual-brand span { font-weight: 800; font-size: 14px; letter-spacing: .12em; text-transform: uppercase; color: rgba(255, 255, 255, .85); }
    .visual-copy h1 { font-size: 32px; font-weight: 800; line-height: 1.25; max-width: 440px; margin-bottom: 16px; }
    .visual-copy p { font-size: 15px; color: rgba(255, 255, 255, .75); max-width: 420px; line-height: 1.6; }
    .visual-foot { font-size: 12px; color: rgba(255, 255, 255, .5); }

    .form-panel { flex: 1 1 50%; min-height: 100vh; background: #f8fafc; display: flex; align-items: center; justify-content: center; padding: 32px; }
    .auth-wrapper { width: 100%; max-width: 380px; }

    .mark-chip {
      display: inline-block; background: #e0e7ff; color: #3730a3; padding: 6px 12px; border-radius: 20px;
      font-size: 11px; font-weight: 800; letter-spacing: .08em; margin-bottom: 18px;
    }
    .auth-title { font-size: 26px; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
    .auth-subtitle { font-size: 14px; color: #64748b; margin-bottom: 28px; }

    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 6px; }
    .form-control {
      width: 100%; height: 48px; border-radius: 10px; border: 1px solid #d1d5db; padding: 0 14px;
      font-size: 15px; background: #fff; transition: .2s;
    }
    .form-control:focus { outline: none; border-color: #4338ca; box-shadow: 0 0 0 3px rgba(67, 56, 202, .15); }

    .login-btn {
      width: 100%; height: 48px; border-radius: 10px; background: #1e1b4b; border: none;
      color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 6px; transition: .2s;
    }
    .login-btn:hover { background: #312e81; }

    .error-box { background: #fee2e2; color: #991b1b; padding: 10px 12px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; line-height: 1.5; }
    .error-box a { display: inline-block; margin-top: 8px; color: #991b1b; font-weight: 700; text-decoration: underline; }

    .auth-links { margin-top: 20px; font-size: 13px; }
    .auth-links a { color: #4338ca; text-decoration: none; font-weight: 600; }
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
      <h1>Choose a new password.</h1>
      <p>Pick something you haven't used elsewhere — this account can manage every company on the platform.</p>
    </div>

    <div class="visual-foot">&copy; {{ date('Y') }} {{ config('app.name', 'CRM') }} — restricted access</div>
  </div>

  <div class="form-panel">
    <div class="auth-wrapper">
      <span class="mark-chip">CONTROL PLANE</span>
      <h1 class="auth-title">Set New Password</h1>
      <p class="auth-subtitle">Choose a new password for your Super Admin account.</p>

      @if ($errors->any())
        <div class="error-box">
          {{ $errors->first() }}
          <a href="{{ route('superadmin.password.request') }}">Request a new reset link &rarr;</a>
        </div>
      @endif

      <form method="post" action="{{ route('superadmin.password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required autofocus>
        </div>
        <div class="form-group">
          <label class="form-label">New password</label>
          <input type="password" name="password" class="form-control" placeholder="At least 8 characters" required minlength="8">
        </div>
        <div class="form-group">
          <label class="form-label">Confirm new password</label>
          <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password" required minlength="8">
        </div>
        <button type="submit" class="login-btn">Reset password</button>
      </form>

      <div class="auth-links">
        <a href="{{ route('superadmin.login') }}">&larr; Back to Super Admin login</a>
      </div>
    </div>
  </div>

</body>

</html>
