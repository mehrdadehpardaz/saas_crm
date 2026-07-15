<!-- Views/support/list.php -->
<style>
.sp-wrap { direction: rtl; }

.sp-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.sp-header h2 { font-size:18px; font-weight:800; color:var(--ink); letter-spacing:-.01em; }
.sp-btn-add {
    display:inline-flex; align-items:center; gap:6px; padding:10px 18px; border-radius:10px;
    font-size:13px; font-weight:700; text-decoration:none; background:var(--ember); color:#fff;
    box-shadow:0 3px 10px rgba(255,107,53,.28); transition:all .15s;
}
.sp-btn-add:hover { background:var(--ember-deep); transform:translateY(-1px); }

.sp-alert { padding:11px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; background:#E7F7F3; color:var(--teal-deep); border:1px solid #B8E5DA; }

/* فیلتر وضعیت (سوپر ادمین) */
.sp-filter { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
.sp-filter a {
    padding:7px 16px; border-radius:20px; font-size:12.5px; font-weight:700; text-decoration:none;
    border:1.5px solid var(--line); color:var(--ink-soft); transition:all .15s;
}
.sp-filter a:hover { border-color:var(--ink); color:var(--ink); }
.sp-filter a.active { background:var(--ink); border-color:var(--ink); color:#fff; }

/* empty state */
.sp-empty { text-align:center; padding:50px 20px; background:var(--card); border:1px solid var(--line); border-radius:14px; }
.sp-empty p { color:var(--ink-soft); font-size:14px; margin-bottom:18px; }
.sp-empty a { display:inline-flex; align-items:center; gap:6px; padding:10px 22px; border-radius:10px; background:var(--ember); color:#fff; text-decoration:none; font-weight:700; font-size:13px; }

/* جدول دسکتاپ */
.sp-table-card { background:var(--card); border:1px solid var(--line); border-radius:14px; overflow:hidden; }
.sp-table-wrap { overflow-x:auto; }
.sp-table { width:100%; border-collapse:collapse; font-size:13px; }
.sp-table th { padding:11px 14px; text-align:right; background:var(--paper-2); color:var(--ink-soft); font-weight:700; font-size:11.5px; border-bottom:1px solid var(--line); white-space:nowrap; }
.sp-table td { padding:11px 14px; border-bottom:1px solid var(--paper-2); color:var(--ink); vertical-align:middle; }
.sp-table tbody tr { cursor:pointer; transition:background .12s; }
.sp-table tbody tr:hover { background:var(--paper-2); }
.sp-table tbody tr:last-child td { border-bottom:none; }
.sp-user-name { font-weight:700; color:var(--ink); }
.sp-user-meta { font-size:11px; color:var(--ink-soft); margin-top:2px; }
.sp-subject { font-weight:600; }

.sp-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap; }
.sp-badge-open        { background:#FCE8E6; color:var(--danger); }
.sp-badge-in_progress  { background:#FFF3DD; color:var(--warning-deep); }
.sp-badge-closed      { background:#E7F7F3; color:var(--teal-deep); }

.sp-dot { display:inline-block; width:9px; height:9px; border-radius:50%; background:var(--ember); flex-shrink:0; }
.sp-row-unread { background:#FFF8F4; }
.sp-row-unread:hover { background:#FFF1E6 !important; }
.sp-new-tag { display:inline-block; font-size:10px; font-weight:800; color:#fff; background:var(--ember); padding:1px 7px; border-radius:8px; margin-right:5px; vertical-align:middle; }

/* موبایل: کارت */
.sp-cards { display:none; }
.sp-card {
    display:block; background:var(--card); border:1px solid var(--line); border-radius:13px;
    padding:14px 16px; margin-bottom:10px; text-decoration:none; color:inherit;
}
.sp-card-top { display:flex; justify-content:space-between; align-items:flex-start; gap:8px; margin-bottom:6px; }
.sp-card-date { font-size:11px; color:var(--ink-soft); margin-top:6px; }

@media (max-width: 760px) {
    .sp-table-card { display:none; }
    .sp-cards { display:block; }
}
</style>

<div class="sp-wrap">

<div class="sp-header">
    <h2>🎫 پشتیبانی<?= $is_super ? ' — همه‌ی تیکت‌ها' : '' ?></h2>
    <a href="index.php?page=support&action=add" class="sp-btn-add">+ تیکت جدید</a>
</div>

<?php if (($message ?? '') === 'created'): ?>
    <div class="sp-alert">تیکت شما با موفقیت ثبت شد. به‌زودی پاسخ داده می‌شود.</div>
<?php elseif (($message ?? '') === 'replied'): ?>
    <div class="sp-alert">پاسخ با موفقیت ثبت شد.</div>
<?php endif; ?>

<?php if ($is_super): ?>
<div class="sp-filter">
    <a href="index.php?page=support" class="<?= empty($_GET['status']) ? 'active' : '' ?>">همه</a>
    <a href="index.php?page=support&status=open" class="<?= ($_GET['status'] ?? '') === 'open' ? 'active' : '' ?>">🔴 باز</a>
    <a href="index.php?page=support&status=in_progress" class="<?= ($_GET['status'] ?? '') === 'in_progress' ? 'active' : '' ?>">🟡 در حال بررسی</a>
    <a href="index.php?page=support&status=closed" class="<?= ($_GET['status'] ?? '') === 'closed' ? 'active' : '' ?>">🟢 بسته‌شده</a>
</div>
<?php endif; ?>

<?php
$status_labels = ['open' => '🔴 باز', 'in_progress' => '🟡 در حال بررسی', 'closed' => '🟢 بسته‌شده'];
$role_labels = ['super_admin'=>'سوپر ادمین','admin'=>'مدیر','manager'=>'مدیر فروش','agent'=>'کارشناس'];
?>

<?php if (empty($tickets)): ?>
    <div class="sp-empty">
        <p>هنوز تیکتی ثبت نشده.</p>
        <a href="index.php?page=support&action=add">+ ثبت اولین تیکت</a>
    </div>
<?php else: ?>

    <!-- دسکتاپ: جدول -->
    <div class="sp-table-card">
        <div class="sp-table-wrap">
            <table class="sp-table">
                <thead>
                    <tr>
                        <th style="width:14px"><span class="sr-only">وضعیت خواندن</span></th>
                        <?php if ($is_super): ?><th>کاربر</th><?php endif; ?>
                        <th>موضوع</th>
                        <th>وضعیت</th>
                        <th>آخرین پیام</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $t):
                        $is_unread = $is_super ? !empty($t['unread_by_admin']) : !empty($t['unread_by_user']);
                    ?>
                    <tr onclick="window.location='index.php?page=support&action=view&id=<?= $t['id'] ?>'" class="<?= $is_unread ? 'sp-row-unread' : '' ?>">
                        <td><?php if ($is_unread): ?><span class="sp-dot" aria-label="پیام خوانده‌نشده"></span><?php endif; ?></td>
                        <?php if ($is_super): ?>
                        <td>
                            <div class="sp-user-name"><?= crm_sanitize($t['full_name']) ?></div>
                            <div class="sp-user-meta">📱 <?= crm_sanitize($t['mobile']) ?> — <?= crm_sanitize($role_labels[$t['role']] ?? $t['role']) ?><?= !empty($t['company_name']) ? ' — ' . crm_sanitize($t['company_name']) : '' ?></div>
                        </td>
                        <?php endif; ?>
                        <td class="sp-subject"><?= crm_sanitize($t['subject']) ?><?= $is_unread ? ' <span class="sp-new-tag">جدید</span>' : '' ?></td>
                        <td><span class="sp-badge sp-badge-<?= $t['status'] ?>"><?= $status_labels[$t['status']] ?? $t['status'] ?></span></td>
                        <td style="font-size:11.5px;color:var(--ink-soft);white-space:nowrap"><?= function_exists('jdatetime') ? jdatetime($t['updated_at']) : date('Y/m/d H:i', strtotime($t['updated_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- موبایل: کارت -->
    <div class="sp-cards">
        <?php foreach ($tickets as $t):
            $is_unread = $is_super ? !empty($t['unread_by_admin']) : !empty($t['unread_by_user']);
        ?>
        <a href="index.php?page=support&action=view&id=<?= $t['id'] ?>" class="sp-card <?= $is_unread ? 'sp-row-unread' : '' ?>">
            <div class="sp-card-top">
                <span class="sp-subject"><?php if ($is_unread): ?><span class="sp-dot" aria-label="پیام خوانده‌نشده"></span><?php endif; ?> <?= crm_sanitize($t['subject']) ?></span>
                <span class="sp-badge sp-badge-<?= $t['status'] ?>"><?= $status_labels[$t['status']] ?? $t['status'] ?></span>
            </div>
            <?php if ($is_super): ?>
            <div class="sp-user-meta"><?= crm_sanitize($t['full_name']) ?> — 📱 <?= crm_sanitize($t['mobile']) ?></div>
            <?php endif; ?>
            <div class="sp-card-date"><?= function_exists('jdatetime') ? jdatetime($t['updated_at']) : date('Y/m/d H:i', strtotime($t['updated_at'])) ?></div>
        </a>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

</div><!-- /sp-wrap -->