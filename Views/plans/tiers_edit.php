<!-- Views/plans/tiers_edit.php -->
<style>
.te-wrap { direction: rtl; max-width: 900px; margin: 0 auto; width: 100%; }
.te-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.te-header h2 { font-size:18px; font-weight:800; color:var(--ink); }
.te-btn-back {
    display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:9px;
    font-size:12.5px; font-weight:700; text-decoration:none; background:var(--card);
    color:var(--ink-soft); border:1.5px solid var(--line); transition:all .15s;
}
.te-btn-back:hover { border-color:var(--ink); color:var(--ink); }
.te-alert { padding:11px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; background:#E7F7F3; color:var(--teal-deep); border:1px solid #B8E5DA; }

.te-card { background:var(--card); border:1px solid var(--line); border-radius:14px; padding:20px; margin-bottom:16px; }
.te-card h3 { font-size:14.5px; font-weight:800; color:var(--ink); margin-bottom:14px; }

.te-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px 14px; }
@media(min-width:600px){ .te-grid { grid-template-columns:1fr 1fr 1fr; } }
.te-field label { display:block; font-size:11.5px; font-weight:700; color:var(--ink-soft); margin-bottom:6px; }
.te-field input[type=text],
.te-field input[type=number],
.te-field select {
    width:100%; padding:9px 12px; border:1.5px solid var(--line); border-radius:9px;
    font-size:13px; font-family:inherit; background:var(--paper); color:var(--ink);
}
.te-field input:focus, .te-field select:focus { outline:none; border-color:var(--ember); background:#fff; }
.te-field small { display:block; margin-top:5px; font-size:10.5px; color:#8A8478; }

.te-checks { display:flex; gap:18px; margin-top:14px; flex-wrap:wrap; }
.te-check { display:flex; align-items:center; gap:7px; font-size:12.5px; font-weight:600; color:var(--ink); cursor:pointer; }
.te-check input { width:16px; height:16px; accent-color:var(--ember); }

.te-submit {
    margin-top:16px; padding:10px 22px; border-radius:9px; font-size:13px; font-weight:700; border:none;
    cursor:pointer; background:var(--ember); color:#fff; box-shadow:0 3px 10px rgba(255,107,53,.28);
}
.te-submit:hover { background:var(--ember-deep); }
</style>

<div class="te-wrap">

<div class="te-header">
    <h2>⚙️ ویرایش پلن‌ها</h2>
    <a href="index.php?page=plans" class="te-btn-back">🔙 بازگشت</a>
</div>

<?php if (($message ?? '') === 'tier_updated'): ?>
    <div class="te-alert">✅ تغییرات ذخیره شد.</div>
<?php endif; ?>

<?php foreach ($tiers as $t): ?>
<form method="POST" action="index.php?page=plans&action=update_tier" class="te-card">
    <input type="hidden" name="id" value="<?= $t['id'] ?>">
    <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>

    <h3><?= crm_sanitize($t['name']) ?> <span style="color:var(--ink-soft);font-weight:400;font-size:11.5px">(<?= crm_sanitize($t['slug']) ?>)</span></h3>

    <div class="te-grid">
        <div class="te-field">
            <label>نام پلن</label>
            <input type="text" name="name" value="<?= crm_sanitize($t['name']) ?>" required>
        </div>
        <div class="te-field">
            <label>قیمت ماهانه (تومان)</label>
            <input type="number" name="price_monthly" min="0" step="1000" value="<?= (int)$t['price_monthly'] ?>" required>
        </div>
        <div class="te-field">
            <label>بکاپ‌گیری</label>
            <select name="backup_access">
                <option value="one_monthly" <?= $t['backup_access'] === 'one_monthly' ? 'selected' : '' ?>>یک بکاپ رایگان در ماه</option>
                <option value="unlimited" <?= $t['backup_access'] === 'unlimited' ? 'selected' : '' ?>>نامحدود</option>
            </select>
        </div>

        <div class="te-field">
            <label>سقف کاربران</label>
            <input type="number" name="max_users" min="0" value="<?= $t['max_users'] !== null ? (int)$t['max_users'] : '' ?>" placeholder="خالی = نامحدود">
            <small>خالی بگذارید برای نامحدود</small>
        </div>
        <div class="te-field">
            <label>سقف مشتریان</label>
            <input type="number" name="max_customers" min="0" value="<?= $t['max_customers'] !== null ? (int)$t['max_customers'] : '' ?>" placeholder="خالی = نامحدود">
            <small>خالی بگذارید برای نامحدود</small>
        </div>
        <div class="te-field">
            <label>سقف مخاطبین</label>
            <input type="number" name="max_contacts" min="0" value="<?= $t['max_contacts'] !== null ? (int)$t['max_contacts'] : '' ?>" placeholder="خالی = نامحدود">
            <small>خالی بگذارید برای نامحدود</small>
        </div>
    </div>

    <div class="te-checks">
        <label class="te-check">
            <input type="checkbox" name="management_reports" value="1" <?= $t['management_reports'] ? 'checked' : '' ?>>
            دسترسی به گزارش‌گیری مدیریتی
        </label>
        <label class="te-check">
            <input type="checkbox" name="full_access" value="1" <?= $t['full_access'] ? 'checked' : '' ?>>
            دسترسی کامل صد در صد (بدون هیچ محدودیتی)
        </label>
    </div>

    <button type="submit" class="te-submit">💾 ذخیره تغییرات این پلن</button>
</form>
<?php endforeach; ?>

</div><!-- /te-wrap -->