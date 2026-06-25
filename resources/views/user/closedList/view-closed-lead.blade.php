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

        .content-wrapper {
            background: #f4f6fb;
        }

        /* ---------- PAGE HEADER ---------- */
        .crm-page-header {
            background: #ffffff;
            border-radius: 16px;
            padding: 22px 28px;
            margin-bottom: 22px;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .page-subtitle {
            font-size: 13px;
            color: #64748b;
            margin: 4px 0 0;
        }

        .btn-back {
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all .2s ease;
            white-space: nowrap;
        }

        .btn-back:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        /* ---------- LEAD HERO ---------- */
        .lead-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            background: #ffffff;
            border-radius: 16px;
            padding: 24px 28px;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.05);
            margin-bottom: 22px;
        }

        .lead-hero-left {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .lead-avatar {
            width: 64px;
            height: 64px;
            min-width: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
        }

        .lead-hero-name {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .lead-hero-company {
            font-size: 14px;
            color: #64748b;
            margin-top: 4px;
        }

        .lead-hero-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        /* ---------- BADGES ---------- */
        .badge-pill-soft {
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            line-height: 1.4;
        }

        .badge-green {
            background: #e7f7ee;
            color: #1f9254;
        }

        .badge-red {
            background: #fdecec;
            color: #d64545;
        }

        .badge-amber {
            background: #fef3e2;
            color: #c77700;
        }

        .badge-blue {
            background: #e8effd;
            color: #2563eb;
        }

        .badge-gray {
            background: #eef1f5;
            color: #64748b;
        }

        /* ---------- DETAIL SECTIONS ---------- */
        .detail-section {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .detail-section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            border-bottom: 1px solid #eef1f5;
        }

        .detail-section-icon {
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .detail-section-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }

        .detail-section-body {
            padding: 22px 24px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px 28px;
        }

        .detail-item .detail-label {
            font-size: 11px;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .detail-item .detail-value {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
            word-break: break-word;
        }

        /* icon color themes */
        .icon-blue   { background: #e8effd; color: #2563eb; }
        .icon-indigo { background: #eceafe; color: #6366f1; }
        .icon-teal   { background: #e3f6f3; color: #0d9488; }
        .icon-amber  { background: #fef3e2; color: #d97706; }
        .icon-green  { background: #e7f7ee; color: #1f9254; }
        .icon-gray   { background: #eef1f5; color: #64748b; }

        /* ---------- ACTION BUTTON ---------- */
        .btn-quotation {
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            padding: 11px 22px;
            border-radius: 12px;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .btn-quotation:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.32);
            color: #fff;
        }

        @media (max-width: 991px) {
            .detail-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 575px) {
            .detail-grid { grid-template-columns: 1fr; }
            .lead-hero-badges { justify-content: flex-start; }
        }

        @media (min-width: 1025px) {
            #quickCallCard { display: none !important; }
        }

        /* ---------- QUOTATION MODAL ---------- */
        .quotation-modal {
            border: none;
            border-radius: 18px;
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.25);
            overflow: hidden;
        }

        .quotation-modal .modal-header {
            padding: 22px 24px 6px;
        }

        .quotation-modal .modal-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .quotation-modal .modal-body { padding: 18px 24px; }
        .quotation-modal .modal-footer { padding: 8px 24px 22px; }

        .quote-options { display: grid; gap: 12px; }

        .quote-option {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            cursor: pointer;
            background: #fff;
            transition: all .2s ease;
        }

        .quote-option:hover { border-color: #c7d2fe; background: #f8faff; }

        .quote-option.selected {
            border-color: #2563eb;
            background: linear-gradient(135deg, #eff4ff, #f5f3ff);
            box-shadow: 0 6px 16px rgba(37, 99, 235, .15);
        }

        .quote-option-icon {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
        }

        .qi-quick    { background: linear-gradient(135deg, #06b6d4, #0ea5e9); }
        .qi-standard { background: linear-gradient(135deg, #6366f1, #4f46e5); }
        .qi-final    { background: linear-gradient(135deg, #10b981, #059669); }

        .quote-option-text { display: flex; flex-direction: column; }
        .quote-option-text .t { font-size: 14px; font-weight: 700; color: #1e293b; }
        .quote-option-text .s { font-size: 12px; color: #64748b; }

        .quote-option-check {
            margin-left: auto;
            color: #2563eb;
            font-size: 18px;
            opacity: 0;
            transition: opacity .2s;
        }

        .quote-option.selected .quote-option-check { opacity: 1; }

        .quotation-hint {
            margin-top: 16px;
            font-size: 12px;
            color: #475569;
            background: #f1f5f9;
            border-radius: 10px;
            padding: 10px 12px;
        }

        .quotation-hint i { color: #2563eb; margin-right: 4px; }

        .btn-quote-continue {
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 24px;
            border-radius: 10px;
            transition: box-shadow .2s ease;
        }

        .btn-quote-continue:hover { color: #fff; box-shadow: 0 8px 18px rgba(37, 99, 235, .3); }

        .btn-quote-cancel {
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 20px;
            border-radius: 10px;
        }

        .btn-quote-cancel:hover { background: #f1f5f9; }
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

                    <a href="{{ route('user.closed_lead_list') }}" class="btn-back">
                        <i class="fa fa-arrow-left mr-1"></i> Back to Closed Leads
                    </a>
                </div>

                <!-- LEAD CONTENT (AJAX) -->
                <div id="leadDetailContainer"></div>

                <div class="d-flex justify-content-end mb-4">
                    <button class="btn btn-quotation" id="openQuotationBtn">
                        <i class="fa fa-file-text-o mr-1"></i> Send Quotation
                    </button>
                </div>

                @include('user.include.footer')

            </div>

        </div>
    </div>


    <div class="modal fade" id="quotationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content quotation-modal">

                <!-- HEADER -->
                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title mb-1">Create Quotation</h5>
                        <small class="text-muted">Choose a quotation format to send to the client</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- BODY -->
                <div class="modal-body">

                    <div class="quote-options">
                        <div class="quote-option" data-value="quick">
                            <span class="quote-option-icon qi-quick"><i class="fa fa-bolt"></i></span>
                            <span class="quote-option-text">
                                <span class="t">Quick Quote</span>
                                <span class="s">Initial estimate</span>
                            </span>
                            <i class="fa fa-check-circle quote-option-check"></i>
                        </div>

                        <div class="quote-option" data-value="standard">
                            <span class="quote-option-icon qi-standard"><i class="fa fa-file-text-o"></i></span>
                            <span class="quote-option-text">
                                <span class="t">Standard Quote</span>
                                <span class="s">Detailed breakdown</span>
                            </span>
                            <i class="fa fa-check-circle quote-option-check"></i>
                        </div>

                        <div class="quote-option" data-value="final">
                            <span class="quote-option-icon qi-final"><i class="fa fa-check-square-o"></i></span>
                            <span class="quote-option-text">
                                <span class="t">Final Quote / Proforma</span>
                                <span class="s">Ready to invoice</span>
                            </span>
                            <i class="fa fa-check-circle quote-option-check"></i>
                        </div>
                    </div>

                    <input type="hidden" id="quotation_template" value="">

                    <!-- INFO NOTE -->
                    <div class="quotation-hint">
                        <i class="fa fa-info-circle"></i>
                        You can change the template later if required.
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer border-0">
                    <button type="button" class="btn-quote-cancel" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button" class="btn-quote-continue" onclick="openQuotationView()">
                        Continue <i class="fa fa-arrow-right ml-1"></i>
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

                        const val = v => (v === null || v === undefined || v === '') ? '-' : v;
                        const cap = s => s ? s.charAt(0).toUpperCase() + s.slice(1) : '-';
                        const money = v => v ? '₹ ' + Number(v).toLocaleString('en-IN') : '-';

                        const pill = (value, kind) => {
                            const v = (value ?? '').toString().toLowerCase();
                            let cls = 'badge-gray';
                            if (kind === 'priority')
                                cls = v === 'high' ? 'badge-red' : v === 'medium' ? 'badge-amber' : v === 'low' ? 'badge-green' : 'badge-gray';
                            else if (kind === 'final')
                                cls = v === 'won' ? 'badge-green' : v === 'lost' ? 'badge-red' : 'badge-gray';
                            else if (kind === 'leadstatus')
                                cls = v === 'converted' ? 'badge-green' : ['new', 'contacted', 'interested', 'follow_up'].includes(v) ? 'badge-blue' : 'badge-gray';
                            else if (kind === 'converted')
                                cls = (v === 'yes' || v === '1' || v === 'true') ? 'badge-green' : 'badge-gray';
                            return `<span class="badge-pill-soft ${cls}">${formatCallStatus(value.toString())}</span>`;
                        };

                        const field = (label, value) =>
                            `<div class="detail-item">
                                <div class="detail-label">${label}</div>
                                <div class="detail-value">${value}</div>
                            </div>`;

                        const section = (icon, iconCls, title, items) =>
                            `<div class="detail-section">
                                <div class="detail-section-header">
                                    <span class="detail-section-icon ${iconCls}"><i class="fa ${icon}"></i></span>
                                    <span class="detail-section-title">${title}</span>
                                </div>
                                <div class="detail-section-body"><div class="detail-grid">${items}</div></div>
                            </div>`;

                        const initials = (lead.name || '?').trim().split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase() || '?';
                        const location = [lead.city, lead.state, lead.country].filter(Boolean).join(', ') || '-';

                        const hero = `
                            <div class="lead-hero">
                                <div class="lead-hero-left">
                                    <div class="lead-avatar">${initials}</div>
                                    <div>
                                        <div class="lead-hero-name">${val(lead.name)}</div>
                                        <div class="lead-hero-company"><i class="fa fa-building-o mr-1"></i>${val(lead.company_name)}</div>
                                    </div>
                                </div>
                                <div class="lead-hero-badges">
                                    ${lead.lead_status ? pill(lead.lead_status, 'leadstatus') : ''}
                                    ${lead.priority ? pill(lead.priority, 'priority') : ''}
                                    ${lead.final_status ? pill(lead.final_status, 'final') : ''}
                                </div>
                            </div>`;

                        const contact = section('fa-address-card-o', 'icon-blue', 'Contact Information',
                            field('Email', val(lead.email)) +
                            field('Phone', val(lead.phone)) +
                            field('Alternate Phone', val(lead.alternate_phone)) +
                            field('GST No', val(lead.gst_no)) +
                            field('Location', location)
                        );

                        const details = section('fa-info-circle', 'icon-indigo', 'Lead Details',
                            field('Lead Type', cap(lead.lead_type)) +
                            field('Lead Source', cap(lead.lead_source)) +
                            field('Lead Status', lead.lead_status ? pill(lead.lead_status, 'leadstatus') : '-') +
                            field('Final Status', lead.final_status ? pill(lead.final_status, 'final') : '-') +
                            field('Priority', lead.priority ? pill(lead.priority, 'priority') : '-')
                        );

                        const product = section('fa-cube', 'icon-teal', 'Product & Requirement',
                            field('Product', val(lead.product)) +
                            field('Service', val(lead.service)) +
                            field('Budget', money(lead.budget)) +
                            field('Requirement', val(lead.requirement))
                        );

                        const followup = section('fa-calendar', 'icon-amber', 'Follow Up',
                            field('Follow Up Date', val(lead.follow_up_date)) +
                            field('Follow Up Time', val(lead.follow_up_time)) +
                            field('Follow Up Note', val(lead.follow_up_note))
                        );

                        const conversion = section('fa-check-circle', 'icon-green', 'Conversion',
                            field('Converted', lead.is_converted ? pill(lead.is_converted, 'converted') : '-') +
                            field('Converted At', val(lead.converted_at)) +
                            field('Conversion Value', money(lead.conversion_value))
                        );

                        const internal = section('fa-sticky-note-o', 'icon-gray', 'Notes & Internal',
                            field('Remarks', val(lead.remarks)) +
                            field('Internal Note', val(lead.internal_note)) +
                            field('Last Contacted At', val(lead.last_contacted_at))
                        );

                        $('#leadDetailContainer').html(hero + contact + details + product + followup + conversion + internal);


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


        // Open modal (reset previous selection)
        $(document).on('click', '#openQuotationBtn', function() {
            $('#quotation_template').val('');
            $('.quote-option').removeClass('selected');
            $('#quotationModal').modal('show');
        });

        // Select a quotation template card
        $(document).on('click', '.quote-option', function() {
            $('.quote-option').removeClass('selected');
            $(this).addClass('selected');
            $('#quotation_template').val($(this).data('value'));
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