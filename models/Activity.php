<?php
// models/Activity.php

class Activity {

    /**
     * زیردرخت کاربر (خودش + همه‌ی زیرمجموعه‌های مستقیم/غیرمستقیمش) —
     * برای فیلتر کردن تسک‌ها و فعالیت‌ها استفاده می‌شود.
     *
     * قانون: هر کاربر چیزهای خودش رو می‌بینه؛ «پرنت» علاوه بر خودش،
     * هر چیزی که زیرمجموعه‌هاش (فرزندانش) قابل دیدنه رو هم می‌بینه.
     * این دسترسی جانبی بین هم‌سطح‌ها (حتی در یک سازمان) نمی‌ده — بر خلاف
     * مشتری‌ها که سازمانی/company-wide هستند.
     */
    private static function getScopeIds($user_id) {
        return crm_get_subtree_ids($user_id);
    }

    /**
     * همه‌ی اعضای سازمانِ کاربر (بر اساس company_id) — فقط برای فیلتر
     * کردن چیزهای «company-wide» مثل لیست/تعداد مشتریان استفاده می‌شود؛
     * تسک‌ها و فعالیت‌ها باید از getScopeIds (زیردرخت سلسله‌مراتبی) استفاده کنند.
     */
    private static function getCompanyScopeIds($user_id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT company_id, role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();

        if (!$row) return [(int)$user_id];
        if (($row['role'] ?? '') === 'super_admin') {
            $stmt = $pdo->query("SELECT id FROM users");
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        }
        if (empty($row['company_id'])) return [(int)$user_id];

        $stmt = $pdo->prepare("SELECT id FROM users WHERE company_id = ?");
        $stmt->execute([$row['company_id']]);
        $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        return !empty($ids) ? $ids : [(int)$user_id];
    }

    private static function inClause(array $ids) {
        return implode(',', array_fill(0, count($ids), '?'));
    }

    /**
     * گرفتن ریمایندرهای «باید همین الان پیگیری شوند» — یعنی امروز + تأخیردار
     *
     * نکته مهم: «امروز» را در PHP حساب می‌کنیم (date('Y-m-d')) و به‌عنوان
     * پارامتر به کوئری می‌دهیم — به‌جای تکیه به CURDATE() سمت MySQL.
     * اگر تایم‌زون دیتابیس با تایم‌زون سرور/اپ یکی نباشد (مثلاً MySQL روی UTC
     * و اپ روی Asia/Tehran)، CURDATE() می‌تواند چند ساعت جلو/عقب‌تر از
     * «امروز» واقعی کاربر باشد و باعث شود تسک‌های دقیقاً امروز دیده نشوند.
     */
    public static function getTodayReminders($user_id, $is_manager = false) {
        $pdo   = getDB();
        $today = date('Y-m-d');

        if ($is_manager) {
            $ids = self::getScopeIds($user_id);
            $in  = self::inClause($ids);
            $sql = "SELECT t.*, c.company_name, u.full_name as agent_name
                    FROM tasks t
                    JOIN customers c ON t.customer_id = c.id
                    JOIN users u ON t.user_id = u.id
                    WHERE t.status = 'active'
                    AND t.next_followup_date IS NOT NULL
                    AND DATE(t.next_followup_date) <= ?
                    AND t.user_id IN ($in)
                    ORDER BY t.next_followup_date ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge([$today], $ids));
        } else {
            $sql = "SELECT t.*, c.company_name, u.full_name as agent_name
                    FROM tasks t
                    JOIN customers c ON t.customer_id = c.id
                    JOIN users u ON t.user_id = u.id
                    WHERE t.status = 'active'
                    AND t.next_followup_date IS NOT NULL
                    AND DATE(t.next_followup_date) <= ?
                    AND t.user_id = ?
                    ORDER BY t.next_followup_date ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$today, $user_id]);
        }

        return $stmt->fetchAll();
    }

    /**
     * گرفتن ریمایندرهای آینده (از tasks) — همان مبنای «امروز» با PHP
     */
    public static function getUpcomingReminders($user_id, $is_manager = false, $limit = 5) {
        $pdo   = getDB();
        $today = date('Y-m-d');

        if ($is_manager) {
            $ids = self::getScopeIds($user_id);
            $in  = self::inClause($ids);
            $sql = "SELECT t.*, c.company_name, u.full_name as agent_name
                    FROM tasks t
                    JOIN customers c ON t.customer_id = c.id
                    JOIN users u ON t.user_id = u.id
                    WHERE t.status = 'active'
                    AND t.next_followup_date IS NOT NULL
                    AND DATE(t.next_followup_date) > ?
                    AND t.user_id IN ($in)
                    ORDER BY t.next_followup_date ASC
                    LIMIT ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge([$today], $ids, [$limit]));
        } else {
            $sql = "SELECT t.*, c.company_name, u.full_name as agent_name
                    FROM tasks t
                    JOIN customers c ON t.customer_id = c.id
                    JOIN users u ON t.user_id = u.id
                    WHERE t.status = 'active'
                    AND t.next_followup_date IS NOT NULL
                    AND DATE(t.next_followup_date) > ?
                    AND t.user_id = ?
                    ORDER BY t.next_followup_date ASC
                    LIMIT ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$today, $user_id, $limit]);
        }

        return $stmt->fetchAll();
    }

    /**
     * تعداد پیگیری‌هایی که باید همین الان انجام شوند — امروز + تأخیردار
     * (هماهنگ با getTodayReminders تا عدد KPI با کارت اصلی داشبورد یکی باشد)
     */
    public static function getTodayCount($user_id, $is_manager = false) {
        $pdo   = getDB();
        $today = date('Y-m-d');

        if ($is_manager) {
            $ids = self::getScopeIds($user_id);
            $in  = self::inClause($ids);
            $sql = "SELECT COUNT(*) FROM tasks 
                    WHERE status = 'active'
                    AND next_followup_date IS NOT NULL
                    AND DATE(next_followup_date) <= ?
                    AND user_id IN ($in)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge([$today], $ids));
        } else {
            $sql = "SELECT COUNT(*) FROM tasks 
                    WHERE status = 'active'
                    AND next_followup_date IS NOT NULL
                    AND DATE(next_followup_date) <= ?
                    AND user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$today, $user_id]);
        }

        return $stmt->fetchColumn();
    }

    /**
     * تعداد کل مشتریان
     */
    public static function getTotalCustomers($user_id, $is_manager = false) {
        $pdo = getDB();

        $ids = self::getCompanyScopeIds($user_id);
        $in  = self::inClause($ids);
        $sql = "SELECT COUNT(*) FROM customers WHERE user_id IN ($in)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);

        return $stmt->fetchColumn();
    }

    /**
     * گرفتن همه فعالیت‌ها
     */
    public static function getAll($user_id, $is_manager = false, $filters = []) {
        $pdo = getDB();

        $sql = "SELECT a.*, c.company_name, co.full_name as contact_name, u.full_name as agent_name,
                       t.title as task_title, comp.name as company_label
                FROM activities a
                JOIN customers c ON a.customer_id = c.id
                LEFT JOIN contacts co ON a.contact_id = co.id
                JOIN users u ON a.user_id = u.id
                LEFT JOIN tasks t ON a.task_id = t.id
                LEFT JOIN companies comp ON a.company_id = comp.id
                WHERE 1=1";

        $params = [];

        if ($is_manager) {
            $ids = self::getScopeIds($user_id);
            $in  = self::inClause($ids);
            $sql .= " AND a.user_id IN ($in)";
            $params = array_merge($params, $ids);
        } else {
            $sql .= " AND a.user_id = ?";
            $params[] = $user_id;
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND a.customer_id = ?";
            $params[] = $filters['customer_id'];
        }

        if (!empty($filters['task_id'])) {
            $sql .= " AND a.task_id = ?";
            $params[] = $filters['task_id'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND a.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['date'])) {
            $sql .= " AND DATE(a.created_at) = ?";
            $params[] = $filters['date'];
        }

        $sql .= " ORDER BY a.created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * گرفتن یک فعالیت
     */
    public static function getById($id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT a.*, c.company_name, co.full_name as contact_name, t.title as task_title
                               FROM activities a
                               JOIN customers c ON a.customer_id = c.id
                               LEFT JOIN contacts co ON a.contact_id = co.id
                               LEFT JOIN tasks t ON a.task_id = t.id
                               WHERE a.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * ایجاد فعالیت جدید
     *
     * نکته: قبلاً created_at همیشه از DEFAULT CURRENT_TIMESTAMP جدول پر می‌شد،
     * یعنی حتی اگر کاربر توی فرم تاریخ/ساعت دیگری وارد می‌کرد، نادیده گرفته
     * می‌شد. حالا اگر 'created_at' توی $data داده شده باشد (از فرم)، همان
     * مقدار ثبت می‌شود؛ در غیر این صورت (مثلاً فراخوانی‌های داخلی سیستم مثل
     * ثبت خودکار «تسک تکمیل شد») همان لحظه‌ی فعلی استفاده می‌شود.
     */
    public static function create($data) {
        $pdo = getDB();
        $created_at = !empty($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO activities (user_id, customer_id, task_id, contact_id, company_id, type, description, created_at) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['user_id'],
            $data['customer_id'],
            $data['task_id'] ?? null,
            $data['contact_id'] ?? null,
            $data['company_id'] ?? null,
            $data['type'] ?? 'call',
            $data['description'] ?? null,
            $created_at
        ]);
    }

    /**
     * آپدیت فعالیت
     *
     * اگر 'created_at' توی $data داده شده باشد (کاربر تاریخ/ساعت فعالیت رو
     * توی فرم ویرایش عوض کرده)، همون مقدار جدید هم ذخیره می‌شود؛ در غیر
     * این صورت تاریخ/ساعت قبلی فعالیت دست‌نخورده می‌ماند.
     */
    public static function update($id, $data) {
        $pdo = getDB();

        if (!empty($data['created_at'])) {
            $stmt = $pdo->prepare("UPDATE activities SET 
                                   contact_id = ?, type = ?, description = ?, created_at = ?
                                   WHERE id = ?");
            return $stmt->execute([
                $data['contact_id'] ?? null,
                $data['type'] ?? 'call',
                $data['description'] ?? null,
                $data['created_at'],
                $id
            ]);
        }

        $stmt = $pdo->prepare("UPDATE activities SET 
                               contact_id = ?, type = ?, description = ?
                               WHERE id = ?");
        return $stmt->execute([
            $data['contact_id'] ?? null,
            $data['type'] ?? 'call',
            $data['description'] ?? null,
            $id
        ]);
    }

    /**
     * حذف فعالیت
     */
    public static function delete($id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM activities WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * گرفتن مشتریان برای dropdown
     */
    public static function getCustomersForDropdown($user_id, $is_manager = false) {
        $pdo = getDB();

        $ids = self::getCompanyScopeIds($user_id);
        $in  = self::inClause($ids);
        $stmt = $pdo->prepare("SELECT id, company_name FROM customers 
                            WHERE user_id IN ($in) 
                            AND status = 'active'
                            ORDER BY company_name ASC");
        $stmt->execute($ids);

        return $stmt->fetchAll();
    }
}