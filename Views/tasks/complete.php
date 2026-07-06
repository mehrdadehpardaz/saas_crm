<!-- Views/tasks/complete.php -->

<style>
.tc-wrap {
    --tc-ink:#14213D; --tc-ink-soft:#4A5578; --tc-ember:#FF6B35; --tc-ember-deep:#E6531E;
    --tc-teal:#16A085; --tc-teal-deep:#0E8170; --tc-warning:#D97706; --tc-danger:#EA4335;
    --tc-paper:#FAF8F5; --tc-paper2:#F2EEE6; --tc-line:#E5DFD3; --tc-card:#FFFFFF;
    direction: rtl; 
    max-width: 720px;
    margin: 0 auto;
    width: 100%;
}

.tc-header { margin-bottom:18px; }
.tc-back-link {
    display:inline-flex; align-items:center; gap:5px; font-size:12px; color:var(--tc-ink-soft);
    text-decoration:none; margin-bottom:6px;
}
.tc-back-link:hover { color:var(--tc-ember-deep); }
.tc-header h2 { font-size:18px; font-weight:800; color:var(--tc-ink); letter-spacing:-.01em; }

/* اطلاعات فرصت */
.tc-info-card {
    background:var(--tc-card); border:1px solid var(--tc-line); border-radius:14px;
    overflow:hidden; margin-bottom:16px;
}
.tc-info-row {
    display:flex; align-items:center; gap:10px; padding:12px 16px; border-bottom:1px solid var(--tc-paper2);
    font-size:13px;
}
.tc-info-row:last-child { border-bottom:none; }
.tc-info-icon { font-size:14px; width:24px; flex-shrink:0; text-align:center; }
.tc-info-label { color:var(--tc-ink-soft); min-width:80px; }
.tc-info-val { color:var(--tc-ink); font-weight:600; }

/* فرم */
.tc-form {
    background:var(--tc-card); border:1px solid var(--tc-line); border-radius:16px;
    padding:24px 22px; box-shadow:0 4px 20px rgba(20,33,61,.05);
}
.tc-form h3 { font-size:15px; font-weight:700; color:var(--tc-ink); margin-bottom:14px; }

.tc-group { margin-bottom:16px; }
.tc-group label { display:block; font-size:12.5px; font-weight:700; color:var(--tc-ink-soft); margin-bottom:8px; }
.tc-group textarea {
    width:100%; padding:11px 14px; border:1.5px solid var(--tc-line); border-radius:10px;
    font-size:13.5px; font-family:inherit; background:var(--tc-paper); color:var(--tc-ink);
    resize:vertical; min-height:90px; transition:border-color .15s, background .15s;
}
.tc-group textarea:focus { outline:none; border-color:var(--tc-ember); background:#fff; }

.tc-notice {
    background:#E7F7F3; border:1px solid #B8E5DA; padding:13px 15px; border-radius:11px;
    margin-bottom:18px; font-size:12.5px; color:var(--tc-teal-deep); line-height:1.65;
}
.tc-notice strong { color:var(--tc-teal-deep); }

/* وضعیت radio cards */
.tc-status-grid { display:flex; gap:9px; flex-wrap:wrap; }
.tc-status-option {
    display:flex; align-items:center; gap:7px; cursor:pointer; padding:12px 14px;
    border:2px solid var(--tc-line); border-radius:11px; flex:1; min-width:140px;
    transition:all .15s; background:var(--tc-paper);
}
.tc-status-option input[type=radio] { width:16px; height:16px; margin:0; flex-shrink:0; cursor:pointer; }
.tc-status-option span { font-size:13px; font-weight:600; color:var(--tc-ink); }

/* ── Actions ── */
.tc-actions { display:flex; gap:10px; margin-top:22px; padding-top:18px; border-top:1px solid var(--tc-paper2); }
.tc-btn-submit {
    flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
    padding:13px 20px; border-radius:11px; font-size:14px; font-weight:700; border:none;
    cursor:pointer; color:#fff; transition:all .18s; background:var(--tc-teal);
    box-shadow:0 4px 14px rgba(22,160,133,.3);
}
.tc-btn-submit:hover { transform:translateY(-1px); }
.tc-btn-cancel {
    padding:13px 22px; border-radius:11px; font-size:13.5px; font-weight:700; text-decoration:none;
    background:transparent; color:var(--tc-ink-soft); border:1.5px solid var(--tc-line); transition:all .15s;
}
.tc-btn-cancel:hover { border-color:var(--tc-ink); color:var(--tc-ink); }

@media(max-width:480px){
    .tc-form { padding:20px 16px; }
    .tc-actions { flex-direction:column-reverse; }
    .tc-status-option { min-width:100%; }
}
</style>

<div class="tc-wrap">

<div class="tc-header">
    <a href="index.php?page=tasks&action=view&id=<?= $task['id'] ?>" class="tc-back-link">
        ← <?= crm_sanitize($task['title']) ?>
    </a>
    <h2>✅ تکمیل فرصت</h2>
</div>

<!-- اطلاعات فرصت -->
<div class="tc-info-card">
    <div class="tc-info-row">
        <span class="tc-info-icon">📋</span>
        <span class="tc-info-label">فرصت</span>
        <span class="tc-info-val"><?= crm_sanitize($task['title']) ?></span>
    </div>
    <div class="tc-info-row">
        <span class="tc-info-icon">🏢</span>
        <span class="tc-info-label">مشتری</span>
        <span class="tc-info-val"><?= crm_sanitize($task['company_name']) ?></span>
    </div>
    <?php if ($task['next_followup_date']): ?>
    <div class="tc-info-row">
        <span class="tc-info-icon">⏰</span>
        <span class="tc-info-label">پیگیری فعلی</span>
        <span class="tc-info-val"><?= function_exists('jdatetime') ? jdatetime($task['next_followup_date']) : date('Y/m/d H:i', strtotime($task['next_followup_date'])) ?></span>
    </div>
    <?php endif; ?>
</div>

<form method="POST" action="index.php?page=tasks&action=complete&id=<?= $task['id'] ?>" class="tc-form">
    <h3>📝 شرح نهایی (پیگیری آخر)</h3>

    <div class="tc-group">
        <label>نتیجه نهایی / توضیحات تکمیل</label>
        <textarea name="final_description" rows="4"
                  placeholder="مثال: قرارداد بسته شد به مبلغ ۵۰ میلیون تومان. فاکتور صادر شد."></textarea>
    </div>

    <div class="tc-notice">
        ✅ با زدن این دکمه، فرصت <strong>تکمیل شده</strong> علامت می‌خورد و از لیست فرصت‌های فعال خارج می‌شود.
    </div>

    <div class="tc-group">
        <label>نتیجه نهایی</label>
        <div class="tc-status-grid">
            <label class="tc-status-option" id="lbl-completed">
                <input type="radio" name="complete_status" value="completed" checked onchange="updateLabel()">
                <span>✅ تکمیل شد</span>
            </label>
            <label class="tc-status-option" id="lbl-sold">
                <input type="radio" name="complete_status" value="sold" onchange="updateLabel()">
                <span>💰 منجر به فروش شد</span>
            </label>
            <label class="tc-status-option" id="lbl-cancelled">
                <input type="radio" name="complete_status" value="cancelled" onchange="updateLabel()">
                <span>❌ کنسل شد</span>
            </label>
        </div>
    </div>

    <div class="tc-actions">
        <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
        <button type="submit" class="tc-btn-submit" id="submit-btn">ثبت نتیجه</button>
        <a href="index.php?page=tasks&action=view&id=<?= $task['id'] ?>" class="tc-btn-cancel">انصراف</a>
    </div>
</form>

</div><!-- /tc-wrap -->

<script>
function updateLabel() {
    var val = document.querySelector('input[name="complete_status"]:checked').value;
    var btn = document.getElementById('submit-btn');
    var colors = { completed: '#16A085', sold: '#D97706', cancelled: '#EA4335' };
    var labels = { completed: 'ثبت نتیجه', sold: '💰 ثبت فروش', cancelled: '❌ ثبت کنسلی' };
    btn.style.background = colors[val];
    btn.textContent = labels[val];

    ['completed','sold','cancelled'].forEach(function(s) {
        var lbl = document.getElementById('lbl-' + s);
        lbl.style.borderColor = (val === s) ? colors[s] : '#E5DFD3';
        lbl.style.background = (val === s) ? '#FAF8F5' : '#FAF8F5';
    });
}
updateLabel();
</script>