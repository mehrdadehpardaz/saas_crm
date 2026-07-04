<?php
// controllers/DashboardController.php — هماهنگ با تم پیگیر

require_once __DIR__ . '/../models/Activity.php';

$user = crm_get_current_user();
$is_manager = in_array($user['role'], ['super_admin', 'admin', 'manager']);
$is_admin   = in_array($user['role'], ['super_admin', 'admin']);
$subscription_active = crm_has_active_subscription($user);

$pdo = getDB();

if ($subscription_active) {
    $today_reminders    = Activity::getTodayReminders($user['id'], $is_manager);
    $upcoming_reminders = Activity::getUpcomingReminders($user['id'], $is_manager, 6);
    $today_count        = Activity::getTodayCount($user['id'], $is_manager);
    $total_customers    = Activity::getTotalCustomers($user['id'], $is_manager);

    // ── شمارش تسک‌های تکمیل‌شده / فروش / کنسل در ۳۰ روز اخیر (برای KPI) ──
    // نکته: قبلاً اینجا فقط زیرمجموعه «مستقیم» (parent_id = id) دیده می‌شد —
    // یعنی در سلسله‌مراتب ۲ سطح به بالا (admin → manager → agent) تسک‌های
    // agentهای زیر یک manager از دید admin گم می‌شدند. حالا با همان روش
    // company_name-based که در بقیه پروژه استفاده می‌شود هماهنگ شد.
    if ($is_manager) {
        $scope_root_id = crm_get_company_root($user['id']);
        $scope_ids     = crm_get_company_members($scope_root_id);
        if (empty($scope_ids)) $scope_ids = [(int)$user['id']];
    } else {
        $scope_ids = [(int)$user['id']];
    }
    $scope_in = implode(',', array_fill(0, count($scope_ids), '?'));

    $stmt = $pdo->prepare("SELECT
        SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status='sold' THEN 1 ELSE 0 END) as sold,
        SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled
        FROM tasks
        WHERE user_id IN ($scope_in)
        AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute($scope_ids);
    $task_kpi = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['completed'=>0,'sold'=>0,'cancelled'=>0];

    // ── نمودار ۷ روز اخیر: تعداد فعالیت ثبت‌شده هر روز ──
    // مبنای «۷ روز اخیر» هم با تاریخ PHP حساب می‌شود (نه CURDATE سمت MySQL)
    $week_start = date('Y-m-d', strtotime('-6 days'));
    $stmt = $pdo->prepare("SELECT DATE(created_at) as dt, COUNT(*) as cnt
        FROM activities
        WHERE user_id IN ($scope_in)
        AND created_at >= ?
        GROUP BY DATE(created_at)
        ORDER BY dt ASC");
    $stmt->execute(array_merge($scope_ids, [$week_start]));
    $weekly_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $weekly_chart = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $weekly_chart[] = ['dt' => $d, 'count' => (int)($weekly_raw[$d] ?? 0)];
    }
}

// ── وضعیت اشتراک/پلن (فقط برای admin) ──
if ($is_admin && !empty($user['company_name'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE company_name = ? AND status = 'active'");
    $stmt->execute([$user['company_name']]);
    $active_users_count = (int)$stmt->fetchColumn();
    $max_users_limit     = (int)($user['max_users_limit'] ?? 0);
    $plan_expiry_ts       = strtotime($user['plan_expiry'] ?? 'now');
    $days_left            = max(0, ceil(($plan_expiry_ts - time()) / 86400));
    $plan_type            = $user['plan_type'] ?? 'trial';
}

// ── جدول تسک‌های باز همه مشتری‌ها (دید کلی، فقط مشاهده) ──
if ($subscription_active) {
    if ($is_admin) {
        $stmt = $pdo->prepare("
            SELECT t.id, t.title, t.next_followup_date, t.next_followup_topic,
                   c.company_name, c.id as customer_id, u.full_name as owner_name
            FROM tasks t
            JOIN customers c ON t.customer_id = c.id
            JOIN users u ON t.user_id = u.id
            WHERE t.status = 'active' AND u.company_name = ?
            ORDER BY (t.next_followup_date IS NULL), t.next_followup_date ASC
            LIMIT 30
        ");
        $stmt->execute([$user['company_name']]);
    } else {
        $stmt = $pdo->prepare("
            SELECT t.id, t.title, t.next_followup_date, t.next_followup_topic,
                   c.company_name, c.id as customer_id, u.full_name as owner_name
            FROM tasks t
            JOIN customers c ON t.customer_id = c.id
            JOIN users u ON t.user_id = u.id
            WHERE t.status = 'active'
            AND t.user_id IN ($scope_in)
            ORDER BY (t.next_followup_date IS NULL), t.next_followup_date ASC
            LIMIT 30
        ");
        $stmt->execute($scope_ids);
    }
    $open_tasks_overview = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$role_labels = ['super_admin'=>'سوپر ادمین','admin'=>'مدیر','manager'=>'مدیر فروش','agent'=>'کارشناس'];
$role_label  = $role_labels[$user['role']] ?? $user['role'];

include __DIR__ . '/../Views/dashboard.php';