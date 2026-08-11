<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CRM Admin Panel</title>

    <!-- plugins:css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{asset('vendors/feather/feather.css')}}">
    <link rel="stylesheet" href="{{asset('vendors/ti-icons/css/themify-icons.css')}}">
    <link rel="stylesheet" href="{{asset('vendors/css/vendor.bundle.base.css')}}">

    <!-- Plugin css -->
    <link rel="stylesheet" href="{{asset('vendors/datatables.net-bs4/dataTables.bootstrap4.css')}}">
    <link rel="stylesheet" href="{{asset('js/select.dataTables.min.css')}}">

    <!-- inject css -->
    <link rel="stylesheet" href="{{asset('css/vertical-layout-light/style.css')}}">
    <link rel="stylesheet" href="{{asset('css/confirm.css')}}">
    <link rel="stylesheet" href="{{asset('css/toast.css')}}">

    <link rel="stylesheet" href="{{asset('vendors/select2/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('vendors/select2-bootstrap-theme/select2-bootstrap.min.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <style>
        /* ===== PAGE HEADER ===== */
        .page-header {
            background: #ffffff;
            padding: 18px 24px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .page-header h2 {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        .page-header .breadcrumb {
            font-size: 12px;
            color: #6b7280;
        }

        /* ===== FORM CARD ===== */
        .lead-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
        }

        /* ===== SECTION TITLE ===== */
        .section-title {
            font-size: 13px;
            font-weight: 600;
            color: #2563eb;
            text-transform: uppercase;
            margin-bottom: 12px;
            border-left: 4px solid #2563eb;
            padding-left: 8px;
        }

        /* ===== FORM CONTROLS ===== */
        .form-group label {
            font-size: 12px;
            font-weight: 500;
            color: #374151;
        }

        .form-control {
            font-size: 13px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            height: 38px;
        }

        textarea.form-control {
            height: auto;
        }

        /* ===== DIVIDER ===== */
        .form-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 24px 0;
        }

        /* ===== FOOTER BUTTONS ===== */
        .form-footer {
            position: sticky;
            bottom: 0;
            background: #ffffff;
            padding: 15px 24px;
            margin-top: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-back {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-save-solid {
            background: #2563eb;
            border: none;
        }

        .btn-save-solid:hover {
            background: #1e40af;
        }

        .gradient-border-card {
            /* border thickness */
            border-radius: 16px;
            background: linear-gradient(135deg,
                    #2563eb,
                    #22c55e,
                    #f59e0b,
                    #ec4899);
        }

        #addUserForm {
            background: #ffffff;
            border-radius: 14px;
            padding: 24px;
        }


        .form-row {
            margin-bottom: 6px;
        }

        .section-title {
            margin-top: 10px;
        }

        .form-control::placeholder {
            color: #9ca3af;
            font-size: 12px;
        }

        /* ===============================
   HERO HEADER STYLE
================================ */
        .lead-hero-header {
            background: linear-gradient(135deg, #1d4ed8, #0ea5e9);
            padding: 22px 28px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #ffffff;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .lead-hero-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .lead-hero-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .lead-hero-header h4 {
            font-weight: 700;
            font-size: 20px;
        }

        .lead-hero-header small {
            font-size: 13px;
            opacity: 0.9;
        }

        .lead-hero-right .btn {
            border-radius: 30px;
            padding: 6px 18px;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .lead-hero-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
            }

            .lead-hero-right {
                width: 100%;
            }

            .lead-hero-right .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container-scroller">
        @include('include.header')

        <div class="container-fluid page-body-wrapper gradient-border-card">
            @include('include.sidebar')

            <div class="content-wrapper ">
                <form id="addUserForm" method="post">
                    @csrf

                    <div class="modal-body lead-form" style="font-size:12.5px;">
                        <!-- 🔷 ADD LEAD HERO HEADER -->
                        <div class="lead-hero-header mb-4">

                            <div class="lead-hero-left">
                                <div class="lead-hero-icon">
                                    <i class="fa fa-plus-circle"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0">Add New Lead</h4>
                                    <small>Create and assign a new CRM lead</small>
                                </div>
                            </div>

                            <div class="lead-hero-right">
                                <button type="button"
                                    class="btn btn-light btn-sm"
                                    onclick="backButton()">
                                    <i class="fa fa-arrow-left mr-1"></i> Back
                                </button>
                            </div>

                        </div>



                        <!-- ================= BASIC INFO ================= -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Lead Type</label>
                                <select name="lead_type" id="lead_type" class="form-control" data-master-type="lead_type">
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Lead Source</label>
                                <select name="lead_source" id="lead_source" class="form-control" data-master-type="lead_source">
                                    <option value="">-- Select Lead Source --</option>
                                </select>
                            </div>

                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Company Name</label>
                                <input type="text" name="company_name" id="company_name"
                                    class="form-control" placeholder="Company name">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Gst No.</label>
                                <input type="text" name="gst_no" id="gst_no"
                                    class="form-control" placeholder="Gst number">
                            </div>
                        </div>

                        <!-- ================= CONTACT ================= -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Name</label>
                                <input type="text" name="name" id="name"
                                    class="form-control" placeholder="Full name">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Phone</label>
                                <input type="text" name="phone" id="phone"
                                    class="form-control" placeholder="Primary contact number">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Alternate Phone</label>
                                <input type="text" name="alternate_phone" id="alternate_phone"
                                    class="form-control" placeholder="Secondary contact number">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" name="email" id="email"
                                    class="form-control" placeholder="Email address">
                            </div>
                        </div>

                        <div id="duplicateWarning" style="display:none;"
                            class="alert alert-warning" role="alert">
                        </div>

                        <!-- ================= LOCATION ================= -->
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>City</label>
                                <input type="text" name="city" id="city"
                                    class="form-control" placeholder="City">
                            </div>

                            <div class="form-group col-md-4">
                                <label>State</label>
                                <input type="text" name="state" id="state"
                                    class="form-control" placeholder="State">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Country</label>
                                <input type="text" name="country" id="country"
                                    class="form-control" placeholder="Country" value="India">
                            </div>
                        </div>

                        <!-- ================= PRODUCT / SERVICE ================= -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Product</label>
                                <input type="text" name="product" id="product"
                                    class="form-control" placeholder="Product name">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Service</label>
                                <input type="text" name="service" id="service"
                                    class="form-control" placeholder="Service name">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Budget</label>
                                <input type="number" name="budget" id="budget"
                                    class="form-control" placeholder="Estimated budget">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Priority</label>
                                <select name="priority" id="priority" class="form-control" data-master-type="lead_priority">
                                </select>
                            </div>
                        </div>

                        <!-- ================= STATUS ================= -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Lead Status</label>
                                <select name="lead_status" id="lead_status" class="form-control" data-master-type="lead_status">
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Status Reason</label>
                                <input type="text" name="status_reason" id="status_reason"
                                    class="form-control" placeholder="Reason for status">
                            </div>
                        </div>

                        <!-- ================= FOLLOW UP ================= -->
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Follow Up Date</label>
                                <input type="date" name="follow_up_date" id="follow_up_date"
                                    class="form-control">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Follow Up Time</label>
                                <input type="time" name="follow_up_time" id="follow_up_time"
                                    class="form-control">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Follow Up Note</label>
                                <input type="text" name="follow_up_note" id="follow_up_note"
                                    class="form-control" placeholder="Short follow up note">
                            </div>
                        </div>

                        <!-- ================= REQUIREMENT ================= -->
                        <div class="form-group">
                            <label>Requirement</label>
                            <textarea name="requirement" id="requirement"
                                class="form-control" rows="2"
                                placeholder="Client requirements"></textarea>
                        </div>

                        <!-- ================= ASSIGNMENT ================= -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Assigned To (User ID)</label>
                                <input type="number" name="assigned_to" id="assigned_to"
                                    class="form-control" placeholder="User ID">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Assigned By (User ID)</label>
                                <input type="number" name="assigned_by" id="assigned_by"
                                    class="form-control" placeholder="User ID">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Assigned At</label>
                                <input type="datetime-local" name="assigned_at" id="assigned_at"
                                    class="form-control">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Last Contacted At</label>
                                <input type="datetime-local" name="last_contacted_at" id="last_contacted_at"
                                    class="form-control">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Last Contacted By (User ID)</label>
                                <input type="number" name="last_contacted_by" id="last_contacted_by"
                                    class="form-control" placeholder="User ID">
                            </div>
                        </div>

                        <!-- ================= CONVERSION ================= -->
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Is Converted</label>
                                <select name="is_converted" id="is_converted" class="form-control">
                                    <option value="No">No</option>
                                    <option value="Yes">Yes</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Converted At</label>
                                <input type="datetime-local" name="converted_at" id="converted_at"
                                    class="form-control">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Conversion Value</label>
                                <input type="number" step="0.01" name="conversion_value" id="conversion_value"
                                    class="form-control" placeholder="Amount">
                            </div>
                        </div>

                        <!-- ================= NOTES ================= -->
                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea name="remarks" id="remarks"
                                class="form-control" rows="2"
                                placeholder="Remarks"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Internal Note</label>
                            <textarea name="internal_note" id="internal_note"
                                class="form-control" rows="2"
                                placeholder="Internal note"></textarea>
                        </div>

                        <!-- ================= CUSTOMER CONTACT DETAILS ================= -->
                        <div class="form-divider"></div>

                        <div class="section-title">Customer Contact Details</div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Customer Name</label>
                                <input type="text"
                                    name="customer_name"
                                    class="form-control"
                                    placeholder="Customer full name">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Phone</label>
                                <input type="text"
                                    name="customer_phone"
                                    class="form-control"
                                    placeholder="Primary phone number">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email"
                                    name="customer_email"
                                    class="form-control"
                                    placeholder="Email address">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Designation</label>
                                <input type="text"
                                    name="designation"
                                    class="form-control"
                                    placeholder="Designation / Role">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Budget</label>
                                <input type="number"
                                    name="customer_budget"
                                    class="form-control"
                                    placeholder="Estimated budget">
                            </div>

                            <div class="form-group col-md-6">
                                <label>City</label>
                                <input type="text"
                                    name="customer_city"
                                    class="form-control"
                                    placeholder="City">
                            </div>
                        </div>

                    </div>

                    <!-- FOOTER -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light px-4" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-save-solid">
                            <i class="fa fa-save"></i> Save Lead
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{asset('vendors/chart.js/Chart.min.js')}}"></script>
    <script src="{{asset('vendors/datatables.net/jquery.dataTables.js')}}"></script>
    <script src="{{asset('vendors/datatables.net-bs4/dataTables.bootstrap4.js')}}"></script>
    <script src="{{asset('js/dataTables.select.min.js')}}"></script>
    <script src="{{asset('vendors/select2/select2.min.js')}}"></script>
    <script src="{{asset('js/select2.js')}}"></script>
    <script src="{{asset('js/off-canvas.js')}}"></script>
    <script src="{{asset('js/hoverable-collapse.js')}}"></script>
    <script src="{{asset('js/template.js')}}"></script>
    <script src="{{asset('js/settings.js')}}"></script>
    <script src="{{asset('js/todolist.js')}}"></script>
    <script src="{{asset('js/dashboard.js')}}"></script>
    <script src="{{asset('js/Chart.roundedBarCharts.js')}}"></script>
    <script src="{{asset('js/toast.js')}}"></script>
    <script src="{{asset('js/confirm.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script>
        const esc = value => $('<div>').text(value ?? '').html();
        // Populates every <select data-master-type="..."> from the Master Data
        // lookup endpoint instead of hardcoded <option> lists (Phase 2).
        function loadMasterDropdowns() {
            $('select[data-master-type]').each(function() {
                const $select = $(this);
                const type = $select.data('master-type');
                const keepFirstOption = $select.find('option').length > 0;

                $.get("{{ url('master-data/lookup') }}/" + type, function(response) {
                    response.data.forEach(function(option) {
                        $select.append(`<option value="${esc(option.code)}">${esc(option.label)}</option>`);
                    });
                    if (!keepFirstOption && $select.data('selected')) {
                        $select.val($select.data('selected'));
                    }
                });
            });
        }

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

        $(document).on('submit', '#addUserForm', function(e) {
            e.preventDefault();
            let form = this;
            let formData = new FormData(form);
            $.ajax({
                url: '{{route("leads.store")}}',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    setTimeout(function() {
                        toastr.success(response.message);
                    }, 1000);
                    form.reset();
                    window.location.href = "{{ route('leads.index') }}";
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Unexpected error occurred');
                    }
                }
            });
        })

        function backButton() {
            setTimeout(function() {

            }, 500);
            window.location.href = "{{ route('leads.index') }}";

        }

        // Non-blocking duplicate check (Phase 4) — warns, doesn't stop submission.
        function checkDuplicate() {
            const phone = $('#phone').val();
            const email = $('#email').val();
            const company_name = $('#company_name').val();

            if (!phone && !email && !company_name) {
                $('#duplicateWarning').hide();
                return;
            }

            $.post("{{ route('leads.check_duplicate') }}", {
                phone,
                email,
                company_name
            }, function(response) {
                if (response.data && response.data.length) {
                    let list = response.data.map(l =>
                        `<a href="{{ url('leads') }}/${l.id}" target="_blank">${esc(l.name)} (${esc(l.phone ?? l.email ?? l.company_name)})</a>`
                    ).join(', ');
                    $('#duplicateWarning')
                        .html(`<i class="fa fa-exclamation-triangle"></i> Possible duplicate of: ${list}`)
                        .show();
                } else {
                    $('#duplicateWarning').hide();
                }
            });
        }

        $(document).on('blur', '#phone, #email, #company_name', checkDuplicate);

        $(document).ready(function() {
            loadMasterDropdowns();
        });
    </script>
</body>

</html>
