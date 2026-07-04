<!-- Views/plans/buy.php -->

<?php
$current_max = (int)$user['max_users_limit'];
$current_expiry = strtotime($user['plan_expiry']);
$is_active = $current_expiry > time() && ($user['status'] ?? 'active') === 'active';
$months_left = $is_active ? ceil(($current_expiry - time()) / (30 * 24 * 3600)) : 0;
if ($months_left < 1) $months_left = 0;

$base_plan = Plan::getByType('base');
$per_user_plan = Plan::getByType('per_user');
$initial_count = max($current_max, 1);
?>

<style>
.pb-wrap {
    --pb-ink:#14213D; --pb-ink-soft:#4A5578; --pb-ember:#FF6B35; --pb-ember-deep:#E6531E;
    --pb-teal:#16A085; --pb-teal-deep:#0E8170; --pb-paper:#FAF8F5; --pb-paper2:#F2EEE6;
    --pb-line:#E5DFD3; --pb-card:#FFFFFF; --pb-blue:#1a73e8; --pb-danger:#EA4335;
    direction: rtl;
    max-width: 720px;
    margin: 0 auto;
    width: 100%;
}

.pb-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.pb-header h2 { font-size:18px; font-weight:800; color:var(--pb-ink); letter-spacing:-.01em; }
.pb-btn-back {
    display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:9px;
    font-size:12.5px; font-weight:700; text-decoration:none; background:var(--pb-card);
    color:var(--pb-ink-soft); border:1.5px solid var(--pb-line); transition:all .15s;
}
.pb-btn-back:hover { border-color:var(--pb-ink); color:var(--pb-ink); }

.pb-alert-error {
    background:#FCE8E6; color:#C0392B; border:1px solid #F5C6CB; border-radius:10px;
    padding:11px 16px; font-size:13px; margin-bottom:16px;
}

/* وضعیت فعلی */
.pb-status-card {
    background:var(--pb-paper2); border:1px solid var(--pb-line); border-radius:13px;
    padding:16px 17px; margin-bottom:18px;
}
.pb-status-row { display:flex; justify-content:space-between; align-items:center; padding:6px 0; font-size:13px; }
.pb-status-row .label { color:var(--pb-ink-soft); display:flex; align-items:center; gap:6px; }
.pb-status-row .val { color:var(--pb-ink); font-weight:600; }
.pb-tag-ok   { color:var(--pb-teal-deep); font-size:11px; font-weight:600; }
.pb-tag-bad  { color:var(--pb-danger); font-size:11px; font-weight:600; }

.pb-warning {
    background:#FCE8E6; border:1px solid #F5C6CB; border-radius:11px; padding:13px 15px;
    font-size:12.5px; color:#8C3024; margin-bottom:18px; line-height:1.65;
}

/* فرم کارت */
.pb-form-card {
    background:var(--pb-card); border:1px solid var(--pb-line); border-radius:15px;
    padding:22px 20px; margin-bottom:16px;
}
.pb-form-card.accent-ember { border-right:4px solid var(--pb-ember); }
.pb-form-card.accent-teal  { border-right:4px solid var(--pb-teal); }
.pb-form-card h3 { font-size:15px; font-weight:700; color:var(--pb-ink); margin-bottom:9px; }
.pb-form-card .desc { font-size:12px; color:var(--pb-ink-soft); margin-bottom:16px; line-height:1.7; }
.pb-form-card .desc strong { color:var(--pb-ink); }

.pb-group { margin-bottom:16px; }
.pb-group label { display:block; font-size:12.5px; font-weight:700; color:var(--pb-ink-soft); margin-bottom:8px; }
.pb-group input[type=text] {
    width:100%; padding:11px 14px; border:1.5px solid var(--pb-line); border-radius:10px;
    font-size:13.5px; font-family:inherit; background:var(--pb-paper); color:var(--pb-ink);
}
.pb-group input[type=text]:focus { outline:none; border-color:var(--pb-ember); background:#fff; }
.pb-group select {
    width:100%; padding:11px 14px; border:1.5px solid var(--pb-line); border-radius:10px;
    font-size:13.5px; font-family:inherit; background:var(--pb-paper); color:var(--pb-ink);
}
.pb-group select:focus { outline:none; border-color:var(--pb-ember); background:#fff; }
.pb-hint { display:block; margin-top:7px; font-size:11.5px; color:var(--pb-ink-soft); }

/* counter */
.pb-counter { display:flex; align-items:center; gap:9px; }
.pb-counter-btn {
    width:40px; height:40px; font-size:19px; padding:0; border-radius:10px;
    background:var(--pb-paper2); border:1.5px solid var(--pb-line); color:var(--pb-ink);
    cursor:pointer; flex-shrink:0; transition:all .15s; display:flex; align-items:center; justify-content:center;
}
.pb-counter-btn:hover { background:var(--pb-ember); border-color:var(--pb-ember); color:#fff; }
.pb-counter input[type=number] {
    width:76px; text-align:center; font-size:18px; font-weight:800; padding:9px;
    border:1.5px solid var(--pb-line); border-radius:10px; color:var(--pb-ink); background:var(--pb-paper);
}
.pb-counter input[type=number]:focus { outline:none; border-color:var(--pb-ember); background:#fff; }
.pb-counter-unit { font-size:13px; color:var(--pb-ink-soft); }

/* price box */
.pb-price-box {
    background:var(--pb-paper2); border-radius:12px; padding:14px 16px; margin-bottom:16px;
}
.pb-price-row { display:flex; justify-content:space-between; align-items:center; padding:5px 0; font-size:13px; color:var(--pb-ink-soft); }
.pb-price-row .v { color:var(--pb-ink); font-weight:600; }
.pb-price-total {
    display:flex; justify-content:space-between; align-items:center;
    padding-top:11px; margin-top:6px; border-top:1.5px solid var(--pb-line);
    font-size:14px; font-weight:700; color:var(--pb-ink);
}
.pb-price-total .v { font-size:21px; font-weight:800; }
.pb-price-total.ember .v { color:var(--pb-ember-deep); }
.pb-price-total.teal .v  { color:var(--pb-teal-deep); }

/* submit buttons */
.pb-submit {
    width:100%; display:flex; align-items:center; justify-content:center; gap:8px;
    padding:13px 20px; border-radius:11px; font-size:14.5px; font-weight:700; border:none;
    cursor:pointer; color:#fff; transition:all .18s;
}
.pb-submit.ember { background:var(--pb-ember); box-shadow:0 4px 14px rgba(255,107,53,.3); }
.pb-submit.ember:hover { background:var(--pb-ember-deep); transform:translateY(-1px); }
.pb-submit.teal { background:var(--pb-teal); box-shadow:0 4px 14px rgba(22,160,133,.3); }
.pb-submit.teal:hover { background:var(--pb-teal-deep); transform:translateY(-1px); }
</style>

<div class="pb-wrap">

<div class="pb-header">
    <h2>🛒 خرید اشتراک</h2>
    <a href="index.php?page=plans" class="pb-btn-back">🔙 بازگشت</a>
</div>

<?php if ($error): ?>
    <div class="pb-alert-error"><?= $error ?></div>
<?php endif; ?>

<!-- وضعیت فعلی -->
<div class="pb-status-card">
    <div class="pb-status-row">
        <span class="label">🏢 شرکت</span>
        <span class="val"><?= crm_sanitize($user['company_name'] ?? 'ندارد') ?></span>
    </div>
    <div class="pb-status-row">
        <span class="label">👥 سقف فعلی</span>
        <span class="val"><?= $current_max ?> کاربر</span>
    </div>
    <div class="pb-status-row">
        <span class="label">⏰ پایان اشتراک</span>
        <span class="val">
            <?= function_exists('jdate') ? jdate($current_expiry) : date('Y/m/d', $current_expiry) ?>
            <?php if ($is_active): ?>
            <span class="pb-tag-ok">(<?= $months_left ?> ماه باقی‌مانده)</span>
            <?php else: ?>
            <span class="pb-tag-bad">(منقضی شده)</span>
            <?php endif; ?>
        </span>
    </div>
</div>

<?php if (!$is_active): ?>
    <!-- ===== حالت منقضی: فقط خرید کامل ===== -->
    <div class="pb-warning">⚠️ اشتراک شما منقضی شده است. برای فعال‌سازی مجدد باید اشتراک جدید خریداری کنید.</div>

    <form method="POST" action="index.php?page=plans&action=buy" id="buyForm" class="pb-form-card">

        <div class="pb-group">
            <label>👥 تعداد کاربران</label>
            <div class="pb-counter">
                <button type="button" class="pb-counter-btn" onclick="changeCount(-1)">−</button>
                <input type="number" name="user_count" id="user_count" value="1" min="1" max="100" onchange="calc()" oninput="calc()">
                <button type="button" class="pb-counter-btn" onclick="changeCount(1)">+</button>
                <span class="pb-counter-unit">کاربر</span>
            </div>
        </div>

        <?php if (empty($user['company_name'])): ?>
        <div class="pb-group">
            <label>🏢 نام شرکت</label>
            <input type="text" name="company_name" placeholder="مثال: شرکت فولاد مبارکه">
        </div>
        <?php endif; ?>

        <div class="pb-group">
            <label>📅 دوره اشتراک</label>
            <select name="period" id="period" onchange="calc()">
                <option value="monthly">📅 ماهانه</option>
                <option value="yearly">🗓️ سالانه (۲ ماه رایگان)</option>
            </select>
        </div>

        <div class="pb-price-box" id="price_box">
            <div class="pb-price-row"><span>🏠 هزینه پایه</span><span class="v" id="base_disp"></span></div>
            <div class="pb-price-row"><span>👤 <span id="uc_disp">1</span> کاربر</span><span class="v" id="users_disp"></span></div>
            <div class="pb-price-total ember"><span>💰 جمع کل</span><span class="v" id="total_disp"></span></div>
        </div>

        <input type="hidden" name="mode" value="full">
        <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
        <button type="submit" class="pb-submit ember">✅ پرداخت و فعال‌سازی</button>
    </form>

<?php else: ?>
    <!-- ===== حالت فعال: دو گزینه ===== -->

    <!-- گزینه ۱: خرید کاربر اضافه -->
    <form method="POST" action="index.php?page=plans&action=buy" class="pb-form-card accent-ember">
        <input type="hidden" name="mode" value="upgrade">
        <h3>👥 خرید کاربر اضافه (برای مدت باقی‌مانده)</h3>
        <p class="desc">
            📌 فقط هزینه کاربران اضافه برای <strong><?= $months_left ?> ماه</strong> باقی‌مانده محاسبه می‌شود.
            <br>⏰ تاریخ پایان اشتراک تغییر نمی‌کند.
        </p>

        <div class="pb-group">
            <label>کاربران جدید (کل)</label>
            <div class="pb-counter">
                <button type="button" class="pb-counter-btn" onclick="changeUpgradeCount(-1)">−</button>
                <input type="number" name="user_count" id="upgrade_count" value="<?= $current_max + 1 ?>" min="<?= $current_max + 1 ?>" max="100" onchange="calcUpgrade()" oninput="calcUpgrade()">
                <button type="button" class="pb-counter-btn" onclick="changeUpgradeCount(1)">+</button>
                <span class="pb-counter-unit">کاربر</span>
            </div>
            <small class="pb-hint">فعلی: <?= $current_max ?> کاربر | اضافه: <span id="extra_disp">1</span> کاربر</small>
        </div>

        <div class="pb-price-box">
            <div class="pb-price-row"><span>👤 کاربر اضافه</span><span class="v" id="upgrade_extra_disp"></span></div>
            <div class="pb-price-row"><span>📅 مدت باقی‌مانده</span><span class="v"><?= $months_left ?> ماه</span></div>
            <div class="pb-price-total ember"><span>💰 مبلغ</span><span class="v" id="upgrade_total_disp"></span></div>
        </div>

        <button type="submit" class="pb-submit ember">✅ خرید کاربر اضافه</button>
    </form>

    <!-- گزینه ۲: تمدید اشتراک -->
    <form method="POST" action="index.php?page=plans&action=buy" class="pb-form-card accent-teal">
        <input type="hidden" name="mode" value="renew">
        <h3>🔄 تمدید اشتراک</h3>
        <p class="desc">
            📌 دوره جدید به پایان اشتراک فعلی اضافه می‌شود.
            <?php if ($current_max > 0): ?>
            <br>⚠️ تعداد کاربر نمی‌تواند از سقف فعلی (<?= $current_max ?>) کمتر باشد.
            <?php endif; ?>
        </p>

        <div class="pb-group">
            <label>👥 تعداد کاربران</label>
            <div class="pb-counter">
                <button type="button" class="pb-counter-btn" onclick="changeRenewCount(-1)">−</button>
                <input type="number" name="user_count" id="renew_count" value="<?= $current_max ?>" min="<?= $current_max ?>" max="100" onchange="calcRenew()" oninput="calcRenew()">
                <button type="button" class="pb-counter-btn" onclick="changeRenewCount(1)">+</button>
                <span class="pb-counter-unit">کاربر</span>
            </div>
        </div>

        <div class="pb-group">
            <label>📅 دوره تمدید</label>
            <select name="period" id="renew_period" onchange="calcRenew()">
                <option value="monthly">📅 ماهانه</option>
                <option value="yearly">🗓️ سالانه (۲ ماه رایگان)</option>
            </select>
        </div>

        <div class="pb-price-box" id="renew_price_box">
            <div id="renew_same" style="display:block;">
                <div class="pb-price-row"><span>🏠 هزینه پایه</span><span class="v" id="renew_base_disp"></span></div>
                <div class="pb-price-row"><span>👤 <span id="renew_uc_disp"><?= $current_max ?></span> کاربر</span><span class="v" id="renew_users_disp"></span></div>
                <div class="pb-price-total teal"><span>💰 هزینه تمدید</span><span class="v" id="renew_total_disp"></span></div>
            </div>
            <div id="renew_mixed" style="display:none;">
                <div class="pb-price-row"><span>🔄 تمدید <?= $current_max ?> کاربر</span><span class="v" id="renew_base_part"></span></div>
                <div class="pb-price-row"><span>👤 <span id="renew_extra_label">0</span> کاربر اضافه (<?= $months_left ?> ماه)</span><span class="v" id="renew_extra_part"></span></div>
                <div class="pb-price-total ember"><span>💰 جمع کل</span><span class="v" id="renew_grand_total"></span></div>
            </div>
        </div>

        <button type="submit" class="pb-submit teal">✅ تمدید اشتراک</button>
    </form>
<?php endif; ?>

</div><!-- /pb-wrap -->

<script>
var baseM = <?= $base_plan['price_monthly'] ?>;
var baseY = <?= $base_plan['price_yearly'] ?>;
var perM = <?= $per_user_plan['price_monthly'] ?>;
var perY = <?= $per_user_plan['price_yearly'] ?>;
var curMax = <?= $current_max ?>;
var monthsLeft = <?= $months_left ?>;

function fmt(n) { return n.toLocaleString('fa-IR'); }

// ===== حالت منقضی =====
function changeCount(d) {
    var el = document.getElementById('user_count');
    var v = parseInt(el.value) + d;
    if (v >= 1 && v <= 100) { el.value = v; calc(); }
}
function calc() {
    var uc = parseInt(document.getElementById('user_count').value) || 1;
    var per = document.getElementById('period').value === 'yearly';
    var b = per ? baseY : baseM;
    var p = per ? perY : perM;
    var t = b + uc * p;
    document.getElementById('base_disp').textContent = fmt(b) + ' تومان';
    document.getElementById('users_disp').textContent = fmt(uc * p) + ' تومان';
    document.getElementById('uc_disp').textContent = uc;
    document.getElementById('total_disp').textContent = fmt(t) + ' تومان';
}

// ===== خرید کاربر اضافه =====
function changeUpgradeCount(d) {
    var el = document.getElementById('upgrade_count');
    var v = parseInt(el.value) + d;
    if (v > curMax && v <= 100) { el.value = v; calcUpgrade(); }
}
function calcUpgrade() {
    var uc = parseInt(document.getElementById('upgrade_count').value) || (curMax+1);
    if (uc <= curMax) uc = curMax+1;
    var ex = uc - curMax;
    var t = ex * perM * monthsLeft;
    document.getElementById('extra_disp').textContent = ex;
    document.getElementById('upgrade_extra_disp').textContent = ex + ' × ' + fmt(perM) + ' × ' + monthsLeft + ' ماه';
    document.getElementById('upgrade_total_disp').textContent = fmt(t) + ' تومان';
}

// ===== تمدید =====
function changeRenewCount(d) {
    var el = document.getElementById('renew_count');
    var v = parseInt(el.value) + d;
    if (v >= curMax && v <= 100) { el.value = v; calcRenew(); }
}

function calcRenew() {
    var uc = parseInt(document.getElementById('renew_count').value) || curMax;
    if (uc < curMax) uc = curMax;
    var per = document.getElementById('renew_period').value === 'yearly';
    var b = per ? baseY : baseM;
    var p = per ? perY : perM;

    if (uc === curMax) {
        document.getElementById('renew_same').style.display = 'block';
        document.getElementById('renew_mixed').style.display = 'none';

        var t = b + (uc * p);
        document.getElementById('renew_base_disp').textContent = fmt(b) + ' تومان';
        document.getElementById('renew_users_disp').textContent = fmt(uc * p) + ' تومان';
        document.getElementById('renew_uc_disp').textContent = uc;
        document.getElementById('renew_total_disp').textContent = fmt(t) + ' تومان';

    } else {
        document.getElementById('renew_same').style.display = 'none';
        document.getElementById('renew_mixed').style.display = 'block';

        var ex = uc - curMax;
        var renew_part = b + (uc * p);
        var extra_part = ex * perM * monthsLeft;
        var grand = renew_part + extra_part;

        document.getElementById('renew_base_part').textContent = '🔄 تمدید ' + uc + ' کاربر: ' + fmt(renew_part) + ' تومان';
        document.getElementById('renew_extra_label').textContent = ex;
        document.getElementById('renew_extra_part').textContent = '👤 ' + ex + ' کاربر اضافه × ' + fmt(perM) + ' × ' + monthsLeft + ' ماه = ' + fmt(extra_part) + ' تومان';
        document.getElementById('renew_grand_total').textContent = fmt(grand) + ' تومان';
    }
}

// اجرای اولیه
if (document.getElementById('user_count')) calc();
if (document.getElementById('upgrade_count')) calcUpgrade();
if (document.getElementById('renew_count')) calcRenew();
</script>