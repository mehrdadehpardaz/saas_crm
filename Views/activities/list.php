<!-- Views/activities/list.php -->

<style>
.al-wrap {
    direction: rtl;
}

.al-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.al-header h2 { font-size:18px; font-weight:800; color:var(--ink); letter-spacing:-.01em; }
.al-btn-add {
    display:inline-flex; align-items:center; gap:6px; padding:10px 18px; border-radius:10px;
    font-size:13px; font-weight:700; text-decoration:none; background:var(--ember); color:#fff;
    box-shadow:0 3px 10px rgba(255,107,53,.28); transition:all .15s;
}
.al-btn-add:hover { background:var(--ember-deep); transform:translateY(-1px); }

.al-alert { padding:11px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; background:#E7F7F3; color:var(--teal-deep); border:1px solid #B8E5DA; }

/* فیلتر */
.al-filter {
    display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px; background:var(--card);
    border:1px solid var(--line); border-radius:12px; padding:12px 14px; align-items:center;
}
.al-filter select, .al-filter input[type=date] {
    padding:8px 12px; border:1.5px solid var(--line); border-radius:9px; font-size:12.5px;
    font-family:inherit; background:var(--paper); color:var(--ink); flex:1; min-width:130px;
}
.al-filter select:focus, .al-filter input:focus { outline:none; border-color:var(--ember); background:#fff; }
.al-filter-btn {
    padding:8px 18px; border-radius:9px; border:none; background:var(--ink); color:#fff;
    font-size:12.5px; font-weight:700; cursor:pointer; flex-shrink:0; transition:background .15s;
}
.al-filter-btn:hover { background:#1C2D52; }

/* empty state */
.al-empty { text-align:center; padding:50px 20px; background:var(--card); border:1px solid var(--line); border-radius:14px; }
.al-empty p { color:var(--ink-soft); font-size:14px; margin-bottom:18px; }
.al-empty a { display:inline-flex; align-items:center; gap:6px; padding:10px 22px; border-radius:10px; background:var(--ember); color:#fff; text-decoration:none; font-weight:700; font-size:13px; }

/* activity cards */
.al-list { display:flex; flex-direction:column; gap:10px; }
.al-card {
    background:var(--card); border:1px solid var(--line); border-radius:13px;
    padding:14px 16px; transition:box-shadow .18s; position:relative; overflow:hidden;
}
.al-card:hover { box-shadow:0 4px 16px rgba(20,33,61,.06); }

.al-card-top { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; margin-bottom:8px; flex-wrap:wrap; }
.al-card-who { font-size:13.5px; }
.al-card-who strong { color:var(--ink); font-weight:700; }
.al-card-who .contact-name { color:var(--ink-soft); }

.al-tags { display:flex; gap:6px; flex-wrap:wrap; }
.al-type-tag {
    display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px;
    font-size:11px; font-weight:700;
}
.al-type-call    { background:#E8F0FE; color:var(--blue); }
.al-type-meeting { background:#FFF3DD; color:var(--warning-deep); }
.al-type-email   { background:#E7F7F3; color:var(--teal); }
.al-type-note    { background:var(--paper-2); color:var(--ink-soft); }
.al-agent-tag { background:var(--paper-2); color:var(--ink-soft); padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }

.al-desc { font-size:13px; color:var(--ink); line-height:1.7; margin-bottom:10px; }

.al-card-foot { display:flex; justify-content:space-between; align-items:center; gap:10px; font-size:11px; color:var(--ink-soft); flex-wrap:wrap; padding-top:9px; border-top:1px solid var(--paper-2); }
.al-date { display:flex; align-items:center; gap:5px; }
.al-task-link {
    display:inline-flex; align-items:center; gap:4px; font-size:11px; color:var(--ember-deep);
    text-decoration:none; font-weight:600; background:#FFF1EA; padding:3px 9px; border-radius:8px;
}
.al-task-link:hover { background:#FFE3D1; }
.al-row-actions { display:flex; gap:6px; align-items:center; margin-right:auto; }
.al-action-btn {
    width:28px; height:28px; border-radius:7px; display:flex; align-items:center; justify-content:center;
    text-decoration:none; font-size:12px; transition:background .15s; border:none; cursor:pointer; padding:0;
}
.al-action-edit { background:#E8F0FE; color:var(--blue); }
.al-action-edit:hover { background:#D5E5FC; }
.al-action-delete { background:#FCE8E6; color:var(--danger); }
.al-action-delete:hover { background:#F9D4D0; }

.al-count { font-size:11.5px; color:var(--ink-soft); margin-top:14px; text-align:center; }
</style>

<div class="al-wrap">

<div class="al-header">
    <h2>فعالیت‌ها</h2>
    <a href="index.php?page=activities&action=add" class="al-btn-add">+ فعالیت جدید</a>
</div>

<?php if ($message === 'created'): ?>
    <div class="al-alert">فعالیت با موفقیت ثبت شد.</div>
<?php elseif ($message === 'updated'): ?>
    <div class="al-alert">فعالیت بروزرسانی شد.</div>
<?php elseif ($message === 'deleted'): ?>
    <div class="al-alert">فعالیت حذف شد.</div>
<?php endif; ?>

<form method="GET" action="index.php" class="al-filter" aria-label="فیلتر فعالیت‌ها">
    <input type="hidden" name="page" value="activities">
    <?php if ($customer_id): ?>
        <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
    <?php endif; ?>

    <label class="sr-only" for="al-filter-type">نوع فعالیت</label>
    <select name="type" id="al-filter-type">
        <option value="">همه نوع‌ها</option>
        <option value="call" <?= ($search_type === 'call') ? 'selected' : '' ?>>تماس</option>
        <option value="meeting" <?= ($search_type === 'meeting') ? 'selected' : '' ?>>جلسه</option>
        <option value="email" <?= ($search_type === 'email') ? 'selected' : '' ?>>ایمیل</option>
        <option value="note" <?= ($search_type === 'note') ? 'selected' : '' ?>>یادداشت</option>
    </select>

    <label class="sr-only" for="al-filter-date">تاریخ</label>
    <input type="date" name="date" id="al-filter-date" value="<?= crm_sanitize($search_date) ?>">

    <button type="submit" class="al-filter-btn">فیلتر</button>
</form>

<?php if (empty($activities)): ?>
    <div class="al-empty">
        <p>هیچ فعالیتی یافت نشد.</p>
        <a href="index.php?page=activities&action=add">+ ثبت اولین فعالیت</a>
    </div>
<?php else: ?>

    <div class="al-list">
        <?php
        $type_map = [
            'call'    => ['al-type-call', 'تماس'],
            'meeting' => ['al-type-meeting', 'جلسه'],
            'email'   => ['al-type-email', 'ایمیل'],
            'note'    => ['al-type-note', 'یادداشت'],
        ];
        foreach ($activities as $a):
            [$type_class, $type_label] = $type_map[$a['type']] ?? ['al-type-note', 'یادداشت'];
        ?>
        <article class="al-card">
            <div class="al-card-top">
                <div class="al-card-who">
                    <strong><?= crm_sanitize($a['company_name']) ?></strong>
                    <?php if ($a['contact_name']): ?>
                    <span class="contact-name"> — <?= crm_sanitize($a['contact_name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="al-tags">
                    <span class="al-type-tag <?= $type_class ?>"><?= $type_label ?></span>
                    <?php if ($is_manager): ?>
                    <span class="al-agent-tag"><?= crm_sanitize($a['agent_name']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($a['description']): ?>
            <p class="al-desc">
                <?= nl2br(crm_sanitize(mb_substr($a['description'], 0, 150))) ?>
                <?= mb_strlen($a['description']) > 150 ? '…' : '' ?>
            </p>
            <?php endif; ?>

            <div class="al-card-foot">
                <span class="al-date"><?= function_exists('jdatetime') ? jdatetime($a['created_at']) : date('Y/m/d H:i', strtotime($a['created_at'])) ?></span>

                <?php if (!empty($a['task_title']) && !empty($a['task_id'])): ?>
                <a href="index.php?page=tasks&action=view&id=<?= $a['task_id'] ?>" class="al-task-link">
                    <?= crm_sanitize($a['task_title']) ?>
                </a>
                <?php endif; ?>

                <div class="al-row-actions">
                    <a href="index.php?page=activities&action=edit&id=<?= $a['id'] ?>" class="al-action-btn al-action-edit" aria-label="ویرایش فعالیت">✏️</a>
                    <form method="POST" action="index.php?page=activities&action=delete&id=<?= $a['id'] ?>"
                          onsubmit="return confirm('حذف شود؟')" style="display:inline">
                        <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
                        <button type="submit" class="al-action-btn al-action-delete" aria-label="حذف فعالیت">🗑️</button>
                    </form>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <div class="al-count">نمایش <?= count($activities) ?> فعالیت</div>

<?php endif; ?>

</div><!-- /al-wrap -->