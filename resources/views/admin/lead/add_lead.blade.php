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
                                <label>Existing Company <small class="text-muted">(optional — leave blank to create a new one)</small></label>
                                <select name="company_id" id="company_id" class="form-control">
                                    <option value="">— New company —</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6" id="companyNameGroup">
                                <label>Company Name</label>
                                <input type="text" name="company_name" id="company_name"
                                    class="form-control" placeholder="Company name">
                            </div>
                        </div>
                        <div class="form-row" id="gstGroup">
                            <div class="form-group col-md-6">
                                <label>Gst No.</label>
                                <input type="text" name="gst_no" id="gst_no"
                                    class="form-control" placeholder="Gst number">
                            </div>
                        </div>

                        <!-- ================= CONTACT PERSON =================
                             One section, one set of fields — this used to be typed
                             twice (once here, once under "Customer Contact Details")
                             even though both ended up on the same Contact record
                             (LeadController::store()). -->
                        <div class="form-divider"></div>
                        <div class="section-title">Contact Person</div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Existing Contact <small class="text-muted">(optional — leave blank to create a new one)</small></label>
                                <select name="contact_id" id="contact_id" class="form-control">
                                    <option value="">— New contact —</option>
                                </select>
                            </div>
                        </div>
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

                            <div class="form-group col-md-6">
                                <label>Designation</label>
                                <input type="text" name="designation" id="designation"
                                    class="form-control" placeholder="Designation / Role">
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

                        <!-- ================= ASSIGNMENT =================
                             Was a raw numeric User-ID input — nobody has user IDs
                             memorized. Same name-resolving picker the Leads list's
                             own assignee filter already uses (leads.assignable_users).
                             Left unset, the backend auto-assigns to whichever active
                             sales user currently has the fewest leads. Assigned By /
                             Assigned At / Last Contacted * were dropped entirely: the
                             backend already defaults "assigned by" to the current
                             user, and a brand-new lead can't have a "last contacted"
                             date yet. -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Assign To <small class="text-muted">(optional — auto-assigned if left blank)</small></label>
                                <select name="assigned_to" id="assigned_to" class="form-control">
                                    <option value="">Auto-assign</option>
                                </select>
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

        // Existing-company / existing-contact / assignee pickers — previously
        // these were either a free-text "type the company name again" field
        // (always creating a new Company row) or a raw numeric User-ID input.
        var contactsById = {};

        function loadPickers() {
            $.get("{{ route('companies.options') }}", function(response) {
                (response.data || []).forEach(function(company) {
                    $('#company_id').append(`<option value="${company.id}">${esc(company.name)}</option>`);
                });
            });

            $.get("{{ route('contacts.options') }}", function(response) {
                (response.data || []).forEach(function(contact) {
                    contactsById[contact.id] = contact;
                    const companyLabel = contact.company ? ' — ' + contact.company.name : '';
                    $('#contact_id').append(`<option value="${contact.id}">${esc(contact.name)}${esc(companyLabel)}</option>`);
                });
            });

            $.get("{{ route('leads.assignable_users') }}", function(response) {
                (response.users || []).forEach(function(user) {
                    $('#assigned_to').append(`<option value="${user.id}">${esc(user.name)}</option>`);
                });
            });
        }

        // Picking an existing company makes the free-text name/GST fields
        // redundant (the backend links to that company by ID and ignores
        // them), so grey them out rather than leave two ways to say the
        // same thing both active at once.
        $(document).on('change', '#company_id', function() {
            const picked = !!this.value;
            $('#companyNameGroup, #gstGroup').find('input').prop('disabled', picked);
            if (picked) $('#company_name').val($(this).find(':selected').text());
        });

        // Picking an existing contact pre-fills their details for reference
        // and disables the fields — a linked contact isn't re-created or
        // silently edited by submitting this form.
        $(document).on('change', '#contact_id', function() {
            const contact = contactsById[this.value];
            const fields = ['name', 'phone', 'alternate_phone', 'email', 'designation'];
            fields.forEach(function(field) {
                const $input = $('#' + field);
                if (contact) {
                    $input.val(contact[field] ?? '').prop('disabled', true);
                } else {
                    $input.prop('disabled', false);
                }
            });
        });

        $(document).ready(function() {
            loadMasterDropdowns();
            loadPickers();
        });
    </script>
</body>

</html>
