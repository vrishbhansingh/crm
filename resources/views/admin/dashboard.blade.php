<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Crm Admin Panel</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="{{asset('vendors/ti-icons/css/themify-icons.css')}}">
  <link rel="stylesheet" href="{{asset('vendors/css/vendor.bundle.base.css')}}">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <link rel="stylesheet" href="{{asset('vendors/datatables.net-bs4/dataTables.bootstrap4.css')}}">
  <link rel="stylesheet" type="text/css" href="{{asset('js/select.dataTables.min.css')}}">
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="{{asset('css/vertical-layout-light/style.css')}}">
  <link rel="stylesheet" href="{{asset('css/confirm.css')}}">
  <link rel="stylesheet" href="{{asset('css/toast.css')}}">
  <!-- endinject -->

  <link rel="stylesheet" href="{{asset('vendors/select2/select2.min.css')}}">
  <link rel="stylesheet" href="{{asset('vendors/select2-bootstrap-theme/select2-bootstrap.min.css')}}">

  <style>
    :root {
      --primary: #007bff;
      --secondary: #00c6ff;
      --text-dark: #212529;
      --text-light: #6c757d;
      --card-bg: rgba(255, 255, 255, 0.95);
      --gradient-primary: linear-gradient(135deg, var(--primary), var(--secondary));
      --shadow-default: 0 4px 12px rgba(0, 0, 0, 0.1);
      --shadow-hover: 0 12px 30px rgba(0, 0, 0, 0.15);
      --transition: all 0.3s ease-in-out;
    }

    .dash-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 10px 25px rgba(0, 123, 255, 0.3);
      border-color: transparent;
    }


    .breadcrumb {
      border: 0px solid #CED4DA;
    }

    body {
      font-family: 'Poppins', sans-serif;
    }

    .dashboard-header {
      background: var(--gradient-primary);
      padding: 20px 30px;
      border-radius: 16px;
      margin-bottom: 30px;
      box-shadow: var(--shadow-default);
      display: flex;
      align-items: center;
      justify-content: space-between;
      color: #fff;
    }

    .dashboard-header h2 {
      font-size: 24px;
      font-weight: 700;
    }

    .breadcrumb {
      background: transparent;
      padding: 0;
      margin: 0;
    }

    .breadcrumb-item a {
      color: #fff;
      text-decoration: none;
      font-weight: 500;
    }

    .breadcrumb-item.active {
      color: #e3f2fd;
    }

    /* Dash Card */
    .dash-card {
      position: relative;
      background: var(--card-bg);
      border-radius: 20px;
      padding: 24px;
      display: flex;
      align-items: flex-start;
      gap: 18px;
      transition: var(--transition);
      box-shadow: var(--shadow-default);
      overflow: hidden;
      border: 2px solid transparent;
    }


    /* Icon Box */
    .icon-box {
      width: 60px;
      height: 60px;
      border-radius: 16px;
      background: var(--gradient-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: #fff;
      box-shadow: 0 4px 12px rgba(0, 123, 255, 0.35);
    }

    /* Texts */
    .dash-card-body {
      flex-grow: 1;
    }

    .title {
      font-size: 13px;
      font-weight: 600;
      color: var(--text-light);
      text-transform: uppercase;
      letter-spacing: 0.7px;
      margin-bottom: 5px;
    }

    .value {
      font-size: 28px;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 10px;
    }

    .btn-view {
      font-size: 12px;
      padding: 5px 14px;
      border-radius: 30px;
      border: 1px solid var(--primary);
      color: var(--primary);
      font-weight: 600;
      text-decoration: none;
      transition: var(--transition);
    }

    .btn-view:hover {
      background: var(--primary);
      color: #fff;
    }

    /* ===============================
   900px – 1024px (S20 Ultra / Tablet)
================================ */
    @media (min-width: 900px) and (max-width: 1024px) {

      /* Grid: 2 cards per row */
      .col-md-3 {
        flex: 0 0 50%;
        max-width: 50%;
      }

      /* Card spacing */
      .dash-card {
        padding: 18px;
        gap: 14px;
        min-height: 140px;
      }

      /* Icon size */
      .icon-box {
        width: 50px;
        height: 50px;
        font-size: 18px;
        border-radius: 12px;
      }

      /* Text scaling */
      .title {
        font-size: 11px;
      }

      .value {
        font-size: 24px;
      }

      /* Button */
      .btn-view {
        font-size: 11px;
        padding: 5px 14px;
      }
    }
  </style>
</head>

<body>
  <div class="container-scroller">
    @include('admin.include.header')
    <div class="container-fluid page-body-wrapper">
      @include('admin.include.sidebar')
      <div class="content-wrapper">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <!-- Admin Dashboard Header -->
        <div class="dashboard-header">
          <h2>Admin Dashboard</h2>
        </div>

        <div class="row">
          <!-- Total Leads -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#007bff,#00c6ff);">
                <i class="fa fa-bar-chart"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">Total Leads</h6>
                <h4 class="value counter" id="total_lead" data-target="0">0</h4>
                <a href="{{route('admin.lead')}}" class="btn-view">View Details</a>
              </div>
            </div>
          </div>

          <!-- Call Leads -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#28a745,#6fdd8b);">
                <i class="fa fa-phone"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">Call Leads</h6>
                <h4 class="value counter" id="call_lead" data-target="">0</h4>
                <a href="{{route('admin.lead')}}" class="btn-view">View Details</a>
              </div>
            </div>
          </div>

          <!-- Web Leads -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#ff9800,#ffc107);">
                <i class="fa fa-globe"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">Web Leads</h6>
                <h4 class="value counter" id="web_lead" data-target="">0</h4>
                <a href="{{route('admin.lead')}}" class="btn-view">View Details</a>
              </div>
            </div>
          </div>

          <!-- Social Media Leads -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#e91e63,#ff4081);">
                <i class="fa fa-facebook"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">Social Media Leads</h6>
                <h4 class="value counter" id="facebook_lead" data-target="0">0</h4>
                <a href="{{route('admin.lead')}}" class="btn-view">View Details</a>
              </div>
            </div>
          </div>

          <!-- New Leads -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#9c27b0,#ba68c8);">
                <i class="fa fa-user-plus"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">New Leads</h6>
                <h4 class="value counter" id="new_lead" data-target="0">0</h4>
                <a href="{{route('admin.lead')}}" class="btn-view">View Details</a>
              </div>
            </div>
          </div>

          <!-- Open Leads -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#ff5722,#ff8a65);">
                <i class="fa fa-folder-open"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">Hot Leads</h6>
                <h4 class="value counter" id="hot_lead" data-target="0">0</h4>
                <a href="{{route('admin.lead')}}" class="btn-view">View Details</a>
              </div>
            </div>
          </div>
          <!-- Total Sales -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#4caf50,#8bc34a);">
                <i class="fa fa-shopping-cart"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">Total Sales</h6>
                <h4 class="value counter" data-target="27">0</h4>
                <a href="#" class="btn-view">View Details</a>
              </div>
            </div>
          </div>
          <!-- Total Customers -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#3f51b5,#5c6bc0);">
                <i class="fa fa-users"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">Total Customers</h6>
                <h4 class="value counter" data-target="370">0</h4>
                <a href="#" class="btn-view">View Details</a>
              </div>
            </div>
          </div>
        </div>

        <!-- TEAM ATTENDANCE -->
        <style>
          .attendance-card { background:#fff; border-radius:16px; box-shadow:0 8px 24px rgba(15,23,42,.05); padding:22px 24px; margin-bottom:24px; }
          .attendance-head h5 { font-weight:700; color:#1e293b; margin:0; font-size:16px; }
          .attendance-head small { color:#94a3b8; font-size:12px; }
          .attendance-table { width:100%; border-collapse:collapse; }
          .attendance-table th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#94a3b8; font-weight:600; padding:10px 12px; border-bottom:1px solid #eef1f5; }
          .attendance-table td { padding:14px 12px; font-size:14px; color:#1e293b; border-bottom:1px solid #f1f5f9; }
          .att-badge { padding:4px 12px; border-radius:999px; font-size:12px; font-weight:600; }
          .att-online { background:#e7f7ee; color:#1f9254; }
          .att-offline { background:#eef1f5; color:#64748b; }
        </style>
        <div class="row">
          <div class="col-12">
            <div class="attendance-card">
              <div class="attendance-head mb-3">
                <h5><i class="fa fa-clock-o mr-2"></i> Team Attendance</h5>
                <small>Last check-in / check-out per agent</small>
              </div>
              <div class="table-responsive">
                <table class="attendance-table">
                  <thead>
                    <tr>
                      <th>Agent</th>
                      <th>Last Check-in</th>
                      <th>Last Check-out</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="attendanceBody">
                    <tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        @include('admin.include.footer')
      </div>
    </div>
  </div>
  <script>
    $(function() {
      getCrmDashboardData();
      loadAttendance();
    });

    function fmtDateTime(dt) {
      if (!dt) return '—';
      const d = new Date(String(dt).replace(' ', 'T'));
      if (isNaN(d.getTime())) return dt;
      return d.toLocaleString('en-IN', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit', hour12: true
      });
    }

    function loadAttendance() {
      $.ajax({
        url: "{{ route('admin.getAttendance') }}",
        type: "GET",
        success: function(res) {
          let rows = '';
          if (res.status && Array.isArray(res.data) && res.data.length) {
            res.data.forEach(a => {
              const online = !a.check_out;
              rows += `
                <tr>
                  <td><strong>${a.name ?? '-'}</strong></td>
                  <td>${fmtDateTime(a.check_in)}</td>
                  <td>${fmtDateTime(a.check_out)}</td>
                  <td><span class="att-badge ${online ? 'att-online' : 'att-offline'}">${online ? 'Online' : 'Checked out'}</span></td>
                </tr>`;
            });
          } else {
            rows = `<tr><td colspan="4" class="text-center text-muted">No attendance records yet.</td></tr>`;
          }
          $('#attendanceBody').html(rows);
        },
        error: function() {
          $('#attendanceBody').html(`<tr><td colspan="4" class="text-center text-muted">Failed to load attendance.</td></tr>`);
        }
      });
    }

    function getCrmDashboardData() {
      $.ajax({
        url: "{{route('admin.getDashboardData')}}",
        type: "GET",
        processData: false,
        contentType: false,
        beforeSend: function() {
          $('#load').show();
        },
        success: function(response) {
          if (response.status) {
            $('#total_lead').html(response.totalLead);
            $('#new_lead').html(response.newLeadToday);
            $('#call_lead').html(response.callLead);
            $('#hot_lead').html(response.hotLead);
            $('#facebook_lead').html(response.facebook);
            $('#web_lead').html(response.webLead);
          }
        },
        error: function(jqXHR, exception) {
          console.log(jqXHR.responseText);
        }
      });
    }
  </script>

</body>

</html>