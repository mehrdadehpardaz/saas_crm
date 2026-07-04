<!-- Views/customers/list.php -->

<style>
.cl-wrap {
    direction: rtl;
}

.cl-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.cl-header h2 { font-size:18px; font-weight:800; color:var(--ink); letter-spacing:-.01em; }
.cl-btn-add {
    display:inline-flex; align-items:center; gap:6px; padding:10px 18px; border-radius:10px;
    font-size:13px; font-weight:700; text-decoration:none; background:var(--ember); color:#fff;
    box-shadow:0 3px 10px rgba(255,107,53,.28); transition:all .15s;
}
.cl-btn-add:hover { background:var(--ember-deep); transform:translateY(-1px); }

.cl-alert { padding:11px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; background:#E7F7F3; color:var(--teal-deep); border:1px solid #B8E5DA; }

/* search */
.cl-search-form { margin-bottom:14px; }
.cl-search-bar {
    display:flex; align-items:center; gap:8px; background:var(--card); border:1.5px solid var(--line);
    border-radius:12px; padding:4px 4px 4px 14px; transition:border-color .15s;
}
.cl-search-bar:focus-within { border-color:var(--ember); }
.cl-search-input {
    flex:1; border:none; background:transparent; padding:9px 4px; font-size:13.5px;
    font-family:inherit; color:var(--ink); outline:none;
}
.cl-search-btn {
    background:var(--ink); color:#fff; border:none; padding:9px 18px; border-radius:9px;
    font-size:12.5px; font-weight:700; cursor:pointer; flex-shrink:0; transition:background .15s;
}
.cl-search-btn:hover { background:#1C2D52; }

/* empty state */
.cl-empty { text-align:center; padding:50px 20px; background:var(--card); border:1px solid var(--line); border-radius:14px; }
.cl-empty p { color:var(--ink-soft); font-size:14px; margin-bottom:18px; }
.cl-empty a { display:inline-flex; align-items:center; gap:6px; padding:10px 22px; border-radius:10px; background:var(--ember); color:#fff; text-decoration:none; font-weight:700; font-size:13px; }

/* ── جدول دسکتاپ (مثل اکسل: سرستون قابل‌کلیک برای مرتب‌سازی) ── */
.cl-table-card { background:var(--card); border:1px solid var(--line); border-radius:14px; overflow:hidden; }
.cl-table-wrap { overflow-x:auto; }
.cl-table { width:100%; border-collapse:collapse; font-size:13px; }
.cl-table th {
    padding:11px 14px; text-align:right; background:var(--paper-2); color:var(--ink-soft);
    font-weight:700; font-size:11.5px; border-bottom:1px solid var(--line); white-space:nowrap;
    cursor:pointer; user-select:none; position:relative; transition:color .15s;
}
.cl-table th:hover { color:var(--ink); }
.cl-table th .sort-arrow { display:inline-block; width:10px; font-size:10px; color:var(--ember); margin-left:3px; opacity:0; }
.cl-table th.sorted .sort-arrow { opacity:1; }
.cl-table th:not(.sortable) { cursor:default; }
.cl-table th:not(.sortable):hover { color:var(--ink-soft); }
.cl-table td { padding:11px 14px; border-bottom:1px solid var(--paper-2); color:var(--ink); vertical-align:middle; }
.cl-table tbody tr { cursor:pointer; transition:background .12s; }
.cl-table tbody tr:nth-child(even) { background:#FCFBF9; }
.cl-table tbody tr:hover { background:var(--paper-2); }
.cl-table tbody tr:last-child td { border-bottom:none; }
.cl-table-name { font-weight:700; color:var(--ink); }
.cl-table-industry { display:inline-flex; padding:3px 10px; border-radius:20px; font-size:10.5px; font-weight:700; background:#E8F0FE; color:var(--blue); white-space:nowrap; }
.cl-table-muted { color:var(--ink-soft); }
.cl-table-count { background:var(--paper-2); color:var(--ink-soft); padding:2px 9px; border-radius:10px; font-size:11px; font-weight:600; white-space:nowrap; }
.cl-table-agent { background:#E7F7F3; color:var(--teal-deep); padding:2px 9px; border-radius:10px; font-size:11px; font-weight:600; white-space:nowrap; }
.cl-table-org { background:#F3E8FD; color:var(--purple, #9c27b0); padding:2px 9px; border-radius:10px; font-size:11px; font-weight:600; white-space:nowrap; }
.cl-tel-link { color:var(--blue); text-decoration:none; direction:ltr; display:inline-flex; align-items:center; gap:4px; position:relative; z-index:1; }
.cl-tel-link:hover { text-decoration:underline; }

/* ── موبایل: کارت فشرده با همان ترتیب ستون‌ها ── */
.cl-cards { display:none; }
.cl-card {
    display:block; background:var(--card); border:1px solid var(--line); border-radius:13px;
    padding:13px 15px; text-decoration:none; color:var(--ink); margin-bottom:8px; cursor:pointer;
}
.cl-card-top { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; margin-bottom:7px; }
.cl-card-name { font-size:14px; font-weight:700; color:var(--ink); }
.cl-card-meta { display:flex; flex-wrap:wrap; gap:6px 12px; font-size:11.5px; color:var(--ink-soft); align-items:center; }

@media (max-width: 760px) {
    .cl-table-card { display:none; }
    .cl-cards { display:block; }
}

/* count footer */
.cl-count { font-size:11.5px; color:var(--ink-soft); margin-top:14px; text-align:center; }
</style>

<div class="cl-wrap">

<div class="cl-header">
    <h2>مشتریان</h2>
    <a href="index.php?page=customers&action=add" class="cl-btn-add">+ مشتری جدید</a>
</div>

<?php if ($message === 'created'): ?>
    <div class="cl-alert">مشتری با موفقیت ثبت شد.</div>
<?php elseif ($message === 'updated'): ?>
    <div class="cl-alert">مشتری با موفقیت بروزرسانی شد.</div>
<?php elseif ($message === 'deleted'): ?>
    <div class="cl-alert">مشتری حذف شد.</div>
<?php endif; ?>

<form method="GET" action="index.php" class="cl-search-form" role="search">
    <input type="hidden" name="page" value="customers">
    <div class="cl-search-bar">
        <label class="sr-only" for="cl-search">جستجوی مشتری</label>
        <input type="text" name="search" id="cl-search" class="cl-search-input" placeholder="جستجو بر اساس نام شرکت، شخص یا تلفن..." value="<?= crm_sanitize($search) ?>">
        <button type="submit" class="cl-search-btn">جستجو</button>
    </div>
</form>

<?php if (empty($customers)): ?>
    <div class="cl-empty">
        <p><?= !empty($search) ? 'نتیجه‌ای برای جستجوی شما یافت نشد.' : 'هنوز مشتری‌ای ثبت نکردید.' ?></p>
        <a href="index.php?page=customers&action=add">+ ثبت اولین مشتری</a>
    </div>
<?php else: ?>

    <!-- ── دسکتاپ: جدول قابل مرتب‌سازی ── -->
    <div class="cl-table-card">
        <div class="cl-table-wrap">
            <table class="cl-table" id="cl-sortable-table">
                <thead>
                    <tr>
                        <th class="sortable" data-type="text">نام شرکت<span class="sort-arrow">▲</span></th>
                        <th class="sortable" data-type="text">صنعت<span class="sort-arrow">▲</span></th>
                        <th class="sortable" data-type="text">شخص اصلی<span class="sort-arrow">▲</span></th>
                        <th class="sortable" data-type="text">تلفن<span class="sort-arrow">▲</span></th>
                        <th class="sortable" data-type="num">فعالیت‌ها<span class="sort-arrow">▲</span></th>
                        <?php if ($is_manager): ?>
                        <th class="sortable" data-type="text">مسئول<span class="sort-arrow">▲</span></th>
                        <?php endif; ?>
                        <?php if ($is_super): ?>
                        <th class="sortable" data-type="text">سازمان<span class="sort-arrow">▲</span></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $c): ?>
                    <tr onclick="window.location='index.php?page=customers&action=view&id=<?= $c['id'] ?>'">
                        <td class="cl-table-name"><?= crm_sanitize($c['company_name']) ?></td>
                        <td><?php if ($c['industry_title']): ?><span class="cl-table-industry"><?= crm_sanitize($c['industry_title']) ?></span><?php else: ?><span class="cl-table-muted">—</span><?php endif; ?></td>
                        <td class="cl-table-muted"><?= $c['contact_person'] ? crm_sanitize($c['contact_person']) : '—' ?></td>
                        <td>
                            <?php if ($c['phone']): ?>
                                <a href="tel:<?= crm_sanitize($c['phone']) ?>" class="cl-tel-link" onclick="event.stopPropagation()">📞 <?= crm_sanitize($c['phone']) ?></a>
                            <?php else: ?>
                                <span class="cl-table-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-sort-value="<?= (int)$c['activity_count'] ?>"><span class="cl-table-count"><?= $c['activity_count'] ?></span></td>
                        <?php if ($is_manager): ?>
                        <td><span class="cl-table-agent"><?= crm_sanitize($c['agent_name']) ?></span></td>
                        <?php endif; ?>
                        <?php if ($is_super): ?>
                        <td><span class="cl-table-org"><?= crm_sanitize($c['company_label'] ?? '—') ?></span></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── موبایل: کارت فشرده با همان ترتیب اطلاعات ── -->
    <div class="cl-cards">
        <?php foreach ($customers as $c): ?>
        <div class="cl-card" onclick="window.location='index.php?page=customers&action=view&id=<?= $c['id'] ?>'">
            <div class="cl-card-top">
                <div class="cl-card-name"><?= crm_sanitize($c['company_name']) ?></div>
                <?php if ($c['industry_title']): ?>
                <span class="cl-table-industry"><?= crm_sanitize($c['industry_title']) ?></span>
                <?php endif; ?>
            </div>
            <div class="cl-card-meta">
                <?php if ($c['contact_person']): ?><span><?= crm_sanitize($c['contact_person']) ?></span><?php endif; ?>
                <?php if ($c['phone']): ?><a href="tel:<?= crm_sanitize($c['phone']) ?>" class="cl-tel-link" onclick="event.stopPropagation()">📞 <?= crm_sanitize($c['phone']) ?></a><?php endif; ?>
                <span class="cl-table-count"><?= $c['activity_count'] ?> فعالیت</span>
                <?php if ($is_manager): ?><span class="cl-table-agent"><?= crm_sanitize($c['agent_name']) ?></span><?php endif; ?>
                <?php if ($is_super): ?><span class="cl-table-org"><?= crm_sanitize($c['company_label'] ?? '—') ?></span><?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="cl-count">نمایش <?= count($customers) ?> مشتری — برای مرتب‌سازی روی نام هر ستون کلیک کنید</div>

<?php endif; ?>

</div><!-- /cl-wrap -->

<script>
(function(){
    var table = document.getElementById('cl-sortable-table');
    if (!table) return;
    var tbody = table.querySelector('tbody');
    var headers = table.querySelectorAll('th.sortable');

    headers.forEach(function(th, colIndex){
        th.addEventListener('click', function(){
            var asc = !th.classList.contains('sorted') || th.dataset.dir === 'desc';
            headers.forEach(function(h){ h.classList.remove('sorted'); h.querySelector('.sort-arrow').textContent = '▲'; h.dataset.dir=''; });
            th.classList.add('sorted');
            th.dataset.dir = asc ? 'asc' : 'desc';
            th.querySelector('.sort-arrow').textContent = asc ? '▲' : '▼';

            var type = th.getAttribute('data-type');
            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));

            rows.sort(function(r1, r2){
                var c1 = r1.children[colIndex];
                var c2 = r2.children[colIndex];
                var v1, v2;
                if (type === 'num') {
                    v1 = parseFloat(c1.getAttribute('data-sort-value') || c1.textContent) || 0;
                    v2 = parseFloat(c2.getAttribute('data-sort-value') || c2.textContent) || 0;
                } else {
                    v1 = c1.textContent.trim();
                    v2 = c2.textContent.trim();
                }
                var cmp = (v1 < v2) ? -1 : (v1 > v2 ? 1 : 0);
                return asc ? cmp : -cmp;
            });

            rows.forEach(function(r){ tbody.appendChild(r); });
        });
    });
})();
</script>