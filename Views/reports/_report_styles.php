<?php /* Views/reports/_report_styles.php — هماهنگ با تم پیگیر */ ?>
<style>
/* ════════════════════════════════════════
   متغیرهای برند پیگیر
   ════════════════════════════════════════ */
.rpt-wrap {
    --r-ink:#14213D; --r-ink-soft:#4A5578; --r-ember:#FF6B35; --r-ember-deep:#E6531E;
    --r-teal:#16A085; --r-teal-deep:#0E8170; --r-paper:#FAF8F5; --r-paper2:#F2EEE6;
    --r-line:#E5DFD3; --r-card:#FFFFFF; --r-blue:#1a73e8; --r-purple:#9c27b0;
    --r-danger:#EA4335; --r-warning:#f5a623;
    direction: rtl;
}

/* ── Nav ── */
.rpt-nav { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.rpt-nav h2 { font-size:17px; font-weight:800; color:var(--r-ink); letter-spacing:-.01em; }
.rpt-tabs { display:flex; gap:6px; }
.rpt-tab {
    padding:7px 17px; border-radius:20px; font-size:12.5px; font-weight:600;
    border:1.5px solid var(--r-line); background:var(--r-card); color:var(--r-ink-soft);
    text-decoration:none; transition:all .18s;
}
.rpt-tab.active, .rpt-tab:hover { background:var(--r-ember); color:#fff; border-color:var(--r-ember); }
.rpt-btn-sm {
    padding:6px 12px; border-radius:8px; border:1.5px solid var(--r-line);
    background:var(--r-card); color:var(--r-ink-soft); font-size:11.5px; font-weight:600;
    text-decoration:none; cursor:pointer; transition:all .15s;
}
.rpt-btn-sm:hover { color:var(--r-ember-deep); border-color:var(--r-ember); }

/* ── Filter ── */
.rpt-filter {
    display:flex; gap:10px; margin-bottom:18px; flex-wrap:wrap; align-items:flex-end;
    background:var(--r-card); border:1px solid var(--r-line); border-radius:14px; padding:14px 16px;
}
.rpt-filter label { font-size:11.5px; font-weight:600; color:var(--r-ink-soft); display:block; margin-bottom:5px; }
.rpt-filter input, .rpt-filter select {
    padding:8px 12px; border:1.5px solid var(--r-line); border-radius:9px;
    background:var(--r-paper); color:var(--r-ink); font-size:12.5px; font-family:inherit;
    transition:border-color .15s;
}
.rpt-filter input:focus, .rpt-filter select:focus { outline:none; border-color:var(--r-ember); background:#fff; }

/* ── KPI ── */
.kpi-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(105px, 1fr)); gap:10px; margin-bottom:18px; }
.kpi-card {
    background:var(--r-card); border:1px solid var(--r-line); border-radius:14px;
    padding:15px 13px; position:relative; overflow:hidden; transition:box-shadow .2s, transform .2s;
}
.kpi-card:hover { box-shadow:0 6px 20px rgba(20,33,61,.08); transform:translateY(-1px); }
.kpi-icon { width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:15px; margin-bottom:9px; }
.kpi-val { font-size:23px; font-weight:800; line-height:1; margin-bottom:4px; color:var(--r-ink); }
.kpi-label { font-size:11.5px; color:var(--r-ink-soft); }
.kpi-accent { position:absolute; bottom:0; right:0; left:0; height:3px; border-radius:0 0 14px 14px; }

/* ── Cards ── */
.rpt-card { background:var(--r-card); border:1px solid var(--r-line); border-radius:14px; overflow:hidden; margin-bottom:16px; }
.rpt-card-hd { padding:13px 16px; border-bottom:1px solid var(--r-line); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px; }
.rpt-card-hd h3 { font-size:13.5px; font-weight:700; color:var(--r-ink); margin:0; }
.rpt-card-body { padding:16px; }

/* ── Chart Row ── */
.chart-row { display:grid; grid-template-columns:2fr 1fr; gap:14px; margin-bottom:18px; }
@media(max-width:640px){ .chart-row { grid-template-columns:1fr; } }

/* ── Bar chart ── */
.bar-chart-wrap { display:flex; gap:4px; align-items:flex-end; height:170px; position:relative; overflow-x:auto; overflow-y:visible; padding-bottom:22px; padding-top:22px; }
.bar-col { display:flex; flex-direction:column; align-items:center; flex:1; min-width:30px; height:100%; justify-content:flex-end; }
.bar-inner { width:100%; border-radius:5px 5px 2px 2px; cursor:pointer; transition:opacity .15s; overflow:visible; }
.bar-inner:hover { opacity:.78 !important; }
.bar-lbl { font-size:9.5px; color:var(--r-ink-soft); margin-top:5px; white-space:nowrap; text-overflow:ellipsis; overflow:hidden; max-width:100%; text-align:center; }
.bar-grid-line { position:absolute; left:0; right:0; border-top:1px dashed rgba(20,33,61,.08); pointer-events:none; }

/* ── Donut ── */
.donut-wrap { display:flex; align-items:center; gap:14px; }
.donut-legend { flex:1; }
.donut-leg-row { display:flex; align-items:center; justify-content:space-between; font-size:11.5px; padding:5px 0; border-bottom:1px solid var(--r-paper2); }
.donut-leg-row:last-child { border:none; }
.donut-dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; margin-left:7px; }

/* ── Timeline ── */
.tl-legend { display:flex; gap:13px; }
.tl-leg-item { display:flex; align-items:center; gap:5px; font-size:10.5px; color:var(--r-ink-soft); }
.tl-leg-dot { width:10px; height:10px; border-radius:3px; flex-shrink:0; }
.timeline-row { display:flex; align-items:center; gap:9px; margin-bottom:6px; }
.tl-date { font-size:10.5px; color:var(--r-ink-soft); width:42px; flex-shrink:0; text-align:left; direction:ltr; }
.tl-bars { display:flex; gap:2px; flex:1; height:17px; align-items:center; }
.tl-seg { height:15px; border-radius:4px; min-width:4px; transition:opacity .15s; }
.tl-seg:hover { opacity:.7; }
.tl-total { font-size:10.5px; font-weight:700; min-width:22px; text-align:right; color:var(--r-ink); }

/* ── Table ── */
.rpt-table { width:100%; border-collapse:collapse; font-size:12.5px; }
.rpt-table th { padding:10px 11px; text-align:right; background:var(--r-paper2); color:var(--r-ink-soft); font-weight:600; font-size:11px; border-bottom:1px solid var(--r-line); }
.rpt-table td { padding:10px 11px; border-bottom:1px solid var(--r-line); vertical-align:middle; color:var(--r-ink); }
.rpt-table tr:last-child td { border:none; }
.rpt-table tr:hover td { background:var(--r-paper2); }

/* ── Progress bar ── */
.progress-bar-wrap { background:var(--r-paper2); border-radius:5px; height:7px; overflow:hidden; }
.progress-bar-fill { height:100%; border-radius:5px; transition:width .6s ease; }

/* ── Pills ── */
.act-pill { display:inline-flex; align-items:center; padding:3px 9px; border-radius:11px; font-size:10.5px; font-weight:700; gap:3px; }
</style>