<!-- Views/contacts/list.php -->

<style>
.ctl-wrap {
    direction: rtl;
}

.ctl-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.ctl-header h2 { font-size:18px; font-weight:800; color:var(--ink); letter-spacing:-.01em; }

/* ── نوار فیلتر/جستجو ── */
.ctl-toolbar {
    background:var(--card); border:1.5px solid var(--line); border-radius:12px;
    padding:12px 14px; margin-bottom:14px; display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end;
}
.ctl-field { flex:1; min-width:150px; }
.ctl-field label { display:block; font-size:11px; font-weight:600; color:var(--ink-soft); margin-bottom:5px; }
.ctl-field select,
.ctl-field input[type=text] {
    width:100%; padding:9px 12px; border:1.5px solid var(--line); border-radius:9px;
    font-size:12.5px; font-family:inherit; background:var(--paper); color:var(--ink);
    transition:border-color .15s;
}
.ctl-field select:focus, .ctl-field input:focus { outline:none; border-color:var(--ember); background:#fff; }
.ctl-search-row { flex:2; min-width:220px; display:flex; gap:8px; }
.ctl-search-btn {
    background:var(--ink); color:#fff; border:none; padding:9px 18px; border-radius:9px;
    font-size:12.5px; font-weight:700; cursor:pointer; flex-shrink:0; transition:background .15s; align-self:flex-end;
}
.ctl-search-btn:hover { background:#1C2D52; }
.ctl-clear-btn {
    padding:9px 16px; border-radius:9px; font-size:12.5px; font-weight:700; text-decoration:none;
    background:var(--card); color:var(--ink-soft); border:1.5px solid var(--line); flex-shrink:0; transition:all .15s;
    align-self:flex-end; white-space:nowrap;
}
.ctl-clear-btn:hover { border-color:var(--ink); color:var(--ink); }

.ctl-active-filters { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:14px; }
.ctl-filter-chip {
    display:inline-flex; align-items:center; gap:6px; background:#FFF1EA; color:var(--ember-deep);
    padding:4px 6px 4px 12px; border-radius:20px; font-size:11.5px; font-weight:700;
}
.ctl-filter-chip a { color:var(--ember-deep); text-decoration:none; background:rgba(255,107,53,.15); width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; }
.ctl-filter-chip a:hover { background:rgba(255,107,53,.3); }

/* empty state */
.ctl-empty { text-align:center; padding:50px 20px; background:var(--card); border:1px solid var(--line); border-radius:14px; color:var(--ink-soft); }

/* ── جدول دسکتاپ (قابل مرتب‌سازی، هماهنگ با صفحه مشتریان) ── */
.ctl-table-card { background:var(--card); border:1px solid var(--line); border-radius:14px; overflow:hidden; }
.ctl-table-wrap { overflow-x:auto; }
.ctl-table { width:100%; border-collapse:collapse; font-size:13px; }
.ctl-table th {
    padding:11px 14px; text-align:right; background:var(--paper-2); color:var(--ink-soft);
    font-weight:700; font-size:11.5px; border-bottom:1px solid var(--line); white-space:nowrap;
    cursor:pointer; user-select:none; transition:color .15s;
}
.ctl-table th:hover { color:var(--ink); }
.ctl-table th .sort-arrow { display:inline-block; width:10px; font-size:10px; color:var(--ember); margin-left:3px; opacity:0; }
.ctl-table th.sorted .sort-arrow { opacity:1; }
.ctl-table th.no-sort { cursor:default; }
.ctl-table th.no-sort:hover { color:var(--ink-soft); }
.ctl-table td { padding:11px 14px; border-bottom:1px solid var(--paper-2); color:var(--ink); vertical-align:middle; }
.ctl-table tbody tr:nth-child(even) { background:#FCFBF9; }
.ctl-table tbody tr:hover { background:var(--paper-2); }
.ctl-table tbody tr:last-child td { border-bottom:none; }
.ctl-table a.cust-link { color:var(--blue); text-decoration:none; font-weight:500; }
.ctl-table a.cust-link:hover { text-decoration:underline; }
.ctl-edit-link {
    display:inline-flex; align-items:center; gap:4px; padding:5px 12px; border-radius:8px;
    font-size:11.5px; font-weight:700; text-decoration:none; background:#E8F0FE; color:var(--blue); transition:background .15s;
}
.ctl-edit-link:hover { background:#D5E5FC; }
.ctl-primary-icon { color:var(--ember); font-size:14px; }
.ctl-owner-tag { background:#E7F7F3; color:var(--teal-deep); padding:2px 9px; border-radius:10px; font-size:11px; font-weight:600; white-space:nowrap; }
.ctl-tel-link { color:var(--blue); text-decoration:none; direction:ltr; display:inline-flex; align-items:center; gap:4px; }
.ctl-tel-link:hover { text-decoration:underline; }

/* ── موبایل: کارت ── */
.ctl-mob-cards { display:none; }
.ctl-mob-card {
    background:var(--card); border:1px solid var(--line); border-radius:13px;
    padding:14px 16px; margin-bottom:10px;
}
.ctl-mob-top { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; margin-bottom:9px; }
.ctl-mob-name { font-weight:700; font-size:14px; color:var(--ink); display:flex; align-items:center; gap:6px; }
.ctl-mob-position { font-size:11.5px; color:var(--ink-soft); margin-top:3px; }
.ctl-mob-edit { font-size:11.5px; color:var(--blue); text-decoration:none; font-weight:700; background:#E8F0FE; padding:5px 11px; border-radius:8px; flex-shrink:0; }
.ctl-mob-meta { display:flex; flex-direction:column; gap:6px; font-size:12px; color:var(--ink-soft); }
.ctl-mob-meta a { color:inherit; text-decoration:none; }
.ctl-mob-meta .cust-link { color:var(--blue); font-weight:500; }

@media (max-width: 760px) {
    .ctl-table-card { display:none; }
    .ctl-mob-cards { display:block; }
}

.ctl-count { font-size:11.5px; color:var(--ink-soft); margin-top:14px; text-align:center; }
</style>

<div class="ctl-wrap">

<div class="ctl-header">
    <h2>لیست مخاطبین</h2>
</div>

<form method="GET" class="ctl-toolbar" role="search">
    <input type="hidden" name="page" value="contacts">
    <input type="hidden" name="action" value="list">

    <div class="ctl-field" style="position:relative">
        <label for="ctl-company">شرکت</label>
        <input type="text" name="company_q" id="ctl-company" autocomplete="off"
               list="ctl-company-options"
               placeholder="تایپ کنید یا از لیست انتخاب کنید..."
               value="<?= crm_sanitize($filter_customer_id !== '' && isset($filter_companies[$filter_customer_id]) ? $filter_companies[$filter_customer_id] : $filter_company_q) ?>">
        <input type="hidden" name="company_id" id="ctl-company-id" value="<?= crm_sanitize($filter_customer_id) ?>">
        <datalist id="ctl-company-options">
            <?php foreach ($filter_companies as $cid => $cname): ?>
            <option data-id="<?= $cid ?>" value="<?= crm_sanitize($cname) ?>"></option>
            <?php endforeach; ?>
        </datalist>
    </div>

    <div class="ctl-field">
        <label for="ctl-owner">ثبت‌کننده</label>
        <select name="owner_id" id="ctl-owner" onchange="this.form.submit()">
            <option value="">همه کاربران</option>
            <?php foreach ($filter_owners as $oid => $oname): ?>
            <option value="<?= $oid ?>" <?= (string)$filter_owner_id === (string)$oid ? 'selected' : '' ?>><?= crm_sanitize($oname) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="ctl-search-row">
        <div class="ctl-field">
            <label for="ctl-q">جستجو</label>
            <input type="text" name="q" id="ctl-q" value="<?= crm_sanitize($_GET['q'] ?? '') ?>" placeholder="نام، سمت، تلفن...">
        </div>
        <button type="submit" class="ctl-search-btn">اعمال</button>
        <?php if ($filter_customer_id !== '' || $filter_company_q !== '' || $filter_owner_id !== '' || !empty($_GET['q'])): ?>
        <a href="index.php?page=contacts&action=list" class="ctl-clear-btn">پاک کردن همه</a>
        <?php endif; ?>
    </div>
</form>

<?php if ($filter_customer_id !== '' || $filter_company_q !== '' || $filter_owner_id !== ''): ?>
<div class="ctl-active-filters">
    <?php if ($filter_customer_id !== '' && isset($filter_companies[$filter_customer_id])): ?>
    <span class="ctl-filter-chip">
        شرکت: <?= crm_sanitize($filter_companies[$filter_customer_id]) ?>
        <a href="index.php?page=contacts&action=list&owner_id=<?= urlencode($filter_owner_id) ?>&q=<?= urlencode($_GET['q'] ?? '') ?>" aria-label="حذف فیلتر شرکت">×</a>
    </span>
    <?php elseif ($filter_company_q !== ''): ?>
    <span class="ctl-filter-chip">
        شرکت شامل: «<?= crm_sanitize($filter_company_q) ?>»
        <a href="index.php?page=contacts&action=list&owner_id=<?= urlencode($filter_owner_id) ?>&q=<?= urlencode($_GET['q'] ?? '') ?>" aria-label="حذف فیلتر شرکت">×</a>
    </span>
    <?php endif; ?>
    <?php if ($filter_owner_id !== '' && isset($filter_owners[$filter_owner_id])): ?>
    <span class="ctl-filter-chip">
        ثبت‌کننده: <?= crm_sanitize($filter_owners[$filter_owner_id]) ?>
        <a href="index.php?page=contacts&action=list&company_id=<?= urlencode($filter_customer_id) ?>&company_q=<?= urlencode($filter_company_q) ?>&q=<?= urlencode($_GET['q'] ?? '') ?>" aria-label="حذف فیلتر ثبت‌کننده">×</a>
    </span>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (empty($contacts)): ?>
    <div class="ctl-empty">
        <?= (!empty($_GET['q']) || $filter_customer_id !== '' || $filter_owner_id !== '') ? 'با این فیلتر نتیجه‌ای یافت نشد.' : 'هنوز مخاطبی ثبت نشده.' ?>
    </div>
<?php else: ?>

<!-- دسکتاپ: جدول قابل مرتب‌سازی -->
<div class="ctl-table-card">
    <div class="ctl-table-wrap">
        <table class="ctl-table" id="ctl-sortable-table">
            <thead>
                <tr>
                    <th data-type="text">نام<span class="sort-arrow">▲</span></th>
                    <th data-type="text">سمت<span class="sort-arrow">▲</span></th>
                    <th data-type="text">شرکت<span class="sort-arrow">▲</span></th>
                    <th data-type="text">تلفن<span class="sort-arrow">▲</span></th>
                    <th data-type="text">ثبت‌کننده<span class="sort-arrow">▲</span></th>
                    <th class="no-sort" style="text-align:center">اصلی</th>
                    <th class="no-sort"><span class="sr-only">عملیات</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $c): ?>
                <tr>
                    <td style="font-weight:700"><?= crm_sanitize($c['full_name']) ?></td>
                    <td><?= crm_sanitize($c['position'] ?? '—') ?></td>
                    <td>
                        <a href="index.php?page=customers&action=view&id=<?= $c['cid'] ?>" class="cust-link">
                            <?= crm_sanitize($c['customer_name'] ?? '—') ?>
                        </a>
                    </td>
                    <td>
                        <?php if ($c['phone']): ?>
                            <a href="tel:<?= crm_sanitize($c['phone']) ?>" class="ctl-tel-link">📞 <?= crm_sanitize($c['phone']) ?></a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?php if ($c['owner_name']): ?><span class="ctl-owner-tag"><?= crm_sanitize($c['owner_name']) ?></span><?php else: ?>—<?php endif; ?></td>
                    <td style="text-align:center"><?= $c['is_primary'] ? '<span class="ctl-primary-icon" aria-label="مخاطب اصلی">★</span>' : '—' ?></td>
                    <td>
                        <a href="index.php?page=contacts&action=edit&id=<?= $c['id'] ?>" class="ctl-edit-link">ویرایش</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- موبایل: کارت -->
<div class="ctl-mob-cards">
    <?php foreach ($contacts as $c): ?>
    <div class="ctl-mob-card">
        <div class="ctl-mob-top">
            <div>
                <div class="ctl-mob-name">
                    <?= crm_sanitize($c['full_name']) ?>
                    <?= $c['is_primary'] ? '<span class="ctl-primary-icon" aria-label="مخاطب اصلی">★</span>' : '' ?>
                </div>
                <?php if ($c['position']): ?>
                <div class="ctl-mob-position"><?= crm_sanitize($c['position']) ?></div>
                <?php endif; ?>
            </div>
            <a href="index.php?page=contacts&action=edit&id=<?= $c['id'] ?>" class="ctl-mob-edit">ویرایش</a>
        </div>

        <div class="ctl-mob-meta">
            <div>
                <a href="index.php?page=customers&action=view&id=<?= $c['cid'] ?>" class="cust-link">
                    <?= crm_sanitize($c['customer_name'] ?? '—') ?>
                </a>
            </div>
            <?php if ($c['phone']): ?>
            <div><a href="tel:<?= crm_sanitize($c['phone']) ?>" class="ctl-tel-link">📞 <?= crm_sanitize($c['phone']) ?></a></div>
            <?php endif; ?>
            <?php if ($c['email']): ?>
            <div><?= crm_sanitize($c['email']) ?></div>
            <?php endif; ?>
            <?php if ($c['owner_name']): ?>
            <div><span class="ctl-owner-tag"><?= crm_sanitize($c['owner_name']) ?></span></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="ctl-count"><?= count($contacts) ?> مخاطب — برای مرتب‌سازی روی نام هر ستون کلیک کنید</div>

<?php endif; ?>

</div><!-- /ctl-wrap -->

<script>
(function(){
    var table = document.getElementById('ctl-sortable-table');
    if (!table) return;
    var tbody = table.querySelector('tbody');
    var headers = table.querySelectorAll('th:not(.no-sort)');

    headers.forEach(function(th){
        var colIndex = Array.prototype.indexOf.call(th.parentNode.children, th);
        th.addEventListener('click', function(){
            var asc = !th.classList.contains('sorted') || th.dataset.dir === 'desc';
            table.querySelectorAll('th').forEach(function(h){
                h.classList.remove('sorted');
                var arrow = h.querySelector('.sort-arrow');
                if (arrow) arrow.textContent = '▲';
                h.dataset.dir = '';
            });
            th.classList.add('sorted');
            th.dataset.dir = asc ? 'asc' : 'desc';
            th.querySelector('.sort-arrow').textContent = asc ? '▲' : '▼';

            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
            rows.sort(function(r1, r2){
                var v1 = r1.children[colIndex].textContent.trim();
                var v2 = r2.children[colIndex].textContent.trim();
                var cmp = v1.localeCompare(v2, 'fa');
                return asc ? cmp : -cmp;
            });
            rows.forEach(function(r){ tbody.appendChild(r); });
        });
    });
})();

// ── تطبیق متن تایپ‌شده در فیلد «شرکت» با گزینه‌های پیشنهادی ──
// اگر متن دقیقاً با یکی از شرکت‌های لیست یکی باشد، شناسه دقیق آن در
// فیلد مخفی قرار می‌گیرد (فیلتر دقیق)؛ در غیر این صورت فیلد مخفی خالی
// می‌ماند و هنگام ارسال فرم، فیلتر «شامل» سمت سرور روی متن آزاد اجرا می‌شود.
(function(){
    var input  = document.getElementById('ctl-company');
    var hidden = document.getElementById('ctl-company-id');
    var options = document.getElementById('ctl-company-options');
    if (!input || !hidden || !options) return;

    function resolveExactMatch() {
        var typed = input.value.trim().toLowerCase();
        var match = null;
        options.querySelectorAll('option').forEach(function(opt){
            if (opt.value.trim().toLowerCase() === typed) match = opt.getAttribute('data-id');
        });
        hidden.value = match || '';
    }

    input.addEventListener('input', resolveExactMatch);
    resolveExactMatch();
})();
</script>