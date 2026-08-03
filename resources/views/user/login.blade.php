<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

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
            display: flex;
        }

        /* LEFT IMAGE */
        .login-image {
            width: 50%;
            background: linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.45)),
            url("{{ asset('images/login_bg.avif') }}");
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            padding: 40px;
        }

        .login-image h1 {
            font-size: 36px;
            max-width: 420px;
            line-height: 1.3;
        }

        /* RIGHT FORM */
        /* RIGHT FORM */
        .login-form {
            width: 50%;
            background: linear-gradient(135deg, #f8fafc, #eef2ff);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .form-box {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            padding: 40px 36px;
            border-radius: 14px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-box h2 {
            font-size: 26px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }

        .form-box p {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 28px;
        }

        /* INPUT FIELDS */
        .field {
            margin-bottom: 18px;
        }

        .field label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            display: block;
        }

        .field input {
            width: 100%;
            height: 46px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 0 14px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .field input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        /* BUTTON */
        .login-btn {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.35);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            width: 100%;
            padding-right: 45px;
            /* space for eye button */
        }

        .eye-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            cursor: pointer;
            color: #6c757d;
            font-size: 16px;
        }

        .eye-btn:focus {
            outline: none;
        }

        .eye-btn:hover {
            color: #000;
        }
    </style>
</head>

<body>

    <div class="login-image">
        <h1>Welcome to the CRM User Portal</h1>
    </div>

    <div class="login-form">
        <div class="form-box">
            <h2>User Login</h2>
            <p>Sign in to access your dashboard</p>

            <form method="POST" id="userLogin">
                @csrf
                
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" id="email" placeholder="Enter your email">
                </div>

                <div class="field">
                    <label>Password</label>

                    <div class="password-wrapper">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Enter your password">
                        <button type="button" class="eye-btn" id="togglePassword">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="login-btn">
                    <i id="fa_spinner" class="fa fa-spinner fa-spin" style="display:none;"></i>
                    <span id="btn-text">Login</span>
                </button>
            </form>

            <div class="footer" style="margin-top: 10px;">
                © {{ date('Y') }} CRM System
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


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
        $(document).on('submit', '#userLogin', function(e) {
            e.preventDefault();
             $('#fa_spinner').show();
            formData = new FormData(this);
            $.ajax({
                url: '{{route("user.login_submit")}}',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status) {
                        setTimeout(() => {
                            $('#fa_spinner').hide();
                        }, 1000);
                        toastr.success(response.message);
                        setTimeout(() => {
                            window.location.href = "{{ route('user.dashboard') }}";
                        }, 1200);
                    }
                    else{
                         setTimeout(() => {
                            $('#fa_spinner').hide();
                        }, 1000);
                        toastr.error(response.message);
                    }
                },
                    error: function(err)
                    {
                         setTimeout(() => {
                            $('#fa_spinner').hide();
                        }, 1000);
                        toastr.error(err.message);
                    }
            })
        })

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