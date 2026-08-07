<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($deal) ? 'Edit Deal' : 'New Deal' }} | CRM Admin</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        .card-box {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            padding: 22px;
            margin-bottom: 20px;
        }

        .page-header {
            background: #ffffff;
            padding: 18px 22px;
            border-radius: 14px;
            margin-bottom: 18px;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05);
        }

        .page-header h4 {
            font-weight: 700;
            margin: 0;
        }
    </style>
</head>

<body>

    <div class="container-scroller">

        @include('include.header')

        <div class="container-fluid page-body-wrapper">

            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="page-header">
                    <a href="{{ route('deals.index') }}" class="text-muted" style="font-size:12px;">
                        <i class="fa fa-arrow-left"></i> Back to Deals
                    </a>
                    <h4 class="mt-1">{{ isset($deal) ? 'Edit Deal' : 'New Deal' }}</h4>
                </div>

                <div class="card-box">
                    <form id="dealForm">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Deal Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ $deal->name ?? '' }}" required>
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
                                <label>Pipeline</label>
                                <select name="pipeline_id" id="pipeline_id" class="form-control" required>
                                    @foreach ($pipelines as $p)
                                        <option value="{{ $p->id }}" {{ $p->is_default ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Stage</label>
                                <select name="stage_id" id="stage_id" class="form-control" required></select>
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

                            <div class="form-group col-md-12">
                                <label>Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3">{{ $deal->notes ?? '' }}</textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> {{ isset($deal) ? 'Save Changes' : 'Create Deal' }}
                        </button>
                        <a href="{{ route('deals.index') }}" class="btn btn-light">Cancel</a>
                    </form>
                </div>

                @include('include.footer')

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

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
        });
    </script>

</body>

</html>
