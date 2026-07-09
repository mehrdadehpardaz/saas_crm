<?php
// controllers/ActivityController.php
if (!defined('CRM_APP')) { http_response_code(403); exit('Direct access denied'); }

require_once __DIR__ . '/../models/Activity.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Task.php';

$user = crm_get_current_user();
$is_manager = in_array($user['role'], ['super_admin', 'admin', 'manager']);
$is_super = ($user['role'] === 'super_admin');
$is_admin = in_array($user['role'], ['super_admin', 'admin']);
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$task_id = isset($_GET['task_id']) ? (int)$_GET['task_id'] : null;
$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
$error = '';

/**
 * ساخت رشته‌ی تاریخ/ساعت فعالیت از فیلدهای فرم (activity_date + activity_time).
 * اگر کاربر چیزی وارد نکرده باشد null برمی‌گرداند تا مدل از لحظه‌ی فعلی استفاده کند.
 */
function crm_build_activity_datetime(): ?string {
    if (empty($_POST['activity_date'])) {
        return null;
    }
    $time = $_POST['activity_time'] ?? date('H:i');
    return $_POST['activity_date'] . ' ' . $time . ':00';
}

/**
 * مسیر برگشت/ریدایرکت برای صفحات فعالیت.
 * فعالیت‌ها همیشه زیرمجموعه‌ی یک تسک‌اند، پس هر «بازگشت» یا ریدایرکت بعد از
 * عملیات باید به صفحه‌ی همون تسک برود، نه به لیست کلی فعالیت‌ها. اگر هیچ
 * تسکی در دسترس نبود (فعالیت مستقل، فقط به یک مشتری وصل)، به لیست تسک‌ها می‌رویم.
 */
function crm_activity_back_url(?int $known_task_id): string {
    return $known_task_id
        ? "index.php?page=tasks&action=view&id={$known_task_id}"
        : "index.php?page=tasks&action=list_all";
}

// ========== پردازش POST ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    crm_csrf_verify();
    if ($action === 'create') {
        $task_id_post = $_POST['task_id'] ?? null;
        $customer_id_post = (int)($_POST['customer_id'] ?? 0);
        $activity_datetime = crm_build_activity_datetime();

        if (empty($customer_id_post)) {
            $error = 'انتخاب مشتری الزامی است.';
        } elseif (!crm_user_can_access_customer($customer_id_post)) {
            $error = '⛔ شما به این مشتری دسترسی ندارید.';
        } else {
            // سازمانِ فعالیت همیشه از روی مشتریِ صاحبش گرفته می‌شود
            $owning_customer = Customer::getById($customer_id_post);

            Activity::create([
                'user_id'     => $user['id'],
                'customer_id' => $customer_id_post,
                'company_id'  => $owning_customer['company_id'] ?? null,
                'task_id'     => $task_id_post ? (int)$task_id_post : null,
                'contact_id'  => $_POST['contact_id'] ?: null,
                'type'        => $_POST['type'] ?? 'call',
                'description' => trim($_POST['description'] ?? ''),
                'created_at'  => $activity_datetime
            ]);
            
            // اگر تاریخ پیگیری جدید برای task داده شده
            if (!empty($_POST['update_task_followup']) && $task_id_post) {
                $task_for_update = Task::getById((int)$task_id_post);
                if ($task_for_update && crm_user_can_access_customer($task_for_update['customer_id'])) {
                    $followup_date = !empty($_POST['next_followup_date']) ?
                        $_POST['next_followup_date'] . ' ' . ($_POST['next_followup_time'] ?? '09:00') . ':00' : null;

                    Task::update($task_id_post, [
                        'title'               => $_POST['task_title'] ?? '',
                        'next_followup_date'  => $followup_date,
                        'next_followup_topic' => trim($_POST['next_followup_topic'] ?? '')
                    ]);
                }
            }

            $redirect_task_id = $task_id_post ? (int)$task_id_post : null;
            $redirect = $redirect_task_id
                ? "index.php?page=tasks&action=view&id=$redirect_task_id&msg=activity_added"
                : crm_activity_back_url(null);

            header("Location: $redirect");
            exit;
        }
    }
    
    if ($action === 'update' && $id) {
        $existing_activity = Activity::getById($id);
        crm_require_activity_access($existing_activity);

        $activity_datetime = crm_build_activity_datetime();

        Activity::update($id, [
            'contact_id'  => $_POST['contact_id'] ?: null,
            'type'        => $_POST['type'] ?? 'call',
            'description' => trim($_POST['description'] ?? ''),
            'created_at'  => $activity_datetime
        ]);
        
        // بازگشت به همان تسک (اگر در زمینه تسک ویرایش شده، وگرنه تسک متعلق به خود فعالیت)
        $redirect_task_id = $task_id ?: ($existing_activity['task_id'] ?? null);
        $redirect = $redirect_task_id
            ? "index.php?page=tasks&action=view&id=$redirect_task_id&msg=activity_updated"
            : crm_activity_back_url(null);
        header("Location: $redirect");
        exit;
    }
    
    if ($action === 'delete' && $id) {
        if (!$is_admin) {
            http_response_code(403);
            die('<div class="alert alert-error">⛔ حذف فقط برای مدیران امکان‌پذیر است.</div>');
        }
        $existing_activity = Activity::getById($id);
        crm_require_activity_access($existing_activity);

        Activity::delete($id);

        $redirect_task_id = $task_id ?: ($existing_activity['task_id'] ?? null);
        $redirect = crm_activity_back_url($redirect_task_id ? (int)$redirect_task_id : null);
        header("Location: $redirect");
        exit;
    }
}

// ========== نمایش ==========

if ($action === 'add') {
    $is_edit = false;
    $activity = null;
    $task = null;
    
    if ($task_id) {
        $candidate_task = Task::getById($task_id);
        if ($candidate_task && crm_user_can_access_customer($candidate_task['customer_id'])) {
            $task = $candidate_task;
            $customer_id = $task['customer_id'];
        }
    }
    
    $customers_list = Activity::getCustomersForDropdown($user['id'], $is_manager);
    $contacts_list = $customer_id ? Customer::getContacts($customer_id) : [];
    
    // لیست task های مشتری برای dropdown
    $tasks_list = $customer_id ? Task::getAll($customer_id, $user['id'], $is_manager) : [];
    
    include __DIR__ . '/../Views/activities/form.php';
}
elseif ($action === 'edit' && $id) {
    $is_edit = true;
    $activity = Activity::getById($id);
    if (!$activity) {
        echo '<div class="alert alert-error">فعالیت یافت نشد.</div>';
        exit;
    }
    crm_require_activity_access($activity);
    $customers_list = Activity::getCustomersForDropdown($user['id'], $is_manager);
    $contacts_list = Customer::getContacts($activity['customer_id']);

    // اگر ویرایش از داخل صفحه‌ی یک تسک باز شده (؟task_id=..) یا خودِ فعالیت
    // از قبل به یک تسک وصل است، همون تسک رو برای نمایش جعبه‌ی اطلاعاتی بالای
    // فرم لود می‌کنیم — این همون متغیری‌ست که نبودش باعث Undefined variable می‌شد.
    $task = null;
    $effective_task_id = $task_id ?: ($activity['task_id'] ?? null);
    if ($effective_task_id) {
        $candidate_task = Task::getById((int)$effective_task_id);
        if ($candidate_task && crm_user_can_access_customer($candidate_task['customer_id'])) {
            $task = $candidate_task;
            $task_id = $effective_task_id; // برای هماهنگی لینک بازگشت/فرم
        }
    }

    include __DIR__ . '/../Views/activities/form.php';
}
else {
    $search_type = $_GET['type'] ?? '';
    $search_date = $_GET['date'] ?? '';
    $message = $_GET['msg'] ?? '';
    
    $filters = [];
    if ($task_id) $filters['task_id'] = $task_id;
    if ($customer_id) $filters['customer_id'] = $customer_id;
    if ($search_type) $filters['type'] = $search_type;
    if ($search_date) $filters['date'] = $search_date;
    
    $activities = Activity::getAll($user['id'], $is_manager, $filters);
    include __DIR__ . '/../Views/activities/list.php';
}