<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{{ session('auto_approved') ? 'Workspace ready' : 'Registration received' }}</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }

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
    }

    .auth-wrapper { width: 100%; max-width: 460px; padding: 20px; }

    .auth-card {
      background: #ffffff;
      border-radius: 20px;
      padding: 40px 34px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
      text-align: center;
      animation: slideUp 0.6s ease;
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .status-icon {
      width: 62px;
      height: 62px;
      border-radius: 50%;
      background: #dcfce7;
      color: #16a34a;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 26px;
      margin: 0 auto 18px;
    }

    .auth-title { font-size: 22px; font-weight: 700; color: #1f2937; margin-bottom: 10px; }
    .auth-subtitle { font-size: 14.5px; color: #6b7280; line-height: 1.6; margin-bottom: 22px; }

    .warning {
      background: #fef3c7;
      color: #92400e;
      padding: 12px 14px;
      border-radius: 10px;
      font-size: 13.5px;
      margin-bottom: 22px;
      text-align: left;
    }

    .login-link {
      display: inline-block;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: #fff;
      text-decoration: none;
      padding: 13px 26px;
      border-radius: 14px;
      font-weight: 600;
      font-size: 15px;
      transition: 0.3s;
    }

    .login-link:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 25px rgba(37, 99, 235, 0.3);
    }
  </style>
</head>

<body>
  <div class="auth-wrapper">
    <div class="auth-card">
      <div class="status-icon"><i class="fa fa-check"></i></div>

      @if (session('auto_approved'))
        <h1 class="auth-title">Your workspace is ready</h1>
        <p class="auth-subtitle">Your company's private database has been created and your Admin account is active. You can sign in right now.</p>
      @else
        <h1 class="auth-title">Registration received</h1>
        <p class="auth-subtitle">Your isolated company workspace has been queued for Super Admin approval. You can sign in once it's approved.</p>
      @endif

      @if (session('provision_warning'))
        <p class="warning">{{ session('provision_warning') }}</p>
      @endif

      <a href="{{ url('/') }}" class="login-link">Go to sign in</a>
    </div>
  </div>
</body>

</html>
