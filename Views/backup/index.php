<!-- Views/backup/index.php -->
<style>
.bk-wrap {
    --bk-ink:#14213D; --bk-ink-soft:#4A5578; --bk-ember:#FF6B35; --bk-ember-deep:#E6531E;
    --bk-teal:#16A085; --bk-teal-deep:#0E8170; --bk-paper:#FAF8F5; --bk-paper2:#F2EEE6;
    --bk-line:#E5DFD3; --bk-card:#FFFFFF; --bk-blue:#1a73e8; --bk-danger:#EA4335; --bk-warning:#E6951E;
    direction: rtl; 
    max-width: 1000px;
    margin: 0 auto;
    width: 100%;
}

/* Header */
.bk-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; flex-wrap:wrap; gap:10px; }
.bk-header h2 { font-size:18px; font-weight:800; color:var(--bk-ink); letter-spacing:-.01em; }
.bk-badge { padding:4px 13px; border-radius:14px; font-size:11.5px; font-weight:700; }

/* Stats row */
.bk-stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(110px, 1fr)); gap:10px; margin-bottom:22px; }
.bk-stat { background:var(--bk-card); border:1px solid var(--bk-line); border-radius:13px; padding:15px 12px; text-align:center; transition:box-shadow .18s; }
.bk-stat:hover { box-shadow:0 4px 16px rgba(20,33,61,.06); }
.bk-stat-val { font-size:22px; font-weight:800; color:var(--bk-ember); }
.bk-stat-lbl { font-size:11px; color:var(--bk-ink-soft); margin-top:4px; }

/* Section cards */
.bk-card { background:var(--bk-card); border:1px solid var(--bk-line); border-radius:14px; overflow:hidden; margin-bottom:16px; }
.bk-card-hd { padding:15px 17px; border-bottom:1px solid var(--bk-line); display:flex; align-items:center; gap:11px; }
.bk-card-hd-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.bk-card-hd h3 { font-size:14.5px; font-weight:700; color:var(--bk-ink); margin:0; flex:1; }
.bk-card-hd p { font-size:11.5px; color:var(--bk-ink-soft); margin:2px 0 0 0; }
.bk-card-body { padding:17px; }

/* Buttons */
.bk-btn { display:inline-flex; align-items:center; gap:7px; padding:11px 22px; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; text-decoration:none; border:none; transition:all .18s; }
.bk-btn-primary { background:var(--bk-ink); color:#fff; box-shadow:0 3px 10px rgba(20,33,61,.25); }
.bk-btn-primary:hover { background:#1C2D52; transform:translateY(-1px); }
.bk-btn-success { background:var(--bk-teal); color:#fff; box-shadow:0 3px 10px rgba(22,160,133,.3); }
.bk-btn-success:hover { background:var(--bk-teal-deep); transform:translateY(-1px); }
.bk-btn-danger { background:var(--bk-danger); color:#fff; box-shadow:0 3px 10px rgba(234,67,53,.3); }
.bk-btn-danger:hover { background:#C0392B; transform:translateY(-1px); }
.bk-btn-outline { background:transparent; color:var(--bk-ink-soft); border:1.5px solid var(--bk-line); }
.bk-btn-outline:hover { border-color:var(--bk-ink); color:var(--bk-ink); }
.bk-btn:disabled { opacity:.45; cursor:not-allowed; transform:none !important; box-shadow:none !important; }

/* Upload zone */
.bk-upload-zone { border:2px dashed var(--bk-line); border-radius:13px; padding:32px 20px; text-align:center; cursor:pointer; transition:all .2s; position:relative; background:var(--bk-paper); }
.bk-upload-zone:hover, .bk-upload-zone.dragover { border-color:var(--bk-ember); background:#FFF8F4; }
.bk-upload-zone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; }
.bk-upload-icon { font-size:34px; margin-bottom:10px; }
.bk-upload-text { font-size:13.5px; color:var(--bk-ink); font-weight:600; }
.bk-upload-hint { font-size:11.5px; color:var(--bk-ink-soft); margin-top:5px; }
.bk-file-selected { display:none; margin-top:12px; padding:9px 14px; background:#E7F7F3; border-radius:8px; font-size:12.5px; color:var(--bk-teal-deep); font-weight:600; }

/* Warning box */
.bk-warning { display:flex; gap:11px; padding:13px 15px; border-radius:10px; margin-bottom:17px; }
.bk-warning-yellow { background:#FFF3DD; border:1px solid #F5D78E; }
.bk-warning-red    { background:#FCE8E6; border:1.5px solid var(--bk-danger); }
.bk-warning-icon { font-size:17px; flex-shrink:0; margin-top:1px; }
.bk-warning-text { font-size:12.5px; color:var(--bk-ink); line-height:1.65; }

/* Alert */
.bk-alert { padding:13px 17px; border-radius:10px; font-size:13px; margin-bottom:17px; display:flex; align-items:center; gap:9px; }
.bk-alert-ok  { background:#E7F7F3; color:var(--bk-teal-deep); border:1px solid #B8E5DA; }
.bk-alert-err { background:#FCE8E6; color:#C0392B; border:1px solid #F5C6CB; }

/* Confirm modal */
.bk-modal-overlay { position:fixed; inset:0; background:rgba(20,33,61,.55); backdrop-filter:blur(3px); z-index:9999; display:none; align-items:center; justify-content:center; padding:20px; }
.bk-modal-overlay.active { display:flex; animation:bkFadeIn .2s ease; }
@keyframes bkFadeIn { from{opacity:0} to{opacity:1} }
.bk-modal { background:var(--bk-card); border-radius:16px; padding:26px; max-width:380px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,.25); animation:bkModalUp .22s cubic-bezier(.2,.8,.2,1); }
@keyframes bkModalUp { from{opacity:0; transform:translateY(14px) scale(.98)} to{opacity:1; transform:none} }
.bk-modal h4 { font-size:16px; font-weight:800; margin:0 0 10px; color:var(--bk-danger); display:flex; align-items:center; gap:7px; }
.bk-modal p { font-size:13px; color:var(--bk-ink-soft); margin:0 0 20px; line-height:1.7; }
.bk-modal-btns { display:flex; gap:9px; justify-content:flex-end; }

/* Progress */
.bk-progress { display:none; margin-top:13px; }
.bk-progress-bar { height:6px; background:var(--bk-paper2); border-radius:4px; overflow:hidden; }
.bk-progress-fill { height:100%; background:var(--bk-ember); border-radius:4px; width:0; animation:bk-indeterminate 1.2s infinite linear; }
@keyframes bk-indeterminate { 0%{width:0;margin-right:100%} 50%{width:60%;margin-right:20%} 100%{width:0;margin-right:0} }
.bk-progress-text { font-size:11.5px; color:var(--bk-ink-soft); margin-top:7px; text-align:center; }

/* Info list */
.bk-info-list { list-style:none; padding:0; margin:0; }
.bk-info-list li { display:flex; align-items:center; gap:8px; font-size:12.5px; padding:7px 0; border-bottom:1px solid var(--bk-paper2); color:var(--bk-ink-soft); }
.bk-info-list li:last-child { border:none; }
.bk-info-list li span:first-child { color:var(--bk-ink); font-weight:600; min-width:130px; }
</style>

<div class="bk-wrap">

<!-- Header -->
<div class="bk-header">
    <div>
        <h2>💾 پشتیبان‌گیری و بازگردانی</h2>
        <div style="font-size:12px;color:var(--bk-ink-soft);margin-top:4px">
            <?= $is_super ? 'سوپر ادمین — دسترسی کامل' : 'مدیر — پشتیبان‌گیری شرکت ' . crm_sanitize($user['company_name']) ?>
        </div>
    </div>
    <span class="bk-badge" style="background:<?= $is_super ? '#FEF3C7' : '#E8F0FE' ?>;color:<?= $is_super ? '#D97706' : 'var(--bk-blue)' ?>">
        <?= $is_super ? '👑 Super Admin' : '🛡️ Admin' ?>
    </span>
</div>

<!-- Alert پس از restore -->
<?php if (!empty($restore_msg)): ?>
<div class="bk-alert <?= ($restore_ok ?? false) ? 'bk-alert-ok' : 'bk-alert-err' ?>">
    <span><?= ($restore_ok ?? false) ? '✅' : '❌' ?></span>
    <span><?= crm_sanitize($restore_msg) ?></span>
</div>
<?php endif; ?>

<!-- آمار داده‌ها -->
<div class="bk-stats">
    <div class="bk-stat">
        <div class="bk-stat-val"><?= number_format($db_stats['users']) ?></div>
        <div class="bk-stat-lbl">👤 کاربر</div>
    </div>
    <div class="bk-stat">
        <div class="bk-stat-val"><?= number_format($db_stats['customers']) ?></div>
        <div class="bk-stat-lbl">🏢 مشتری</div>
    </div>
    <div class="bk-stat">
        <div class="bk-stat-val"><?= number_format($db_stats['contacts']) ?></div>
        <div class="bk-stat-lbl">👥 مخاطب</div>
    </div>
    <div class="bk-stat">
        <div class="bk-stat-val"><?= number_format($db_stats['activities']) ?></div>
        <div class="bk-stat-lbl">📋 فعالیت</div>
    </div>
    <div class="bk-stat">
        <div class="bk-stat-val"><?= number_format($db_stats['tasks']) ?></div>
        <div class="bk-stat-lbl">✅ تسک</div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     بخش ۱: خروجی اکسل (همه admin‌ها)
════════════════════════════════════════════ -->
<div class="bk-card">
    <div class="bk-card-hd">
        <div class="bk-card-hd-icon" style="background:#E7F7F3">📊</div>
        <div>
            <h3>خروجی اکسل</h3>
            <p><?= $is_super ? 'کل دیتابیس — همه شرکت‌ها و کاربران' : 'داده‌های شرکت ' . crm_sanitize($user['company_name']) ?></p>
        </div>
    </div>
    <div class="bk-card-body">
        <div class="bk-warning bk-warning-yellow">
            <span class="bk-warning-icon">ℹ️</span>
            <div class="bk-warning-text">
                فایل اکسل شامل <strong>۶ شیت</strong> می‌شود: کاربران، مشتریان، مخاطبین، فعالیت‌ها، تسک‌ها و صنایع.
                این فایل برای گزارش‌گیری و آرشیو است — برای بازگردانی از خروجی SQL استفاده کنید.
            </div>
        </div>

        <ul class="bk-info-list" style="margin-bottom:17px">
            <li><span>فرمت خروجی</span><span>📄 Excel (.xlsx) — قابل باز شدن در اکسل و Google Sheets</span></li>
            <li><span>محدوده داده</span><span><?= $is_super ? 'همه شرکت‌ها' : crm_sanitize($user['company_name']) ?></span></li>
            <li><span>شامل</span><span>کاربران، مشتریان، مخاطبین، فعالیت‌ها، تسک‌ها، صنایع</span></li>
            <li><span>تاریخ</span><span><?= function_exists('jdatetime') ? jdatetime(date('Y-m-d H:i:s')) : date('Y/m/d H:i') ?></span></li>
        </ul>

        <a href="index.php?page=backup&action=export_excel"
           class="bk-btn bk-btn-success"
           id="btn-excel"
           onclick="startExcelDownload()">
            ⬇️ دانلود فایل اکسل
        </a>

        <div class="bk-progress" id="excel-progress">
            <div class="bk-progress-bar"><div class="bk-progress-fill"></div></div>
            <div class="bk-progress-text">در حال آماده‌سازی فایل...</div>
        </div>
    </div>
</div>

<?php if ($is_super): ?>
<!-- ══════════════════════════════════════════
     بخش ۲: خروجی SQL (فقط super_admin)
════════════════════════════════════════════ -->
<div class="bk-card">
    <div class="bk-card-hd">
        <div class="bk-card-hd-icon" style="background:#E8F0FE">🗄️</div>
        <div>
            <h3>خروجی کامل دیتابیس (SQL)</h3>
            <p>پشتیبان کامل از کل دیتابیس — قابل استفاده برای بازگردانی</p>
        </div>
    </div>
    <div class="bk-card-body">
        <div class="bk-warning bk-warning-yellow">
            <span class="bk-warning-icon">⚠️</span>
            <div class="bk-warning-text">
                این فایل شامل <strong>تمام جداول، داده‌ها و ساختار دیتابیس</strong> می‌شود.
                فایل SQL را در مکان امن نگه‌داری کنید.
            </div>
        </div>

        <ul class="bk-info-list" style="margin-bottom:17px">
            <li><span>فرمت خروجی</span><span>🗄️ MySQL SQL Dump (.sql)</span></li>
            <li><span>محتوا</span><span>ساختار جداول + تمام داده‌ها</span></li>
            <li><span>قابل استفاده در</span><span>phpMyAdmin، MySQL CLI، بخش Restore پایین</span></li>
            <li><span>تاریخ</span><span><?= function_exists('jdatetime') ? jdatetime(date('Y-m-d H:i:s')) : date('Y/m/d H:i') ?></span></li>
        </ul>

        <a href="index.php?page=backup&action=export_sql"
           class="bk-btn bk-btn-primary"
           id="btn-sql"
           onclick="startSqlDownload()">
            ⬇️ دانلود فایل SQL
        </a>

        <div class="bk-progress" id="sql-progress">
            <div class="bk-progress-bar"><div class="bk-progress-fill"></div></div>
            <div class="bk-progress-text">در حال ساخت dump دیتابیس...</div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     بخش ۳: بازگردانی (فقط super_admin)
════════════════════════════════════════════ -->
<div class="bk-card" style="border-color:#F5C6CB">
    <div class="bk-card-hd" style="background:#FFF9F8">
        <div class="bk-card-hd-icon" style="background:#FCE8E6">🔄</div>
        <div>
            <h3>بازگردانی دیتابیس</h3>
            <p>آپلود فایل SQL و اجرا روی دیتابیس فعلی</p>
        </div>
    </div>
    <div class="bk-card-body">
        <div class="bk-warning bk-warning-red">
            <span class="bk-warning-icon">🚨</span>
            <div class="bk-warning-text">
                <strong>هشدار جدی:</strong> بازگردانی داده‌های فعلی را بازنویسی می‌کند.
                قبل از ادامه، یک بکاپ SQL از وضعیت فعلی بگیرید.
                این عملیات قابل برگشت نیست.
            </div>
        </div>

        <!-- نکته‌ی فنی: فیلد CSRF باید داخل خودِ این <form> باشد، نه داخل
             مودال تأیید پایین صفحه. مودال از نظر ساختار HTML یک عنصر
             کاملاً جدا و بیرون از این فرم است؛ وقتی جاوااسکریپت با
             .submit() این فرم را ارسال می‌کند، فقط فیلدهایی که واقعاً
             داخل همین تگ <form> باشند به سرور فرستاده می‌شوند. قبلاً
             فیلد CSRF داخل مودال بود، پس اصلاً ارسال نمی‌شد و سرور با
             «درخواست نامعتبر است» (403) رد می‌کرد. -->
        <form method="POST" action="index.php?page=backup&action=restore"
              enctype="multipart/form-data" id="restore-form">

            <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>

            <!-- Upload zone -->
            <div class="bk-upload-zone" id="upload-zone">
                <input type="file" name="sql_file" id="sql-file-input" accept=".sql" onchange="onFileSelected(this)">
                <div class="bk-upload-icon">📁</div>
                <div class="bk-upload-text">فایل SQL را اینجا بکشید یا کلیک کنید</div>
                <div class="bk-upload-hint">فقط فایل .sql — حداکثر ۵۰ مگابایت</div>
                <div class="bk-file-selected" id="file-selected-info"></div>
            </div>

            <div style="display:flex; gap:10px; margin-top:15px; align-items:center; flex-wrap:wrap">
                <button type="button"
                        class="bk-btn bk-btn-danger"
                        id="btn-restore"
                        disabled
                        onclick="showRestoreConfirm()">
                    🔄 اجرای بازگردانی
                </button>
                <span style="font-size:11.5px;color:var(--bk-ink-soft)" id="restore-hint">ابتدا فایل SQL را انتخاب کنید</span>
            </div>

            <div class="bk-progress" id="restore-progress">
                <div class="bk-progress-bar"><div class="bk-progress-fill"></div></div>
                <div class="bk-progress-text">در حال اجرای دستورات SQL...</div>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Modal -->
<div class="bk-modal-overlay" id="restore-modal">
    <div class="bk-modal">
        <h4>🚨 تأیید بازگردانی</h4>
        <p>مطمئن هستید؟ تمام داده‌های فعلی با محتوای فایل SQL جایگزین می‌شوند.<br>این عملیات <strong>قابل برگشت نیست</strong>.</p>
        <div class="bk-modal-btns">
            <button class="bk-btn bk-btn-outline" onclick="hideRestoreConfirm()">انصراف</button>
            <button class="bk-btn bk-btn-danger" onclick="submitRestore()">بله، بازگردانی کن</button>
        </div>
    </div>
</div>

<?php endif; ?>

</div><!-- /bk-wrap -->

<script>
function startExcelDownload() {
    var btn = document.getElementById('btn-excel');
    var prg = document.getElementById('excel-progress');
    btn.style.opacity = '0.6';
    btn.style.pointerEvents = 'none';
    prg.style.display = 'block';
    setTimeout(function(){
        btn.style.opacity = '';
        btn.style.pointerEvents = '';
        prg.style.display = 'none';
    }, 4000);
}

function startSqlDownload() {
    var btn = document.getElementById('btn-sql');
    var prg = document.getElementById('sql-progress');
    if (!btn) return;
    btn.style.opacity = '0.6';
    btn.style.pointerEvents = 'none';
    prg.style.display = 'block';
    setTimeout(function(){
        btn.style.opacity = '';
        btn.style.pointerEvents = '';
        prg.style.display = 'none';
    }, 5000);
}

function onFileSelected(input) {
    var btn  = document.getElementById('btn-restore');
    var info = document.getElementById('file-selected-info');
    var hint = document.getElementById('restore-hint');
    var zone = document.getElementById('upload-zone');

    if (input.files && input.files[0]) {
        var f    = input.files[0];
        var name = f.name;
        var size = (f.size / 1024).toFixed(1) + ' KB';
        if (f.size > 1024 * 1024) size = (f.size / 1024 / 1024).toFixed(2) + ' MB';

        info.style.display = 'block';
        info.textContent   = '✅ ' + name + ' (' + size + ')';
        btn.disabled       = false;
        hint.textContent   = 'فایل آماده — روی دکمه کلیک کنید';
        zone.style.borderColor = '#16A085';
    } else {
        btn.disabled = true;
        info.style.display = 'none';
        hint.textContent   = 'ابتدا فایل SQL را انتخاب کنید';
        zone.style.borderColor = '';
    }
}

function showRestoreConfirm() {
    document.getElementById('restore-modal').classList.add('active');
}

function hideRestoreConfirm() {
    document.getElementById('restore-modal').classList.remove('active');
}

function submitRestore() {
    hideRestoreConfirm();
    var btn = document.getElementById('btn-restore');
    var prg = document.getElementById('restore-progress');
    btn.disabled = true;
    prg.style.display = 'block';
    document.getElementById('restore-form').submit();
}

// Drag & drop
(function(){
    var zone = document.getElementById('upload-zone');
    if (!zone) return;
    zone.addEventListener('dragover', function(e){ e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', function(){ zone.classList.remove('dragover'); });
    zone.addEventListener('drop', function(e){
        e.preventDefault();
        zone.classList.remove('dragover');
        var inp = document.getElementById('sql-file-input');
        if (e.dataTransfer.files.length) {
            inp.files = e.dataTransfer.files;
            onFileSelected(inp);
        }
    });
})();
</script>