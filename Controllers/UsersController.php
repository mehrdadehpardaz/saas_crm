<?php
// controllers/UsersController.php
ob_start();

$user = crm_get_current_user();
if (!$user || !in_array($user['role'], ['super_admin', 'admin', 'manager'])) {
    echo '<div class="alert alert-error">⛔ دسترسی غیرمجاز</div>';
    exit;
}

$is_admin = ($user['role'] === 'admin' || $user['role'] === 'super_admin');
$is_super = ($user['role'] === 'super_admin');
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$error = '';

// ========== POST ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    crm_csrf_verify();
    // ایجاد کاربر جدید (زیرمجموعه)
    if ($action === 'create') {
        $mobile = crm_validate_mobile($_POST['mobile'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $position_title = trim($_POST['position_title'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = $_POST['role'] ?? 'agent';
        
        if ($is_super) {
            $company_name = trim($_POST['company_name'] ?? '');
            $company_id = crm_get_or_create_company_id($company_name);
        } else {
            $company_name = $user['company_name'] ?? null;
            $company_id = $user['company_id'] ?? null;
        }
        
        if (!$is_super) {
            if ($role !== 'agent' && $role !== 'manager') {
                $role = 'agent';
            }
            if (!$is_admin && $role === 'manager') {
                $role = 'agent';
            }
        }
        
        if (!$mobile) {
            $error = 'شماره موبایل معتبر نیست.';
        } elseif (strlen($password) < 6) {
            $error = 'رمز عبور حداقل ۶ کاراکتر.';
        } elseif (empty($full_name)) {
            $error = 'نام و نام خانوادگی الزامی است.';
        } else {
            $pdo = getDB();
            
            $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ?");
            $stmt->execute([$mobile]);
            if ($stmt->fetch()) {
                $error = 'این شماره قبلاً ثبت شده.';
            } else {
                
                // چک سقف کاربران
                if (!$is_super && !empty($company_name)) {
                    $stmt = $pdo->prepare("SELECT id, max_users_limit FROM users WHERE company_name = ? AND role IN ('admin', 'super_admin') ORDER BY role LIMIT 1");
                    $stmt->execute([$company_name]);
                    $company_admin = $stmt->fetch();
                    
                    if ($company_admin) {
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE company_name = ? AND status = 'active'");
                        $stmt->execute([$company_name]);
                        $active_count = $stmt->fetchColumn();
                        
                        if ($active_count >= $company_admin['max_users_limit']) {
                            $error = '⛔ سقف کاربران فعال (' . $company_admin['max_users_limit'] . ' نفر) پر شده است.';
                            include __DIR__ . '/../Views/users/form.php';
                            exit;
                        }
                    }
                }
                
                if (empty($error)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (mobile, password, full_name, company_name, company_id, position_title, phone, role, parent_id, plan_type, plan_expiry, status) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'trial', DATE_ADD(NOW(), INTERVAL 14 DAY), 'active')");
                    $stmt->execute([$mobile, $hashed, $full_name, $company_name, $company_id, $position_title, $phone, $role, $user['id']]);
                    
                    header('Location: index.php?page=users&msg=created');
                    exit;
                }
            }
        }
    }
    
    // ویرایش کاربر
    if ($action === 'update' && $id) {
        $full_name = trim($_POST['full_name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $position_title = trim($_POST['position_title'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $status_new = $_POST['status'] ?? 'active';
        $new_role = $_POST['role'] ?? null;
        
        $pdo = getDB();
        
        // گرفتن اطلاعات فعلی کاربر
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $edit_user_data = $stmt->fetch();
        
        if (!$edit_user_data) {
            echo '<div class="alert alert-error">کاربر یافت نشد.</div>';
            echo '<a href="index.php?page=users" class="btn">بازگشت</a>';
            exit;
        }
        
        // چک دسترسی
        $can_edit = false;
        if ($is_super) {
            $can_edit = true;
        } elseif ($is_admin) {
            $can_edit = true;
        } elseif ($user['role'] === 'manager' && $edit_user_data['parent_id'] == $user['id']) {
            $can_edit = true;
        } elseif ($id == $user['id']) {
            $can_edit = true;
        }
        
        if (!$can_edit) {
            echo '<div class="alert alert-error">⛔ دسترسی غیرمجاز</div>';
            echo '<a href="index.php?page=users" class="btn">بازگشت</a>';
            exit;
        }
        
        // چک سقف قبل از فعال‌سازی
        if ($status_new === 'active' && $edit_user_data['status'] === 'inactive' && !$is_super) {
            $company = $edit_user_data['company_name'] ?? $user['company_name'];
            if (!empty($company)) {
                $stmt = $pdo->prepare("SELECT id, max_users_limit FROM users WHERE company_name = ? AND role IN ('admin', 'super_admin') ORDER BY role LIMIT 1");
                $stmt->execute([$company]);
                $company_admin = $stmt->fetch();
                
                if ($company_admin) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE company_name = ? AND status = 'active'");
                    $stmt->execute([$company]);
                    $active_count = $stmt->fetchColumn();
                    
                    if ($active_count >= $company_admin['max_users_limit']) {
                        echo '<div class="alert alert-error">⛔ سقف کاربران فعال پر شده است. نمیتوان کاربر را فعال کرد.</div>';
                        echo '<a href="index.php?page=users" class="btn">بازگشت</a>';
                        exit;
                    }
                }
            }
        }
        
        // اجرای UPDATE
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, position_title = ?, phone = ?, status = ? WHERE id = ?");
        $stmt->execute([$full_name, $position_title, $phone, $status_new, $id]);
        
        // آپدیت mobile اگر وارد شده
        if (!empty($mobile)) {
            $pdo->prepare("UPDATE users SET mobile = ? WHERE id = ?")->execute([$mobile, $id]);
        }
        
        // آپدیت company_name (فقط super_admin)
        if ($is_super && isset($_POST['company_name'])) {
            $new_company_name = trim($_POST['company_name']);
            $new_company_id = crm_get_or_create_company_id($new_company_name);
            $pdo->prepare("UPDATE users SET company_name = ?, company_id = ? WHERE id = ?")
                ->execute([$new_company_name, $new_company_id, $id]);
        }
        
        // آپدیت parent_id (فقط admin/super_admin)
        if (($is_admin || $is_super) && isset($_POST['parent_id']) && $_POST['parent_id'] !== '') {
            $new_parent_id = (int)$_POST['parent_id'];
            if ($new_parent_id != $id) {
                $pdo->prepare("UPDATE users SET parent_id = ? WHERE id = ?")->execute([$new_parent_id, $id]);
            }
        }
        
        // آپدیت role
        if ($new_role && in_array($new_role, ['agent', 'manager'])) {
            if ($is_admin || $is_super || $new_role === 'agent') {
                $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$new_role, $id]);
            }
        }
        
        // اگه فعال شده و plan_expiry گذشته، تمدید ۳۰ روز
        if ($status_new === 'active' && strtotime($edit_user_data['plan_expiry']) <= time()) {
            $pdo->prepare("UPDATE users SET plan_expiry = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = ?")->execute([$id]);
        }
        
        header('Location: index.php?page=users&msg=updated');
        exit;
    }
    
    // شارژ حساب کاربر
    if ($action === 'recharge' && $id) {
        $plan_type = $_POST['plan_type'] ?? 'monthly';
        $days = ($plan_type === 'yearly') ? 365 : 30;
        
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE users SET plan_type = ?, plan_expiry = DATE_ADD(NOW(), INTERVAL ? DAY) WHERE id = ? AND parent_id = ?");
        $stmt->execute([$plan_type, $days, $id, $user['id']]);
        
        header('Location: index.php?page=users&msg=recharged');
        exit;
    }
}

// ========== نمایش ==========

$message = $_GET['msg'] ?? '';

// لیست زیرمجموعه‌ها
if ($action === 'list' || !$action) {
    $pdo = getDB();
    
    if ($is_super) {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY company_name, created_at DESC");
        $users_list = $stmt->fetchAll();
    } elseif ($is_admin) {
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE id = ? OR parent_id = ? OR parent_id IN (SELECT id FROM users WHERE parent_id = ?)
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user['id'], $user['id'], $user['id']]);
        $users_list = $stmt->fetchAll();
    } elseif ($user['role'] === 'manager') {
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE id = ? OR parent_id = ? OR id = (SELECT parent_id FROM users WHERE id = ?)
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user['id'], $user['id'], $user['id']]);
        $users_list = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $users_list = $stmt->fetchAll();
    }
    
    include __DIR__ . '/../Views/users/list.php';
}
// فرم ایجاد
elseif ($action === 'add') {
    include __DIR__ . '/../Views/users/form.php';
}
// فرم ویرایش
elseif ($action === 'edit' && $id) {
    $pdo = getDB();
    
    // چک دسترسی
    $can_edit = false;
    if ($is_super) {
        $can_edit = true;
    } elseif ($is_admin) {
        $can_edit = true;
    } elseif ($user['role'] === 'manager') {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND parent_id = ?");
        $stmt->execute([$id, $user['id']]);
        $can_edit = ($stmt->fetch() !== false);
    } elseif ($id == $user['id']) {
        $can_edit = true;
    }
    
    if (!$can_edit) {
        echo '<div class="alert alert-error">⛔ دسترسی غیرمجاز</div>';
        echo '<a href="index.php?page=users" class="btn">بازگشت</a>';
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $edit_user = $stmt->fetch();
    
    if (!$edit_user) {
        echo '<div class="alert alert-error">کاربر یافت نشد.</div>';
        exit;
    }
    
    include __DIR__ . '/../Views/users/form.php';
}
// مشاهده کاربر
elseif ($action === 'view' && $id) {
    $pdo = getDB();
    
    // چک دسترسی
    $has_access = false;
    
    if ($is_super) {
        $has_access = true;
    } elseif ($is_admin) {
        $company_members = crm_get_company_members($user['id']);
        $has_access = in_array($id, $company_members);
    } elseif ($user['role'] === 'manager') {
        if ($id == $user['id']) {
            $has_access = true;
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE parent_id = ? AND id = ?");
            $stmt->execute([$user['id'], $id]);
            if ($stmt->fetch()) $has_access = true;
            
            if (!$has_access && $user['parent_id']) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND id = ?");
                $stmt->execute([$user['parent_id'], $id]);
                if ($stmt->fetch()) $has_access = true;
            }
        }
    } else {
        $has_access = ($id == $user['id']);
    }
    
    if (!$has_access) {
        echo '<div class="alert alert-error">⛔ شما به این کاربر دسترسی ندارید.</div>';
        echo '<a href="index.php?page=users" class="btn">بازگشت</a>';
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $view_user = $stmt->fetch();
    
    if (!$view_user) {
        echo '<div class="alert alert-error">کاربر یافت نشد.</div>';
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE user_id = ?");
    $stmt->execute([$id]);
    $total_customers = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$id]);
    $active_tasks = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND DATE(next_followup_date) = CURDATE()");
    $stmt->execute([$id]);
    $today_reminders = $stmt->fetchColumn();
    
    include __DIR__ . '/../Views/users/view.php';
}
// پیش‌فرض
else {
    header('Location: index.php?page=users');
    exit;
}

ob_end_flush();