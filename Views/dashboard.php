<!-- Views/dashboard.php -->
<style>
/* ════════════════════════════════════════
   داشبورد — تک‌محوری (اصل سادگی گوگل)
   اولویت ۱: «امروز با کی تماس بگیرم؟»
   بقیه آمار/گزارش پشت یک آکاردئون جمع‌شونده
   ════════════════════════════════════════ */

.dash-hello { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:18px; flex-wrap:wrap; }
.dash-hello h2 { font-size:19px; font-weight:800; color:var(--ink); letter-spacing:-.01em; }
.dash-hello .role-pill {
    display:inline-flex; align-items:center; gap:5px; margin-top:6px;
    font-size:11.5px; font-weight:700; padding:4px 12px; border-radius:20px;
    background:#FFF1EA; color:var(--ember-deep);
}
.dash-date { font-size:12px; color:var(--ink-soft); text-align:left; }

/* ── Expired subscription alert ── */
.dash-expired {
    background:#FCE8E6; border:1px solid #F5C6CB; border-radius:14px;
    padding:22px; text-align:center; margin-bottom:18px;
}
.dash-expired h3 { color:var(--danger-deep); font-size:16px; font-weight:800; margin-bottom:6px; }
.dash-expired p { color:#8C3024; font-size:13px; margin-bottom:14px; }
.dash-expired .btn-renew {
    display:inline-block; background:var(--ember); color:#fff; padding:10px 24px;
    border-radius:10px; font-size:13.5px; font-weight:700; text-decoration:none;
}

/* ── کارت اصلی: پیگیری امروز (تنها تمرکز صفحه) ── */
.dash-today-card {
    background:var(--card); border:1.5px solid var(--ember); border-radius:16px;
    overflow:hidden; margin-bottom:16px; box-shadow:0 6px 24px rgba(255,107,53,.08);
}
.dash-today-hd {
    padding:16px 18px; border-bottom:1px solid var(--line);
    display:flex; justify-content:space-between; align-items:center;
    background:#FFF8F4;
}
.dash-today-hd h2 { font-size:15px; font-weight:800; color:var(--ink); }
.dash-today-hd .count-badge { background:var(--ember); color:#fff; font-size:12px; font-weight:800; padding:3px 11px; border-radius:13px; }
.dash-today-body { padding:10px 14px 14px; }

/* ── Reminder rows ── */
.dash-reminder {
    display:flex; align-items:center; gap:12px; padding:12px 10px; border-radius:10px;
    text-decoration:none; color:inherit; margin-bottom:6px; transition:background .15s;
    border:1px solid transparent;
}
.dash-reminder:hover { background:var(--paper-2); border-color:var(--line); }
.dash-reminder-time {
    font-size:12px; font-weight:800; color:var(--ember-deep); min-width:50px; text-align:center;
    background:#FFF1EA; padding:7px 8px; border-radius:8px; flex-shrink:0;
}
.dash-reminder-content { flex:1; min-width:0; }
.dash-reminder-company { font-size:14px; font-weight:700; color:var(--ink); }
.dash-reminder-title { font-size:12.5px; color:var(--ink-soft); margin-top:1px; }
.dash-reminder-meta { display:flex; gap:6px; align-items:center; margin-top:4px; flex-wrap:wrap; }
.dash-reminder-owner { font-size:10.5px; color:var(--ink-soft); background:var(--paper-2); padding:1px 8px; border-radius:10px; }
.dash-reminder-topic { font-size:11px; color:var(--ink-soft); }
.dash-reminder-arrow { font-size:16px; color:var(--line); flex-shrink:0; }

/* برچسب تأخیر — برای فرصت‌های فروشی که از تاریخ پیگیری‌شان گذشته */
.dash-reminder-time.overdue { background:#FCE8E6; color:var(--danger); }
.dash-overdue-tag {
    font-size:10.5px; font-weight:700; color:var(--danger); background:#FCE8E6;
    padding:1px 8px; border-radius:10px;
}

.dash-empty { text-align:center; padding:32px 16px; color:var(--ink-soft); }
.dash-empty p { font-size:13.5px; }

/* ── Quick actions ── */
.dash-quick { display:flex; gap:10px; margin-bottom:18px; flex-wrap:wrap; }
.dash-quick a {
    flex:1; min-width:150px; display:flex; align-items:center; justify-content:center; gap:8px;
    padding:13px 16px; border-radius:12px; font-size:13.5px; font-weight:700; text-decoration:none;
}
.dash-quick .primary { background:var(--ember); color:#fff; box-shadow:0 4px 14px rgba(255,107,53,.28); }
.dash-quick .secondary { background:var(--card); color:var(--ink); border:1.5px solid var(--line); }

/* ── دکمه باز کردن «آمار و گزارش بیشتر» ── */
.dash-more-toggle {
    display:flex; align-items:center; justify-content:center; gap:7px;
    width:100%; background:var(--card); border:1.5px dashed var(--line); border-radius:12px;
    padding:12px; margin-bottom:16px; cursor:pointer; color:var(--ink-soft);
    font-size:13px; font-weight:700; transition:all .15s;
}
.dash-more-toggle:hover { border-color:var(--ember); color:var(--ember-deep); }
.dash-more-toggle .arrow { font-size:11px; transition:transform .2s; }
.dash-more-toggle.open .arrow { transform:rotate(90deg); }

.dash-more-section { display:none; }
.dash-more-section.open { display:block; }

/* ── KPI grid ── */
.dash-kpi-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-bottom:18px; }
@media(min-width:600px){ .dash-kpi-grid { grid-template-columns:repeat(4,1fr); } }
.dash-kpi {
    background:var(--card); border:1px solid var(--line); border-radius:14px;
    padding:16px 14px; position:relative; overflow:hidden;
}
.dash-kpi-icon { width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px; margin-bottom:10px; }
.dash-kpi-val { font-size:24px; font-weight:800; color:var(--ink); line-height:1; }
.dash-kpi-label { font-size:11.5px; color:var(--ink-soft); margin-top:5px; }
.dash-kpi-accent { position:absolute; bottom:0; right:0; left:0; height:3px; }

/* ── Plan status card (admin) ── */
.dash-plan {
    background:linear-gradient(135deg, var(--ink) 0%, #1C2D52 100%);
    border-radius:16px; padding:22px; margin-bottom:18px; position:relative; overflow:hidden; color:#fff;
}
.dash-plan::before { content:''; position:absolute; inset:0; background:radial-gradient(circle at 85% 20%, rgba(255,107,53,.25), transparent 50%); }
.dash-plan-inner { position:relative; }
.dash-plan-top { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:8px; }
.dash-plan-title { font-size:13px; font-weight:700; color:#C7CEE0; display:flex; align-items:center; gap:6px; }
.dash-plan-badge { font-size:11px; font-weight:800; padding:3px 12px; border-radius:20px; background:rgba(255,107,53,.18); color:#FFB48A; }
.dash-plan-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; }
@media(min-width:500px){ .dash-plan-grid { grid-template-columns:repeat(3,1fr); } }
.dash-plan-stat-num { font-size:22px; font-weight:800; color:#fff; }
.dash-plan-stat-label { font-size:11px; color:#9AA4C4; margin-top:3px; }
.dash-plan-progress-wrap { margin-top:16px; }
.dash-plan-progress-bar { height:6px; background:rgba(255,255,255,.15); border-radius:4px; overflow:hidden; margin-top:6px; }
.dash-plan-progress-fill { height:100%; border-radius:4px; }
.dash-plan-cta {
    display:inline-block; margin-top:16px; background:var(--ember); color:#fff;
    padding:9px 20px; border-radius:9px; font-size:12.5px; font-weight:700; text-decoration:none;
}

/* ── Section cards ── */
.dash-card { background:var(--card); border:1px solid var(--line); border-radius:14px; overflow:hidden; margin-bottom:16px; }
.dash-card-hd { padding:14px 16px; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center; }
.dash-card-hd h3 { font-size:14px; font-weight:700; color:var(--ink); }
.dash-card-hd .count-badge { background:var(--ember); color:#fff; font-size:11px; font-weight:800; padding:2px 9px; border-radius:12px; }
.dash-card-body { padding:8px 12px 12px; }

/* ── Mini weekly chart ── */
.dash-chart-wrap { padding:6px 4px 4px; }
.dash-chart { display:flex; align-items:flex-end; gap:8px; height:90px; padding-top:18px; }
.dash-chart-col { flex:1; display:flex; flex-direction:column; align-items:center; }
.dash-chart-bar { width:100%; max-width:30px; border-radius:6px 6px 2px 2px; background:var(--ember); opacity:.85; position:relative; transition:opacity .15s; }
.dash-chart-bar:hover { opacity:1; }
.dash-chart-val { font-size:10px; font-weight:700; color:var(--ink-soft); margin-bottom:3px; height:12px; }
.dash-chart-label { font-size:10px; color:var(--ink-soft); margin-top:6px; }

/* ── Open tasks overview table ── */
.dash-table { width:100%; border-collapse:collapse; font-size:12.5px; }
.dash-table th { text-align:right; padding:9px 10px; color:var(--ink-soft); font-weight:600; font-size:11px; border-bottom:1px solid var(--line); background:var(--paper-2); }
.dash-table td { padding:10px; border-bottom:1px solid var(--line); color:var(--ink); }
.dash-table tr:last-child td { border:none; }
.dash-table tr:hover td { background:var(--paper-2); }
.dash-table a { color:var(--ink); text-decoration:none; }
.dash-table a:hover { color:var(--ember-deep); }
.dash-followup-soon { color:var(--ember-deep); font-weight:700; }
.dash-followup-none { color:#A8A295; }

/* mobile card view for open tasks */
.dash-task-cards { display:none; }
@media(max-width:680px){
    .dash-table-wrap { display:none; }
    .dash-task-cards { display:block; }
}
.dash-task-card {
    border:1px solid var(--line); border-radius:10px; padding:12px; margin-bottom:8px;
    text-decoration:none; color:inherit; display:block;
}
.dash-task-card .company { font-size:13px; font-weight:700; color:var(--ink); }
.dash-task-card .title { font-size:12px; color:var(--ink-soft); margin-top:2px; }
.dash-task-card .meta-row { display:flex; justify-content:space-between; align-items:center; margin-top:8px; font-size:11px; }
</style>

<div class="dash-wrap">

<?php if (!$subscription_active): ?>

<div class="dash-expired">
    <h3>اشتراک شما به پایان رسیده است</h3>
    <p>برای دسترسی مجدد به اطلاعات و ادامه کار، لطفاً پلن خود را تمدید کنید.<br>
    <small>تاریخ پایان: <?= function_exists('jdate') ? jdate($user['plan_expiry']) : date('Y/m/d', strtotime($user['plan_expiry'])) ?></small></p>
    <?php if (in_array($user['role'], ['admin','super_admin'])): ?>
    <a href="index.php?page=plans" class="btn-renew">تمدید اشتراک</a>
    <?php endif; ?>
</div>

<?php else: ?>

<!-- ═══ خوش‌آمدگویی ═══ -->
<div class="dash-hello">
    <div>
        <h2>سلام، <?= crm_sanitize($user['full_name']) ?></h2>
        <span class="role-pill"><?= crm_sanitize($role_label) ?></span>
    </div>
    <div class="dash-date"><?= function_exists('jdate') ? jdate(date('Y-m-d'), 'j F Y') : date('Y/m/d') ?></div>
</div>

<!-- ═══ تمرکز اصلی صفحه: امروز با کی تماس بگیرم؟ (شامل تأخیردارها هم می‌شود) ═══ -->
<div class="dash-today-card">
    <div class="dash-today-hd">
        <h2>امروز با کی تماس بگیرم؟</h2>
        <span class="count-badge"><?= count($today_reminders) ?></span>
    </div>
    <div class="dash-today-body">
        <?php if (empty($today_reminders)): ?>
            <div class="dash-empty">
                <p>پیگیری‌ای برای امروز نداری.</p>
            </div>
        <?php else: ?>
            <?php
            $today_str = date('Y-m-d');
            foreach ($today_reminders as $r):
                $r_date = date('Y-m-d', strtotime($r['next_followup_date']));
                $is_overdue = $r_date < $today_str;
                $days_late = $is_overdue ? (int)((strtotime($today_str) - strtotime($r_date)) / 86400) : 0;
            ?>
            <a href="index.php?page=tasks&action=view&id=<?= $r['id'] ?>" class="dash-reminder">
                <div class="dash-reminder-time<?= $is_overdue ? ' overdue' : '' ?>">
                    <?= $is_overdue ? jdate($r['next_followup_date'], 'm/d') : date('H:i', strtotime($r['next_followup_date'])) ?>
                </div>
                <div class="dash-reminder-content">
                    <div class="dash-reminder-company"><?= crm_sanitize($r['company_name']) ?></div>
                    <div class="dash-reminder-title"><?= crm_sanitize($r['title']) ?></div>
                    <div class="dash-reminder-meta">
                        <?php if ($is_overdue): ?>
                        <span class="dash-overdue-tag"><?= $days_late ?> روز تأخیر</span>
                        <?php endif; ?>
                        <?php if (!empty($r['agent_name']) && $is_manager): ?>
                        <span class="dash-reminder-owner"><?= crm_sanitize($r['agent_name']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($r['next_followup_topic'])): ?>
                        <span class="dash-reminder-topic"><?= crm_sanitize($r['next_followup_topic']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <span class="dash-reminder-arrow" aria-hidden="true">‹</span>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ═══ اکشن سریع ═══ -->
<div class="dash-quick">
    <a href="index.php?page=customers&action=add" class="primary">مشتری جدید</a>
    <a href="index.php?page=tasks&action=add" class="secondary">فرصت فروش جدید</a>
</div>

<!-- ═══ دکمه باز کردن آمار و گزارش بیشتر ═══ -->
<button type="button" class="dash-more-toggle" id="dash-more-btn">
    <span class="arrow" aria-hidden="true">›</span>
    <span id="dash-more-label">نمایش آمار و گزارش بیشتر</span>
</button>

<div class="dash-more-section" id="dash-more-section">

    <!-- ═══ وضعیت اشتراک (admin) ═══ -->
    <?php if ($is_admin && isset($max_users_limit)): ?>
    <div class="dash-plan">
        <div class="dash-plan-inner">
            <div class="dash-plan-top">
                <span class="dash-plan-title">وضعیت اشتراک</span>
                <span class="dash-plan-badge"><?= $plan_type === 'trial' ? 'دوره آزمایشی' : 'فعال' ?></span>
            </div>
            <div class="dash-plan-grid">
                <div>
                    <div class="dash-plan-stat-num"><?= $days_left ?></div>
                    <div class="dash-plan-stat-label">روز باقی‌مانده</div>
                </div>
                <div>
                    <div class="dash-plan-stat-num"><?= $active_users_count ?> / <?= $max_users_limit ?></div>
                    <div class="dash-plan-stat-label">کاربر فعال</div>
                </div>
                <div>
                    <div class="dash-plan-stat-num"><?= $total_customers ?></div>
                    <div class="dash-plan-stat-label">کل مشتریان</div>
                </div>
            </div>
            <div class="dash-plan-progress-wrap">
                <div style="display:flex;justify-content:space-between;font-size:11px;color:#9AA4C4">
                    <span>ظرفیت کاربران</span>
                    <span><?= $max_users_limit > 0 ? round($active_users_count/$max_users_limit*100) : 0 ?>%</span>
                </div>
                <div class="dash-plan-progress-bar">
                    <div class="dash-plan-progress-fill" style="width:<?= $max_users_limit>0 ? min(100, round($active_users_count/$max_users_limit*100)) : 0 ?>%; background:<?= $days_left <= 3 ? 'var(--danger)' : 'var(--ember)' ?>"></div>
                </div>
            </div>
            <a href="index.php?page=plans" class="dash-plan-cta"><?= $days_left <= 3 ? 'تمدید فوری اشتراک' : 'مدیریت پلن' ?></a>
        </div>
    </div>
    <?php endif; ?>

    <!-- ═══ KPI ها ═══ -->
    <div class="dash-kpi-grid">
        <div class="dash-kpi">
            <div class="dash-kpi-icon" style="background:#FFF1EA" aria-hidden="true">⏰</div>
            <div class="dash-kpi-val"><?= $today_count ?></div>
            <div class="dash-kpi-label">نیاز به پیگیری</div>
            <div class="dash-kpi-accent" style="background:var(--ember)"></div>
        </div>
        <div class="dash-kpi">
            <div class="dash-kpi-icon" style="background:#E8F0FE" aria-hidden="true">🏢</div>
            <div class="dash-kpi-val"><?= $total_customers ?></div>
            <div class="dash-kpi-label">کل مشتریان</div>
            <div class="dash-kpi-accent" style="background:var(--blue)"></div>
        </div>
        <div class="dash-kpi">
            <div class="dash-kpi-icon" style="background:#E7F7F3" aria-hidden="true">💰</div>
            <div class="dash-kpi-val"><?= $task_kpi['sold'] ?? 0 ?></div>
            <div class="dash-kpi-label">فروش (۳۰ روز)</div>
            <div class="dash-kpi-accent" style="background:var(--teal)"></div>
        </div>
        <div class="dash-kpi">
            <div class="dash-kpi-icon" style="background:#F3E8FD" aria-hidden="true">✅</div>
            <div class="dash-kpi-val"><?= $task_kpi['completed'] ?? 0 ?></div>
            <div class="dash-kpi-label">تکمیل (۳۰ روز)</div>
            <div class="dash-kpi-accent" style="background:var(--purple)"></div>
        </div>
    </div>

    <!-- ═══ نمودار کوچک ۷ روز ═══ -->
    <?php if (!empty($weekly_chart)): ?>
    <div class="dash-card">
        <div class="dash-card-hd">
            <h3>فعالیت ۷ روز اخیر</h3>
        </div>
        <div class="dash-card-body">
            <div class="dash-chart-wrap">
                <?php
                $max_c = max(1, max(array_column($weekly_chart, 'count')));
                $day_names = ['شنبه','یکشنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنجشنبه','جمعه'];
                ?>
                <div class="dash-chart">
                    <?php foreach ($weekly_chart as $w):
                        $h = round(($w['count'] / $max_c) * 70);
                        $dow_index = (int)date('w', strtotime($w['dt'])); // 0=Sunday
                        $fa_day = $day_names[($dow_index + 1) % 7];
                    ?>
                    <div class="dash-chart-col">
                        <div class="dash-chart-val"><?= $w['count'] > 0 ? $w['count'] : '' ?></div>
                        <div class="dash-chart-bar" style="height:<?= max($h, $w['count']>0 ? 4 : 2) ?>px; opacity:<?= $w['count']>0 ? '.85' : '.25' ?>"></div>
                        <div class="dash-chart-label"><?= mb_substr($fa_day, 0, 2) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ═══ پیگیری‌های آینده ═══ -->
    <?php if (!empty($upcoming_reminders)): ?>
    <div class="dash-card">
        <div class="dash-card-hd">
            <h3>پیگیری‌های آینده</h3>
        </div>
        <div class="dash-card-body">
            <?php foreach ($upcoming_reminders as $r): ?>
            <a href="index.php?page=tasks&action=view&id=<?= $r['id'] ?>" class="dash-reminder">
                <div class="dash-reminder-time" style="background:#E8F0FE;color:var(--blue)"><?= function_exists('jdate') ? jdate($r['next_followup_date'], 'm/d') : date('m/d', strtotime($r['next_followup_date'])) ?></div>
                <div class="dash-reminder-content">
                    <div class="dash-reminder-company"><?= crm_sanitize($r['company_name']) ?></div>
                    <div class="dash-reminder-title"><?= crm_sanitize($r['title']) ?></div>
                </div>
                <span class="dash-reminder-arrow" aria-hidden="true">‹</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ═══ دید کلی فرصت‌های فروشِ باز ═══ -->
    <?php if (!empty($open_tasks_overview)): ?>
    <div class="dash-card">
        <div class="dash-card-hd">
            <h3>دید کلی تماس‌های آینده</h3>
            <span class="count-badge"><?= count($open_tasks_overview) ?></span>
        </div>
        <div class="dash-card-body" style="padding:0">

            <!-- دسکتاپ: جدول -->
            <div class="dash-table-wrap" style="overflow-x:auto">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>مشتری</th>
                            <th>موضوع فرصت فروش</th>
                            <th>مالک</th>
                            <th>پیگیری بعدی</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($open_tasks_overview as $t):
                            $has_date = !empty($t['next_followup_date']);
                            $is_soon  = $has_date && strtotime($t['next_followup_date']) <= strtotime('+1 day');
                        ?>
                        <tr>
                            <td>
                                <a href="index.php?page=customers&action=view&id=<?= $t['customer_id'] ?>">
                                    <strong><?= crm_sanitize($t['company_name']) ?></strong>
                                </a>
                            </td>
                            <td>
                                <a href="index.php?page=tasks&action=view&id=<?= $t['id'] ?>"><?= crm_sanitize($t['title']) ?></a>
                                <?php if (!empty($t['next_followup_topic'])): ?>
                                <div style="font-size:10.5px;color:var(--ink-soft);margin-top:2px"><?= crm_sanitize($t['next_followup_topic']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= crm_sanitize($t['owner_name']) ?></td>
                            <td class="<?= $is_soon ? 'dash-followup-soon' : ($has_date ? '' : 'dash-followup-none') ?>">
                                <?php if ($has_date): ?>
                                    <?= function_exists('jdatetime') ? jdatetime($t['next_followup_date']) : date('Y/m/d H:i', strtotime($t['next_followup_date'])) ?>
                                <?php else: ?>
                                    تعیین نشده
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- موبایل: کارت -->
            <div class="dash-task-cards" style="padding:0 4px 8px">
                <?php foreach ($open_tasks_overview as $t):
                    $has_date = !empty($t['next_followup_date']);
                    $is_soon  = $has_date && strtotime($t['next_followup_date']) <= strtotime('+1 day');
                ?>
                <a href="index.php?page=tasks&action=view&id=<?= $t['id'] ?>" class="dash-task-card">
                    <div class="company"><?= crm_sanitize($t['company_name']) ?></div>
                    <div class="title"><?= crm_sanitize($t['title']) ?></div>
                    <div class="meta-row">
                        <span style="color:var(--ink-soft)"><?= crm_sanitize($t['owner_name']) ?></span>
                        <span class="<?= $is_soon ? 'dash-followup-soon' : ($has_date ? '' : 'dash-followup-none') ?>">
                            <?php if ($has_date): ?>
                                <?= function_exists('jdate') ? jdate($t['next_followup_date'], 'm/d H:i') : date('m/d H:i', strtotime($t['next_followup_date'])) ?>
                            <?php else: ?>
                                تعیین نشده
                            <?php endif; ?>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
    <?php endif; ?>

</div><!-- /dash-more-section -->

<script>
(function(){
    var btn = document.getElementById('dash-more-btn');
    var section = document.getElementById('dash-more-section');
    var label = document.getElementById('dash-more-label');
    btn.addEventListener('click', function(){
        var isOpen = section.classList.toggle('open');
        btn.classList.toggle('open', isOpen);
        label.textContent = isOpen ? 'بستن آمار و گزارش' : 'نمایش آمار و گزارش بیشتر';
    });
})();
</script>

<?php endif; /* subscription_active */ ?>

</div><!-- /dash-wrap -->