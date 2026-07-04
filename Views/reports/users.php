<!-- views/reports/users.php -->
<?php
// آماده‌سازی داده برای JS
$js_users = [];
foreach ($users_data as $ud) {
    $js_users[] = [
        'id'    => (int)$ud['id'],
        'name'  => mb_substr($ud['full_name'], 0, 12),
        'role'  => $ud['role'],
        'calls' => (int)($ud['calls']??0),
        'meet'  => (int)($ud['meetings']??0),
        'email' => (int)($ud['emails']??0),
        'note'  => (int)($ud['notes']??0),
        'total' => (int)($ud['total_activities']??0),
        'cust'  => (int)($ud['customers']??0),
        'cont'  => (int)($ud['contacts']??0),
        'tasks' => (int)($ud['tasks']??0),
        'done'  => (int)($ud['completed']??0),
    ];
}
$users_json = json_encode($js_users);
$pie_cust_json = json_encode($pie_customers);
$pie_cont_json = json_encode($pie_contacts);

$js_detail = [];
if ($detail_user) {
    foreach ($daily_detail as $d) {
        $js_detail[] = [
            'dt'    => $d['dt'],
            'calls' => (int)$d['calls'],
            'meet'  => (int)$d['meetings'],
            'email' => (int)$d['emails'],
            'note'  => (int)$d['notes'],
            'total' => (int)$d['calls']+(int)$d['meetings']+(int)$d['emails']+(int)$d['notes'],
        ];
    }
}
$detail_json = json_encode(array_reverse($js_detail));

$chart_labels = [
    'total_activities' => 'کل فعالیت‌ها',
    'activities'       => 'کل فعالیت‌ها',
    'calls'            => 'تماس‌ها',
    'meetings'         => 'جلسات',
    'customers'        => 'مشتریان جدید',
    'contacts'         => 'مخاطبین',
    'tasks'            => 'تسک‌ها',
];
$chart_field_map = [
    'activities'       => 'total',
    'total_activities' => 'total',
    'calls'            => 'calls',
    'meetings'         => 'meet',
    'customers'        => 'cust',
    'contacts'         => 'cont',
    'tasks'            => 'tasks',
];
$active_field = $chart_field_map[$chart_type] ?? 'total';
?>

<?php require_once __DIR__ . '/_report_styles.php'; ?>

<div class="rpt-wrap">

<!-- Navigation -->
<div class="rpt-nav">
    <h2>📊 گزارش کاربران</h2>
    <div class="rpt-tabs">
        <a href="index.php?page=reports&action=self" class="rpt-tab">خودم</a>
        <a href="index.php?page=reports&action=users" class="rpt-tab active">کاربران</a>
        <a href="index.php?page=reports&action=managers" class="rpt-tab">مدیران</a>
    </div>
</div>

<!-- Filter -->
<form method="GET">
    <input type="hidden" name="page" value="reports">
    <input type="hidden" name="action" value="users">
    <div class="rpt-filter">
        <div><label>از تاریخ</label><input type="date" name="date_from" value="<?= $date_from ?>" onchange="this.form.submit()"></div>
        <div><label>تا تاریخ</label><input type="date" name="date_to" value="<?= $date_to ?>" onchange="this.form.submit()"></div>
        <div style="flex:2; min-width:180px;">
            <label>نمودار بر اساس</label>
            <select name="chart_type" onchange="this.form.submit()" style="width:100%">
                <?php
                $opts = ['activities'=>'کل فعالیت‌ها','calls'=>'تماس‌ها','meetings'=>'جلسات','customers'=>'مشتریان جدید','contacts'=>'مخاطبین','tasks'=>'تسک‌ها'];
                foreach ($opts as $v => $l): ?>
                <option value="<?= $v ?>" <?= $chart_type === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($filter_user_id > 0): ?>
        <a href="index.php?page=reports&action=users&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&chart_type=<?= $chart_type ?>" class="rpt-btn-sm" style="align-self:flex-end">× پاک‌کردن فیلتر</a>
        <?php endif; ?>
    </div>
</form>

<?php if (!empty($users_data)): ?>

<!-- Summary KPI row -->
<?php
$sum_total = array_sum(array_column($users_data, 'total_activities'));
$sum_cust  = array_sum(array_column($users_data, 'customers'));
$sum_cont  = array_sum(array_column($users_data, 'contacts'));
$sum_tasks = array_sum(array_column($users_data, 'tasks'));
$sum_done  = array_sum(array_column($users_data, 'completed'));
$done_rate = $sum_tasks > 0 ? round($sum_done / $sum_tasks * 100) : 0;
?>
<div class="kpi-grid kpi-grid-5" style="--cols:5">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#e8f0fe">👥</div>
        <div class="kpi-val" style="color:#1a73e8"><?= count($users_data) ?></div>
        <div class="kpi-label">کاربران</div>
        <div class="kpi-accent" style="background:#1a73e8;opacity:.2"></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#e0f2f1">📋</div>
        <div class="kpi-val" style="color:#00897b"><?= $sum_total ?></div>
        <div class="kpi-label">کل فعالیت</div>
        <div class="kpi-accent" style="background:#00897b;opacity:.2"></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fce8e6">🏢</div>
        <div class="kpi-val" style="color:#d93025"><?= $sum_cust ?></div>
        <div class="kpi-label">مشتری جدید</div>
        <div class="kpi-accent" style="background:#d93025;opacity:.2"></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#e6f4ea">✅</div>
        <div class="kpi-val" style="color:#34a853"><?= $sum_done ?></div>
        <div class="kpi-label">تسک انجام‌شده</div>
        <div class="kpi-accent" style="background:#34a853;opacity:.2"></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fff8e1">🎯</div>
        <div class="kpi-val" style="color:#f5a623"><?= $done_rate ?>%</div>
        <div class="kpi-label">نرخ تکمیل</div>
        <div class="kpi-accent" style="background:#f5a623;opacity:.2"></div>
    </div>
</div>

<!-- Charts Row: Bar + Pie -->
<div class="chart-row">

    <!-- Bar Chart -->
    <div class="rpt-card">
        <div class="rpt-card-hd">
            <h3>📊 مقایسه کاربران — <?= $chart_labels[$chart_type] ?? 'کل فعالیت‌ها' ?></h3>
            <span style="font-size:10px;color:var(--text-light)">کلیک برای جزئیات</span>
        </div>
        <div class="rpt-card-body">
            <div id="usersBarChart" class="bar-chart-wrap" style="height:180px"></div>
        </div>
    </div>

    <!-- Donut: customer share -->
    <div class="rpt-card">
        <div class="rpt-card-hd"><h3>🥧 سهم مشتری</h3></div>
        <div class="rpt-card-body">
            <div class="donut-wrap">
                <canvas id="custDonut" width="100" height="100"></canvas>
                <div class="donut-legend" id="custDonutLegend"></div>
            </div>
        </div>
    </div>
</div>

<!-- Ranking Table -->
<div class="rpt-card" style="margin-bottom:18px">
    <div class="rpt-card-hd">
        <h3>🏆 رتبه‌بندی کاربران</h3>
        <span style="font-size:10px;color:var(--text-light)">مرتب‌شده بر اساس <?= $chart_labels[$chart_type] ?? 'کل فعالیت' ?></span>
    </div>
    <div style="overflow-x:auto">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th style="width:36px">#</th>
                    <th>کاربر</th>
                    <th style="text-align:center">نقش</th>
                    <th style="text-align:center">📞</th>
                    <th style="text-align:center">🤝</th>
                    <th style="text-align:center">📧</th>
                    <th style="text-align:center">📋 کل</th>
                    <th style="text-align:center">👥</th>
                    <th style="text-align:center">✅</th>
                    <th style="min-width:80px">نمودار</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sorted = $users_data;
                $field_map_php = [
                    'activities'       => 'total_activities',
                    'total_activities' => 'total_activities',
                    'calls'            => 'calls',
                    'meetings'         => 'meetings',
                    'customers'        => 'customers',
                    'contacts'         => 'contacts',
                    'tasks'            => 'tasks',
                ];
                $sort_field = $field_map_php[$chart_type] ?? 'total_activities';
                usort($sorted, fn($a,$b) => ($b[$sort_field]??0) - ($a[$sort_field]??0));
                $max_total = max(1, max(array_column($sorted, 'total_activities')));
                $medals = ['🥇','🥈','🥉'];
                $role_labels = ['super_admin'=>'سوپر ادمین','admin'=>'مدیر','manager'=>'مدیر فروش','agent'=>'کارشناس'];
                $role_colors = ['super_admin'=>['#fef3c7','#d97706'],'admin'=>['#dbeafe','#1d4ed8'],'manager'=>['#ffedd5','#c2410c'],'agent'=>['#d1fae5','#065f46']];
                foreach ($sorted as $i => $ud):
                    $rc = $role_colors[$ud['role']] ?? ['#f3f4f6','#374151'];
                    $bar_w = $max_total > 0 ? round(($ud['total_activities'] / $max_total) * 100) : 0;
                    $is_selected = ($filter_user_id > 0 && $ud['id'] == $filter_user_id);
                ?>
                <tr style="cursor:pointer;<?= $is_selected ? 'background:rgba(26,115,232,.06);' : '' ?>"
                    onclick="window.location='index.php?page=reports&action=users&user_id=<?= $ud['id'] ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&chart_type=<?= $chart_type ?>'">
                    <td style="color:var(--text-light);text-align:center;font-size:13px"><?= $medals[$i] ?? ($i+1) ?></td>
                    <td style="font-weight:600"><?= crm_sanitize($ud['full_name']) ?></td>
                    <td style="text-align:center">
                        <span class="act-pill" style="background:<?= $rc[0] ?>;color:<?= $rc[1] ?>"><?= $role_labels[$ud['role']] ?? $ud['role'] ?></span>
                    </td>
                    <td style="text-align:center"><?= $ud['calls']??0 ?></td>
                    <td style="text-align:center"><?= $ud['meetings']??0 ?></td>
                    <td style="text-align:center"><?= $ud['emails']??0 ?></td>
                    <td style="text-align:center"><strong style="color:#1a73e8"><?= $ud['total_activities']??0 ?></strong></td>
                    <td style="text-align:center"><?= $ud['customers']??0 ?></td>
                    <td style="text-align:center;color:#34a853;font-weight:600"><?= $ud['completed']??0 ?></td>
                    <td>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill" style="width:<?= $bar_w ?>%;background:#1a73e8"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Detail section for selected user -->
<?php if (!empty($detail_user)): ?>
<div id="detail-section" class="rpt-card rpt-detail-card" style="margin-bottom:18px;border:2px solid #1a73e8">
    <div class="rpt-card-hd" style="background:rgba(26,115,232,.05)">
        <h3>👤 جزئیات: <?= crm_sanitize($detail_user['full_name']) ?></h3>
        <a href="index.php?page=reports&action=users&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&chart_type=<?= $chart_type ?>" style="font-size:11px;color:var(--text-light)">× بستن</a>
    </div>
    <div class="rpt-card-body">
        <!-- Detail KPI -->
        <div class="kpi-grid" style="margin-bottom:14px">
            <?php
            $dkpis = [
                ['l'=>'تماس','v'=>$detail_user['calls']??0,'c'=>'#1a73e8','bg'=>'#e8f0fe'],
                ['l'=>'جلسه','v'=>$detail_user['meetings']??0,'c'=>'#f5a623','bg'=>'#fff8e1'],
                ['l'=>'ایمیل','v'=>$detail_user['emails']??0,'c'=>'#34a853','bg'=>'#e6f4ea'],
                ['l'=>'کل فعالیت','v'=>$detail_user['total_activities']??0,'c'=>'#00897b','bg'=>'#e0f2f1'],
                ['l'=>'مشتری','v'=>$detail_user['customers']??0,'c'=>'#d93025','bg'=>'#fce8e6'],
                ['l'=>'مخاطب','v'=>$detail_user['contacts']??0,'c'=>'#1a73e8','bg'=>'#e8f0fe'],
                ['l'=>'تسک','v'=>$detail_user['tasks']??0,'c'=>'#f5a623','bg'=>'#fff8e1'],
                ['l'=>'تکمیل','v'=>$detail_user['completed']??0,'c'=>'#34a853','bg'=>'#e6f4ea'],
            ];
            foreach ($dkpis as $k): ?>
            <div class="kpi-card">
                <div class="kpi-val" style="color:<?= $k['c'] ?>;font-size:20px"><?= $k['v'] ?></div>
                <div class="kpi-label"><?= $k['l'] ?></div>
                <div class="kpi-accent" style="background:<?= $k['c'] ?>;opacity:.2"></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Detail Timeline -->
        <?php if (!empty($daily_detail)): ?>
        <div class="tl-legend" style="margin-bottom:8px">
            <span class="tl-leg-item"><span class="tl-leg-dot" style="background:#1a73e8"></span>تماس</span>
            <span class="tl-leg-item"><span class="tl-leg-dot" style="background:#f5a623"></span>جلسه</span>
            <span class="tl-leg-item"><span class="tl-leg-dot" style="background:#34a853"></span>ایمیل</span>
            <span class="tl-leg-item"><span class="tl-leg-dot" style="background:#9e9e9e"></span>یادداشت</span>
        </div>
        <div id="detailTimeline"></div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Contact share Donut (full width) -->
<?php if ($total_contacts_all > 0): ?>
<div class="chart-row">
    <div class="rpt-card">
        <div class="rpt-card-hd"><h3>🥧 سهم کاربران — ثبت مخاطب</h3></div>
        <div class="rpt-card-body">
            <div class="donut-wrap">
                <canvas id="contDonut" width="100" height="100"></canvas>
                <div class="donut-legend" id="contDonutLegend"></div>
            </div>
        </div>
    </div>
    <div class="rpt-card" style="display:flex;align-items:center;justify-content:center;padding:20px">
        <div style="text-align:center">
            <div style="font-size:32px;font-weight:700;color:#1a73e8"><?= $total_customers_all ?></div>
            <div style="font-size:12px;color:var(--text-light)">مشتری ثبت‌شده</div>
            <div style="margin-top:12px;font-size:32px;font-weight:700;color:#00897b"><?= $total_contacts_all ?></div>
            <div style="font-size:12px;color:var(--text-light)">مخاطب ثبت‌شده</div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<div class="rpt-card" style="text-align:center;padding:48px;color:var(--text-light)">
    <div style="font-size:32px;margin-bottom:8px">📭</div>
    <div>داده‌ای برای نمایش وجود ندارد.</div>
</div>
<?php endif; ?>

</div><!-- /rpt-wrap -->

<script>
(function(){
    var users   = <?= $users_json ?>;
    var pieCust = <?= $pie_cust_json ?>;
    var pieCont = <?= $pie_cont_json ?>;
    var detail  = <?= $detail_json ?>;
    var activeField = '<?= $active_field ?>';
    var dateFrom = '<?= $date_from ?>';
    var dateTo   = '<?= $date_to ?>';
    var chartType = '<?= $chart_type ?>';
    var PIE_COLORS = ['#1a73e8','#f5a623','#34a853','#9c27b0','#ea4335','#00bcd4','#ff7043','#607d8b'];

    // ===== Users Bar Chart =====
    var chartEl = document.getElementById('usersBarChart');
    if (chartEl && users.length) {
        var fieldMap = {total:'total',calls:'calls',meet:'meet',email:'email',note:'note',cust:'cust',cont:'cont',tasks:'tasks'};
        var f = fieldMap[activeField] || 'total';
        var vals = users.map(function(u){ return u[f]||0; });
        var maxV = Math.max.apply(null, vals.concat([1]));
        // ارتفاع chart کمتر از wrapper تا عدد بالای ستون کلیپ نشه
        var chartH = 130;

        // اضافه کردن padding-top به wrapper تا فضا برای عدد باشه
        chartEl.style.paddingTop = '22px';
        // overflow-y باید visible باشه
        chartEl.style.overflowY = 'visible';

        for (var g=1; g<=4; g++) {
            var gl = document.createElement('div');
            gl.className = 'bar-grid-line';
            gl.style.bottom = (22 + (chartH/4)*g) + 'px';
            chartEl.appendChild(gl);
        }

        var roleColors = {super_admin:'#f59e0b',admin:'#1a73e8',manager:'#f97316',agent:'#34a853'};
        users.forEach(function(u) {
            var uid = u.id; // capture برای closure
            var v = u[f]||0;
            var h = maxV > 0 ? Math.max(Math.round((v/maxV)*chartH), v>0?3:0) : 0;
            var col = document.createElement('div');
            col.className = 'bar-col';
            col.style.cursor = 'pointer';
            col.title = u.name + ': ' + v;
            col.onclick = function(){
                window.location = 'index.php?page=reports&action=users&user_id='+uid+'&date_from='+dateFrom+'&date_to='+dateTo+'&chart_type='+chartType+'#detail-section';
            };

            var bar = document.createElement('div');
            bar.className = 'bar-inner';
            bar.style.height = h + 'px';
            bar.style.background = roleColors[u.role] || '#1a73e8';
            bar.style.opacity = '0.85';
            bar.style.position = 'relative';
            // overflow visible روی bar هم لازمه
            bar.style.overflow = 'visible';

            // عدد بالای ستون — خارج از bar با margin-bottom روی col
            var vl = document.createElement('div');
            vl.style.cssText = 'font-size:9px;font-weight:700;white-space:nowrap;color:var(--text);text-align:center;height:18px;line-height:18px;';
            vl.textContent = v > 0 ? v : '';

            var lbl = document.createElement('div');
            lbl.className = 'bar-lbl';
            lbl.textContent = u.name.substring(0,6);

            // ترتیب: عدد → ستون → برچسب
            col.appendChild(vl);
            col.appendChild(bar);
            col.appendChild(lbl);
            chartEl.appendChild(col);
        });
    }

    // ===== Donut helper =====
    function drawDonut(canvasId, legendId, data, totalLabel) {
        var cvs = document.getElementById(canvasId);
        var legEl = document.getElementById(legendId);
        if (!cvs) return;
        var total = data.reduce(function(a,d){ return a+(d.value||0); }, 0);
        if (total === 0) { cvs.style.display='none'; return; }
        var ctx = cvs.getContext('2d');
        var angle = -Math.PI/2;
        var cx=50, cy=50, r=40, ri=26;
        data.forEach(function(d, i) {
            if (!d.value) return;
            var slice = (d.value/total)*Math.PI*2;
            ctx.beginPath();
            ctx.moveTo(cx,cy);
            ctx.arc(cx,cy,r,angle,angle+slice);
            ctx.closePath();
            ctx.fillStyle = PIE_COLORS[i % PIE_COLORS.length];
            ctx.fill();
            angle += slice;
        });
        ctx.beginPath();
        ctx.arc(cx,cy,ri,0,Math.PI*2);
        ctx.fillStyle='#fff';
        ctx.fill();
        ctx.fillStyle='#333';
        ctx.font='bold 12px Segoe UI';
        ctx.textAlign='center';
        ctx.textBaseline='middle';
        ctx.fillText(total, cx, cy);

        if (legEl) {
            data.forEach(function(d, i) {
                if (!d.value) return;
                var pct = Math.round((d.value/total)*100);
                legEl.innerHTML += '<div class="donut-leg-row">'+
                    '<span style="display:flex;align-items:center"><span class="donut-dot" style="background:'+PIE_COLORS[i%PIE_COLORS.length]+'"></span>'+
                    (d.name||'').substring(0,10)+'</span>'+
                    '<span style="font-weight:700">'+d.value+' <span style="color:var(--text-light);font-weight:400">('+pct+'%)</span></span></div>';
            });
        }
    }

    drawDonut('custDonut', 'custDonutLegend', pieCust, 'مشتری');
    drawDonut('contDonut', 'contDonutLegend', pieCont, 'مخاطب');

    // ===== Detail Timeline =====
    var tlEl = document.getElementById('detailTimeline');
    if (tlEl && detail.length) {
        var maxTot = Math.max.apply(null, detail.map(function(d){ return d.total; }).concat([1]));
        var maxW = 200;
        var recent = detail.slice(-14);
        recent.forEach(function(d) {
            var parts = d.dt.split('-');
            var dateStr = parts[1]+'/'+parts[2];
            var segs = [
                {v:d.calls,c:'#1a73e8'},
                {v:d.meet, c:'#f5a623'},
                {v:d.email,c:'#34a853'},
                {v:d.note, c:'#9e9e9e'}
            ];
            var segHtml = segs.map(function(s){
                if (!s.v) return '';
                var w = Math.max(Math.round((s.v/maxTot)*maxW), 6);
                return '<div class="tl-seg" style="width:'+w+'px;background:'+s.c+'" title="'+s.v+'"></div>';
            }).join('');
            tlEl.innerHTML += '<div class="timeline-row">'+
                '<span class="tl-date">'+dateStr+'</span>'+
                '<div class="tl-bars">'+segHtml+'</div>'+
                '<span class="tl-total">'+d.total+'</span></div>';
        });
    }

    // ===== اسکرول به بخش جزئیات بعد از load =====
    var detailEl = document.getElementById('detail-section');
    if (detailEl) {
        // کمی تاخیر تا صفحه کامل render بشه
        setTimeout(function(){
            detailEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 120);
    }
})();
</script>
