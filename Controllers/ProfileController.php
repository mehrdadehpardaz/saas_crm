<?php
// controllers/ProfileController.php
// صفحه پروفایل شخصی — برای همه نقش‌ها (super_admin, admin, manager, agent)
// همیشه فقط روی کاربر لاگین‌شده (session) عمل می‌کند، هیچ ID از URL نمی‌گیرد
// پس به‌طور ذاتی در برابر IDOR ایمن است.
if (!defined('CRM_APP')) { http_response_code(403); exit('Direct access denied'); }

$user = crm_get_current_user();
if (!$user) {
    crm_redirect('landing.php');
}

$error = '';
$success = '';
$pwd_error = '';
$pwd_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    crm_csrf_verify();
    $form = $_POST['form'] ?? '';

    // ── ویرایش اطلاعات پایه (نام، سمت، تلفن) ──
    // عمداً موبایل/شرکت/نقش اینجا قابل تغییر نیستند چون این‌ها مسیر ورود
    // و دسترسی کاربر را تعیین می‌کنند و باید فقط توسط ادمین مدیریت شوند.
    if ($form === 'profile') {
        $full_name      = trim($_POST['full_name'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $position_title = trim($_POST['position_title'] ?? '');

        if (empty($full_name)) {
            $error = 'نام و نام خانوادگی الزامی است.';
        } else {
            $pdo = getDB();
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, position_title = ? WHERE id = ?");
            $stmt->execute([$full_name, $phone, $position_title, $user['id']]);
            $success = 'اطلاعات با موفقیت بروزرسانی شد.';
            $user = crm_get_current_user(); // بازخوانی اطلاعات تازه
        }
    }

    // ── تغییر رمز عبور ──
    if ($form === 'password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password     = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // محدودیت تلاش — جلوگیری از حدس‌زنی رمز فعلی با درخواست‌های پیاپی
        $rate_key = 'pwdchange_' . $user['id'];
        if (!crm_rate_limit_check($rate_key, 5, 300)) {
            $wait = crm_rate_limit_remaining($rate_key, 300);
            $pwd_error = "تعداد تلاش‌های ناموفق زیاد است. لطفاً $wait ثانیه صبر کنید.";
        } elseif (!password_verify($current_password, $user['password'])) {
            $pwd_error = 'رمز عبور فعلی اشتباه است.';
        } elseif (strlen($new_password) < 6) {
            $pwd_error = 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد.';
        } elseif ($new_password !== $confirm_password) {
            $pwd_error = 'رمز عبور جدید و تکرار آن یکسان نیستند.';
        } elseif ($current_password === $new_password) {
            $pwd_error = 'رمز عبور جدید نمی‌تواند با رمز فعلی یکسان باشد.';
        } else {
            $pdo = getDB();
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user['id']]);

            // ریست محدودیت تلاش بعد از موفقیت
            unset($_SESSION['rl_' . md5($rate_key)]);
            $pwd_success = 'رمز عبور با موفقیت تغییر کرد.';
        }
    }
}

$role_labels = ['super_admin' => 'سوپر ادمین', 'admin' => 'مدیر', 'manager' => 'مدیر فروش', 'agent' => 'کارشناس'];
$role_label  = $role_labels[$user['role']] ?? $user['role'];

include __DIR__ . '/../Views/profile/index.php';