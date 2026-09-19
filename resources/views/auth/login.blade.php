<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CRM Login</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Inter", sans-serif;
    }

    :root {
      --bg: #f5f7fb;
      --panel: #ffffff;
      --text: #1f2937;
      --muted: #6b7280;
      --border: #d1d5db;
      --primary: #2563eb;
      --primary-dark: #1d4ed8;
    }

    html[data-theme="dark"] {
      --bg: #0f1117;
      --panel: #1a1d2b;
      --text: #eef0f6;
      --muted: #9aa1b5;
      --border: #2a2e40;
      --primary: #6366f1;
      --primary-dark: #93a4fd;
    }

    html, body { min-height: 100vh; background: var(--bg); }

    .auth-shell {
      min-height: 100vh;
      display: grid;
      /* ~66/34 split, matching the reference layout's proportions. */
      grid-template-columns: minmax(0, 2fr) minmax(360px, 1fr);
    }

    /* ===== LEFT: brand / visual panel =====
       Real, live page content (text + cards) over a plain photo, so it
       reflows and rescales with the window instead of being a flat picture.
       Sizes are driven by the panel's own width (cqw) with clamps. */
    .auth-visual {
      position: relative;
      overflow: hidden;
      background: #cfe2f3;
      container-type: inline-size;
      container-name: hero;
    }
    .auth-photo {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: 50% 55%;
    }
    /* Keeps the bottom tagline/quote legible over the darker grass. */
    .auth-visual::after {
      content: "";
      position: absolute;
      inset: 0;
      pointer-events: none;
      background: linear-gradient(to top, rgba(8, 40, 22, 0.42), rgba(8, 40, 22, 0) 30%);
    }

    .auth-hero {
      position: relative;
      z-index: 1;
      min-height: 100vh;
      padding: clamp(22px, 4.2cqw, 64px);
      display: grid;
      grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
      grid-template-rows: 1fr auto;
      column-gap: clamp(16px, 2.4cqw, 40px);
      align-items: center;
    }

    .hero-copy { grid-column: 1; grid-row: 1; align-self: start; padding-top: clamp(8px, 7vh, 90px); }
    .hero-eyebrow {
      font-size: clamp(9.5px, 0.85cqw, 13px);
      font-weight: 500;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      line-height: 1.7;
      color: #5b6b82;
      margin-bottom: clamp(12px, 2.2cqw, 34px);
    }
    .hero-title {
      font-size: clamp(24px, 3.75cqw, 58px);
      font-weight: 800;
      line-height: 1.16;
      letter-spacing: -0.01em;
      color: #0f1f3a;
    }
    .hero-rule {
      display: block;
      width: clamp(28px, 3.1cqw, 46px);
      height: 2px;
      margin: clamp(12px, 1.9cqw, 28px) 0;
      background: #2dd4bf;
    }
    .hero-sub {
      max-width: 30em;
      font-size: clamp(12.5px, 1.32cqw, 20px);
      line-height: 1.6;
      color: #5b6b82;
    }

    .hero-cards {
      grid-column: 2;
      grid-row: 1;
      display: grid;
      grid-template-columns: 1.55fr 1fr;
      gap: clamp(8px, 1.1cqw, 16px);
    }
    .glass {
      background: rgba(255, 255, 255, 0.74);
      -webkit-backdrop-filter: blur(14px) saturate(1.2);
      backdrop-filter: blur(14px) saturate(1.2);
      border: 1px solid rgba(255, 255, 255, 0.75);
      border-radius: clamp(12px, 1.3cqw, 20px);
      box-shadow: 0 18px 40px rgba(30, 58, 100, 0.14);
      padding: clamp(10px, 1.45cqw, 22px);
      color: #0f1f3a;
      min-width: 0;
    }
    .glass h3 { font-size: clamp(12.5px, 1.3cqw, 20px); font-weight: 700; line-height: 1.2; }
    .glass small, .glass .muted { font-size: clamp(9px, 0.82cqw, 12.5px); color: #7b8aa0; font-weight: 400; }
    .glass .up { color: #10b981; font-weight: 600; font-size: clamp(9.5px, 0.9cqw, 13.5px); }

    .sales-card { grid-column: 1 / -1; }
    .sc-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
    .sc-head small { display: block; margin-top: 3px; }
    .pill {
      background: #dcfce7; color: #10b981; font-weight: 700;
      font-size: clamp(11px, 1.15cqw, 17px);
      padding: 0.28em 0.8em; border-radius: 8px; white-space: nowrap;
    }
    .sc-body { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: clamp(10px, 2cqw, 30px); align-items: end; margin-top: clamp(6px, 1cqw, 16px); }
    .sc-kpi strong { display: block; font-size: clamp(18px, 2.05cqw, 32px); font-weight: 800; line-height: 1.1; }
    .sc-kpi .up { display: block; margin-top: 4px; }
    .sc-kpi .muted { display: block; margin-top: 2px; }
    .sc-chart svg { display: block; width: 100%; height: clamp(34px, 4.6cqw, 70px); }
    .sc-days { display: flex; justify-content: space-between; margin-top: 3px; }
    .sc-days span { font-size: clamp(7px, 0.62cqw, 10px); color: #a3afc0; }

    .stat-card { grid-column: 1; }
    .st-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; flex-wrap: wrap; }
    .st-head small { display: block; margin-top: 3px; }
    .st-legend { display: flex; gap: 10px; align-items: center; font-size: clamp(8.5px, 0.78cqw, 12px); color: #475569; }
    .st-legend i { display: inline-block; width: 0.8em; height: 0.8em; border-radius: 50%; margin-right: 4px; vertical-align: -0.05em; }
    .dot-rev { background: #14d3a5; }
    .dot-sal { background: #3b82f6; }
    .st-chart { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 8px; margin-top: clamp(8px, 1.2cqw, 18px); }
    .st-y { display: flex; flex-direction: column; justify-content: space-between; align-items: flex-end; height: clamp(90px, 12.5cqw, 190px); }
    .st-y span { font-size: clamp(7px, 0.62cqw, 10px); color: #7b8aa0; line-height: 1; }
    .st-plot {
      position: relative; display: flex; justify-content: space-around; align-items: flex-end;
      height: clamp(90px, 12.5cqw, 190px);
      background-image: repeating-linear-gradient(to bottom, transparent 0, transparent calc(16.66% - 1px), rgba(148,163,184,.35) calc(16.66% - 1px), rgba(148,163,184,.35) 16.66%);
      background-size: 100% 100%;
    }
    .st-group { display: flex; align-items: flex-end; gap: 2px; height: 100%; }
    .st-group b { display: block; width: clamp(5px, 0.62cqw, 10px); border-radius: 2px 2px 0 0; }
    .st-group b:first-child { background: linear-gradient(to bottom, #14d3a5, #38bdf8); }
    .st-group b:last-child { background: linear-gradient(to bottom, #3b9cf6, #3b5bf0); }
    .st-x { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 8px; margin-top: 4px; }
    .st-x div { display: flex; justify-content: space-around; }
    .st-x span { font-size: clamp(7.5px, 0.7cqw, 11px); color: #7b8aa0; }
    .st-x .st-spacer { visibility: hidden; font-size: clamp(7px, 0.62cqw, 10px); }

    .hero-mini { grid-column: 2; display: grid; grid-template-rows: 1fr 1fr; gap: clamp(8px, 1.1cqw, 16px); }
    .mini-card { display: flex; align-items: center; gap: clamp(8px, 1.1cqw, 16px); }
    .mini-ic {
      flex: none; display: grid; place-items: center;
      width: clamp(28px, 3.5cqw, 54px); height: clamp(28px, 3.5cqw, 54px);
      border-radius: clamp(8px, 0.95cqw, 14px); font-size: clamp(13px, 1.6cqw, 24px);
    }
    .mini-ic.blue { background: linear-gradient(135deg, #93c5fd, #60a5fa); color: #1d4ed8; }
    .mini-ic.teal { background: linear-gradient(135deg, #6ee7b7, #2dd4bf); color: #0f766e; }
    .mini-card small { display: block; }
    .mini-card strong { display: block; font-size: clamp(15px, 1.8cqw, 28px); font-weight: 800; line-height: 1.15; margin: 2px 0; }

    .hero-foot {
      grid-column: 1 / -1; grid-row: 2;
      display: flex; justify-content: space-between; align-items: flex-end; gap: 16px;
      color: rgba(255, 255, 255, 0.92);
      padding-top: clamp(14px, 3vh, 40px);
    }
    .hero-tagline {
      font-size: clamp(9px, 0.82cqw, 13px); letter-spacing: 0.3em; text-transform: uppercase; font-weight: 500;
    }
    .hero-tagline::after, .hero-quote::after {
      content: ""; display: block; width: 26px; height: 1px; margin-top: 10px; background: rgba(255,255,255,.7);
    }
    .hero-quote { font-size: clamp(9.5px, 0.92cqw, 14px); line-height: 1.55; max-width: 24em; }

    /* Narrower panel: drop the busiest card and stack the rest under the copy. */
    @container hero (max-width: 860px) {
      .auth-hero { grid-template-columns: 1fr; grid-template-rows: auto auto auto; row-gap: clamp(16px, 3cqw, 28px); align-items: start; }
      .hero-copy, .hero-cards { grid-column: 1; }
      .hero-copy { grid-row: 1; padding-top: 0; }
      .hero-cards { grid-row: 2; grid-template-columns: 1fr 1fr; }
      .stat-card { display: none; }
      .hero-mini { grid-column: 1 / -1; grid-template-rows: none; grid-template-columns: 1fr 1fr; }
      .hero-foot { grid-row: 3; align-self: end; }
      .hero-title { font-size: clamp(24px, 5.6cqw, 40px); }
      .hero-sub { font-size: clamp(12.5px, 2.1cqw, 16px); }
      .hero-eyebrow { font-size: clamp(9.5px, 1.3cqw, 12px); }
      .glass h3 { font-size: clamp(13px, 2.1cqw, 17px); }
      .glass small { font-size: clamp(9.5px, 1.4cqw, 12px); }
      .sc-kpi strong { font-size: clamp(19px, 3.2cqw, 28px); }
      .mini-card strong { font-size: clamp(16px, 2.6cqw, 22px); }
      .mini-ic { width: clamp(30px, 5cqw, 42px); height: clamp(30px, 5cqw, 42px); font-size: clamp(14px, 2.2cqw, 19px); }
      .hero-tagline, .hero-quote { font-size: clamp(9.5px, 1.4cqw, 12px); }
      .sc-days span { font-size: clamp(8px, 1.1cqw, 10px); }
      .sc-chart svg { height: clamp(36px, 6cqw, 56px); }
    }
    /* Short windows: keep everything on screen by dropping the tallest card. */
    @media (max-height: 640px) and (min-width: 901px) {
      .stat-card { display: none; }
      .hero-mini { grid-template-rows: none; grid-template-columns: 1fr 1fr; grid-column: 1 / -1; }
    }

    /* ===== RIGHT: form panel ===== */
    .auth-form-panel {
      position: relative;
      overflow: hidden;
      background: var(--panel);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 28px;
    }
    /* Soft brand-colored glows top-right and bottom-left so the panel
       reads as designed rather than a flat white rectangle. */
    .auth-form-panel::before,
    .auth-form-panel::after {
      content: "";
      position: absolute;
      z-index: 0;
      border-radius: 50%;
      pointer-events: none;
      filter: blur(60px);
    }
    .auth-form-panel::before {
      width: 280px;
      height: 280px;
      top: -90px;
      right: -70px;
      background: radial-gradient(circle, rgba(37, 99, 235, 0.16), transparent 70%);
    }
    .auth-form-panel::after {
      width: 320px;
      height: 320px;
      bottom: -110px;
      left: -90px;
      background: radial-gradient(circle, rgba(99, 102, 241, 0.12), transparent 70%);
    }

    .auth-form-wrap {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 380px;
      animation: slideUp 0.5s ease;
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(18px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .auth-form-logo {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 6px;
    }
    .auth-form-logo img { height: 26px; }
    .auth-form-tagline { font-size: 12px; color: var(--muted); margin-bottom: 24px; }

    .auth-title {
      font-size: 26px;
      font-weight: 800;
      color: var(--text);
      margin-bottom: 6px;
    }

    .auth-subtitle {
      font-size: 14px;
      color: var(--muted);
      margin-bottom: 22px;
    }

    .form-group { margin-bottom: 16px; }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 6px;
    }

    .form-control {
      width: 100%;
      height: 46px;
      border-radius: 10px;
      border: 1px solid var(--border);
      background: var(--panel);
      color: var(--text);
      padding: 0 14px;
      font-size: 14.5px;
      transition: 0.2s;
    }
    .form-control::placeholder { color: var(--muted); }

    .form-control:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    .login-btn {
      width: 100%;
      height: 48px;
      border-radius: 10px;
      background: var(--primary);
      border: none;
      color: #fff;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 2px;
      transition: 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .login-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 22px rgba(99, 102, 241, 0.3);
    }

    .auth-signup-line {
      text-align: center;
      margin-top: 20px;
      font-size: 13.5px;
      color: var(--muted);
    }
    .auth-signup-line a { color: var(--primary); font-weight: 600; text-decoration: none; }

    .footer-text {
      text-align: center;
      font-size: 12px;
      color: var(--muted);
      margin-top: 28px;
    }

    .password-wrapper { position: relative; }
    .password-wrapper .form-control { padding-right: 42px; }

    .form-input-icon { position: relative; }
    .form-input-icon .form-control { padding-left: 42px; }
    .form-input-icon .field-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted);
      font-size: 14px;
      pointer-events: none;
    }

    .eye-btn {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: transparent;
      border: none;
      cursor: pointer;
      color: var(--muted);
      font-size: 14px;
    }
    .eye-btn:hover { color: var(--text); }
    .eye-btn:focus { outline: none; }

    .form-options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 10px;
      margin-bottom: 4px;
    }
    .remember-check {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12.5px;
      color: var(--text);
      cursor: pointer;
      user-select: none;
    }
    .remember-check input { width: 14px; height: 14px; accent-color: var(--primary); cursor: pointer; }

    .forgot-link { margin: 0; }
    .forgot-link a { font-size: 12.5px; color: var(--primary); text-decoration: none; }

    /* Tablet / phone: stack. The visual becomes a compact banner above the
       form with just the headline copy (cards + footer text are too busy
       for this width). */
    @media (max-width: 900px) {
      .auth-shell { grid-template-columns: 1fr; }
      .auth-hero { min-height: 0; padding: clamp(22px, 5vw, 44px) clamp(20px, 5vw, 44px) clamp(26px, 6vw, 48px); }
      .hero-cards, .hero-foot { display: none; }
      .hero-title { font-size: clamp(24px, 6.4vw, 40px); }
      .hero-sub { font-size: clamp(13px, 2.4vw, 16px); }
      .hero-eyebrow { font-size: clamp(9.5px, 1.6vw, 12px); margin-bottom: 12px; }
      .auth-photo { object-position: 50% 30%; }
      .auth-visual::after { display: none; }
      .auth-form-panel { padding: 32px 22px 40px; }
    }
    @media (max-width: 480px) {
      .hero-sub { display: none; }
      .hero-rule { margin-bottom: 0; }
    }
  </style>
</head>

<body>

  <div class="auth-shell">

    <div class="auth-visual">
      <img class="auth-photo" src="{{ asset('images/login_photo.jpg') }}" alt="" fetchpriority="high">

      <div class="auth-hero">
        <div class="hero-copy">
          <p class="hero-eyebrow">Customer relationships<br>for a brighter tomorrow</p>
          <h2 class="hero-title">Your CRM,<br>Smarter and Simpler</h2>
          <span class="hero-rule"></span>
          <p class="hero-sub">Manage leads, track performance, and grow your business — all in one powerful yet intuitive platform.</p>
        </div>

        <div class="hero-cards">
          <div class="glass sales-card">
            <div class="sc-head">
              <div><h3>Sales</h3><small>This Month</small></div>
              <span class="pill">+12%</span>
            </div>
            <div class="sc-body">
              <div class="sc-kpi">
                <strong>$6,324</strong>
                <span class="up">&uarr; 12%</span>
                <span class="muted">vs last month</span>
              </div>
              <div class="sc-chart">
                <svg viewBox="0 0 300 80" preserveAspectRatio="none" aria-hidden="true">
                  <defs>
                    <linearGradient id="scFill" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0" stop-color="#2dd4bf" stop-opacity=".35"/>
                      <stop offset="1" stop-color="#2dd4bf" stop-opacity="0"/>
                    </linearGradient>
                  </defs>
                  <path d="M0,72 C20,68 28,44 55,40 C80,37 92,56 118,52 C146,48 158,32 185,34 C212,36 222,18 250,12 C270,8 285,18 300,16 L300,80 L0,80 Z" fill="url(#scFill)"/>
                  <path d="M0,72 C20,68 28,44 55,40 C80,37 92,56 118,52 C146,48 158,32 185,34 C212,36 222,18 250,12 C270,8 285,18 300,16" fill="none" stroke="#14d3a5" stroke-width="2" vector-effect="non-scaling-stroke" stroke-linecap="round"/>
                </svg>
                <div class="sc-days"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
              </div>
            </div>
          </div>

          <div class="glass stat-card">
            <div class="st-head">
              <div><h3>Statistic</h3><small>Income and Expenses</small></div>
              <div class="st-legend"><span><i class="dot-rev"></i>Revenue</span><span><i class="dot-sal"></i>Sales</span></div>
            </div>
            <div class="st-chart">
              <div class="st-y"><span>$14k</span><span>$12k</span><span>$10k</span><span>$8k</span><span>$4k</span><span>$2k</span><span>0</span></div>
              <div class="st-plot">
                <div class="st-group"><b style="height:55%"></b><b style="height:42%"></b></div>
                <div class="st-group"><b style="height:42%"></b><b style="height:45%"></b></div>
                <div class="st-group"><b style="height:32%"></b><b style="height:51%"></b></div>
                <div class="st-group"><b style="height:67%"></b><b style="height:51%"></b></div>
                <div class="st-group"><b style="height:99%"></b><b style="height:76%"></b></div>
                <div class="st-group"><b style="height:45%"></b><b style="height:56%"></b></div>
              </div>
            </div>
            <div class="st-x">
              <span class="st-spacer">$14k</span>
              <div><span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>May</span><span>Jun</span></div>
            </div>
          </div>

          <div class="hero-mini">
            <div class="glass mini-card">
              <span class="mini-ic blue"><i class="fa fa-user-group"></i></span>
              <div><small>Active Leads</small><strong>1,428</strong><span class="up">&uarr; 8%</span></div>
            </div>
            <div class="glass mini-card">
              <span class="mini-ic teal"><i class="fa fa-bullseye"></i></span>
              <div><small>Conversion Rate</small><strong>24.5%</strong><span class="up">&uarr; 3%</span></div>
            </div>
          </div>
        </div>

        <div class="hero-foot">
          <span class="hero-tagline">People &middot; Process &middot; Growth</span>
          <p class="hero-quote">&ldquo;Stronger Customer Relationships<br>Build Brighter Tomorrows.&rdquo;</p>
        </div>
      </div>
    </div>

    <div class="auth-form-panel">
      <div class="auth-form-wrap">

        <div class="auth-form-logo">
          <img src="{{ asset('images/logo.svg') }}" alt="CRMS" onerror="this.style.display='none'">
        </div>
        <p class="auth-form-tagline">Smarter and Simpler CRM</p>

        <h1 class="auth-title">Welcome Back</h1>
        <p class="auth-subtitle">Sign in to continue to your CRM account.</p>

        <form id="loginSubmit" method="POST" action="{{ $submitRoute }}">
          @csrf
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <div class="form-input-icon">
              <i class="fa fa-envelope field-icon"></i>
              <input type="email" name="email" class="form-control" placeholder="you@company.com" required autofocus>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <div class="password-wrapper form-input-icon">
              <i class="fa fa-lock field-icon"></i>
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

          <div class="form-options">
            <label class="remember-check">
              <input type="checkbox">
              Remember me
            </label>
            <div class="forgot-link">
              <a href="{{ route('password.request') }}">Forgot password?</a>
            </div>
          </div>

          <button type="submit" class="login-btn">
            <i id="fa_spinner" class="fa fa-spinner fa-spin" style="display:none;"></i>
            <span id="btn-text">Sign In</span>
            <i class="fa fa-arrow-right"></i>
          </button>
        </form>

        <p class="auth-signup-line">New company? <a href="{{ route('register') }}">Create a workspace</a></p>

        <div class="footer-text">
          &copy; {{ date('Y') }} {{ config('app.name', 'CRM') }}. All rights reserved.
        </div>

      </div>
    </div>

  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>

  <script>
    // Applied immediately (not inside DOMContentLoaded) so the page paints
    // in the right theme on first load instead of flashing light-then-dark
    // — same pattern used on every page after login (include/header.blade.php).
    (function() {
      if (localStorage.getItem('crm-theme') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
      }
    })();

    function showToast(message, type = 'success') {
      toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000"
      };
      toastr[type] ? toastr[type](message) : toastr.info(message);
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
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
          $('#fa_spinner').hide();
          if (response.status) {
            toastr.success(response.message);
            setTimeout(() => {
              window.location.href = response.location;
            }, 800);
          } else {
            toastr.error(response.message);
          }
        },
        error: function(xhr) {
          $('#fa_spinner').hide();
          const message = xhr.responseJSON?.message || 'Something went wrong!';
          toastr.error(message);
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
