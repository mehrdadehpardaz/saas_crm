<!-- Views/tasks/assign.php -->

<style>
.ta-wrap {
    --ta-ink:#14213D; --ta-ink-soft:#4A5578; --ta-ember:#FF6B35; --ta-ember-deep:#E6531E;
    --ta-paper:#FAF8F5; --ta-paper2:#F2EEE6; --ta-line:#E5DFD3; --ta-card:#FFFFFF;
    direction: rtl; 
    max-width: 720px;
    margin: 0 auto;
    width: 100%;
}

.ta-header { margin-bottom:18px; }
.ta-back-link {
    display:inline-flex; align-items:center; gap:5px; font-size:12px; color:var(--ta-ink-soft);
    text-decoration:none; margin-bottom:6px;
}
.ta-back-link:hover { color:var(--ta-ember-deep); }
.ta-header h2 { font-size:18px; font-weight:800; color:var(--ta-ink); letter-spacing:-.01em; }

.ta-alert-error {
    background:#FCE8E6; color:#C0392B; border:1px solid #F5C6CB; border-radius:10px;
    padding:11px 16px; font-size:13px; margin-bottom:16px;
}

/* اطلاعات فرصت */
.ta-info-card {
    background:var(--ta-card); border:1px solid var(--ta-line); border-radius:14px;
    overflow:hidden; margin-bottom:16px;
}
.ta-info-row {
    display:flex; align-items:center; gap:10px; padding:12px 16px; border-bottom:1px solid var(--ta-paper2);
    font-size:13px;
}
.ta-info-row:last-child { border-bottom:none; }
.ta-info-icon { font-size:14px; width:24px; flex-shrink:0; text-align:center; }
.ta-info-label { color:var(--ta-ink-soft); min-width:80px; }
.ta-info-val { color:var(--ta-ink); font-weight:600; }

/* فرم */
.ta-form {
    background:var(--ta-card); border:1px solid var(--ta-line); border-radius:16px;
    padding:24px 22px; box-shadow:0 4px 20px rgba(20,33,61,.05);
}
.ta-form h3 { font-size:15px; font-weight:700; color:var(--ta-ink); margin-bottom:15px; }

.ta-group { margin-bottom:16px; }
.ta-group label { display:block; font-size:12.5px; font-weight:700; color:var(--ta-ink-soft); margin-bottom:7px; }
.ta-group select {
    width:100%; padding:11px 14px; border:1.5px solid var(--ta-line); border-radius:10px;
    font-size:13.5px; font-family:inherit; background:var(--ta-paper); color:var(--ta-ink);
    transition:border-color .15s, background .15s;
}
.ta-group select:focus { outline:none; border-color:var(--ta-ember); background:#fff; }

.ta-warning {
    display:flex; gap:9px; background:#FFF3DD; border:1px solid #F5D78E; border-radius:11px;
    padding:12px 14px; margin-bottom:18px; font-size:12.5px; color:var(--ta-ink); line-height:1.65;
}
.ta-warning-icon { flex-shrink:0; font-size:15px; }

.ta-actions { display:flex; gap:10px; }
.ta-btn-submit {
    flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
    padding:12px 20px; border-radius:11px; font-size:14px; font-weight:700; border:none;
    cursor:pointer; background:var(--ta-ember); color:#fff; box-shadow:0 4px 14px rgba(255,107,53,.3);
    transition:all .15s;
}
.ta-btn-submit:hover { background:var(--ta-ember-deep); transform:translateY(-1px); }
.ta-btn-cancel {
    padding:12px 22px; border-radius:11px; font-size:13.5px; font-weight:700; text-decoration:none;
    background:transparent; color:var(--ta-ink-soft); border:1.5px solid var(--ta-line); transition:all .15s;
}
.ta-btn-cancel:hover { border-color:var(--ta-ink); color:var(--ta-ink); }

/* empty state */
.ta-empty-card {
    background:var(--ta-card); border:1px solid var(--ta-line); border-radius:16px;
    padding:30px 22px; text-align:center;
}
.ta-empty-card .icon { font-size:32px; margin-bottom:10px; }
.ta-empty-card p { color:var(--ta-ink-soft); font-size:13.5px; margin-bottom:16px; }

@media(max-width:480px){
    .ta-form { padding:20px 16px; }
    .ta-actions { flex-direction:column-reverse; }
}
</style>

<div class="ta-wrap">

<div class="ta-header">
    <a href="index.php?page=tasks&action=view&id=<?= $task['id'] ?>" class="ta-back-link">
        ← <?= crm_sanitize($task['title']) ?>
    </a>
    <h2>🔄 انتقال فرصت</h2>
</div>

<?php if ($error): ?>
    <div class="ta-alert-error"><?= $error ?></div>
<?php endif; ?>

<!-- اطلاعات فرصت -->
<div class="ta-info-card">
    <div class="ta-info-row">
        <span class="ta-info-icon">📋</span>
        <span class="ta-info-label">فرصت</span>
        <span class="ta-info-val"><?= crm_sanitize($task['title']) ?></span>
    </div>
    <div class="ta-info-row">
        <span class="ta-info-icon">🏢</span>
        <span class="ta-info-label">مشتری</span>
        <span class="ta-info-val"><?= crm_sanitize($task['company_name']) ?></span>
    </div>
    <div class="ta-info-row">
        <span class="ta-info-icon">👤</span>
        <span class="ta-info-label">مالک فعلی</span>
        <span class="ta-info-val"><?= crm_sanitize($task['agent_name']) ?></span>
    </div>
</div>

<?php if (empty($assignable_users)): ?>
    <div class="ta-empty-card">
        <div class="icon">🤷‍♂️</div>
        <p>کاربر دیگری برای انتقال یافت نشد.</p>
        <a href="index.php?page=tasks&action=view&id=<?= $task['id'] ?>" class="ta-btn-cancel">بازگشت</a>
    </div>
<?php else: ?>

<form method="POST" action="index.php?page=tasks&action=assign&id=<?= $task['id'] ?>" class="ta-form">
    <h3>👤 انتخاب مالک جدید</h3>

    <div class="ta-group">
        <label>انتقال به:</label>
        <select name="new_user_id" required>
            <option value="">-- انتخاب کاربر --</option>
            <?php foreach ($assignable_users as $u): ?>
            <option value="<?= $u['id'] ?>">
                <?= crm_sanitize($u['full_name']) ?>
                (<?= $u['role'] === 'manager' ? '👔 مدیر' : '📞 کارشناس' ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="ta-warning">
        <span class="ta-warning-icon">⚠️</span>
        <span>با انتقال این فرصت، تمام فعالیت‌های قبلی هم به کاربر جدید منتقل می‌شوند.</span>
    </div>

    <div class="ta-actions">
        <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
        <button type="submit" class="ta-btn-submit">🔄 انتقال</button>
        <a href="index.php?page=tasks&action=view&id=<?= $task['id'] ?>" class="ta-btn-cancel">انصراف</a>
    </div>
</form>

<?php endif; ?>

</div><!-- /ta-wrap -->