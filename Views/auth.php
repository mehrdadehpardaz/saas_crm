<!-- views/auth.php -->
<div class="auth-container">
    <div class="auth-card">
        <a href="landing.php" class="auth-back-link">→ بازگشت به صفحه اصلی</a>

        <div class="auth-top">
            <div class="logo">پ</div>
            <p class="auth-tagline">سیستم پیگیری مشتری</p>
        </div>

        <!-- سوییچ حالت — یک‌جا و واضح، به‌جای لینک گنگ پایین صفحه -->
        <div class="auth-mode-switch" role="tablist" aria-label="حالت ورود یا ثبت‌نام">
            <a href="?page=auth&mode=login"
               class="auth-mode-btn <?= $mode !== 'register' ? 'active' : '' ?>"
               role="tab" aria-selected="<?= $mode !== 'register' ? 'true' : 'false' ?>">ورود</a>
            <a href="?page=auth&mode=register"
               class="auth-mode-btn <?= $mode === 'register' ? 'active' : '' ?>"
               role="tab" aria-selected="<?= $mode === 'register' ? 'true' : 'false' ?>">ثبت‌نام</a>
        </div>

        <h2><?= $mode === 'register' ? 'ساخت حساب جدید' : 'ورود به حساب' ?></h2>

        <?php if ($error): ?>
            <div class="alert-error" role="alert"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="auth-form" novalidate>
            <?php if ($mode === 'register'): ?>
                <div class="form-group">
                    <label for="full_name">نام و نام خانوادگی *</label>
                    <input type="text" id="full_name" name="full_name" required
                           autocomplete="name" autofocus
                           placeholder="مثال: علی محمدی"
                           value="<?= crm_sanitize($_POST['full_name'] ?? '') ?>">
                </div>

                <button type="button" class="auth-more-toggle" id="auth-more-btn">
                    <span class="arrow" aria-hidden="true">›</span>
                    <span id="auth-more-label">افزودن نام شرکت (اختیاری)</span>
                </button>
                <div class="auth-more-fields<?= !empty($_POST['company_name']) ? ' open' : '' ?>" id="auth-more-fields">
                    <div class="form-group">
                        <label for="company_name">نام شرکت</label>
                        <input type="text" id="company_name" name="company_name"
                               placeholder="اگر خالی بگذارید، نام شما به‌عنوان شرکت ثبت می‌شود"
                               value="<?= crm_sanitize($_POST['company_name'] ?? '') ?>">
                        <small class="form-hint">اگر وارد نشود، نام شما به‌عنوان نام شرکت ثبت می‌شود.</small>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="mobile">شماره موبایل *</label>
                <input type="tel" id="mobile" name="mobile" required
                       inputmode="numeric" autocomplete="tel"
                       placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                       pattern="09[0-9]{9}" maxlength="11"
                       <?= $mode !== 'register' ? 'autofocus' : '' ?>
                       value="<?= crm_sanitize($_POST['mobile'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password">رمز عبور *</label>
                <div class="password-field">
                    <input type="password" id="password" name="password" required
                           autocomplete="<?= $mode === 'register' ? 'new-password' : 'current-password' ?>"
                           placeholder="<?= $mode === 'register' ? 'حداقل ۶ کاراکتر' : 'رمز عبور' ?>"
                           minlength="6">
                    <button type="button" class="password-toggle" id="password-toggle" aria-controls="password">نمایش</button>
                </div>
            </div>

            <input type="hidden" name="_csrf" value="<?= crm_csrf_token() ?>">

            <button type="submit" class="btn" id="auth-submit-btn">
                <?= $mode === 'register' ? 'ثبت‌نام' : 'ورود' ?>
            </button>
        </form>

        <?php if ($mode === 'register'): ?>
            <div class="auth-trial-note">
                <strong>۱۴ روز رایگان</strong> با سقف ۵ کاربر — بدون نیاز به کارت بانکی
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    // ── نمایش/پنهان‌سازی رمز عبور ──
    var pwdInput = document.getElementById('password');
    var pwdToggle = document.getElementById('password-toggle');
    if (pwdToggle) {
        pwdToggle.addEventListener('click', function () {
            var isHidden = pwdInput.type === 'password';
            pwdInput.type = isHidden ? 'text' : 'password';
            pwdToggle.textContent = isHidden ? 'پنهان' : 'نمایش';
        });
    }

    // ── باز/بسته کردن فیلد اختیاری «نام شرکت» ──
    var moreBtn = document.getElementById('auth-more-btn');
    if (moreBtn) {
        var moreFields = document.getElementById('auth-more-fields');
        var moreLabel = document.getElementById('auth-more-label');
        if (moreFields.classList.contains('open')) {
            moreBtn.classList.add('open');
            moreLabel.textContent = 'بستن';
        }
        moreBtn.addEventListener('click', function () {
            var isOpen = moreFields.classList.toggle('open');
            moreBtn.classList.toggle('open', isOpen);
            moreLabel.textContent = isOpen ? 'بستن' : 'افزودن نام شرکت (اختیاری)';
        });
    }

    // ── جلوگیری از ارسال دوباره فرم با چند بار کلیک ──
    var form = document.getElementById('auth-form');
    var submitBtn = document.getElementById('auth-submit-btn');
    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            if (form.checkValidity()) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'لطفاً صبر کنید...';
            }
        });
    }
})();
</script>