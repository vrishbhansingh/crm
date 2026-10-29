<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $campaign->name }} · Campaign report | CRM</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .ecr-card { background: var(--crm-surface, #fff); border: 1px solid var(--crm-border, #dcdfd8); border-radius: 14px; padding: 16px 18px; margin-bottom: 14px; }
        .ecr-title { display: flex; flex-wrap: wrap; gap: 10px 16px; align-items: center; justify-content: space-between; }
        .ecr-title h4 { margin: 0; font-weight: 700; font-size: 19px; color: var(--crm-ink, #171a17); }
        .ecr-sub { font-size: 12.5px; color: var(--crm-ink-muted, #585f57); margin-top: 3px; }
        .ecr-back { font-size: 13px; color: var(--crm-accent, #1f6f63); text-decoration: none; font-weight: 600; }
        .ecr-kpis { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; }
        .ecr-kpi { background: var(--crm-surface, #fff); border: 1px solid var(--crm-border, #dcdfd8); border-radius: 12px; padding: 12px 14px; }
        .ecr-kpi .v { font-size: 22px; font-weight: 700; color: var(--crm-ink, #171a17); line-height: 1.1; }
        .ecr-kpi .v small { font-size: 12.5px; font-weight: 600; color: var(--crm-accent, #1f6f63); margin-left: 4px; }
        .ecr-kpi .l { font-size: 11.5px; text-transform: uppercase; letter-spacing: .04em; color: var(--crm-ink-muted, #585f57); margin-top: 4px; }
        .ecr-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr); gap: 14px; align-items: start; }
        .ecr-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
        .ecr-chip { border: 1px solid var(--crm-border, #dcdfd8); background: var(--crm-surface, #fff); color: var(--crm-ink, #171a17); border-radius: 999px; padding: 4px 12px; font-size: 12.5px; cursor: pointer; }
        .ecr-chip.on { background: var(--crm-accent, #1f6f63); border-color: var(--crm-accent, #1f6f63); color: #fff; }
        .ecr-table { width: 100%; font-size: 13px; }
        .ecr-table th { font-size: 11.5px; text-transform: uppercase; letter-spacing: .04em; color: var(--crm-ink-muted, #585f57); font-weight: 600; padding: 8px 8px; border-bottom: 1px solid var(--crm-border, #dcdfd8); white-space: nowrap; }
        .ecr-table td { padding: 9px 8px; border-bottom: 1px solid var(--crm-border, #eceee9); vertical-align: top; color: var(--crm-ink, #171a17); }
        .ecr-who { font-weight: 600; }
        .ecr-muted { color: var(--crm-ink-muted, #585f57); font-size: 12px; }
        .ecr-tag { display: inline-block; font-size: 11.5px; font-weight: 600; padding: 2px 9px; border-radius: 999px; }
        .ecr-tag.yes { background: var(--crm-success-tint, #e5f3ec); color: var(--crm-success, #1f7a4d); }
        .ecr-tag.no { background: var(--crm-neutral-tint, #eceee9); color: var(--crm-neutral, #585f57); }
        .ecr-tag.bad { background: var(--crm-danger-tint, #fae8e7); color: var(--crm-danger, #b3261e); }
        .ecr-tag.wait { background: var(--crm-warning-tint, #fbf0e1); color: var(--crm-warning, #a6650a); }
        .ecr-link { display: flex; justify-content: space-between; gap: 10px; padding: 7px 0; border-bottom: 1px solid var(--crm-border, #eceee9); font-size: 12.5px; }
        .ecr-link span:first-child { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; color: var(--crm-ink, #171a17); }
        .ecr-ev { display: flex; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--crm-border, #eceee9); font-size: 12.5px; }
        .ecr-ev i { margin-top: 3px; color: var(--crm-accent, #1f6f63); width: 14px; text-align: center; }
        .ecr-empty { text-align: center; padding: 26px 8px; color: var(--crm-ink-muted, #585f57); font-size: 13px; }
        .ecr-note { font-size: 12px; color: var(--crm-ink-muted, #585f57); margin-top: 6px; }
        .ecr-pager { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 12.5px; }
        .ecr-search { max-width: 260px; }
        @media (max-width: 991px) { .ecr-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } .ecr-grid { grid-template-columns: minmax(0, 1fr); } }
    </style>
</head>

<body>
    <div class="container-scroller">
        @include('include.header')

        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="ecr-card">
                    <a class="ecr-back" href="{{ route('campaigns.index') }}"><i class="fa fa-arrow-left"></i> All campaigns</a>
                    <div class="ecr-title mt-2">
                        <div>
                            <h4>{{ $campaign->name }}</h4>
                            <div class="ecr-sub">Template: {{ $campaign->template?->name ?? '—' }} &middot; Audience: {{ ucfirst($campaign->audience_type) }} &middot; <span id="ecrStatus">{{ ucfirst($campaign->status) }}</span><span id="ecrSent"></span></div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="ecrRefresh"><i class="fa fa-rotate"></i> Refresh</button>
                    </div>
                </div>

                <div class="ecr-kpis" id="ecrKpis"></div>

                <div class="ecr-grid">
                    <div class="ecr-card">
                        <div class="ecr-chips" id="ecrChips"></div>
                        <div class="d-flex flex-wrap gap-2 mb-2" style="gap:8px;">
                            <input type="search" id="ecrSearch" class="form-control form-control-sm ecr-search" placeholder="Search by email">
                        </div>
                        <div class="table-responsive">
                            <table class="ecr-table">
                                <thead>
                                    <tr><th>Recipient</th><th>Delivery</th><th>Opened</th><th>Clicked</th></tr>
                                </thead>
                                <tbody id="ecrRows"><tr><td colspan="4" class="ecr-empty">Loading&hellip;</td></tr></tbody>
                            </table>
                        </div>
                        <div class="ecr-pager">
                            <span id="ecrCount"></span>
                            <span>
                                <button class="btn btn-sm btn-outline-secondary" id="ecrPrev">&lsaquo; Prev</button>
                                <span id="ecrPage" class="mx-2"></span>
                                <button class="btn btn-sm btn-outline-secondary" id="ecrNext">Next &rsaquo;</button>
                            </span>
                        </div>
                        <div class="ecr-note">Opens are approximate: some mail apps (e.g. Apple Mail, Gmail) pre-load images, and people who block images are never counted. Clicks are the reliable signal.</div>
                    </div>

                    <div>
                        <div class="ecr-card">
                            <strong>Most clicked links</strong>
                            <div id="ecrLinks" class="mt-2"></div>
                        </div>
                        <div class="ecr-card">
                            <strong>Recent activity</strong>
                            <div id="ecrTimeline" class="mt-2"></div>
                        </div>
                    </div>
                </div>

                @include('include.footer')
            </div>
        </div>
    </div>

    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script>
        const dataUrl = @json(route('campaigns.report.data', $campaign->id));
        const esc = v => $('<div>').text(v ?? '').html();
        const filters = [['all', 'All'], ['opened', 'Opened'], ['clicked', 'Clicked'], ['not_opened', 'Not opened'], ['failed', 'Failed'], ['pending', 'Pending']];
        let state = { filter: 'all', q: '', page: 1 };
        let timer = null;

        function chips() {
            $('#ecrChips').html(filters.map(f => `<button type="button" class="ecr-chip ${state.filter === f[0] ? 'on' : ''}" data-f="${f[0]}">${f[1]}</button>`).join(''));
        }

        function kpi(value, label, extra) {
            return `<div class="ecr-kpi"><div class="v">${value}${extra ? `<small>${extra}</small>` : ''}</div><div class="l">${label}</div></div>`;
        }

        function deliveryTag(r) {
            if (r.status === 'failed') return `<span class="ecr-tag bad" title="${esc(r.error)}">Failed</span>`;
            if (r.status === 'pending') return '<span class="ecr-tag wait">Waiting</span>';
            return `<span class="ecr-tag yes">Sent</span><div class="ecr-muted">${esc(r.sent_at || '')}</div>`;
        }

        function engagement(at, n, noun) {
            return at ? `<span class="ecr-tag yes">${n > 1 ? n + ' ' + noun : 'Yes'}</span><div class="ecr-muted">${esc(at)}</div>` : '<span class="ecr-tag no">No</span>';
        }

        function load() {
            $.get(dataUrl, { filter: state.filter, q: state.q, page: state.page }, res => {
                const d = res.data, s = d.summary;
                $('#ecrSent').text(d.campaign.sent_at ? ' · Sent ' + d.campaign.sent_at : '');
                $('#ecrKpis').html(kpi(s.total, 'Recipients') + kpi(s.sent, 'Delivered', s.failed ? s.failed + ' failed' : '') + kpi(s.opened, 'Opened', s.open_rate + '%') + kpi(s.clicked, 'Clicked', s.click_rate + '%') + kpi(s.pending, 'Waiting to send'));

                $('#ecrRows').html(d.recipients.length ? d.recipients.map(r => `
                    <tr>
                        <td><div class="ecr-who">${esc(r.name || r.email)}</div>${r.name ? `<div class="ecr-muted">${esc(r.email)}</div>` : ''}<div class="ecr-muted text-capitalize">${esc(r.type)}</div></td>
                        <td>${deliveryTag(r)}</td>
                        <td>${engagement(r.opened_at, r.open_count, 'times')}</td>
                        <td>${engagement(r.clicked_at, r.click_count, 'clicks')}</td>
                    </tr>`).join('') : '<tr><td colspan="4" class="ecr-empty">No recipients match this view.</td></tr>');

                const p = d.pagination;
                $('#ecrCount').text(p.total + ' recipient' + (p.total === 1 ? '' : 's'));
                $('#ecrPage').text('Page ' + p.page + ' of ' + p.last_page);
                $('#ecrPrev').prop('disabled', p.page <= 1);
                $('#ecrNext').prop('disabled', p.page >= p.last_page);

                $('#ecrLinks').html(d.top_links.length ? d.top_links.map(l => `<div class="ecr-link"><span title="${esc(l.url)}">${esc(l.url)}</span><span><strong>${l.clicks}</strong> <span class="ecr-muted">· ${l.people} ${l.people == 1 ? 'person' : 'people'}</span></span></div>`).join('') : '<div class="ecr-empty">No link clicks yet.</div>');

                $('#ecrTimeline').html(d.timeline.length ? d.timeline.map(e => `
                    <div class="ecr-ev"><i class="fa ${e.type === 'click' ? 'fa-hand-pointer' : 'fa-envelope-open'}"></i>
                        <div><strong>${esc(e.name || e.email || 'Someone')}</strong> ${e.type === 'click' ? 'clicked a link' : 'opened the email'}
                        ${e.type === 'click' && e.url ? `<div class="ecr-muted" style="word-break:break-all;">${esc(e.url)}</div>` : ''}
                        <div class="ecr-muted">${esc(e.when)} (${esc(e.ago)})</div></div></div>`).join('') : '<div class="ecr-empty">Nothing yet. Opens and clicks appear here as they happen.</div>');
            });
        }

        $(document).on('click', '.ecr-chip', function () { state.filter = $(this).data('f'); state.page = 1; chips(); load(); });
        $('#ecrPrev').on('click', () => { state.page--; load(); });
        $('#ecrNext').on('click', () => { state.page++; load(); });
        $('#ecrRefresh').on('click', load);
        $('#ecrSearch').on('input', function () { clearTimeout(timer); const v = $(this).val(); timer = setTimeout(() => { state.q = v; state.page = 1; load(); }, 300); });

        chips();
        load();
    </script>
</body>
</html>
