<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Settings | CRM User</title>

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        body {
            background: linear-gradient(135deg, #eef2ff, #f8fafc);
            font-family: 'Inter', sans-serif;
        }

        /* ===== CENTER WRAPPER ===== */
        .security-wrapper {
            min-height: calc(100vh - 120px);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ===== GRADIENT BORDER CARD ===== */
        .security-card {
            background: linear-gradient(135deg, #6366f1, #2563eb, #06b6d4);
            padding: 1px;
            border-radius: 24px;
            box-shadow: 0 35px 70px rgba(37, 99, 235, 0.35);
            width: 100%;
            max-width: 420px;
        }

        .security-card-inner {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(18px);
            border-radius: 23px;
            padding: 34px 30px;
        }

        /* ===== HEADER ===== */
        .security-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .security-header .icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 28px;
            margin: 0 auto 14px;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.45);
        }

        .security-header h4 {
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }

        .security-header p {
            font-size: 13px;
            color: #6b7280;
        }

        /* ===== FLOATING INPUTS ===== */
        .floating-group {
            position: relative;
            margin-bottom: 22px;
        }

        .floating-group input {
            width: 100%;
            padding: 14px 14px;
            border-radius: 12px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            background: #ffffff;
            transition: 0.25s;
        }

        .floating-group label {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            color: #6b7280;
            pointer-events: none;
            transition: 0.25s;
            background: #ffffff;
            padding: 0 6px;
        }

        .floating-group input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        .floating-group input:focus+label,
        .floating-group input:not(:placeholder-shown)+label {
            top: -7px;
            font-size: 11px;
            color: #2563eb;
            font-weight: 600;
        }

        /* ===== INFO BOX ===== */
        .password-info {
            background: linear-gradient(135deg, #eef2ff, #f5f7ff);
            border-left: 4px solid #2563eb;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 12px;
            color: #374151;
            margin-bottom: 24px;
        }

        /* ===== BUTTONS ===== */
        .security-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .btn-security {
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            border: none;
            color: #ffffff;
            padding: 11px 28px;
            font-weight: 600;
            border-radius: 999px;
            transition: 0.3s;
        }

        .btn-security:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.45);
        }

        .btn-back {
            color: #2563eb;
            font-weight: 600;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            opacity: 0.85;
            padding: 10px;
            border-radius: 20px;
        }

        .gradient-border {
            border: 2px solid transparent;
            border-radius: 20px;
            background:
                linear-gradient(#fff, #fff) padding-box,
                linear-gradient(135deg, #2563eb, #4f46e5) border-box;
        }

        .btn-back:hover {
            opacity: 1;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="container-scroller">

        @include('user.include.header')

        <div class="container-fluid page-body-wrapper">

            @include('user.include.sidebar')

            <div class="content-wrapper">

                <div class="security-wrapper">

                    <div class="security-card">
                        <div class="security-card-inner">

                            <!-- HEADER -->
                            <div class="security-header">
                                <div class="icon">
                                    <i class="fa fa-shield"></i>
                                </div>
                                <h4>Security Settings</h4>
                                <p>Update your account password</p>
                            </div>

                            <!-- FORM -->
                            <form id="updatePasswordForm" method="post">
                                @csrf

                                <div class="floating-group">
                                    <input type="password" name="new_password" placeholder=" " required>
                                    <label>New Password</label>
                                </div>

                                <div class="floating-group">
                                    <input type="password" name="confirm_password" placeholder=" " required>
                                    <label>Confirm New Password</label>
                                </div>

                                <div class="password-info">
                                    <i class="fa fa-info-circle"></i>
                                    Password must be at least <strong>6 characters</strong> long.
                                </div>

                                <div class="security-actions">
                                    <button type="button" class="btn-back gradient-border" onclick=toDashboard()>
                                        <i class="fa fa-times"></i> Cancel Changes
                                    </button>

                                    <button type="submit" class="btn-security">
                                        Update Password
                                    </button>
                                </div>

                            </form>

                        </div>
                    </div>

                </div>

                @include('user.include.footer')

            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).on('submit', '#updatePasswordForm', function(e) {
            e.preventDefault();

            let newPassword = $('input[name="new_password"]').val();
            let confirmPassword = $('input[name="confirm_password"]').val();

            if (newPassword !== confirmPassword) {
                toastr.error('New password and confirm password do not match');
                return;
            }

            let formData = new FormData(this);

            $.ajax({
                url: '{{route("user.update_security_pass")}}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        setTimeout(() => {
                            window.location.href = '{{ route("user.login") }}';
                        }, 1500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Something went wrong');
                }
            });
        });

        function toDashboard() {
            window.location.href = "{{ route('user.dashboard') }}";
        }
    </script>

</body>

</html>