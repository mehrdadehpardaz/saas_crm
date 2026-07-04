<?php
// controllers/PlansController.php
ob_start();

require_once __DIR__ . '/../models/Plan.php';

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
    if ($action === 'buy') {
        $user_count = (int)($_POST['user_count'] ?? 1);
        $period = $_POST['period'] ?? 'monthly';
        $mode = $_POST['mode'] ?? 'full'; // full, upgrade, renew
        
        $pdo = getDB();
        $current_max = (int)$user['max_users_limit'];
        $current_expiry = strtotime($user['plan_expiry']);
        $now = time();
        $is_active = $current_expiry > $now && ($user['status'] ?? 'active') === 'active';
        $company = $user['company_name'] ?? '';
        
        if ($mode === 'upgrade') {
            // فقط کاربر اضافه برای مدت باقی‌مانده
            if ($user_count <= $current_max) {
                $error = 'تعداد کاربران باید بیشتر از سقف فعلی باشد.';
            } else {
                $months_left = ceil(($current_expiry - $now) / (30 * 24 * 3600));
                if ($months_left < 1) $months_left = 1;
                
                $extra = $user_count - $current_max;
                $perM = (int)Plan::getByType('per_user')['price_monthly'];
                $price = $extra * $perM * $months_left;
                $max_users = $user_count;
                
                // آپدیت admin
                $pdo->prepare("UPDATE users SET max_users_limit = ?, credit = credit + ? WHERE id = ?")
                    ->execute([$max_users, $price, $user['id']]);
                
                // فعال کردن زیرمجموعه‌ها بر اساس سقف جدید
                if (!empty($company)) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE company_name = ? AND status = 'active' AND id != ?");
                    $stmt->execute([$company, $user['id']]);
                    $active = $stmt->fetchColumn();
                    
                    $slots = $max_users - $active;
                    
                    if ($slots > 0) {
                        $stmt = $pdo->prepare("UPDATE users SET status = 'active', plan_expiry = (SELECT plan_expiry FROM users WHERE company_name = ? AND role = 'admin' LIMIT 1) WHERE company_name = ? AND role = 'manager' AND status = 'inactive' ORDER BY created_at DESC LIMIT ?");
                        $stmt->execute([$company, $company, $slots]);
                        $slots -= $stmt->rowCount();
                    }
                    
                    if ($slots > 0) {
                        $stmt = $pdo->prepare("UPDATE users SET status = 'active', plan_expiry = (SELECT plan_expiry FROM users WHERE company_name = ? AND role = 'admin' LIMIT 1) WHERE company_name = ? AND role = 'agent' AND status = 'inactive' ORDER BY created_at DESC LIMIT ?");
                        $stmt->execute([$company, $company, $slots]);
                    }
                }
            }
        }
        elseif ($mode === 'renew') {
            // تمدید
            if ($user_count < $current_max) {
                $error = 'تعداد کاربران نمیتواند کمتر از سقف فعلی باشد.';
            } else {
                $days = ($period === 'yearly') ? 365 : 30;
                
                // هزینه تمدید با تعداد جدید
                $price_full = Plan::calculatePrice($user_count, $period);
                
                if ($user_count > $current_max) {
                    // تمدید + کاربر اضافه
                    $months_left = ceil(($current_expiry - $now) / (30 * 24 * 3600));
                    if ($months_left < 1) $months_left = 1;
                    
                    $extra = $user_count - $current_max;
                    $perM = (int)Plan::getByType('per_user')['price_monthly'];
                    $price = $price_full + ($extra * $perM * $months_left);
                } else {
                    $price = $price_full;
                }
                $max_users = $user_count;
                
                // آپدیت admin
                $pdo->prepare("UPDATE users SET plan_type = ?, max_users_limit = ?, credit = credit + ?, plan_expiry = DATE_ADD(plan_expiry, INTERVAL ? DAY), status = 'active' WHERE id = ?")
                    ->execute([$period, $max_users, $price, $days, $user['id']]);
                
                // فعال کردن زیرمجموعه‌ها بر اساس سقف جدید
                if (!empty($company)) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE company_name = ? AND status = 'active' AND id != ?");
                    $stmt->execute([$company, $user['id']]);
                    $active = $stmt->fetchColumn();
                    
                    $slots = $max_users - $active;
                    
                    if ($slots > 0) {
                        $stmt = $pdo->prepare("UPDATE users SET status = 'active', plan_expiry = (SELECT plan_expiry FROM users WHERE company_name = ? AND role = 'admin' LIMIT 1) WHERE company_name = ? AND role = 'manager' AND status = 'inactive' ORDER BY created_at DESC LIMIT ?");
                        $stmt->execute([$company, $company, $slots]);
                        $slots -= $stmt->rowCount();
                    }
                    
                    if ($slots > 0) {
                        $stmt = $pdo->prepare("UPDATE users SET status = 'active', plan_expiry = (SELECT plan_expiry FROM users WHERE company_name = ? AND role = 'admin' LIMIT 1) WHERE company_name = ? AND role = 'agent' AND status = 'inactive' ORDER BY created_at DESC LIMIT ?");
                        $stmt->execute([$company, $company, $slots]);
                    }
                }
            }
        }
        else {
            // خرید کامل (منقضی شده)
            $price = Plan::calculatePrice($user_count, $period);
            $max_users = $user_count;
            $days = ($period === 'yearly') ? 365 : 30;
            
            // آپدیت admin
            $pdo->prepare("UPDATE users SET plan_type = ?, max_users_limit = ?, credit = credit + ?, plan_expiry = DATE_ADD(NOW(), INTERVAL ? DAY), status = 'active' WHERE id = ?")
                ->execute([$period, $max_users, $price, $days, $user['id']]);
            
            // فعال کردن زیرمجموعه‌ها بر اساس سقف جدید
            if (!empty($company)) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE company_name = ? AND status = 'active' AND id != ?");
                $stmt->execute([$company, $user['id']]);
                $active = $stmt->fetchColumn();
                
                $slots = $max_users - $active;
                
                if ($slots > 0) {
                    $stmt = $pdo->prepare("UPDATE users SET status = 'active', plan_expiry = (SELECT plan_expiry FROM users WHERE company_name = ? AND role = 'admin' LIMIT 1) WHERE company_name = ? AND role = 'manager' AND status = 'inactive' ORDER BY created_at DESC LIMIT ?");
                    $stmt->execute([$company, $company, $slots]);
                    $slots -= $stmt->rowCount();
                }
                
                if ($slots > 0) {
                    $stmt = $pdo->prepare("UPDATE users SET status = 'active', plan_expiry = (SELECT plan_expiry FROM users WHERE company_name = ? AND role = 'admin' LIMIT 1) WHERE company_name = ? AND role = 'agent' AND status = 'inactive' ORDER BY created_at DESC LIMIT ?");
                    $stmt->execute([$company, $company, $slots]);
                }
            }
        }
        
        // آپدیت company_name اگر وارد شده
        if (!empty($_POST['company_name'])) {
            $cn = crm_sanitize($_POST['company_name']);
            $cid = crm_get_or_create_company_id($cn);
            $pdo->prepare("UPDATE users SET company_name = ?, company_id = ? WHERE id = ?")->execute([$cn, $cid, $user['id']]);
        }
        
        if (empty($error)) {
            // رفرش session
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php?page=plans&msg=purchased');
            exit;
        }
    }
}

// ========== نمایش ==========
$message = $_GET['msg'] ?? '';

// لیست پلن‌ها
if ($action === 'list' || !$action) {
    $plans = Plan::getAll();
    
    $active_users_count = 0;
    if ($is_admin || $is_super) {
        $pdo = getDB();
        if ($is_super) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
            $active_users_count = $stmt->fetchColumn();
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE company_name = ? AND status = 'active'");
            $stmt->execute([$user['company_name']]);
            $active_users_count = $stmt->fetchColumn();
        }
    }
    
    include __DIR__ . '/../Views/plans/list.php';
}
// صفحه خرید
elseif ($action === 'buy') {
    include __DIR__ . '/../Views/plans/buy.php';
}
// پیش‌فرض
else {
    header('Location: index.php?page=plans');
    exit;
}

ob_end_flush();