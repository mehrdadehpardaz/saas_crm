<?php
// controllers/ReportController.php
ob_start();

require_once __DIR__ . '/../models/Activity.php';
require_once __DIR__ . '/../models/Customer.php';

$user = crm_get_current_user();
$is_manager = in_array($user['role'], ['super_admin', 'admin', 'manager']);
$is_admin = in_array($user['role'], ['super_admin', 'admin']);
$is_super = ($user['role'] === 'super_admin');

$action = $_GET['action'] ?? ($is_manager ? 'managers' : 'self');

$pdo = getDB();

// ===== حالت: گزارش شخصی (برای همه) =====
if ($action === 'self') {
    
    $date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
    $date_to = $_GET['date_to'] ?? date('Y-m-d');
    
    // آمار کاربر
    $stmt = $pdo->prepare("SELECT 
        SUM(CASE WHEN type = 'call' THEN 1 ELSE 0 END) as calls,
        SUM(CASE WHEN type = 'meeting' THEN 1 ELSE 0 END) as meetings,
        SUM(CASE WHEN type = 'email' THEN 1 ELSE 0 END) as emails,
        SUM(CASE WHEN type = 'note' THEN 1 ELSE 0 END) as notes
        FROM activities WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$user['id'], $date_from, $date_to]);
    $stats = $stmt->fetch();
    $stats['total_activities'] = ($stats['calls'] ?? 0) + ($stats['meetings'] ?? 0) + ($stats['emails'] ?? 0) + ($stats['notes'] ?? 0);
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ? AND status = 'active'");
    $stmt->execute([$user['id'], $date_from, $date_to]);
    $stats['customers'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?) AND DATE(created_at) BETWEEN ? AND ? AND status = 'active'");
    $stmt->execute([$user['id'], $date_from, $date_to]);
    $stats['contacts'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$user['id'], $date_from, $date_to]);
    $stats['tasks'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'completed' AND DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$user['id'], $date_from, $date_to]);
    $stats['completed'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'sold' AND DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$user['id'], $date_from, $date_to]);
    $stats['sold'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'cancelled' AND DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$user['id'], $date_from, $date_to]);
    $stats['cancelled'] = $stmt->fetchColumn();
    
    // فعالیت روزانه
    $stmt = $pdo->prepare("SELECT DATE(created_at) as dt, 
        SUM(CASE WHEN type = 'call' THEN 1 ELSE 0 END) as calls,
        SUM(CASE WHEN type = 'meeting' THEN 1 ELSE 0 END) as meetings,
        SUM(CASE WHEN type = 'email' THEN 1 ELSE 0 END) as emails,
        SUM(CASE WHEN type = 'note' THEN 1 ELSE 0 END) as notes
        FROM activities WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at) ORDER BY dt DESC");
    $stmt->execute([$user['id'], $date_from, $date_to]);
    $daily_stats = $stmt->fetchAll();
    
    include __DIR__ . '/../Views/reports/self.php';
}

// ===== حالت: گزارش مدیران =====
elseif ($action === 'managers' && $is_manager) {
    
    $date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
    $date_to = $_GET['date_to'] ?? date('Y-m-d');
    $chart_type = $_GET['chart_type'] ?? 'total_activities';
    
    // لیست مدیران
    if ($is_super) {
        $managers = $pdo->query("SELECT id, full_name, company_name FROM users WHERE role IN ('admin', 'manager') ORDER BY full_name")->fetchAll();
    } elseif ($is_admin) {
        $stmt = $pdo->prepare("SELECT id, full_name, company_name FROM users WHERE company_name = ? AND role IN ('admin', 'manager') ORDER BY full_name");
        $stmt->execute([$user['company_name']]);
        $managers = $stmt->fetchAll();
    } else {
        $managers = [['id' => $user['id'], 'full_name' => $user['full_name'], 'company_name' => $user['company_name']]];
    }
    
    $managers_data = [];
    $all_child_ids = [];
    
    foreach ($managers as $mgr) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE parent_id = ?");
        $stmt->execute([$mgr['id']]);
        $child_ids = array_merge([$mgr['id']], $stmt->fetchAll(PDO::FETCH_COLUMN));
        $all_child_ids = array_merge($all_child_ids, $child_ids);
        
        $placeholders = implode(',', array_fill(0, count($child_ids), '?'));
        
        $stmt = $pdo->prepare("SELECT 
            SUM(CASE WHEN type = 'call' THEN 1 ELSE 0 END) as calls,
            SUM(CASE WHEN type = 'meeting' THEN 1 ELSE 0 END) as meetings,
            SUM(CASE WHEN type = 'email' THEN 1 ELSE 0 END) as emails,
            SUM(CASE WHEN type = 'note' THEN 1 ELSE 0 END) as notes
            FROM activities WHERE user_id IN ($placeholders) AND DATE(created_at) BETWEEN ? AND ?");
        $params = array_merge($child_ids, [$date_from, $date_to]);
        $stmt->execute($params);
        $activity_stats = $stmt->fetch();
        
        $total = ($activity_stats['calls'] ?? 0) + ($activity_stats['meetings'] ?? 0) + ($activity_stats['emails'] ?? 0) + ($activity_stats['notes'] ?? 0);
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE user_id IN ($placeholders) AND DATE(created_at) BETWEEN ? AND ? AND status = 'active'");
        $stmt->execute(array_merge($child_ids, [$date_from, $date_to]));
        $customers = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE customer_id IN (SELECT id FROM customers WHERE user_id IN ($placeholders)) AND DATE(created_at) BETWEEN ? AND ? AND status = 'active'");
        $stmt->execute(array_merge($child_ids, [$date_from, $date_to]));
        $contacts = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id IN ($placeholders) AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute(array_merge($child_ids, [$date_from, $date_to]));
        $tasks = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id IN ($placeholders) AND status = 'completed' AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute(array_merge($child_ids, [$date_from, $date_to]));
        $completed = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id IN ($placeholders) AND status = 'sold' AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute(array_merge($child_ids, [$date_from, $date_to]));
        $sold = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id IN ($placeholders) AND status = 'cancelled' AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute(array_merge($child_ids, [$date_from, $date_to]));
        $cancelled = $stmt->fetchColumn();
        
        $managers_data[] = array_merge($mgr, [
            'calls' => $activity_stats['calls'] ?? 0,
            'meetings' => $activity_stats['meetings'] ?? 0,
            'emails' => $activity_stats['emails'] ?? 0,
            'notes' => $activity_stats['notes'] ?? 0,
            'total_activities' => $total,
            'customers' => $customers,
            'contacts' => $contacts,
            'tasks' => $tasks,
            'completed' => $completed,
            'sold'      => $sold,
            'cancelled' => $cancelled,
            'team_count' => count($child_ids)
        ]);
    }
    
    // ===== Pie chart: سهم مشتری و مخاطب =====
    $pie_customers = [];
    $pie_contacts = [];
    
    if ($is_super) {
        $all_users_for_pie = $pdo->query("SELECT id, full_name FROM users")->fetchAll();
    } elseif ($is_admin) {
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE company_name = ?");
        $stmt->execute([$user['company_name']]);
        $all_users_for_pie = $stmt->fetchAll();
    } else {
        $all_users_for_pie = $pdo->query("SELECT id, full_name FROM users WHERE parent_id = " . $user['id'] . " OR id = " . $user['id'])->fetchAll();
    }
    
    $total_customers_all = 0;
    $total_contacts_all = 0;
    
    foreach ($all_users_for_pie as $u) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ? AND status = 'active'");
        $stmt->execute([$u['id'], $date_from, $date_to]);
        $c = $stmt->fetchColumn();
        $pie_customers[] = ['name' => $u['full_name'], 'value' => $c];
        $total_customers_all += $c;
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?) AND DATE(created_at) BETWEEN ? AND ? AND status = 'active'");
        $stmt->execute([$u['id'], $date_from, $date_to]);
        $co = $stmt->fetchColumn();
        $pie_contacts[] = ['name' => $u['full_name'], 'value' => $co];
        $total_contacts_all += $co;
    }
    
    include __DIR__ . '/../Views/reports/managers.php';
}

// ===== حالت: گزارش کاربران =====
elseif ($action === 'users' && $is_manager) {
    
    $date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
    $date_to = $_GET['date_to'] ?? date('Y-m-d');
    $chart_type = $_GET['chart_type'] ?? 'total_activities';
    $filter_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    
    if ($is_super) {
        $all_users = $pdo->query("SELECT id, full_name, role, company_name FROM users ORDER BY full_name")->fetchAll();
    } elseif ($is_admin) {
        $stmt = $pdo->prepare("SELECT id, full_name, role, company_name FROM users WHERE company_name = ? ORDER BY full_name");
        $stmt->execute([$user['company_name']]);
        $all_users = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("SELECT id, full_name, role, company_name FROM users WHERE parent_id = ? OR id = ? ORDER BY full_name");
        $stmt->execute([$user['id'], $user['id']]);
        $all_users = $stmt->fetchAll();
    }
    
    $users_data = [];
    
    foreach ($all_users as $u) {
        $stmt = $pdo->prepare("SELECT 
            SUM(CASE WHEN type = 'call' THEN 1 ELSE 0 END) as calls,
            SUM(CASE WHEN type = 'meeting' THEN 1 ELSE 0 END) as meetings,
            SUM(CASE WHEN type = 'email' THEN 1 ELSE 0 END) as emails,
            SUM(CASE WHEN type = 'note' THEN 1 ELSE 0 END) as notes
            FROM activities WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute([$u['id'], $date_from, $date_to]);
        $stats = $stmt->fetch();
        $stats['total_activities'] = ($stats['calls'] ?? 0) + ($stats['meetings'] ?? 0) + ($stats['emails'] ?? 0) + ($stats['notes'] ?? 0);
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ? AND status = 'active'");
        $stmt->execute([$u['id'], $date_from, $date_to]);
        $customers = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?) AND DATE(created_at) BETWEEN ? AND ? AND status = 'active'");
        $stmt->execute([$u['id'], $date_from, $date_to]);
        $contacts = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute([$u['id'], $date_from, $date_to]);
        $tasks = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'completed' AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute([$u['id'], $date_from, $date_to]);
        $completed = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'sold' AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute([$u['id'], $date_from, $date_to]);
        $sold = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'cancelled' AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->execute([$u['id'], $date_from, $date_to]);
        $cancelled = $stmt->fetchColumn();

        $users_data[] = array_merge($u, [
            'calls' => $stats['calls'] ?? 0,
            'meetings' => $stats['meetings'] ?? 0,
            'emails' => $stats['emails'] ?? 0,
            'notes' => $stats['notes'] ?? 0,
            'total_activities' => $stats['total_activities'],
            'customers' => $customers,
            'contacts' => $contacts,
            'tasks' => $tasks,
            'completed' => $completed,
            'sold'      => $sold,
            'cancelled' => $cancelled,
        ]);
    }
    
    $detail_user = null;
    $daily_detail = [];
    if ($filter_user_id > 0) {
        foreach ($users_data as $ud) {
            if ($ud['id'] == $filter_user_id) { $detail_user = $ud; break; }
        }
        if ($detail_user) {
            $stmt = $pdo->prepare("SELECT DATE(created_at) as dt, 
                SUM(CASE WHEN type = 'call' THEN 1 ELSE 0 END) as calls,
                SUM(CASE WHEN type = 'meeting' THEN 1 ELSE 0 END) as meetings,
                SUM(CASE WHEN type = 'email' THEN 1 ELSE 0 END) as emails,
                SUM(CASE WHEN type = 'note' THEN 1 ELSE 0 END) as notes
                FROM activities WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at) ORDER BY dt DESC");
            $stmt->execute([$filter_user_id, $date_from, $date_to]);
            $daily_detail = $stmt->fetchAll();
        }
    }
    
    // Pie chart
    $pie_customers = [];
    $pie_contacts = [];
    $total_customers_all = 0;
    $total_contacts_all = 0;
    
    foreach ($all_users as $u) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ? AND status = 'active'");
        $stmt->execute([$u['id'], $date_from, $date_to]);
        $c = $stmt->fetchColumn();
        $pie_customers[] = ['name' => $u['full_name'], 'value' => $c];
        $total_customers_all += $c;
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?) AND DATE(created_at) BETWEEN ? AND ? AND status = 'active'");
        $stmt->execute([$u['id'], $date_from, $date_to]);
        $co = $stmt->fetchColumn();
        $pie_contacts[] = ['name' => $u['full_name'], 'value' => $co];
        $total_contacts_all += $co;
    }
    
    include __DIR__ . '/../Views/reports/users.php';
}

ob_end_flush();