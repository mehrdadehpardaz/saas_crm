<!-- Views/profile/index.php -->

<style>
.pf-wrap {
    direction: rtl;
    max-width: 560px;
    margin: 0 auto;
    width: 100%;
}

.pf-header { margin-bottom:18px; }
.pf-header h2 { font-size:18px; font-weight:800; color:var(--ink); letter-spacing:-.01em; }

.pf-alert { padding:11px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; }
.pf-alert-success { background:#E7F7F3; color:var(--teal-deep); border:1px solid #B8E5DA; }
.pf-alert-error { background:#FCE8E6; color:var(--danger-deep); border:1px solid #F5C6CB; }

/* ── کارت‌ها ── */
.pf-card { background:var(--card); border:1px solid var(--line); border-radius:14px; overflow:hidden; margin-bottom:16px; }
.pf-card-hd { padding:14px 18px; border-bottom:1px solid var(--line); }
.pf-card-hd h3 { font-size:14px; font-weight:700; color:var(--ink); }
.pf-card-hd p { font-size:11.5px; color:var(--ink-soft); margin-top:3px; }
.pf-card-body { padding:18px; }

/* اطلاعات حساب — جدول کلید/مقدار فقط‌خواندنی */
.pf-info-table { width:100%; border-collapse:collapse; font-size:13px; }
.pf-info-table tr { border-bottom:1px solid var(--paper-2); }
.pf-info-table tr:last-child { border-bottom:none; }
.pf-info-table th { text-align:right; padding:10px 18px; color:var(--ink-soft); font-weight:600; font-size:12px; width:120px; white-space:nowrap; background:var(--paper-2); }
.pf-info-table td { padding:10px 18px; color:var(--ink); font-weight:500; }
.pf-tel-link { color:var(--blue); text-decoration:none; display:inline-flex; align-items:center; gap:5px; }
.pf-tel-link:hover { text-decoration:underline; }

/* فرم */
.pf-group { margin-bottom:16px; }
.pf-group:last-of-type { margin-bottom:0; }
.pf-group label { display:block; font-size:12.5px; font-weight:700; color:var(--ink-soft); margin-bottom:7px; }
.pf-group input[type=text],
.pf-group input[type=tel],
.pf-group input[type=password] {
    width:100%; padding:11px 14px; border:1.5px solid var(--line); border-radius:var(--radius);
    font-size:13.5px; font-family:inherit; background:var(--paper); color:var(--ink);
    transition:border-color .15s, background .15s;
}
.pf-group input:focus { outline:none; border-color:var(--ember); background:#fff; }
.pf-hint { display:block; margin-top:6px; font-size:11px; color:#8A8478; }

.pf-btn-submit {
    display:inline-flex; align-items:center; justify-content:center; gap:7px;
    padding:11px 24px; border-radius:11px; font-size:13.5px; font-weight:700; border:none;
    cursor:pointer; background:var(--ember); color:#fff; box-shadow:0 4px 14px rgba(255,107,53,.3);
    transition:all .15s;
}
.pf-btn-submit:hover { background:var(--ember-deep); transform:translateY(-1px); }

@media (max-width: 480px) {
    .pf-card-body { padding:16px; }
}
</style>

<div class="pf-wrap">

<div class="pf-header">
    <h2>پروفایل من</h2>
</div>

<?php if ($success): ?>
    <div class="pf-alert pf-alert-success"><?= crm_sanitize($success) ?></div>
<?php elseif ($error): ?>
    <div class="pf-alert pf-alert-error"><?= crm_sanitize($error) ?></div>
<?php endif; ?>

<!-- ═══ اطلاعات حساب — فقط‌خواندنی ═══ -->
<div class="pf-card">
    <div class="pf-card-hd">
        <h3>اطلاعات حساب</h3>
        <p>این موارد فقط توسط مدیر سیستم قابل تغییرند.</p>
    </div>
    <table class="pf-info-table">
        <tr>
            <th>شماره موبایل</th>
            <td style="direction:ltr;text-align:right">
                <a href="tel:<?= crm_sanitize($user['mobile']) ?>" class="pf-tel-link">📞 <?= crm_sanitize($user['mobile']) ?></a>
            </td>
        </tr>
        <?php if (!empty($user['company_name'])): ?>
        <tr><th>شرکت</th><td><?= crm_sanitize($user['company_name']) ?></td></tr>
        <?php endif; ?>
        <tr><th>نقش</th><td><?= crm_sanitize($role_label) ?></td></tr>
        <tr><th>تاریخ عضویت</th><td><?= function_exists('jdate') ? jdate($user['created_at']) : date('Y/m/d', strtotime($user['created_at'])) ?></td></tr>
    </table>
</div>

<!-- ═══ ویرایش اطلاعات پایه ═══ -->
<div class="pf-card">
    <div class="pf-card-hd"><h3>ویرایش اطلاعات</h3></div>
    <div class="pf-card-body">
        <form method="POST" action="index.php?page=profile">
            <input type="hidden" name="form" value="profile">
            <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>

            <div class="pf-group">
                <label for="pf-full-name">نام و نام خانوادگی *</label>
                <input type="text" id="pf-full-name" name="full_name" required
                       value="<?= crm_sanitize($user['full_name']) ?>">
            </div>

            <div class="pf-group">
                <label for="pf-position">سمت سازمانی</label>
                <input type="text" id="pf-position" name="position_title"
                       placeholder="مثال: کارشناس فروش"
                       value="<?= crm_sanitize($user['position_title'] ?? '') ?>">
            </div>

            <div class="pf-group">
                <label for="pf-phone">شماره تماس</label>
                <input type="tel" id="pf-phone" name="phone"
                       placeholder="مثال: ۰۹۱۲۳۴۵۶۷۸۹"
                       value="<?= crm_sanitize($user['phone'] ?? '') ?>">
            </div>

            <button type="submit" class="pf-btn-submit">ذخیره تغییرات</button>
        </form>
    </div>
</div>

<!-- ═══ تغییر رمز عبور ═══ -->
<div class="pf-card">
    <div class="pf-card-hd"><h3>تغییر رمز عبور</h3></div>
    <div class="pf-card-body">

        <?php if ($pwd_success): ?>
            <div class="pf-alert pf-alert-success"><?= crm_sanitize($pwd_success) ?></div>
        <?php elseif ($pwd_error): ?>
            <div class="pf-alert pf-alert-error"><?= crm_sanitize($pwd_error) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=profile">
            <input type="hidden" name="form" value="password">
            <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>

            <div class="pf-group">
                <label for="pf-current-pwd">رمز عبور فعلی *</label>
                <input type="password" id="pf-current-pwd" name="current_password" required autocomplete="current-password">
            </div>

            <div class="pf-group">
                <label for="pf-new-pwd">رمز عبور جدید *</label>
                <input type="password" id="pf-new-pwd" name="new_password" required minlength="6" autocomplete="new-password">
                <small class="pf-hint">حداقل ۶ کاراکتر</small>
            </div>

            <div class="pf-group">
                <label for="pf-confirm-pwd">تکرار رمز عبور جدید *</label>
                <input type="password" id="pf-confirm-pwd" name="confirm_password" required minlength="6" autocomplete="new-password">
            </div>

            <button type="submit" class="pf-btn-submit">تغییر رمز عبور</button>
        </form>
    </div>
</div>

</div><!-- /pf-wrap -->