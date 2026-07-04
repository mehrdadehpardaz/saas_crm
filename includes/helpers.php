<?php
// includes/helpers.php
function crm_sanitize($data) {
    if (is_array($data)) {
        return array_map('crm_sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

function crm_validate_mobile($mobile) {
    $mobile = preg_replace('/[^0-9]/', '', (string)$mobile);
    if (preg_match('/^09[0-9]{9}$/', $mobile)) {
        return $mobile;
    }
    return false;
}

function crm_generate_token() {
    return bin2hex(random_bytes(32));
}

function crm_redirect($url) {
    header("Location: $url");
    exit;
}

function crm_is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function crm_get_current_user() {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        return null;
    }
    
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([(int)$_SESSION['user_id']]);
    $result = $stmt->fetch();
    
    if ($result && is_array($result)) {
        return $result;
    }
    
    return null;
}

function crm_has_active_subscription($user) {
        if (!is_array($user)) return false;
        
        // اگه status غیرفعاله
        if (($user['status'] ?? 'active') === 'inactive') {
            return false;
        }
        
        // اگه admin یا super_admin نیست، plan_expiry رو از parent admin بگیر
        if (!in_array($user['role'], ['super_admin', 'admin']) && !empty($user['parent_id'])) {
            $pdo = getDB();
            $current_id = $user['parent_id'];
            
            // برو بالا تا برسی به admin
            for ($i = 0; $i < 5; $i++) {
                $stmt = $pdo->prepare("SELECT id, parent_id, role, plan_expiry, status FROM users WHERE id = ?");
                $stmt->execute([$current_id]);
                $parent = $stmt->fetch();
                
                if (!$parent) break;
                
                if (in_array($parent['role'], ['super_admin', 'admin'])) {
                    return strtotime($parent['plan_expiry']) > time() && ($parent['status'] ?? 'active') === 'active';
                }
                
                if (empty($parent['parent_id'])) break;
                $current_id = $parent['parent_id'];
            }
        }
        
        // برای admin/super_admin یا اگه parent پیدا نشد
        return strtotime($user['plan_expiry'] ?? 'now') > time();
    }

function crm_get_company_root($user_id) {
    // super_admin خودش root هست
    if (crm_is_super_admin()) {
        return $user_id;
    }
    
    $pdo = getDB();
    $current_id = $user_id;
    $max_loops = 10;
    $loops = 0;
    
    while ($loops < $max_loops) {
        $stmt = $pdo->prepare("SELECT id, parent_id FROM users WHERE id = ?");
        $stmt->execute([$current_id]);
        $user = $stmt->fetch();
        
        if (!$user || !$user['parent_id']) {
            return $current_id;
        }
        
        $current_id = $user['parent_id'];
        $loops++;
    }
    
    return $user_id;
}



function crm_get_company_members($root_id) {
    // super_admin همه رو میبینه
    if (crm_is_super_admin()) {
        $pdo = getDB();
        $stmt = $pdo->query("SELECT id FROM users");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    $pdo = getDB();
    
    $stmt = $pdo->prepare("SELECT company_name FROM users WHERE id = ?");
    $stmt->execute([$root_id]);
    $root = $stmt->fetch();
    
    if (!$root || empty($root['company_name'])) {
        return [(int)$root_id];
    }
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE company_name = ?");
    $stmt->execute([$root['company_name']]);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    return array_map('intval', $ids);
}


/**
 * بررسی می‌کند که آیا کاربر فعلی (یا کاربر مشخص‌شده) اجازه دسترسی
 * به یک مشتری خاص را دارد یا نه — بر اساس عضویت در همان شرکت.
 * این تابع باید قبل از هر عملیات update/delete/view روی منابعی که
 * به customer_id وصل هستند (activities, tasks, contacts) صدا زده شود.
 */
function crm_user_can_access_customer($customer_id, $user = null) {
    $user = $user ?: crm_get_current_user();
    if (!$user || empty($customer_id)) return false;
    if ($user['role'] === 'super_admin') return true;

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT user_id FROM customers WHERE id = ?");
    $stmt->execute([(int)$customer_id]);
    $owner_id = $stmt->fetchColumn();
    if ($owner_id === false) return false;

    $root_id    = crm_get_company_root($user['id']);
    $member_ids = crm_get_company_members($root_id);
    return in_array((int)$owner_id, $member_ids);
}

/**
 * مثل crm_user_can_access_customer ولی در صورت نداشتن دسترسی
 * مستقیماً درخواست را با خطای ۴۰۳ متوقف می‌کند.
 */
function crm_require_customer_access($customer_id, $user = null) {
    if (!crm_user_can_access_customer($customer_id, $user)) {
        http_response_code(403);
        die('<div class="alert alert-error">⛔ شما به این مشتری دسترسی ندارید.</div>');
    }
}

/**
 * بررسی دسترسی به یک تسک با استفاده از customer_id آن.
 */
function crm_require_task_access($task) {
    if (!$task) {
        http_response_code(404);
        die('<div class="alert alert-error">⛔ تسک یافت نشد.</div>');
    }
    crm_require_customer_access($task['customer_id']);
}

/**
 * بررسی دسترسی به یک فعالیت با استفاده از customer_id آن.
 */
function crm_require_activity_access($activity) {
    if (!$activity) {
        http_response_code(404);
        die('<div class="alert alert-error">⛔ فعالیت یافت نشد.</div>');
    }
    crm_require_customer_access($activity['customer_id']);
}

/**
 * بررسی دسترسی به یک مخاطب با استفاده از customer_id آن.
 */
function crm_require_contact_access($contact) {
    if (!$contact) {
        http_response_code(404);
        die('<div class="alert alert-error">⛔ مخاطب یافت نشد.</div>');
    }
    crm_require_customer_access($contact['customer_id']);
}

function crm_is_super_admin() {
    $user = crm_get_current_user();
    return $user && $user['role'] === 'super_admin';
}

function crm_is_admin() {
    $user = crm_get_current_user();
    return $user && in_array($user['role'], ['super_admin', 'admin']);
}

function crm_is_manager() {
    $user = crm_get_current_user();
    return $user && in_array($user['role'], ['super_admin', 'admin', 'manager']);
}

function jdate($date, $format = 'Y/m/d') {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') return '—';
    $ts = is_numeric($date) ? (int)$date : strtotime($date);
    if (!$ts || $ts <= 0) return '—';

    $gy = (int)date('Y', $ts);
    $gm = (int)date('n', $ts);
    $gd = (int)date('j', $ts);

    // الگوریتم صحیح تبدیل
    $g_days_in_month = [31,28,31,30,31,30,31,31,30,31,30,31];
    $j_days_in_month = [31,31,31,31,31,31,30,30,30,30,30,29];

    $gy -= 1600; $gm -= 1; $gd -= 1;
    $g_day_no = 365*$gy + (int)(($gy+3)/4) - (int)(($gy+99)/100) + (int)(($gy+399)/400);
    for ($i=0; $i<$gm; $i++) $g_day_no += $g_days_in_month[$i];
    if ($gm>1 && (($gy+1600)%4==0 && (($gy+1600)%100!=0 || ($gy+1600)%400==0))) $g_day_no++;
    $g_day_no += $gd;

    $j_day_no = $g_day_no - 79;
    $j_np = (int)($j_day_no/12053); $j_day_no %= 12053;
    $jy = 979 + 33*$j_np + 4*(int)($j_day_no/1461);
    $j_day_no %= 1461;
    if ($j_day_no >= 366) {
        $jy += (int)(($j_day_no-1)/365);
        $j_day_no = ($j_day_no-1)%365;
    }
    $jm = 0;
    for ($i=0; $i<11 && $j_day_no>=$j_days_in_month[$i]; $i++) {
        $j_day_no -= $j_days_in_month[$i];
        $jm++;
    }
    $jd = $j_day_no + 1;
    $jm += 1;

    $h = date('H',$ts); $i = date('i',$ts);
    $months = ['','فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];

    $out = '';
    for ($fi=0; $fi<strlen($format); $fi++) {
        switch($format[$fi]) {
            case 'Y': $out .= $jy; break;
            case 'm': $out .= str_pad($jm,2,'0',STR_PAD_LEFT); break;
            case 'n': $out .= $jm; break;
            case 'd': $out .= str_pad($jd,2,'0',STR_PAD_LEFT); break;
            case 'j': $out .= $jd; break;
            case 'H': $out .= $h; break;
            case 'i': $out .= $i; break;
            case 'F': $out .= $months[$jm]; break;
            default:  $out .= $format[$fi];
        }
    }
    return $out;
}

function jdatetime($date) { return jdate($date, 'Y/m/d H:i'); }


// ── CSRF Protection ──
function crm_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function crm_csrf_verify() {
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('درخواست نامعتبر است. لطفاً صفحه را رفرش کنید.');
    }
}

// ── Rate Limiting (لاگین) ──
function crm_rate_limit_check($key, $max = 5, $window = 300) {
    $k = 'rl_' . md5($key);
    if (empty($_SESSION[$k])) {
        $_SESSION[$k] = ['count' => 0, 'start' => time()];
    }
    if (time() - $_SESSION[$k]['start'] > $window) {
        $_SESSION[$k] = ['count' => 0, 'start' => time()];
    }
    $_SESSION[$k]['count']++;
    return $_SESSION[$k]['count'] <= $max;
}

function crm_rate_limit_remaining($key, $window = 300) {
    $k = 'rl_' . md5($key);
    if (empty($_SESSION[$k])) return $window;
    return max(0, $window - (time() - $_SESSION[$k]['start']));
}