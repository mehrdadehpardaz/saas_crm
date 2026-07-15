<!-- Views/support/form.php -->
<style>
.sf-wrap {
    direction: rtl;
    max-width: 560px;
    margin: 0 auto;
    width: 100%;
}

.sf-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.sf-header h2 { font-size:18px; font-weight:800; color:var(--ink); letter-spacing:-.01em; }
.sf-btn-back {
    display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:9px;
    font-size:12.5px; font-weight:700; text-decoration:none; background:var(--card);
    color:var(--ink-soft); border:1.5px solid var(--line); transition:all .15s;
}
.sf-btn-back:hover { border-color:var(--ink); color:var(--ink); }

.sf-alert-error {
    background:#FCE8E6; color:var(--danger-deep); border:1px solid #F5C6CB; border-radius:10px;
    padding:11px 16px; font-size:13px; margin-bottom:16px;
}

.sf-form {
    background:var(--card); border:1px solid var(--line); border-radius:16px;
    padding:26px 24px; box-shadow:var(--shadow);
}

.sf-group { margin-bottom:18px; }
.sf-group:last-of-type { margin-bottom:0; }
.sf-group label { display:block; font-size:12.5px; font-weight:700; color:var(--ink-soft); margin-bottom:7px; }
.sf-group input[type=text],
.sf-group textarea {
    width:100%; padding:11px 14px; border:1.5px solid var(--line); border-radius:var(--radius);
    font-size:13.5px; font-family:inherit; background:var(--paper); color:var(--ink);
    transition:border-color .15s, background .15s;
}
.sf-group input:focus, .sf-group textarea:focus { outline:none; border-color:var(--ember); background:#fff; }
.sf-group textarea { resize:vertical; min-height:140px; }

.sf-actions { display:flex; gap:10px; margin-top:22px; padding-top:18px; border-top:1px solid var(--paper-2); }
.sf-btn-submit {
    flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
    padding:12px 20px; border-radius:11px; font-size:14px; font-weight:700; border:none;
    cursor:pointer; background:var(--ember); color:#fff; box-shadow:0 4px 14px rgba(255,107,53,.3);
    transition:all .15s;
}
.sf-btn-submit:hover { background:var(--ember-deep); transform:translateY(-1px); }
.sf-btn-cancel {
    padding:12px 22px; border-radius:11px; font-size:13.5px; font-weight:700; text-decoration:none;
    background:transparent; color:var(--ink-soft); border:1.5px solid var(--line); transition:all .15s;
}
.sf-btn-cancel:hover { border-color:var(--ink); color:var(--ink); }

@media(max-width:480px){
    .sf-form { padding:20px 16px; }
    .sf-actions { flex-direction:column-reverse; }
}
</style>

<div class="sf-wrap">

<div class="sf-header">
    <h2>🎫 تیکت جدید</h2>
    <a href="index.php?page=support" class="sf-btn-back">بازگشت</a>
</div>

<?php if ($error): ?>
    <div class="sf-alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" action="index.php?page=support&action=create" class="sf-form">
    <div class="sf-group">
        <label for="subject">موضوع *</label>
        <input type="text" id="subject" name="subject" required
               placeholder="مثال: مشکل در ثبت مشتری جدید"
               value="<?= crm_sanitize($_POST['subject'] ?? '') ?>">
    </div>

    <div class="sf-group">
        <label for="message">شرح مشکل یا درخواست *</label>
        <textarea id="message" name="message" required
                  placeholder="هرچی لازمه بدونیم رو کامل بنویس — هرچی دقیق‌تر باشه، سریع‌تر کمکت می‌کنیم."><?= crm_sanitize($_POST['message'] ?? '') ?></textarea>
    </div>

    <div class="sf-actions">
        <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
        <button type="submit" class="sf-btn-submit">✅ ثبت تیکت</button>
        <a href="index.php?page=support" class="sf-btn-cancel">انصراف</a>
    </div>
</form>

</div><!-- /sf-wrap -->