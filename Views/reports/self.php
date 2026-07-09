<!-- Views/reports/self.php -->
<?php
$task_rate  = ($stats['tasks'] > 0) ? round(($stats['completed'] / $stats['tasks']) * 100) : 0;
$total_act  = $stats['total_activities'] ?? 0;

$js_calls    = (int)($stats['calls']    ?? 0);
$js_meetings = (int)($stats['meetings'] ?? 0);
$js_emails   = (int)($stats['emails']   ?? 0);
$js_notes    = (int)($stats['notes']    ?? 0);

$daily_js = [];
foreach ($daily_stats as $d) {
    $daily_js[] = [
        'dt'    => function_exists('jdate') ? jdate($d['dt'], 'm/d') : date('m/d', strtotime($d['dt'])),
        'calls' => (int)$d['calls'],
        'meet'  => (int)$d['meetings'],
        'email' => (int)$d['emails'],
        'note'  => (int)$d['notes'],
        'total' => (int)$d['calls'] + (int)$d['meetings'] + (int)$d['emails'] + (int)$d['notes'],
    ];
}
$daily_json = json_encode(array_reverse($daily_js));
?>

<?php require_once __DIR__ . '/_report_styles.php'; ?>

<div class="rpt-wrap">

<div class="rpt-nav">
    <h2>📊 گزارش شخصی — <?= crm_sanitize($user['full_name']) ?></h2>
    <?php if ($is_manager): ?>
    <div class="rpt-tabs">
        <a href="index.php?page=reports&action=self" class="rpt-tab active">خودم</a>
        <a href="index.php?page=reports&action=users" class="rpt-tab">کاربران</a>
        <a href="index.php?page=reports&action=managers" class="rpt-tab">مدیران</a>
    </div>
    <?php endif; ?>
</div>

<form method="GET">
    <input type="hidden" name="page" value="reports">
    <input type="hidden" name="action" value="self">
    <div class="rpt-filter">
        <div><label>از تاریخ</label><input type="date" name="date_from" value="<?= $date_from ?>" onchange="this.form.submit()"></div>
        <div><label>تا تاریخ</label><input type="date" name="date_to" value="<?= $date_to ?>" onchange="this.form.submit()"></div>
        <div style="margin-right:auto; font-size:11.5px; color:var(--r-ink-soft); align-self:center;">
            بازه: <?= function_exists('jdate') ? jdate($date_from) : $date_from ?> تا <?= function_exists('jdate') ? jdate($date_to) : $date_to ?>
        </div>
    </div>
</form>

<div class="kpi-grid">
    <?php
    $kpis = [
        ['label'=>'تماس',          'val'=>$stats['calls']??0,    'color'=>'#1a73e8','bg'=>'#E8F0FE','icon'=>'📞'],
        ['label'=>'جلسه',          'val'=>$stats['meetings']??0, 'color'=>'#E6951E','bg'=>'#FFF3DD','icon'=>'🤝'],
        ['label'=>'ایمیل',         'val'=>$stats['emails']??0,   'color'=>'#16A085','bg'=>'#E7F7F3','icon'=>'📧'],
        ['label'=>'یادداشت',       'val'=>$stats['notes']??0,    'color'=>'#9c27b0','bg'=>'#F3E8FD','icon'=>'📝'],
        ['label'=>'کل فعالیت',    'val'=>$total_act,             'color'=>'#0E8170','bg'=>'#E7F7F3','icon'=>'📋'],
        ['label'=>'مشتری جدید',   'val'=>$stats['customers']??0,'color'=>'#E6531E','bg'=>'#FFF1EA','icon'=>'🏢'],
        ['label'=>'مخاطب جدید',   'val'=>$stats['contacts']??0, 'color'=>'#1a73e8','bg'=>'#E8F0FE','icon'=>'👤'],
        ['label'=>'فرصت فروش تکمیل‌شده','val'=>$stats['completed']??0,'color'=>'#16A085','bg'=>'#E7F7F3','icon'=>'✅'],
    ];
    if (isset($stats['sold'])) $kpis[] = ['label'=>'منجر به فروش','val'=>$stats['sold']??0,'color'=>'#FF6B35','bg'=>'#FFF1EA','icon'=>'💰'];
    if (isset($stats['cancelled'])) $kpis[] = ['label'=>'کنسل‌شده','val'=>$stats['cancelled']??0,'color'=>'#EA4335','bg'=>'#FCE8E6','icon'=>'❌'];
    foreach ($kpis as $k): ?>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:<?= $k['bg'] ?>"><?= $k['icon'] ?></div>
        <div class="kpi-val" style="color:<?= $k['color'] ?>"><?= $k['val'] ?></div>
        <div class="kpi-label"><?= $k['label'] ?></div>
        <div class="kpi-accent" style="background:<?= $k['color'] ?>;opacity:.3"></div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (($stats['tasks'] ?? 0) > 0): ?>
<div class="rpt-card">
    <div class="rpt-card-hd">
        <h3>📋 نرخ تکمیل فرصت‌های فروش</h3>
        <span style="font-size:13px;font-weight:800;color:<?= $task_rate >= 70 ? '#16A085' : ($task_rate >= 40 ? '#E6951E' : '#EA4335') ?>"><?= $task_rate ?>%</span>
    </div>
    <div class="rpt-card-body">
        <div style="display:flex;justify-content:space-between;font-size:11.5px;color:var(--r-ink-soft);margin-bottom:7px">
            <span>تکمیل‌شده: <strong style="color:var(--r-ink)"><?= $stats['completed']??0 ?></strong></span>
            <span>در انتظار: <strong style="color:var(--r-ink)"><?= ($stats['tasks']??0) - ($stats['completed']??0) ?></strong></span>
            <span>کل: <strong style="color:var(--r-ink)"><?= $stats['tasks']??0 ?></strong></span>
        </div>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill" style="width:<?= $task_rate ?>%;background:<?= $task_rate >= 70 ? '#16A085' : ($task_rate >= 40 ? '#E6951E' : '#EA4335') ?>"></div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($daily_stats)): ?>
<div class="chart-row">
    <div class="rpt-card">
        <div class="rpt-card-hd">
            <h3>📈 روند روزانه فعالیت‌ها</h3>
            <span style="font-size:10.5px;color:var(--r-ink-soft)"><?= count($daily_stats) ?> روز</span>
        </div>
        <div class="rpt-card-body">
            <div class="bar-chart-wrap" id="dailyBarChart"></div>
        </div>
    </div>

    <div class="rpt-card">
        <div class="rpt-card-hd"><h3>🥧 ترکیب فعالیت</h3></div>
        <div class="rpt-card-body">
            <div class="donut-wrap">
                <canvas id="donutChart" width="100" height="100"></canvas>
                <div class="donut-legend" id="donutLegend"></div>
            </div>
        </div>
    </div>
</div>

<div class="rpt-card">
    <div class="rpt-card-hd">
        <h3>📅 تایم‌لاین فعالیت‌ها</h3>
        <div class="tl-legend">
            <span class="tl-leg-item"><span class="tl-leg-dot" style="background:#1a73e8"></span>تماس</span>
            <span class="tl-leg-item"><span class="tl-leg-dot" style="background:#E6951E"></span>جلسه</span>
            <span class="tl-leg-item"><span class="tl-leg-dot" style="background:#16A085"></span>ایمیل</span>
            <span class="tl-leg-item"><span class="tl-leg-dot" style="background:#9e9e9e"></span>یادداشت</span>
        </div>
    </div>
    <div class="rpt-card-body" id="activityTimeline"></div>
</div>

<div class="rpt-card">
    <div class="rpt-card-hd"><h3>📋 جزئیات روزانه</h3></div>
    <div style="overflow-x:auto">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>تاریخ</th>
                    <th style="text-align:center">📞 تماس</th>
                    <th style="text-align:center">🤝 جلسه</th>
                    <th style="text-align:center">📧 ایمیل</th>
                    <th style="text-align:center">📝 یادداشت</th>
                    <th style="text-align:center">کل</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daily_stats as $d):
                    $tot = $d['calls'] + $d['meetings'] + $d['emails'] + $d['notes'];
                    $rowDate = function_exists('jdate') ? jdate($d['dt']) : date('Y/m/d', strtotime($d['dt']));
                ?>
                <tr>
                    <td><?= $rowDate ?></td>
                    <td style="text-align:center"><?= $d['calls'] ?></td>
                    <td style="text-align:center"><?= $d['meetings'] ?></td>
                    <td style="text-align:center"><?= $d['emails'] ?></td>
                    <td style="text-align:center"><?= $d['notes'] ?></td>
                    <td style="text-align:center">
                        <span class="act-pill" style="background:<?= $tot >= 10 ? '#E7F7F3' : ($tot >= 5 ? '#E8F0FE' : '#F2EEE6') ?>;color:<?= $tot >= 10 ? '#0E8170' : ($tot >= 5 ? '#1a73e8' : '#9e9e9e') ?>">
                            <?= $tot ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

</div><!-- /rpt-wrap -->

<script>
(function(){
    var daily = <?= $daily_json ?>;
    if (!daily.length) return;

    var chartEl = document.getElementById('dailyBarChart');
    if (!chartEl) return;

    var totals = daily.map(function(d){ return d.total; });
    var maxVal = Math.max.apply(null, totals.concat([1]));
    var chartH = 130;

    chartEl.style.paddingTop = '22px';
    chartEl.style.overflowY = 'visible';

    for (var g = 1; g <= 4; g++) {
        var gl = document.createElement('div');
        gl.className = 'bar-grid-line';
        gl.style.bottom = (22 + (chartH / 4) * g) + 'px';
        chartEl.appendChild(gl);
    }

    daily.forEach(function(d) {
        var col = document.createElement('div');
        col.className = 'bar-col';
        col.title = d.dt + ': ' + d.total + ' فعالیت';

        var h = maxVal > 0 ? Math.max(Math.round((d.total / maxVal) * chartH), d.total > 0 ? 3 : 0) : 0;

        var vl = document.createElement('div');
        vl.style.cssText = 'font-size:9.5px;font-weight:700;white-space:nowrap;color:var(--r-ink-soft);text-align:center;height:18px;line-height:18px;';
        vl.textContent = d.total > 0 ? d.total : '';

        var bar = document.createElement('div');
        bar.className = 'bar-inner';
        bar.style.height = h + 'px';
        bar.style.background = d.total === 0 ? 'rgba(20,33,61,.08)' : '#FF6B35';
        bar.style.opacity = d.total === 0 ? '0.5' : '0.88';

        var lbl = document.createElement('div');
        lbl.className = 'bar-lbl';
        lbl.textContent = (daily.indexOf(d) % 4 === 0) ? d.dt : '';

        col.appendChild(vl);
        col.appendChild(bar);
        col.appendChild(lbl);
        chartEl.appendChild(col);
    });

    var donutData = [
        { l:'تماس',     v:<?= $js_calls ?>,    c:'#1a73e8' },
        { l:'جلسه',     v:<?= $js_meetings ?>, c:'#E6951E' },
        { l:'ایمیل',    v:<?= $js_emails ?>,   c:'#16A085' },
        { l:'یادداشت',  v:<?= $js_notes ?>,    c:'#9c27b0' }
    ];
    var total = donutData.reduce(function(a,d){ return a+d.v; }, 0);
    var cvs = document.getElementById('donutChart');
    if (cvs && total > 0) {
        var ctx = cvs.getContext('2d');
        var angle = -Math.PI / 2;
        var cx=50, cy=50, r=40, ri=24;
        donutData.forEach(function(d) {
            if (d.v === 0) return;
            var slice = (d.v / total) * Math.PI * 2;
            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.arc(cx, cy, r, angle, angle + slice);
            ctx.closePath();
            ctx.fillStyle = d.c;
            ctx.fill();
            angle += slice;
        });
        ctx.beginPath();
        ctx.arc(cx, cy, ri, 0, Math.PI*2);
        ctx.fillStyle = '#fff';
        ctx.fill();
        ctx.fillStyle = '#14213D';
        ctx.font = 'bold 13px Vazirmatn, Segoe UI';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(total, cx, cy);
    }
    var legEl = document.getElementById('donutLegend');
    if (legEl) {
        donutData.forEach(function(d) {
            var pct = total > 0 ? Math.round((d.v/total)*100) : 0;
            legEl.innerHTML += '<div class="donut-leg-row">' +
                '<span style="display:flex;align-items:center"><span class="donut-dot" style="background:'+d.c+'"></span>'+d.l+'</span>' +
                '<span style="font-weight:700">'+d.v+' <span style="color:var(--r-ink-soft);font-weight:400">('+pct+'%)</span></span></div>';
        });
    }

    var tlEl = document.getElementById('activityTimeline');
    if (!tlEl) return;
    var maxTot = Math.max.apply(null, daily.map(function(d){ return d.total; }).concat([1]));
    var maxW = 200;

    var recent = daily.slice(-14);
    recent.forEach(function(d) {
        var segs = [
            {v:d.calls, c:'#1a73e8'},
            {v:d.meet,  c:'#E6951E'},
            {v:d.email, c:'#16A085'},
            {v:d.note,  c:'#9e9e9e'}
        ];
        var segHtml = segs.map(function(s){
            if (s.v === 0) return '';
            var w = Math.max(Math.round((s.v/maxTot)*maxW), 6);
            return '<div class="tl-seg" style="width:'+w+'px;background:'+s.c+'" title="'+s.v+'"></div>';
        }).join('');
        tlEl.innerHTML += '<div class="timeline-row">' +
            '<span class="tl-date">'+d.dt+'</span>' +
            '<div class="tl-bars">'+segHtml+'</div>' +
            '<span class="tl-total">'+d.total+'</span></div>';
    });
})();
</script>