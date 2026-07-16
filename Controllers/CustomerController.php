<?php
// controllers/CustomerController.php
if (!defined('CRM_APP')) { http_response_code(403); exit('Direct access denied'); }

require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Task.php';  

$user = crm_get_current_user();
$is_manager = in_array($user['role'], ['super_admin', 'admin', 'manager']);
$is_admin = in_array($user['role'], ['super_admin', 'admin']);
$is_super = ($user['role'] === 'super_admin');
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = '';
$success = '';

// پردازش فرم‌ها
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    crm_csrf_verify();
    // ایجاد مشتری جدید
    if ($action === 'create') {
        $industry_id = $_POST['industry_id'] ?? null;
        $company_name = crm_sanitize($_POST['company_name'] ?? '');
        $contact_person = crm_sanitize($_POST['contact_person'] ?? '');
        $contact_position = crm_sanitize($_POST['contact_position'] ?? '');
        $contact_phone = crm_sanitize($_POST['contact_phone'] ?? '');
        $phone = crm_sanitize($_POST['phone'] ?? '');
        $email = crm_sanitize($_POST['email'] ?? '');
        $notes = crm_sanitize($_POST['notes'] ?? '');
        
        if (empty($company_name)) {
            $error = 'نام شرکت الزامی است.';
        } elseif ($limit_box = crm_require_plan_limit('customers')) {
            $error = $limit_box;
        } else {
            $data = [
                'user_id' => $user['id'],
                'company_id' => $user['company_id'] ?? null,
                'industry_id' => !empty($industry_id) ? $industry_id : null,
                'company_name' => $company_name,
                'contact_person' => $contact_person,
                'contact_position' => $contact_position,
                'contact_phone' => $contact_phone,
                'phone' => $phone,
                'email' => $email,
                'notes' => $notes
            ];
            
            $customer_id = Customer::create($data);
            
            if ($customer_id) {
                header('Location: index.php?page=customers&msg=created&id=' . $customer_id);
                exit;
            } else {
                $error = 'خطا در ثبت مشتری. لطفاً دوباره تلاش کنید.';
            }
        }

        // اگر همینجا به خطا خوردیم (اعتبارسنجی، سقف پلن یا خطای ساخت)،
        // باید همون فرمِ «مشتری جدید» با پیام خطا دوباره نمایش داده بشه —
        // نه این‌که ساکت به لیست مشتریان بریم و پیام گم بشه.
        if (!empty($error)) {
            $customer = null;
            $primary_contact = null;
            include __DIR__ . '/../Views/customers/form.php';
            exit;
        }
    }
    
    // آپدیت مشتری
    if ($action === 'update' && $id) {
        crm_require_customer_access($id);
        $industry_id = $_POST['industry_id'] ?? null;
        $company_name = crm_sanitize($_POST['company_name'] ?? '');
        $contact_person = crm_sanitize($_POST['contact_person'] ?? '');
        $contact_position = crm_sanitize($_POST['contact_position'] ?? '');
        $contact_phone = crm_sanitize($_POST['contact_phone'] ?? '');
        $phone = crm_sanitize($_POST['phone'] ?? '');
        $email = crm_sanitize($_POST['email'] ?? '');
        $notes = crm_sanitize($_POST['notes'] ?? '');
        
        if (empty($company_name)) {
            $error = 'نام شرکت الزامی است.';
            $customer = Customer::getById($id);
            $primary_contact = Customer::getPrimaryContact($id);
            include __DIR__ . '/../Views/customers/form.php';
            exit;
        } else {
            $data = [
                'industry_id' => !empty($industry_id) ? $industry_id : null,
                'company_name' => $company_name,
                'contact_person' => $contact_person,
                'contact_position' => $contact_position,
                'contact_phone' => $contact_phone,
                'phone' => $phone,
                'email' => $email,
                'notes' => $notes,
                'acting_user_id' => $user['id']
            ];
            
            if (Customer::update($id, $data)) {
                header('Location: index.php?page=customers&msg=updated&id=' . $id);
                exit;
            } else {
                $error = 'خطا در بروزرسانی.';
                $customer = Customer::getById($id);
                $primary_contact = Customer::getPrimaryContact($id);
                include __DIR__ . '/../Views/customers/form.php';
                exit;
            }
        }
    }
    
    // حذف مشتری (غیرفعال کردن)
    if ($action === 'delete' && $id) {
        // فقط admin و super_admin
        if (!$is_super && !$is_admin) {
            header('Location: index.php?page=customers&msg=access_denied');
            exit;
        }

        // باید مشتری متعلق به همین شرکت باشد
        crm_require_customer_access($id);

        $pdo = getDB();
        $pdo->prepare("UPDATE customers SET status = 'inactive' WHERE id = ?")->execute([$id]);

        header('Location: index.php?page=customers&msg=deleted');
        exit;
    }
}

// نمایش فرم ایجاد/ویرایش
if ($action === 'add' || $action === 'edit') {
    $customer = null;
    $primary_contact = null;
    $is_edit = ($action === 'edit');
    
    if ($is_edit && $id) {
        crm_require_customer_access($id);
        $customer = Customer::getById($id);
        if (!$customer) {
            echo '<div class="alert alert-error">مشتری یافت نشد.</div>';
            echo '<a href="index.php?page=customers" class="btn">بازگشت</a>';
            exit;
        }
        $primary_contact = Customer::getPrimaryContact($id);
    }
    
    include __DIR__ . '/../Views/customers/form.php';
}
// مشاهده جزئیات مشتری
elseif ($action === 'view' && $id) {
    $customer = Customer::getById($id);
    
    if (!$customer) {
        echo '<div class="alert alert-error">مشتری یافت نشد.</div>';
        echo '<a href="index.php?page=customers" class="btn">بازگشت</a>';
        exit;
    }
    
    // چک دسترسی: آیا کاربر company_id یکسانی با این مشتری داره؟ (قانون ۱)
    if (!crm_user_can_access_customer($id)) {
        echo '<div class="alert alert-error">⛔ شما به این مشتری دسترسی ندارید.</div>';
        echo '<a href="index.php?page=customers" class="btn">بازگشت</a>';
        exit;
    }
    
    $contacts = Customer::getContacts($id);
    $activities = Customer::getActivities($id);
    $message = $_GET['msg'] ?? '';
    
    require_once __DIR__ . '/../models/Task.php';
    $tasks = Task::getAll($id, $user['id'], $is_manager);
    
    include __DIR__ . '/../Views/customers/view.php';
}
// لیست مشتریان (پیش‌فرض)
else {
    $search = $_GET['search'] ?? '';
    $message = $_GET['msg'] ?? '';
    $customers = Customer::getAll($user['id'], $is_manager, $search);
    include __DIR__ . '/../Views/customers/list.php';
}