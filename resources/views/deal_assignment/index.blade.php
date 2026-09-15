<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Deal Assignment | CRM Admin</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">

    <style>
        :root { --primary: #16a34a; --border: #e5e7eb; --text-dark: #111827; --text-muted: #6b7280; --surface: #f8fafc; }

        .crm-page-header {
            background: #fff; padding: 18px 22px; border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06); border-left: 4px solid var(--primary);
            display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 18px; flex-wrap: wrap;
        }
        .crm-header-left { display: flex; align-items: center; gap: 14px; }
        .crm-header-icon {
            width: 42px; height: 42px; border-radius: 10px;
            background: linear-gradient(135deg, #15803d, #16a34a); color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .crm-page-header h4 { font-weight: 700; font-size: 18px; color: var(--text-dark); margin: 0; }
        .crm-subtitle { color: var(--text-muted); font-size: 13px; }

        .la-tabs { display: flex; gap: 4px; background: #fff; border-radius: 13px; padding: 6px; box-shadow: 0 8px 24px rgba(15,23,42,.06); margin-bottom: 18px; flex-wrap: wrap; }
        .la-tab-btn { border: none; background: transparent; padding: 10px 18px; border-radius: 9px; font-size: 13.5px; font-weight: 600; color: #64748b; cursor: pointer; }
        .la-tab-btn:hover { background: var(--surface); color: #1e293b; }
        .la-tab-btn.active { background: #ecfdf5; color: #15803d; }
        .la-panel { display: none; }
        .la-panel.active { display: block; }

        .la-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
        .la-panel-head p { margin: 0; color: var(--text-muted); font-size: 13.5px; }

        .la-card { background: #fff; border-radius: 14px; box-shadow: 0 8px 24px rgba(15,23,42,.06); min-height: 220px; }
        .la-rule-row {
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
            padding: 16px 20px; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap;
        }
        .la-rule-row:last-child { border-bottom: none; }
        .la-rule-value { font-weight: 700; font-size: 14.5px; color: var(--text-dark); display: flex; align-items: center; gap: 8px; }
        .la-rule-value .fa-chevron-right { color: #cbd5e1; font-size: 11px; }
        .la-agent-chips { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px; }
        .la-agent-chip { background: var(--surface); color: #334155; font-size: 11.5px; font-weight: 600; padding: 3px 10px; border-radius: 999px; }
        .la-rule-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .la-status-pill { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
        .la-status-pill.on { background: #dcfce7; color: #15803d; }
        .la-status-pill.off { background: #f1f5f9; color: #94a3b8; }
        .la-icon-btn { width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--border); background: #fff; color: var(--text-muted); display: flex; align-items: center; justify-content: center; }
        .la-icon-btn:hover { background: var(--surface); color: var(--text-dark); }
        .la-icon-btn.danger:hover { background: #fef2f2; color: #dc2626; border-color: #fecaca; }

        .la-empty { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .la-empty i { font-size: 34px; opacity: .4; display: block; margin-bottom: 12px; }
        .la-empty h6 { font-weight: 700; color: var(--text-dark); margin-bottom: 4px; }

        .la-agent-picker { display: flex; flex-direction: column; gap: 2px; max-height: 220px; overflow-y: auto; border: 1px solid var(--border); border-radius: 10px; padding: 6px; }
        .la-agent-option { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 8px; }
        .la-agent-option:hover { background: var(--surface); }
        .la-agent-option input { margin: 0; }
        .la-agent-hint { font-size: 12px; color: var(--text-muted); margin-top: 6px; }

        [data-theme="dark"] { --border: #2a2e40; --text-dark: #eef0f6; --text-muted: #9aa1b5; --surface: #232637; }
        [data-theme="dark"] .crm-page-header,
        [data-theme="dark"] .la-tabs,
        [data-theme="dark"] .la-card { background: #1a1d2b; box-shadow: 0 8px 24px rgba(0,0,0,.35); }
        [data-theme="dark"] .la-tab-btn { color: #9aa1b5; }
        [data-theme="dark"] .la-tab-btn:hover { background: #232637; color: #eef0f6; }
        [data-theme="dark"] .la-tab-btn.active { background: rgba(34,197,94,.16); color: #4ade80; }
        [data-theme="dark"] .la-rule-row { border-bottom-color: #2a2e40; }
        [data-theme="dark"] .la-agent-chip { background: #232637; color: #d7dbe4; }
        [data-theme="dark"] .la-icon-btn { background: #1a1d2b; border-color: #2a2e40; color: #9aa1b5; }
        [data-theme="dark"] .la-icon-btn:hover { background: #232637; color: #eef0f6; }
        [data-theme="dark"] .la-status-pill.off { background: #232637; color: #6b7280; }
        [data-theme="dark"] .la-agent-picker { border-color: #2a2e40; }
    </style>
</head>

<body>
    <div class="container-scroller">
        @include('include.header')

        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="crm-page-header">
                    <div class="crm-header-left">
                        <div class="crm-header-icon"><i class="fa fa-share-square-o"></i></div>
                        <div>
                            <h4>Deal Assignment Rules</h4>
                            <small class="crm-subtitle">Configure automatic deal assignment rules. Priority: Pipeline &rarr; Source &rarr; Round Robin.</small>
                        </div>
                    </div>
                </div>

                <div class="la-tabs" id="laTabs">
                    <button class="la-tab-btn active" data-tab="pipeline">Pipeline Rules</button>
                    <button class="la-tab-btn" data-tab="source">Source Rules</button>
                    <button class="la-tab-btn" data-tab="round_robin">Round Robin</button>
                </div>

                <div class="la-panel active" id="panel-pipeline">
                    <div class="la-panel-head">
                        <p>Assign deals to agents based on pipeline. Multiple agents use round-robin.</p>
                        @can('deals.manage-settings')
                        <button class="btn btn-success btn-sm add-rule-btn" data-type="pipeline"><i class="fa fa-plus"></i> Add Rule</button>
                        @endcan
                    </div>
                    <div class="la-card" id="list-pipeline"></div>
                </div>

                <div class="la-panel" id="panel-source">
                    <div class="la-panel-head">
                        <p>Assign deals to agents based on where the deal came from. Multiple agents use round-robin.</p>
                        @can('deals.manage-settings')
                        <button class="btn btn-success btn-sm add-rule-btn" data-type="source"><i class="fa fa-plus"></i> Add Rule</button>
                        @endcan
                    </div>
                    <div class="la-card" id="list-source"></div>
                </div>

                <div class="la-panel" id="panel-round_robin">
                    <div class="la-panel-head">
                        <p>The final fallback — any deal that doesn't match a Pipeline or Source rule goes to this pool, round-robin.</p>
                        @can('deals.manage-settings')
                        <button class="btn btn-success btn-sm add-rule-btn" data-type="round_robin"><i class="fa fa-plus"></i> Set Pool</button>
                        @endcan
                    </div>
                    <div class="la-card" id="list-round_robin"></div>
                </div>

                @include('include.footer')
            </div>
        </div>
    </div>

    {{-- Add / Edit rule modal --}}
    <div class="modal fade" id="ruleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="ruleForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ruleModalTitle">Add Rule</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="ruleId">
                        <input type="hidden" name="rule_type" id="ruleType">

                        <div class="form-group" id="matchValueGroup">
                            <label id="matchValueLabel">Pipeline</label>
                            <select id="matchValuePipeline" class="form-control" style="display:none;"></select>
                            <select id="matchValueSource" class="form-control" style="display:none;"></select>
                        </div>

                        <div class="form-group">
                            <label>Assign to <small class="text-muted">(select one or more — multiple agents round-robin)</small></label>
                            <div class="la-agent-picker" id="agentPicker">
                                @foreach($agents as $agent)
                                <label class="la-agent-option">
                                    <input type="checkbox" name="agent_ids[]" value="{{ $agent->id }}">
                                    <span>{{ $agent->name }}</span>
                                </label>
                                @endforeach
                            </div>
                            @if($agents->isEmpty())
                                <div class="la-agent-hint">No active users yet — add a team member first.</div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Rule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        const esc = v => $('<div>').text(v ?? '').html();
        const TAB_LABELS = { pipeline: 'Pipeline', source: 'Source', round_robin: 'Round Robin' };
        const PIPELINES = @json($pipelines);
        const LEAD_SOURCES = @json($leadSources);

        $('#laTabs').on('click', '.la-tab-btn', function () {
            const tab = $(this).data('tab');
            $('.la-tab-btn').removeClass('active').filter(`[data-tab="${tab}"]`).addClass('active');
            $('.la-panel').removeClass('active');
            $(`#panel-${tab}`).addClass('active');
        });

        function agentChips(agents) {
            return agents.map(a => `<span class="la-agent-chip">${esc(a.name)}</span>`).join('');
        }

        function renderRuleRow(rule, type) {
            const toggleLabel = rule.is_active ? 'Pause' : 'Activate';
            const valueLabel = type === 'round_robin' ? 'Everyone else' : esc(rule.match_label ?? rule.match_value);
            return `
                <div class="la-rule-row" data-id="${rule.id}">
                    <div>
                        <div class="la-rule-value">${valueLabel} <i class="fa fa-chevron-right"></i> ${rule.agents.length} agent${rule.agents.length === 1 ? '' : 's'}</div>
                        <div class="la-agent-chips">${agentChips(rule.agents)}</div>
                    </div>
                    <div class="la-rule-actions">
                        <span class="la-status-pill ${rule.is_active ? 'on' : 'off'}">${rule.is_active ? 'Active' : 'Paused'}</span>
                        @can('deals.manage-settings')
                        <button class="la-icon-btn toggle-rule-btn" data-id="${rule.id}" data-active="${rule.is_active ? 1 : 0}" title="${toggleLabel}"><i class="fa fa-${rule.is_active ? 'pause' : 'play'}"></i></button>
                        <button class="la-icon-btn edit-rule-btn" data-id="${rule.id}" data-type="${type}" title="Edit"><i class="fa fa-pencil"></i></button>
                        ${type !== 'round_robin' ? `<button class="la-icon-btn danger delete-rule-btn" data-id="${rule.id}" title="Delete"><i class="fa fa-trash"></i></button>` : ''}
                        @endcan
                    </div>
                </div>`;
        }

        function emptyState(type) {
            const copy = {
                pipeline: ['No Pipeline rules', 'Create a rule to auto-assign deals based on pipeline.'],
                source: ['No Source rules', 'Create a rule to auto-assign deals based on source.'],
                round_robin: ['No fallback pool set', 'Set a pool so deals matching nothing else still get assigned automatically.'],
            }[type];
            return `<div class="la-empty"><i class="fa fa-inbox"></i><h6>${copy[0]}</h6><p>${copy[1]}</p></div>`;
        }

        window.currentRules = {};

        function load() {
            $.get("{{ route('deal_assignment.data') }}", function (res) {
                window.currentRules = res.data;
                Object.keys(res.data).forEach(type => {
                    const rows = res.data[type];
                    $(`#list-${type}`).html(rows.length ? rows.map(r => renderRuleRow(r, type)).join('') : emptyState(type));
                });
            });
        }

        function openModal(type, rule) {
            $('#ruleForm')[0].reset();
            $('#ruleId').val(rule ? rule.id : '');
            $('#ruleType').val(type);
            $('#ruleModalTitle').text(rule ? 'Edit Rule' : (type === 'round_robin' ? 'Set Round Robin Pool' : 'Add Rule'));

            $('#matchValuePipeline, #matchValueSource').hide();
            if (type === 'pipeline') {
                let opts = '<option value="">Select a pipeline…</option>';
                PIPELINES.forEach(p => { opts += `<option value="${p.id}">${esc(p.name)}</option>`; });
                $('#matchValuePipeline').html(opts).show();
                if (rule) $('#matchValuePipeline').val(rule.match_value);
            } else if (type === 'source') {
                let opts = '<option value="">Select a source…</option>';
                LEAD_SOURCES.forEach(s => { opts += `<option value="${esc(s.code)}">${esc(s.label)}</option>`; });
                $('#matchValueSource').html(opts).show();
                if (rule) $('#matchValueSource').val(rule.match_value);
            }

            $('#matchValueGroup').toggle(type !== 'round_robin');
            $('#matchValueLabel').text(TAB_LABELS[type]);

            $('#agentPicker input[type="checkbox"]').prop('checked', false);
            if (rule) {
                rule.agents.forEach(a => $(`#agentPicker input[value="${a.id}"]`).prop('checked', true));
            }

            $('#ruleModal').modal('show');
        }

        $(document).on('click', '.add-rule-btn', function () {
            const type = $(this).data('type');
            if (type === 'round_robin' && window.currentRules.round_robin && window.currentRules.round_robin.length) {
                openModal(type, window.currentRules.round_robin[0]);
            } else {
                openModal(type, null);
            }
        });

        $(document).on('click', '.edit-rule-btn', function () {
            const type = $(this).data('type');
            const id = $(this).data('id');
            const rule = (window.currentRules[type] || []).find(r => r.id === id);
            openModal(type, rule);
        });

        $(document).on('click', '.delete-rule-btn', function () {
            const id = $(this).data('id');
            if (!confirm('Remove this rule? Deals will fall through to the next priority level instead.')) return;
            $.ajax({ url: `{{ url('settings/deal-assignment') }}/${id}`, method: 'DELETE' })
                .done(res => { toastr.success(res.message); load(); })
                .fail(x => toastr.error(x.responseJSON?.message || 'Could not delete this rule.'));
        });

        $(document).on('click', '.toggle-rule-btn', function () {
            const id = $(this).data('id');
            const nowActive = !$(this).data('active');
            $.ajax({ url: `{{ url('settings/deal-assignment') }}/${id}`, method: 'PUT', data: { is_active: nowActive ? 1 : 0 } })
                .done(() => load())
                .fail(x => toastr.error(x.responseJSON?.message || 'Could not update this rule.'));
        });

        $('#ruleForm').on('submit', function (e) {
            e.preventDefault();
            const type = $('#ruleType').val();
            const id = $('#ruleId').val();
            const matchValue = type === 'pipeline' ? $('#matchValuePipeline').val() : (type === 'source' ? $('#matchValueSource').val() : null);
            const agentIds = $('#agentPicker input[type="checkbox"]:checked').map(function () { return this.value; }).get();

            if (type !== 'round_robin' && !matchValue) { toastr.error('Pick a value to match on.'); return; }
            if (!agentIds.length) { toastr.error('Pick at least one agent.'); return; }

            const payload = { rule_type: type, match_value: matchValue, agent_ids: agentIds };
            const url = id ? `{{ url('settings/deal-assignment') }}/${id}` : "{{ route('deal_assignment.store') }}";
            const method = id ? 'PUT' : 'POST';

            $.ajax({ url, method, data: payload })
                .done(res => { toastr.success(res.message); $('#ruleModal').modal('hide'); load(); })
                .fail(x => toastr.error(x.responseJSON?.message || 'Could not save this rule.'));
        });

        load();
    </script>
</body>
</html>
