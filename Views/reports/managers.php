<!-- Views/reports/managers.php -->
<?php
$js_managers = [];
foreach ($managers_data as $md) {
    $js_managers[] = [
        'id'    => (int)$md['id'],
        'name'  => mb_substr($md['full_name'], 0, 12),
        'co'    => mb_substr($md['company_name'] ?? '', 0, 12),
        'team'  => (int)($md['team_count']??0),
        'calls' => (int)($md['calls']??0),
        'meet'  => (int)($md['meetings']??0),
        'email' => (int)($md['emails']??0),
        'note'  => (int)($md['notes']??0),
        'total' => (int)($md['total_activities']??0),
        'cust'  => (int)($md['customers']??0),
        'cont'  => (int)($md['contacts']??0),
        'tasks' => (int)($md['tasks']??0),
        'done'  => (int)($md['completed']??0),
        'sold'  => (int)($md['sold']??0),
        'cancel'=> (int)($md['cancelled']??0),
    ];
}
$mgr_json  = json_encode($js_managers);
$pie_cust_json = json_encode($pie_customers);
$pie_cont_json = json_encode($pie_contacts);

$chart_labels_map = [
    'activities' => 'کل فعالیت‌ها', 'total_activities' => 'کل فعالیت‌ها',
    'calls' => 'تماس‌ها', 'meetings' => 'جلسات',
    'customers' => 'مشتریان جدید', 'contacts' => 'مخاطبین',
    'tasks' => 'تسک‌ها', 'completed' => 'تسک تکمیل‌شده',
];
$js_field_map = [
    'activities' => 'total', 'total_activities' => 'total', 'calls' => 'calls',
    'meetings' => 'meet', 'customers' => 'cust', 'contacts' => 'cont',
    'tasks' => 'tasks', 'completed' => 'done',
];
$active_field = $js_field_map[$chart_type] ?? 'total';

$grand_total   = array_sum(array_column($managers_data, 'total_activities'));
$grand_cust    = array_sum(array_column($managers_data, 'customers'));
$grand_tasks   = array_sum(array_column($managers_data, 'tasks'));
$grand_done    = array_sum(array_column($managers_data, 'completed'));
$grand_sold    = array_sum(array_column($managers_data, 'sold'));
$grand_teams   = array_sum(array_column($managers_data, 'team_count'));
$done_rate     = $grand_tasks > 0 ? round($grand_done / $grand_tasks * 100) : 0;
?>

<?php require_once __DIR__ . '/_report_styles.php'; ?>

<div class="rpt-wrap">

<div class="rpt-nav">
    <h2>📊 گزارش مدیران — مقایسه تیم‌ها</h2>
    <div class="rpt-tabs">
        <a href="index.php?page=reports&action=self" class="rpt-tab">خودم</a>
        <a href="index.php?page=reports&action=users" class="rpt-tab">کاربران</a>
        <a href="index.php?page=reports&action=managers" class="rpt-tab active">مدیران</a>
    </div>
</div>

<form method="GET">
    <input type="hidden" name="page" value="reports">
    <input type="hidden" name="action" value="managers">
    <div class="rpt-filter">
        <div><label>از تاریخ</label><input type="date" name="date_from" value="<?= $date_from ?>" onchange="this.form.submit()"></div>
        <div><label>تا تاریخ</label><input type="date" name="date_to" value="<?= $date_to ?>" onchange="this.form.submit()"></div>
        <div style="flex:2; min-width:180px;">
            <label>نمودار بر اساس</label>
            <select name="chart_type" onchange="this.form.submit()" style="width:100%">
                <?php
                $opts = [
                    'activities'=>'کل فعالیت‌ها','calls'=>'تماس‌ها','meetings'=>'جلسات',
                    'customers'=>'مشتریان جدید','contacts'=>'مخاطبین','tasks'=>'تسک‌ها','completed'=>'تسک‌های تکمیل',
                ];
                foreach ($opts as $v => $l): ?>
                <option value="<?= $v ?>" <?= $chart_type === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</form>

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#E8F0FE">👥</div>
        <div class="kpi-val" style="color:#1a73e8"><?= count($managers_data) ?></div>
        <div class="kpi-label">مدیران</div>
        <div class="kpi-accent" style="background:#1a73e8;opacity:.3"></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#FFF3DD">🏢</div>
        <div class="kpi-val" style="color:#E6951E"><?= $grand_teams ?></div>
        <div class="kpi-label">اعضای تیم</div>
        <div class="kpi-accent" style="background:#E6951E;opacity:.3"></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#E7F7F3">📋</div>
        <div class="kpi-val" style="color:#0E8170"><?= $grand_total ?></div>
        <div class="kpi-label">کل فعالیت</div>
        <div class="kpi-accent" style="background:#0E8170;opacity:.3"></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#FFF1EA">💰</div>
        <div class="kpi-val" style="color:#FF6B35"><?= $grand_sold ?></div>
        <div class="kpi-label">فروش</div>
        <div class="kpi-accent" style="background:#FF6B35;opacity:.3"></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#E7F7F3">🎯</div>
        <div class="kpi-val" style="color:#16A085"><?= $done_rate ?>%</div>
        <div class="kpi-label">نرخ تکمیل</div>
        <div class="kpi-accent" style="background:#16A085;opacity:.3"></div>
    </div>
</div>

<?php if (!empty($managers_data)): ?>

<div class="chart-row">
    <div class="rpt-card">
        <div class="rpt-card-hd">
            <h3>📊 مقایسه تیم‌ها — <?= $chart_labels_map[$chart_type] ?? 'کل فعالیت‌ها' ?></h3>
        </div>
        <div class="rpt-card-body">
            <div id="mgrBarChart" class="bar-chart-wrap" style="height:200px"></div>
        </div>
    </div>
    <div class="rpt-card">
        <div class="rpt-card-hd"><h3>🥧 سهم مشتری تیم‌ها</h3></div>
        <div class="rpt-card-body">
            <div class="donut-wrap">
                <canvas id="custDonut" width="100" height="100"></canvas>
                <div class="donut-legend" id="custDonutLegend"></div>
            </div>
        </div>
    </div>
</div>

<div class="rpt-card">
    <div class="rpt-card-hd">
        <h3>📊 ترکیب فعالیت‌ها — تفکیک تیم‌ها</h3>
        <div class="tl-legend">
            <span class="tl-leg-item"><span class="tl-leg-dot" style="background:#1a73e8"></span>تماس</span>
            <span class="tl-leg-item"><span class="tl-leg-dot" style="background:#E6951E"></span>جلسه</span>
            <span class="tl-leg-item"><span class="tl-leg-dot" style="background:#16A085"></span>ایمیل</span>
            <span class="tl-leg-item"><span class="tl-leg-dot" style="background:#9e9e9e"></span>یادداشت</span>
        </div>
    </div>
    <div class="rpt-card-body" id="mgrStackedChart"></div>
</div>

<div class="rpt-card">
    <div class="rpt-card-hd">
        <h3>🏆 جدول مقایسه تیم‌ها</h3>
        <span style="font-size:10.5px;color:var(--r-ink-soft)">مرتب‌شده بر اساس <?= $chart_labels_map[$chart_type] ?? 'کل فعالیت' ?></span>
    </div>
    <div style="overflow-x:auto">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th style="width:32px">#</th>
                    <th>مدیر / شرکت</th>
                    <th style="text-align:center">👥 تیم</th>
                    <th style="text-align:center">📞 تماس</th>
                    <th style="text-align:center">🤝 جلسه</th>
                    <th style="text-align:center">📧 ایمیل</th>
                    <th style="text-align:center">📋 کل</th>
                    <th style="text-align:center">🏢 مشتری</th>
                    <th style="text-align:center">✅ تکمیل</th>
                    <th style="min-width:80px">نمودار</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sorted = $managers_data;
                $php_field_map = [
                    'activities' => 'total_activities', 'total_activities' => 'total_activities',
                    'calls' => 'calls', 'meetings' => 'meetings', 'customers' => 'customers',
                    'contacts' => 'contacts', 'tasks' => 'tasks', 'completed' => 'completed',
                ];
                $sort_field = $php_field_map[$chart_type] ?? 'total_activities';
                usort($sorted, fn($a,$b) => ($b[$sort_field]??0) - ($a[$sort_field]??0));
                $max_val = max(1, max(array_column($sorted, 'total_activities')));
                $medals = ['🥇','🥈','🥉'];
                foreach ($sorted as $i => $md):
                    $bar_w = round(($md['total_activities'] / $max_val) * 100);
                    $task_r = ($md['tasks']>0) ? round($md['completed']/$md['tasks']*100) : 0;
                ?>
                <tr>
                    <td style="text-align:center;font-size:13px"><?= $medals[$i] ?? ($i+1) ?></td>
                    <td>
                        <div style="font-weight:700"><?= crm_sanitize($md['full_name']) ?></div>
                        <?php if (!empty($md['company_name'])): ?>
                        <div style="font-size:10.5px;color:var(--r-ink-soft)">🏢 <?= crm_sanitize($md['company_name']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <span class="act-pill" style="background:#E8F0FE;color:#1a73e8"><?= $md['team_count'] ?> نفر</span>
                    </td>
                    <td style="text-align:center"><?= $md['calls']??0 ?></td>
                    <td style="text-align:center"><?= $md['meetings']??0 ?></td>
                    <td style="text-align:center"><?= $md['emails']??0 ?></td>
                    <td style="text-align:center"><strong style="color:#FF6B35"><?= $md['total_activities']??0 ?></strong></td>
                    <td style="text-align:center"><?= $md['customers']??0 ?></td>
                    <td style="text-align:center">
                        <span style="color:#16A085;font-weight:700"><?= $md['completed']??0 ?></span>
                        <span style="font-size:10.5px;color:var(--r-ink-soft)"> (<?= $task_r ?>%)</span>
                    </td>
                    <td>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill" style="width:<?= $bar_w ?>%;background:#FF6B35"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:var(--r-paper2);font-weight:800">
                    <td colspan="2" style="padding:10px 11px">جمع کل</td>
                    <td style="text-align:center"><?= $grand_teams ?></td>
                    <td style="text-align:center"><?= array_sum(array_column($managers_data,'calls')) ?></td>
                    <td style="text-align:center"><?= array_sum(array_column($managers_data,'meetings')) ?></td>
                    <td style="text-align:center"><?= array_sum(array_column($managers_data,'emails')) ?></td>
                    <td style="text-align:center;color:#FF6B35"><?= $grand_total ?></td>
                    <td style="text-align:center"><?= $grand_cust ?></td>
                    <td style="text-align:center;color:#16A085"><?= $grand_done ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php if ($total_contacts_all > 0): ?>
<div class="chart-row">
    <div class="rpt-card">
        <div class="rpt-card-hd"><h3>🥧 سهم مخاطبین</h3></div>
        <div class="rpt-card-body">
            <div class="donut-wrap">
                <canvas id="contDonut" width="100" height="100"></canvas>
                <div class="donut-legend" id="contDonutLegend"></div>
            </div>
        </div>
    </div>
    <div class="rpt-card" style="display:flex;align-items:center;justify-content:center">
        <div style="text-align:center;padding:20px">
            <div style="font-size:12px;color:var(--r-ink-soft);margin-bottom:4px">میانگین فعالیت هر تیم</div>
            <div style="font-size:27px;font-weight:800;color:#FF6B35">
                <?= count($managers_data) > 0 ? round($grand_total / count($managers_data)) : 0 ?>
            </div>
            <div style="font-size:11px;color:var(--r-ink-soft);margin-top:12px">میانگین تسک تکمیل‌شده</div>
            <div style="font-size:27px;font-weight:800;color:#16A085"><?= $done_rate ?>%</div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<div class="rpt-card" style="text-align:center;padding:48px;color:var(--r-ink-soft)">
    <div style="font-size:32px;margin-bottom:8px">📭</div>
    <div>داده‌ای برای نمایش وجود ندارد.</div>
</div>
<?php endif; ?>

</div><!-- /rpt-wrap -->

<script>
(function(){
    var managers = <?= $mgr_json ?>;
    var pieCust  = <?= $pie_cust_json ?>;
    var pieCont  = <?= $pie_cont_json ?>;
    var activeField = '<?= $active_field ?>';
    var PIE_COLORS = ['#FF6B35','#1a73e8','#16A085','#9c27b0','#E6951E','#00bcd4','#EA4335','#607d8b'];

    var chartEl = document.getElementById('mgrBarChart');
    if (chartEl && managers.length) {
        var vals = managers.map(function(m){ return m[activeField]||0; });
        var maxV = Math.max.apply(null, vals.concat([1]));
        var chartH = 145;

        chartEl.style.paddingTop = '22px';
        chartEl.style.overflowY = 'visible';

        for (var g=1; g<=4; g++) {
            var gl = document.createElement('div');
            gl.className = 'bar-grid-line';
            gl.style.bottom = (22 + (chartH/4)*g) + 'px';
            chartEl.appendChild(gl);
        }

        managers.forEach(function(m) {
            var v = m[activeField]||0;
            var h = maxV > 0 ? Math.max(Math.round((v/maxV)*chartH), v>0?3:0) : 0;
            var col = document.createElement('div');
            col.className = 'bar-col';
            col.title = m.name + ': ' + v;

            var vl = document.createElement('div');
            vl.style.cssText = 'font-size:9.5px;font-weight:700;white-space:nowrap;color:var(--r-ink-soft);text-align:center;height:18px;line-height:18px;';
            vl.textContent = v > 0 ? v : '';

            var bar = document.createElement('div');
            bar.className = 'bar-inner';
            bar.style.height = h + 'px';
            bar.style.background = '#FF6B35';
            bar.style.opacity = '0.88';

            var lbl = document.createElement('div');
            lbl.className = 'bar-lbl';
            lbl.textContent = m.name.substring(0,7);

            var sub = document.createElement('div');
            sub.style.cssText = 'font-size:8.5px;color:var(--r-ink-soft);text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%';
            sub.textContent = m.team + ' نفر';

            col.appendChild(vl);
            col.appendChild(bar);
            col.appendChild(lbl);
            col.appendChild(sub);
            chartEl.appendChild(col);
        });
    }

    var stackEl = document.getElementById('mgrStackedChart');
    if (stackEl && managers.length) {
        var maxTot = Math.max.apply(null, managers.map(function(m){ return m.total; }).concat([1]));
        var maxW = 220;
        var segsConfig = [
            {key:'calls',c:'#1a73e8',l:'تماس'},
            {key:'meet', c:'#E6951E',l:'جلسه'},
            {key:'email',c:'#16A085',l:'ایمیل'},
            {key:'note', c:'#9e9e9e',l:'یادداشت'}
        ];
        managers.forEach(function(m) {
            var segHtml = segsConfig.map(function(s){
                var v = m[s.key]||0;
                if (!v) return '';
                var w = Math.max(Math.round((v/maxTot)*maxW), 6);
                return '<div class="tl-seg" style="width:'+w+'px;background:'+s.c+'" title="'+s.l+': '+v+'"></div>';
            }).join('');
            stackEl.innerHTML +=
                '<div class="timeline-row">' +
                '<span class="tl-date" style="width:70px;font-size:10.5px;font-weight:700;color:var(--r-ink)">' + m.name.substring(0,8) + '</span>' +
                '<div class="tl-bars">' + segHtml + '</div>' +
                '<span class="tl-total">' + m.total + '</span></div>';
        });
    }

    function drawDonut(canvasId, legendId, data) {
        var cvs = document.getElementById(canvasId);
        var legEl = document.getElementById(legendId);
        if (!cvs) return;
        var total = data.reduce(function(a,d){ return a+(d.value||0); }, 0);
        if (!total) { cvs.parentNode.style.display='none'; return; }
        var ctx = cvs.getContext('2d');
        var angle = -Math.PI/2;
        var cx=50,cy=50,r=40,ri=26;
        data.forEach(function(d, i) {
            if (!d.value) return;
            var slice = (d.value/total)*Math.PI*2;
            ctx.beginPath();
            ctx.moveTo(cx,cy);
            ctx.arc(cx,cy,r,angle,angle+slice);
            ctx.closePath();
            ctx.fillStyle = PIE_COLORS[i%PIE_COLORS.length];
            ctx.fill();
            angle += slice;
        });
        ctx.beginPath();
        ctx.arc(cx,cy,ri,0,Math.PI*2);
        ctx.fillStyle='#fff';
        ctx.fill();
        ctx.fillStyle='#14213D';
        ctx.font='bold 12px Vazirmatn, Segoe UI';
        ctx.textAlign='center';
        ctx.textBaseline='middle';
        ctx.fillText(total, cx, cy);

        if (legEl) {
            data.forEach(function(d, i) {
                if (!d.value) return;
                var pct = Math.round((d.value/total)*100);
                legEl.innerHTML += '<div class="donut-leg-row">'+
                    '<span style="display:flex;align-items:center"><span class="donut-dot" style="background:'+PIE_COLORS[i%PIE_COLORS.length]+'"></span>'+(d.name||'').substring(0,10)+'</span>'+
                    '<span style="font-weight:700">'+d.value+' <span style="color:var(--r-ink-soft);font-weight:400">('+pct+'%)</span></span></div>';
            });
        }
    }

    drawDonut('custDonut','custDonutLegend', pieCust);
    drawDonut('contDonut','contDonutLegend', pieCont);
})();
</script>