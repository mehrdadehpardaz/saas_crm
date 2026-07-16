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

/**
 * گرفتن id شرکت بر اساس نام؛ اگر شرکتی با این نام وجود نداشت، ساخته می‌شود.
 * منبع واحد برای تبدیل «نام آزاد شرکت» (که جاهایی مثل ثبت‌نام یا فرم
 * سوپر ادمین به‌صورت متن آزاد گرفته می‌شود) به یک ردیف واقعی در جدول
 * companies — تا company_id همیشه به یک رکورد معتبر اشاره کند.
 */
function crm_get_or_create_company_id($name) {
    $name = trim((string)$name);
    if ($name === '') {
        return null;
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id FROM companies WHERE name = ? LIMIT 1");
    $stmt->execute([$name]);
    $existing = $stmt->fetchColumn();
    if ($existing) {
        return (int)$existing;
    }

    $stmt = $pdo->prepare("INSERT INTO companies (name) VALUES (?)");
    $stmt->execute([$name]);
    return (int)$pdo->lastInsertId();
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
 * به یک مشتری خاص را دارد یا نه.
 *
 * قانون: هر کاربری که company_id‌اش با company_id همان مشتری یکسان باشد،
 * به آن مشتری در صفحه‌ی مشتریان دسترسی دارد (بدون توجه به سلسله‌مراتب —
 * یعنی همه‌ی اعضای یک سازمان، همه‌ی مشتریان همان سازمان را می‌بینند).
 * این ساده‌ترین و مطمئن‌ترین راهه، چون مستقیم از روی ستون company_id
 * (نه رشته‌ی company_name و نه دنبال کردن زنجیره‌ی parent_id) مقایسه
 * می‌شود.
 */
function crm_user_can_access_customer($customer_id, $user = null) {
    $user = $user ?: crm_get_current_user();
    if (!$user || empty($customer_id)) return false;
    if ($user['role'] === 'super_admin') return true;

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT company_id FROM customers WHERE id = ?");
    $stmt->execute([(int)$customer_id]);
    $customer_company_id = $stmt->fetchColumn();
    if ($customer_company_id === false) return false;

    // اگر company_id یکی از دو طرف مشخص نیست (داده‌ی خیلی قدیمی، قبل از
    // مهاجرت جدول companies)، محافظه‌کارانه دسترسی رد می‌شود.
    if (empty($user['company_id']) || empty($customer_company_id)) return false;

    return (int)$user['company_id'] === (int)$customer_company_id;
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
 * آیا $ancestor_id یکی از «والدین» (مستقیم یا غیرمستقیم) $descendant_id
 * هست؟ یعنی با دنبال کردن زنجیره‌ی parent_id از $descendant_id به بالا،
 * در نهایت به $ancestor_id می‌رسیم؟ (خودِ فرد «والد خودش» حساب نمی‌شود —
 * آن را جدا چک کنید.)
 */
function crm_is_ancestor_of($ancestor_id, $descendant_id) {
    if (empty($ancestor_id) || empty($descendant_id)) return false;

    $pdo = getDB();
    $current_id = $descendant_id;
    for ($i = 0; $i < 10; $i++) {
        $stmt = $pdo->prepare("SELECT parent_id FROM users WHERE id = ?");
        $stmt->execute([$current_id]);
        $parent_id = $stmt->fetchColumn();
        if (!$parent_id) return false;
        if ((int)$parent_id === (int)$ancestor_id) return true;
        $current_id = $parent_id;
    }
    return false;
}

/**
 * زیردرخت کامل یک کاربر: خودش + همه‌ی زیرمجموعه‌های مستقیم و غیرمستقیمش
 * (بر اساس parent_id، بدون محدودیت عمق ثابت). برای فیلتر کردن لیست‌ها
 * (تسک‌ها، فعالیت‌ها، مخاطبین) استفاده می‌شود: «من + هر کسی که من مدیرشم».
 */
function crm_get_subtree_ids($user_id) {
    $ids = [(int)$user_id];
    $frontier = [(int)$user_id];
    $pdo = getDB();

    for ($depth = 0; $depth < 10 && !empty($frontier); $depth++) {
        $in = implode(',', array_fill(0, count($frontier), '?'));
        $stmt = $pdo->prepare("SELECT id FROM users WHERE parent_id IN ($in)");
        $stmt->execute($frontier);
        $children = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        $new_ids = array_values(array_diff($children, $ids));
        if (empty($new_ids)) break;

        $ids = array_merge($ids, $new_ids);
        $frontier = $new_ids;
    }

    return $ids;
}

/**
 * بررسی دسترسی به یک رکورد «مالکیت‌دار» (تسک، فعالیت یا مخاطب) که
 * توسط $creator_user_id ساخته شده.
 *
 * قانون: هر کاربر به چیزهایی که خودش ساخته (user_id خودش) کامل دسترسی
 * داره. علاوه بر این، «پرنت» (والدِ مستقیم یا غیرمستقیمِ سازنده، طبق
 * زنجیره‌ی parent_id) هم به همان اندازه‌ی خودِ سازنده دسترسی کامل داره —
 * یعنی مدیر همه‌چیزِ زیرمجموعه‌هاش رو می‌بینه، علاوه بر چیزهای خودش.
 * دسترسی جانبی (بین دو نفر هم‌سطح که والدِ هم نیستن) وجود ندارد، حتی اگر
 * هر دو عضو یک سازمان باشند.
 */
function crm_user_can_access_owned_record($creator_user_id, $user = null) {
    $user = $user ?: crm_get_current_user();
    if (!$user || empty($creator_user_id)) return false;
    if ($user['role'] === 'super_admin') return true;
    if ((int)$user['id'] === (int)$creator_user_id) return true;

    return crm_is_ancestor_of($user['id'], $creator_user_id);
}

/**
 * مثل crm_user_can_access_owned_record ولی در صورت نداشتن دسترسی
 * مستقیماً درخواست را با خطای ۴۰۳ متوقف می‌کند.
 */
function crm_require_owned_record_access($creator_user_id, $message = '⛔ شما به این مورد دسترسی ندارید.') {
    if (!crm_user_can_access_owned_record($creator_user_id)) {
        http_response_code(403);
        die('<div class="alert alert-error">' . $message . '</div>');
    }
}

/**
 * بررسی دسترسی به یک تسک — بر اساس سازنده‌ی خودِ تسک (user_id تسک)،
 * نه بر اساس مشتریِ صاحبِ آن. طبق قانون: سازنده + هر «پرنت» بالادستیِ
 * سازنده دسترسی کامل دارند.
 */
function crm_require_task_access($task) {
    if (!$task) {
        http_response_code(404);
        die('<div class="alert alert-error">⛔ تسک یافت نشد.</div>');
    }
    crm_require_owned_record_access($task['user_id'], '⛔ شما به این تسک دسترسی ندارید.');
}

/**
 * بررسی دسترسی به یک فعالیت — بر اساس سازنده‌ی خودِ فعالیت (user_id).
 */
function crm_require_activity_access($activity) {
    if (!$activity) {
        http_response_code(404);
        die('<div class="alert alert-error">⛔ فعالیت یافت نشد.</div>');
    }
    crm_require_owned_record_access($activity['user_id'], '⛔ شما به این فعالیت دسترسی ندارید.');
}

/**
 * بررسی دسترسی به یک مخاطب — بر اساس سازنده‌ی خودِ مخاطب (user_id مخاطب)،
 * نه بر اساس مشتریِ صاحبِ آن.
 */
function crm_require_contact_access($contact) {
    if (!$contact) {
        http_response_code(404);
        die('<div class="alert alert-error">⛔ مخاطب یافت نشد.</div>');
    }
    crm_require_owned_record_access($contact['user_id'] ?? null, '⛔ شما به این مخاطب دسترسی ندارید.');
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

require_once __DIR__ . '/plan_limits.php';