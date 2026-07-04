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
            // اگر سوپرادمین نام شرکت را خالی بگذارد، مثل ثبت‌نام معمولی از
            // نام خود کاربر جدید استفاده می‌شود. این مهم است چون company_name
            // مبنای تشخیص «هم‌شرکتی بودن» در کل پروژه است — اگر خالی بماند،
            // این کاربر (و بعداً زیرمجموعه‌هایش) از دید ادمین/مدیرِ خودشان
            // در لیست‌ها دیده نمی‌شوند.
            if ($company_name === '') {
                $company_name = $full_name;
            }
        } else {
            $company_name = $user['company_name'] ?? null;
        }
        
        if (!$is_super) {
            if ($role !== 'agent' && $role !== 'manager') {
                $role = 'agent';
            }
            if (!$is_admin && $role === 'manager') {
                $role = 'agent';
            }
        }

        // ── تعیین مدیر بالادستی (پرنت) ──
        // پیش‌فرض: خود کاربر ایجادکننده. اگر ادمین/سوپرادمین از فرم یک
        // پرنت معتبر انتخاب کرده باشد (مثلاً یک مدیر فروش زیرمجموعه خودش)
        // همان استفاده می‌شود؛ در غیر این صورت به‌صورت امن نادیده گرفته می‌شود.
        $parent_id = $user['id'];
        if (($is_admin || $is_super) && !empty($_POST['parent_id'])) {
            $requested_parent_id = (int)$_POST['parent_id'];
            $pdo_parent_check = getDB();
            if ($is_super) {
                // سوپر ادمین می‌تواند هر کاربری را به‌عنوان پرنت انتخاب کند
                $stmt = $pdo_parent_check->prepare("SELECT id FROM users WHERE id = ?");
                $stmt->execute([$requested_parent_id]);
                if ($stmt->fetch()) $parent_id = $requested_parent_id;
            } else {
                // ادمین فقط می‌تواند خودش یا یکی از مدیران فروش زیرمجموعه‌اش را انتخاب کند
                $stmt = $pdo_parent_check->prepare("SELECT id FROM users WHERE id = ? AND (id = ? OR (parent_id = ? AND role = 'manager'))");
                $stmt->execute([$requested_parent_id, $user['id'], $user['id']]);
                if ($stmt->fetch()) $parent_id = $requested_parent_id;
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
                    // ستون max_users_limit در دیتابیس پیش‌فرض ۱ دارد. اگر این کاربر
                    // جدید خودش «مدیر شرکت» (admin/super_admin) است، باید سقف
                    // معقولی مثل نسخه رایگان (۵ کاربر) داشته باشد — وگرنه منطق
                    // فعال/غیرفعال‌سازی خودکار در index.php بقیه زیرمجموعه‌هایش
                    // (مدیر فروش، کارشناس) را بلافاصله غیرفعال می‌کند.
                    $new_max_users_limit = in_array($role, ['admin', 'super_admin']) ? 5 : 1;
                    $stmt = $pdo->prepare("INSERT INTO users (mobile, password, full_name, company_name, position_title, phone, role, parent_id, plan_type, plan_expiry, max_users_limit, status) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'trial', DATE_ADD(NOW(), INTERVAL 14 DAY), ?, 'active')");
                    $stmt->execute([$mobile, $hashed, $full_name, $company_name, $position_title, $phone, $role, $parent_id, $new_max_users_limit]);
                    
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
        // نکته: قبلاً برای ادمین همیشه true بود که یعنی حتی کاربر شرکت دیگر هم
        // قابل ویرایش بود. حالا مثل بقیه پروژه بر اساس عضویت در همان شرکت
        // (company_name خودِ ادمین) بررسی می‌شود — بدون پیمایش parent_id،
        // چون اگر ادمین توسط سوپرادمین ساخته شده باشد، آن پیمایش تا
        // سوپرادمین بالا می‌رود که اصلاً جزو همان شرکت مشتری نیست.
        $can_edit = false;
        if ($is_super) {
            $can_edit = true;
        } elseif ($is_admin) {
            $company_members = crm_get_company_members($user['id']);
            $can_edit = in_array((int)$edit_user_data['id'], $company_members);
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
            $pdo->prepare("UPDATE users SET company_name = ? WHERE id = ?")->execute([trim($_POST['company_name']), $id]);
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

        // ── تغییر رمز عبور توسط مدیر بالادستی ──
        // چون $can_edit از قبل احراز شده (ادمین/سوپرادمین/مدیر فروش نسبت به
        // زیرمجموعه خودش)، اگر رمز جدیدی وارد شده و طول معتبر داشته باشد
        // اعمال می‌شود؛ در غیر این صورت رمز فعلی کاربر دست‌نخورده می‌ماند.
        $new_password = $_POST['new_password'] ?? '';
        if ($new_password !== '' && strlen($new_password) >= 6) {
            $hashed_new = password_hash($new_password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed_new, $id]);
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
        // بر اساس عضویت در همان شرکت (company_name خودِ ادمین) — بدون پیمایش
        // parent_id تا ریشه، چون اگر این ادمین را سوپرادمین ساخته باشد،
        // آن پیمایش تا سوپرادمین بالا می‌رفت که جزو این شرکت نیست.
        $member_ids = crm_get_company_members($user['id']);
        if (empty($member_ids)) $member_ids = [(int)$user['id']];
        $placeholders = implode(',', array_fill(0, count($member_ids), '?'));
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id IN ($placeholders) ORDER BY created_at DESC");
        $stmt->execute($member_ids);
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
        $company_members = crm_get_company_members($user['id']);
        $can_edit = in_array((int)$id, $company_members);
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
        // بر اساس company_name خودِ ادمین — بدون پیمایش parent_id تا ریشه،
        // چون اگر این ادمین را سوپرادمین ساخته باشد (parent_id = سوپرادمین)،
        // آن پیمایش اشتباهاً تا سوپرادمین بالا می‌رفت که جزو این شرکت نیست.
        $company_members = crm_get_company_members($user['id']);
        $has_access = in_array((int)$id, $company_members);
    } elseif ($user['role'] === 'manager') {
        if ($id == $user['id']) {
            $has_access = true;
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND (parent_id = ? OR id = (SELECT parent_id FROM users WHERE id = ?))");
            $stmt->execute([$id, $user['id'], $user['id']]);
            if ($stmt->fetch()) $has_access = true;
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
