<!-- Views/tasks/list_all.php -->

<style>
.tla-wrap {
    direction: rtl;
}

.tla-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.tla-header h2 { font-size:18px; font-weight:800; color:var(--ink); letter-spacing:-.01em; }
.tla-btn-add {
    display:inline-flex; align-items:center; gap:6px; padding:10px 18px; border-radius:10px;
    font-size:13px; font-weight:700; text-decoration:none; background:var(--ember); color:#fff;
    box-shadow:0 3px 10px rgba(255,107,53,.28); transition:all .15s;
}
.tla-btn-add:hover { background:var(--ember-deep); transform:translateY(-1px); }

/* فیلتر */
.tla-filter {
    background:var(--card); border:1px solid var(--line); border-radius:13px;
    padding:14px 16px; margin-bottom:18px; display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;
}
.tla-filter-group { flex:1; min-width:120px; }
.tla-filter-group label { font-size:11px; font-weight:600; color:var(--ink-soft); display:block; margin-bottom:5px; }
.tla-filter-group select,
.tla-filter-group input[type=date] {
    width:100%; padding:8px 12px; border:1.5px solid var(--line); border-radius:9px;
    font-size:12.5px; font-family:inherit; background:var(--paper); color:var(--ink);
    transition:border-color .15s;
}
.tla-filter-group select:focus,
.tla-filter-group input:focus { outline:none; border-color:var(--ember); background:#fff; }
.tla-clear-btn {
    display:inline-flex; align-items:center; gap:5px; padding:8px 14px; border-radius:9px;
    background:var(--paper-2); color:var(--ink-soft); border:1.5px solid var(--line);
    text-decoration:none; font-size:12px; font-weight:600; white-space:nowrap; align-self:flex-end;
    transition:all .15s;
}
.tla-clear-btn:hover { border-color:var(--ink); color:var(--ink); }

/* empty state */
.tla-empty { text-align:center; padding:50px 20px; background:var(--card); border:1px solid var(--line); border-radius:14px; }
.tla-empty p { color:var(--ink-soft); font-size:14px; }

/* جدول */
.tla-table-card { background:var(--card); border:1px solid var(--line); border-radius:14px; overflow:hidden; overflow-x:auto; }
.tla-table { width:100%; border-collapse:collapse; font-size:12.5px; }
.tla-table th { padding:11px 10px; text-align:right; background:var(--paper-2); color:var(--ink-soft); font-weight:600; font-size:11px; border-bottom:1px solid var(--line); white-space:nowrap; }
.tla-table td { padding:11px 10px; border-bottom:1px solid var(--paper-2); color:var(--ink); vertical-align:middle; }
.tla-table tr:last-child td { border:none; }
.tla-table tr.clickable { cursor:pointer; transition:background .15s; }
.tla-table tr.clickable:hover td { background:var(--paper-2); }
.tla-table tr.dim td { opacity:.6; }
.tla-table tr.very-dim td { opacity:.4; }

.tla-task-title { font-weight:700; color:var(--ink); font-size:13px; }
.tla-task-topic { font-size:10.5px; color:var(--ink-soft); margin-top:3px; }

/* priority dot — نگه داشته شده چون اطلاعات وضعیت واقعی منتقل می‌کند */
.tla-dot { display:inline-block; width:10px; height:10px; border-radius:50%; flex-shrink:0; }

/* status badge */
.tla-badge { display:inline-flex; align-items:center; gap:3px; padding:3px 9px; border-radius:10px; font-size:10.5px; font-weight:700; white-space:nowrap; }
.tla-badge-active    { background:#E7F7F3; color:var(--teal-deep); }
.tla-badge-completed { background:#E8F0FE; color:var(--blue); }
.tla-badge-sold      { background:#FFF3DD; color:var(--warning-deep); }
.tla-badge-cancelled { background:#FCE8E6; color:var(--danger); }
.tla-badge-org        { background:#F3E8FD; color:#9c27b0; }

/* footer summary */
.tla-footer { display:flex; justify-content:space-between; align-items:center; margin-top:13px; font-size:11.5px; color:var(--ink-soft); flex-wrap:wrap; gap:6px; }
.tla-summary { display:flex; gap:12px; flex-wrap:wrap; }
.tla-summary-item { display:flex; align-items:center; gap:5px; }
</style>

<div class="tla-wrap">

<div class="tla-header">
    <h2>همه تسک‌ها</h2>
    <a href="index.php?page=tasks&action=add" class="tla-btn-add">+ تسک جدید</a>
</div>

<?php if (($_GET['filter_status'] ?? '') === 'overdue'): ?>
<div style="padding:11px 16px;border-radius:10px;font-size:13px;margin-bottom:16px;background:#FCE8E6;color:var(--danger-deep);border:1px solid #F5C6CB">
    فقط تسک‌های فعالی که از تاریخ پیگیری‌شان گذشته نشان داده می‌شوند.
</div>
<?php endif; ?>

<form method="GET" action="index.php" id="filterForm" aria-label="فیلتر تسک‌ها">
    <input type="hidden" name="page" value="tasks">
    <input type="hidden" name="action" value="list_all">

    <div class="tla-filter">
        <div class="tla-filter-group">
            <label for="tla-status">وضعیت</label>
            <select name="filter_status" id="tla-status" onchange="document.getElementById('filterForm').submit()">
                <option value="active"    <?= ($_GET['filter_status'] ?? 'active') === 'active'    ? 'selected' : '' ?>>فعال</option>
                <option value="overdue"   <?= ($_GET['filter_status'] ?? '') === 'overdue'   ? 'selected' : '' ?>>تأخیردار</option>
                <option value="completed" <?= ($_GET['filter_status'] ?? '') === 'completed' ? 'selected' : '' ?>>تکمیل شده</option>
                <option value="cancelled" <?= ($_GET['filter_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>لغو شده</option>
                <option value="sold"      <?= ($_GET['filter_status'] ?? '') === 'sold'      ? 'selected' : '' ?>>منجر به فروش</option>
                <option value="all"       <?= ($_GET['filter_status'] ?? '') === 'all'       ? 'selected' : '' ?>>همه</option>
            </select>
        </div>

        <div class="tla-filter-group">
            <label for="tla-from">از تاریخ</label>
            <input type="date" name="date_from" id="tla-from" value="<?= crm_sanitize($_GET['date_from'] ?? '') ?>"
                   onchange="document.getElementById('filterForm').submit()">
        </div>

        <div class="tla-filter-group">
            <label for="tla-to">تا تاریخ</label>
            <input type="date" name="date_to" id="tla-to" value="<?= crm_sanitize($_GET['date_to'] ?? '') ?>"
                   onchange="document.getElementById('filterForm').submit()">
        </div>

        <?php if ($is_super || $is_admin || $user['role'] === 'manager'): ?>
        <div class="tla-filter-group">
            <label for="tla-owner">مالک</label>
            <select name="filter_user" id="tla-owner" onchange="document.getElementById('filterForm').submit()">
                <option value="">همه</option>
                <?php
                $pdo = getDB();
                if ($is_super) {
                    $users_q = $pdo->query("SELECT id, full_name FROM users ORDER BY full_name");
                } elseif ($is_admin) {
                    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE id = ? OR parent_id = ? OR parent_id IN (SELECT id FROM users WHERE parent_id = ?) ORDER BY full_name");
                    $stmt->execute([$user['id'], $user['id'], $user['id']]);
                    $users_q = $stmt;
                } else {
                    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE id = ? OR parent_id = ? ORDER BY full_name");
                    $stmt->execute([$user['id'], $user['id']]);
                    $users_q = $stmt;
                }
                $filter_users = $users_q->fetchAll();
                $current_filter_user = $_GET['filter_user'] ?? '';
                foreach ($filter_users as $fu): ?>
                <option value="<?= $fu['id'] ?>" <?= $current_filter_user == $fu['id'] ? 'selected' : '' ?>>
                    <?= crm_sanitize($fu['full_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <a href="index.php?page=tasks&action=list_all" class="tla-clear-btn">حذف فیلتر</a>
    </div>
</form>

<?php if (empty($all_tasks)): ?>
    <div class="tla-empty">
        <p>هیچ تسکی یافت نشد.</p>
    </div>
<?php else:
    $active_count    = count(array_filter($all_tasks, fn($t) => $t['status'] === 'active'));
    $completed_count = count(array_filter($all_tasks, fn($t) => $t['status'] === 'completed'));
    $sold_count      = count(array_filter($all_tasks, fn($t) => $t['status'] === 'sold'));
    $cancelled_count = count(array_filter($all_tasks, fn($t) => $t['status'] === 'cancelled'));
    $now = time();
?>

<div class="tla-table-card">
    <table class="tla-table">
        <thead>
            <tr>
                <th style="width:32px; text-align:center;"><span class="sr-only">اولویت</span>●</th>
                <th>عنوان تسک</th>
                <th>مشتری</th>
                <th>مالک</th>
                <?php if ($is_super): ?>
                <th>سازمان</th>
                <?php endif; ?>
                <th>پیگیری بعدی</th>
                <th>ایجاد</th>
                <th>وضعیت</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_tasks as $t):
                $followup = !empty($t['next_followup_date']) ? strtotime($t['next_followup_date']) : null;
                $diff_days = $followup ? floor(($followup - $now) / 86400) : 999;

                // رنگ اولویت
                if ($t['status'] !== 'active' || !$followup) {
                    $dot_color = '#D0C9BC';
                } elseif ($diff_days < 0) {
                    $dot_color = 'var(--danger)';
                } elseif ($diff_days === 0) {
                    $dot_color = 'var(--warning-deep)';
                } elseif ($diff_days <= 2) {
                    $dot_color = 'var(--ember)';
                } elseif ($diff_days <= 7) {
                    $dot_color = 'var(--blue)';
                } else {
                    $dot_color = 'var(--teal)';
                }

                $followup_color = ($followup && $diff_days < 0 && $t['status'] === 'active') ? 'var(--danger)' : 'var(--ink-soft)';

                $row_class = 'clickable';
                if ($t['status'] === 'completed') $row_class .= ' dim';
                if ($t['status'] === 'cancelled')  $row_class .= ' very-dim';

                $status_badge = match($t['status']) {
                    'active'    => '<span class="tla-badge tla-badge-active">فعال</span>',
                    'completed' => '<span class="tla-badge tla-badge-completed">تکمیل</span>',
                    'sold'      => '<span class="tla-badge tla-badge-sold">فروش</span>',
                    default     => '<span class="tla-badge tla-badge-cancelled">لغو</span>',
                };
            ?>
            <tr class="<?= $row_class ?>" onclick="window.location='index.php?page=tasks&action=view&id=<?= $t['id'] ?>'">
                <td style="text-align:center">
                    <span class="tla-dot" style="background:<?= $dot_color ?>" aria-hidden="true"></span>
                </td>
                <td>
                    <div class="tla-task-title"><?= crm_sanitize($t['title']) ?></div>
                    <?php if (!empty($t['next_followup_topic']) && $t['status'] === 'active'): ?>
                    <div class="tla-task-topic"><?= crm_sanitize($t['next_followup_topic']) ?></div>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px"><?= crm_sanitize($t['company_name']) ?></td>
                <td style="font-size:11.5px;color:var(--ink-soft)"><?= crm_sanitize($t['agent_name']) ?></td>
                <?php if ($is_super): ?>
                <td><span class="tla-badge tla-badge-org"><?= crm_sanitize($t['company_label'] ?? '—') ?></span></td>
                <?php endif; ?>
                <td style="font-size:11.5px;color:<?= $followup_color ?>;white-space:nowrap">
                    <?= $followup ? (function_exists('jdatetime') ? jdatetime($t['next_followup_date']) : date('Y/m/d H:i', $followup)) : '—' ?>
                </td>
                <td style="font-size:11px;color:var(--ink-soft);white-space:nowrap">
                    <?= function_exists('jdate') ? jdate($t['created_at']) : date('Y/m/d', strtotime($t['created_at'])) ?>
                </td>
                <td><?= $status_badge ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="tla-footer">
    <span>کل: <strong><?= count($all_tasks) ?></strong> تسک</span>
    <div class="tla-summary">
        <span class="tla-summary-item"><span class="tla-dot" style="background:var(--teal)" aria-hidden="true"></span><?= $active_count ?> فعال</span>
        <span class="tla-summary-item"><span class="tla-dot" style="background:var(--blue)" aria-hidden="true"></span><?= $completed_count ?> تکمیل</span>
        <span class="tla-summary-item"><span class="tla-dot" style="background:var(--warning-deep)" aria-hidden="true"></span><?= $sold_count ?> فروش</span>
        <span class="tla-summary-item"><span class="tla-dot" style="background:var(--danger)" aria-hidden="true"></span><?= $cancelled_count ?> لغو</span>
    </div>
</div>

<?php endif; ?>

</div><!-- /tla-wrap -->