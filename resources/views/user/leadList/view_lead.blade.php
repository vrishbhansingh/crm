<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>View Lead | CRM</title>

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        .sidebar {
            width: 235px !important;
        }

        .lead-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            padding: 24px;
        }

        .crm-page-header {
            background: #ffffff;
            border-radius: 14px;
            padding: 22px 28px;
            margin-bottom: 24px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
        }

        .page-subtitle {
            font-size: 13px;
            color: #6b7280;
        }

        /* ---------- VERTICAL LEAD GRID ---------- */

        .lead-info-vertical {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 30px;
        }

        .lead-info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px 16px;
        }

        .lead-info-label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .lead-info-value {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
        }

        @media(max-width: 991px) {
            .lead-info-vertical {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width: 575px) {
            .lead-info-vertical {
                grid-template-columns: 1fr;
            }
        }

        /* ---------- CALL OUTCOME ---------- */

        .call-outcome-card {
            background: #ffffff;
            border-radius: 14px;
            margin-top: 24px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        .call-outcome-header {
            padding: 14px 22px;
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
            border-radius: 14px 14px 0 0;
        }

        .call-outcome-body {
            padding: 22px;
        }

        @media (min-width: 1025px) {
            #quickCallCard {
                display: none !important;
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

                <!-- PAGE HEADER -->
                <div class="crm-page-header">
                    <div>
                        <h1 class="page-title">Lead Details</h1>
                        <p class="page-subtitle">View complete information and interaction status of the selected lead</p>
                    </div>

                    <a href="{{ route('user.lead_list') }}" class="btn btn-light btn-sm">
                        ← Back to Leads
                    </a>
                </div>

                <!-- LEAD CARD -->
                <div class="lead-card">

                    <!-- LEAD INFO -->
                    <div class="lead-info-vertical" id="leadMainRow">
                        <!-- AJAX DATA -->
                    </div>

                    <div class="container-fluid mt-4">
                        <div class="row">
                            <div class="col-12">
                                <div class="card shadow-sm">

                                    <!-- Card Header -->
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 fw-semibold">Lead Follow-Up History</h5>
                                        <span class="badge bg-secondary">Recent Calls</span>
                                    </div>

                                    <!-- Card Body -->
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered align-middle mb-0" id="leadFollowupTable">

                                                <thead class="table-primary text-center">
                                                    <tr>
                                                        <th style="width: 50px;">#</th>
                                                        <th>Call Status</th>
                                                        <th>Lead Response</th>
                                                        <th>Follow-Up Date</th>
                                                        <th>Follow-Up Time</th>
                                                        <th>Call Note</th>
                                                    </tr>
                                                </thead>

                                                <tbody class="text-center">
                                                    <!-- JS will inject rows here -->
                                                </tbody>

                                            </table>
                                        </div>
                                    </div>

                                    <!-- Card Footer -->
                                    <div class="card-footer text-muted small text-end">
                                        Showing latest follow-ups
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LEAD STATUS UPDATE -->
                    <div class="call-outcome-card mb-3" id="leadStatusCard">

                        <div class="call-outcome-header">
                            Lead Status Update
                        </div>

                        <div class="call-outcome-body">

                            <input type="hidden" id="status_lead_id">

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label><strong>Lead Status</strong></label>
                                    <select class="form-control" id="lead_status_update">
                                        <option value="">-- Select Status --</option>
                                        <option value="new">New</option>
                                        <option value="not_interested">Not Intrested</option>
                                        <option value="new">New</option>
                                        <option value="contacted">Contacted</option>
                                        <option value="interested">Interested</option>
                                        <option value="follow_up">Follow Up</option>
                                        <option value="converted">Converted</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-4 d-none" id="closedOutcomeBox">
                                <label><strong>Closed Outcome</strong></label>
                                <select class="form-control" id="closed_outcome">
                                    <option value="">-- Select Outcome --</option>
                                    <option value="won">Won</option>
                                    <option value="lost">Lost</option>
                                </select>
                            </div>

                            <button class="btn btn-success btn-sm" id="updateLeadStatusBtn" disabled>
                                <i class="fa fa-refresh"></i> Update Lead Status
                            </button>
                        </div>
                    </div>


                    <!-- QUICK CALL (MOBILE ONLY) -->
                    <div class="call-outcome-card mt-3 d-none" id="quickCallCard">
                        <div class="call-outcome-header">
                            Quick Call
                        </div>

                        <div class="call-outcome-body text-center d-flex ">
                            <a href="#" id="callLeadBtn" class="btn btn-success btn-lg w-100">
                                <i class="fa fa-phone"></i> Call Now
                            </a>
                        </div>
                    </div>
                    <!-- CALL STATUS -->
                    <div class="mt-3">

                        <div class="form-group">
                            <label><strong>Call Status (Mandatory)</strong></label>
                            <select class="form-control" id="call_status">
                                <option value="">-- Select Call Status --</option>
                                <option value="call_connected">☎️ Call Connected</option>
                                <option value="not_reachable">❌ Not Reachable</option>
                                <option value="switched_off">📵 Switched Off</option>
                                <option value="busy">🔁 Busy</option>
                                <option value="wrong_number">🚫 Wrong Number</option>
                            </select>
                        </div>

                        <button class="btn btn-primary mt-2" id="nextStepBtn" disabled>
                            👉 Next Step
                        </button>

                        <!-- CONNECTED FIELDS -->
                        <div id="connectedFields" class="call-outcome-card" style="display:none;">

                            <div class="call-outcome-header">
                                Call Outcome Details
                            </div>

                            <div class="call-outcome-body">

                                <input type="hidden" class="lead_id" name="lead_id">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Lead Response</label>
                                        <select class="form-control" id="lead_response" name="lead_response">
                                            <option value="">Select response</option>
                                            <option value="interested">Interested</option>
                                            <option value="callback">Call Back</option>
                                            <option value="not_interested">Not Interested</option>
                                            <option value="meeting_scheduled">Meeting Scheduled</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Next Follow-up Date</label>
                                        <input type="date" class="form-control" id="next_followup_date" name="follow_up_date">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Next Follow-up Time</label>
                                        <input type="time" class="form-control" id="next_followup_time" name="follow_up_time">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Call Status</label>
                                        <input type="text" class="form-control" value="call_connected" disabled name="call_status">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Call Notes</label>
                                    <textarea class="form-control" rows="3" name="call_note"
                                        placeholder="Client ke response ya conversation summary likhein..."></textarea>
                                </div>
                                <div class="text-right mt-3">
                                    <button type="button" class="btn btn-primary" id="saveConnectedCall">
                                        Save Call Outcome
                                    </button>
                                </div>

                            </div>
                        </div>

                        <div id="nonConnectedFields" class="call-outcome-card" style="display:none;">

                            <div class="call-outcome-header">
                                Call Attempt Details
                            </div>

                            <div class="call-outcome-body">
                                <input type="hidden" class="lead_id" name="lead_id">

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Next Follow-up Date</label>
                                        <input type="date" class="form-control" id="retry_followup_date" name="follow_up_date">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Next Follow-up Date</label>
                                        <input type="time" class="form-control" id="retry_followup_time" name="follow_up_time">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Call Status</label>
                                        <input type="text" class="form-control" id="retry_status" name="call_status" disabled>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Call Notes</label>
                                    <textarea
                                        name="call_note"
                                        class="form-control"
                                        rows="3"
                                        placeholder="Call attempt ka reason likhein (e.g. switched off, busy, not reachable)..."></textarea>
                                </div>

                                <div class="text-right mt-3">
                                    <button type="button" class="btn btn-primary" id="saveCallAttempt">
                                        Save Call Attempt
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @include('user.include.footer')

            </div>

        </div>
    </div>


    <!-- ORDER MODAL -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg">

                <!-- HEADER -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-file-text-o"></i> Create Order & Close Lead
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>

                <!-- BODY -->
                <div class="modal-body">
                    <input type="hidden" id="order_lead_id">

                    <!-- ORDER INFO -->
                    <h6 class="mb-3 border-bottom pb-2">Order Information</h6>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Invoice Date</label>
                            <input type="datetime-local" class="form-control" id="invoice_date">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Order Status</label>
                            <select class="form-control" id="order_status">
                                <option value="new">New</option>
                                <option value="approved">Approved</option>
                                <option value="in_progress">In Progress</option>
                                <option value="on_hold">On Hold</option>
                            </select>
                        </div>
                    </div>

                    <!-- AMOUNT SECTION -->
                    <h6 class="mt-4 mb-3 border-bottom pb-2">Amount Details</h6>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Sub Total</label>
                            <input type="number" class="form-control calc" id="sub_total">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Discount</label>
                            <input type="number" class="form-control calc" id="discount" value="0">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>GST (%)</label>
                            <input type="number" class="form-control calc" id="gst" value="18">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Total Amount</label>
                            <input type="number" class="form-control" id="total_amount" readonly>
                        </div>
                    </div>

                    <!-- PAYMENT DETAILS -->
                    <h6 class="mt-4 mb-3 border-bottom pb-2">Payment Details</h6>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Payment Terms</label>
                            <select class="form-control" id="payment_terms">
                                <option value="advance">Advance</option>
                                <option value="partial_advance">Partial Advance</option>
                                <option value="on_delivery">On Delivery</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Payment Mode</label>
                            <select class="form-control" id="payment_mode">
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Paid Amount</label>
                            <input type="number" class="form-control calc" id="paid_amount" value="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Net Amount</label>
                            <input type="number" class="form-control" id="net_amount" readonly>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Due Amount</label>
                            <input type="number" class="form-control" id="due_amount" readonly>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Payment Status</label>
                            <select class="form-control" id="payment_status">
                                <option value="pending">Pending</option>
                                <option value="partial">Partial</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Currency</label>
                            <select class="form-control" id="currency">
                                <option value="INR">INR</option>
                                <option value="EURO">EURO</option>
                                <option value="DOLLOR">DOLLOR</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Payment Date</label>
                            <input type="datetime-local"
                                class="form-control"
                                id="payment_date"
                                name="payment_date"
                                value="<?= date('Y-m-d\TH:i'); ?>">
                        </div>
                    </div>

                    <!-- PROJECT DETAILS -->
                    <h6 class="mt-4 mb-3 border-bottom pb-2">Project Information</h6>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Project Name</label>
                            <input type="text"
                                class="form-control"
                                id="project_name"
                                placeholder="Enter Project Name">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Tech Stack</label>
                            <input type="text"
                                class="form-control"
                                id="tech_stack"
                                placeholder="Enter technologies require">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Priority</label>
                            <select class="form-control" id="project_priority">
                                <option value="">-- Select Priority --</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Expected Start Date</label>
                            <input type="date"
                                class="form-control"
                                id="expected_start_date">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Expected Delivery Date</label>
                            <input type="date"
                                class="form-control"
                                id="expected_delivery_date">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Actual Delivery Date</label>
                            <input type="date"
                                class="form-control"
                                id="actual_delivery_date">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Project Description</label>
                        <textarea class="form-control"
                            id="project_description"
                            rows="3"
                            placeholder="Project scope, requirements, notes..."></textarea>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer bg-light">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" id="confirmOrderBtn">
                        <i class="fa fa-check"></i> Confirm Order & Close Lead
                    </button>
                </div>

            </div>
        </div>
    </div>



    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        const kolkataDate = new Date().toLocaleDateString('en-CA', {
            timeZone: 'Asia/Kolkata'
        });
        const kolkataTime = new Date().toLocaleTimeString('en-GB', {
            timeZone: 'Asia/Kolkata',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        let leadid = 0;

        $(document).ready(function() {

            const pathParts = window.location.pathname.split('/');
            const encodedId = pathParts[pathParts.length - 1];

            $.ajax({
                url: '{{ route("user.get_viewLead_data") }}',
                type: 'GET',
                data: {
                    id: encodedId
                },
                success: function(response) {

                    if (response.status && response.lead) {
                        leadid = response.lead.id;
                        const lead = response.lead;

                        $('#leadMainRow').html(`
                        <div class="lead-info-box">
                            <div class="lead-info-label">Name</div>
                            <div class="lead-info-value">${lead.name ?? '-'}</div>
                        </div>

                        <div class="lead-info-box">
                            <div class="lead-info-label">Phone</div>
                            <div class="lead-info-value">${lead.phone ?? '-'}</div>
                        </div>

                        <div class="lead-info-box">
                            <div class="lead-info-label">Email</div>
                            <div class="lead-info-value">${lead.email ?? '-'}</div>
                        </div>

                        <div class="lead-info-box">
                            <div class="lead-info-label">Lead Type</div>
                            <div class="lead-info-value">${capitalizeFirstLetter(lead.lead_type) ?? '-'}</div>
                        </div>

                        <div class="lead-info-box">
                            <div class="lead-info-label">Source</div>
                            <div class="lead-info-value">${lead.lead_source ?? '-'}</div>
                        </div>

                        <div class="lead-info-box">
                            <div class="lead-info-label">Status</div>
                            <div class="lead-info-value">${capitalizeFirstLetter(lead.lead_status)}</div>
                        </div>

                        <div class="lead-info-box">
                            <div class="lead-info-label">Priority</div>
                            <div class="lead-info-value">${capitalizeFirstLetter(lead.priority) ?? '-'}</div>
                        </div>

                        <div class="lead-info-box">
                            <div class="lead-info-label">Follow Up Date</div>
                            <div class="lead-info-value">${lead.follow_up_date ?? '-'}</div>
                        </div>
                    `);

                        /* ✅ QUICK CALL (MOBILE ONLY) */
                        const isTouchDevice =
                            ('ontouchstart' in window) ||
                            (navigator.maxTouchPoints > 0);

                        const isMobileOrTablet = window.innerWidth <= 1024;

                        if (isTouchDevice && isMobileOrTablet && lead.phone) {
                            $('#quickCallCard').removeClass('d-none');
                            $('#callLeadBtn').attr('href', `tel:${lead.phone}`);
                        } else {
                            $('#quickCallCard').addClass('d-none');
                        }

                    } else {
                        toastr.error('Lead data not found');
                    }
                },
                error: function() {
                    toastr.error('Something went wrong');
                }
            });

            /* -------- CALL STATUS FLOW -------- */

            $('#call_status').on('change', function() {
                $('#nextStepBtn').prop('disabled', !$(this).val());
            });

            $('#nextStepBtn').on('click', function() {

                const status = $('#call_status').val();

                if (!status) {
                    toastr.warning('Please select call status first');
                    return;
                }

                if (status === 'call_connected') {
                    $('#connectedFields').slideDown();
                    $('.lead_id').val(leadid);
                    $('#next_followup_date').val(kolkataDate);
                    $('#next_followup_time').val(kolkataTime);
                    if ($('#nonConnectedFields').slideDown()) {
                        $('#nonConnectedFields').slideUp();
                    }
                } else {
                    let status_value = formatCallStatus(status);
                    $('.lead_id').val(leadid);
                    $('#retry_status').val(status_value);
                    $('#retry_followup_date').val(kolkataDate);
                    $('#retry_followup_time').val(kolkataTime);
                    $('#connectedFields').slideUp();
                    $('#nonConnectedFields').slideDown();
                }
            });
        });

        function loadLeadFollowups() {
            const pathParts = window.location.pathname.split('/');
            const leadid = pathParts[pathParts.length - 1];

            $.ajax({
                url: '{{route("user.get_leadFollowups")}}',
                type: 'GET',
                data: {
                    lead_id: leadid
                },
                success: function(response) {

                    let rows = '';
                    let index = 1;

                    if (response.lead_follow_up.length === 0) {
                        rows = `
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No follow-up records found
                            </td>
                        </tr>
                    `;
                    } else {
                        response.lead_follow_up.forEach(item => {
                            rows += `
                            <tr>
                                <td>${index++}</td>
                                <td>${formatCallStatus(item.call_status)}</td>
                                <td>
                                    <span class="badge bg-info text-light">
                                        ${capitalizeFirstLetter(item.lead_response) || '-'}
                                    </span>
                                </td>
                                <td>${item.follow_up_date ?? '-'}</td>
                                <td>${item.follow_up_time ?? '-'}</td>
                                <td class="text-start">${capitalizeFirstLetter(item.call_note) ?? '-'}</td>
                            </tr>
                        `;
                        });
                    }

                    $('#leadFollowupTable tbody').html(rows);
                }
            });
        }

        $(document).ready(function() {
            loadLeadFollowups();
        });

        function capitalizeFirstLetter(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function formatCallStatus(value) {
            if (!value) return '';
            return value
                .toLowerCase()
                .replace(/_/g, ' ')
                .replace(/\b\w/g, char => char.toUpperCase());
        }

        $(document).on('click', '#saveCallAttempt', function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const data = {
                id: $('.lead_id').val(),
                call_status: $('#call_status').val(),
                lead_response: null,
                followup_date: $('#retry_followup_date').val(),
                followup_time: $('#retry_followup_time').val(),
                call_notes: $('#nonConnectedFields textarea').val()
            };

            $.ajax({
                url: '{{route("user.user_lead_call_update")}}',
                data: data,
                type: 'POST',
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        if ($('#nonConnectedFields').slideDown()) {
                            $('#nonConnectedFields').slideUp();
                        }
                        setTimeout(loadLeadFollowups, 1500);
                    }
                },
                error: function(err) {
                    toastr.error(err.message);
                }
            });
        });

        $(document).on('click', '#saveConnectedCall', function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const data = {
                id: $('.lead_id').val(),
                call_status: 'call_connected',
                lead_response: $('#lead_response').val(),
                followup_date: $('#next_followup_date').val(),
                followup_time: $('#next_followup_time').val(),
                call_notes: $('#connectedFields textarea').val()
            };

            $.ajax({
                url: '{{route("user.user_lead_call_update")}}',
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        if ($('#connectedFields').slideDown()) {
                            $('#connectedFields').slideUp();
                        }
                        setTimeout(loadLeadFollowups, 1500);
                    }
                },
                error: function(err) {
                    toastr.error(err.message);
                }
            });
        });



        function updateTheStatus(button = null) {

            if (button && $(button).prop('disabled')) return;

            const pathParts = window.location.pathname.split('/');
            const leadId = pathParts[pathParts.length - 1];

            const status = $('#lead_status_update').val();
            let outcome = null;

            if (status === 'closed') {
                outcome = $('#closed_outcome').val();
            }

            $.ajax({
                url: '{{ route("user.update_lead_status") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    lead_id: leadId,
                    lead_status: status,
                    outcome: outcome
                },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        if (status === 'closed') {
                            window.location.href = '{{route("user.dashboard")}}';
                        } else {
                            setTimeout(() => location.reload(), 1200);
                        }
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to update lead status');
                }
            });
        }


        $(document).on('click', '#updateLeadStatusBtn', function() {
            updateTheStatus(this);
        });
        $(document).on('click', '#confirmOrderBtn', function() {

            const pathParts = window.location.pathname.split('/');
            const encodedId = pathParts[pathParts.length - 1];
            const data = {
                lead_id: $('#order_lead_id').val(),
                userId: encodedId,
                invoice_date: $('#invoice_date').val(),
                order_status: $('#order_status').val(),

                // Amount Details
                sub_total: $('#sub_total').val(),
                discount: $('#discount').val(),
                gst: $('#gst').val(),
                total_amount: $('#total_amount').val(),
                net_amount: $('#net_amount').val(),
                due_amount: $('#due_amount').val(),

                // Payment Details
                payment_terms: $('#payment_terms').val(),
                payment_mode: $('#payment_mode').val(),
                paid_amount: $('#paid_amount').val(),
                payment_status: $('#payment_status').val(),
                currency: $('#currency').val(),
                payment_date: $('#payment_date').val(),

                /* ================== PROJECT INFORMATION ================== */
                tech_stack: $('#tech_stack').val(),
                expected_start_date: $('#expected_start_date').val(),
                expected_delivery_date: $('#expected_delivery_date').val(),
                actual_delivery_date: $('#actual_delivery_date').val(),
                project_priority: $('#project_priority').val(),
                project_description: $('#project_description').val()
            };

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: '{{route("user.order_success")}}',
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.status) {
                        $('#orderModal').modal('hide');
                        toastr.success(response.message);
                        setTimeout(function() {
                            updateTheStatus(); // auto update lead
                        }, 800);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Order creation failed');
                }
            });
        });


        $(document).on('change', '#lead_status_update', function() {
            const status = $(this).val();

            // Reset everything
            $('#updateLeadStatusBtn').prop('disabled', true);
            $('#closed_outcome').val('');
            $('#closedOutcomeBox').addClass('d-none');

            if (!status) return;

            // If CLOSED → show Won/Lost dropdown
            if (status === 'closed') {
                $('#closedOutcomeBox').removeClass('d-none');
                // button still disabled until won/lost selected
            } else {
                // Any other status → enable button directly
                $('#updateLeadStatusBtn').prop('disabled', false);
            }
        });


        $(document).on('change', '#closed_outcome', function() {
            const outcome = $(this).val();

            // Enable button ONLY if won/lost selected
            if (outcome === 'won' || outcome === 'lost') {
                $('#updateLeadStatusBtn').prop('disabled', false);
            } else {
                $('#updateLeadStatusBtn').prop('disabled', true);
            }
        });


        /* ✅ Auto-select call_connected when Call button clicked */
        $(document).on('click', '#callLeadBtn', function() {
            $('#call_status').val('call_connected').trigger('change');
        });

        function calculateOrderTotal() {

            let price = parseFloat($('#order_price').val()) || 0;
            let tax = parseFloat($('#order_tax').val()) || 0;
            let discount = parseFloat($('#order_discount').val()) || 0;

            // Step 1: price after discount
            let discountedPrice = price - discount;

            // Step 2: GST calculation
            let taxAmount = (discountedPrice * tax) / 100;

            // Step 3: final total
            let total = discountedPrice + taxAmount;

            $('#order_total').val(total.toFixed(2));
        }


        $(document).on('input', '#order_qty, #order_price, #order_tax, #order_discount', calculateOrderTotal);

        $(document).on('change', '#closed_outcome', function() {
            const outcome = $(this).val();

            if (outcome === 'won') {
                $('#updateLeadStatusBtn').prop('disabled', true);

                const pathParts = window.location.pathname.split('/');
                const leadId = pathParts[pathParts.length - 1];

                $('#order_lead_id').val(leadId);
                $('#orderModal').modal('show');
            }

            if (outcome === 'lost') {
                $('#updateLeadStatusBtn').prop('disabled', false);
            }
        });

        $('.calc').on('input', function() {

            let subTotal = parseFloat($('#sub_total').val()) || 0;
            let discount = parseFloat($('#discount').val()) || 0;
            let gst = parseFloat($('#gst').val()) || 0;
            let paid = parseFloat($('#paid_amount').val()) || 0;

            // Step 1: Taxable amount
            let taxable = Math.max(subTotal - discount, 0);

            // Step 2: GST
            let gstAmount = (taxable * gst) / 100;

            // Step 3: Total
            let total = taxable + gstAmount;

            // Step 4: Net Amount
            let netAmount = total;

            // Step 5: Total Paid
            let totalPaid = paid;

            // Step 6: Due Amount (never negative)
            let dueAmount = Math.max(netAmount - totalPaid, 0);

            $('#total_amount').val(total.toFixed(2));
            $('#net_amount').val(netAmount.toFixed(2));
            $('#due_amount').val(dueAmount.toFixed(2));
        });
    </script>


</body>

</html>