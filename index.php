<?php
// index.php — v3.0 | ناوبری یکپارچه (۲ سیستم به‌جای ۴) | با پشتیبان‌گیری
ob_start();

// ── سخت‌سازی Session ──
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}
session_start();

// ── هدرهای امنیتی HTTP ──
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
define('CRM_APP', true);


require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
// require_once __DIR__ . '/includes/JalaliDate.php';
$page        = $_GET['page'] ?? 'dashboard';
$public_pages = ['auth'];

if (!in_array($page, $public_pages) && !crm_is_logged_in()) {
    crm_redirect('landing.php');
}

$with_layout = !in_array($page, $public_pages);

// ══════════════════════════════════════════════
//  مدیریت اشتراک و سقف کاربران
// ══════════════════════════════════════════════
if (crm_is_logged_in()) {
    $current_user = crm_get_current_user();

    if (in_array($current_user['role'], ['admin', 'super_admin']) && !empty($current_user['company_name'])) {
        $pdo        = getDB();
        $now        = time();
        $expiry     = strtotime($current_user['plan_expiry']);
        $is_expired = $expiry <= $now;
        $is_inactive = ($current_user['status'] ?? 'active') === 'inactive';

        if ($is_expired || $is_inactive) {
            $pdo->prepare("UPDATE users SET status='inactive' WHERE company_name=?")->execute([$current_user['company_name']]);
        } else {
            $max_limit  = (int)$current_user['max_users_limit'];
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE company_name=? AND status='active'");
            $stmt->execute([$current_user['company_name']]);
            $active_count = (int)$stmt->fetchColumn();

            if ($active_count > $max_limit) {
                $to_deactivate = $active_count - $max_limit;
                foreach (['agent','manager'] as $role) {
                    if ($to_deactivate <= 0) break;
                    $stmt = $pdo->prepare("UPDATE users SET status='inactive' WHERE company_name=? AND role=? AND status='active' AND id!=? ORDER BY created_at ASC LIMIT ?");
                    $stmt->execute([$current_user['company_name'], $role, $current_user['id'], $to_deactivate]);
                    $to_deactivate -= $stmt->rowCount();
                }
            } elseif ($active_count < $max_limit) {
                $slots = $max_limit - $active_count;
                foreach (['manager','agent'] as $role) {
                    if ($slots <= 0) break;
                    // نکته: هیچ‌وقت کاربری رو فعال نکن که پرنتِ مستقیمش غیرفعاله،
                    // و هیچ‌وقت کاربری رو که ادمین خودش دستی غیرفعالش کرده
                    // (deactivated_manually=1) خودکار فعال نکن — فقط کسایی که
                    // به‌خاطر پر شدن سقف پلن به‌صورت خودکار غیرفعال شده بودن.
                    $stmt = $pdo->prepare("UPDATE users SET status='active', plan_expiry=(SELECT plan_expiry FROM users WHERE company_name=? AND role='admin' LIMIT 1) WHERE company_name=? AND role=? AND status='inactive' AND deactivated_manually=0 AND (parent_id IS NULL OR parent_id IN (SELECT id FROM users WHERE status='active')) ORDER BY created_at DESC LIMIT ?");
                    $stmt->execute([$current_user['company_name'], $current_user['company_name'], $role, $slots]);
                    $slots -= $stmt->rowCount();
                }
            }
        }
    }

    if (!in_array($current_user['role'] ?? '', ['admin','super_admin']) && !empty($current_user['company_name'])) {
        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE company_name=? AND role='admin' LIMIT 1");
        $stmt->execute([$current_user['company_name']]);
        $company_admin = $stmt->fetch();

        if ($company_admin) {
            if (strtotime($company_admin['plan_expiry']) <= time() || ($company_admin['status'] ?? 'active') === 'inactive') {
                if (($current_user['status'] ?? 'active') === 'active') {
                    $pdo->prepare("UPDATE users SET status='inactive' WHERE id=?")->execute([$current_user['id']]);
                }
            }
        }
    }
}

// ══════════════════════════════════════════════
//  مپینگ صفحات به کنترلرها
// ══════════════════════════════════════════════
$controller_map = [
    'dashboard'  => 'DashboardController',
    'auth'       => 'AuthController',
    'customers'  => 'CustomerController',
    'customer'   => 'CustomerController',
    'activities' => 'ActivityController',
    'activity'   => 'ActivityController',
    'tasks'      => 'TasksController',
    'task'       => 'TasksController',
    'users'      => 'UsersController',
    'user'       => 'UsersController',
    'contacts'   => 'ContactController',
    'contact'    => 'ContactController',
    'plans'      => 'PlansController',
    'plan'       => 'PlansController',
    'reports'    => 'ReportController',
    'report'     => 'ReportController',
    'backup'     => 'BackupController',
    'profile'    => 'ProfileController',
    'support'    => 'SupportController',
    'ticket'     => 'SupportController',
];

$controller_name = $controller_map[$page] ?? null;
$controller_file = $controller_name
    ? __DIR__ . '/Controllers/' . $controller_name . '.php'
    : __DIR__ . '/Controllers/' . ucfirst($page) . 'Controller.php';

// ── اطلاعات منو برای هدر ──
$nav_user  = crm_is_logged_in() ? crm_get_current_user() : null;
$nav_role  = $nav_user['role']  ?? '';
$is_admin  = in_array($nav_role, ['super_admin','admin']);
$is_manager = in_array($nav_role, ['super_admin','admin','manager']);
$is_super  = ($nav_role === 'super_admin');

// صفحاتی که در تب‌بار موبایل اصلی نیستند و باید "بیشتر" را فعال نشان دهند
$secondary_pages = ['contacts', 'reports', 'users', 'plans', 'backup', 'support', 'profile'];
$is_on_secondary_page = in_array($page, $secondary_pages);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#FF6B35">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="robots" content="noindex, nofollow">
    <title>پیگیریو — CRM پیگیری مشتری</title>

    <!-- فونت: preconnect + لینک مستقیم به‌جای @import (رفع render-blocking) -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/forms.css">
    <link rel="stylesheet" href="assets/css/cards.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/nav.css">
    <link rel="stylesheet" href="assets/css/customers.css">
    <link rel="stylesheet" href="assets/css/utilities.css">
    <link rel="stylesheet" href="assets/css/auth.css">

    <style>
    /* ════════════════════════════════════════
       SHELL — index.php
       فقط ۲ سیستم ناوبری:
       - موبایل: تب‌بار پایین (۴ آیتم) + کشوی «بیشتر»
       - دسکتاپ: سایدبار ثابت (همیشه دیده می‌شود)
    ════════════════════════════════════════ */

    *, *::before, *::after { box-sizing: border-box; }
    html { height: 100%; -webkit-text-size-adjust: 100%; }
    body {
        min-height: 100%;
        min-height: 100dvh;
        display: flex;
        flex-direction: column;
        background: var(--paper);
        color: var(--ink);
        font-family: var(--font);
        padding-bottom: env(safe-area-inset-bottom);
    }

    /* ── Top bar (فقط موبایل — فقط لوگو، بدون اکشن) ── */
    .crm-topbar {
        position: sticky;
        top: 0;
        z-index: 200;
        background: var(--card);
        border-bottom: 1px solid var(--line);
        display: flex;
        align-items: center;
        padding: 0 16px;
        height: 54px;
        box-shadow: 0 1px 4px rgba(20,33,61,.06);
    }
    .crm-topbar-logo {
        font-size: 15px;
        font-weight: 800;
        color: var(--ink);
        text-decoration: none;
        letter-spacing: -.3px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .crm-logo-mark {
        width: 26px; height: 26px; border-radius: 8px;
        background: linear-gradient(135deg, var(--ember), var(--ember-deep));
        color: #fff; font-weight: 900; font-size: 13px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }

    /* ── Sidebar — هم drawer موبایل، هم ستون ثابت دسکتاپ ── */
    .crm-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.4);
        z-index: 299;
        opacity: 0;
        pointer-events: none;
        transition: opacity .25s;
    }
    .crm-overlay.show { opacity: 1; pointer-events: auto; }

    .crm-sidebar {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: min(280px, 85vw);
        background: var(--card);
        z-index: 300;
        display: flex;
        flex-direction: column;
        transform: translateX(100%);
        transition: transform .28s cubic-bezier(.4,0,.2,1);
        box-shadow: -4px 0 24px rgba(20,33,61,.12);
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: env(safe-area-inset-bottom);
    }
    .crm-sidebar.open { transform: translateX(0); }

    .crm-sb-head {
        padding: 16px 14px 14px;
        border-bottom: 1px solid var(--line);
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--paper);
        flex-shrink: 0;
    }
    .crm-sb-name { font-size: 13px; font-weight: 700; color: var(--ink); }
    .crm-sb-role { font-size: 11px; color: var(--ink-soft); margin-top: 2px; }
    .crm-sb-profile-link {
        display: flex; align-items: center; gap: 10px;
        flex: 1; min-width: 0; text-decoration: none;
        padding: 4px; margin: -4px; border-radius: 10px;
        transition: background .15s;
    }
    .crm-sb-profile-link:hover { background: var(--paper-2); }
    .crm-sb-avatar {
        width: 38px; height: 38px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 700; color: #fff; flex-shrink: 0;
    }
    .crm-sb-close {
        margin-right: auto;
        width: 30px; height: 30px; border: none;
        background: rgba(0,0,0,.05); border-radius: 50%;
        cursor: pointer; font-size: 15px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        -webkit-tap-highlight-color: transparent;
        color: var(--ink-soft);
    }

    .crm-sb-body { flex: 1; padding: 10px 0; }
    .crm-sb-group { margin-bottom: 4px; }
    .crm-sb-group-label {
        font-size: 10px; font-weight: 700; letter-spacing: .6px;
        color: var(--ink-soft); padding: 10px 16px 4px; text-transform: uppercase;
    }
    .crm-sb-link {
        display: flex; align-items: center; gap: 11px;
        padding: 11px 16px; font-size: 13px; color: var(--ink);
        text-decoration: none; transition: background .15s;
        position: relative; -webkit-tap-highlight-color: transparent;
    }
    .crm-sb-link:hover, .crm-sb-link:active { background: var(--paper-2); }
    .crm-sb-link.active {
        background: rgba(255,107,53,.08);
        color: var(--ember-deep);
        font-weight: 700;
    }
    .crm-sb-link.active::before {
        content: ''; position: absolute; right: 0; top: 6px; bottom: 6px; width: 3px;
        background: var(--ember); border-radius: 3px 0 0 3px;
    }
    .crm-sb-divider { height: 1px; background: var(--line); margin: 6px 0; }
    .crm-sb-icon { font-size: 17px; width: 24px; text-align: center; flex-shrink: 0; }
    .crm-sb-badge {
        margin-right: auto; background: var(--ember); color: #fff; font-size: 10.5px; font-weight: 800;
        padding: 1px 8px; border-radius: 10px; flex-shrink: 0;
    }

    .crm-sb-foot {
        padding: 10px 14px;
        border-top: 1px solid var(--line);
        background: var(--paper);
        flex-shrink: 0;
    }
    .crm-sb-logout {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; border-radius: 8px; font-size: 13px;
        color: var(--danger); text-decoration: none; transition: background .15s;
    }
    .crm-sb-logout:hover { background: #FCE8E6; }

    /* ── Bottom tab bar (فقط موبایل) ── */
    .crm-tabbar {
        position: fixed;
        bottom: 0; right: 0; left: 0;
        z-index: 190;
        background: var(--card);
        border-top: 1px solid var(--line);
        display: flex;
        padding-bottom: env(safe-area-inset-bottom);
        box-shadow: 0 -2px 10px rgba(20,33,61,.07);
    }
    .crm-tab-item {
        flex: 1;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 8px 4px 6px; font-size: 10px; color: var(--ink-soft);
        text-decoration: none; gap: 3px; transition: color .15s;
        min-width: 0; -webkit-tap-highlight-color: transparent; position: relative;
        background: none; border: none; cursor: pointer; font-family: inherit;
    }
    .crm-tab-item.active { color: var(--ember); }
    .crm-tab-item.active::after {
        content: ''; position: absolute; top: 0; left: 20%; right: 20%; height: 2px;
        background: var(--ember); border-radius: 0 0 2px 2px;
    }
    .crm-tab-icon { font-size: 20px; line-height: 1; }
    .crm-tab-label { font-size: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; text-align: center; }

    /* ── Main content ── */
    .crm-main {
        flex: 1;
        min-width: 0;
        padding: 14px;
        padding-bottom: calc(env(safe-area-inset-bottom) + 72px);
        background: var(--paper);
    }
    .crm-main { animation: crm-fadein .18s ease; }
    @keyframes crm-fadein { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }

    .crm-auth-wrap { flex: 1; display: flex; align-items: center; justify-content: center; padding: 20px; }

    /* ════════════════════════════════════════
       دسکتاپ (≥768px): سایدبار ثابت، بدون تب‌بار/تاپ‌بار
    ════════════════════════════════════════ */
    @media (min-width: 768px) {
        body { flex-direction: row; align-items: stretch; }

        .crm-topbar, .crm-tabbar, .crm-overlay { display: none !important; }

        .crm-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            transform: none !important;
            width: 240px;
            flex-shrink: 0;
            box-shadow: none;
            border-left: 1px solid var(--line);
        }
        .crm-sb-close { display: none; }

        .crm-main {
            padding: 24px 32px;
            padding-bottom: 24px;
            max-width: 1100px;
            margin: 0 auto;
            width: 100%;
        }
    }
    </style>
</head>
<script src="assets/js/jalali-picker.js"></script>
<body>

<?php if ($with_layout && $nav_user): ?>

<?php
$role_colors = ['super_admin'=>'#d97706','admin'=>'#1a73e8','manager'=>'#f97316','agent'=>'#34a853'];
$role_labels = ['super_admin'=>'سوپر ادمین','admin'=>'مدیر','manager'=>'مدیر فروش','agent'=>'کارشناس'];
$avatar_color = $role_colors[$nav_role] ?? '#1a73e8';
$role_label   = $role_labels[$nav_role] ?? $nav_role;
$avatar_initials = mb_substr($nav_user['full_name'] ?? 'U', 0, 1);

// آیتم‌های منو (یک منبع واحد — هم برای سایدبار دسکتاپ، هم کشوی موبایل)
$nav_groups = [
    [
        'label' => null,
        'items' => [
            ['page' => 'dashboard',  'icon' => '🏠', 'label' => 'داشبورد'],
            ['page' => 'customers',  'icon' => '🏢', 'label' => 'مشتریان'],
            ['page' => 'contacts',   'icon' => '👤', 'label' => 'مخاطبین'],
            ['page' => 'tasks',      'icon' => '✅', 'label' => 'فرصت‌های فروش'],
        ],
    ],
];
if ($is_manager) {
    $nav_groups[] = [
        'label' => 'گزارش‌ها',
        'items' => [
            ['page' => 'reports', 'action' => 'self',     'icon' => '📈', 'label' => 'گزارش شخصی'],
            ['page' => 'reports', 'action' => 'users',    'icon' => '👥', 'label' => 'گزارش کاربران'],
            ['page' => 'reports', 'action' => 'managers', 'icon' => '📊', 'label' => 'گزارش مدیران'],
        ],
    ];
}
if ($is_admin) {
    $nav_groups[] = [
        'label' => 'مدیریت',
        'items' => [
            ['page' => 'users',  'icon' => '👥', 'label' => 'کاربران'],
            ['page' => 'plans',  'icon' => '💳', 'label' => 'پلن و اشتراک'],
            ['page' => 'backup', 'icon' => '💾', 'label' => 'پشتیبان‌گیری'],
        ],
    ];
}
// «پروفایل من» و «پشتیبانی» برای همه نقش‌ها — همیشه آخرین گروه
// نکته: badge پشتیبانی برای همه‌ی کاربرها نشون داده می‌شه، نه فقط سوپر
// ادمین — سوپر ادمین تعداد تیکت‌هایی که پیام خوانده‌نشده دارن رو می‌بینه،
// و هر کاربر عادی تعداد تیکت‌های خودش که سوپر ادمین بهشون جواب تازه داده رو.
require_once __DIR__ . '/models/SupportTicket.php';
$support_badge = $is_super
    ? SupportTicket::countUnreadForAdmin()
    : SupportTicket::countUnreadForUser($nav_user['id']);
if ($support_badge <= 0) $support_badge = null;

$nav_groups[] = [
    'label' => 'حساب کاربری',
    'items' => [
        ['page' => 'profile', 'icon' => '👤', 'label' => 'پروفایل من'],
        ['page' => 'support', 'icon' => '🎫', 'label' => 'پشتیبانی', 'badge' => $support_badge],
    ],
];
?>

<!-- ══ TOP BAR (فقط موبایل) ══ -->
<header class="crm-topbar">
    <a href="index.php?page=dashboard" class="crm-topbar-logo">
        <span class="crm-logo-mark" aria-hidden="true">پ</span>
        پیگیریو
    </a>
</header>

<!-- ══ SIDEBAR OVERLAY (فقط موبایل) ══ -->
<div class="crm-overlay" id="crm-overlay"></div>

<!-- ══ SIDEBAR — یک منبع واحد ناوبری برای موبایل و دسکتاپ ══ -->
<aside class="crm-sidebar" id="crm-sidebar" aria-hidden="true">
    <div class="crm-sb-head">
        <a href="index.php?page=profile" class="crm-sb-profile-link" aria-label="پروفایل من">
            <div class="crm-sb-avatar" style="background:<?= $avatar_color ?>">
                <?= crm_sanitize($avatar_initials) ?>
            </div>
            <div>
                <div class="crm-sb-name"><?= crm_sanitize($nav_user['full_name']) ?></div>
                <div class="crm-sb-role"><?= crm_sanitize($role_label) ?></div>
            </div>
        </a>
        <button class="crm-sb-close" id="crm-sb-close" aria-label="بستن منو">✕</button>
    </div>

    <nav class="crm-sb-body" id="crm-sb-nav" aria-label="ناوبری اصلی">
        <?php foreach ($nav_groups as $group): ?>
            <?php if ($group['label']): ?>
                <div class="crm-sb-divider"></div>
                <div class="crm-sb-group-label"><?= crm_sanitize($group['label']) ?></div>
            <?php endif; ?>
            <div class="crm-sb-group">
                <?php foreach ($group['items'] as $item):
                    $href = 'index.php?page=' . $item['page'] . (!empty($item['action']) ? '&action=' . $item['action'] : '');
                    $is_active = ($page === $item['page']) && (empty($item['action']) || ($_GET['action'] ?? '') === $item['action']);
                ?>
                <a href="<?= $href ?>" class="crm-sb-link <?= $is_active ? 'active' : '' ?>" <?= $is_active ? 'aria-current="page"' : '' ?>>
                    <span class="crm-sb-icon" aria-hidden="true"><?= $item['icon'] ?></span>
                    <?= crm_sanitize($item['label']) ?>
                    <?php if (!empty($item['badge'])): ?>
                    <span class="crm-sb-badge"><?= (int)$item['badge'] ?></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </nav>

    <div class="crm-sb-foot">
        <a href="index.php?page=auth&action=logout" class="crm-sb-logout">
            <span aria-hidden="true">🚪</span> خروج از سیستم
        </a>
    </div>
</aside>

<?php endif; /* with_layout */ ?>

<!-- ══ MAIN CONTENT ══ -->
<?php if ($with_layout && $nav_user): ?>
<main class="crm-main">
<?php else: ?>
<div class="crm-auth-wrap">
<?php endif; ?>

    <?php if (file_exists($controller_file)): ?>
        <?php include $controller_file; ?>
    <?php else: ?>
        <div style="text-align:center;padding:40px;color:var(--ink-soft)">
            <div style="font-size:15px;font-weight:600">صفحه یافت نشد</div>
            <div style="font-size:12px;margin-top:6px;color:#9e9e9e"><?= htmlspecialchars($controller_file) ?></div>
            <a href="index.php" style="display:inline-block;margin-top:16px;color:var(--blue);font-size:13px">بازگشت به داشبورد</a>
        </div>
    <?php endif; ?>

<?php if ($with_layout && $nav_user): ?>
</main>
<?php else: ?>
</div>
<?php endif; ?>

<!-- ══ BOTTOM TAB BAR (فقط موبایل) ══ -->
<?php if ($with_layout && $nav_user): ?>
<nav class="crm-tabbar" aria-label="ناوبری پایین">
    <a href="index.php?page=dashboard" class="crm-tab-item <?= $page==='dashboard'?'active':'' ?>" <?= $page==='dashboard' ? 'aria-current="page"' : '' ?>>
        <span class="crm-tab-icon" aria-hidden="true">🏠</span>
        <span class="crm-tab-label">داشبورد</span>
    </a>
    <a href="index.php?page=customers" class="crm-tab-item <?= $page==='customers'?'active':'' ?>" <?= $page==='customers' ? 'aria-current="page"' : '' ?>>
        <span class="crm-tab-icon" aria-hidden="true">🏢</span>
        <span class="crm-tab-label">مشتریان</span>
    </a>
    <a href="index.php?page=tasks" class="crm-tab-item <?= $page==='tasks'?'active':'' ?>" <?= $page==='tasks' ? 'aria-current="page"' : '' ?>>
        <span class="crm-tab-icon" aria-hidden="true">✅</span>
        <span class="crm-tab-label">فرصت‌های فروش</span>
    </a>
    <button type="button" class="crm-tab-item <?= $is_on_secondary_page ? 'active' : '' ?>" id="crm-more-btn" aria-haspopup="true" aria-expanded="false">
        <span class="crm-tab-icon" aria-hidden="true">⋯</span>
        <span class="crm-tab-label">بیشتر</span>
    </button>
</nav>

<!-- ══ JAVASCRIPT ════════════════════════════ -->
<script>
(function () {
    var moreBtn = document.getElementById('crm-more-btn');
    var sidebar = document.getElementById('crm-sidebar');
    var overlay = document.getElementById('crm-overlay');
    var sbClose = document.getElementById('crm-sb-close');
    var open    = false;

    function openSB() {
        open = true;
        sidebar.classList.add('open');
        overlay.classList.add('show');
        sidebar.setAttribute('aria-hidden', 'false');
        if (moreBtn) moreBtn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeSB() {
        open = false;
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        sidebar.setAttribute('aria-hidden', 'true');
        if (moreBtn) moreBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    if (moreBtn) moreBtn.addEventListener('click', function () { open ? closeSB() : openSB(); });
    overlay.addEventListener('click', closeSB);
    sbClose.addEventListener('click', closeSB);

    // بستن با swipe به راست (فقط موبایل)
    var startX = 0;
    sidebar.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    sidebar.addEventListener('touchend', function (e) {
        if (e.changedTouches[0].clientX - startX > 60) closeSB();
    }, { passive: true });

    // بستن با Escape
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && open) closeSB(); });

    // بستن با کلیک روی هر لینک سایدبار (فقط در حالت drawer موبایل تأثیر دارد)
    var links = document.querySelectorAll('.crm-sb-link');
    links.forEach(function (l) {
        l.addEventListener('click', function () {
            if (window.innerWidth < 768) closeSB();
        });
    });
})();
</script>

<?php endif; /* with_layout && nav_user */ ?>

<script src="assets/js/app.js"></script>
</body>
</html>