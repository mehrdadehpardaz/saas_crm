<!-- Views/customers/view.php -->
<?php
$user = crm_get_current_user();
$message = $_GET['msg'] ?? '';
$is_super = ($user['role'] === 'super_admin');
$is_admin = in_array($user['role'], ['super_admin', 'admin']);
$is_manager = in_array($user['role'], ['super_admin', 'admin', 'manager']);
?>

<style>
.cv-wrap {
    direction: rtl;
}

/* ── Header ── */
.cv-header { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:18px; flex-wrap:wrap; }
.cv-header h2 { font-size:18px; font-weight:800; color:var(--ink); letter-spacing:-.01em; }
.cv-actions { display:flex; gap:8px; flex-wrap:wrap; }
.cv-btn {
    display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:9px;
    font-size:12.5px; font-weight:700; text-decoration:none; border:1.5px solid transparent;
    transition:all .15s; cursor:pointer; white-space:nowrap;
}
.cv-btn-primary { background:var(--ember); color:#fff; box-shadow:0 3px 10px rgba(255,107,53,.28); }
.cv-btn-primary:hover { background:var(--ember-deep); transform:translateY(-1px); }
.cv-btn-outline { background:var(--card); color:var(--ink-soft); border-color:var(--line); }
.cv-btn-outline:hover { border-color:var(--ink); color:var(--ink); }
.cv-btn-sm { padding:5px 11px; font-size:11px; border-radius:7px; }

/* ── Alerts ── */
.cv-alert { padding:11px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; }
.cv-alert-success { background:#E7F7F3; color:var(--teal-deep); border:1px solid #B8E5DA; }

/* ── Info card: جدول کلید/مقدار ساده ── */
.cv-card { background:var(--card); border:1px solid var(--line); border-radius:14px; overflow:hidden; margin-bottom:16px; }
.cv-card-hd { padding:13px 16px; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center; }
.cv-card-hd h3 { font-size:14px; font-weight:700; color:var(--ink); }
.cv-count-badge { background:var(--paper-2); color:var(--ink-soft); font-size:11px; font-weight:700; padding:2px 9px; border-radius:11px; }

.cv-info-table { width:100%; border-collapse:collapse; font-size:13px; }
.cv-info-table tr { border-bottom:1px solid var(--paper-2); }
.cv-info-table tr:last-child { border-bottom:none; }
.cv-info-table th {
    text-align:right; padding:10px 16px; color:var(--ink-soft); font-weight:600; font-size:12px;
    width:120px; white-space:nowrap; vertical-align:top; background:var(--paper-2);
}
.cv-info-table td { padding:10px 16px; color:var(--ink); font-weight:500; }
.cv-tel-link { color:var(--blue); text-decoration:none; direction:ltr; display:inline-flex; align-items:center; gap:5px; }
.cv-tel-link:hover { text-decoration:underline; }
.cv-org-tag { background:#F3E8FD; color:#9c27b0; padding:2px 9px; border-radius:10px; font-size:11px; font-weight:600; white-space:nowrap; margin-right:8px; }

/* ── جدول مخاطبین (مثل اکسل) ── */
.cv-table-wrap { overflow-x:auto; }
.cv-table { width:100%; border-collapse:collapse; font-size:13px; }
.cv-table th { padding:9px 14px; text-align:right; background:var(--paper-2); color:var(--ink-soft); font-weight:700; font-size:11px; border-bottom:1px solid var(--line); white-space:nowrap; }
.cv-table td { padding:10px 14px; border-bottom:1px solid var(--paper-2); color:var(--ink); vertical-align:middle; }
.cv-table tbody tr:last-child td { border-bottom:none; }
.cv-table tbody tr:nth-child(even) { background:#FCFBF9; }
.cv-table tbody tr:hover td { background:var(--paper-2); }
.cv-primary-badge { background:#FFF3DD; color:var(--warning-deep); padding:2px 9px; border-radius:10px; font-size:10px; font-weight:700; white-space:nowrap; }
.cv-row-actions { display:flex; gap:5px; white-space:nowrap; }
.cv-row-action-btn { width:26px; height:26px; border-radius:7px; display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:12px; transition:background .15s; border:none; cursor:pointer; padding:0; }
.cv-action-edit { background:#E8F0FE; color:var(--blue); }
.cv-action-edit:hover { background:#D5E5FC; }
.cv-action-delete { background:#FCE8E6; color:var(--danger); }
.cv-action-delete:hover { background:#F9D4D0; }

.cv-empty { padding:24px 16px; text-align:center; color:var(--ink-soft); font-size:13px; }
.cv-empty a { color:var(--ember-deep); font-weight:700; text-decoration:none; }

/* status pill برای تسک‌ها */
.cv-status-pill { font-size:10.5px; font-weight:700; padding:2px 9px; border-radius:10px; display:inline-flex; align-items:center; white-space:nowrap; }
.cv-status-active { background:#E7F7F3; color:var(--teal-deep); }
.cv-status-completed { background:#E8F0FE; color:var(--blue); }
.cv-status-sold { background:#FFF3DD; color:var(--warning-deep); }
.cv-status-cancelled { background:#FCE8E6; color:var(--danger); }
.cv-locked { color:var(--ink-soft); }
.cv-table-row-link { text-decoration:none; color:inherit; display:contents; }

/* موبایل: مخاطبین و تسک‌ها به کارت تبدیل می‌شوند تا اسکرول افقی نداشته باشند */
.cv-mobile-cards { display:none; }
.cv-m-card { padding:12px 16px; border-bottom:1px solid var(--paper-2); }
.cv-m-card:last-child { border-bottom:none; }
.cv-m-top { display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:5px; flex-wrap:wrap; }
.cv-m-name { font-weight:700; color:var(--ink); font-size:13.5px; }
.cv-m-meta { display:flex; flex-wrap:wrap; gap:5px 10px; font-size:11.5px; color:var(--ink-soft); }

@media (max-width: 700px) {
    .cv-table-wrap { display:none; }
    .cv-mobile-cards { display:block; }
}
</style>

<div class="cv-wrap">

<!-- ═══ هدر ═══ -->
<div class="cv-header">
    <h2><?= crm_sanitize($customer['company_name']) ?></h2>
    <div class="cv-actions">
        <a href="index.php?page=tasks&action=add&customer_id=<?= $customer['id'] ?>" class="cv-btn cv-btn-primary">+ تسک جدید</a>
        <a href="index.php?page=customers&action=edit&id=<?= $customer['id'] ?>" class="cv-btn cv-btn-outline">ویرایش</a>
        <a href="index.php?page=customers" class="cv-btn cv-btn-outline">بازگشت</a>
    </div>
</div>

<?php if ($message === 'created'): ?>
    <div class="cv-alert cv-alert-success">مشتری با موفقیت ثبت شد.</div>
<?php elseif ($message === 'updated'): ?>
    <div class="cv-alert cv-alert-success">اطلاعات مشتری بروزرسانی شد.</div>
<?php elseif ($message === 'contact_added'): ?>
    <div class="cv-alert cv-alert-success">مخاطب با موفقیت اضافه شد.</div>
<?php elseif ($message === 'contact_updated'): ?>
    <div class="cv-alert cv-alert-success">مخاطب بروزرسانی شد.</div>
<?php elseif ($message === 'contact_deleted'): ?>
    <div class="cv-alert cv-alert-success">مخاطب حذف شد.</div>
<?php endif; ?>

<!-- ═══ اطلاعات مشتری — جدول کلید/مقدار ═══ -->
<div class="cv-card">
    <div class="cv-card-hd"><h3>اطلاعات مشتری</h3></div>
    <table class="cv-info-table">
        <?php if ($is_super): ?>
        <tr>
            <th>سازنده</th>
            <td>
                <?= crm_sanitize($customer['agent_name'] ?? '—') ?>
                <span class="cv-org-tag"><?= crm_sanitize($customer['company_label'] ?? '—') ?></span>
            </td>
        </tr>
        <?php endif; ?>
        <tr><th>صنعت</th><td><?= crm_sanitize($customer['industry_title'] ?? 'ثبت نشده') ?></td></tr>
        <tr><th>شخص اصلی</th><td><?= crm_sanitize($customer['contact_person'] ?? 'ندارد') ?></td></tr>
        <tr>
            <th>تلفن</th>
            <td>
                <?php if (!empty($customer['phone'])): ?>
                    <a href="tel:<?= crm_sanitize($customer['phone']) ?>" class="cv-tel-link">📞 <?= crm_sanitize($customer['phone']) ?></a>
                <?php else: ?>
                    ندارد
                <?php endif; ?>
            </td>
        </tr>
        <tr><th>ایمیل</th><td><?= crm_sanitize($customer['email'] ?? 'ندارد') ?></td></tr>
        <tr><th>یادداشت</th><td><?= nl2br(crm_sanitize($customer['notes'] ?? 'ندارد')) ?></td></tr>
    </table>
</div>

<!-- ═══ مخاطبین — جدول ═══ -->
<div class="cv-card">
    <div class="cv-card-hd">
        <h3>مخاطبین <span class="cv-count-badge"><?= count($contacts) ?></span></h3>
        <a href="index.php?page=contacts&action=add&customer_id=<?= $customer['id'] ?>" class="cv-btn cv-btn-primary cv-btn-sm">+ مخاطب جدید</a>
    </div>

    <?php
    $can_edit_contact   = ($is_super || $is_admin || $user['role'] === 'manager' || $customer['user_id'] == $user['id']);
    $can_delete_contact = ($is_super || $is_admin);
    ?>

    <?php if (empty($contacts)): ?>
        <div class="cv-empty">
            هنوز مخاطبی ثبت نشده.
            <a href="index.php?page=contacts&action=add&customer_id=<?= $customer['id'] ?>">افزودن مخاطب</a>
        </div>
    <?php else: ?>

        <!-- دسکتاپ: جدول -->
        <div class="cv-table-wrap">
            <table class="cv-table">
                <thead>
                    <tr>
                        <th>نام</th>
                        <th>سمت</th>
                        <th>تلفن</th>
                        <th>ایمیل</th>
                        <th style="text-align:center">اصلی</th>
                        <th><span class="sr-only">عملیات</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $co): ?>
                    <tr>
                        <td style="font-weight:700"><?= crm_sanitize($co['full_name']) ?></td>
                        <td><?= $co['position'] ? crm_sanitize($co['position']) : '—' ?></td>
                        <td>
                            <?php if ($co['phone']): ?>
                                <a href="tel:<?= crm_sanitize($co['phone']) ?>" class="cv-tel-link">📞 <?= crm_sanitize($co['phone']) ?></a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= $co['email'] ? crm_sanitize($co['email']) : '—' ?></td>
                        <td style="text-align:center"><?= $co['is_primary'] ? '<span class="cv-primary-badge">اصلی</span>' : '—' ?></td>
                        <td>
                            <div class="cv-row-actions">
                                <?php if ($can_edit_contact): ?>
                                <a href="index.php?page=contacts&action=edit&id=<?= $co['id'] ?>" class="cv-row-action-btn cv-action-edit" aria-label="ویرایش مخاطب">✏️</a>
                                <?php endif; ?>
                                <?php if ($can_delete_contact): ?>
                                <form method="POST" action="index.php?page=contacts&action=delete&id=<?= $co['id'] ?>"
                                      onsubmit="return confirm('آیا از حذف این مخاطب مطمئن هستید؟')" style="display:inline">
                                    <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
                                    <button type="submit" class="cv-row-action-btn cv-action-delete" aria-label="حذف مخاطب">🗑️</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- موبایل: کارت فشرده -->
        <div class="cv-mobile-cards">
            <?php foreach ($contacts as $co): ?>
            <div class="cv-m-card">
                <div class="cv-m-top">
                    <span class="cv-m-name"><?= crm_sanitize($co['full_name']) ?></span>
                    <?php if ($co['is_primary']): ?><span class="cv-primary-badge">اصلی</span><?php endif; ?>
                </div>
                <div class="cv-m-meta">
                    <?php if ($co['position']): ?><span><?= crm_sanitize($co['position']) ?></span><?php endif; ?>
                    <?php if ($co['phone']): ?><a href="tel:<?= crm_sanitize($co['phone']) ?>" class="cv-tel-link">📞 <?= crm_sanitize($co['phone']) ?></a><?php endif; ?>
                    <?php if ($co['email']): ?><span><?= crm_sanitize($co['email']) ?></span><?php endif; ?>
                </div>
                <div class="cv-row-actions" style="margin-top:8px">
                    <?php if ($can_edit_contact): ?>
                    <a href="index.php?page=contacts&action=edit&id=<?= $co['id'] ?>" class="cv-row-action-btn cv-action-edit" aria-label="ویرایش مخاطب">✏️</a>
                    <?php endif; ?>
                    <?php if ($can_delete_contact): ?>
                    <form method="POST" action="index.php?page=contacts&action=delete&id=<?= $co['id'] ?>"
                          onsubmit="return confirm('آیا از حذف این مخاطب مطمئن هستید؟')" style="display:inline">
                        <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
                        <button type="submit" class="cv-row-action-btn cv-action-delete" aria-label="حذف مخاطب">🗑️</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

<!-- ═══ تسک‌ها — جدول ═══ -->
<div class="cv-card">
    <div class="cv-card-hd">
        <h3>تسک‌ها <span class="cv-count-badge"><?= count($tasks) ?></span></h3>
        <a href="index.php?page=tasks&action=add&customer_id=<?= $customer['id'] ?>" class="cv-btn cv-btn-primary cv-btn-sm">+ تسک جدید</a>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="cv-empty">هنوز تسکی برای این مشتری تعریف نشده.</div>
    <?php else: ?>
        <?php
        $status_map = [
            'active'    => ['cv-status-active', 'فعال'],
            'completed' => ['cv-status-completed', 'تکمیل'],
            'sold'      => ['cv-status-sold', 'فروش'],
            'cancelled' => ['cv-status-cancelled', 'لغو'],
        ];
        // پیش‌محاسبه دسترسی هر تسک (یک‌بار، نه داخل دو حلقه جدا)
        $task_access = [];
        foreach ($tasks as $t) {
            $can_view_task = ($t['user_id'] == $user['id']);
            if (!$can_view_task && $is_manager) {
                $pdo = getDB();
                $current_id = $t['user_id'];
                for ($i = 0; $i < 5; $i++) {
                    $stmt = $pdo->prepare("SELECT parent_id FROM users WHERE id = ?");
                    $stmt->execute([$current_id]);
                    $p = $stmt->fetch();
                    if (!$p || !$p['parent_id']) break;
                    if ($p['parent_id'] == $user['id']) { $can_view_task = true; break; }
                    $current_id = $p['parent_id'];
                }
            }
            $task_access[$t['id']] = $can_view_task;
        }
        ?>

        <!-- دسکتاپ: جدول -->
        <div class="cv-table-wrap">
            <table class="cv-table">
                <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>وضعیت</th>
                        <th>مسئول</th>
                        <th>پیگیری بعدی</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $t):
                        $can_view_task = $task_access[$t['id']];
                        [$status_class, $status_label] = $status_map[$t['status']] ?? ['cv-status-active', $t['status']];
                        $is_overdue = $can_view_task && !empty($t['next_followup_date']) && strtotime($t['next_followup_date']) < time();
                    ?>
                    <tr<?= $can_view_task ? " onclick=\"window.location='index.php?page=tasks&action=view&id={$t['id']}'\" style=\"cursor:pointer\"" : '' ?>>
                        <td style="font-weight:700"><?= crm_sanitize($t['title']) ?></td>
                        <td><span class="cv-status-pill <?= $status_class ?>"><?= $status_label ?></span></td>
                        <td class="<?= $can_view_task ? '' : 'cv-locked' ?>"><?= crm_sanitize($t['agent_name']) ?></td>
                        <td>
                            <?php if (!$can_view_task): ?>
                                <span class="cv-locked">بدون دسترسی</span>
                            <?php elseif (!empty($t['next_followup_date'])): ?>
                                <span style="color:<?= $is_overdue ? 'var(--danger)' : 'var(--ink-soft)' ?>">
                                    <?= function_exists('jdate') ? jdate($t['next_followup_date']) : date('Y/m/d', strtotime($t['next_followup_date'])) ?>
                                </span>
                            <?php else: ?>
                                <span class="cv-locked">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- موبایل: کارت فشرده -->
        <div class="cv-mobile-cards">
            <?php foreach ($tasks as $t):
                $can_view_task = $task_access[$t['id']];
                [$status_class, $status_label] = $status_map[$t['status']] ?? ['cv-status-active', $t['status']];
                $is_overdue = $can_view_task && !empty($t['next_followup_date']) && strtotime($t['next_followup_date']) < time();
                $tag = $can_view_task ? 'a' : 'div';
            ?>
            <<?= $tag ?> <?= $can_view_task ? 'href="index.php?page=tasks&action=view&id='.$t['id'].'"' : '' ?> class="cv-m-card" style="display:block;text-decoration:none;color:inherit">
                <div class="cv-m-top">
                    <span class="cv-m-name"><?= crm_sanitize($t['title']) ?></span>
                    <span class="cv-status-pill <?= $status_class ?>"><?= $status_label ?></span>
                </div>
                <div class="cv-m-meta">
                    <span class="<?= $can_view_task ? '' : 'cv-locked' ?>"><?= crm_sanitize($t['agent_name']) ?></span>
                    <?php if ($can_view_task && !empty($t['next_followup_date'])): ?>
                    <span style="color:<?= $is_overdue ? 'var(--danger)' : 'var(--ink-soft)' ?>">
                        <?= function_exists('jdate') ? jdate($t['next_followup_date']) : date('Y/m/d', strtotime($t['next_followup_date'])) ?>
                    </span>
                    <?php elseif (!$can_view_task): ?>
                    <span class="cv-locked">بدون دسترسی</span>
                    <?php endif; ?>
                </div>
            </<?= $tag ?>>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

</div><!-- /cv-wrap -->