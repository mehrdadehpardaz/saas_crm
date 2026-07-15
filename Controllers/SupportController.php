<?php
// controllers/SupportController.php
if (!defined('CRM_APP')) { http_response_code(403); exit('Direct access denied'); }

require_once __DIR__ . '/../models/SupportTicket.php';

$user = crm_get_current_user();
$is_super = ($user['role'] === 'super_admin');
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$error = '';
$success = '';

// ========== پردازش POST ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    crm_csrf_verify();

    // ثبت تیکت جدید — هر کاربری می‌تونه
    if ($action === 'create') {
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($subject === '' || $message === '') {
            $error = 'موضوع و متن پیام هر دو الزامی هستند.';
        } else {
            SupportTicket::create($user['id'], crm_sanitize($subject), crm_sanitize($message));
            header('Location: index.php?page=support&msg=created');
            exit;
        }
    }

    // افزودن پیام به یک تیکتِ موجود — هم صاحب تیکت، هم سوپر ادمین.
    // تغییر وضعیت تیکت فقط با سوپر ادمینه.
    if ($action === 'reply' && $id) {
        $ticket_meta = SupportTicket::getById($id);
        if (!$ticket_meta) {
            echo '<div class="alert alert-error">تیکت یافت نشد.</div>';
            exit;
        }
        if (!$is_super && (int)$ticket_meta['user_id'] !== (int)$user['id']) {
            http_response_code(403);
            die('<div class="alert alert-error">⛔ شما به این تیکت دسترسی ندارید.</div>');
        }

        $msg_text = trim($_POST['message'] ?? '');
        if ($msg_text !== '') {
            SupportTicket::addMessage($id, $user['id'], crm_sanitize($msg_text), $is_super);
        }

        // فقط سوپر ادمین می‌تونه وضعیت رو صریحاً عوض کنه
        if ($is_super && !empty($_POST['status']) && in_array($_POST['status'], ['open', 'in_progress', 'closed'])) {
            SupportTicket::updateStatus($id, $_POST['status']);
        }

        header('Location: index.php?page=support&action=view&id=' . $id . '&msg=replied');
        exit;
    }
}

// ========== نمایش ==========

if ($action === 'add') {
    include __DIR__ . '/../Views/support/form.php';
}
elseif ($action === 'view' && $id) {
    $ticket = SupportTicket::getById($id);
    if (!$ticket) {
        echo '<div class="alert alert-error">تیکت یافت نشد.</div>';
        exit;
    }
    // یک کاربر عادی فقط می‌تونه تیکت خودش رو ببینه؛ سوپر ادمین همه رو
    if (!$is_super && (int)$ticket['user_id'] !== (int)$user['id']) {
        http_response_code(403);
        die('<div class="alert alert-error">⛔ شما به این تیکت دسترسی ندارید.</div>');
    }

    // با باز شدن صفحه، پرچم «خوانده‌نشده» برای همون طرفی که داره می‌بینه پاک می‌شه
    if ($is_super) {
        SupportTicket::markReadByAdmin($id);
    } else {
        SupportTicket::markReadByUser($id);
    }

    $messages = SupportTicket::getMessages($id);
    $message = $_GET['msg'] ?? '';
    include __DIR__ . '/../Views/support/view.php';
}
else {
    $message = $_GET['msg'] ?? '';
    if ($is_super) {
        $status_filter = $_GET['status'] ?? '';
        $tickets = SupportTicket::getAllWithUsers($status_filter);
    } else {
        $tickets = SupportTicket::getByUser($user['id']);
    }
    include __DIR__ . '/../Views/support/list.php';
}