<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Profile | CRM</title>

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        .card {
            border-radius: 14px;
        }

        .form-control {
            border-radius: 8px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #4b49ac;
            box-shadow: 0 0 0 2px rgba(75, 73, 172, 0.15);
        }

        .btn-primary {
            font-weight: 600;
        }

        .alert {
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <div class="container-scroller">
        @include('admin.include.header')

        <div class="container-fluid page-body-wrapper">
            @include('admin.include.sidebar')

            <div class="content-wrapper">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">

                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">

                                <!-- Header -->
                                <div class="text-center mb-4">
                                    <i class="fa fa-lock fa-2x text-primary mb-2"></i>
                                    <h4 class="font-weight-bold mb-1">Update Password</h4>
                                    <p class="text-muted small mb-0">
                                        Keep your account secure by updating your password regularly
                                    </p>
                                </div>

                                <!-- Update Password Form -->
                                <form id="updatePasswordForm" method="post">

                                    @csrf

                                    <!-- Current Password -->
                                    <div class="form-group">
                                        <label>Current Password</label>
                                        <input type="password"
                                            name="current_password"
                                            class="form-control"
                                            placeholder="Enter current password"
                                            required>
                                    </div>

                                    <!-- New Password -->
                                    <div class="form-group">
                                        <label>New Password</label>
                                        <input type="password"
                                            name="new_password"
                                            class="form-control"
                                            placeholder="Enter new password"
                                            required>
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="form-group">
                                        <label>Confirm New Password</label>
                                        <input type="password"
                                            name="confirm_password"
                                            class="form-control"
                                            placeholder="Confirm new password"
                                            required>
                                    </div>

                                    <!-- Password Tips -->
                                    <div class="alert alert-light small mb-3">
                                        <i class="fa fa-info-circle text-primary"></i>
                                        Password should be at least <strong>8 characters</strong> long.
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ url()->previous() }}" class="btn btn-light">
                                            <i class="fa fa-arrow-left"></i> Back
                                        </a>

                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fa fa-save"></i> Update Password
                                        </button>
                                    </div>

                                </form>

                            </div>
                        </div>

                    </div>
                </div>


                @include('admin.include.footer')
            </div>
        </div>
    </div>




    <!-- jQuery (ONLY ONCE) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS (REQUIRED for modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>


    <script>
        $(document).on('submit', '#updatePasswordForm', function(e) {
            e.preventDefault();

            let newPassword = $('input[name="new_password"]').val();
            let confirmPassword = $('input[name="confirm_password"]').val();

            // ✅ Frontend password match check
            if (newPassword !== confirmPassword) {
                toastr.error('New password and confirm password do not match');
                return;
            }

            let formData = new FormData(this);

            $.ajax({
                url: '{{ route("admin.update_pass") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,

                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);

                        // logout after password update
                        setTimeout(() => {
                            window.location.href = '{{ route("admin.login") }}';
                        }, 1500);

                    } else {
                        toastr.error(response.message);
                    }
                },

                error: function(xhr) {
                    toastr.error('Something went wrong');
                }
            });
        });
    </script>


</body>

</html>