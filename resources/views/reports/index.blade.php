<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Reports & Analytics</title>
<link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}"><link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}"><link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}"><link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    /* Same modernization pattern as the rest of this pass. */
    .crm-page-header{background:#fff;padding:20px 22px;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06);margin-bottom:18px;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px}
    .crm-page-header h3{margin:0 0 6px;font-weight:700;font-size:18px;color:#111827}.crm-page-header p{margin:0;color:#6b7280;font-size:14px}
    .report-card{border:0;border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.metric-value{font-size:28px;font-weight:700;color:#1e3a8a}.metric-label{font-size:12.5px;color:#64748b;text-transform:uppercase;letter-spacing:.04em;font-weight:600}.chart-box{height:310px}.filter-bar{display:flex;gap:10px;align-items:end;flex-wrap:wrap}.filter-bar label{font-size:12.5px;color:#64748b}.filter-bar .btn{border-radius:10px;padding:8px 16px;font-weight:600}
    .report-card h5{font-weight:700;font-size:15.5px;margin-bottom:16px;color:#111827}

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
<div class="crm-page-header"><div><h3>Reports & Analytics</h3><p>Funnel, revenue, acquisition, and team performance.</p></div><div class="filter-bar"><div><label>From</label><input id="from" type="date" class="form-control"></div><div><label>To</label><input id="to" type="date" class="form-control"></div><button class="btn btn-primary" id="runReport">Run report</button></div></div>
<div class="row" id="metrics">@foreach(['Leads created','Deals created','Won deals','Win rate','Pipeline value','Booked revenue','Cash collected'] as $label)<div class="col-xl col-md-4 col-sm-6 mb-3"><div class="card report-card h-100"><div class="card-body"><div class="metric-label">{{ $label }}</div><div class="metric-value">—</div></div></div></div>@endforeach</div>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card report-card">
            <div class="card-body">
                <h5>Lead Sources</h5>
                <div class="chart-box"><canvas id="sourceChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-7 mb-4">
        <div class="card report-card">
            <div class="card-body">
                <h5>Revenue Overview</h5>
                <div class="chart-box"><canvas id="trendChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card report-card">
            <div class="card-body">
                <h5>Monthly Summary</h5>
                <div class="table-responsive">
                    <table class="summary-table">
                        <thead>
                            <tr><th>Month</th><th>Revenue</th><th>Cash Collected</th><th>Outstanding</th><th>Growth</th></tr>
                        </thead>
                        <tbody id="monthlySummaryRows"><tr><td colspan="5" class="summary-empty">Loading…</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row"><div class="col-lg-6 mb-4"><div class="card report-card"><div class="card-body"><h5>Deal funnel</h5><div class="chart-box"><canvas id="funnelChart"></canvas></div></div></div></div><div class="col-lg-6 mb-4"><div class="card report-card"><div class="card-body"><h5>Owner performance</h5><div class="table-responsive"><table class="table"><thead><tr><th>Owner</th><th>Deals</th><th>Won</th><th>Won value</th></tr></thead><tbody id="ownerRows"></tbody></table></div></div></div></div></div>
</div>@include('include.footer')</div></div></div><script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script><script src="{{ asset('vendors/chart.js/Chart.min.js') }}"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/0.7.0/chartjs-plugin-datalabels.min.js"></script><script>
(() => {let charts={};const money=new Intl.NumberFormat(undefined,{style:'currency',currency:'INR',maximumFractionDigits:0});const moneyCompact=new Intl.NumberFormat(undefined,{style:'currency',currency:'INR',notation:'compact',maximumFractionDigits:1});const esc=v=>$('<div>').text(v??'').html();const metricKeys=['leads_created','deals_created','won_deals','win_rate','pipeline_value','booked_revenue','cash_collected'];
const SOURCE_COLORS=['#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#64748b','#ec4899'];
function applyChartTheme(){const isDark=document.documentElement.getAttribute('data-theme')==='dark';Chart.defaults.global.defaultFontColor=isDark?'#9aa1b5':'#6b7280';Chart.defaults.scale.gridLines.color=isDark?'#2a2e40':'rgba(0,0,0,.1)';Chart.defaults.scale.gridLines.zeroLineColor=isDark?'#2a2e40':'rgba(0,0,0,.25)';}
applyChartTheme();document.addEventListener('crm-theme-changed',function(){applyChartTheme();run();});
function draw(id,type,labels,datasets,options={}){if(charts[id])charts[id].destroy();charts[id]=new Chart(document.getElementById(id),{type,data:{labels,datasets},options:{responsive:true,maintainAspectRatio:false,legend:{position:'bottom'},...options}})}

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
            legend:{
                position:'bottom',
                labels:{
                    usePointStyle:true,
                    generateLabels:function(chart){
                        const d=chart.data;
                        return d.labels.map((label,i)=>{
                            const pct=total?Math.round((d.datasets[0].data[i]/total)*100):0;
                            return{text:`${label} — ${pct}%`,fillStyle:d.datasets[0].backgroundColor[i],strokeStyle:d.datasets[0].backgroundColor[i],lineWidth:0,index:i};
                        });
                    }
                }
            },
            plugins:{
                datalabels:{
                    color:'#fff',font:{weight:'700',size:12},
                    formatter:(value)=>{const pct=total?Math.round((value/total)*100):0;return pct>=6?`${pct}%`:'';}
                }
            }
        }
    });
}

function drawRevenueBars(trend){
    if(charts.trendChart)charts.trendChart.destroy();
    charts.trendChart=new Chart(document.getElementById('trendChart'),{
        type:'bar',
        data:{
            labels:trend.labels,
            datasets:[
                {label:'Booked revenue',data:trend.revenue,backgroundColor:'#2563eb',borderRadius:6,maxBarThickness:38},
                {label:'Cash collected',data:trend.cash,backgroundColor:'#10b981',borderRadius:6,maxBarThickness:38},
            ]
        },
        options:{
            responsive:true,maintainAspectRatio:false,
            legend:{position:'bottom'},
            scales:{yAxes:[{ticks:{beginAtZero:true}}]},
            plugins:{
                datalabels:{
                    color:'#fff',font:{weight:'700',size:11},anchor:'center',align:'center',
                    formatter:(value)=>value>0?moneyCompact.format(value):''
                }
            }
        }
    });
}

function growthClass(g){if(g===null||g===undefined)return'is-flat';return g>0?'is-up':(g<0?'is-down':'is-flat');}
function growthText(g){if(g===null||g===undefined)return'—';const sign=g>0?'+':'';return `${sign}${g}%`;}

function renderMonthlySummary(rows){
    if(!rows||!rows.length){$('#monthlySummaryRows').html('<tr><td colspan="5" class="summary-empty">No data for this range.</td></tr>');return;}
    $('#monthlySummaryRows').html(rows.map(r=>`
        <tr>
            <td>${esc(r.month)}</td>
            <td>${esc(money.format(r.revenue))}</td>
            <td>${esc(money.format(r.cash_collected))}</td>
            <td>${esc(money.format(r.outstanding))}</td>
            <td><span class="summary-growth ${growthClass(r.growth)}">${growthText(r.growth)}</span></td>
        </tr>`).join(''));
}

function run(){const p=new URLSearchParams({from:$('#from').val(),to:$('#to').val()});$.get(`{{ route('reports.data') }}?${p}`,r=>{const vals=metricKeys.map(k=>r.summary[k]);$('#metrics .metric-value').each((i,e)=>{$(e).text(i===3?`${vals[i]}%`:(i>=4?money.format(vals[i]):vals[i]))});
    drawSourcePie(r.sources);
    drawRevenueBars(r.trend);
    renderMonthlySummary(r.monthlySummary);
    draw('funnelChart','horizontalBar',r.funnel.map(x=>x.name),[{label:'Deals',data:r.funnel.map(x=>x.total),backgroundColor:r.funnel.map(x=>x.color||'#2563eb')}],{scales:{xAxes:[{ticks:{beginAtZero:true,precision:0}}]},plugins:{datalabels:{display:false}}});
    $('#ownerRows').html(r.owners.map(o=>`<tr><td>${esc(o.name)}</td><td>${o.deals}</td><td>${o.won}</td><td>${esc(money.format(o.won_value))}</td></tr>`).join('')||'<tr><td colspan="4" class="text-muted text-center">No results</td></tr>');}).fail(x=>alert(x.responseJSON?.message||'Unable to load report.'))}
const today=new Date();const from=new Date();from.setDate(today.getDate()-29);$('#to').val(today.toISOString().slice(0,10));$('#from').val(from.toISOString().slice(0,10));$('#runReport').on('click',run);run();})();
</script></body></html>
