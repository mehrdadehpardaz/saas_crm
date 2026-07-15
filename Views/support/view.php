<!-- Views/support/view.php -->
<?php
$status_labels = ['open' => '🔴 باز', 'in_progress' => '🟡 در حال بررسی', 'closed' => '🟢 بسته‌شده'];
$role_labels = ['super_admin'=>'👑 سوپر ادمین','admin'=>'🛡️ مدیر','manager'=>'👔 مدیر فروش','agent'=>'📞 کارشناس'];
?>
<style>
.sv-wrap {
    direction: rtl;
    max-width: 720px;
    margin: 0 auto;
    width: 100%;
}

.sv-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.sv-header h2 { font-size:18px; font-weight:800; color:var(--ink); letter-spacing:-.01em; }
.sv-btn-back {
    display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:9px;
    font-size:12.5px; font-weight:700; text-decoration:none; background:var(--card);
    color:var(--ink-soft); border:1.5px solid var(--line); transition:all .15s;
}
.sv-btn-back:hover { border-color:var(--ink); color:var(--ink); }

.sv-alert { padding:11px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; background:#E7F7F3; color:var(--teal-deep); border:1px solid #B8E5DA; }

.sv-badge { display:inline-flex; align-items:center; gap:4px; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; margin-bottom:16px; }
.sv-badge-open        { background:#FCE8E6; color:var(--danger); }
.sv-badge-in_progress  { background:#FFF3DD; color:var(--warning-deep); }
.sv-badge-closed      { background:#E7F7F3; color:var(--teal-deep); }

/* کارت اطلاعات کاربر — فقط سوپر ادمین */
.sv-user-card { background:var(--card); border:1px solid var(--line); border-radius:14px; overflow:hidden; margin-bottom:16px; }
.sv-user-hd { padding:13px 16px; border-bottom:1px solid var(--line); background:var(--paper-2); }
.sv-user-hd h3 { font-size:13.5px; font-weight:700; color:var(--ink); }
.sv-user-table { width:100%; border-collapse:collapse; font-size:13px; }
.sv-user-table tr { border-bottom:1px solid var(--paper-2); }
.sv-user-table tr:last-child { border-bottom:none; }
.sv-user-table th { text-align:right; padding:9px 16px; color:var(--ink-soft); font-weight:600; font-size:11.5px; width:110px; white-space:nowrap; }
.sv-user-table td { padding:9px 16px; color:var(--ink); font-weight:500; }
.sv-tel-link { color:var(--blue); text-decoration:none; direction:ltr; }
.sv-tel-link:hover { text-decoration:underline; }

/* موضوع تیکت */
.sv-subject-row { font-size:15px; font-weight:800; color:var(--ink); margin-bottom:14px; }

/* گفتگو — حباب‌های پیام */
.sv-thread { display:flex; flex-direction:column; gap:12px; margin-bottom:18px; }
.sv-msg { max-width:82%; padding:12px 15px; border-radius:14px; position:relative; }
.sv-msg-own {
    align-self: flex-end;
    background: var(--ember);
    color: #fff;
    border-bottom-left-radius: 4px;
}
.sv-msg-other {
    align-self: flex-start;
    background: var(--card);
    border: 1px solid var(--line);
    color: var(--ink);
    border-bottom-right-radius: 4px;
}
.sv-msg-meta { display:flex; align-items:center; gap:6px; font-size:11px; margin-bottom:5px; opacity:.85; }
.sv-msg-own .sv-msg-meta { color: rgba(255,255,255,.9); }
.sv-msg-other .sv-msg-meta { color: var(--ink-soft); }
.sv-msg-role-tag {
    font-size: 10px; font-weight: 800; padding: 1px 8px; border-radius: 10px;
}
.sv-msg-own .sv-msg-role-tag { background: rgba(255,255,255,.25); }
.sv-msg-other .sv-msg-role-tag { background: #F3E8FD; color: #9c27b0; }
.sv-msg-text { font-size:13.5px; line-height:1.75; white-space:pre-wrap; }

/* فرم ارسال پیام جدید — هم صاحب تیکت، هم سوپر ادمین */
.sv-composer { background:var(--card); border:1px solid var(--line); border-radius:16px; padding:20px; }
.sv-composer h3 { font-size:14px; font-weight:700; color:var(--ink); margin-bottom:14px; }
.sv-group { margin-bottom:14px; }
.sv-group label { display:block; font-size:12.5px; font-weight:700; color:var(--ink-soft); margin-bottom:7px; }
.sv-group textarea {
    width:100%; padding:11px 14px; border:1.5px solid var(--line); border-radius:10px;
    font-size:13.5px; font-family:inherit; background:var(--paper); color:var(--ink);
    resize:vertical; min-height:100px; transition:border-color .15s, background .15s;
}
.sv-group textarea:focus { outline:none; border-color:var(--ember); background:#fff; }
.sv-group select {
    width:100%; padding:10px 14px; border:1.5px solid var(--line); border-radius:10px;
    font-size:13px; font-family:inherit; background:var(--paper); color:var(--ink);
}
.sv-composer-actions { display:flex; justify-content:flex-end; }
.sv-btn-submit {
    display:inline-flex; align-items:center; gap:7px; padding:11px 24px; border-radius:11px;
    font-size:13.5px; font-weight:700; border:none; cursor:pointer; background:var(--ember); color:#fff;
    box-shadow:0 4px 14px rgba(255,107,53,.3); transition:all .15s;
}
.sv-btn-submit:hover { background:var(--ember-deep); transform:translateY(-1px); }

.sv-closed-notice {
    background:#F2EEE6; border:1px dashed var(--line); border-radius:12px; padding:12px 16px;
    font-size:12.5px; color:var(--ink-soft); text-align:center;
}
</style>

<div class="sv-wrap">

<div class="sv-header">
    <h2>🎫 تیکت پشتیبانی</h2>
    <a href="index.php?page=support" class="sv-btn-back">🔙 بازگشت</a>
</div>

<?php if (($message ?? '') === 'replied'): ?>
    <div class="sv-alert">پیام با موفقیت ثبت شد.</div>
<?php endif; ?>

<span class="sv-badge sv-badge-<?= $ticket['status'] ?>"><?= $status_labels[$ticket['status']] ?? $ticket['status'] ?></span>

<?php if ($is_super): ?>
<!-- ═══ اطلاعات کامل کاربرِ فرستنده — فقط سوپر ادمین ═══ -->
<div class="sv-user-card">
    <div class="sv-user-hd"><h3>👤 مشخصات کاربر</h3></div>
    <table class="sv-user-table">
        <tr><th>نام</th><td><?= crm_sanitize($ticket['full_name']) ?></td></tr>
        <tr><th>موبایل</th><td><a href="tel:<?= crm_sanitize($ticket['mobile']) ?>" class="sv-tel-link">📞 <?= crm_sanitize($ticket['mobile']) ?></a></td></tr>
        <?php if (!empty($ticket['user_phone'])): ?>
        <tr><th>تلفن</th><td><a href="tel:<?= crm_sanitize($ticket['user_phone']) ?>" class="sv-tel-link">📞 <?= crm_sanitize($ticket['user_phone']) ?></a></td></tr>
        <?php endif; ?>
        <tr><th>نقش</th><td><?= crm_sanitize($role_labels[$ticket['role']] ?? $ticket['role']) ?></td></tr>
        <?php if (!empty($ticket['position_title'])): ?>
        <tr><th>سمت</th><td><?= crm_sanitize($ticket['position_title']) ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($ticket['company_name'])): ?>
        <tr><th>شرکت</th><td><?= crm_sanitize($ticket['company_name']) ?></td></tr>
        <?php endif; ?>
        <tr><th>پلن</th><td><?= $ticket['plan_type'] === 'trial' ? '🎁 رایگان ۱۴ روزه' : ($ticket['plan_type'] === 'monthly' ? '📅 ماهانه' : '🗓️ سالانه') ?></td></tr>
        <tr><th>عضویت از</th><td><?= function_exists('jdate') ? jdate($ticket['user_created_at']) : date('Y/m/d', strtotime($ticket['user_created_at'])) ?></td></tr>
        <tr>
            <th>مشاهده</th>
            <td><a href="index.php?page=users&action=view&id=<?= $ticket['user_id'] ?>" style="color:var(--blue);font-weight:700">پروفایل کامل کاربر ←</a></td>
        </tr>
    </table>
</div>
<?php endif; ?>

<div class="sv-subject-row"><?= crm_sanitize($ticket['subject']) ?></div>

<!-- ═══ گفتگو ═══ -->
<div class="sv-thread">
    <?php foreach ($messages as $m):
        $is_own = ((int)$m['sender_user_id'] === (int)$user['id']);
        $sender_is_staff = in_array($m['sender_role'], ['super_admin', 'admin']);
    ?>
    <div class="sv-msg <?= $is_own ? 'sv-msg-own' : 'sv-msg-other' ?>">
        <div class="sv-msg-meta">
            <span><?= crm_sanitize($m['sender_name']) ?></span>
            <?php if ($sender_is_staff): ?>
            <span class="sv-msg-role-tag">پشتیبانی</span>
            <?php endif; ?>
            <span>—</span>
            <span><?= function_exists('jdatetime') ? jdatetime($m['created_at']) : date('Y/m/d H:i', strtotime($m['created_at'])) ?></span>
        </div>
        <div class="sv-msg-text"><?= nl2br(crm_sanitize($m['message'])) ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ═══ فرم ارسال پیام — صاحب تیکت یا سوپر ادمین ═══ -->
<div class="sv-composer">
    <h3>✏️ ارسال پیام جدید</h3>
    <form method="POST" action="index.php?page=support&action=reply&id=<?= $ticket['id'] ?>">
        <div class="sv-group">
            <textarea name="message" placeholder="پیام خودتون رو بنویسید..."></textarea>
        </div>
        <?php if ($is_super): ?>
        <div class="sv-group">
            <label>وضعیت تیکت</label>
            <select name="status">
                <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>🔴 باز</option>
                <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>🟡 در حال بررسی</option>
                <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>🟢 بسته‌شده</option>
            </select>
        </div>
        <?php elseif ($ticket['status'] === 'closed'): ?>
        <div class="sv-closed-notice" style="margin-bottom:14px">این تیکت بسته شده — اگه پیام جدیدی بفرستید، خودکار دوباره باز می‌شه.</div>
        <?php endif; ?>
        <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
        <div class="sv-composer-actions">
            <button type="submit" class="sv-btn-submit">📨 ارسال</button>
        </div>
    </form>
</div>

</div><!-- /sv-wrap -->