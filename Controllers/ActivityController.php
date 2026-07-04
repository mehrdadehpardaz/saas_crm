<?php
// controllers/ActivityController.php
if (!defined('CRM_APP')) { http_response_code(403); exit('Direct access denied'); }

require_once __DIR__ . '/../models/Activity.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Task.php';

$user = crm_get_current_user();
$is_manager = in_array($user['role'], ['super_admin', 'admin', 'manager']);
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
            Activity::create([
                'user_id'     => $user['id'],
                'customer_id' => $customer_id_post,
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
            
            $redirect = $task_id_post ? 
                "index.php?page=tasks&action=view&id=$task_id_post&msg=activity_added" : 
                "index.php?page=activities&msg=created";
            
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
        
        // بازگشت به همان تسک (اگر در زمینه تسک ویرایش شده) وگرنه به لیست فعالیت‌ها
        $redirect_task_id = $task_id ?: ($existing_activity['task_id'] ?? null);
        $redirect = $redirect_task_id
            ? "index.php?page=tasks&action=view&id=$redirect_task_id&msg=activity_updated"
            : "index.php?page=activities&msg=updated";
        header("Location: $redirect");
        exit;
    }
    
    if ($action === 'delete' && $id) {
        $existing_activity = Activity::getById($id);
        crm_require_activity_access($existing_activity);

        Activity::delete($id);
        $redirect = $task_id ? 
            "index.php?page=tasks&action=view&id=$task_id" : 
            "index.php?page=activities&msg=deleted";
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
        $task = Task::getById($task_id);
        $customer_id = $task['customer_id'];
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