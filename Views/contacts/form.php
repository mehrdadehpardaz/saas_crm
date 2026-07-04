<!-- Views/contacts/form.php -->

<style>
.ctf-wrap {
    --ctf-ink:#14213D; --ctf-ink-soft:#4A5578; --ctf-ember:#FF6B35; --ctf-ember-deep:#E6531E;
    --ctf-paper:#FAF8F5; --ctf-paper2:#F2EEE6; --ctf-line:#E5DFD3; --ctf-card:#FFFFFF;
    direction: rtl;
    max-width: 720px;
    margin: 0 auto;
    width: 100%;
}

.ctf-header { margin-bottom:18px; }
.ctf-back-link {
    display:inline-flex; align-items:center; gap:5px; font-size:12px; color:var(--ctf-ink-soft);
    text-decoration:none; margin-bottom:6px;
}
.ctf-back-link:hover { color:var(--ctf-ember-deep); }
.ctf-header h2 { font-size:18px; font-weight:800; color:var(--ctf-ink); letter-spacing:-.01em; }

.ctf-alert-error {
    background:#FCE8E6; color:#C0392B; border:1px solid #F5C6CB; border-radius:10px;
    padding:11px 16px; font-size:13px; margin-bottom:16px;
}

.ctf-form {
    background:var(--ctf-card); border:1px solid var(--ctf-line); border-radius:16px;
    padding:26px 24px; box-shadow:0 4px 20px rgba(20,33,61,.05);
}

.ctf-group { margin-bottom:18px; }
.ctf-group:last-of-type { margin-bottom:0; }
.ctf-group label {
    display:flex; align-items:center; gap:6px; font-size:12.5px; font-weight:700;
    color:var(--ctf-ink-soft); margin-bottom:7px;
}
.ctf-group input[type=text],
.ctf-group input[type=tel],
.ctf-group input[type=email] {
    width:100%; padding:11px 14px; border:1.5px solid var(--ctf-line); border-radius:10px;
    font-size:13.5px; font-family:inherit; background:var(--ctf-paper); color:var(--ctf-ink);
    transition:border-color .15s, background .15s;
}
.ctf-group input:focus { outline:none; border-color:var(--ctf-ember); background:#fff; }

/* primary checkbox */
.ctf-primary-box {
    background:var(--ctf-paper2); border:1px solid var(--ctf-line); border-radius:12px;
    padding:14px 16px;
}
.ctf-primary-label {
    display:flex; align-items:center; gap:10px; cursor:pointer; font-size:13.5px;
    font-weight:700; color:var(--ctf-ink); margin-bottom:0 !important;
}
.ctf-primary-label input[type=checkbox] {
    width:18px; height:18px; margin:0; cursor:pointer; accent-color:var(--ctf-ember); flex-shrink:0;
}
.ctf-hint { display:block; margin-top:7px; font-size:11px; color:#8A8478; padding-right:28px; }

/* ── Actions ── */
.ctf-actions { display:flex; gap:10px; margin-top:22px; padding-top:18px; border-top:1px solid var(--ctf-paper2); }
.ctf-btn-submit {
    flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
    padding:12px 20px; border-radius:11px; font-size:14px; font-weight:700; border:none;
    cursor:pointer; background:var(--ctf-ember); color:#fff; box-shadow:0 4px 14px rgba(255,107,53,.3);
    transition:all .15s;
}
.ctf-btn-submit:hover { background:var(--ctf-ember-deep); transform:translateY(-1px); }
.ctf-btn-cancel {
    padding:12px 22px; border-radius:11px; font-size:13.5px; font-weight:700; text-decoration:none;
    background:transparent; color:var(--ctf-ink-soft); border:1.5px solid var(--ctf-line); transition:all .15s;
}
.ctf-btn-cancel:hover { border-color:var(--ctf-ink); color:var(--ctf-ink); }

@media(max-width:480px){
    .ctf-form { padding:20px 16px; }
    .ctf-actions { flex-direction:column-reverse; }
}
</style>

<div class="ctf-wrap">

<div class="ctf-header">
    <a href="index.php?page=customers&action=view&id=<?= $customer['id'] ?>" class="ctf-back-link">
        ← <?= crm_sanitize($customer['company_name']) ?>
    </a>
    <h2><?= $is_edit ? '✏️ ویرایش مخاطب' : '➕ مخاطب جدید' ?></h2>
</div>

<?php if ($error): ?>
    <div class="ctf-alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" action="index.php?page=contacts&action=<?= $is_edit ? 'update&id='.$id : 'create' ?>" class="ctf-form">

    <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">

    <div class="ctf-group">
        <label>👤 نام و نام خانوادگی *</label>
        <input type="text" name="full_name" required
               placeholder="مثال: آقای محمدی"
               value="<?= crm_sanitize($contact['full_name'] ?? $_POST['full_name'] ?? '') ?>">
    </div>

    <div class="ctf-group">
        <label>💼 سمت</label>
        <input type="text" name="position"
               placeholder="مثال: مدیرعامل، بازرگانی، مسئول خرید..."
               value="<?= crm_sanitize($contact['position'] ?? $_POST['position'] ?? '') ?>">
    </div>

    <div class="ctf-group">
        <label>📞 تلفن</label>
        <input type="tel" name="phone"
               placeholder="مثال: ۰۹۱۲۳۴۵۶۷۸۹"
               value="<?= crm_sanitize($contact['phone'] ?? $_POST['phone'] ?? '') ?>">
    </div>

    <div class="ctf-group">
        <label>📧 ایمیل</label>
        <input type="email" name="email"
               placeholder="example@company.com"
               value="<?= crm_sanitize($contact['email'] ?? $_POST['email'] ?? '') ?>">
    </div>

    <div class="ctf-group">
        <div class="ctf-primary-box">
            <label class="ctf-primary-label">
                <input type="checkbox" name="is_primary" value="1"
                       <?= ($contact['is_primary'] ?? 0) ? 'checked' : '' ?>>
                ⭐ مخاطب اصلی (Primary)
            </label>
            <small class="ctf-hint">اگر انتخاب شود، مخاطب اصلی فعلی جایگزین می‌شود.</small>
        </div>
    </div>

    <div class="ctf-actions">
        <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
        <button type="submit" class="ctf-btn-submit">
            <?= $is_edit ? '💾 بروزرسانی' : '✅ ثبت مخاطب' ?>
        </button>
        <a href="index.php?page=customers&action=view&id=<?= $customer['id'] ?>" class="ctf-btn-cancel">انصراف</a>
    </div>
</form>

</div><!-- /ctf-wrap -->