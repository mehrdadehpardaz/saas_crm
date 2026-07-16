<!-- Views/users/form.php -->
<?php
$is_edit = ($action === 'edit');
$is_super = crm_is_super_admin();
?>

<style>
.uf-wrap {
    --uf-ink:#14213D; --uf-ink-soft:#4A5578; --uf-ember:#FF6B35; --uf-ember-deep:#E6531E;
    --uf-teal:#16A085; --uf-paper:#FAF8F5; --uf-paper2:#F2EEE6; --uf-line:#E5DFD3; --uf-card:#FFFFFF;
    direction: rtl; 
    max-width: 720px;
    margin: 0 auto;
    width: 100%;
}
.uf-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.uf-header h2 { font-size:18px; font-weight:800; color:var(--uf-ink); letter-spacing:-.01em; }
.uf-btn-back { display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:9px; font-size:12.5px; font-weight:700; text-decoration:none; background:var(--uf-card); color:var(--uf-ink-soft); border:1.5px solid var(--uf-line); transition:all .15s; }
.uf-btn-back:hover { border-color:var(--uf-ink); color:var(--uf-ink); }
.uf-alert-error { background:#FCE8E6; color:#C0392B; border:1px solid #F5C6CB; border-radius:10px; padding:11px 16px; font-size:13px; margin-bottom:16px; }
.uf-form { background:var(--uf-card); border:1px solid var(--uf-line); border-radius:16px; padding:26px 24px; box-shadow:0 4px 20px rgba(20,33,61,.05); }
.uf-group { margin-bottom:18px; }
.uf-group label { display:flex; align-items:center; gap:6px; font-size:12.5px; font-weight:700; color:var(--uf-ink-soft); margin-bottom:7px; }
.uf-group input[type=text],
.uf-group input[type=tel],
.uf-group input[type=password],
.uf-group select {
    width:100%; padding:11px 14px; border:1.5px solid var(--uf-line); border-radius:10px;
    font-size:13.5px; font-family:inherit; background:var(--uf-paper); color:var(--uf-ink);
    transition:border-color .15s, background .15s;
}
.uf-group input:focus, .uf-group select:focus { outline:none; border-color:var(--uf-ember); background:#fff; }
.uf-group input:disabled { background:var(--uf-paper2); color:var(--uf-ink-soft); cursor:not-allowed; }
.uf-hint { display:block; margin-top:6px; font-size:11px; color:#8A8478; }
.uf-divider { height:1px; background:var(--uf-paper2); margin:20px 0; }
.uf-section-label { font-size:11.5px; font-weight:800; color:var(--uf-ink-soft); letter-spacing:.03em; text-transform:uppercase; margin-bottom:14px; }
.uf-actions { display:flex; gap:10px; margin-top:22px; padding-top:18px; border-top:1px solid var(--uf-paper2); }
.uf-btn-submit { flex:1; display:flex; align-items:center; justify-content:center; gap:7px; padding:12px 20px; border-radius:11px; font-size:14px; font-weight:700; border:none; cursor:pointer; background:var(--uf-ember); color:#fff; box-shadow:0 4px 14px rgba(255,107,53,.3); transition:all .15s; }
.uf-btn-submit:hover { background:var(--uf-ember-deep); transform:translateY(-1px); }
.uf-btn-cancel { padding:12px 22px; border-radius:11px; font-size:13.5px; font-weight:700; text-decoration:none; background:transparent; color:var(--uf-ink-soft); border:1.5px solid var(--uf-line); transition:all .15s; }
.uf-btn-cancel:hover { border-color:var(--uf-ink); color:var(--uf-ink); }
@media(max-width:480px){
    .uf-form { padding:20px 16px; }
    .uf-actions { flex-direction:column-reverse; }
}
</style>

<div class="uf-wrap">

<div class="uf-header">
    <h2><?= $is_edit ? '✏️ ویرایش کاربر' : '➕ کاربر جدید' ?></h2>
    <a href="index.php?page=users" class="uf-btn-back">🔙 بازگشت</a>
</div>

<?php if ($error): ?>
    <?php if (strpos($error, 'crm-upgrade-box') !== false): ?>
        <?= $error ?>
    <?php else: ?>
        <div class="cf-alert-error"><?= $error ?></div>
    <?php endif; ?>
<?php endif; ?>

<form method="POST" action="index.php?page=users&action=<?= $is_edit ? 'update&id='.$id : 'create' ?>" class="uf-form">

    <!-- اطلاعات شرکت -->
    <?php if ($is_super && $is_edit): ?>
    <div class="uf-group">
        <label>🏢 نام شرکت</label>
        <input type="text" name="company_name" value="<?= crm_sanitize($edit_user['company_name'] ?? '') ?>">
    </div>
    <?php elseif ($is_super && !$is_edit): ?>
    <div class="uf-group">
        <label>🏢 نام شرکت</label>
        <input type="text" name="company_name" placeholder="اگر خالی بگذارید، از نام و نام خانوادگی همین کاربر استفاده می‌شود">
        <small class="uf-hint">این فیلد تعیین می‌کند این کاربر (و بعداً زیرمجموعه‌هایش) عضو کدام شرکت شمرده شوند.</small>
    </div>
    <?php elseif ($is_edit && !empty($edit_user['company_name'])): ?>
    <div class="uf-group">
        <label>🏢 نام شرکت</label>
        <input type="text" value="<?= crm_sanitize($edit_user['company_name'] ?? '') ?>" disabled>
        <small class="uf-hint">فقط سوپر ادمین می‌تواند نام شرکت را تغییر دهد.</small>
    </div>
    <?php endif; ?>

    <div class="uf-section-label">اطلاعات شخصی</div>

    <div class="uf-group">
        <label>👤 نام و نام خانوادگی *</label>
        <input type="text" name="full_name" required placeholder="نام و نام خانوادگی"
               value="<?= crm_sanitize($edit_user['full_name'] ?? $_POST['full_name'] ?? '') ?>">
    </div>

    <div class="uf-group">
        <label>💼 سمت سازمانی</label>
        <input type="text" name="position_title" placeholder="مثال: مدیر فروش، کارشناس ارشد..."
               value="<?= crm_sanitize($edit_user['position_title'] ?? $_POST['position_title'] ?? '') ?>">
    </div>

    <div class="uf-group">
        <label>📞 شماره تماس</label>
        <input type="tel" name="phone" placeholder="مثال: ۰۹۱۲۳۴۵۶۷۸۹"
               value="<?= crm_sanitize($edit_user['phone'] ?? $_POST['phone'] ?? '') ?>">
    </div>

    <?php if ($is_edit): ?>
    <div class="uf-group">
        <label>📱 شماره موبایل</label>
        <input type="tel" name="mobile" placeholder="09123456789"
               value="<?= crm_sanitize($edit_user['mobile'] ?? '') ?>">
    </div>
    <?php else: ?>
    <div class="uf-group">
        <label>📱 شماره موبایل *</label>
        <input type="tel" name="mobile" required placeholder="09123456789" pattern="09[0-9]{9}" maxlength="11">
    </div>
    <div class="uf-group">
        <label>🔒 رمز عبور *</label>
        <input type="password" name="password" required placeholder="حداقل ۶ کاراکتر" minlength="6">
    </div>
    <?php endif; ?>

    <div class="uf-divider"></div>
    <div class="uf-section-label">تنظیمات دسترسی</div>

    <?php if ($is_edit): ?>
    <div class="uf-group">
        <label>وضعیت کاربر</label>
        <select name="status">
            <option value="active"   <?= ($edit_user['status'] ?? 'active') === 'active'   ? 'selected' : '' ?>>🟢 فعال</option>
            <option value="inactive" <?= ($edit_user['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>🔴 غیرفعال</option>
        </select>
    </div>

    <?php if ($edit_user['id'] != $user['id']): ?>
    <div class="uf-group">
        <label>🔒 تغییر رمز عبور این کاربر</label>
        <input type="password" name="new_password" placeholder="خالی بگذارید تا رمز عوض نشود" minlength="6" autocomplete="new-password">
        <small class="uf-hint">به‌عنوان مدیر بالادستی می‌توانید رمز جدیدی برای این کاربر تعیین کنید (حداقل ۶ کاراکتر). اگر خالی بماند، رمز فعلی او تغییر نمی‌کند.</small>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <?php
    $can_change_role = false;
    if ($is_edit) {
        if ($is_super) $can_change_role = true;
        elseif ($is_admin) $can_change_role = ($edit_user['parent_id'] !== null || $edit_user['id'] == $user['id']);
        elseif ($user['role'] === 'manager') $can_change_role = ($edit_user['parent_id'] == $user['id']);
    } else {
        $can_change_role = ($is_super || $is_admin);
    }
    ?>

    <?php if ($can_change_role): ?>
    <div class="uf-group">
        <label>نقش سیستمی</label>
        <select name="role">
            <?php if ($is_super): ?>
            <option value="super_admin" <?= ($edit_user['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>👑 سوپر ادمین</option>
            <option value="admin"       <?= ($edit_user['role'] ?? '') === 'admin'       ? 'selected' : '' ?>>🛡️ مدیر ارشد</option>
            <?php endif; ?>
            <?php if ($is_super || $is_admin): ?>
            <option value="manager" <?= ($edit_user['role'] ?? '') === 'manager' ? 'selected' : '' ?>>👔 مدیر فروش</option>
            <?php endif; ?>
            <option value="agent" <?= ($edit_user['role'] ?? 'agent') === 'agent' ? 'selected' : '' ?>>📞 کارشناس فروش</option>
        </select>
        <?php if ($is_edit): ?>
        <small class="uf-hint">نقش فعلی: <?php
            $rl = ['super_admin'=>'👑 سوپر ادمین','admin'=>'🛡️ مدیر ارشد','manager'=>'👔 مدیر فروش','agent'=>'📞 کارشناس فروش'];
            echo $rl[$edit_user['role']] ?? $edit_user['role'];
        ?></small>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- انتخاب / تغییر مدیر بالادستی (پرنت) — هم موقع ایجاد، هم ویرایش -->
    <?php if ($is_admin || $is_super): ?>
        <?php
        $pdo = getDB();
        if ($is_super) {
            if ($is_edit) {
                $stmt = $pdo->prepare("SELECT id, full_name, role, company_name FROM users WHERE id != ? ORDER BY company_name, role, full_name");
                $stmt->execute([(int)$edit_user['id']]);
            } else {
                $stmt = $pdo->query("SELECT id, full_name, role, company_name FROM users ORDER BY company_name, role, full_name");
            }
        } else {
            // ادمین فقط می‌تواند خودش یا مدیران فروشی که مستقیماً زیرمجموعه‌اش هستند را به‌عنوان پرنت انتخاب کند
            if ($is_edit) {
                $stmt = $pdo->prepare("SELECT id, full_name, role, company_name FROM users WHERE (id = ? OR (parent_id = ? AND role = 'manager')) AND id != ? ORDER BY role, full_name");
                $stmt->execute([$user['id'], $user['id'], $edit_user['id']]);
            } else {
                $stmt = $pdo->prepare("SELECT id, full_name, role, company_name FROM users WHERE id = ? OR (parent_id = ? AND role = 'manager') ORDER BY role, full_name");
                $stmt->execute([$user['id'], $user['id']]);
            }
        }
        $possible_parents = $stmt->fetchAll();
        $current_parent_id = $edit_user['parent_id'] ?? null;
        ?>
        <?php if (!empty($possible_parents)): ?>
        <div class="uf-group">
            <label>👔 مدیر بالادستی <?= $is_edit ? '(Parent)' : '(زیرمجموعه چه کسی باشد؟)' ?></label>
            <select name="parent_id">
                <?php if ($is_edit): ?>
                <option value="">-- بدون مدیر (ریشه) --</option>
                <?php endif; ?>
                <?php foreach ($possible_parents as $p):
                    $p_role = ['super_admin'=>'👑 سوپر ادمین','admin'=>'🛡️ مدیر','manager'=>'👔 مدیر فروش','agent'=>'📞 کارشناس'][$p['role']] ?? '👤';
                    $is_selected = $is_edit
                        ? ($current_parent_id == $p['id'])
                        : ($p['id'] == $user['id']); // پیش‌فرض موقع ایجاد: خود کاربر جاری
                ?>
                <option value="<?= $p['id'] ?>" <?= $is_selected ? 'selected' : '' ?>>
                    <?= crm_sanitize($p['full_name']) ?> (<?= $p_role ?>)<?= $p['company_name'] ? ' — '.crm_sanitize($p['company_name']) : '' ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if ($is_edit): ?>
            <small class="uf-hint">مدیر فعلی: <?php
                if ($current_parent_id) {
                    $s2 = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
                    $s2->execute([$current_parent_id]);
                    echo crm_sanitize($s2->fetchColumn() ?: '—');
                } else { echo 'ندارد (ریشه)'; }
            ?></small>
            <?php else: ?>
            <small class="uf-hint">مثلاً اگر می‌خواهید یک کارشناس زیرمجموعه یکی از مدیران فروش‌تان باشد، همان را انتخاب کنید؛ در غیر این صورت خودتان را انتخاب کنید.</small>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="uf-actions">
        <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
        <button type="submit" class="uf-btn-submit"><?= $is_edit ? '💾 بروزرسانی' : '✅ ایجاد کاربر' ?></button>
        <a href="index.php?page=users" class="uf-btn-cancel">انصراف</a>
    </div>
</form>

</div><!-- /uf-wrap -->