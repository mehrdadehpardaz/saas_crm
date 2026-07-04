<!-- Views/tasks/form.php -->
<?php
$is_edit = ($action === 'edit');
$selected_customer_id = $task['customer_id'] ?? $customer_id ?? null;
?>

<style>
.tf-wrap {
    --tf-ink:#14213D; --tf-ink-soft:#4A5578; --tf-ember:#FF6B35; --tf-ember-deep:#E6531E;
    --tf-teal:#16A085; --tf-paper:#FAF8F5; --tf-paper2:#F2EEE6; --tf-line:#E5DFD3; --tf-card:#FFFFFF;
    direction: rtl; 
    max-width: 720px;
    margin: 0 auto;
    width: 100%;
}

.tf-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.tf-header h2 { font-size:18px; font-weight:800; color:var(--tf-ink); letter-spacing:-.01em; }
.tf-btn-back {
    display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:9px;
    font-size:12.5px; font-weight:700; text-decoration:none; background:var(--tf-card);
    color:var(--tf-ink-soft); border:1.5px solid var(--tf-line); transition:all .15s; cursor:pointer;
}
.tf-btn-back:hover { border-color:var(--tf-ink); color:var(--tf-ink); }

.tf-alert-error {
    background:#FCE8E6; color:#C0392B; border:1px solid #F5C6CB; border-radius:10px;
    padding:11px 16px; font-size:13px; margin-bottom:16px;
}

.tf-form {
    background:var(--tf-card); border:1px solid var(--tf-line); border-radius:16px;
    padding:26px 24px; box-shadow:0 4px 20px rgba(20,33,61,.05);
}

.tf-group { margin-bottom:18px; }
.tf-group:last-of-type { margin-bottom:0; }
.tf-group label {
    display:flex; align-items:center; gap:6px; font-size:12.5px; font-weight:700;
    color:var(--tf-ink-soft); margin-bottom:7px;
}
.tf-group input[type=text],
.tf-group input[type=date],
.tf-group input[type=time],
.tf-group select {
    width:100%; padding:11px 14px; border:1.5px solid var(--tf-line); border-radius:10px;
    font-size:13.5px; font-family:inherit; background:var(--tf-paper); color:var(--tf-ink);
    transition:border-color .15s, background .15s;
}
.tf-group input:focus, .tf-group select:focus { outline:none; border-color:var(--tf-ember); background:#fff; }
.tf-group input:disabled { background:var(--tf-paper2); color:var(--tf-ink-soft); cursor:not-allowed; }

.tf-row { display:flex; gap:8px; }
.tf-row > * { flex:1; }
.tf-row > :first-child { flex:2; }

/* ── Actions ── */
.tf-actions { display:flex; gap:10px; margin-top:22px; padding-top:18px; border-top:1px solid var(--tf-paper2); }
.tf-btn-submit {
    flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
    padding:12px 20px; border-radius:11px; font-size:14px; font-weight:700; border:none;
    cursor:pointer; background:var(--tf-ember); color:#fff; box-shadow:0 4px 14px rgba(255,107,53,.3);
    transition:all .15s;
}
.tf-btn-submit:hover { background:var(--tf-ember-deep); transform:translateY(-1px); }
.tf-btn-cancel {
    padding:12px 22px; border-radius:11px; font-size:13.5px; font-weight:700;
    background:transparent; color:var(--tf-ink-soft); border:1.5px solid var(--tf-line);
    transition:all .15s; cursor:pointer; text-decoration:none;
}
.tf-btn-cancel:hover { border-color:var(--tf-ink); color:var(--tf-ink); }

@media(max-width:480px){
    .tf-form { padding:20px 16px; }
    .tf-actions { flex-direction:column-reverse; }
    .tf-row { flex-direction:column; }
    .tf-row > :first-child { flex:1; }
}
</style>

<div class="tf-wrap">

<div class="tf-header">
    <h2><?= $is_edit ? '✏️ ویرایش تسک' : '➕ تسک جدید' ?></h2>
    <button type="button" onclick="history.back()" class="tf-btn-back">🔙 بازگشت</button>
</div>

<?php if ($error): ?>
    <div class="tf-alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" action="index.php?page=tasks&action=<?= $is_edit ? 'update&id='.$id : 'create' ?>" class="tf-form">

    <div class="tf-group">
        <label>🏢 مشتری *</label>
        <?php if ($is_edit): ?>
            <input type="text" value="<?= crm_sanitize($task['company_name'] ?? '') ?>" disabled>
        <?php elseif ($customer_id && isset($selected_customer)): ?>
            <input type="text" value="<?= crm_sanitize($selected_customer['company_name'] ?? '') ?>" disabled>
            <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
        <?php else: ?>
            <select name="customer_id" required>
                <option value="">-- انتخاب مشتری --</option>
                <?php foreach ($customers_list as $cust): ?>
                <option value="<?= $cust['id'] ?>" <?= $selected_customer_id == $cust['id'] ? 'selected' : '' ?>>
                    <?= crm_sanitize($cust['company_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </div>

    <div class="tf-group">
        <label>📋 عنوان تسک *</label>
        <input type="text" name="title" required
               placeholder="مثال: خرید دیزل ژنراتور"
               value="<?= crm_sanitize($task['title'] ?? $_POST['title'] ?? '') ?>">
    </div>

    <?php if ($is_edit): ?>
    <div class="tf-group">
        <label>وضعیت</label>
        <select name="status">
            <option value="active"    <?= ($task['status'] ?? 'active') === 'active'    ? 'selected' : '' ?>>🟢 فعال</option>
            <option value="completed" <?= ($task['status'] ?? '') === 'completed' ? 'selected' : '' ?>>✅ تکمیل شده</option>
            <option value="sold"      <?= ($task['status'] ?? '') === 'sold'      ? 'selected' : '' ?>>💰 منجر به فروش</option>
            <option value="cancelled" <?= ($task['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>❌ لغو شده</option>
        </select>
    </div>
    <?php endif; ?>

    <div class="tf-group">
        <label>⏰ تاریخ پیگیری بعدی</label>
        <div class="tf-row">
            <input type="date" name="next_followup_date"
                   value="<?= !empty($task['next_followup_date']) ? date('Y-m-d', strtotime($task['next_followup_date'])) : '' ?>">
            <input type="time" name="next_followup_time"
                   value="<?= !empty($task['next_followup_date']) ? date('H:i', strtotime($task['next_followup_date'])) : '09:00' ?>">
        </div>
    </div>

    <div class="tf-group">
        <label>📌 موضوع پیگیری بعدی</label>
        <input type="text" name="next_followup_topic"
               placeholder="در تماس بعدی چه موضوعی دنبال شود؟"
               value="<?= crm_sanitize($task['next_followup_topic'] ?? $_POST['next_followup_topic'] ?? '') ?>">
    </div>

    <div class="tf-actions">
        <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
        <button type="submit" class="tf-btn-submit"><?= $is_edit ? '💾 بروزرسانی' : '✅ ایجاد تسک' ?></button>
        <button type="button" onclick="history.back()" class="tf-btn-cancel">انصراف</button>
    </div>
</form>

</div><!-- /tf-wrap -->