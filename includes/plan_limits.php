<?php
// includes/plan_limits.php
// لایه‌ی مرکزی «سقف پلن». هر جای پروژه که قبل از ساختن یک رکورد
// (مشتری/مخاطب/کاربر) یا گرفتن بکاپ باید چک شود که آیا شرکت به سقف
// تعرفه‌اش رسیده یا نه، از این توابع استفاده می‌کند:
//
//   crm_require_plan_limit('customers')  → null اگر مجاز، یا HTML یک باکس آماده با دکمه
//   crm_check_backup_allowed()           → ['ok' => bool, 'message' => متن ساده]
//
// این فایل باید یک‌بار در ابتدای اجرا include شود — ساده‌ترین راه اضافه
// کردن همین یک خط به انتهای includes/helpers.php است:
//
//   require_once __DIR__ . '/plan_limits.php';

require_once __DIR__ . '/../models/PlanTier.php';

/**
 * تعرفه‌ی فعلیِ شرکتِ کاربر (بر اساس ادمین/ریشه‌ی سلسله‌مراتب سازمان).
 * اگر شرکتی هنوز هیچ تعرفه‌ای انتخاب نکرده (رکورد قدیمی، قبل از این
 * مهاجرت)، null برمی‌گردد و به‌معنای «بدون محدودیت» تلقی می‌شود — تا
 * کاربرهای موجود قبل از فعال‌سازی این سیستم قفل نشوند.
 */
function crm_get_user_plan_tier($user = null) {
    $user = $user ?: crm_get_current_user();
    if (!$user) return null;

    $pdo = getDB();
    $root_id = crm_get_company_root($user['id']);
    $stmt = $pdo->prepare("SELECT plan_tier_id FROM users WHERE id = ?");
    $stmt->execute([$root_id]);
    $tier_id = $stmt->fetchColumn();

    return $tier_id ? PlanTier::getById($tier_id) : null;
}

/**
 * باکس آماده‌ی «سقف پلن پر شده» — آیکون + پیام + دکمه‌ی «ارتقای اشتراک».
 * کاملاً self-contained (استایل inline) تا هرجای پروژه درج شود درست
 * دیده شود. کلاس crm-upgrade-box روی خودش هست تا ویوها بتوانند
 * تشخیصش بدهند و آن را بدون wrapper اضافه (مثل alert قرمز فرم‌ها) چاپ کنند.
 */
function crm_render_upgrade_box($message, $current = null, $max = null) {
    $meter = '';
    if ($current !== null && $max !== null) {
        $meter = '<div style="font-size:11.5px;color:#8C5A2E;margin-bottom:12px">مصرف فعلی: <strong>' . (int)$current . ' از ' . (int)$max . '</strong></div>';
    }
    $safe_message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    return '<div class="crm-upgrade-box" style="'
        . 'background:#FFF8F4;border:1.5px solid #FFD9C2;border-radius:14px;'
        . 'padding:22px 18px;margin-bottom:16px;text-align:center;">'
        . '<div style="font-size:30px;line-height:1;margin-bottom:8px">🚀</div>'
        . '<div style="font-size:14.5px;font-weight:800;color:#14213D;margin-bottom:6px">سقف پلن فعلی شما پر شده است</div>'
        . '<div style="font-size:12.5px;color:#4A5578;line-height:1.8;margin-bottom:10px">' . $safe_message . '</div>'
        . $meter
        . '<a href="index.php?page=plans" style="'
        . 'display:inline-flex;align-items:center;gap:7px;background:var(--ember,#FF6B35);color:#fff;'
        . 'font-size:13px;font-weight:700;padding:11px 24px;border-radius:10px;text-decoration:none;'
        . 'box-shadow:0 4px 14px rgba(255,107,53,.3);">⬆️ ارتقای اشتراک</a>'
        . '</div>';
}

/**
 * $limit_type: 'users' | 'customers' | 'contacts'
 * برمی‌گرداند: ['ok' => bool, 'message' => متن ساده (بدون HTML), 'current' => int, 'max' => int|null]
 */
function crm_check_plan_limit($limit_type, $user = null) {
    $user = $user ?: crm_get_current_user();
    $tier = crm_get_user_plan_tier($user);
    if (!$tier) return ['ok' => true, 'current' => null, 'max' => null];

    $field = 'max_' . $limit_type;
    if (!array_key_exists($field, $tier)) return ['ok' => true, 'current' => null, 'max' => null];

    $max = $tier[$field];
    if ($max === null) return ['ok' => true, 'current' => null, 'max' => null]; // نامحدود

    $pdo = getDB();
    $root_id = crm_get_company_root($user['id']);
    $company_ids = crm_get_company_members($root_id);
    if (empty($company_ids)) $company_ids = [(int)$user['id']];
    $in = implode(',', array_fill(0, count($company_ids), '?'));

    if ($limit_type === 'users') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id IN ($in) AND status = 'active'");
    } elseif ($limit_type === 'customers') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE user_id IN ($in) AND status = 'active'");
    } elseif ($limit_type === 'contacts') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE user_id IN ($in) AND status = 'active'");
    } else {
        return ['ok' => true, 'current' => null, 'max' => null];
    }
    $stmt->execute($company_ids);
    $count = (int)$stmt->fetchColumn();

    $nouns = ['users' => 'کاربران فعال', 'customers' => 'مشتریان', 'contacts' => 'مخاطبین'];

    if ($count >= (int)$max) {
        return [
            'ok' => false,
            'message' => "شما در پلن «{$tier['name']}» به سقف {$nouns[$limit_type]} رسیده‌اید.",
            'current' => $count,
            'max' => (int)$max,
        ];
    }
    return ['ok' => true, 'current' => $count, 'max' => (int)$max];
}

/**
 * نسخه‌ی راحت برای استفاده مستقیم در کنترلرها:
 *   if ($limit_box = crm_require_plan_limit('customers')) { $error = $limit_box; ... }
 * خروجی: null اگر مجاز است، وگرنه HTML کامل باکس ارتقا (آماده‌ی چاپ).
 */
function crm_require_plan_limit($limit_type, $user = null) {
    $check = crm_check_plan_limit($limit_type, $user);
    if ($check['ok']) return null;
    return crm_render_upgrade_box($check['message'], $check['current'], $check['max']);
}

/**
 * بررسی سقف بکاپ. پلن «یک بکاپ رایگان در ماه» را با شمارش ردیف‌های
 * backup_logs در ماه جاری اجرا می‌کند؛ پلن‌های دیگر نامحدودند.
 */
function crm_check_backup_allowed($user = null) {
    $user = $user ?: crm_get_current_user();
    $tier = crm_get_user_plan_tier($user);
    if (!$tier || $tier['backup_access'] === 'unlimited') {
        return ['ok' => true];
    }

    $pdo = getDB();
    $root_id = crm_get_company_root($user['id']);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM backup_logs WHERE company_root_id = ? AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00')");
    $stmt->execute([$root_id]);
    $used = (int)$stmt->fetchColumn();

    if ($used >= 1) {
        return [
            'ok' => false,
            'message' => "در پلن «{$tier['name']}» فقط یک بکاپ رایگان در ماه مجاز است.",
        ];
    }
    return ['ok' => true];
}

/** بعد از هر بکاپ موفق صدا زده می‌شود تا مصرف ماه جاری ثبت شود. */
function crm_log_backup_usage($user = null, $type = 'excel') {
    $user = $user ?: crm_get_current_user();
    $pdo = getDB();
    $root_id = crm_get_company_root($user['id']);
    $stmt = $pdo->prepare("INSERT INTO backup_logs (company_root_id, user_id, type) VALUES (?, ?, ?)");
    $stmt->execute([$root_id, $user['id'], $type]);
}

/** آیا تعرفه‌ی فعلی به گزارش‌گیری مدیریتی (تب «کاربران»/«مدیران») دسترسی دارد؟ */
function crm_has_management_reports($user = null) {
    $user = $user ?: crm_get_current_user();
    $tier = crm_get_user_plan_tier($user);
    if (!$tier) return true; // شرکت‌های قدیمی بدون تعرفه: بدون محدودیت
    return (bool)$tier['management_reports'];
}