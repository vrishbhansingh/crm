<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Profile | CRM</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        :root {
            --bg: #f5f7fb;
            --card: #ffffff;
            --border: #e6e9f0;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #2563eb;
        }

        body {
            background: var(--bg);
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text);
        }

        .crm-page-header {
            background: var(--card);
            padding: 26px 28px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            margin-bottom: 24px;
        }

        .crm-page-header h3 { margin: 0 0 4px; font-weight: 700; font-size: 22px; }
        .crm-page-header p { margin: 0; color: var(--muted); font-size: 14px; }

        .profile-layout { display: grid; grid-template-columns: 300px 1fr; gap: 24px; }

        @media (max-width: 992px) { .profile-layout { grid-template-columns: 1fr; } }

        .panel {
            background: var(--card);
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 22px;
        }

        .profile-panel { text-align: center; }

        .avatar-wrapper { position: relative; width: 120px; height: 120px; margin: 0 auto 14px; }

        .avatar {
            width: 120px; height: 120px; border-radius: 50%;
            object-fit: cover; border: 4px solid #eef2ff; background: #eef2ff;
            display: block;
        }

        .avatar-edit-btn {
            position: absolute; bottom: 2px; right: 2px;
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--primary); color: #fff; border: 3px solid #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 13px; transition: 0.15s;
        }
        .avatar-edit-btn:hover { background: #1d4ed8; }

        .user-name { font-size: 18px; font-weight: 600; margin-bottom: 4px; }
        .user-role { font-size: 13px; color: var(--muted); margin-bottom: 14px; }

        .status-pill {
            display: inline-block; background: #eef2ff; color: var(--primary);
            padding: 6px 14px; border-radius: 999px; font-size: 12px; font-weight: 500;
        }

        .section-title {
            font-size: 15px; font-weight: 600; margin-bottom: 18px;
            border-bottom: 1px solid var(--border); padding-bottom: 10px;
        }

        label { font-size: 12.5px; font-weight: 600; color: var(--muted); }
        .form-control { border-radius: 9px; border: 1px solid var(--border); }
        .form-control:disabled, .form-control[readonly] { background: #f8fafc; color: var(--muted); }

        .data-item span { font-size: 12px; color: var(--muted); display: block; margin-bottom: 4px; }
        .data-item strong { font-size: 14px; font-weight: 500; }

        .activity { display: flex; justify-content: space-between; padding: 10px 0; font-size: 14px; }
        .activity time { color: var(--muted); font-size: 13px; }
    </style>
</head>

<body>
    @php
    $user = Auth::guard('web')->user();
    $avatarUrl = $user->avatar ? asset($user->avatar) : asset('images/profile_img.jpg');
    @endphp

    <div class="container-scroller">
        @include('include.header')

        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="crm-page-header">
                    <h3>My Profile</h3>
                    <p>Update your photo and personal details.</p>
                </div>

                <form id="profileForm">
                    <div class="profile-layout">

                        <!-- LEFT PROFILE PANEL -->
                        <div class="panel profile-panel">
                            <div class="avatar-wrapper">
                                <img src="{{ $avatarUrl }}" class="avatar" id="avatarPreview">
                                <label class="avatar-edit-btn" title="Change photo">
                                    <i class="fa fa-camera"></i>
                                    <input type="file" name="avatar" id="avatarInput" accept="image/png,image/jpeg,image/webp" style="display:none">
                                </label>
                            </div>

                            <div class="user-name">{{ $user->name }}</div>
                            <div class="user-role">{{ $user->getRoleNames()->first() }}</div>
                            <div class="status-pill">{{ $user->status }}</div>
                        </div>

                        <!-- RIGHT DETAILS -->
                        <div>

                            <div class="panel section mb-4">
                                <div class="section-title">Personal Information</div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Full Name</label>
                                        <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Phone Number</label>
                                        <input type="text" name="phone" id="phone" class="form-control" value="{{ $user->phone }}" placeholder="Not added">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Email Address</label>
                                        <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Joined On</label>
                                        <input type="text" class="form-control" value="{{ $user->created_at->format('d M Y') }}" readonly>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-2">
                                    <i id="saveSpinner" class="fa fa-spinner fa-spin" style="display:none"></i> Save Changes
                                </button>
                            </div>

                            <div class="panel section">
                                <div class="section-title">Recent Activity</div>
                                <div class="activity">
                                    <span>Last Login</span>
                                    <time>{{ $user->last_login ? \Carbon\Carbon::parse($user->last_login)->format('d M Y, h:i A') : 'Never logged in' }}</time>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>

                @include('include.footer')
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });

        $(document).on('change', '#avatarInput', function() {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => $('#avatarPreview').attr('src', e.target.result);
            reader.readAsDataURL(file);
        });

        $(document).on('submit', '#profileForm', function(e) {
            e.preventDefault();
            $('#saveSpinner').show();

            const formData = new FormData(this);

            $.ajax({
                url: "{{ route('profile.update') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#saveSpinner').hide();
                    if (response.status) {
                        toastr.success(response.message);
                        if (response.avatar_url) {
                            $('#avatarPreview').attr('src', response.avatar_url);
                            // Keep the header's own avatar in sync without a full reload.
                            $('.crm-profile-avatar').attr('src', response.avatar_url);
                        }
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    $('#saveSpinner').hide();
                    const errors = xhr.responseJSON?.errors;
                    const message = errors ? Object.values(errors)[0][0] : (xhr.responseJSON?.message || 'Something went wrong');
                    toastr.error(message);
                }
            });
        });
    </script>

</body>

</html>
