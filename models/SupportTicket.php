<?php
// models/SupportTicket.php

class SupportTicket {

    /**
     * ثبت تیکت جدید + اولین پیام (خودِ کاربر فرستنده)
     */
    public static function create($user_id, $subject, $first_message) {
        $pdo = getDB();

        try {
            $pdo->beginTransaction();

            // message اینجا فقط برای سازگاری با نسخه‌ی قدیمی نگه داشته شده؛
            // منبع اصلیِ متن از این به بعد جدول support_ticket_messages است.
            $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, subject, message, unread_by_admin, unread_by_user) VALUES (?, ?, ?, 1, 0)");
            $stmt->execute([$user_id, $subject, $first_message]);
            $ticket_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO support_ticket_messages (ticket_id, sender_user_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$ticket_id, $user_id, $first_message]);

            $pdo->commit();
            return $ticket_id;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * افزودن یک پیام جدید به یک تیکتِ موجود — هم صاحب تیکت می‌تونه
     * استفاده کنه، هم سوپر ادمین (پارامتر $is_admin_sender فرق می‌ذاره).
     *
     * قانون: وقتی سوپر ادمین پیام می‌ده → برای کاربر «خوانده‌نشده» می‌شه.
     * وقتی خودِ کاربر پیام می‌ده → برای سوپر ادمین «خوانده‌نشده» می‌شه.
     * اگه کاربر به یک تیکتِ بسته‌شده پیام جدید بده، خودکار دوباره باز می‌شه.
     */
    public static function addMessage($ticket_id, $sender_user_id, $message, $is_admin_sender) {
        $pdo = getDB();

        $stmt = $pdo->prepare("INSERT INTO support_ticket_messages (ticket_id, sender_user_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$ticket_id, $sender_user_id, $message]);

        if ($is_admin_sender) {
            $pdo->prepare("UPDATE support_tickets SET unread_by_user = 1, unread_by_admin = 0, updated_at = NOW() WHERE id = ?")
                ->execute([$ticket_id]);
        } else {
            $pdo->prepare("UPDATE support_tickets SET unread_by_admin = 1, unread_by_user = 0, updated_at = NOW(), status = IF(status = 'closed', 'open', status) WHERE id = ?")
                ->execute([$ticket_id]);
        }
    }

    /**
     * همه‌ی پیام‌های یک تیکت، به ترتیب زمانی (قدیمی‌ترین بالا)
     */
    public static function getMessages($ticket_id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT m.*, u.full_name AS sender_name, u.role AS sender_role
                                FROM support_ticket_messages m
                                JOIN users u ON m.sender_user_id = u.id
                                WHERE m.ticket_id = ?
                                ORDER BY m.created_at ASC, m.id ASC");
        $stmt->execute([$ticket_id]);
        return $stmt->fetchAll();
    }

    /** علامت‌زدن یک تیکت به‌عنوان «دیده‌شده توسط صاحبِ تیکت» */
    public static function markReadByUser($ticket_id) {
        $pdo = getDB();
        $pdo->prepare("UPDATE support_tickets SET unread_by_user = 0 WHERE id = ?")->execute([$ticket_id]);
    }

    /** علامت‌زدن یک تیکت به‌عنوان «دیده‌شده توسط سوپر ادمین» */
    public static function markReadByAdmin($ticket_id) {
        $pdo = getDB();
        $pdo->prepare("UPDATE support_tickets SET unread_by_admin = 0 WHERE id = ?")->execute([$ticket_id]);
    }

    /**
     * همه‌ی تیکت‌ها به همراه مشخصات کامل کاربرِ فرستنده — فقط برای سوپر ادمین.
     */
    public static function getAllWithUsers($status_filter = '') {
        $pdo = getDB();
        $sql = "SELECT t.*, 
                       u.full_name, u.mobile, u.role, u.company_name, u.phone AS user_phone, u.position_title
                FROM support_tickets t
                JOIN users u ON t.user_id = u.id";
        $params = [];
        if (!empty($status_filter)) {
            $sql .= " WHERE t.status = ?";
            $params[] = $status_filter;
        }
        $sql .= " ORDER BY t.unread_by_admin DESC, t.updated_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * تیکت‌های یک کاربر خاص (برای نمایش به خودش)
     */
    public static function getByUser($user_id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY unread_by_user DESC, updated_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    /**
     * یک تیکت با مشخصات کامل کاربرِ فرستنده (بدون متن پیام‌ها —
     * پیام‌ها را جدا با getMessages بگیرید)
     */
    public static function getById($id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT t.*, 
                       u.full_name, u.mobile, u.role, u.company_name, u.phone AS user_phone, 
                       u.position_title, u.created_at AS user_created_at, u.plan_type, u.plan_expiry
                FROM support_tickets t
                JOIN users u ON t.user_id = u.id
                WHERE t.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * تغییر وضعیت تیکت (فقط سوپر ادمین)
     */
    public static function updateStatus($id, $status) {
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE support_tickets SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    /** تعداد تیکت‌هایی که سوپر ادمین پیام خوانده‌نشده داره — برای badge نوبار */
    public static function countUnreadForAdmin() {
        $pdo = getDB();
        return (int)$pdo->query("SELECT COUNT(*) FROM support_tickets WHERE unread_by_admin = 1")->fetchColumn();
    }

    /** تعداد تیکت‌هایی که این کاربر پیام خوانده‌نشده داره — برای badge نوبار */
    public static function countUnreadForUser($user_id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets WHERE user_id = ? AND unread_by_user = 1");
        $stmt->execute([$user_id]);
        return (int)$stmt->fetchColumn();
    }
}