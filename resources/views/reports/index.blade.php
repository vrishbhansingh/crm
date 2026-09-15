<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Reports & Analytics</title>
<link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}"><link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}"><link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}"><link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    /* Same modernization pattern as the rest of this pass. */
    .crm-page-header{background:#fff;padding:20px 22px;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06);margin-bottom:18px;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px}
    .crm-page-header h3{margin:0 0 6px;font-weight:700;font-size:18px;color:#111827}.crm-page-header p{margin:0;color:#6b7280;font-size:14px}
    .report-card{border:0;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.metric-value{font-size:28px;font-weight:700;color:#1e3a8a}.metric-label{font-size:12.5px;color:#64748b;text-transform:uppercase;letter-spacing:.04em;font-weight:600}.chart-box{height:310px}.filter-bar{display:flex;gap:10px;align-items:end;flex-wrap:wrap}.filter-bar label{font-size:12.5px;color:#64748b}.filter-bar .btn{border-radius:10px;padding:8px 16px;font-weight:600}
    .report-card h5{font-weight:700;font-size:15.5px;margin-bottom:16px;color:#111827}
    .metric-value.sm{font-size:22px}

    /* ===== Report tabs — one page, click a tab instead of navigating to a
       new one, so switching report types costs nothing extra. ===== */
    .report-tabs{display:flex;gap:4px;background:#fff;border-radius:13px;padding:6px;box-shadow:0 8px 24px rgba(15,23,42,.06);margin-bottom:18px;flex-wrap:wrap}
    .report-tab-btn{border:none;background:transparent;padding:10px 16px;border-radius:9px;font-size:13.5px;font-weight:600;color:#64748b;cursor:pointer;display:flex;align-items:center;gap:8px;white-space:nowrap}
    .report-tab-btn:hover{background:#f8fafc;color:#1e293b}
    .report-tab-btn.active{background:#eff6ff;color:#1d4ed8}
    .report-tab-btn i{font-size:13px}
    .report-tab{display:none}
    .report-tab.active{display:block}
    .report-loading{text-align:center;color:#94a3b8;padding:40px 0}

    /* ===== Simple data tables shared by the new report tabs ===== */
    .simple-table{width:100%;border-collapse:collapse}
    .simple-table thead th{font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;font-weight:700;text-align:left;padding:0 12px 12px;border-bottom:1px solid #eef1f6}
    .simple-table thead th:not(:first-child){text-align:right}
    .simple-table tbody td{padding:13px 12px;font-size:13.5px;border-bottom:1px solid #f1f5f9}
    .simple-table tbody tr:last-child td{border-bottom:none}
    .simple-table tbody td:not(:first-child){text-align:right;font-weight:600;color:#1e293b}
    .simple-table tbody td:first-child{font-weight:700;color:#1e293b}
    .simple-empty{text-align:center;color:#94a3b8;padding:28px 0}
    .pct-bar-wrap{display:flex;align-items:center;gap:8px}
    .pct-bar{flex:1;height:6px;border-radius:99px;background:#eef1f6;overflow:hidden;min-width:60px}
    .pct-bar span{display:block;height:100%;background:#2563eb;border-radius:99px}
    .status-chip{display:inline-block;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:600}

    /* ===== Monthly Summary table ===== */
    .summary-table{width:100%;border-collapse:collapse}
    .summary-table thead th{font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;font-weight:700;text-align:left;padding:0 12px 12px;border-bottom:1px solid #eef1f6}
    .summary-table thead th:not(:first-child){text-align:right}
    .summary-table tbody td{padding:16px 12px;font-size:14px;border-bottom:1px solid #f1f5f9}
    .summary-table tbody tr:last-child td{border-bottom:none}
    .summary-table tbody td:not(:first-child){text-align:right;font-weight:600;color:#1e293b}
    .summary-table tbody td:first-child{font-weight:700;color:#1e293b}
    .summary-growth.is-up{color:#16a34a}
    .summary-growth.is-down{color:#dc2626}
    .summary-growth.is-flat{color:#94a3b8}
    .summary-empty{text-align:center;color:#94a3b8;padding:28px 0}

    [data-theme="dark"] .crm-page-header{background:#1a1d2b;box-shadow:0 8px 24px rgba(0,0,0,.35)}
    [data-theme="dark"] .crm-page-header h3{color:#eef0f6}[data-theme="dark"] .crm-page-header p{color:#9aa1b5}
    [data-theme="dark"] .filter-bar label{color:#9aa1b5}
    [data-theme="dark"] .report-card{background:#1a1d2b;box-shadow:0 8px 24px rgba(0,0,0,.35)}
    [data-theme="dark"] .report-card h5{color:#eef0f6}
    [data-theme="dark"] .metric-value{color:#93a4fd}
    [data-theme="dark"] .metric-label{color:#9aa1b5}
    [data-theme="dark"] .report-tabs{background:#1a1d2b;box-shadow:0 8px 24px rgba(0,0,0,.35)}
    [data-theme="dark"] .report-tab-btn{color:#9aa1b5}
    [data-theme="dark"] .report-tab-btn:hover{background:#232637;color:#eef0f6}
    [data-theme="dark"] .report-tab-btn.active{background:rgba(147,164,253,.16);color:#93a4fd}
    [data-theme="dark"] .simple-table thead th{color:#9aa1b5;border-bottom-color:#2a2e40}
    [data-theme="dark"] .simple-table tbody td{border-bottom-color:#2a2e40}
    [data-theme="dark"] .simple-table tbody td:first-child,[data-theme="dark"] .simple-table tbody td:not(:first-child){color:#eef0f6}
    [data-theme="dark"] .simple-empty{color:#9aa1b5}
    [data-theme="dark"] .pct-bar{background:#232637}
    [data-theme="dark"] .summary-table thead th{color:#9aa1b5;border-bottom-color:#2a2e40}
    [data-theme="dark"] .summary-table tbody td{border-bottom-color:#2a2e40}
    [data-theme="dark"] .summary-table tbody td:first-child,
    [data-theme="dark"] .summary-table tbody td:not(:first-child){color:#eef0f6}
    [data-theme="dark"] .summary-growth.is-up{color:#4ade80}
    [data-theme="dark"] .summary-growth.is-down{color:#fca5a5}
    [data-theme="dark"] .summary-growth.is-flat{color:#9aa1b5}
    [data-theme="dark"] .summary-empty{color:#9aa1b5}
</style></head>
<body><div class="container-scroller">@include('include.header')<div class="container-fluid page-body-wrapper">@include('include.sidebar')<div class="main-panel"><div class="content-wrapper">

<div class="crm-page-header"><div><h3>Reports & Analytics</h3><p>Sales, leads, follow-ups, team, communications, and automation — one date range, six views.</p></div><div class="filter-bar"><div><label>From</label><input id="from" type="date" class="form-control"></div><div><label>To</label><input id="to" type="date" class="form-control"></div><button class="btn btn-primary" id="runReport">Run report</button></div></div>

<div class="report-tabs" id="reportTabs">
    <button class="report-tab-btn active" data-tab="overview"><i class="fa fa-bar-chart"></i> Overview</button>
    <button class="report-tab-btn" data-tab="leads"><i class="fa fa-bullseye"></i> Leads</button>
    <button class="report-tab-btn" data-tab="followups"><i class="fa fa-phone"></i> Follow-ups</button>
    <button class="report-tab-btn" data-tab="agents"><i class="fa fa-users"></i> Team</button>
    <button class="report-tab-btn" data-tab="comms"><i class="fa fa-envelope"></i> Communications</button>
    <button class="report-tab-btn" data-tab="automation"><i class="fa fa-bolt"></i> Automation</button>
</div>

{{-- ============================= OVERVIEW ============================= --}}
<div class="report-tab active" id="tab-overview">
    <div class="row" id="metrics">@foreach(['Leads created','Deals created','Won deals','Win rate','Pipeline value','Booked revenue','Cash collected'] as $label)<div class="col-xl col-md-4 col-sm-6 mb-3"><div class="card report-card h-100"><div class="card-body"><div class="metric-label">{{ $label }}</div><div class="metric-value">—</div></div></div></div>@endforeach</div>

    <div class="row">
        <div class="col-lg-5 mb-4"><div class="card report-card"><div class="card-body"><h5>Lead Sources</h5><div class="chart-box"><canvas id="sourceChart"></canvas></div></div></div></div>
        <div class="col-lg-7 mb-4"><div class="card report-card"><div class="card-body"><h5>Revenue Overview</h5><div class="chart-box"><canvas id="trendChart"></canvas></div></div></div></div>
    </div>

    <div class="row">
        <div class="col-12 mb-4"><div class="card report-card"><div class="card-body"><h5>Monthly Summary</h5><div class="table-responsive"><table class="summary-table"><thead><tr><th>Month</th><th>Revenue</th><th>Cash Collected</th><th>Outstanding</th><th>Growth</th></tr></thead><tbody id="monthlySummaryRows"><tr><td colspan="5" class="summary-empty">Loading…</td></tr></tbody></table></div></div></div></div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4"><div class="card report-card"><div class="card-body"><h5>Deal funnel</h5><div class="chart-box"><canvas id="funnelChart"></canvas></div></div></div></div>
        <div class="col-lg-6 mb-4"><div class="card report-card"><div class="card-body"><h5>Owner performance</h5><div class="table-responsive"><table class="table"><thead><tr><th>Owner</th><th>Deals</th><th>Won</th><th>Won value</th></tr></thead><tbody id="ownerRows"></tbody></table></div></div></div></div>
    </div>
</div>

{{-- ============================== LEADS ================================ --}}
<div class="report-tab" id="tab-leads">
    <div class="row mb-1">
        @foreach(['Total leads' => 'leadsTotal', 'Converted' => 'leadsConverted', 'Avg. days to convert' => 'leadsAvgDays', 'No follow-up scheduled' => 'leadsStale'] as $label => $id)
        <div class="col-xl-3 col-md-6 mb-3"><div class="card report-card h-100"><div class="card-body"><div class="metric-label">{{ $label }}</div><div class="metric-value sm" id="{{ $id }}">—</div></div></div></div>
        @endforeach
    </div>
    <div class="row">
        <div class="col-lg-4 mb-4"><div class="card report-card"><div class="card-body"><h5>By status</h5><div class="table-responsive"><table class="simple-table"><thead><tr><th>Status</th><th>Leads</th></tr></thead><tbody id="leadsByStatus"></tbody></table></div></div></div></div>
        <div class="col-lg-4 mb-4"><div class="card report-card"><div class="card-body"><h5>By priority</h5><div class="table-responsive"><table class="simple-table"><thead><tr><th>Priority</th><th>Leads</th></tr></thead><tbody id="leadsByPriority"></tbody></table></div></div></div></div>
        <div class="col-lg-4 mb-4"><div class="card report-card"><div class="card-body"><h5>Best-converting sources</h5><div class="table-responsive"><table class="simple-table"><thead><tr><th>Source</th><th>Rate</th></tr></thead><tbody id="leadsTopSources"></tbody></table></div></div></div></div>
    </div>
    <div class="row">
        <div class="col-12 mb-4"><div class="card report-card"><div class="card-body"><h5>Lead source performance</h5><div class="table-responsive"><table class="simple-table"><thead><tr><th>Source</th><th>Leads</th><th>Converted</th><th>Conversion rate</th></tr></thead><tbody id="leadsBySource"></tbody></table></div></div></div></div>
    </div>
</div>

{{-- ============================= FOLLOW-UPS ============================= --}}
<div class="report-tab" id="tab-followups">
    <div class="row mb-1">
        @foreach(['Follow-ups completed' => 'fuCompleted', 'Scheduled in range' => 'fuScheduled', 'Overdue right now' => 'fuOverdue'] as $label => $id)
        <div class="col-xl-4 col-md-6 mb-3"><div class="card report-card h-100"><div class="card-body"><div class="metric-label">{{ $label }}</div><div class="metric-value sm" id="{{ $id }}">—</div></div></div></div>
        @endforeach
    </div>
    <div class="row">
        <div class="col-12 mb-4"><div class="card report-card"><div class="card-body"><h5>Follow-up discipline by agent</h5><p class="text-muted" style="font-size:12.5px;margin-top:-10px;">Completed = calls actually logged. Overdue = scheduled follow-ups still waiting, past their date.</p><div class="table-responsive"><table class="simple-table"><thead><tr><th>Agent</th><th>Completed</th><th>Scheduled</th><th>Overdue</th></tr></thead><tbody id="fuByAgent"></tbody></table></div></div></div></div>
    </div>
</div>

{{-- =============================== TEAM ================================ --}}
<div class="report-tab" id="tab-agents">
    <div class="row">
        <div class="col-12 mb-4"><div class="card report-card"><div class="card-body"><h5>Agent performance</h5><div class="table-responsive"><table class="simple-table"><thead><tr><th>Agent</th><th>Leads</th><th>Converted</th><th>Deals won</th><th>Won value</th><th>Follow-ups</th><th>Tasks done</th><th>Tasks overdue</th></tr></thead><tbody id="agentRows"></tbody></table></div></div></div></div>
    </div>
</div>

{{-- ========================== COMMUNICATIONS =========================== --}}
<div class="report-tab" id="tab-comms">
    <div class="row mb-1">
        @foreach(['Email campaigns' => 'commEmailCampaigns', 'Emails sent' => 'commEmailSent', 'WhatsApp sent' => 'commWaSent', 'WhatsApp received' => 'commWaReceived'] as $label => $id)
        <div class="col-xl-3 col-md-6 mb-3"><div class="card report-card h-100"><div class="card-body"><div class="metric-label">{{ $label }}</div><div class="metric-value sm" id="{{ $id }}">—</div></div></div></div>
        @endforeach
    </div>
    <div class="row">
        <div class="col-lg-6 mb-4"><div class="card report-card"><div class="card-body"><h5>Recent email campaigns</h5><div class="table-responsive"><table class="simple-table"><thead><tr><th>Campaign</th><th>Sent</th><th>Failed</th></tr></thead><tbody id="commEmailRows"></tbody></table></div></div></div></div>
        <div class="col-lg-6 mb-4"><div class="card report-card"><div class="card-body"><h5>Recent WhatsApp campaigns</h5><div class="table-responsive"><table class="simple-table"><thead><tr><th>Campaign</th><th>Sent</th><th>Failed</th></tr></thead><tbody id="commWaRows"></tbody></table></div></div></div></div>
    </div>
    <div class="row">
        <div class="col-12 mb-4"><div class="card report-card"><div class="card-body"><h5>Connected WhatsApp accounts</h5><div class="table-responsive"><table class="simple-table"><thead><tr><th>Account</th><th>Channel</th><th>Sent (all-time)</th><th>Received (all-time)</th></tr></thead><tbody id="commWaAccounts"></tbody></table></div></div></div></div>
    </div>
</div>

{{-- ============================= AUTOMATION ============================ --}}
<div class="report-tab" id="tab-automation">
    <div class="row mb-1">
        @foreach(['Leads captured via webhook' => 'autoWebhookLeads', 'Reminder notifications sent' => 'autoReminders', 'Scheduled campaigns dispatched' => 'autoScheduled', 'Duplicates/ignored' => 'autoIgnored'] as $label => $id)
        <div class="col-xl-3 col-md-6 mb-3"><div class="card report-card h-100"><div class="card-body"><div class="metric-label">{{ $label }}</div><div class="metric-value sm" id="{{ $id }}">—</div></div></div></div>
        @endforeach
    </div>
    <div class="row">
        <div class="col-lg-6 mb-4"><div class="card report-card"><div class="card-body"><h5>Webhook leads by platform</h5><div class="table-responsive"><table class="simple-table"><thead><tr><th>Platform</th><th>Integrations</th><th>Leads created</th></tr></thead><tbody id="autoByPlatform"></tbody></table></div></div></div></div>
        <div class="col-lg-6 mb-4"><div class="card report-card"><div class="card-body"><h5>Reminders sent, by type</h5><div class="table-responsive"><table class="simple-table"><thead><tr><th>Type</th><th>Sent</th></tr></thead><tbody id="autoByReminderType"></tbody></table></div></div></div></div>
    </div>
</div>

</div>@include('include.footer')</div></div></div><script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script><script src="{{ asset('vendors/chart.js/Chart.min.js') }}"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/0.7.0/chartjs-plugin-datalabels.min.js"></script><script>
(() => {let charts={};const money=new Intl.NumberFormat(undefined,{style:'currency',currency:'INR',maximumFractionDigits:0});const moneyCompact=new Intl.NumberFormat(undefined,{style:'currency',currency:'INR',notation:'compact',maximumFractionDigits:1});const esc=v=>$('<div>').text(v??'').html();const metricKeys=['leads_created','deals_created','won_deals','win_rate','pipeline_value','booked_revenue','cash_collected'];
const SOURCE_COLORS=['#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#64748b','#ec4899'];
function applyChartTheme(){const isDark=document.documentElement.getAttribute('data-theme')==='dark';Chart.defaults.global.defaultFontColor=isDark?'#9aa1b5':'#6b7280';Chart.defaults.scale.gridLines.color=isDark?'#2a2e40':'rgba(0,0,0,.1)';Chart.defaults.scale.gridLines.zeroLineColor=isDark?'#2a2e40':'rgba(0,0,0,.25)';}
applyChartTheme();document.addEventListener('crm-theme-changed',function(){applyChartTheme();if(activeTab==='overview')loadOverview();});
function draw(id,type,labels,datasets,options={}){if(charts[id])charts[id].destroy();charts[id]=new Chart(document.getElementById(id),{type,data:{labels,datasets},options:{responsive:true,maintainAspectRatio:false,legend:{position:'bottom'},...options}})}
function humanize(s){return String(s??'').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase());}
function fmtDate(v){return v?new Date(v).toLocaleDateString():'—';}

function drawSourcePie(sources){
    const labels=sources.map(x=>x.label);
    const values=sources.map(x=>x.total);
    const total=values.reduce((a,b)=>a+b,0);
    const colors=labels.map((_,i)=>SOURCE_COLORS[i%SOURCE_COLORS.length]);
    if(charts.sourceChart)charts.sourceChart.destroy();
    charts.sourceChart=new Chart(document.getElementById('sourceChart'),{
        type:'pie',
        data:{labels,datasets:[{data:values,backgroundColor:colors}]},
        options:{
            responsive:true,maintainAspectRatio:false,
            legend:{position:'bottom',labels:{usePointStyle:true,generateLabels:function(chart){const d=chart.data;return d.labels.map((label,i)=>{const pct=total?Math.round((d.datasets[0].data[i]/total)*100):0;return{text:`${label} — ${pct}%`,fillStyle:d.datasets[0].backgroundColor[i],strokeStyle:d.datasets[0].backgroundColor[i],lineWidth:0,index:i};});}}},
            plugins:{datalabels:{color:'#fff',font:{weight:'700',size:12},formatter:(value)=>{const pct=total?Math.round((value/total)*100):0;return pct>=6?`${pct}%`:'';}}}
        }
    });
}

function drawRevenueBars(trend){
    if(charts.trendChart)charts.trendChart.destroy();
    charts.trendChart=new Chart(document.getElementById('trendChart'),{
        type:'bar',
        data:{labels:trend.labels,datasets:[{label:'Booked revenue',data:trend.revenue,backgroundColor:'#2563eb',borderRadius:6,maxBarThickness:38},{label:'Cash collected',data:trend.cash,backgroundColor:'#10b981',borderRadius:6,maxBarThickness:38}]},
        options:{responsive:true,maintainAspectRatio:false,legend:{position:'bottom'},scales:{yAxes:[{ticks:{beginAtZero:true}}]},plugins:{datalabels:{color:'#fff',font:{weight:'700',size:11},anchor:'center',align:'center',formatter:(value)=>value>0?moneyCompact.format(value):''}}}
    });
}

function growthClass(g){if(g===null||g===undefined)return'is-flat';return g>0?'is-up':(g<0?'is-down':'is-flat');}
function growthText(g){if(g===null||g===undefined)return'—';const sign=g>0?'+':'';return `${sign}${g}%`;}

function renderMonthlySummary(rows){
    if(!rows||!rows.length){$('#monthlySummaryRows').html('<tr><td colspan="5" class="summary-empty">No data for this range.</td></tr>');return;}
    $('#monthlySummaryRows').html(rows.map(r=>`<tr><td>${esc(r.month)}</td><td>${esc(money.format(r.revenue))}</td><td>${esc(money.format(r.cash_collected))}</td><td>${esc(money.format(r.outstanding))}</td><td><span class="summary-growth ${growthClass(r.growth)}">${growthText(r.growth)}</span></td></tr>`).join(''));
}

function loadOverview(){
    return $.get(`{{ route('reports.data') }}?${qs()}`,r=>{
        const vals=metricKeys.map(k=>r.summary[k]);
        $('#metrics .metric-value').each((i,e)=>{$(e).text(i===3?`${vals[i]}%`:(i>=4?money.format(vals[i]):vals[i]))});
        drawSourcePie(r.sources);
        drawRevenueBars(r.trend);
        renderMonthlySummary(r.monthlySummary);
        draw('funnelChart','horizontalBar',r.funnel.map(x=>x.name),[{label:'Deals',data:r.funnel.map(x=>x.total),backgroundColor:r.funnel.map(x=>x.color||'#2563eb')}],{scales:{xAxes:[{ticks:{beginAtZero:true,precision:0}}]},plugins:{datalabels:{display:false}}});
        $('#ownerRows').html(r.owners.map(o=>`<tr><td>${esc(o.name)}</td><td>${o.deals}</td><td>${o.won}</td><td>${esc(money.format(o.won_value))}</td></tr>`).join('')||'<tr><td colspan="4" class="text-muted text-center">No results</td></tr>');
    });
}

function pctBar(pct){return `<div class="pct-bar-wrap"><div class="pct-bar"><span style="width:${Math.min(pct,100)}%"></span></div><span>${pct}%</span></div>`;}

function loadLeads(){
    return $.get(`{{ route('reports.leads') }}?${qs()}`,r=>{
        $('#leadsTotal').text(r.total);
        $('#leadsConverted').text(r.converted);
        $('#leadsAvgDays').text(r.avg_days_to_convert!==null?r.avg_days_to_convert+'d':'—');
        $('#leadsStale').text(r.stale);
        $('#leadsByStatus').html(r.by_status.map(x=>`<tr><td>${esc(humanize(x.label))}</td><td>${x.total}</td></tr>`).join('')||'<tr><td colspan="2" class="simple-empty">No data</td></tr>');
        $('#leadsByPriority').html(r.by_priority.map(x=>`<tr><td>${esc(humanize(x.label))}</td><td>${x.total}</td></tr>`).join('')||'<tr><td colspan="2" class="simple-empty">No data</td></tr>');
        $('#leadsBySource').html(r.by_source.map(x=>`<tr><td>${esc(humanize(x.label))}</td><td>${x.total}</td><td>${x.converted}</td><td>${pctBar(x.rate)}</td></tr>`).join('')||'<tr><td colspan="4" class="simple-empty">No data</td></tr>');
        const top=[...r.by_source].filter(x=>x.total>=1).sort((a,b)=>b.rate-a.rate).slice(0,5);
        $('#leadsTopSources').html(top.map(x=>`<tr><td>${esc(humanize(x.label))}</td><td>${x.rate}%</td></tr>`).join('')||'<tr><td colspan="2" class="simple-empty">No data</td></tr>');
    });
}

function loadFollowUps(){
    return $.get(`{{ route('reports.follow_ups') }}?${qs()}`,r=>{
        $('#fuCompleted').text(r.completed);
        $('#fuScheduled').text(r.scheduled);
        $('#fuOverdue').text(r.overdue);
        $('#fuByAgent').html(r.by_agent.map(a=>`<tr><td>${esc(a.name)}</td><td>${a.completed}</td><td>${a.scheduled}</td><td>${a.overdue}</td></tr>`).join('')||'<tr><td colspan="4" class="simple-empty">No follow-up activity in this range.</td></tr>');
    });
}

function loadAgents(){
    return $.get(`{{ route('reports.agents') }}?${qs()}`,r=>{
        $('#agentRows').html(r.agents.map(a=>`<tr><td>${esc(a.name)}</td><td>${a.leads}</td><td>${a.leads_converted}</td><td>${a.deals_won}</td><td>${esc(money.format(a.won_value))}</td><td>${a.followups_logged}</td><td>${a.tasks_completed}</td><td>${a.tasks_overdue}</td></tr>`).join('')||'<tr><td colspan="8" class="simple-empty">No agents to show.</td></tr>');
    });
}

const STATUS_COLORS={draft:'#94a3b8',scheduled:'#f59e0b',sending:'#2563eb',sent:'#16a34a',failed:'#dc2626'};
function statusChip(s){const c=STATUS_COLORS[s]||'#64748b';return `<span class="status-chip" style="background:${c}1a;color:${c}">${esc(humanize(s))}</span>`;}

function loadComms(){
    return $.get(`{{ route('reports.communications') }}?${qs()}`,r=>{
        $('#commEmailCampaigns').text(r.email.totals.campaigns);
        $('#commEmailSent').text(r.email.totals.sent);
        $('#commWaSent').text(r.whatsapp.totals.sent);
        $('#commWaReceived').text(r.whatsapp.totals.received);
        $('#commEmailRows').html(r.email.campaigns.map(c=>`<tr><td>${esc(c.name)} ${statusChip(c.status)}</td><td>${c.sent}</td><td>${c.failed}</td></tr>`).join('')||'<tr><td colspan="3" class="simple-empty">No email campaigns in this range.</td></tr>');
        $('#commWaRows').html(r.whatsapp.campaigns.map(c=>`<tr><td>${esc(c.name)} ${statusChip(c.status)}</td><td>${c.sent}</td><td>${c.failed}</td></tr>`).join('')||'<tr><td colspan="3" class="simple-empty">No WhatsApp campaigns in this range.</td></tr>');
        $('#commWaAccounts').html(r.whatsapp.accounts.map(a=>`<tr><td>${esc(a.name)}</td><td>${esc(a.channel_type==='meta_cloud'?'Meta Cloud API':'Unofficial Gateway')}</td><td>${a.messages_sent_count}</td><td>${a.messages_received_count}</td></tr>`).join('')||'<tr><td colspan="4" class="simple-empty">No WhatsApp account connected.</td></tr>');
    });
}

function loadAutomation(){
    return $.get(`{{ route('reports.automation') }}?${qs()}`,r=>{
        $('#autoWebhookLeads').text(r.webhook_leads_created);
        $('#autoReminders').text(r.reminders_sent);
        $('#autoScheduled').text(r.scheduled_campaigns_sent);
        $('#autoIgnored').text(r.webhook_duplicates_ignored);
        $('#autoByPlatform').html(r.by_platform.map(p=>`<tr><td>${esc(humanize(p.platform))}</td><td>${p.integrations}</td><td>${p.leads_created}</td></tr>`).join('')||'<tr><td colspan="3" class="simple-empty">No webhook integrations connected.</td></tr>');
        $('#autoByReminderType').html(`<tr><td>Task reminders</td><td>${r.task_reminders_sent}</td></tr><tr><td>Lead follow-up reminders</td><td>${r.lead_reminders_sent}</td></tr>`);
    });
}

const LOADERS={overview:loadOverview,leads:loadLeads,followups:loadFollowUps,agents:loadAgents,comms:loadComms,automation:loadAutomation};
let activeTab='overview';let loadedTabs=new Set();

function qs(){return new URLSearchParams({from:$('#from').val(),to:$('#to').val()}).toString();}

function activateTab(tab){
    activeTab=tab;
    $('.report-tab-btn').removeClass('active').filter(`[data-tab="${tab}"]`).addClass('active');
    $('.report-tab').removeClass('active');
    $(`#tab-${tab}`).addClass('active');
    if(!loadedTabs.has(tab)){
        loadedTabs.add(tab);
        LOADERS[tab]().fail(x=>{loadedTabs.delete(tab);alert(x.responseJSON?.message||'Unable to load report.')});
    }
}

$('#reportTabs').on('click','.report-tab-btn',function(){activateTab($(this).data('tab'));});

function run(){loadedTabs.clear();LOADERS[activeTab]().fail(x=>alert(x.responseJSON?.message||'Unable to load report.'));loadedTabs.add(activeTab);}

const today=new Date();const from=new Date();from.setDate(today.getDate()-29);$('#to').val(today.toISOString().slice(0,10));$('#from').val(from.toISOString().slice(0,10));$('#runReport').on('click',run);
loadOverview();loadedTabs.add('overview');
})();
</script></body></html>
