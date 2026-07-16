<?php
// models/PlanTier.php
// مدل تعرفه‌های ثابت جدید (پایه / حرفه‌ای / نامحدود).
// همه‌ی اعداد این مدل (قیمت و سقف‌ها) از دیتابیس خوانده می‌شوند، پس
// از صفحه‌ی مدیریت پلن‌ها (super_admin) قابل تغییرند بدون نیاز به کد نویسی.

class PlanTier {

    public static function getAll() {
        $pdo = getDB();
        return $pdo->query("SELECT * FROM plan_tiers ORDER BY sort_order ASC")->fetchAll();
    }

    public static function getById($id) {
        if (empty($id)) return null;
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM plan_tiers WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getBySlug($slug) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM plan_tiers WHERE slug = ?");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * ویرایش یک تعرفه. مقادیر خالی در سقف‌ها به NULL (نامحدود) تبدیل می‌شوند.
     */
    public static function update($id, array $data) {
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE plan_tiers SET
                name = ?, price_monthly = ?,
                max_users = ?, max_customers = ?, max_contacts = ?,
                backup_access = ?, management_reports = ?, full_access = ?
            WHERE id = ?");

        $norm = function ($v) {
            $v = trim((string)($v ?? ''));
            return ($v === '') ? null : (int)$v;
        };

        return $stmt->execute([
            trim($data['name'] ?? ''),
            (int)($data['price_monthly'] ?? 0),
            $norm($data['max_users'] ?? ''),
            $norm($data['max_customers'] ?? ''),
            $norm($data['max_contacts'] ?? ''),
            in_array($data['backup_access'] ?? '', ['one_monthly', 'unlimited']) ? $data['backup_access'] : 'unlimited',
            !empty($data['management_reports']) ? 1 : 0,
            !empty($data['full_access']) ? 1 : 0,
            (int)$id,
        ]);
    }
}