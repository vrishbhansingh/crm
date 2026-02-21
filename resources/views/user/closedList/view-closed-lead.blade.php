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

                    <a href="{{ route('user.closed_lead_list') }}" class="btn btn-light btn-sm">
                        ← Back to Closed Leads
                    </a>
                </div>

                <!-- LEAD CARD -->
                <div class="lead-card">

                    <!-- LEAD INFO -->
                    <div class="lead-info-vertical" id="leadMainRow">
                        <!-- AJAX DATA -->
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary btn-sm" id="openQuotationBtn">
                            <i class="fa fa-file-text-o"></i> Send Quotation
                        </button>
                    </div>


                </div>

                @include('user.include.footer')

            </div>

        </div>
    </div>


    <div class="modal fade" id="quotationModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content quotation-modal">

                <!-- HEADER -->
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title mb-1">Create Quotation</h5>
                        <small class="text-muted">
                            Select the quotation format you want to send
                        </small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- BODY -->
                <div class="modal-body pt-3">

                    <div class="form-group">
                        <label class="modal-label">
                            Quotation Type
                        </label>

                        <select class="form-control quotation-select" id="quotation_template">
                            <option value="">-- Select Template --</option>
                            <option value="quick">Quick Quote (Initial Estimate)</option>
                            <option value="standard">Standard Quote (Detailed)</option>
                            <option value="final">Final Quote / Proforma</option>
                        </select>
                    </div>

                    <!-- INFO NOTE -->
                    <div class="quotation-hint">
                        <i class="fa fa-info-circle"></i>
                        You can change the template later if required.
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button"
                        class="btn btn-primary btn-sm px-4"
                        onclick="openQuotationView()">
                        Continue
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
                                <!-- BASIC INFO -->
                                <div class="lead-info-box">
                                    <div class="lead-info-label">Name</div>
                                    <div class="lead-info-value">${lead.name ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Company</div>
                                    <div class="lead-info-value">${lead.company_name ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Email</div>
                                    <div class="lead-info-value">${lead.email ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Phone</div>
                                    <div class="lead-info-value">${lead.phone ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Alternate Phone</div>
                                    <div class="lead-info-value">${lead.alternate_phone ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">GST No</div>
                                    <div class="lead-info-value">${lead.gst_no ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Location</div>
                                    <div class="lead-info-value">
                                        ${lead.city ?? '-'}, ${lead.state ?? '-'}, ${lead.country ?? '-'}
                                    </div>
                                </div>

                                <!-- LEAD DETAILS -->
                                <div class="lead-info-box">
                                    <div class="lead-info-label">Lead Type</div>
                                    <div class="lead-info-value">${capitalizeFirstLetter(lead.lead_type)}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Lead Source</div>
                                    <div class="lead-info-value">${capitalizeFirstLetter(lead.lead_source)}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Lead Status</div>
                                    <div class="lead-info-value">${capitalizeFirstLetter(lead.lead_status)}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Final Status</div>
                                    <div class="lead-info-value">${lead.final_status ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Priority</div>
                                    <div class="lead-info-value">${capitalizeFirstLetter(lead.priority)}</div>
                                </div>

                                <!-- PRODUCT / SERVICE -->
                                <div class="lead-info-box">
                                    <div class="lead-info-label">Product</div>
                                    <div class="lead-info-value">${lead.product ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Service</div>
                                    <div class="lead-info-value">${lead.service ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Budget</div>
                                    <div class="lead-info-value">
                                        ${lead.budget ? '₹ ' + lead.budget : '-'}
                                    </div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Requirement</div>
                                    <div class="lead-info-value">${lead.requirement ?? '-'}</div>
                                </div>

                                <!-- FOLLOW UP -->
                                <div class="lead-info-box">
                                    <div class="lead-info-label">Follow Up Date</div>
                                    <div class="lead-info-value">${lead.follow_up_date ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Follow Up Time</div>
                                    <div class="lead-info-value">${lead.follow_up_time ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Follow Up Note</div>
                                    <div class="lead-info-value">${lead.follow_up_note ?? '-'}</div>
                                </div>

                                <!-- CONVERSION -->
                                <div class="lead-info-box">
                                    <div class="lead-info-label">Converted</div>
                                    <div class="lead-info-value">${lead.is_converted ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Converted At</div>
                                    <div class="lead-info-value">${lead.converted_at ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Conversion Value</div>
                                    <div class="lead-info-value">
                                        ${lead.conversion_value ? '₹ ' + lead.conversion_value : '-'}
                                    </div>
                                </div>

                                <!-- INTERNAL -->
                                <div class="lead-info-box">
                                    <div class="lead-info-label">Remarks</div>
                                    <div class="lead-info-value">${lead.remarks ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Internal Note</div>
                                    <div class="lead-info-value">${lead.internal_note ?? '-'}</div>
                                </div>

                                <div class="lead-info-box">
                                    <div class="lead-info-label">Last Contacted At</div>
                                    <div class="lead-info-value">${lead.last_contacted_at ?? '-'}</div>
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


        // Open modal
        $(document).on('click', '#openQuotationBtn', function() {
            $('#quotation_template').val('');
            $('#quotationModal').modal('show');
        });


        function openQuotationView() {
            const template = document.getElementById('quotation_template').value;
            const pathParts = window.location.pathname.split('/');
            const encodedId = pathParts[pathParts.length - 1];

            if (!template) {
                alert('Please select a quotation template');
                return;
            }

            let url = '';

            if (template === 'quick') {
                url = "{{ route('user.quotation.template1', ':id') }}";
            } else if (template === 'standard') {
                url = "{{ route('user.quotation.template2', ':id') }}";
            } else if (template === 'final') {
                url = "{{ route('user.quotation.template3', ':id') }}";
            }

            url = url.replace(':id', encodedId);
            window.location.href = url;
        }
    </script>


</body>

</html>