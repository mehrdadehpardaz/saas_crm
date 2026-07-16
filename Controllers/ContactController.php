<?php
// controllers/ContactController.php
if (!defined('CRM_APP')) { http_response_code(403); exit('Direct access denied'); }

require_once __DIR__ . '/../models/Customer.php';

$user = crm_get_current_user();
$is_admin = in_array($user['role'], ['super_admin', 'admin']);
$is_super = ($user['role'] === 'super_admin');
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
$error = '';

// ========== پردازش POST ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    crm_csrf_verify();
    // ایجاد مخاطب جدید
    if ($action === 'create') {
        $customer_id_post = (int)($_POST['customer_id'] ?? 0);
        $full_name = trim($_POST['full_name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $is_primary = isset($_POST['is_primary']) ? 1 : 0;
        
        if (empty($full_name)) {
            $error = 'نام مخاطب الزامی است.';
        } elseif (empty($customer_id_post)) {
            $error = 'مشتری مشخص نشده.';
        } elseif (!crm_user_can_access_customer($customer_id_post)) {
            $error = '⛔ شما به این مشتری دسترسی ندارید.';
        } elseif ($limit_box = crm_require_plan_limit('contacts')) {
            $error = $limit_box;
        } else {
            $pdo = getDB();
            
            // اگر primary هست، بقیه رو غیر-primary کن
            if ($is_primary) {
                $pdo->prepare("UPDATE contacts SET is_primary = 0 WHERE customer_id = ?")->execute([$customer_id_post]);
            }

            // سازمانِ مخاطب همیشه از روی مشتریِ صاحبش گرفته می‌شود
            $owning_customer = Customer::getById($customer_id_post);
            $company_id = $owning_customer['company_id'] ?? null;
            
            $stmt = $pdo->prepare("INSERT INTO contacts (customer_id, user_id, company_id, full_name, position, phone, email, is_primary, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$customer_id_post, $user['id'], $company_id, $full_name, $position, $phone, $email, $is_primary]);
            
            header('Location: index.php?page=customers&action=view&id=' . $customer_id_post . '&msg=contact_added');
            exit;
        }

        // اگر خطا خوردیم (اعتبارسنجی، دسترسی یا سقف پلن)، فرمِ «مخاطب جدید»
        // با پیام خطا دوباره نمایش داده می‌شود — نه ریدایرکت ساکت.
        if (!empty($error)) {
            $is_edit = false;
            $contact = null;
            $customer_id = $customer_id_post ?: $customer_id;
            $customer = $customer_id ? Customer::getById($customer_id) : null;
            if (!$customer) {
                echo '<div class="alert alert-error">مشتری یافت نشد.</div>';
                echo '<a href="index.php?page=customers" class="btn">بازگشت</a>';
                exit;
            }
            include __DIR__ . '/../Views/contacts/form.php';
            exit;
        }
    }
    
    // ویرایش مخاطب
    if ($action === 'update' && $id) {
        $full_name = trim($_POST['full_name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $is_primary = isset($_POST['is_primary']) ? 1 : 0;
        
        if (empty($full_name)) {
            $error = 'نام مخاطب الزامی است.';
        } else {
            $pdo = getDB();
            
            // گرفتن customer_id این مخاطب
            $stmt = $pdo->prepare("SELECT customer_id FROM contacts WHERE id = ?");
            $stmt->execute([$id]);
            $contact = $stmt->fetch();

            crm_require_contact_access($contact);

            if ($contact) {
                // اگر primary هست، بقیه رو غیر-primary کن
                if ($is_primary) {
                    $pdo->prepare("UPDATE contacts SET is_primary = 0 WHERE customer_id = ?")->execute([$contact['customer_id']]);
                }
                
                $stmt = $pdo->prepare("UPDATE contacts SET full_name = ?, position = ?, phone = ?, email = ?, is_primary = ? WHERE id = ?");
                $stmt->execute([$full_name, $position, $phone, $email, $is_primary, $id]);
                
                header('Location: index.php?page=customers&action=view&id=' . $contact['customer_id'] . '&msg=contact_updated');
                exit;
            }
        }

        if (!empty($error)) {
            $is_edit = true;
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
            $stmt->execute([$id]);
            $contact = $stmt->fetch();
            if (!$contact) {
                echo '<div class="alert alert-error">مخاطب یافت نشد.</div>';
                exit;
            }
            $customer_id = $contact['customer_id'];
            $customer = Customer::getById($customer_id);
            include __DIR__ . '/../Views/contacts/form.php';
            exit;
        }
    }
}

// ========== پردازش GET (حذف) ==========

// حذف مخاطب (غیرفعال کردن) - با POST و CSRF
if ($action === 'delete' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    crm_csrf_verify();
    $pdo = getDB();
    
    // چک کن مخاطب وجود داره
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
    $stmt->execute([$id]);
    $contact = $stmt->fetch();
    
    if (!$contact) {
        header('Location: index.php?page=customers&msg=not_found');
        exit;
    }
    
    // فقط admin و super_admin میتونن حذف کنن
    if (!$is_super && !$is_admin) {
        header('Location: index.php?page=customers&action=view&id=' . $contact['customer_id'] . '&msg=access_denied');
        exit;
    }

    // باید مخاطب متعلق به همین شرکت باشد (جلوگیری از حذف مخاطب شرکت دیگر)
    crm_require_contact_access($contact);
    
    // غیرفعال کردن
    $stmt = $pdo->prepare("UPDATE contacts SET status = 'inactive' WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Location: index.php?page=customers&action=view&id=' . $contact['customer_id'] . '&msg=contact_deleted');
    exit;
}

// ========== نمایش ==========

// فرم ایجاد مخاطب
if ($action === 'add' && $customer_id) {
    $is_edit = false;
    $contact = null;
    crm_require_customer_access($customer_id);
    $customer = Customer::getById($customer_id);
    
    if (!$customer) {
        echo '<div class="alert alert-error">مشتری یافت نشد.</div>';
        exit;
    }
    
    include __DIR__ . '/../Views/contacts/form.php';
}
// فرم ویرایش مخاطب
elseif ($action === 'edit' && $id) {
    $is_edit = true;
    
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
    $stmt->execute([$id]);
    $contact = $stmt->fetch();
    
    if (!$contact) {
        echo '<div class="alert alert-error">مخاطب یافت نشد.</div>';
        exit;
    }
    
    // چک دسترسی — طبق قانون: خودِ سازنده‌ی مخاطب، یا هر «پرنت» بالادستی‌اش
    $customer = Customer::getById($contact['customer_id']);
    $can_edit = crm_user_can_access_owned_record($contact['user_id'] ?? null);
    
    if (!$can_edit) {
        echo '<div class="alert alert-error">⛔ شما اجازه ویرایش این مخاطب را ندارید.</div>';
        echo '<a href="index.php?page=customers&action=view&id=' . $contact['customer_id'] . '" class="btn">بازگشت</a>';
        exit;
    }
    
    $customer_id = $contact['customer_id'];
    
    include __DIR__ . '/../Views/contacts/form.php';
}
// لیست مخاطبین
elseif ($action === 'list' || $action === 'index') {
    $pdo = getDB();

    // نکته: owner_name/owner_id حالا از روی خودِ سازنده‌ی مخاطب (co.user_id)
    // گرفته می‌شه، نه صاحبِ مشتری (cu.user_id) — چون ممکنه یک مدیر یا
    // مدیرفروش، مخاطبی رو برای مشتریِ یک کارشناسِ دیگه ثبت کرده باشه.
    // برای تشخیص محدوده‌ی دسترسی (شرکت/زیرمجموعه) هنوز از صاحبِ مشتری
    // (cu.user_id) به اسم owner_scope_id استفاده می‌کنیم.
    // قانون دسترسی: هرکس مخاطب‌هایی که خودش ساخته رو می‌بینه؛ «پرنت»
    // علاوه بر خودش، مخاطب‌های همه‌ی زیرمجموعه‌های مستقیم/غیرمستقیمش رو
    // هم می‌بینه — بر اساس سازنده‌ی واقعیِ مخاطب (co.user_id)، نه صاحبِ
    // مشتری. سوپر ادمین همه‌چیز رو می‌بینه.
    if ($is_super) {
        $contacts = $pdo->query("
            SELECT co.*, cu.company_name AS customer_name, cu.id AS cid,
                   creator.full_name AS owner_name, co.user_id AS owner_id,
                   cu.user_id AS owner_scope_id, comp.name AS company_label
            FROM contacts co
            LEFT JOIN customers cu ON co.customer_id = cu.id
            LEFT JOIN users u ON cu.user_id = u.id
            LEFT JOIN users creator ON co.user_id = creator.id
            LEFT JOIN companies comp ON co.company_id = comp.id
            WHERE co.status = 'active'
            ORDER BY co.id DESC
        ")->fetchAll();
    } else {
        $scope_ids = crm_get_subtree_ids($user['id']);
        $in = implode(',', array_fill(0, count($scope_ids), '?'));
        $stmt = $pdo->prepare("
            SELECT co.*, cu.company_name AS customer_name, cu.id AS cid,
                   creator.full_name AS owner_name, co.user_id AS owner_id,
                   cu.user_id AS owner_scope_id, comp.name AS company_label
            FROM contacts co
            LEFT JOIN customers cu ON co.customer_id = cu.id
            LEFT JOIN users u ON cu.user_id = u.id
            LEFT JOIN users creator ON co.user_id = creator.id
            LEFT JOIN companies comp ON co.company_id = comp.id
            WHERE co.status = 'active' AND co.user_id IN ($in)
            ORDER BY co.id DESC
        ");
        $stmt->execute($scope_ids);
        $contacts = $stmt->fetchAll();
    }

    // ── گزینه‌های فیلتر (شرکت‌ها و ثبت‌کننده‌ها) — فقط از داده‌های قابل‌دسترس همین کاربر ──
    // این لیست قبل از اعمال فیلتر/جستجو ساخته می‌شود تا همیشه همه گزینه‌های ممکن نشان داده شوند
    $filter_companies = [];
    $filter_owners = [];
    foreach ($contacts as $c) {
        if (!empty($c['cid']) && !isset($filter_companies[$c['cid']])) {
            $filter_companies[$c['cid']] = $c['customer_name'];
        }
        if (!empty($c['owner_id']) && !isset($filter_owners[$c['owner_id']])) {
            $filter_owners[$c['owner_id']] = $c['owner_name'];
        }
    }
    asort($filter_companies, SORT_FLAG_CASE | SORT_STRING);
    asort($filter_owners, SORT_FLAG_CASE | SORT_STRING);

    // ── اعمال فیلتر شرکت ──
    // company_id: وقتی کاربر دقیقاً یکی از پیشنهادهای autocomplete را انتخاب کرده (فیلتر دقیق)
    // company_q : متن آزاد تایپ‌شده — وقتی با هیچ گزینه‌ای دقیقاً مطابق نبود (فیلتر شامل/تطبیقی)
    $filter_customer_id = $_GET['company_id'] ?? '';
    $filter_company_q   = trim($_GET['company_q'] ?? '');

    if ($filter_customer_id !== '' && isset($filter_companies[$filter_customer_id])) {
        $contacts = array_filter($contacts, function($c) use ($filter_customer_id) {
            return (string)$c['cid'] === (string)$filter_customer_id;
        });
    } elseif ($filter_company_q !== '') {
        $contacts = array_filter($contacts, function($c) use ($filter_company_q) {
            return mb_stripos($c['customer_name'] ?? '', $filter_company_q) !== false;
        });
        // فیلتر دقیق دیگر معتبر نیست چون کاربر متن آزاد تایپ کرده
        $filter_customer_id = '';
    }

    // ── اعمال فیلتر ثبت‌کننده (سازنده‌ی واقعی مخاطب) ──
    $filter_owner_id = $_GET['owner_id'] ?? '';
    if ($filter_owner_id !== '') {
        $contacts = array_filter($contacts, function($c) use ($filter_owner_id) {
            return (string)$c['owner_id'] === (string)$filter_owner_id;
        });
    }

    $search = trim($_GET['q'] ?? '');
    if ($search !== '') {
        $contacts = array_filter($contacts, function($c) use ($search) {
            return mb_stripos($c['full_name'], $search) !== false
                || mb_stripos($c['customer_name'], $search) !== false
                || mb_stripos($c['phone'] ?? '', $search) !== false
                || mb_stripos($c['position'] ?? '', $search) !== false;
        });
    }

    include __DIR__ . '/../Views/contacts/list.php';
}
// پیش‌فرض
else {
    header('Location: index.php?page=customers');
    exit;
}