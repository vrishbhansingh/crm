<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | CRM</title>

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">


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

        /* LAYOUT */
        .profile-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 24px;
        }

        @media (max-width: 992px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }
        }

        /* COMMON CARD */
        .panel {
            background: var(--card);
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 22px;
        }

        /* LEFT SIDEBAR */
        .profile-panel {
            text-align: center;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #eef2ff;
            margin-bottom: 14px;
        }

        .user-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .user-role {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 14px;
        }

        .status-pill {
            display: inline-block;
            background: #eef2ff;
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 18px;
        }

        .profile-actions a {
            display: block;
            margin-top: 10px;
            padding: 10px;
            border-radius: 10px;
            font-size: 14px;
            text-decoration: none;
            border: 1px solid var(--border);
            color: var(--text);
            transition: 0.2s;
        }

        .profile-actions a.primary {
            background: var(--primary);
            color: #fff;
            border: none;
        }

        .profile-actions a:hover {
            transform: translateY(-1px);
        }

        /* RIGHT CONTENT */
        .section {
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 8px;
        }

        .data-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px 30px;
        }

        @media (max-width: 768px) {
            .data-grid {
                grid-template-columns: 1fr;
            }
        }

        .data-item span {
            font-size: 12px;
            color: var(--muted);
            display: block;
            margin-bottom: 4px;
        }

        .data-item strong {
            font-size: 14px;
            font-weight: 500;
        }

        /* ACTIVITY */
        .activity {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed var(--border);
            font-size: 14px;
        }

        .activity:last-child {
            border-bottom: none;
        }

        .activity time {
            color: var(--muted);
            font-size: 13px;
        }
    </style>
</head>

<body>
    @php
    $lastLogin = Auth::guard('user')->user()->last_login;
    @endphp

    <div class="container-scroller">
        @include('user.include.header')

        <div class="container-fluid page-body-wrapper">
            @include('user.include.sidebar')

            <div class="content-wrapper">

                <h3 class="mb-4">User Profile</h3>

                <div class="profile-layout">

                    <!-- LEFT PROFILE PANEL -->
                    <div class="panel profile-panel">
                        <!-- <img src="{{ asset('images/default-user.png') }}" class="avatar"> -->
                        <img src="{{ asset('images/profile_img.jpg') }}" class="avatar">

                        <div class="user-name">
                            {{ Auth::guard('user')->user()->name }}
                        </div>

                        <div class="user-role">
                            {{ ucfirst(str_replace('_',' ', Auth::guard('user')->user()->role)) }}
                        </div>

                        <div class="status-pill">
                            {{ Auth::guard('user')->user()->status }}
                        </div>

                        <div class="profile-actions">
                            <!-- <a href="#" class="primary">Edit Profile</a> -->
                        </div>
                    </div>

                    <!-- RIGHT DETAILS -->
                    <div>

                        <!-- PERSONAL INFO -->
                        <div class="panel section">
                            <div class="section-title">Personal Information</div>

                            <div class="data-grid">
                                <div class="data-item">
                                    <span>Full Name</span>
                                    <strong>{{ Auth::guard('user')->user()->name }}</strong>
                                </div>

                                <div class="data-item">
                                    <span>Email Address</span>
                                    <strong>{{ Auth::guard('user')->user()->email }}</strong>
                                </div>

                                <div class="data-item">
                                    <span>Phone Number</span>
                                    <strong>{{ Auth::guard('user')->user()->phone ?? 'Not added' }}</strong>
                                </div>

                                <div class="data-item">
                                    <span>Joined On</span>
                                    <strong>{{ Auth::guard('user')->user()->created_at->format('d M Y') }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- ACCOUNT INFO -->
                        <div class="panel section">
                            <div class="section-title">Account Details</div>

                            <div class="data-grid">
                                <div class="data-item">
                                    <span>Username</span>
                                    <strong>{{ Auth::guard('user')->user()->email }}</strong>
                                </div>

                                <div class="data-item">
                                    <span>Role</span>
                                    <strong>{{ ucfirst(str_replace('_',' ', Auth::guard('user')->user()->role)) }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- ACTIVITY -->
                        <div class="panel section">
                            <div class="section-title">Recent Activity</div>

                            <div class="activity">
                                <span>Last Login</span>
                                <time>
                                    {{ $lastLogin ? \Carbon\Carbon::parse($lastLogin)->format('d M Y, h:i A') : 'Never logged in' }}
                                </time>

                            </div>
                            <div class="activity d-none">
                                <span>Last profile update</span>
                                <time>{{ Auth::guard('user')->user()->updated_at->diffForHumans() }}</time>
                            </div>
                        </div>

                    </div>
                </div>

                @include('user.include.footer')
            </div>
        </div>
    </div>

</body>

</html>