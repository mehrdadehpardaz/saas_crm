<!-- Views/plans/list.php -->

<style>
.pl-wrap {
    --pl-ink:#14213D; --pl-ink-soft:#4A5578; --pl-ember:#FF6B35; --pl-ember-deep:#E6531E;
    --pl-teal:#16A085; --pl-teal-deep:#0E8170; --pl-paper:#FAF8F5; --pl-paper2:#F2EEE6;
    --pl-line:#E5DFD3; --pl-card:#FFFFFF; --pl-blue:#1a73e8; --pl-danger:#EA4335;
    direction: rtl;
    max-width: 980px;
    margin: 0 auto;
    width: 100%;
}

.pl-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.pl-header h2 { font-size:18px; font-weight:800; color:var(--pl-ink); letter-spacing:-.01em; }
.pl-btn-back {
    display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:9px;
    font-size:12.5px; font-weight:700; text-decoration:none; background:var(--pl-card);
    color:var(--pl-ink-soft); border:1.5px solid var(--pl-line); transition:all .15s;
}
.pl-btn-back:hover { border-color:var(--pl-ink); color:var(--pl-ink); }

.pl-alert { padding:11px 16px; border-radius:10px; font-size:13px; margin-bottom:18px; background:#E7F7F3; color:var(--pl-teal-deep); border:1px solid #B8E5DA; }
.pl-alert-error { padding:11px 16px; border-radius:10px; font-size:13px; margin-bottom:18px; background:#FCE8E6; color:#C0392B; border:1px solid #F5C6CB; }

/* وضعیت فعلی */
.pl-status {
    background:linear-gradient(135deg, var(--pl-ink) 0%, #1C2D52 100%);
    border-radius:16px; padding:22px; margin-bottom:22px; position:relative; overflow:hidden; color:#fff;
}
.pl-status::before { content:''; position:absolute; inset:0; background:radial-gradient(circle at 85% 20%, rgba(255,107,53,.25), transparent 50%); }
.pl-status-inner { position:relative; }
.pl-status-title { font-size:13px; font-weight:700; color:#C7CEE0; display:flex; align-items:center; gap:6px; margin-bottom:16px; }
.pl-status-badge { font-size:11px; font-weight:800; padding:3px 12px; border-radius:20px; background:rgba(255,107,53,.18); color:#FFB48A; margin-right:8px; }

.pl-usage-grid { display:grid; grid-template-columns:repeat(1,1fr); gap:14px; margin-top:8px; }
@media(min-width:640px){ .pl-usage-grid { grid-template-columns:repeat(3,1fr); } }
.pl-usage-item { background:rgba(255,255,255,.06); border-radius:11px; padding:12px 14px; }
.pl-usage-label { font-size:11px; color:#9AA4C4; margin-bottom:6px; display:flex; justify-content:space-between; }
.pl-usage-num { font-size:13px; font-weight:800; color:#fff; }
.pl-usage-bar-wrap { height:6px; background:rgba(255,255,255,.15); border-radius:4px; overflow:hidden; }
.pl-usage-bar-fill { height:100%; border-radius:4px; }
.pl-usage-full { color:#FFB48A !important; }

/* تعرفه‌ها */
.pl-section-title { font-size:15px; font-weight:700; color:var(--pl-ink); margin:6px 0 14px; display:flex; align-items:center; gap:7px; }

.pl-tier-toggle {
    display:grid; grid-template-columns:1fr 1fr; align-items:stretch; background:var(--pl-paper2);
    border-radius:30px; padding:4px; margin:0 auto 22px; position:relative; width:100%; max-width:300px;
}
.pl-pt-btn {
    display:flex; align-items:center; justify-content:center; gap:5px; padding:9px 10px; border-radius:26px;
    font-size:13px; font-weight:700; border:none; background:transparent; color:var(--pl-ink-soft);
    cursor:pointer; position:relative; z-index:1; transition:color .2s;
}
.pl-pt-btn.active { color:#fff; }
.pl-pt-slider {
    position:absolute; top:4px; bottom:4px; right:4px; width:calc(50% - 4px);
    background:var(--pl-ink); border-radius:26px; transition:transform .25s cubic-bezier(.4,0,.2,1); z-index:0;
}
.pl-pt-slider.yearly { transform:translateX(calc(-100% - 0px)); }
.pl-pt-save { font-size:9.5px; background:var(--pl-teal); color:#fff; padding:2px 6px; border-radius:8px; }

.pl-tier-grid { display:grid; grid-template-columns:1fr; gap:16px; }
@media(min-width:760px){ .pl-tier-grid { grid-template-columns:repeat(3,1fr); } }

.pl-tier-card {
    background:var(--pl-card); border:1.5px solid var(--pl-line); border-radius:16px;
    padding:24px 20px; position:relative; display:flex; flex-direction:column;
}
.pl-tier-card.current { border-color:var(--pl-teal); box-shadow:0 8px 26px rgba(22,160,133,.14); }
.pl-tier-card.recommended { border-color:var(--pl-ember); box-shadow:0 8px 26px rgba(255,107,53,.16); }
.pl-tier-tag {
    position:absolute; top:-12px; right:20px; font-size:10.5px; font-weight:800; padding:4px 13px; border-radius:20px; color:#fff;
}
.pl-tier-tag.cur { background:var(--pl-teal); }
.pl-tier-tag.rec { background:var(--pl-ember); }

.pl-tier-card h3 { font-size:16px; font-weight:800; color:var(--pl-ink); margin-bottom:6px; }
.pl-tier-price { display:flex; align-items:baseline; gap:6px; margin-bottom:2px; }
.pl-tier-price .num { font-size:26px; font-weight:800; color:var(--pl-ink); }
.pl-tier-price .unit { font-size:12px; color:var(--pl-ink-soft); }
.pl-tier-period-note { font-size:11px; color:var(--pl-ink-soft); margin-bottom:16px; }

.pl-tier-feats { list-style:none; margin-bottom:20px; flex:1; }
.pl-tier-feats li { display:flex; align-items:flex-start; gap:8px; font-size:12.5px; color:var(--pl-ink-soft); padding:6px 0; }
.pl-tier-feats li::before { content:'✓'; color:var(--pl-teal-deep); font-weight:800; flex-shrink:0; }

.pl-tier-btn {
    width:100%; padding:11px 18px; border-radius:10px; font-size:13.5px; font-weight:700; border:none;
    cursor:pointer; text-align:center; transition:all .18s; text-decoration:none; display:block;
}
.pl-tier-btn.primary { background:var(--pl-ember); color:#fff; box-shadow:0 4px 14px rgba(255,107,53,.28); }
.pl-tier-btn.primary:hover { background:var(--pl-ember-deep); transform:translateY(-1px); }
.pl-tier-btn.disabled { background:var(--pl-paper2); color:var(--pl-ink-soft); cursor:default; }
</style>

<div class="pl-wrap">

<div class="pl-header">
    <h2>💳 پلن‌ها و اشتراک</h2>
    <a href="index.php?page=dashboard" class="pl-btn-back">🔙 داشبورد</a>
</div>

<?php if ($message === 'purchased'): ?>
    <div class="pl-alert">✅ اشتراک با موفقیت خریداری/ارتقا یافت.</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="pl-alert-error"><?= $error ?></div>
<?php endif; ?>

<!-- وضعیت فعلی -->
<div class="pl-status">
    <div class="pl-status-inner">
        <div class="pl-status-title">
            📊 وضعیت فعلی اشتراک
            <span class="pl-status-badge"><?= $current_tier ? crm_sanitize($current_tier['name']) : 'بدون پلن مشخص' ?></span>
        </div>

        <div class="pl-usage-grid">
            <?php
            $usage_rows = [
                ['label' => '👥 کاربران',  'key' => 'users'],
                ['label' => '🏢 مشتریان',  'key' => 'customers'],
                ['label' => '👤 مخاطبین',  'key' => 'contacts'],
            ];
            foreach ($usage_rows as $row):
                $u = $usage[$row['key']];
                $is_unlimited = ($u['max'] === null);
                $pct = (!$is_unlimited && $u['max'] > 0) ? min(100, round($u['current'] / $u['max'] * 100)) : 0;
                $near_full = !$is_unlimited && $pct >= 90;
            ?>
            <div class="pl-usage-item">
                <div class="pl-usage-label">
                    <span><?= $row['label'] ?></span>
                    <span class="pl-usage-num <?= $near_full ? 'pl-usage-full' : '' ?>">
                        <?= $is_unlimited ? 'نامحدود' : (($u['current'] ?? '—') . ' / ' . $u['max']) ?>
                    </span>
                </div>
                <?php if (!$is_unlimited): ?>
                <div class="pl-usage-bar-wrap">
                    <div class="pl-usage-bar-fill" style="width:<?= $pct ?>%; background:<?= $near_full ? '#EA4335' : 'var(--pl-ember,#FF6B35)' ?>"></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- سه تعرفه -->
<div class="pl-section-title">📋 پلن‌ها</div>

<div class="pl-tier-toggle" id="tierToggle">
    <div class="pl-pt-slider" id="tierSlider"></div>
    <button type="button" class="pl-pt-btn active" id="btnMonthly" onclick="setTierBilling('monthly')">ماهانه</button>
    <button type="button" class="pl-pt-btn" id="btnYearly" onclick="setTierBilling('yearly')">سالانه <span class="pl-pt-save">۲ ماه رایگان</span></button>
</div>

<div class="pl-tier-grid">
    <?php foreach ($tiers as $t):
        $is_current = $current_tier && (int)$current_tier['id'] === (int)$t['id'];
        $is_recommended = ($t['slug'] === 'pro');
        $card_class = $is_current ? 'current' : ($is_recommended ? 'recommended' : '');
        $yearly_price = $t['price_monthly'] * 10;
    ?>
    <div class="pl-tier-card <?= $card_class ?>">
        <?php if ($is_current): ?>
        <span class="pl-tier-tag cur">پلن فعلی شما</span>
        <?php elseif ($is_recommended): ?>
        <span class="pl-tier-tag rec">پیشنهادی</span>
        <?php endif; ?>

        <h3><?= crm_sanitize($t['name']) ?></h3>
        <?php if ((int)$t['price_monthly'] === 0): ?>
        <div class="pl-tier-price">
            <span class="num" style="color:var(--pl-teal-deep)">رایگان</span>
        </div>
        <div class="pl-tier-period-note">بدون نیاز به پرداخت</div>
        <?php else: ?>
        <div class="pl-tier-price">
            <span class="num tier-price" data-monthly="<?= number_format($t['price_monthly']) ?>" data-yearly="<?= number_format(round($yearly_price/12)) ?>">
                <?= number_format($t['price_monthly']) ?>
            </span>
            <span class="unit">تومان / ماه</span>
        </div>
        <div class="pl-tier-period-note">پرداخت ماهانه — جمع سالانه <span class="tier-yearly-total" data-total="<?= number_format($yearly_price) ?>"><?= number_format($t['price_monthly'] * 12) ?></span> تومان</div>
        <?php endif; ?>

        <ul class="pl-tier-feats">
            <li><?= $t['max_users'] !== null ? (int)$t['max_users'] . ' کاربر' : 'کاربر نامحدود' ?></li>
            <li><?= $t['max_customers'] !== null ? number_format($t['max_customers']) . ' مشتری' : 'مشتری نامحدود' ?></li>
            <li><?= $t['max_contacts'] !== null ? number_format($t['max_contacts']) . ' مخاطب' : 'مخاطب نامحدود' ?></li>
            <li><?= $t['backup_access'] === 'unlimited' ? 'بکاپ‌گیری نامحدود' : 'یک بکاپ رایگان در ماه' ?></li>
            <?php if ($t['management_reports']): ?><li>گزارش‌گیری مدیریتی (تیم و کاربران)</li><?php endif; ?>
            <?php if ($t['full_access']): ?><li>دسترسی کامل و بدون محدودیت</li><?php endif; ?>
        </ul>

        <?php if ($is_current): ?>
            <span class="pl-tier-btn disabled">پلن فعلی شماست</span>
        <?php else: ?>
            <form method="POST" action="index.php?page=plans&action=select" class="tier-select-form">
                <input type="hidden" name="tier_id" value="<?= $t['id'] ?>">
                <input type="hidden" name="period" class="tier-period-input" value="monthly">
                <?php include __DIR__ . '/../../includes/csrf_field.php'; ?>
                <button type="submit" class="pl-tier-btn primary">
                    <?= $current_tier ? 'ارتقا به این پلن' : 'انتخاب این پلن' ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($is_super): ?>
<div style="text-align:center;margin-top:22px">
    <a href="index.php?page=plans&action=tiers" style="font-size:12.5px;color:var(--pl-ink-soft);text-decoration:underline">⚙️ ویرایش قیمت و سقف پلن‌ها (سوپر ادمین)</a>
</div>
<?php endif; ?>

</div><!-- /pl-wrap -->

<script>
function setTierBilling(mode) {
    var slider = document.getElementById('tierSlider');
    var btnM = document.getElementById('btnMonthly');
    var btnY = document.getElementById('btnYearly');
    var nums = document.querySelectorAll('.tier-price');
    var periodInputs = document.querySelectorAll('.tier-period-input');

    var yearly = (mode === 'yearly');
    slider.classList.toggle('yearly', yearly);
    btnY.classList.toggle('active', yearly);
    btnM.classList.toggle('active', !yearly);

    nums.forEach(function (n) {
        n.textContent = yearly ? n.getAttribute('data-yearly') : n.getAttribute('data-monthly');
    });
    periodInputs.forEach(function (i) { i.value = yearly ? 'yearly' : 'monthly'; });
}
</script>