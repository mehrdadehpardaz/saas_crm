<!-- Views/customers/form.php -->
<?php
$is_edit = ($action === 'edit');
$customer_id = $id ?? null;
// آیا فیلدهای اختیاری از قبل مقدار دارند؟ اگر بله، باز نمایش بده
$has_extra_values = !empty($customer['industry_title']) || !empty($customer['email']) || !empty($customer['notes']) || !empty($customer['contact_person']) || !empty($primary_contact['phone']);
?>

<style>
.cf-wrap {
    direction: rtl;
    max-width: 560px;
    margin: 0 auto;
    width: 100%;
}

.cf-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.cf-header h2 { font-size:18px; font-weight:800; color:var(--ink); letter-spacing:-.01em; }
.cf-btn-back {
    display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:9px;
    font-size:12.5px; font-weight:700; text-decoration:none; background:var(--card);
    color:var(--ink-soft); border:1.5px solid var(--line); transition:all .15s;
}
.cf-btn-back:hover { border-color:var(--ink); color:var(--ink); }

.cf-alert-error {
    background:#FCE8E6; color:var(--danger-deep); border:1px solid #F5C6CB; border-radius:10px;
    padding:11px 16px; font-size:13px; margin-bottom:16px;
}

.cf-form {
    background:var(--card); border:1px solid var(--line); border-radius:16px;
    padding:26px 24px; box-shadow:var(--shadow);
}

.cf-group { margin-bottom:18px; }
.cf-group:last-of-type { margin-bottom:0; }
.cf-group label {
    display:block; font-size:12.5px; font-weight:700;
    color:var(--ink-soft); margin-bottom:7px;
}
.cf-group input[type=text],
.cf-group input[type=tel],
.cf-group input[type=email],
.cf-group textarea {
    width:100%; padding:11px 14px; border:1.5px solid var(--line); border-radius:var(--radius);
    font-size:13.5px; font-family:inherit; background:var(--paper); color:var(--ink);
    transition:border-color .15s, background .15s;
}
.cf-group input:focus, .cf-group textarea:focus {
    outline:none; border-color:var(--ember); background:#fff;
}
.cf-group textarea { resize:vertical; min-height:80px; }
.cf-row { display:flex; gap:8px; }
.cf-row > * { flex:1; }

/* دکمه باز کردن جزئیات بیشتر */
.cf-more-toggle {
    display:flex; align-items:center; gap:6px; background:none; border:none; cursor:pointer;
    color:var(--ink-soft); font-size:12.5px; font-weight:700; padding:8px 0; margin-bottom:6px;
    width:100%; text-align:right;
}
.cf-more-toggle:hover { color:var(--ember-deep); }
.cf-more-toggle .arrow { transition:transform .2s; font-size:11px; }
.cf-more-toggle.open .arrow { transform:rotate(90deg); }
.cf-more-fields { display:none; }
.cf-more-fields.open { display:block; }

/* ── Autocomplete ── */
.autocomplete-wrapper { position:relative; }
.autocomplete-wrapper input { width:100%; }
.autocomplete-suggestions {
    position:absolute; top:calc(100% + 4px); right:0; left:0; z-index:50;
    background:#fff; border:1.5px solid var(--line); border-radius:10px;
    box-shadow:var(--shadow-lg); max-height:220px; overflow-y:auto;
    display:none;
}
.autocomplete-suggestions.active { display:block; }
.autocomplete-suggestions .suggestion-item {
    padding:10px 14px; font-size:13px; color:var(--ink); cursor:pointer;
    transition:background .12s;
}
.autocomplete-suggestions .suggestion-item:hover { background:#FFF1EA; color:var(--ember-deep); }
.autocomplete-suggestions .suggestion-add {
    color:var(--ember-deep); font-weight:700; border-top:1px solid var(--line);
}

/* ── Actions ── */
.cf-actions { display:flex; gap:10px; margin-top:22px; padding-top:18px; border-top:1px solid var(--paper-2); }
.cf-btn-submit {
    flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
    padding:12px 20px; border-radius:11px; font-size:14px; font-weight:700; border:none;
    cursor:pointer; background:var(--ember); color:#fff; box-shadow:0 4px 14px rgba(255,107,53,.3);
    transition:all .15s;
}
.cf-btn-submit:hover { background:var(--ember-deep); transform:translateY(-1px); }
.cf-btn-cancel {
    padding:12px 22px; border-radius:11px; font-size:13.5px; font-weight:700; text-decoration:none;
    background:transparent; color:var(--ink-soft); border:1.5px solid var(--line); transition:all .15s;
}
.cf-btn-cancel:hover { border-color:var(--ink); color:var(--ink); }

@media(max-width:480px){
    .cf-form { padding:20px 16px; }
    .cf-actions { flex-direction:column-reverse; }
    .cf-row { flex-direction:column; }
}
</style>

<div class="cf-wrap">

<div class="cf-header">
    <h2><?= $is_edit ? 'ویرایش مشتری' : 'مشتری جدید' ?></h2>
    <a href="index.php?page=customers" class="cf-btn-back">بازگشت</a>
</div>

<?php if ($error): ?>
    <?php if (strpos($error, 'crm-upgrade-box') !== false): ?>
        <?= $error ?>
    <?php else: ?>
        <div class="cf-alert-error"><?= $error ?></div>
    <?php endif; ?>
<?php endif; ?>

<form method="POST" action="index.php?page=customers&action=<?= $is_edit ? 'update&id='.$customer_id : 'create' ?>" class="cf-form">

    <!-- فیلدهای اصلی — همیشه نمایش داده می‌شوند -->
    <div class="cf-group">
        <label for="company_name">نام شرکت *</label>
        <input type="text" id="company_name" name="company_name" required
               placeholder="مثال: فولاد مبارکه"
               value="<?= crm_sanitize($customer['company_name'] ?? $_POST['company_name'] ?? '') ?>">
    </div>

    <div class="cf-group">
        <label for="phone">تلفن شرکت</label>
        <input type="tel" id="phone" name="phone"
               placeholder="مثال: ۰۲۱۱۲۳۴۵۶۷۸"
               value="<?= crm_sanitize($customer['phone'] ?? $_POST['phone'] ?? '') ?>">
    </div>

    <!-- دکمه باز کردن فیلدهای اختیاری -->
    <button type="button" class="cf-more-toggle<?= $has_extra_values ? ' open' : '' ?>" id="cf-more-btn">
        <span class="arrow">›</span>
        <span id="cf-more-label"><?= $has_extra_values ? 'بستن جزئیات بیشتر' : 'افزودن جزئیات بیشتر (صنعت، ایمیل، یادداشت...)' ?></span>
    </button>

    <div class="cf-more-fields<?= $has_extra_values ? ' open' : '' ?>" id="cf-more-fields">

        <div class="cf-group">
            <label for="industry_search">حوزه فعالیت</label>
            <div class="autocomplete-wrapper">
                <input type="text"
                       id="industry_search"
                       placeholder="جستجو یا افزودن صنعت..."
                       value="<?= crm_sanitize($customer['industry_title'] ?? '') ?>"
                       autocomplete="off">
                <input type="hidden" id="industry_id" name="industry_id"
                       value="<?= $customer['industry_id'] ?? '' ?>">
                <div id="industry_suggestions" class="autocomplete-suggestions"></div>
            </div>
        </div>

        <div class="cf-row">
            <div class="cf-group">
                <label for="contact_person">نام شخص اصلی</label>
                <input type="text" id="contact_person" name="contact_person"
                       placeholder="مثال: آقای احمدی"
                       value="<?= crm_sanitize($customer['contact_person'] ?? $_POST['contact_person'] ?? '') ?>">
            </div>

            <div class="cf-group">
                <label for="contact_phone">📱 شماره همراه شخص اصلی</label>
                <input type="tel" id="contact_phone" name="contact_phone"
                       placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                       value="<?= crm_sanitize($primary_contact['phone'] ?? $_POST['contact_phone'] ?? '') ?>">
            </div>
        </div>

        <div class="cf-group">
            <label for="contact_position">سمت</label>
            <input type="text" id="contact_position" name="contact_position"
                   placeholder="مثلاً: مدیرعامل، بازرگانی، فنی..."
                   value="<?= crm_sanitize($primary_contact['position'] ?? $_POST['contact_position'] ?? '') ?>">
        </div>

        <div class="cf-group">
            <label for="email">ایمیل</label>
            <input type="email" id="email" name="email"
                   placeholder="example@company.com"
                   value="<?= crm_sanitize($customer['email'] ?? $_POST['email'] ?? '') ?>">
        </div>

        <div class="cf-group">
            <label for="notes">یادداشت</label>
            <textarea id="notes" name="notes" rows="3"
                      placeholder="یادداشت‌های خود را اینجا بنویسید..."><?= crm_sanitize($customer['notes'] ?? $_POST['notes'] ?? '') ?></textarea>
        </div>

    </div>

    <div class="cf-actions">
        <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
        <button type="submit" class="cf-btn-submit">
            <?= $is_edit ? 'بروزرسانی' : 'ثبت مشتری' ?>
        </button>
        <a href="index.php?page=customers" class="cf-btn-cancel">انصراف</a>
    </div>
</form>

</div><!-- /cf-wrap -->

<script src="assets/js/customers.js"></script>
<script>
(function(){
    var btn = document.getElementById('cf-more-btn');
    var fields = document.getElementById('cf-more-fields');
    var label = document.getElementById('cf-more-label');
    btn.addEventListener('click', function(){
        var isOpen = fields.classList.toggle('open');
        btn.classList.toggle('open', isOpen);
        label.textContent = isOpen ? 'بستن جزئیات بیشتر' : 'افزودن جزئیات بیشتر (صنعت، ایمیل، یادداشت...)';
    });
})();
</script>