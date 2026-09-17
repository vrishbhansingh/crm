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
      grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
    }

    /* ===== LEFT: brand / visual panel — rotating photo slideshow ===== */
    .auth-visual {
      position: relative;
      overflow: hidden;
    }

    .auth-slide {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 56px 64px;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.9s ease;
    }
    .auth-slide.is-active { opacity: 1; visibility: visible; }

    .auth-slide-bg {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    /* Soft light scrim behind the copy only (upper-left) — keeps the photo
       bright like the reference mockups instead of the old dark overlay,
       while still guaranteeing the headline stays legible over any photo. */
    .auth-slide-scrim {
      position: absolute;
      inset: 0;
      background: linear-gradient(115deg, rgba(255, 255, 255, 0.55) 0%, rgba(255, 255, 255, 0.16) 34%, rgba(255, 255, 255, 0) 56%);
    }
    html[data-theme="dark"] .auth-slide-scrim {
      background: linear-gradient(115deg, rgba(10, 12, 20, 0.72) 0%, rgba(10, 12, 20, 0.34) 34%, rgba(10, 12, 20, 0) 56%);
    }

    .auth-slide-content { position: relative; z-index: 1; max-width: 400px; }

    .auth-visual-logo {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 32px;
    }
    .auth-visual-logo img { height: 28px; width: auto; }
    .auth-visual-logo span { font-size: 19px; font-weight: 800; letter-spacing: 0.02em; color: #0f172a; }
    html[data-theme="dark"] .auth-visual-logo span { color: #eef0f6; }

    .auth-slide-eyebrow {
      display: block;
      font-size: 11.5px;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: #475569;
      margin-bottom: 14px;
    }
    html[data-theme="dark"] .auth-slide-eyebrow { color: #cbd5e1; }

    .auth-slide-content h2 {
      font-size: 32px;
      font-weight: 800;
      line-height: 1.25;
      color: #0f172a;
      margin-bottom: 12px;
    }
    .auth-slide-content > p {
      font-size: 14.5px;
      color: #334155;
      line-height: 1.6;
      max-width: 320px;
    }
    html[data-theme="dark"] .auth-slide-content h2,
    html[data-theme="dark"] .auth-slide-content > p { color: #f1f5f9; }

    /* Floating KPI / stat cards, stacked vertically over the photo */
    .auth-card-stack {
      position: absolute;
      z-index: 1;
      top: 12%;
      right: 6%;
      width: 240px;
      display: flex;
      flex-direction: column;
      gap: 14px;
    }
    .auth-slide-card {
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(6px);
      border-radius: 14px;
      box-shadow: 0 14px 34px rgba(15, 23, 42, 0.16);
      padding: 14px 16px;
    }
    html[data-theme="dark"] .auth-slide-card { background: rgba(26, 29, 43, 0.92); }

    .auth-card-kpi-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
    .auth-card-kpi-label { display: block; font-size: 13px; font-weight: 700; color: #0f172a; }
    .auth-card-kpi-top small { display: block; font-size: 10.5px; color: #94a3b8; margin-top: 1px; }
    html[data-theme="dark"] .auth-card-kpi-label { color: #eef0f6; }
    .auth-card-badge { font-size: 10.5px; font-weight: 700; padding: 2px 7px; border-radius: 999px; white-space: nowrap; }
    .auth-card-kpi-value { font-size: 21px; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    html[data-theme="dark"] .auth-card-kpi-value { color: #eef0f6; }
    .auth-sparkline { width: 100%; height: 30px; display: block; }
    .auth-sparkline polyline { fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .auth-sparkline path { stroke: none; }

    /* Lead-sources donut card */
    .auth-card-donut-head { font-size: 12.5px; font-weight: 700; color: #0f172a; margin-bottom: 12px; }
    html[data-theme="dark"] .auth-card-donut-head { color: #eef0f6; }
    .auth-card-donut { display: flex; align-items: center; gap: 14px; }
    .auth-donut-ring { position: relative; width: 72px; height: 72px; border-radius: 50%; flex-shrink: 0; }
    .auth-donut-ring::before {
      content: ""; position: absolute; inset: 12px; border-radius: 50%; background: #ffffff;
    }
    html[data-theme="dark"] .auth-donut-ring::before { background: #1a1d2b; }
    .auth-donut-center {
      position: absolute; inset: 12px; z-index: 1;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .auth-donut-center strong { font-size: 13px; font-weight: 800; color: #0f172a; line-height: 1.1; }
    html[data-theme="dark"] .auth-donut-center strong { color: #eef0f6; }
    .auth-donut-center span { font-size: 7.5px; color: #94a3b8; text-align: center; }
    .auth-donut-legend { flex: 1; display: flex; flex-direction: column; gap: 5px; min-width: 0; }
    .auth-donut-legend-row { display: flex; align-items: center; justify-content: space-between; gap: 6px; font-size: 10px; color: #475569; }
    html[data-theme="dark"] .auth-donut-legend-row { color: #cbd5e1; }
    .auth-donut-legend-row .dot-label { display: flex; align-items: center; gap: 6px; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .auth-donut-legend-row i { width: 7px; height: 7px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
    .auth-donut-legend-row b { font-weight: 700; color: #0f172a; flex-shrink: 0; }
    html[data-theme="dark"] .auth-donut-legend-row b { color: #eef0f6; }

    .auth-card-tiles { display: flex; gap: 10px; }
    .auth-tile { flex: 1; text-align: left; }
    .auth-tile-icon {
      width: 26px; height: 26px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
      font-size: 11px; margin-bottom: 8px;
    }
    .auth-tile-value { font-size: 15px; font-weight: 800; color: #0f172a; }
    html[data-theme="dark"] .auth-tile-value { color: #eef0f6; }
    .auth-tile-label { font-size: 10px; color: #94a3b8; margin-top: 2px; }
    .auth-tile-trend { font-size: 10px; font-weight: 700; margin-top: 3px; }

    /* Persistent feature row — identical on every slide, so it lives once
       above the crossfading backgrounds rather than being duplicated per slide. */
    .auth-visual-features {
      position: absolute;
      z-index: 2;
      left: 64px;
      bottom: 32px;
      display: flex;
      gap: 32px;
    }
    .auth-feature { display: flex; flex-direction: column; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; color: #1e293b; }
    html[data-theme="dark"] .auth-feature { color: #f1f5f9; }
    .auth-feature-icon {
      width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
      background: rgba(255, 255, 255, 0.85); color: #2563eb; font-size: 15px; box-shadow: 0 6px 16px rgba(15, 23, 42, 0.12);
    }
    html[data-theme="dark"] .auth-feature-icon { background: rgba(30, 33, 45, 0.85); }

    /* ===== RIGHT: form panel ===== */
    .auth-form-panel {
      position: relative;
      background: var(--panel);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 28px;
    }

    .auth-form-wrap {
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
      margin-bottom: 28px;
    }

    .form-group { margin-bottom: 18px; }

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
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      border: none;
      color: #fff;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 6px;
      transition: 0.2s;
    }

    .login-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 22px rgba(99, 102, 241, 0.3);
    }
    .login-btn i { margin-right: 6px; }

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

    .forgot-link { text-align: right; margin-top: 8px; }
    .forgot-link a { font-size: 12.5px; color: var(--primary); text-decoration: none; }

    @media (max-width: 900px) {
      .auth-shell { grid-template-columns: 1fr; }
      .auth-visual { display: none; }
    }
  </style>
</head>

<body>

  <div class="auth-shell">

    <div class="auth-visual">

      <div class="auth-slide is-active">
        <img class="auth-slide-bg" src="{{ asset('images/login_bg.avif') }}" alt="">
        <div class="auth-slide-scrim"></div>
        <div class="auth-slide-content">
          <div class="auth-visual-logo">
            <img src="{{ asset('images/favicon.svg') }}" alt="" onerror="this.style.display='none'">
            <span>CRMS</span>
          </div>
          <span class="auth-slide-eyebrow">Customer relationships<br>for a brighter tomorrow</span>
          <h2>Your CRM,<br>Smarter and Simpler</h2>
          <p>Manage leads, track performance, and grow your business — all in one powerful yet intuitive platform.</p>
        </div>

        <div class="auth-card-stack">
          <div class="auth-slide-card auth-card-kpi">
            <div class="auth-card-kpi-top">
              <div><span class="auth-card-kpi-label">Sales</span><small>This Month</small></div>
              <span class="auth-card-badge" style="background:#dcfce7;color:#16a34a;">&uarr; 12%</span>
            </div>
            <div class="auth-card-kpi-value">$6,324</div>
            <svg class="auth-sparkline" viewBox="0 0 100 30" preserveAspectRatio="none">
              <path d="M0,24 L15,20 L30,22 L45,14 L60,16 L75,6 L100,4 L100,30 L0,30 Z" style="fill:#16a34a;opacity:.12;"></path>
              <polyline points="0,24 15,20 30,22 45,14 60,16 75,6 100,4" style="stroke:#16a34a;"></polyline>
            </svg>
          </div>

          <div class="auth-slide-card auth-card-donut">
            <div>
              <div class="auth-card-donut-head">Lead Sources</div>
              <div class="auth-donut-ring" style="background: conic-gradient(#14b8a6 0% 38%, #3b82f6 38% 62%, #8b5cf6 62% 82%, #ec4899 82% 94%, #e2e8f0 94% 100%);">
                <div class="auth-donut-center"><strong>1,428</strong><span>Total Leads</span></div>
              </div>
            </div>
            <div class="auth-donut-legend">
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#14b8a6"></i>Website</span><b>38%</b></div>
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#3b82f6"></i>Referral</span><b>24%</b></div>
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#8b5cf6"></i>Social Media</span><b>20%</b></div>
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#ec4899"></i>Campaign</span><b>12%</b></div>
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#e2e8f0"></i>Other</span><b>6%</b></div>
            </div>
          </div>

          <div class="auth-slide-card auth-card-tiles">
            <div class="auth-tile">
              <div class="auth-tile-icon" style="background:#dcfce7;color:#16a34a;"><i class="fa fa-users"></i></div>
              <div class="auth-tile-value">1,428</div>
              <div class="auth-tile-label">Active Leads</div>
              <div class="auth-tile-trend" style="color:#16a34a;">&uarr; 8%</div>
            </div>
            <div class="auth-tile">
              <div class="auth-tile-icon" style="background:#dcfce7;color:#16a34a;"><i class="fa fa-bullseye"></i></div>
              <div class="auth-tile-value">24.5%</div>
              <div class="auth-tile-label">Conversion Rate</div>
              <div class="auth-tile-trend" style="color:#16a34a;">&uarr; 3%</div>
            </div>
            <div class="auth-tile">
              <div class="auth-tile-icon" style="background:#dcfce7;color:#16a34a;"><i class="fa fa-chart-line"></i></div>
              <div class="auth-tile-value">$12,430</div>
              <div class="auth-tile-label">Total Revenue</div>
              <div class="auth-tile-trend" style="color:#16a34a;">&uarr; 11%</div>
            </div>
          </div>
        </div>
      </div>

      <div class="auth-slide">
        <img class="auth-slide-bg" src="{{ asset('images/login_bg.avif') }}" alt="">
        <div class="auth-slide-scrim"></div>
        <div class="auth-slide-content">
          <div class="auth-visual-logo">
            <img src="{{ asset('images/favicon.svg') }}" alt="" onerror="this.style.display='none'">
            <span>CRMS</span>
          </div>
          <span class="auth-slide-eyebrow">Turn conversations<br>into opportunities</span>
          <h2>Your CRM,<br>Smarter and Simpler</h2>
          <p>Manage leads, track performance, and grow your business — all in one powerful yet intuitive platform.</p>
        </div>

        <div class="auth-card-stack">
          <div class="auth-slide-card auth-card-kpi">
            <div class="auth-card-kpi-top">
              <div><span class="auth-card-kpi-label">Total Leads</span><small>This Month</small></div>
              <span class="auth-card-badge" style="background:#ede9fe;color:#7c3aed;">&uarr; 18%</span>
            </div>
            <div class="auth-card-kpi-value">2,428</div>
            <svg class="auth-sparkline" viewBox="0 0 100 30" preserveAspectRatio="none">
              <path d="M0,26 L15,22 L30,24 L45,12 L60,18 L75,8 L100,6 L100,30 L0,30 Z" style="fill:#7c3aed;opacity:.12;"></path>
              <polyline points="0,26 15,22 30,24 45,12 60,18 75,8 100,6" style="stroke:#7c3aed;"></polyline>
            </svg>
          </div>

          <div class="auth-slide-card auth-card-donut">
            <div>
              <div class="auth-card-donut-head">Lead Sources</div>
              <div class="auth-donut-ring" style="background: conic-gradient(#14b8a6 0% 38%, #3b82f6 38% 62%, #8b5cf6 62% 82%, #ec4899 82% 94%, #e2e8f0 94% 100%);">
                <div class="auth-donut-center"><strong>1,760</strong><span>Total Leads</span></div>
              </div>
            </div>
            <div class="auth-donut-legend">
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#14b8a6"></i>Website</span><b>38%</b></div>
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#3b82f6"></i>Referral</span><b>24%</b></div>
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#8b5cf6"></i>Social Media</span><b>20%</b></div>
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#ec4899"></i>Campaign</span><b>12%</b></div>
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#e2e8f0"></i>Other</span><b>6%</b></div>
            </div>
          </div>

          <div class="auth-slide-card auth-card-tiles">
            <div class="auth-tile">
              <div class="auth-tile-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fa fa-users"></i></div>
              <div class="auth-tile-value">1,428</div>
              <div class="auth-tile-label">Active Leads</div>
              <div class="auth-tile-trend" style="color:#7c3aed;">&uarr; 8%</div>
            </div>
            <div class="auth-tile">
              <div class="auth-tile-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fa fa-bullseye"></i></div>
              <div class="auth-tile-value">24.5%</div>
              <div class="auth-tile-label">Conversion Rate</div>
              <div class="auth-tile-trend" style="color:#7c3aed;">&uarr; 3%</div>
            </div>
            <div class="auth-tile">
              <div class="auth-tile-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fa fa-chart-line"></i></div>
              <div class="auth-tile-value">$12,430</div>
              <div class="auth-tile-label">Total Revenue</div>
              <div class="auth-tile-trend" style="color:#7c3aed;">&uarr; 11%</div>
            </div>
          </div>
        </div>
      </div>

      <div class="auth-slide">
        <img class="auth-slide-bg" src="{{ asset('images/login_bg.avif') }}" alt="">
        <div class="auth-slide-scrim"></div>
        <div class="auth-slide-content">
          <div class="auth-visual-logo">
            <img src="{{ asset('images/favicon.svg') }}" alt="" onerror="this.style.display='none'">
            <span>CRMS</span>
          </div>
          <span class="auth-slide-eyebrow">Build lasting relationships<br>drive real growth</span>
          <h2>Your CRM,<br>Smarter and Simpler</h2>
          <p>Manage leads, track performance, and grow your business — all in one powerful yet intuitive platform.</p>
        </div>

        <div class="auth-card-stack">
          <div class="auth-slide-card auth-card-kpi">
            <div class="auth-card-kpi-top">
              <div><span class="auth-card-kpi-label">Revenue</span><small>This Month</small></div>
              <span class="auth-card-badge" style="background:#dbeafe;color:#2563eb;">&uarr; 15%</span>
            </div>
            <div class="auth-card-kpi-value">$24,580</div>
            <svg class="auth-sparkline" viewBox="0 0 100 30" preserveAspectRatio="none">
              <path d="M0,25 L15,21 L30,23 L45,15 L60,17 L75,7 L100,5 L100,30 L0,30 Z" style="fill:#2563eb;opacity:.12;"></path>
              <polyline points="0,25 15,21 30,23 45,15 60,17 75,7 100,5" style="stroke:#2563eb;"></polyline>
            </svg>
          </div>

          <div class="auth-slide-card auth-card-donut">
            <div>
              <div class="auth-card-donut-head">Lead Sources</div>
              <div class="auth-donut-ring" style="background: conic-gradient(#14b8a6 0% 38%, #3b82f6 38% 62%, #8b5cf6 62% 82%, #ec4899 82% 94%, #e2e8f0 94% 100%);">
                <div class="auth-donut-center"><strong>2,050</strong><span>Total Leads</span></div>
              </div>
            </div>
            <div class="auth-donut-legend">
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#14b8a6"></i>Website</span><b>38%</b></div>
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#3b82f6"></i>Referral</span><b>24%</b></div>
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#8b5cf6"></i>Social Media</span><b>20%</b></div>
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#ec4899"></i>Campaign</span><b>12%</b></div>
              <div class="auth-donut-legend-row"><span class="dot-label"><i style="background:#e2e8f0"></i>Other</span><b>6%</b></div>
            </div>
          </div>

          <div class="auth-slide-card auth-card-tiles">
            <div class="auth-tile">
              <div class="auth-tile-icon" style="background:#dbeafe;color:#2563eb;"><i class="fa fa-file-lines"></i></div>
              <div class="auth-tile-value">892</div>
              <div class="auth-tile-label">New Leads</div>
              <div class="auth-tile-trend" style="color:#2563eb;">&uarr; 12%</div>
            </div>
            <div class="auth-tile">
              <div class="auth-tile-icon" style="background:#dbeafe;color:#2563eb;"><i class="fa fa-users"></i></div>
              <div class="auth-tile-value">650</div>
              <div class="auth-tile-label">Active Clients</div>
              <div class="auth-tile-trend" style="color:#2563eb;">&uarr; 8%</div>
            </div>
            <div class="auth-tile">
              <div class="auth-tile-icon" style="background:#dbeafe;color:#2563eb;"><i class="fa fa-trophy"></i></div>
              <div class="auth-tile-value">320</div>
              <div class="auth-tile-label">Deals Won</div>
              <div class="auth-tile-trend" style="color:#2563eb;">&uarr; 20%</div>
            </div>
          </div>
        </div>
      </div>

      <div class="auth-visual-features">
        <div class="auth-feature"><span class="auth-feature-icon"><i class="fa fa-user-group"></i></span>More Leads</div>
        <div class="auth-feature"><span class="auth-feature-icon"><i class="fa fa-chart-line"></i></span>Better Insights</div>
        <div class="auth-feature"><span class="auth-feature-icon"><i class="fa fa-rocket"></i></span>Greater Growth</div>
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

    (function() {
      const slides = document.querySelectorAll('.auth-slide');
      if (!slides.length) return;

      let index = 0;

      function show(i) {
        index = (i + slides.length) % slides.length;
        slides.forEach((s, n) => s.classList.toggle('is-active', n === index));
      }

      setInterval(() => show(index + 1), 5000);
    })();

  </script>

</body>

</html>
