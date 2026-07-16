<?php
// controllers/PlansController.php
// نسخه‌ی جدید — سه تعرفه‌ی ثابت (plan_tiers) به‌جای مدل «پایه + هر کاربر».
ob_start();

require_once __DIR__ . '/../models/PlanTier.php';

$user = crm_get_current_user();
if (!in_array($user['role'], ['super_admin', 'admin'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

$is_admin = in_array($user['role'], ['super_admin', 'admin']);
$is_super = ($user['role'] === 'super_admin');
$action = $_GET['action'] ?? 'list';
$error = '';

// ========== POST ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    crm_csrf_verify();

    // خرید / ارتقای پلن — انتخاب یکی از سه تعرفه‌ی ثابت
    if ($action === 'select') {
        $tier_id = (int)($_POST['tier_id'] ?? 0);
        $period  = ($_POST['period'] ?? 'monthly') === 'yearly' ? 'yearly' : 'monthly';
        $tier = PlanTier::getById($tier_id);

        if (!$tier) {
            $error = 'پلن انتخابی معتبر نیست.';
        } else {
            $pdo = getDB();
            $days = ($period === 'yearly') ? 365 : 30;
            // سالانه = ۱۰ برابر ماهانه (معادل ۲ ماه رایگان) — هماهنگ با بقیه پروژه
            $price = ($period === 'yearly') ? $tier['price_monthly'] * 10 : $tier['price_monthly'];

            // سقف کاربرانِ ستون قدیمی max_users_limit (که بقیه‌ی سیستم — مثلاً
            // فعال/غیرفعال‌سازی خودکار در index.php — به آن وابسته است) با
            // سقف تعرفه هماهنگ می‌شود. برای تعرفه‌ی نامحدود عدد بزرگ می‌گذاریم
            // چون آن ستون NOT NULL است.
            $new_max_users = $tier['max_users'] !== null ? (int)$tier['max_users'] : 999999;

            $stmt = $pdo->prepare("UPDATE users
                SET plan_tier_id = ?, max_users_limit = ?, credit = credit + ?,
                    plan_expiry = DATE_ADD(GREATEST(plan_expiry, NOW()), INTERVAL ? DAY),
                    status = 'active'
                WHERE id = ?");
            $stmt->execute([$tier['id'], $new_max_users, $price, $days, $user['id']]);

            $_SESSION['user_id'] = $user['id']; // رفرش session
            header('Location: index.php?page=plans&msg=purchased');
            exit;
        }
    }

    // ویرایش تعرفه‌ها — فقط سوپر ادمین (اعداد قابل‌تغییر)
    if ($action === 'update_tier' && $is_super) {
        $tid = (int)($_POST['id'] ?? 0);
        if ($tid) {
            PlanTier::update($tid, [
                'name'               => $_POST['name'] ?? '',
                'price_monthly'      => $_POST['price_monthly'] ?? 0,
                'max_users'          => $_POST['max_users'] ?? '',
                'max_customers'      => $_POST['max_customers'] ?? '',
                'max_contacts'       => $_POST['max_contacts'] ?? '',
                'backup_access'      => $_POST['backup_access'] ?? 'unlimited',
                'management_reports' => $_POST['management_reports'] ?? '',
                'full_access'        => $_POST['full_access'] ?? '',
            ]);
        }
        header('Location: index.php?page=plans&action=tiers&msg=tier_updated');
        exit;
    }
}

// ========== نمایش ==========
$message = $_GET['msg'] ?? '';

// صفحه‌ی مدیریت تعرفه‌ها — فقط سوپر ادمین
if ($action === 'tiers') {
    if (!$is_super) { header('Location: index.php?page=plans'); exit; }
    $tiers = PlanTier::getAll();
    include __DIR__ . '/../Views/plans/tiers_edit.php';
}
// صفحه‌ی اصلی: وضعیت فعلی + سه کارت تعرفه + نوار مصرف
else {
    $tiers = PlanTier::getAll();
    $current_tier = crm_get_user_plan_tier($user);
    $usage = [
        'users'     => crm_check_plan_limit('users', $user),
        'customers' => crm_check_plan_limit('customers', $user),
        'contacts'  => crm_check_plan_limit('contacts', $user),
    ];
    include __DIR__ . '/../Views/plans/list.php';
}

ob_end_flush();