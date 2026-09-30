<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($deal) ? 'Edit Deal' : 'New Deal' }} | CRM Admin</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">

    <style>
        /* .card-box/.page-header removed in V2 — background/border/radius/
           shadow now come from the shared .crm-card/.crm-header
           components (crm-components.css, loaded globally). Form is
           capped at the shared 960px reading width. */
        .deal-form-wrap { max-width: 960px; margin: 0 auto; }

        [data-theme="dark"] .card-box label { color: #d7dbe4; }
    </style>
</head>

<body>

    <div class="container-scroller">

        @include('include.header')

        <div class="container-fluid page-body-wrapper">

            @include('include.sidebar')

            <div class="content-wrapper">

                @component('include.page-header', [
                    'icon' => 'fa fa-handshake-o',
                    'title' => isset($deal) ? 'Edit Deal' : 'New Deal',
                ])
                    @slot('actions')
                        <a href="{{ route('deals.list') }}" class="crm-btn crm-btn--secondary">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    @endslot
                @endcomponent

                <div class="deal-form-wrap">
                @if (! isset($deal) && $pipelines->isEmpty())
                <div class="crm-card crm-empty">
                    <div class="crm-empty__icon"><i class="fa fa-random"></i></div>
                    <p class="crm-empty__title">No pipeline yet</p>
                    <p class="crm-empty__desc">A deal needs a pipeline to belong to — create one first.</p>
                    @can('deals.manage-settings')
                    <a href="{{ route('pipelines.index') }}" class="crm-btn crm-btn--primary crm-btn--sm"><i class="fa fa-plus"></i> Create a Pipeline</a>
                    @else
                    <p class="text-muted" style="font-size:12px;">Ask an admin to set up a pipeline in Settings.</p>
                    @endcan
                </div>
                @else
                <div class="crm-card">
                    <form id="dealForm">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="crm-label">Deal Name <span class="crm-required">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ $deal->name ?? '' }}" required aria-required="true">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Amount</label>
                                <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ $deal->amount ?? 0 }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Currency</label>
                                <select name="currency" id="currency" class="form-control">
                                    <option value="">-- Select --</option>
                                    @foreach ($currencies as $c)
                                        <option value="{{ $c->code }}" {{ (($deal->currency ?? '') == $c->code) ? 'selected' : '' }}>{{ $c->label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if (! isset($deal))
                            <div class="form-group col-md-6">
                                <label class="crm-label">Pipeline <span class="crm-required">*</span></label>
                                <select name="pipeline_id" id="pipeline_id" class="form-control" required aria-required="true">
                                    @foreach ($pipelines as $p)
                                        <option value="{{ $p->id }}" {{ $p->is_default ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="crm-label">Stage <span class="crm-required">*</span></label>
                                <select name="stage_id" id="stage_id" class="form-control" required aria-required="true"></select>
                            </div>
                            @endif

                            <div class="form-group col-md-6">
                                <label>Owner</label>
                                <select name="owner_id" id="owner_id" class="form-control">
                                    <option value="">-- Unassigned --</option>
                                    @foreach ($users as $u)
                                        <option value="{{ $u->id }}" {{ (($deal->owner_id ?? null) == $u->id) ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Expected Close Date</label>
                                <input type="date" name="expected_close_date" id="expected_close_date" class="form-control" value="{{ $deal->expected_close_date ?? '' }}">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Lead (optional)</label>
                                <select name="lead_id" id="lead_id" class="form-control">
                                    <option value="">-- No linked lead --</option>
                                    @foreach ($leads as $l)
                                        <option value="{{ $l->id }}" {{ (($deal->lead_id ?? null) == $l->id) ? 'selected' : '' }}>
                                            {{ $l->name }}{{ $l->company_name ? ' — '.$l->company_name : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- A linked Lead already carries its own company/contact, so these
                                 only matter (and are only enabled) when no Lead is picked — a
                                 standalone deal not sourced from a lead can still be tied to an
                                 existing Company/Contact instead of floating unlinked. -->
                            <div class="form-group col-md-6">
                                <label>Company <small class="text-muted">(only used without a linked lead)</small></label>
                                <select name="company_id" id="company_id" class="form-control">
                                    <option value="">-- None --</option>
                                    @foreach ($companies as $c)
                                        <option value="{{ $c->id }}" {{ (($deal->company_id ?? null) == $c->id) ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Contact <small class="text-muted">(only used without a linked lead)</small></label>
                                <select name="contact_id" id="contact_id" class="form-control">
                                    <option value="">-- None --</option>
                                    @foreach ($contacts as $c)
                                        <option value="{{ $c->id }}" {{ (($deal->contact_id ?? null) == $c->id) ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-12">
                                <label>Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3">{{ $deal->notes ?? '' }}</textarea>
                            </div>
                        </div>

                        <button type="submit" class="crm-btn crm-btn--primary">
                            <i class="fa fa-save"></i> {{ isset($deal) ? 'Save Changes' : 'Create Deal' }}
                        </button>
                        <a href="{{ route('deals.list') }}" class="crm-btn crm-btn--secondary">Cancel</a>
                    </form>
                </div>
                @endif
                </div>

                @include('include.footer')

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const isEdit = {{ isset($deal) ? 'true' : 'false' }};
        const dealId = {{ isset($deal) ? (int) $deal->id : 'null' }};
        const pipelinesData = @json($pipelines);

        function populateStages(pipelineId, selectedStageId) {
            const pipeline = pipelinesData.find(p => p.id == pipelineId);
            let options = '';
            if (pipeline) {
                pipeline.stages.forEach(s => {
                    options += `<option value="${s.id}" ${s.id == selectedStageId ? 'selected' : ''}>${s.name}</option>`;
                });
            }
            $('#stage_id').html(options);
        }

        $(document).on('change', '#pipeline_id', function() {
            populateStages($(this).val(), null);
        });

        function toggleCompanyContactPickers() {
            const hasLead = !!$('#lead_id').val();
            $('#company_id, #contact_id').prop('disabled', hasLead);
        }
        $(document).on('change', '#lead_id', toggleCompanyContactPickers);

        $(document).on('submit', '#dealForm', function(e) {
            e.preventDefault();
            const data = {};
            $(this).serializeArray().forEach(f => data[f.name] = f.value);

            const url = isEdit ? "{{ url('deals') }}/" + dealId : "{{ route('deals.store') }}";

            $.post(url, data, function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    setTimeout(function() {
                        window.location = "{{ url('deals') }}/" + (isEdit ? dealId : response.id);
                    }, 600);
                } else {
                    toastr.error(response.message);
                }
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Something went wrong');
            });
        });

        $(document).ready(function() {
            if (!isEdit) {
                populateStages($('#pipeline_id').val(), null);
            }
            toggleCompanyContactPickers();
        });
    </script>

</body>

</html>
