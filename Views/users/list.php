<!-- Views/users/list.php -->

<style>
.ul-wrap {
    --ul-ink:#14213D; --ul-ink-soft:#4A5578; --ul-ember:#FF6B35; --ul-ember-deep:#E6531E;
    --ul-teal:#16A085; --ul-paper:#FAF8F5; --ul-paper2:#F2EEE6; --ul-line:#E5DFD3;
    --ul-card:#FFFFFF; --ul-blue:#1a73e8; --ul-danger:#EA4335;
    direction: rtl;
}
.ul-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.ul-header h2 { font-size:18px; font-weight:800; color:var(--ul-ink); letter-spacing:-.01em; }
.ul-btn-add { display:inline-flex; align-items:center; gap:6px; padding:10px 18px; border-radius:10px; font-size:13px; font-weight:700; text-decoration:none; background:var(--ul-ember); color:#fff; box-shadow:0 3px 10px rgba(255,107,53,.28); transition:all .15s; }
.ul-btn-add:hover { background:var(--ul-ember-deep); transform:translateY(-1px); }
.ul-alert { padding:11px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; background:#E7F7F3; color:#0E8170; border:1px solid #B8E5DA; }
.ul-empty { text-align:center; padding:50px 20px; background:var(--ul-card); border:1px solid var(--ul-line); border-radius:14px; }
.ul-empty .icon { font-size:40px; margin-bottom:12px; }
.ul-empty p { color:var(--ul-ink-soft); font-size:14px; margin-bottom:18px; }
.ul-empty a { display:inline-flex; align-items:center; gap:6px; padding:10px 22px; border-radius:10px; background:var(--ul-ember); color:#fff; text-decoration:none; font-weight:700; font-size:13px; }

/* section header */
.ul-section { margin-bottom:24px; }
.ul-section-title { font-size:13px; font-weight:800; color:var(--ul-ink-soft); margin-bottom:10px; display:flex; align-items:center; gap:7px; padding-bottom:8px; border-bottom:1.5px solid var(--ul-line); }

/* کارت‌های سطح ریشه */
.ul-root-card { background:var(--ul-card); border:1px solid var(--ul-line); border-radius:13px; padding:14px 16px; margin-bottom:6px; text-decoration:none; color:var(--ul-ink); display:block; border-right:4px solid var(--ul-line); transition:box-shadow .18s; }
.ul-root-card:hover { box-shadow:0 4px 16px rgba(20,33,61,.07); }
.ul-root-card.self { background:#F5F8FF; }
.ul-root-card.role-super   { border-right-color:#D97706; }
.ul-root-card.role-admin   { border-right-color:var(--ul-blue); }
.ul-root-card.role-manager { border-right-color:var(--ul-ember); }
.ul-root-card.no-link { cursor:default; opacity:.8; }

/* child سطح ۱ */
.ul-child1-wrap { margin-right:22px; border-right:2px dashed var(--ul-line); padding-right:10px; margin-top:2px; margin-bottom:4px; }
.ul-child1-card { background:var(--ul-paper2); border-radius:10px; padding:12px 14px; margin-bottom:4px; text-decoration:none; color:var(--ul-ink); display:block; border-right:3px solid var(--ul-line); transition:background .15s; }
.ul-child1-card:hover { background:#EBE5D8; }
.ul-child1-card.self { background:#EEF4FF; border-right-color:var(--ul-blue); }
.ul-child1-card.role-manager { border-right-color:var(--ul-ember); }
.ul-child1-card.no-link { cursor:default; opacity:.8; }

/* child سطح ۲ */
.ul-child2-wrap { margin-right:22px; border-right:2px dotted var(--ul-line); padding-right:10px; margin-top:2px; margin-bottom:2px; }
.ul-child2-card { background:#F7F3EE; border-radius:9px; padding:10px 13px; margin-bottom:3px; text-decoration:none; color:var(--ul-ink); display:block; font-size:13px; transition:background .15s; }
.ul-child2-card:hover { background:#EDE7DB; }
.ul-child2-card.self { background:#EEF4FF; }
.ul-child2-card.no-link { cursor:default; opacity:.7; }

/* محتوای کارت */
.ul-card-top { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; }
.ul-card-name { font-weight:700; font-size:14px; color:var(--ul-ink); display:flex; align-items:center; gap:7px; flex-wrap:wrap; }
.ul-role-badge { font-size:10px; font-weight:700; padding:2px 8px; border-radius:8px; white-space:nowrap; }
.ul-role-super   { background:#FEF3C7; color:#D97706; }
.ul-role-admin   { background:#E8F0FE; color:var(--ul-blue); }
.ul-role-manager { background:#FFF1EA; color:var(--ul-ember-deep); }
.ul-role-agent   { background:var(--ul-paper2); color:var(--ul-ink-soft); }
.ul-position { font-size:11px; color:var(--ul-ink-soft); }
.ul-company  { font-size:11px; color:var(--ul-ink-soft); margin-top:2px; }
.ul-card-meta { font-size:11px; color:var(--ul-ink-soft); margin-top:6px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.ul-status-ok  { color:var(--ul-teal); font-weight:600; }
.ul-status-bad { color:var(--ul-danger); font-weight:600; }
.ul-subcount { font-size:10px; color:var(--ul-ink-soft); background:var(--ul-paper); padding:1px 7px; border-radius:8px; border:1px solid var(--ul-line); }

/* orphan */
.ul-orphan { background:var(--ul-card); border:1.5px dashed var(--ul-line); border-radius:10px; padding:12px 14px; margin-bottom:6px; text-decoration:none; color:var(--ul-ink); display:block; font-size:13px; transition:background .15s; }
.ul-orphan:hover { background:var(--ul-paper2); }
.ul-tel-link { color:var(--ul-blue); text-decoration:none; direction:ltr; display:inline-flex; align-items:center; gap:4px; }
.ul-tel-link:hover { text-decoration:underline; }
</style>

<?php

$role_labels = ['super_admin'=>'👑 سوپر ادمین','admin'=>'🛡️ مدیر','manager'=>'👔 مدیر فروش','agent'=>'📞 کارشناس'];
$role_cls    = ['super_admin'=>'ul-role-super','admin'=>'ul-role-admin','manager'=>'ul-role-manager','agent'=>'ul-role-agent'];
$card_role   = ['super_admin'=>'role-super','admin'=>'role-admin','manager'=>'role-manager','agent'=>''];

function ul_can_view($target, $current, $is_super, $is_admin) {
    if ($is_super) return true;
    if ($target['id'] == $current['id']) return true;
    if ($is_admin) return true;
    if ($current['role'] === 'manager') {
        if ($target['parent_id'] == $current['id']) return true;
        if ($current['parent_id'] && $target['id'] == $current['parent_id']) return true;
    }
    return false;
}

function ul_status($u) {
    $bad = strtotime($u['plan_expiry']) <= time() || ($u['status'] ?? 'active') === 'inactive';
    $text = $bad ? (($u['status'] ?? 'active') === 'inactive' ? '🔴 غیرفعال' : '🔴 منقضی') : '🟢 فعال';
    return ['bad' => $bad, 'text' => $text];
}

// ایندکس سریع برای پیدا کردن کاربر با id
$users_by_id = [];
foreach ($users_list as $u) {
    $users_by_id[$u['id']] = $u;
}

if ($is_super) {
    // ── سه بخش جداگانه برای super_admin ──

    // بخش ۱: زیرمجموعه‌های مستقیم super_admin (parent_id == $user['id'])
    $my_children = array_values(array_filter($users_list, function($u) use ($user) {
        return (int)($u['parent_id'] ?? 0) === (int)$user['id'];
    }));

    // بخش ۲: کاربران بدون پرنت (agent یا manager که parent_id خالی/صفر دارن و super_admin نیستن)
    $orphans = array_values(array_filter($users_list, function($u) use ($user) {
        return $u['role'] !== 'super_admin'
            && $u['role'] !== 'admin'
            && (empty($u['parent_id']) || (int)$u['parent_id'] === 0)
            && $u['id'] !== $user['id'];
    }));

    // بخش ۳: بقیه کاربران سیستم (parent_id دارن و زیرمجموعه مستقیم super_admin نیستن)
    $my_child_ids = array_column($my_children, 'id');
    $orphan_ids   = array_column($orphans, 'id');
    $exclude_ids  = array_merge([$user['id']], $my_child_ids, $orphan_ids);

    $system_users = array_values(array_filter($users_list, function($u) use ($exclude_ids) {
        return !in_array($u['id'], $exclude_ids);
    }));

    // گروه‌بندی system_users بر اساس parent_id برای نمایش درختی
    $sys_by_parent = [];
    foreach ($system_users as $u) {
        $pid = (int)($u['parent_id'] ?? 0);
        $sys_by_parent[$pid][] = $u;
    }
    // ریشه‌های سیستم = کسانی که parent_idشون در system_users نیست (یعنی parent یا admin است یا خارج از لیست)
    $system_user_ids = array_column($system_users, 'id');
    $system_roots = array_values(array_filter($system_users, function($u) use ($system_user_ids) {
        return empty($u['parent_id'])
            || (int)$u['parent_id'] === 0
            || !in_array((int)$u['parent_id'], $system_user_ids);
    }));

} else {
    // ── برای admin/manager: نمایش سلسله‌مراتبی معمولی ──
    $by_parent = [];
    foreach ($users_list as $u) {
        $pid = (int)($u['parent_id'] ?? 0);
        $by_parent[$pid][] = $u;
    }
    $roots = $by_parent[0] ?? [];
    if (empty($roots)) {
        $roots = array_filter($users_list, function($u) use ($user) {
            return $u['id'] == $user['id'] || empty($u['parent_id']);
        });
    }
}

// تابع کمکی رندر یه کارت کاربر در سطح ریشه
function ul_render_root($u, $user, $is_super, $is_admin, $role_labels, $role_cls, $card_role, $by_parent_ref = []) {
    if (empty($u)) return;
    $can  = ul_can_view($u, $user, $is_super, $is_admin);
    $st   = ul_status($u);
    $self = ($u['id'] == $user['id']);
    $cls  = 'ul-root-card ' . ($card_role[$u['role']] ?? '') . ($self ? ' self' : '') . ($can ? '' : ' no-link');
    $onclick = $can ? ' onclick="window.location=\'index.php?page=users&action=view&id='.$u['id'].'\'" style="cursor:pointer"' : '';
    echo '<div style="margin-bottom:8px">';
    echo "<div{$onclick} class=\"$cls\">";
    echo '<div class="ul-card-top"><div>';
    echo '<div class="ul-card-name">' . crm_sanitize($u['full_name']);
    echo '<span class="ul-role-badge ' . ($role_cls[$u['role']] ?? 'ul-role-agent') . '">' . ($role_labels[$u['role']] ?? $u['role']) . '</span>';
    if ($u['position_title']) echo '<span class="ul-position">💼 ' . crm_sanitize($u['position_title']) . '</span>';
    echo '</div>';
    if ($u['company_name']) echo '<div class="ul-company">🏢 ' . crm_sanitize($u['company_name']) . '</div>';
    echo '</div>';
    if (!$can) echo '<span style="font-size:11px;color:var(--ul-ink-soft)">🔒</span>';
    echo '</div>';
    echo '<div class="ul-card-meta"><span><a href="tel:' . crm_sanitize($u['mobile']) . '" class="ul-tel-link" onclick="event.stopPropagation()">📱 ' . crm_sanitize($u['mobile']) . '</a></span>';
    echo '<span class="' . ($st['bad'] ? 'ul-status-bad' : 'ul-status-ok') . '">' . $st['text'] . '</span></div>';
    echo '</div>';

    // child سطح ۱
    $c1s = $by_parent_ref[$u['id']] ?? [];
    if (!empty($c1s)) {
        echo '<div class="ul-child1-wrap">';
        foreach ($c1s as $c1) {
            $can1  = ul_can_view($c1, $user, $is_super, $is_admin);
            $st1   = ul_status($c1);
            $self1 = ($c1['id'] == $user['id']);
            $has_gc = !empty($by_parent_ref[$c1['id']]);
            $cls1  = 'ul-child1-card' . ($c1['role']==='manager' ? ' role-manager' : '') . ($self1 ? ' self' : '') . ($can1 ? '' : ' no-link');
            $onclick1 = $can1 ? ' onclick="window.location=\'index.php?page=users&action=view&id='.$c1['id'].'\'" style="cursor:pointer"' : '';
            echo "<div{$onclick1} class=\"$cls1\">";
            echo '<div class="ul-card-top"><div class="ul-card-name" style="font-size:13.5px">' . crm_sanitize($c1['full_name']);
            echo '<span class="ul-role-badge ' . ($role_cls[$c1['role']] ?? 'ul-role-agent') . '">' . ($role_labels[$c1['role']] ?? $c1['role']) . '</span>';
            if ($c1['position_title']) echo '<span class="ul-position">💼 ' . crm_sanitize($c1['position_title']) . '</span>';
            if ($has_gc) echo '<span class="ul-subcount">' . count($by_parent_ref[$c1['id']]) . ' زیرمجموعه</span>';
            echo '</div>';
            if (!$can1) echo '<span style="font-size:10px;color:var(--ul-ink-soft)">🔒</span>';
            echo '</div>';
            echo '<div class="ul-card-meta"><span><a href="tel:' . crm_sanitize($c1['mobile']) . '" class="ul-tel-link" onclick="event.stopPropagation()">📱 ' . crm_sanitize($c1['mobile']) . '</a></span>';
            echo '<span class="' . ($st1['bad'] ? 'ul-status-bad' : 'ul-status-ok') . '">' . $st1['text'] . '</span></div>';
            echo '</div>';
            // child سطح ۲
            if ($has_gc) {
                echo '<div class="ul-child2-wrap">';
                foreach ($by_parent_ref[$c1['id']] as $c2) {
                    $can2  = ul_can_view($c2, $user, $is_super, $is_admin);
                    $st2   = ul_status($c2);
                    $self2 = ($c2['id'] == $user['id']);
                    $cls2  = 'ul-child2-card' . ($self2 ? ' self' : '') . ($can2 ? '' : ' no-link');
                    $onclick2 = $can2 ? ' onclick="window.location=\'index.php?page=users&action=view&id='.$c2['id'].'\'" style="cursor:pointer"' : '';
                    echo "<div{$onclick2} class=\"$cls2\">";
                    echo '<div style="display:flex;justify-content:space-between;align-items:center;gap:8px">';
                    echo '<div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap">';
                    echo '<span style="font-weight:600">' . crm_sanitize($c2['full_name']) . '</span>';
                    echo '<span class="ul-role-badge ' . ($role_cls[$c2['role']] ?? 'ul-role-agent') . '">' . ($role_labels[$c2['role']] ?? $c2['role']) . '</span>';
                    if ($c2['position_title']) echo '<span class="ul-position">' . crm_sanitize($c2['position_title']) . '</span>';
                    echo '</div><div style="display:flex;align-items:center;gap:5px">';
                    echo '<span class="' . ($st2['bad'] ? 'ul-status-bad' : 'ul-status-ok') . '" style="font-size:10.5px">' . $st2['text'] . '</span>';
                    if (!$can2) echo '<span style="font-size:10px;color:var(--ul-ink-soft)">🔒</span>';
                    echo '</div></div>';
                    echo '</div>';
                }
                echo '</div>';
            }
        }
        echo '</div>';
    }
    echo '</div>';
}
?>

<div class="ul-wrap">

<div class="ul-header">
    <h2>👥 <?php
        if ($is_super) echo 'همه کاربران سیستم';
        elseif ($is_admin) echo 'کاربران شرکت';
        else echo 'زیرمجموعه‌های من';
    ?></h2>
    <a href="index.php?page=users&action=add" class="ul-btn-add">➕ کاربر جدید</a>
</div>

<?php if ($message === 'created'): ?>
    <div class="ul-alert">✅ کاربر جدید با موفقیت ایجاد شد.</div>
<?php elseif ($message === 'updated'): ?>
    <div class="ul-alert">✅ کاربر بروزرسانی شد.</div>
<?php elseif ($message === 'recharged'): ?>
    <div class="ul-alert">✅ حساب کاربر شارژ شد.</div>
<?php endif; ?>

<?php if (empty($users_list)): ?>
    <div class="ul-empty">
        <div class="icon">👤</div>
        <p>هنوز زیرمجموعه‌ای تعریف نکردید.</p>
        <a href="index.php?page=users&action=add">➕ افزودن کارشناس</a>
    </div>

<?php elseif ($is_super): ?>

    <!-- ══ بخش ۱: زیرمجموعه‌های من ══ -->
    <?php if (!empty($my_children)): ?>
    <div class="ul-section">
        <div class="ul-section-title">👤 زیرمجموعه‌های من (<?= count($my_children) ?>)</div>
        <?php
        $my_by_parent = [];
        foreach ($users_list as $u) {
            $pid = (int)($u['parent_id'] ?? 0);
            $my_by_parent[$pid][] = $u;
        }
        foreach ($my_children as $u) {
            ul_render_root($u, $user, $is_super, $is_admin, $role_labels, $role_cls, $card_role, $my_by_parent);
        }
        ?>
    </div>
    <?php endif; ?>

    <!-- ══ بخش ۲: کاربران بدون پرنت ══ -->
    <?php if (!empty($orphans)): ?>
    <div class="ul-section">
        <div class="ul-section-title">🔓 کاربران بدون پرنت (<?= count($orphans) ?>)</div>
        <?php foreach ($orphans as $u):
            $can = ul_can_view($u, $user, $is_super, $is_admin);
            $st  = ul_status($u);
            $onclick = $can ? ' onclick="window.location=\'index.php?page=users&action=view&id='.$u['id'].'\'" style="cursor:pointer"' : '';
        ?>
        <div<?= $onclick ?> class="ul-orphan">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    <span style="font-weight:700"><?= crm_sanitize($u['full_name']) ?></span>
                    <span class="ul-role-badge <?= $role_cls[$u['role']] ?? 'ul-role-agent' ?>"><?= $role_labels[$u['role']] ?? $u['role'] ?></span>
                    <?php if ($u['company_name']): ?>
                    <span style="font-size:10.5px;color:var(--ul-ink-soft)">🏢 <?= crm_sanitize($u['company_name']) ?></span>
                    <?php endif; ?>
                    <a href="tel:<?= crm_sanitize($u['mobile']) ?>" class="ul-tel-link" style="font-size:11px" onclick="event.stopPropagation()">📱 <?= crm_sanitize($u['mobile']) ?></a>
                </div>
                <span class="<?= $st['bad'] ? 'ul-status-bad' : 'ul-status-ok' ?>"><?= $st['text'] ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ══ بخش ۳: کاربران سیستم ══ -->
    <?php if (!empty($system_roots)): ?>
    <div class="ul-section">
        <div class="ul-section-title">🏢 کاربران سیستم (<?= count($system_users) ?>)</div>
        <?php
        $sys_all_by_parent = [];
        foreach ($system_users as $u) {
            $pid = (int)($u['parent_id'] ?? 0);
            $sys_all_by_parent[$pid][] = $u;
        }
        foreach ($system_roots as $u) {
            ul_render_root($u, $user, $is_super, $is_admin, $role_labels, $role_cls, $card_role, $sys_all_by_parent);
        }
        ?>
    </div>
    <?php endif; ?>

<?php else: ?>

    <!-- ══ نمایش معمولی برای admin/manager ══ -->
    <?php
    $by_parent_nm = [];
    foreach ($users_list as $u) {
        $pid = (int)($u['parent_id'] ?? 0);
        $by_parent_nm[$pid][] = $u;
    }
    $roots_nm = $by_parent_nm[0] ?? [];
    if (empty($roots_nm)) {
        $roots_nm = array_filter($users_list, function($u) use ($user) {
            return $u['id'] == $user['id'] || empty($u['parent_id']);
        });
    }
    foreach ($roots_nm as $u) {
        ul_render_root($u, $user, $is_super, $is_admin, $role_labels, $role_cls, $card_role, $by_parent_nm);
    }
    ?>

<?php endif; ?>

</div>