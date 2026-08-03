<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Login</title>

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <!-- Toastr -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Inter", sans-serif;
    }



    body {
      min-height: 100vh;
      background: linear-gradient(rgba(15, 23, 42, 0.55),
        rgba(15, 23, 42, 0.55)),
      url("{{ asset('images/login_bg.avif') }}");
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
    }


    .auth-wrapper {
      width: 100%;
      max-width: 420px;
      padding: 20px;
    }

    .auth-card {
      background: #ffffff;
      border-radius: 20px;
      padding: 35px 30px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
      animation: slideUp 0.6s ease;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .logo {
      width: 120px;
      margin: 0 auto 20px;
      display: block;
    }

    .auth-title {
      text-align: center;
      font-size: 26px;
      font-weight: 700;
      color: #1f2937;
      margin-bottom: 6px;
    }

    .auth-subtitle {
      text-align: center;
      font-size: 14px;
      color: #6b7280;
      margin-bottom: 28px;
    }

    .form-group {
      margin-bottom: 18px;
    }

    .form-label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: #374151;
      margin-bottom: 6px;
    }

    .form-control {
      width: 100%;
      height: 48px;
      border-radius: 12px;
      border: 1px solid #d1d5db;
      padding: 0 14px;
      font-size: 15px;
      transition: 0.25s;
    }

    .form-control:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .login-btn {
      width: 100%;
      height: 50px;
      border-radius: 14px;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      border: none;
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 8px;
      transition: 0.3s;
    }

    .login-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 25px rgba(37, 99, 235, 0.3);
    }

    .login-btn i {
      margin-right: 6px;
    }

    .options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 14px;
      font-size: 14px;
      color: #6b7280;
    }

    .options label {
      display: flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
    }

    .footer-text {
      text-align: center;
      font-size: 12px;
      color: #9ca3af;
      margin-top: 25px;
    }

    .password-wrapper {
      position: relative;
    }

    .password-wrapper .form-control {
      padding-right: 42px;
      /* space for eye icon */
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

    .eye-btn:hover {
      color: #000;
    }

    .eye-btn:focus {
      outline: none;
    }
  </style>
</head>

<body>

  <div class="auth-wrapper">
    <div class="auth-card">

      <img src="{{ config('app.url') }}public/images/logo.svg" class="logo" alt="Logo">

      <h2 class="auth-title">Admin Panel</h2>
      <p class="auth-subtitle">Sign in to manage your system</p>

      <form id="loginSubmit" method="POST">
        <div class="form-group">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
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
        </div>

        <button type="submit" class="login-btn">
          <i id="fa_spinner" class="fa fa-spinner fa-spin" style="display:none;"></i>
          <span id="btn-text">Login</span>
        </button>

        <div class="options">
          <label>
            <input type="checkbox">
            Remember me
          </label>
        </div>
      </form>

      <div class="footer-text">
        © {{ date('Y') }} Tech Web Mantra. All rights reserved.
      </div>

    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

  <script>
    function showToast(message, type = 'success') {
      toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000"
      };

      if (type === 'success') {
        toastr.success(message);
      } else if (type === 'error') {
        toastr.error(message);
      } else if (type === 'warning') {
        toastr.warning(message);
      } else {
        toastr.info(message);
      }
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
        url: '{{ route("admin.login_submit") }}',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
          setTimeout(() => {
            $('#fa_spinner').hide();
          }, 1000);
          if (response.status) {
            toastr.success(response.message);
            setTimeout(() => {
              window.location.href = "{{ route('admin.dashboard') }}";
            }, 1200);
          } else {
            toastr.error(response.message);
          }
        },
        error: function() {
          $('#fa_spinner').hide();
          toastr.error('Something went wrong!');
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

  </script>

</body>

</html>