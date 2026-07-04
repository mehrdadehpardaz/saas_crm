<!-- Views/users/view.php -->

<style>
.uv-wrap {
    --uv-ink:#14213D; --uv-ink-soft:#4A5578; --uv-ember:#FF6B35; --uv-ember-deep:#E6531E;
    --uv-teal:#16A085; --uv-teal-deep:#0E8170; --uv-paper:#FAF8F5; --uv-paper2:#F2EEE6;
    --uv-line:#E5DFD3; --uv-card:#FFFFFF; --uv-blue:#1a73e8; --uv-danger:#EA4335; --uv-warning:#D97706;
    direction: rtl; max-width: 760px;
}

.uv-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.uv-header h2 { font-size:18px; font-weight:800; color:var(--uv-ink); letter-spacing:-.01em; display:flex; align-items:center; gap:10px; }
.uv-actions { display:flex; gap:8px; flex-wrap:wrap; }
.uv-btn { display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:9px; font-size:12.5px; font-weight:700; text-decoration:none; border:1.5px solid var(--uv-line); background:var(--uv-card); color:var(--uv-ink-soft); transition:all .15s; }
.uv-btn:hover { border-color:var(--uv-ink); color:var(--uv-ink); }
.uv-btn-edit { background:var(--uv-ember); color:#fff; border-color:var(--uv-ember); box-shadow:0 3px 10px rgba(255,107,53,.28); }
.uv-btn-edit:hover { background:var(--uv-ember-deep); border-color:var(--uv-ember-deep); color:#fff; transform:translateY(-1px); }

/* role badge */
.uv-role { display:inline-flex; align-items:center; gap:5px; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; }
.uv-role-super   { background:#FEF3C7; color:#D97706; }
.uv-role-admin   { background:#E8F0FE; color:var(--uv-blue); }
.uv-role-manager { background:#FFF1EA; color:var(--uv-ember-deep); }
.uv-role-agent   { background:var(--uv-paper2); color:var(--uv-ink-soft); }

/* info card */
.uv-info-card { background:var(--uv-card); border:1px solid var(--uv-line); border-radius:14px; overflow:hidden; margin-bottom:18px; }
.uv-info-row { display:flex; align-items:center; gap:12px; padding:12px 17px; border-bottom:1px solid var(--uv-paper2); font-size:13px; }
.uv-info-row:last-child { border:none; }
.uv-info-icon { font-size:14px; width:24px; text-align:center; flex-shrink:0; }
.uv-info-label { color:var(--uv-ink-soft); min-width:90px; font-size:12px; }
.uv-info-val { color:var(--uv-ink); font-weight:500; flex:1; }
.uv-status-ok  { color:var(--uv-teal-deep); font-weight:700; }
.uv-status-bad { color:var(--uv-danger); font-weight:700; }
.uv-tel-link { color:var(--uv-blue); text-decoration:none; display:inline-flex; align-items:center; gap:5px; }
.uv-tel-link:hover { text-decoration:underline; }

/* KPI */
.uv-kpi-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:18px; }
.uv-kpi { background:var(--uv-card); border:1px solid var(--uv-line); border-radius:13px; padding:16px 13px; text-align:center; }
.uv-kpi-val { font-size:24px; font-weight:800; line-height:1; margin-bottom:6px; }
.uv-kpi-label { font-size:11.5px; color:var(--uv-ink-soft); }

/* شارژ / پلن */
.uv-manage-card { background:var(--uv-card); border:1px solid var(--uv-line); border-radius:14px; overflow:hidden; margin-bottom:16px; }
.uv-manage-hd { padding:13px 17px; border-bottom:1px solid var(--uv-line); font-size:14px; font-weight:700; color:var(--uv-ink); }
.uv-manage-body { padding:17px; }
.uv-manage-body select { width:100%; padding:10px 14px; border:1.5px solid var(--uv-line); border-radius:9px; font-size:13px; font-family:inherit; background:var(--uv-paper); color:var(--uv-ink); margin-bottom:12px; }
.uv-manage-body select:focus { outline:none; border-color:var(--uv-ember); }
.uv-btn-submit { display:inline-flex; align-items:center; gap:6px; padding:10px 22px; border-radius:10px; font-size:13px; font-weight:700; border:none; cursor:pointer; background:var(--uv-teal); color:#fff; box-shadow:0 3px 10px rgba(22,160,133,.28); transition:all .15s; }
.uv-btn-submit:hover { background:var(--uv-teal-deep); transform:translateY(-1px); }
.uv-plan-cta { display:inline-flex; align-items:center; gap:6px; padding:10px 20px; border-radius:10px; font-size:13px; font-weight:700; text-decoration:none; background:var(--uv-ember); color:#fff; box-shadow:0 3px 10px rgba(255,107,53,.28); transition:all .15s; }
.uv-plan-cta:hover { background:var(--uv-ember-deep); transform:translateY(-1px); }

/* زیرمجموعه‌ها */
.uv-sub-card { background:var(--uv-card); border:1px solid var(--uv-line); border-radius:14px; overflow:hidden; margin-bottom:16px; }
.uv-sub-hd { padding:13px 17px; border-bottom:1px solid var(--uv-line); display:flex; justify-content:space-between; align-items:center; }
.uv-sub-hd h3 { font-size:14px; font-weight:700; color:var(--uv-ink); }
.uv-sub-count { font-size:11px; font-weight:700; background:var(--uv-paper2); color:var(--uv-ink-soft); padding:2px 9px; border-radius:11px; }
.uv-sub-row { display:flex; justify-content:space-between; align-items:center; padding:12px 17px; border-bottom:1px solid var(--uv-paper2); text-decoration:none; color:var(--uv-ink); transition:background .15s; }
.uv-sub-row:last-child { border:none; }
.uv-sub-row:hover { background:var(--uv-paper2); }
.uv-sub-name { font-weight:600; font-size:13.5px; }
.uv-sub-pos { font-size:11.5px; color:var(--uv-ink-soft); }
.uv-sub-badge { font-size:10px; font-weight:700; padding:2px 8px; border-radius:8px; }
.uv-sub-badge-manager { background:#FFF1EA; color:var(--uv-ember-deep); }
.uv-sub-badge-agent   { background:var(--uv-paper2); color:var(--uv-ink-soft); }
</style>

<div class="uv-wrap">

<?php
$role_labels = ['super_admin'=>'👑 سوپر ادمین','admin'=>'🛡️ مدیر ارشد','manager'=>'👔 مدیر فروش','agent'=>'📞 کارشناس'];
$role_badge_cls = ['super_admin'=>'uv-role-super','admin'=>'uv-role-admin','manager'=>'uv-role-manager','agent'=>'uv-role-agent'];
$is_expired = strtotime($view_user['plan_expiry']) <= time();
$is_inactive = ($view_user['status'] ?? 'active') === 'inactive';
$is_user_active = !$is_expired && !$is_inactive;
?>

<!-- هدر -->
<div class="uv-header">
    <h2>
        👤 <?= crm_sanitize($view_user['full_name']) ?>
        <span class="uv-role <?= $role_badge_cls[$view_user['role']] ?? 'uv-role-agent' ?>"><?= $role_labels[$view_user['role']] ?? $view_user['role'] ?></span>
    </h2>
    <div class="uv-actions">
        <?php if ($has_access): ?>
        <a href="index.php?page=users&action=edit&id=<?= $view_user['id'] ?>" class="uv-btn uv-btn-edit">✏️ ویرایش</a>
        <?php endif; ?>
        <a href="index.php?page=users" class="uv-btn">🔙 بازگشت</a>
    </div>
</div>

<!-- اطلاعات -->
<div class="uv-info-card">
    <?php if (!empty($view_user['company_name'])): ?>
    <div class="uv-info-row">
        <span class="uv-info-icon">🏢</span>
        <span class="uv-info-label">شرکت</span>
        <span class="uv-info-val"><?= crm_sanitize($view_user['company_name']) ?></span>
    </div>
    <?php endif; ?>
    <?php if (!empty($view_user['position_title'])): ?>
    <div class="uv-info-row">
        <span class="uv-info-icon">💼</span>
        <span class="uv-info-label">سمت</span>
        <span class="uv-info-val"><?= crm_sanitize($view_user['position_title']) ?></span>
    </div>
    <?php endif; ?>
    <div class="uv-info-row">
        <span class="uv-info-icon">📱</span>
        <span class="uv-info-label">موبایل</span>
        <span class="uv-info-val"><a href="tel:<?= crm_sanitize($view_user['mobile']) ?>" class="uv-tel-link" style="direction:ltr">📞 <?= crm_sanitize($view_user['mobile']) ?></a></span>
    </div>
    <?php if (!empty($view_user['phone']) && $view_user['phone'] !== $view_user['mobile']): ?>
    <div class="uv-info-row">
        <span class="uv-info-icon">📞</span>
        <span class="uv-info-label">تلفن</span>
        <span class="uv-info-val"><a href="tel:<?= crm_sanitize($view_user['phone']) ?>" class="uv-tel-link" style="direction:ltr">📞 <?= crm_sanitize($view_user['phone']) ?></a></span>
    </div>
    <?php endif; ?>

    <?php
    $parent = null;
    if ($view_user['parent_id']) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT id, full_name, position_title FROM users WHERE id = ?");
        $stmt->execute([$view_user['parent_id']]);
        $parent = $stmt->fetch();
    }
    ?>
    <?php if ($parent): ?>
    <div class="uv-info-row">
        <span class="uv-info-icon">👔</span>
        <span class="uv-info-label">مدیر</span>
        <span class="uv-info-val">
            <a href="index.php?page=users&action=view&id=<?= $parent['id'] ?>" style="color:var(--uv-blue);text-decoration:none;font-weight:600">
                <?= crm_sanitize($parent['full_name']) ?>
            </a>
            <?php if ($parent['position_title']): ?>
            <span style="font-size:11.5px;color:var(--uv-ink-soft)"> — <?= crm_sanitize($parent['position_title']) ?></span>
            <?php endif; ?>
        </span>
    </div>
    <?php endif; ?>

    <div class="uv-info-row">
        <span class="uv-info-icon">📅</span>
        <span class="uv-info-label">تاریخ عضویت</span>
        <span class="uv-info-val"><?= function_exists('jdate') ? jdate($view_user['created_at']) : date('Y/m/d', strtotime($view_user['created_at'])) ?></span>
    </div>
    <div class="uv-info-row">
        <span class="uv-info-icon">⏰</span>
        <span class="uv-info-label">پایان اشتراک</span>
        <span class="uv-info-val">
            <span class="<?= $is_user_active ? 'uv-status-ok' : 'uv-status-bad' ?>">
                <?= function_exists('jdate') ? jdate($view_user['plan_expiry']) : date('Y/m/d', strtotime($view_user['plan_expiry'])) ?>
            </span>
            <span style="font-size:11.5px;margin-right:8px;color:var(--uv-ink-soft)">(<?= $is_user_active ? '🟢 فعال' : '🔴 منقضی/غیرفعال' ?>)</span>
        </span>
    </div>
    <div class="uv-info-row">
        <span class="uv-info-icon">💳</span>
        <span class="uv-info-label">پلن</span>
        <span class="uv-info-val">
            <?php if ($view_user['plan_type'] === 'trial'): ?>🎁 رایگان ۱۴ روزه
            <?php elseif ($view_user['plan_type'] === 'monthly'): ?>📅 ماهانه
            <?php else: ?>🗓️ سالانه<?php endif; ?>
            <?php if (in_array($view_user['role'], ['admin','super_admin'])): ?>
            <span style="font-size:11.5px;color:var(--uv-ink-soft);margin-right:10px">👥 سقف: <?= $view_user['max_users_limit'] ?> کاربر</span>
            <?php endif; ?>
        </span>
    </div>
</div>

<!-- آمار -->
<div class="uv-kpi-grid">
    <div class="uv-kpi">
        <div class="uv-kpi-val" style="color:var(--uv-blue)"><?= $total_customers ?></div>
        <div class="uv-kpi-label">👥 مشتریان</div>
    </div>
    <div class="uv-kpi">
        <div class="uv-kpi-val" style="color:var(--uv-ember)"><?= $active_tasks ?></div>
        <div class="uv-kpi-label">📋 تسک‌های فعال</div>
    </div>
    <div class="uv-kpi">
        <div class="uv-kpi-val" style="color:var(--uv-warning)"><?= $today_reminders ?></div>
        <div class="uv-kpi-label">⏰ پیگیری امروز</div>
    </div>
</div>


<!-- زیرمجموعه‌ها -->
<?php if (in_array($view_user['role'], ['super_admin','admin','manager'])): ?>
    <?php
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, full_name, position_title, role, plan_expiry, status, mobile FROM users WHERE parent_id = ? ORDER BY created_at DESC");
    $stmt->execute([$view_user['id']]);
    $sub_users = $stmt->fetchAll();
    ?>
    <?php if (!empty($sub_users)): ?>
    <div class="uv-sub-card">
        <div class="uv-sub-hd">
            <h3>👥 زیرمجموعه‌ها</h3>
            <span class="uv-sub-count"><?= count($sub_users) ?></span>
        </div>
        <?php foreach ($sub_users as $sub):
            $sub_active = strtotime($sub['plan_expiry']) > time() && ($sub['status'] ?? 'active') === 'active';
            $badge_cls = $sub['role'] === 'manager' ? 'uv-sub-badge-manager' : 'uv-sub-badge-agent';
            $badge_lbl = $sub['role'] === 'manager' ? '👔 مدیر' : '📞 کارشناس';
        ?>
        <div onclick="window.location='index.php?page=users&action=view&id=<?= $sub['id'] ?>'" class="uv-sub-row" style="cursor:pointer">
            <div>
                <div class="uv-sub-name">
                    <?= crm_sanitize($sub['full_name']) ?>
                    <span class="uv-sub-badge <?= $badge_cls ?>"><?= $badge_lbl ?></span>
                </div>
                <?php if ($sub['position_title']): ?>
                <div class="uv-sub-pos"><?= crm_sanitize($sub['position_title']) ?></div>
                <?php endif; ?>
                <div style="font-size:11px;color:var(--uv-ink-soft);margin-top:2px"><a href="tel:<?= crm_sanitize($sub['mobile']) ?>" class="uv-tel-link" style="direction:ltr" onclick="event.stopPropagation()">📱 <?= crm_sanitize($sub['mobile']) ?></a></div>
            </div>
            <span class="<?= $sub_active ? 'uv-status-ok' : 'uv-status-bad' ?>" style="font-size:13px">
                <?= $sub_active ? '🟢' : '🔴' ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
<?php endif; ?>

</div><!-- /uv-wrap -->