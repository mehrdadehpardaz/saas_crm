<!-- Views/tasks/view.php -->

<style>
.tv-wrap {
    direction: rtl;
}

/* alerts */
.tv-alert { padding:11px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
.tv-alert-success { background:#E7F7F3; color:var(--teal-deep); border:1px solid #B8E5DA; }
.tv-alert-error   { background:#FCE8E6; color:var(--danger-deep); border:1px solid #F5C6CB; }
.tv-alert-warning { background:#FFF3DD; color:var(--warning-deep); border:1px solid #F5D78E; }

/* header */
.tv-header { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:16px; flex-wrap:wrap; }
.tv-back-link { display:inline-flex; align-items:center; gap:5px; font-size:12px; color:var(--ink-soft); text-decoration:none; margin-bottom:5px; }
.tv-back-link:hover { color:var(--ember-deep); }
.tv-title { font-size:18px; font-weight:800; color:var(--ink); letter-spacing:-.01em; }
.tv-actions { display:flex; gap:7px; flex-wrap:wrap; flex-shrink:0; }
.tv-btn {
    display:inline-flex; align-items:center; gap:5px; padding:9px 15px; border-radius:9px;
    font-size:12px; font-weight:700; text-decoration:none; border:none; cursor:pointer; transition:all .15s; white-space:nowrap;
}
.tv-btn-complete { background:var(--teal); color:#fff; box-shadow:0 3px 9px rgba(22,160,133,.3); }
.tv-btn-complete:hover { background:var(--teal-deep); transform:translateY(-1px); }
.tv-btn-transfer { background:#FFF3DD; color:var(--warning-deep); border:1.5px solid #F5D78E; }
.tv-btn-transfer:hover { background:#FFE9B8; }
.tv-btn-edit { background:var(--card); color:var(--ink-soft); border:1.5px solid var(--line); }
.tv-btn-edit:hover { border-color:var(--ink); color:var(--ink); }
.tv-btn-activity { background:var(--ember); color:#fff; box-shadow:0 3px 9px rgba(255,107,53,.28); }
.tv-btn-activity:hover { background:var(--ember-deep); transform:translateY(-1px); }

/* status & owner badges */
.tv-badges { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
.tv-badge { display:inline-flex; align-items:center; gap:5px; padding:5px 13px; border-radius:20px; font-size:12px; font-weight:600; }
.tv-badge-active    { background:#E7F7F3; color:var(--teal-deep); }
.tv-badge-completed { background:#E8F0FE; color:var(--blue); }
.tv-badge-sold      { background:#FFF3DD; color:var(--warning-deep); }
.tv-badge-cancelled { background:#FCE8E6; color:var(--danger); }
.tv-badge-owner     { background:var(--paper-2); color:var(--ink-soft); }

/* followup card */
.tv-followup {
    border-radius:13px; padding:16px 17px; margin-bottom:16px;
    display:flex; justify-content:space-between; align-items:center; gap:14px; flex-wrap:wrap;
    border:1.5px solid var(--line);
}
.tv-followup.overdue { border-color:var(--danger); background:#FFF9F8; }
.tv-followup.upcoming { border-color:var(--teal); background:#F0FBF8; }
.tv-followup-label { font-size:12px; font-weight:700; color:var(--ink-soft); margin-bottom:5px; }
.tv-followup-date { font-size:15px; font-weight:800; }
.tv-followup.overdue .tv-followup-date { color:var(--danger); }
.tv-followup.upcoming .tv-followup-date { color:var(--teal-deep); }
.tv-followup-topic { font-size:12.5px; color:var(--ink-soft); margin-top:5px; }
.tv-followup-cta {
    display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:9px;
    font-size:12.5px; font-weight:700; text-decoration:none; background:var(--ember); color:#fff;
    box-shadow:0 3px 9px rgba(255,107,53,.28); transition:all .15s; flex-shrink:0;
}
.tv-followup-cta:hover { background:var(--ember-deep); }

/* ── جدول ساده فعالیت‌ها (جدیدترین بالا، قدیمی‌ترین پایین) ── */
.tv-activity-card { background:var(--card); border:1px solid var(--line); border-radius:14px; overflow:hidden; }
.tv-activity-hd { padding:14px 17px; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center; }
.tv-activity-hd h3 { font-size:14px; font-weight:700; color:var(--ink); }
.tv-activity-count { font-size:12px; color:var(--ink-soft); background:var(--paper-2); padding:3px 11px; border-radius:12px; font-weight:600; }

.tv-empty { text-align:center; padding:28px 17px; color:var(--ink-soft); }
.tv-empty p { font-size:13.5px; margin-bottom:14px; }
.tv-empty a {
    display:inline-flex; align-items:center; gap:6px; padding:10px 20px; border-radius:9px;
    background:var(--ember); color:#fff; text-decoration:none; font-size:13px; font-weight:700;
}

/* جدول — دسکتاپ */
.tv-table-wrap { overflow-x:auto; }
.tv-table { width:100%; border-collapse:collapse; font-size:13px; }
.tv-table th { padding:10px 14px; text-align:right; background:var(--paper-2); color:var(--ink-soft); font-weight:600; font-size:11.5px; border-bottom:1px solid var(--line); white-space:nowrap; }
.tv-table td { padding:12px 14px; border-bottom:1px solid var(--paper-2); color:var(--ink); vertical-align:top; }
.tv-table tr:last-child td { border-bottom:none; }
.tv-table tr:hover td { background:var(--paper-2); }
.tv-table-date { font-size:11.5px; color:var(--ink-soft); white-space:nowrap; }
.tv-type-tag {
    display:inline-flex; align-items:center; padding:3px 10px; border-radius:20px;
    font-size:11px; font-weight:700; white-space:nowrap;
}
.tv-type-call    { background:#E8F0FE; color:var(--blue); }
.tv-type-meeting { background:#FFF3DD; color:var(--warning-deep); }
.tv-type-email   { background:#F3E8FD; color:var(--purple); }
.tv-type-note    { background:var(--paper-2); color:var(--ink-soft); }
.tv-table-desc { font-size:13px; color:var(--ink); line-height:1.6; max-width:340px; }
.tv-table-contact { font-size:11.5px; color:var(--ink-soft); white-space:nowrap; }
.tv-table-agent { font-size:11.5px; color:var(--ink-soft); white-space:nowrap; }
.tv-row-actions { display:flex; gap:5px; white-space:nowrap; }
.tv-row-action-btn {
    width:26px; height:26px; border-radius:7px; display:flex; align-items:center; justify-content:center;
    text-decoration:none; font-size:12px; transition:background .15s; border:none; cursor:pointer; padding:0;
}
.tv-row-edit { background:#E8F0FE; color:var(--blue); }
.tv-row-edit:hover { background:#D5E5FC; }
.tv-row-delete { background:#FCE8E6; color:var(--danger); }
.tv-row-delete:hover { background:#F9D4D0; }

/* کارت — موبایل (به‌جای جدول، تا اسکرول افقی لازم نباشد) */
.tv-activity-cards { display:none; }
.tv-act-card { padding:13px 16px; border-bottom:1px solid var(--paper-2); }
.tv-act-card:last-child { border-bottom:none; }
.tv-act-top { display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap; }
.tv-act-desc { font-size:13px; color:var(--ink); line-height:1.6; margin-bottom:6px; }
.tv-act-foot { display:flex; justify-content:space-between; align-items:center; gap:8px; font-size:11px; color:var(--ink-soft); }

@media (max-width: 680px) {
    .tv-table-wrap { display:none; }
    .tv-activity-cards { display:block; }
}
</style>

<div class="tv-wrap">

<?php if ($message === 'created'): ?>
    <div class="tv-alert tv-alert-success">فرصت با موفقیت ایجاد شد.</div>
<?php elseif ($message === 'completed'): ?>
    <div class="tv-alert tv-alert-success">فرصت تکمیل شد.</div>
<?php elseif ($message === 'sold'): ?>
    <div class="tv-alert tv-alert-success">فرصت با موفقیت منجر به فروش ثبت شد.</div>
<?php elseif ($message === 'cancelled'): ?>
    <div class="tv-alert tv-alert-warning">فرصت کنسل شد.</div>
<?php elseif ($message === 'assigned'): ?>
    <div class="tv-alert tv-alert-success">فرصت با موفقیت به کاربر جدید منتقل شد.</div>
<?php elseif ($message === 'activity_added'): ?>
    <div class="tv-alert tv-alert-success">فعالیت ثبت شد.</div>
<?php elseif ($message === 'activity_updated'): ?>
    <div class="tv-alert tv-alert-success">فعالیت بروزرسانی شد.</div>
<?php endif; ?>

<!-- هدر -->
<div class="tv-header">
    <div>
        <a href="index.php?page=customers&action=view&id=<?= $task['customer_id'] ?>" class="tv-back-link">
            ← <?= crm_sanitize($task['company_name']) ?>
        </a>
        <div class="tv-title">
            <?= crm_sanitize($task['title']) ?>
        </div>
    </div>
    <div class="tv-actions">
        <?php if ($task['status'] === 'active'): ?>
        <a href="index.php?page=tasks&action=complete&id=<?= $task['id'] ?>" class="tv-btn tv-btn-complete">اتمام</a>
        <?php if ($is_manager): ?>
        <a href="index.php?page=tasks&action=assign&id=<?= $task['id'] ?>" class="tv-btn tv-btn-transfer">انتقال</a>
        <?php endif; ?>
        <?php endif; ?>
        <a href="index.php?page=tasks&action=edit&id=<?= $task['id'] ?>" class="tv-btn tv-btn-edit" aria-label="ویرایش فرصت">✏️</a>
        <a href="index.php?page=activities&action=add&task_id=<?= $task['id'] ?>&customer_id=<?= $task['customer_id'] ?>" class="tv-btn tv-btn-activity">+ فعالیت</a>
    </div>
</div>

<!-- badges -->
<div class="tv-badges">
    <?php
    $status_badge = match($task['status']) {
        'active'    => '<span class="tv-badge tv-badge-active">فعال</span>',
        'completed' => '<span class="tv-badge tv-badge-completed">تکمیل شده</span>',
        'sold'      => '<span class="tv-badge tv-badge-sold">منجر به فروش</span>',
        default     => '<span class="tv-badge tv-badge-cancelled">لغو شده</span>',
    };
    echo $status_badge;
    ?>
    <span class="tv-badge tv-badge-owner"><?= crm_sanitize($task['agent_name']) ?></span>
</div>

<!-- پیگیری بعدی -->
<?php if (!empty($task['next_followup_date']) && $task['status'] === 'active'):
    $is_overdue = strtotime($task['next_followup_date']) < time();
    $followup_class = $is_overdue ? 'overdue' : 'upcoming';
?>
<div class="tv-followup <?= $followup_class ?>">
    <div>
        <div class="tv-followup-label">پیگیری بعدی<?= $is_overdue ? ' — تأخیر دارد' : '' ?></div>
        <div class="tv-followup-date">
            <?= function_exists('jdatetime') ? jdatetime($task['next_followup_date']) : date('Y/m/d - H:i', strtotime($task['next_followup_date'])) ?>
        </div>
        <?php if ($task['next_followup_topic']): ?>
        <div class="tv-followup-topic"><?= crm_sanitize($task['next_followup_topic']) ?></div>
        <?php endif; ?>
    </div>
    <a href="index.php?page=activities&action=add&task_id=<?= $task['id'] ?>&customer_id=<?= $task['customer_id'] ?>" class="tv-followup-cta">ثبت فعالیت</a>
</div>
<?php endif; ?>

<!-- جدول فعالیت‌ها — جدیدترین بالا، قدیمی‌ترین پایین -->
<div class="tv-activity-card">
    <div class="tv-activity-hd">
        <h3>فعالیت‌های این فرصت</h3>
        <span class="tv-activity-count"><?= count($activities) ?></span>
    </div>

    <?php if (empty($activities)): ?>
        <div class="tv-empty">
            <p>هنوز فعالیتی برای این فرصت ثبت نشده.</p>
            <a href="index.php?page=activities&action=add&task_id=<?= $task['id'] ?>&customer_id=<?= $task['customer_id'] ?>">ثبت اولین فعالیت</a>
        </div>
    <?php else: ?>

        <?php
        $type_config = [
            'call'    => ['tv-type-call', 'تماس'],
            'meeting' => ['tv-type-meeting', 'جلسه'],
            'email'   => ['tv-type-email', 'ایمیل'],
            'note'    => ['tv-type-note', 'یادداشت'],
        ];
        ?>

        <!-- دسکتاپ: جدول ساده -->
        <div class="tv-table-wrap">
            <table class="tv-table">
                <thead>
                    <tr>
                        <th>تاریخ</th>
                        <th>نوع</th>
                        <th>شرح</th>
                        <th>مخاطب</th>
                        <th>ثبت‌کننده</th>
                        <th><span class="sr-only">عملیات</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $a):
                        [$type_class, $type_label] = $type_config[$a['type']] ?? $type_config['note'];
                    ?>
                    <tr>
                        <td class="tv-table-date"><?= function_exists('jdatetime') ? jdatetime($a['created_at']) : date('Y/m/d H:i', strtotime($a['created_at'])) ?></td>
                        <td><span class="tv-type-tag <?= $type_class ?>"><?= $type_label ?></span></td>
                        <td class="tv-table-desc"><?= $a['description'] ? nl2br(crm_sanitize($a['description'])) : '—' ?></td>
                        <td class="tv-table-contact"><?= $a['contact_name'] ? crm_sanitize($a['contact_name']) : '—' ?></td>
                        <td class="tv-table-agent"><?= crm_sanitize($a['agent_name']) ?></td>
                        <td>
                            <div class="tv-row-actions">
                                <a href="index.php?page=activities&action=edit&id=<?= $a['id'] ?>&task_id=<?= $task['id'] ?>" class="tv-row-action-btn tv-row-edit" aria-label="ویرایش فعالیت">✏️</a>
                                <form method="POST" action="index.php?page=activities&action=delete&id=<?= $a['id'] ?>&task_id=<?= $task['id'] ?>"
                                      onsubmit="return confirm('حذف شود؟')" style="display:inline">
                                    <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
                                    <button type="submit" class="tv-row-action-btn tv-row-delete" aria-label="حذف فعالیت">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- موبایل: کارت ساده (بدون اسکرول افقی) -->
        <div class="tv-activity-cards">
            <?php foreach ($activities as $a):
                [$type_class, $type_label] = $type_config[$a['type']] ?? $type_config['note'];
            ?>
            <div class="tv-act-card">
                <div class="tv-act-top">
                    <span class="tv-type-tag <?= $type_class ?>"><?= $type_label ?></span>
                    <span class="tv-table-date"><?= function_exists('jdatetime') ? jdatetime($a['created_at']) : date('Y/m/d H:i', strtotime($a['created_at'])) ?></span>
                </div>
                <?php if ($a['description']): ?>
                <div class="tv-act-desc"><?= nl2br(crm_sanitize($a['description'])) ?></div>
                <?php endif; ?>
                <div class="tv-act-foot">
                    <span><?= $a['contact_name'] ? crm_sanitize($a['contact_name']) . ' — ' : '' ?><?= crm_sanitize($a['agent_name']) ?></span>
                    <div class="tv-row-actions">
                        <a href="index.php?page=activities&action=edit&id=<?= $a['id'] ?>&task_id=<?= $task['id'] ?>" class="tv-row-action-btn tv-row-edit" aria-label="ویرایش فعالیت">✏️</a>
                        <form method="POST" action="index.php?page=activities&action=delete&id=<?= $a['id'] ?>&task_id=<?= $task['id'] ?>"
                              onsubmit="return confirm('حذف شود؟')" style="display:inline">
                            <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
                            <button type="submit" class="tv-row-action-btn tv-row-delete" aria-label="حذف فعالیت">🗑️</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

</div><!-- /tv-wrap -->