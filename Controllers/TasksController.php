<?php
// controllers/TaskController.php
if (!defined('CRM_APP')) { http_response_code(403); exit('Direct access denied'); }
ob_start();

require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Activity.php';

$user = crm_get_current_user();
$is_manager = in_array($user['role'], ['super_admin', 'admin', 'manager']);
$is_admin = in_array($user['role'], ['super_admin', 'admin']);
$is_super = ($user['role'] === 'super_admin');
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
$error = '';

// ========== پردازش POST ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    crm_csrf_verify();
    // ایجاد تسک جدید
    if ($action === 'create') {
        $customer_id_post = (int)($_POST['customer_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        
        if (empty($customer_id_post) || empty($title)) {
            $error = 'مشتری و عنوان تسک الزامی است.';
        } elseif (!crm_user_can_access_customer($customer_id_post)) {
            $error = '⛔ شما به این مشتری دسترسی ندارید.';
        } else {
            $followup_date = !empty($_POST['next_followup_date']) ? 
                $_POST['next_followup_date'] . ' ' . ($_POST['next_followup_time'] ?? '09:00') . ':00' : null;

            // سازمانِ تسک همیشه از روی مشتریِ صاحبش گرفته می‌شود، نه از
            // کاربر سازنده — تا با سازمان واقعیِ آن مشتری هماهنگ بماند.
            $owning_customer = Customer::getById($customer_id_post);
            
            $task_id = Task::create([
                'user_id'             => $user['id'],
                'customer_id'         => $customer_id_post,
                'company_id'          => $owning_customer['company_id'] ?? null,
                'title'               => $title,
                'next_followup_date'  => $followup_date,
                'next_followup_topic' => trim($_POST['next_followup_topic'] ?? '')
            ]);
            
            header("Location: index.php?page=tasks&action=view&id=$task_id&msg=created");
            exit;
        }
    }
    
    // ویرایش تسک
    if ($action === 'update' && $id) {
        $task_to_update = Task::getById($id);
        crm_require_task_access($task_to_update);

        $followup_date = !empty($_POST['next_followup_date']) ? 
            $_POST['next_followup_date'] . ' ' . ($_POST['next_followup_time'] ?? '09:00') . ':00' : null;
        
        Task::update($id, [
            'title'               => trim($_POST['title'] ?? ''),
            'status'              => $_POST['status'] ?? 'active',
            'next_followup_date'  => $followup_date,
            'next_followup_topic' => trim($_POST['next_followup_topic'] ?? '')
        ]);
        
        header("Location: index.php?page=tasks&action=view&id=$id&msg=updated");
        exit;
    }
    
    // حذف تسک — طبق قانون، حذف فقط برای Admin (و سوپر ادمین) مجازه
    if ($action === 'delete' && $id) {
        if (!$is_admin) {
            http_response_code(403);
            die('<div class="alert alert-error">⛔ حذف فقط برای مدیران امکان‌پذیر است.</div>');
        }
        $task_to_delete = Task::getById($id);
        crm_require_task_access($task_to_delete);

        Task::delete($id);
        header("Location: index.php?page=tasks&action=list_all");
        exit;
    }
    
    // انتقال تسک
    if ($action === 'assign' && $id) {
        $task_to_assign = Task::getById($id);
        crm_require_task_access($task_to_assign);

        $new_user_id = (int)($_POST['new_user_id'] ?? 0);
        
        if ($new_user_id > 0) {
            $pdo = getDB();
            $valid_user = false;
            
            if ($is_super) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
                $stmt->execute([$new_user_id]);
                $valid_user = ($stmt->fetch() !== false);
            } 
            elseif ($user['role'] === 'admin') {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND (id = ? OR parent_id = ?)");
                $stmt->execute([$new_user_id, $user['id'], $user['id']]);
                $valid_user = ($stmt->fetch() !== false);
            } 
            elseif ($user['role'] === 'manager') {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND (id = ? OR parent_id = ?)");
                $stmt->execute([$new_user_id, $user['id'], $user['id']]);
                $valid_user = ($stmt->fetch() !== false);
            }
            
            if ($valid_user) {
                // فقط tasks آپدیت میشه، activities دست نمیخورن
                $pdo->prepare("UPDATE tasks SET user_id = ? WHERE id = ?")->execute([$new_user_id, $id]);
                header("Location: index.php?page=tasks&action=view&id=$id&msg=assigned");
                exit;
            } else {
                $error = 'کاربر انتخاب شده معتبر نیست.';
            }
        }
    }
    
    // تکمیل تسک
    if ($action === 'complete' && $id) {
        $task_data = Task::getById($id);
        crm_require_task_access($task_data);

        $final_description = trim($_POST['final_description'] ?? '');
        $new_status = $_POST['complete_status'] ?? 'completed';
        if (!in_array($new_status, ['completed', 'sold', 'cancelled'])) $new_status = 'completed';
        $pdo = getDB();
        
        if ($task_data) {
            $pdo->prepare("UPDATE tasks SET status = ?, next_followup_date = NULL WHERE id = ?")->execute([$new_status, $id]);
            
            $prefix = match($new_status) {
                'sold'      => '💰 منجر به فروش شد. ',
                'cancelled' => '❌ تسک کنسل شد. ',
                default     => '✅ تسک تکمیل شد. ',
            };
            Activity::create([
                'user_id'     => $user['id'],
                'customer_id' => $task_data['customer_id'],
                'task_id'     => $id,
                'contact_id'  => null,
                'type'        => 'note',
                'description' => $prefix . ($final_description ?: '')
            ]);
        }
        header("Location: index.php?page=tasks&action=view&id=$id&msg=$new_status");        
        exit;
    }
}

// ========== نمایش ==========

// لیست همه تسک‌ها (منوی تسک‌ها)
if ($action === 'list_all') {
    $pdo = getDB();
    
    $filter_status = $_GET['filter_status'] ?? 'active';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $filter_user = $_GET['filter_user'] ?? '';
    
    $where = [];
    $params = [];
    
    // فیلتر وضعیت
    if ($filter_status === 'overdue') {
        // تسک‌های فعالی که از تاریخ پیگیری‌شان گذشته
        // «امروز» با PHP حساب می‌شود (نه CURDATE سمت MySQL) تا اختلاف تایم‌زون باعث نتیجه اشتباه نشود
        $where[] = "t.status = 'active'";
        $where[] = "t.next_followup_date IS NOT NULL";
        $where[] = "DATE(t.next_followup_date) < ?";
        $params[] = date('Y-m-d');
    } elseif ($filter_status !== 'all') {
        $where[] = "t.status = ?";
        $params[] = $filter_status;
    }
    
    // فیلتر تاریخ
    if (!empty($date_from)) {
        $where[] = "DATE(t.created_at) >= ?";
        $params[] = $date_from;
    }
    if (!empty($date_to)) {
        $where[] = "DATE(t.created_at) <= ?";
        $params[] = $date_to;
    }
    
    // فیلتر دسترسی / مالک
    // اگر فیلتر «مالک» انتخاب شده، فقط همان یک کاربر؛ در غیر این صورت
    // طبق قانون دسترسی: هرکس تسک‌های خودش رو می‌بینه؛ «پرنت» علاوه بر
    // خودش، تسک‌های همه‌ی زیرمجموعه‌های مستقیم/غیرمستقیمش رو هم می‌بینه
    // (بدون محدودیت به ۲ یا ۳ سطح ثابت — با crm_get_subtree_ids که کل
    // زیردرخت رو دنبال می‌کنه).
    if (!empty($filter_user) && ($is_super || $is_admin || $user['role'] === 'manager')) {
        $where[] = "t.user_id = ?";
        $params[] = $filter_user;
    }
    elseif ($is_super) {
        // super_admin همه رو میبینه - بدون محدودیت
    }
    else {
        $scope_ids = ($is_admin || $user['role'] === 'manager')
            ? crm_get_subtree_ids($user['id'])
            : [(int)$user['id']];
        $in = implode(',', array_fill(0, count($scope_ids), '?'));
        $where[] = "t.user_id IN ($in)";
        foreach ($scope_ids as $sid) { $params[] = $sid; }
    }
    
    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT t.*, c.company_name, u.full_name as agent_name, comp.name as company_label
            FROM tasks t
            JOIN customers c ON t.customer_id = c.id
            JOIN users u ON t.user_id = u.id
            LEFT JOIN companies comp ON t.company_id = comp.id
            $where_clause
            ORDER BY t.next_followup_date ASC, t.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $all_tasks = $stmt->fetchAll();
    
    include __DIR__ . '/../Views/tasks/list_all.php';
}
// فرم تکمیل تسک
elseif ($action === 'complete' && $id) {
    $task = Task::getById($id);
    
    if (!$task) {
        echo '<div class="alert alert-error">⛔ تسک یافت نشد.</div>';
        echo '<a href="index.php?page=tasks&action=list_all" class="btn">🔙 بازگشت</a>';
        exit;
    }
    crm_require_task_access($task);
    
    include __DIR__ . '/../Views/tasks/complete.php';
}
// فرم انتقال تسک
elseif ($action === 'assign' && $id) {
    $task = Task::getById($id);
    
    if (!$task) {
        echo '<div class="alert alert-error">⛔ تسک یافت نشد.</div>';
        echo '<a href="index.php?page=tasks&action=list_all" class="btn">🔙 بازگشت</a>';
        exit;
    }
    crm_require_task_access($task);
    
    $pdo = getDB();
    
    if ($is_super) {
        $stmt = $pdo->query("SELECT id, full_name, role, company_name FROM users WHERE id != " . (int)$task['user_id'] . " ORDER BY company_name, role, full_name");
    } 
    elseif ($user['role'] === 'admin') {
        $stmt = $pdo->prepare("
            SELECT id, full_name, role, company_name FROM users 
            WHERE (id = ? OR parent_id = ?) 
            AND id != ?
            ORDER BY role, full_name
        ");
        $stmt->execute([$user['id'], $user['id'], $task['user_id']]);
    } 
    elseif ($user['role'] === 'manager') {
        $stmt = $pdo->prepare("
            SELECT id, full_name, role, company_name FROM users 
            WHERE (id = ? OR parent_id = ?) 
            AND id != ?
            ORDER BY role DESC, full_name
        ");
        $stmt->execute([$user['id'], $user['id'], $task['user_id']]);
    } 
    else {
        $assignable_users = [];
    }
    
    $assignable_users = isset($stmt) ? $stmt->fetchAll() : [];
    
    include __DIR__ . '/../Views/tasks/assign.php';
}
// فرم ایجاد تسک
elseif ($action === 'add') {
    $customers_list = Activity::getCustomersForDropdown($user['id'], $is_manager);
    
    if ($customer_id) {
        $selected_customer = Customer::getById($customer_id);
    }
    
    include __DIR__ . '/../Views/tasks/form.php';
}
// فرم ویرایش تسک
elseif ($action === 'edit' && $id) {
    $task = Task::getById($id);
    
    if (!$task) {
        echo '<div class="alert alert-error">⛔ تسک یافت نشد.</div>';
        echo '<a href="index.php?page=tasks&action=list_all" class="btn">🔙 بازگشت</a>';
        exit;
    }
    crm_require_task_access($task);
    
    include __DIR__ . '/../Views/tasks/form.php';
}
// مشاهده تسک
elseif ($action === 'view' && $id) {
    $task = Task::getById($id);
    
    if (!$task) {
        echo '<div class="alert alert-error">⛔ تسک یافت نشد.</div>';
        echo '<a href="index.php?page=tasks&action=list_all" class="btn">🔙 بازگشت</a>';
        exit;
    }
    crm_require_task_access($task);
    
    $activities = Task::getActivities($id);
    $message = $_GET['msg'] ?? '';
    include __DIR__ . '/../Views/tasks/view.php';
}
// پیش‌فرض
else {
    header('Location: index.php?page=tasks&action=list_all');
    exit;
}

ob_end_flush();