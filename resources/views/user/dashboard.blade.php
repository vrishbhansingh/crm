<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />


  <style>
    /* ===============================
   ROOT VARIABLES
================================ */
    :root {
      --primary: #0d6efd;
      --secondary: #00c6ff;
      --bg-light: #f8f9fc;
      --text-dark: #1f2937;
      --text-muted: #6b7280;
      --white: #ffffff;
      --radius-lg: 16px;
      --radius-md: 12px;
      --shadow-sm: 0 6px 18px rgba(0, 0, 0, 0.08);
      --shadow-md: 0 14px 40px rgba(0, 0, 0, 0.12);
      --transition: all 0.25s ease;
    }

    /* ===============================
   GLOBAL
================================ */
    body {
      background: var(--bg-light);
      font-family: 'Poppins', sans-serif;
      color: var(--text-dark);
    }

    .content-wrapper {
      padding-bottom: 40px;
    }

    /* ===============================
   DASHBOARD HEADER
================================ */
    .dashboard-header {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      padding: 22px 28px;
      border-radius: var(--radius-lg);
      margin-bottom: 28px;
      box-shadow: var(--shadow-sm);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .dashboard-header h2 {
      margin: 0;
      font-size: 22px;
      font-weight: 700;
      color: var(--white);
    }

    .dashboard-header .breadcrumb {
      background: transparent;
      margin: 0;
      padding: 0;
    }

    #attendanceBtn {
      padding: 6px 18px;
      font-size: 13px;
      border-radius: 30px;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* ===============================
   CARD GRID
================================ */
    .row>[class*="col-"] {
      display: flex;
    }

    .dash-card {
      width: 100%;
      background: var(--white);
      border-radius: var(--radius-lg);
      padding: 22px;
      display: flex;
      gap: 18px;
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
      min-height: 155px;
    }

    .dash-card:hover {
      transform: translateY(-6px);
      box-shadow: var(--shadow-md);
    }

    /* ===============================
   ICON
================================ */
    .icon-box {
      width: 56px;
      height: 56px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      color: var(--white);
      flex-shrink: 0;
    }

    /* ===============================
   CARD CONTENT
================================ */
    .dash-card-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .title {
      font-size: 12px;
      font-weight: 600;
      letter-spacing: 0.6px;
      color: var(--text-muted);
      text-transform: uppercase;
      margin-bottom: 6px;
    }

    .value {
      font-size: 20px;
      font-weight: 600;
      color: var(--text-dark);
      line-height: 1.1;
      margin-bottom: 10px;
    }

    /* ===============================
   VIEW LINK
================================ */
    .btn-view {
      font-size: 12px;
      font-weight: 600;
      color: var(--primary);
      text-decoration: none;
      width: fit-content;
    }

    .btn-view:hover {
      text-decoration: underline;
    }

    /* ===============================
   ATTENDANCE MODAL
================================ */
    .attendance-confirm-modal {
      border-radius: var(--radius-md);
      border: none;
      box-shadow: var(--shadow-md);
    }

    .attendance-confirm-modal .modal-header {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: var(--white);
      padding: 14px 20px;
      border-top-left-radius: var(--radius-md);
      border-top-right-radius: var(--radius-md);
    }

    .attendance-confirm-modal .modal-title {
      font-size: 16px;
      font-weight: 600;
    }

    .attendance-close {
      width: 32px;
      height: 32px;
      background: #dc3545;
      border-radius: 50%;
      color: #fff;
      font-size: 18px;
      border: none;
      cursor: pointer;
    }

    .attendance-close:hover {
      background: #c82333;
    }

    .attendance-confirm-modal .modal-body {
      padding: 22px;
    }

    .attendance-info {
      background: #f1f5f9;
      border-radius: 10px;
      padding: 14px 16px;
      margin-bottom: 18px;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      font-size: 14px;
      padding: 6px 0;
    }

    .info-row span {
      color: var(--text-muted);
    }

    .info-row strong {
      font-weight: 600;
    }

    .confirm-check {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      font-size: 14px;
    }

    .confirm-check label {
      line-height: 1.4;
    }

    .attendance-confirm-modal .modal-footer {
      padding: 14px 20px;
    }

    /* ===============================
   MOBILE
================================ */
    @media (max-width: 768px) {
      .dashboard-header {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
      }

      .dash-card {
        min-height: auto;
      }
    }

    .btn-view {
      display: inline-flex;
      align-items: center;
      justify-content: center;

      padding: 6px 16px;
      font-size: 12px;
      font-weight: 600;

      color: #007bff;
      background: transparent;

      border: 1.5px solid #007bff;
      border-radius: 20px;

      text-decoration: none !important;
      cursor: pointer;

      transition: all 0.25s ease;
    }

    /* Hover */
    .btn-view:hover {
      background: #007bff;
      color: #ffffff;
      text-decoration: none;
      transform: translateY(-1px);
    }

    /* Focus & Active */
    .btn-view:focus,
    .btn-view:active {
      outline: none;
      box-shadow: none;
      text-decoration: none;
    }

    /* Optional icon spacing */
    .btn-view i {
      margin-right: 6px;
      font-size: 12px;
    }

    /* ================= MOBILE RESPONSIVE ================= */
    @media (max-width: 768px) {

      /* Header */
      .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        padding: 16px 18px;
        gap: 10px;
      }

      .dashboard-header h2 {
        font-size: 20px;
      }

      .breadcrumb {
        display: none;
        /* hide breadcrumb on mobile */
      }

      /* Cards grid */
      .dash-card {
        padding: 16px;
        gap: 12px;
      }

      .icon-box {
        width: 48px;
        height: 48px;
        font-size: 18px;
      }

      .value {
        font-size: 22px;
      }

      .title {
        font-size: 11px;
      }

      .btn-view {
        font-size: 11px;
        padding: 4px 12px;
      }

      /* Reduce hover animation on touch devices */
      .dash-card:hover {
        transform: none;
        box-shadow: var(--shadow-default);
      }
    }

    /* ================= EXTRA SMALL DEVICES ================= */
    @media (max-width: 480px) {

      .dashboard-header h2 {
        font-size: 18px;
      }

      .dash-card {
        flex-direction: row;
        align-items: center;
      }

      .icon-box {
        width: 44px;
        height: 44px;
        font-size: 16px;
      }

      .value {
        font-size: 20px;
      }
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
    @include('user.include.header')
    <div class="container-fluid page-body-wrapper">
      @include('user.include.sidebar')
      <div class="content-wrapper">

        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <!-- Admin Dashboard Header -->
        <div class="dashboard-header">
          <h2>Admin Dashboard</h2>

          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="border: none;">
              <li class="breadcrumb-item"><a class="btn btn-primary" data-toggle="modal" data-target="#attendanceModal" id="attendanceBtn">
                  Mark Attendance
                </a></li>
            </ol>
          </nav>
        </div>

        <div class="row">

          <!-- My Assigned Leads -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#007bff,#00c6ff);">
                <i class="fa fa-user"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">My Assigned Leads</h6>
                <h4 class="value counter" id="my_leads">0</h4>
                <a href="{{route('user.lead_list')}}" class="btn-view">View Leads</a>
              </div>
            </div>
          </div>

          <!-- My Hot Leads -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#e53935,#ff7043);">
                <i class="fa fa-fire"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">My Hot Leads</h6>
                <h4 class="value counter" id="my_hot_leads">0</h4>
                <a href="{{route('user.lead_list')}}" class="btn-view">View Hot</a>
              </div>
            </div>
          </div>

          <!-- Followups Today -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#3f51b5,#5c6bc0);">
                <i class="fa fa-calendar"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">Followups Today</h6>
                <h4 class="value counter" id="today_followups">0</h4>
                <a href="{{route('user.lead_list')}}" class="btn-view">View Schedule</a>
              </div>
            </div>
          </div>

          <!-- Overdue Followups -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#ff9800,#ffc107);">
                <i class="fa fa-clock-o"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">Overdue Followups</h6>
                <h4 class="value counter" id="overdue_followups">0</h4>
                <a href="{{route('user.lead_list')}}" class="btn-view">View Overdue</a>
              </div>
            </div>
          </div>

          <!-- My Orders -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#4caf50,#8bc34a);">
                <i class="fa fa-shopping-cart"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">My Orders</h6>
                <h4 class="value counter" id="my_orders">0</h4>
                <a href="{{route('user.order_management')}}" class="btn-view">View Orders</a>
              </div>
            </div>
          </div>

          <!-- Active Orders -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#2e7d32,#66bb6a);">
                <i class="fa fa-check-circle"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">Active Orders</h6>
                <h4 class="value counter" id="active_orders">0</h4>
                <a href="{{route('user.order_management')}}" class="btn-view">View Active</a>
              </div>
            </div>
          </div>

          <!-- Payment Collected -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#009688,#4db6ac);">
                <i class="fa fa-money"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">Payment Collected</h6>
                <h4 class="value counter" id="payment_collected">₹0</h4>
                <a href="{{route('user.order_management')}}" class="btn-view">View Payments</a>
              </div>
            </div>
          </div>

          <!-- Pending Payment -->
          <div class="col-md-3 mb-4">
            <div class="dash-card">
              <div class="icon-box" style="background:linear-gradient(135deg,#6d4c41,#a1887f);">
                <i class="fa fa-exclamation-circle"></i>
              </div>
              <div class="dash-card-body">
                <h6 class="title">Pending Payment</h6>
                <h4 class="value counter" id="pending_payment">₹0</h4>
                <a href="{{route('user.order_management')}}" class="btn-view">View Pending</a>
              </div>
            </div>
          </div>

        </div>


        @include('user.include.footer')
      </div>
    </div>
  </div>

  <!-- Attendance Confirmation Modal -->
  <div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content attendance-confirm-modal">

        <div class="modal-header">
          <h5 class="modal-title">Attendance Confirmation</h5>
          <div>
            <button type="button" class="attendance-close" data-dismiss="modal" aria-label="Close">
              &times;
            </button>
          </div>
        </div>

        <div class="modal-body">

          <div class="attendance-info">
            <div class="info-row">
              <span>Date</span>
              <strong id="currentDate"></strong>
            </div>

            <div class="info-row">
              <span>Time</span>
              <strong id="currentTime"></strong>
            </div>
          </div>

          <div class="form-check confirm-check">
            <input class="form-check-input" type="checkbox" id="confirmAttendance" style="margin-left:5px ;margin-top:0px;width:18px;height:18px;">
            <label class="form-check-label" for="confirmAttendance" style="margin-left: 36px;">
              I confirm that I am present and marking my attendance honestly.
            </label>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">
            Cancel
          </button>
          <button type="button" class="btn btn-primary" id="confirmBtn" disabled>
            Confirm Attendance
          </button>
        </div>

      </div>
    </div>
  </div>


  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
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
    /* ===============================
   GLOBAL STATE
================================ */
    let attendanceMarked = false;

    /* ===============================
       DOM READY
    ================================ */
    $(document).ready(function() {

      // 1️⃣ Fetch attendance status
      $.ajax({
        url: "{{ route('user.getAttendence') }}",
        type: "GET",
        success: function(response) {

          if (response.success) {
            attendanceMarked = response.attendanceMarked === true;
            handleAttendanceUI(attendanceMarked);
          }

        },
        error: function() {
          console.error('Failed to fetch attendance status');
        }
      });

    });


    /* ===============================
       HANDLE ATTENDANCE UI
    ================================ */
    function handleAttendanceUI(isMarked) {

      const attendanceBtn = document.getElementById('attendanceBtn');

      if (isMarked === true) {
        if (attendanceBtn) attendanceBtn.style.display = 'none';
        $('#attendanceModal').modal('hide');
        return;
      }

      // ✅ Attendance NOT marked
      if (attendanceBtn) attendanceBtn.style.display = 'block';

      $('#attendanceModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: true
      });

      initAttendancePopup(); // 🔥 initialize popup logic
    }


    /* ===============================
       POPUP DATE/TIME + CHECKBOX
    ================================ */
    function initAttendancePopup() {

      const dateEl = document.getElementById('currentDate');
      const timeEl = document.getElementById('currentTime');
      const checkbox = document.getElementById('confirmAttendance');
      const button = document.getElementById('confirmBtn');

      if (!dateEl || !timeEl || !checkbox || !button) return;

      function updateDateTime() {
        const now = new Date();

        dateEl.textContent = now.toLocaleDateString(undefined, {
          day: '2-digit',
          month: 'long',
          year: 'numeric'
        });

        timeEl.textContent = now.toLocaleTimeString(undefined, {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit'
        });
      }

      updateDateTime();
      setInterval(updateDateTime, 1000);

      checkbox.addEventListener('change', function() {
        button.disabled = !this.checked;
      });
    }


    /* ===============================
       OPEN MODAL FROM BUTTON
    ================================ */
    $(document).on('click', '#attendanceBtn', function() {
      $('#attendanceModal').modal('show');
    });


    /* ===============================
       SUBMIT ATTENDANCE
    ================================ */
    $(document).on('click', '#confirmBtn', function() {

      const userId = "{{ Auth::guard('web')->user()->id }}";
      const attendanceBtn = document.getElementById('attendanceBtn');

      const now = new Date();
      const date = now.toISOString().split('T')[0];
      const time = now.toTimeString().split(' ')[0];

      $.ajax({
        url: "{{ route('user.user_attendence') }}",
        type: "POST",
        data: {
          _token: $('meta[name="csrf-token"]').attr('content'),
          user_id: userId,
          attendance_datetime: date + ' ' + time
        },
        beforeSend: function() {
          $('#confirmBtn').prop('disabled', true).text('Processing...');
        },
        success: function(response) {

          if (response.status === true) {
            attendanceMarked = true;

            $('#attendanceModal').modal('hide');
            if (attendanceBtn) attendanceBtn.style.display = 'none';

            toastr.success(response.message ? response.message : 'Attendance marked successfully');
          }
        },
        error: function() {
          toastr.error('Something went wrong. Please try again.');
          $('#confirmBtn').prop('disabled', false).text('Confirm Attendance');
        }
      });
    });

    $(document).ready(function() {

      $.ajax({
        url: "{{ route('user.dashboard_data') }}",
        type: "GET",
        success: function(response) {

          if (response.status) {
            let data = response.data;

            $('#my_leads').text(data.my_leads);
            $('#my_hot_leads').text(data.my_hot_leads);
            $('#today_followups').text(data.today_followups);
            $('#overdue_followups').text(data.overdue_followups);

            $('#my_orders').text(data.my_orders);
            $('#active_orders').text(data.active_orders);

            $('#payment_collected').text("₹" + parseFloat(data.payment_collected).toFixed(2));
            $('#pending_payment').text("₹" + parseFloat(data.pending_payment).toFixed(2));

          }

        },
        error: function() {
          toastr.error("Failed to load dashboard data");
        }
      });

    });
  </script>

</body>


</html>