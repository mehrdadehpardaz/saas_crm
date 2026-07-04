<?php
// controllers/AuthController.php

$error = '';
$success = '';
$mode = $_GET['mode'] ?? 'login';
$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    session_unset();
    session_destroy();
    crm_redirect('landing.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    crm_csrf_verify();
    $mobile = crm_sanitize($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';

    $mobile = crm_validate_mobile($mobile);
    
    if (!$mobile) {
        $error = 'شماره موبایل معتبر نیست. مثال: 09123456789';
        if ($mode === 'login') { $_SESSION['login_error'] = $error; crm_redirect('landing.php'); }
    }
    elseif (strlen($password) < 6) {
        $error = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
        if ($mode === 'login') { $_SESSION['login_error'] = $error; crm_redirect('landing.php'); }
    }
    else {
        $pdo = getDB();

        if ($mode === 'register') {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ?");
            $stmt->execute([$mobile]);
            if ($stmt->fetch()) {
                $error = 'این شماره قبلاً ثبت شده است.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                
                $full_name = crm_sanitize($_POST['full_name'] ?? 'کاربر جدید');
                $company_name = crm_sanitize($_POST['company_name'] ?? '');
                
                // اگر نام شرکت خالی بود، نام فرد رو به عنوان شرکت بذار
                if (empty($company_name)) {
                    $company_name = $full_name;
                }

                // ساخت/پیدا کردن ردیف واقعی شرکت در جدول companies
                $company_id = crm_get_or_create_company_id($company_name);
                
                $stmt = $pdo->prepare("INSERT INTO users (mobile, password, full_name, company_name, company_id, position_title, role, plan_type, plan_expiry, max_users_limit, status) 
                                       VALUES (?, ?, ?, ?, ?, 'مدیر', 'admin', 'trial', DATE_ADD(NOW(), INTERVAL 14 DAY), 5, 'active')");
                $stmt->execute([$mobile, $hashed, $full_name, $company_name, $company_id]);
                
                $_SESSION['user_id'] = (int)$pdo->lastInsertId();
                crm_redirect('index.php?page=dashboard');
            }
        }
        elseif ($mode === 'login') {
            // Rate limiting: حداکثر ۵ بار در ۵ دقیقه
            $rate_key = 'login_' . ($mobile ?: ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            if (!crm_rate_limit_check($rate_key, 5, 300)) {
                $wait = crm_rate_limit_remaining($rate_key, 300);
                $error = "تعداد تلاش‌های ناموفق زیاد است. لطفاً $wait ثانیه صبر کنید.";
                $_SESSION['login_error'] = $error;
                crm_redirect('landing.php');
            } else {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE mobile = ?");
                $stmt->execute([$mobile]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // ریست rate limit بعد از لاگین موفق
                    unset($_SESSION['rl_' . md5($rate_key)]);
                    $_SESSION['user_id'] = (int)$user['id'];
                    crm_redirect('index.php?page=dashboard');
                } else {
                    $error = 'شماره موبایل یا رمز عبور اشتباه است.';
                    $_SESSION['login_error'] = $error;
                    crm_redirect('landing.php');
                }
            }
        }
    }
}

include __DIR__ . '/../Views/auth.php';