<!-- Views/activities/form.php -->
<?php
$selected_customer_id = $activity['customer_id'] ?? $customer_id ?? null;

// مقدار پیش‌فرض تاریخ/ساعت فعالیت:
// - در حالت ویرایش: همون تاریخ/ساعتی که فعالیت قبلاً باهاش ثبت شده
// - در حالت ایجاد: همین لحظه (تا وقتی کاربر دستی عوضش نکرده)
$activity_date_default = !empty($activity['created_at'])
    ? date('Y-m-d', strtotime($activity['created_at']))
    : date('Y-m-d');
$activity_time_default = !empty($activity['created_at'])
    ? date('H:i', strtotime($activity['created_at']))
    : date('H:i');

// مسیر «بازگشت» / «انصراف»: فعالیت‌ها همیشه زیرمجموعه‌ی یک فرصت فروش‌اند،
// پس همیشه به صفحه‌ی همون فرصت فروش برمی‌گردیم؛ اگر هیچ فرصت فروشی در دسترس نبود
// (فعالیت مستقیماً به یک مشتری وصله، بدون فرصت فروش)، به لیست فرصت‌های فروش می‌رویم.
$back_task_id = $task['id'] ?? ($activity['task_id'] ?? null);
$back_url = $back_task_id
    ? 'index.php?page=tasks&action=view&id=' . $back_task_id
    : 'index.php?page=tasks&action=list_all';
?>

<style>
.af-wrap {
    --af-ink:#14213D; --af-ink-soft:#4A5578; --af-ember:#FF6B35; --af-ember-deep:#E6531E;
    --af-teal:#16A085; --af-paper:#FAF8F5; --af-paper2:#F2EEE6; --af-line:#E5DFD3; --af-card:#FFFFFF;
    direction: rtl;
    max-width: 720px;
    margin: 0 auto;
    width: 100%;
}

.af-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.af-header h2 { font-size:18px; font-weight:800; color:var(--af-ink); letter-spacing:-.01em; }
.af-btn-back {
    display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:9px;
    font-size:12.5px; font-weight:700; text-decoration:none; background:var(--af-card);
    color:var(--af-ink-soft); border:1.5px solid var(--af-line); transition:all .15s;
}
.af-btn-back:hover { border-color:var(--af-ink); color:var(--af-ink); }

.af-alert-error {
    background:#FCE8E6; color:#C0392B; border:1px solid #F5C6CB; border-radius:10px;
    padding:11px 16px; font-size:13px; margin-bottom:16px;
}

.af-form {
    background:var(--af-card); border:1px solid var(--af-line); border-radius:16px;
    padding:26px 24px; box-shadow:0 4px 20px rgba(20,33,61,.05);
}

.af-group { margin-bottom:18px; }
.af-group:last-of-type { margin-bottom:0; }
.af-group label {
    display:flex; align-items:center; gap:6px; font-size:12.5px; font-weight:700;
    color:var(--af-ink-soft); margin-bottom:7px;
}
.af-group input[type=text],
.af-group input[type=date],
.af-group input[type=time],
.af-group select,
.af-group textarea {
    width:100%; padding:11px 14px; border:1.5px solid var(--af-line); border-radius:10px;
    font-size:13.5px; font-family:inherit; background:var(--af-paper); color:var(--af-ink);
    transition:border-color .15s, background .15s;
}
.af-group input:focus, .af-group select:focus, .af-group textarea:focus {
    outline:none; border-color:var(--af-ember); background:#fff;
}
.af-group input:disabled { background:var(--af-paper2); color:var(--af-ink-soft); cursor:not-allowed; }
.af-group textarea { resize:vertical; min-height:80px; }
.af-hint { display:block; margin-top:6px; font-size:11px; color:#8A8478; }
.af-row { display:flex; gap:8px; }
.af-row > * { flex:1; }
.af-row > :first-child { flex:2; }

/* task summary box — فقط جهت اطلاع، غیرقابل‌ویرایش */
.af-task-box {
    background:var(--af-paper2); border:1px solid var(--af-line); border-radius:12px;
    padding:16px; margin-bottom:18px;
}
.af-task-box h4 {
    font-size:12.5px; font-weight:700; color:var(--af-ink); margin-bottom:11px;
    display:flex; align-items:center; gap:6px;
}
.af-task-box-readonly { display:flex; flex-direction:column; gap:6px; }
.af-ro-row { display:flex; gap:8px; font-size:12.5px; }
.af-ro-label { color:var(--af-ink-soft); flex-shrink:0; min-width:52px; }
.af-ro-val { color:var(--af-ink); font-weight:600; }
.af-ro-empty { font-size:12px; color:var(--af-ink-soft); }

/* ── Actions ── */
.af-actions { display:flex; gap:10px; margin-top:22px; padding-top:18px; border-top:1px solid var(--af-paper2); }
.af-btn-submit {
    flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
    padding:12px 20px; border-radius:11px; font-size:14px; font-weight:700; border:none;
    cursor:pointer; background:var(--af-ember); color:#fff; box-shadow:0 4px 14px rgba(255,107,53,.3);
    transition:all .15s;
}
.af-btn-submit:hover { background:var(--af-ember-deep); transform:translateY(-1px); }
.af-btn-cancel {
    padding:12px 22px; border-radius:11px; font-size:13.5px; font-weight:700; text-decoration:none;
    background:transparent; color:var(--af-ink-soft); border:1.5px solid var(--af-line); transition:all .15s;
}
.af-btn-cancel:hover { border-color:var(--af-ink); color:var(--af-ink); }

@media(max-width:480px){
    .af-form { padding:20px 16px; }
    .af-actions { flex-direction:column-reverse; }
    .af-row { flex-direction:column; }
    .af-row > :first-child { flex:1; }
}
</style>

<div class="af-wrap">

<div class="af-header">
    <h2><?= $is_edit ? '✏️ ویرایش فعالیت' : '➕ فعالیت جدید' ?></h2>
    <a href="<?= $back_url ?>" class="af-btn-back">🔙 بازگشت</a>
</div>

<?php if ($error): ?>
    <div class="af-alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" action="index.php?page=activities&action=<?= $is_edit ? 'update&id='.$id : 'create' ?><?= $back_task_id ? '&task_id='.(int)$back_task_id : '' ?>" class="af-form">

    <?php if ($task): ?>
    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
    <input type="hidden" name="customer_id" value="<?= $task['customer_id'] ?>">

    <div class="af-group">
        <label>📋 فرصت فروش</label>
        <input type="text" value="<?= crm_sanitize($task['title']) ?>" disabled>
    </div>

    <div class="af-group">
        <label>🏢 مشتری</label>
        <input type="text" value="<?= crm_sanitize($task['company_name']) ?>" disabled>
    </div>

    <!-- فقط جهت اطلاع — پیگیری فعلی این فرصت فروش. برای تغییرش از فیلدهای
         «تاریخ/موضوع پیگیری بعدی» پایین همین فرم استفاده کن. -->
    <div class="af-task-box">
        <h4>⏰ پیگیری فعلی این فرصت فروش</h4>
        <input type="hidden" name="update_task_followup" value="1">
        <input type="hidden" name="task_title" value="<?= crm_sanitize($task['title']) ?>">

        <div class="af-task-box-readonly">
            <?php if (!empty($task['next_followup_date'])): ?>
                <div class="af-ro-row">
                    <span class="af-ro-label">تاریخ:</span>
                    <span class="af-ro-val"><?= function_exists('jdatetime') ? jdatetime($task['next_followup_date']) : date('Y/m/d H:i', strtotime($task['next_followup_date'])) ?></span>
                </div>
                <?php if (!empty($task['next_followup_topic'])): ?>
                <div class="af-ro-row">
                    <span class="af-ro-label">موضوع:</span>
                    <span class="af-ro-val"><?= crm_sanitize($task['next_followup_topic']) ?></span>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="af-ro-empty">پیگیری بعدی‌ای برای این فرصت فروش تعیین نشده.</div>
            <?php endif; ?>
        </div>
        <small class="af-hint">برای تغییرش، از فیلدهای «پیگیری بعدی» پایین فرم استفاده کن.</small>
    </div>
    <?php else: ?>
    <div class="af-group">
        <label>🏢 مشتری *</label>
        <select name="customer_id" id="customer_select" required onchange="loadContacts(this.value)">
            <option value="">-- انتخاب مشتری --</option>
            <?php foreach ($customers_list as $cust): ?>
                <option value="<?= $cust['id'] ?>" <?= ($activity['customer_id'] ?? $customer_id ?? '') == $cust['id'] ? 'selected' : '' ?>>
                    <?= crm_sanitize($cust['company_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <div class="af-group">
        <label>👤 مخاطب</label>
        <select name="contact_id" id="contact_select">
            <option value="">-- بدون مخاطب --</option>
            <?php foreach ($contacts_list as $cont): ?>
                <option value="<?= $cont['id'] ?>" <?= ($activity['contact_id'] ?? '') == $cont['id'] ? 'selected' : '' ?>>
                    <?= crm_sanitize($cont['full_name']) ?>
                    <?= $cont['position'] ? ' - ' . crm_sanitize($cont['position']) : '' ?>
                    <?= $cont['is_primary'] ? ' (اصلی)' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="af-group">
        <label>📋 نوع فعالیت</label>
        <select name="type" required>
            <option value="call" <?= ($activity['type'] ?? 'call') === 'call' ? 'selected' : '' ?>>📞 تماس</option>
            <option value="meeting" <?= ($activity['type'] ?? '') === 'meeting' ? 'selected' : '' ?>>🤝 جلسه</option>
            <option value="email" <?= ($activity['type'] ?? '') === 'email' ? 'selected' : '' ?>>📧 ایمیل</option>
            <option value="note" <?= ($activity['type'] ?? '') === 'note' ? 'selected' : '' ?>>📝 یادداشت</option>
        </select>
    </div>

    <div class="af-group">
        <label>🕐 تاریخ و ساعت فعالیت</label>
        <div class="af-row">
            <input type="date" name="activity_date" value="<?= $activity_date_default ?>">
            <input type="time" name="activity_time" value="<?= $activity_time_default ?>">
        </div>
        <small class="af-hint">پیش‌فرض همین لحظه‌ست — اگه فعالیت مربوط به قبل‌تره، تاریخ و ساعتشو عوض کن.</small>
    </div>

    <div class="af-group">
        <label>📝 شرح فعالیت</label>
        <textarea name="description" rows="3" placeholder="چه اتفاقی افتاد؟ چی گفته شد؟"><?= crm_sanitize($activity['description'] ?? $_POST['description'] ?? '') ?></textarea>
    </div>

    <div class="af-group">
        <label>⏰ تاریخ پیگیری بعدی</label>
        <div class="af-row">
            <input type="date" name="next_followup_date"
                value="<?= !empty($activity['next_followup_date']) ? date('Y-m-d', strtotime($activity['next_followup_date'])) : '' ?>">
            <input type="time" name="next_followup_time"
                value="<?= !empty($activity['next_followup_date']) ? date('H:i', strtotime($activity['next_followup_date'])) : '09:00' ?>">
        </div>
    </div>

    <div class="af-group">
        <label>📌 موضوع پیگیری بعدی</label>
        <input type="text" name="next_followup_topic"
               placeholder="در مورد چی صحبت بشه؟"
               value="<?= crm_sanitize($activity['next_followup_topic'] ?? '') ?>">
    </div>

    <div class="af-actions">
        <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
        <button type="submit" class="af-btn-submit"><?= $is_edit ? '💾 بروزرسانی' : '✅ ثبت فعالیت' ?></button>
        <a href="<?= $back_url ?>" class="af-btn-cancel">انصراف</a>
    </div>
</form>

</div><!-- /af-wrap -->

<script>
function loadContacts(customerId) {
    if (!customerId) {
        document.getElementById('contact_select').innerHTML = '<option value="">-- بدون مخاطب --</option>';
        return;
    }

    fetch('api/contacts.php?customer_id=' + customerId)
        .then(res => res.json())
        .then(contacts => {
            let html = '<option value="">-- بدون مخاطب --</option>';
            contacts.forEach(c => {
                html += `<option value="${c.id}">${c.full_name} ${c.position ? '- ' + c.position : ''} ${c.is_primary == 1 ? '(اصلی)' : ''}</option>`;
            });
            document.getElementById('contact_select').innerHTML = html;
        });
}
</script>