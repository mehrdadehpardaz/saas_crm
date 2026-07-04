<!-- Views/plans/list.php -->

<style>
.pl-wrap {
    --pl-ink:#14213D; --pl-ink-soft:#4A5578; --pl-ember:#FF6B35; --pl-ember-deep:#E6531E;
    --pl-teal:#16A085; --pl-teal-deep:#0E8170; --pl-paper:#FAF8F5; --pl-paper2:#F2EEE6;
    --pl-line:#E5DFD3; --pl-card:#FFFFFF; --pl-blue:#1a73e8; --pl-danger:#EA4335;
    direction: rtl; 
    max-width: 720px;
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

/* وضعیت فعلی — کارت برند گرادیانی مثل داشبورد */
.pl-status {
    background:linear-gradient(135deg, var(--pl-ink) 0%, #1C2D52 100%);
    border-radius:16px; padding:22px; margin-bottom:24px; position:relative; overflow:hidden; color:#fff;
}
.pl-status::before { content:''; position:absolute; inset:0; background:radial-gradient(circle at 85% 20%, rgba(255,107,53,.25), transparent 50%); }
.pl-status-inner { position:relative; }
.pl-status-title { font-size:13px; font-weight:700; color:#C7CEE0; display:flex; align-items:center; gap:6px; margin-bottom:16px; }
.pl-status-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; }
@media(min-width:520px){ .pl-status-grid { grid-template-columns:repeat(4,1fr); } }
.pl-status-item-label { font-size:10.5px; color:#9AA4C4; margin-bottom:4px; }
.pl-status-item-val { font-size:16px; font-weight:800; color:#fff; }
.pl-status-item-val.warn { color:#FFB48A; }

/* تعرفه‌ها */
.pl-section-title { font-size:15px; font-weight:700; color:var(--pl-ink); margin-bottom:13px; display:flex; align-items:center; gap:7px; }

.pl-table-card { background:var(--pl-card); border:1px solid var(--pl-line); border-radius:14px; overflow:hidden; margin-bottom:18px; }
.pl-table { width:100%; border-collapse:collapse; font-size:13.5px; }
.pl-table th { padding:12px 14px; text-align:right; background:var(--pl-paper2); color:var(--pl-ink-soft); font-weight:600; font-size:11.5px; }
.pl-table th.center { text-align:center; }
.pl-table td { padding:13px 14px; border-bottom:1px solid var(--pl-paper2); color:var(--pl-ink); }
.pl-table tr:last-child td { border:none; }
.pl-table tr:hover td { background:var(--pl-paper2); }
.pl-plan-name { display:flex; align-items:center; gap:9px; font-weight:600; }
.pl-plan-icon { width:30px; height:30px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
.pl-price-monthly { text-align:center; font-weight:700; color:var(--pl-ink); }
.pl-price-yearly { text-align:center; font-weight:700; color:var(--pl-teal-deep); }
.pl-price-yearly .save-tag { display:block; font-size:9.5px; font-weight:600; color:var(--pl-teal); margin-top:2px; }

/* خرید CTA */
.pl-cta {
    background:var(--pl-card); border:1.5px solid var(--pl-ember); border-radius:16px;
    padding:28px 24px; text-align:center; position:relative; overflow:hidden;
}
.pl-cta::before {
    content:''; position:absolute; inset:0; background:radial-gradient(circle at 20% 0%, rgba(255,107,53,.06), transparent 60%);
}
.pl-cta-inner { position:relative; }
.pl-cta p { font-size:14px; color:var(--pl-ink-soft); margin-bottom:18px; }
.pl-cta-btn {
    display:inline-flex; align-items:center; gap:8px; padding:13px 30px; border-radius:11px;
    font-size:14.5px; font-weight:700; text-decoration:none; background:var(--pl-ember); color:#fff;
    box-shadow:0 5px 18px rgba(255,107,53,.32); transition:all .18s;
}
.pl-cta-btn:hover { background:var(--pl-ember-deep); transform:translateY(-2px); box-shadow:0 8px 24px rgba(255,107,53,.4); }
</style>

<div class="pl-wrap">

<div class="pl-header">
    <h2>💳 پلن‌ها و اشتراک</h2>
    <a href="index.php?page=dashboard" class="pl-btn-back">🔙 داشبورد</a>
</div>

<?php if ($message === 'purchased'): ?>
    <div class="pl-alert">✅ اشتراک با موفقیت خریداری/تمدید شد.</div>
<?php endif; ?>

<!-- وضعیت فعلی -->
<div class="pl-status">
    <div class="pl-status-inner">
        <div class="pl-status-title">📊 وضعیت فعلی اشتراک</div>
        <div class="pl-status-grid">
            <div>
                <div class="pl-status-item-label">🏢 شرکت</div>
                <div class="pl-status-item-val"><?= crm_sanitize($user['company_name'] ?? 'ندارد') ?></div>
            </div>
            <div>
                <div class="pl-status-item-label">👥 سقف کاربران</div>
                <div class="pl-status-item-val"><?= $user['max_users_limit'] ?> نفر</div>
            </div>
            <?php
            $days_left = max(0, ceil((strtotime($user['plan_expiry']) - time()) / 86400));
            ?>
            <div>
                <div class="pl-status-item-label">⏰ پایان اشتراک</div>
                <div class="pl-status-item-val <?= $days_left <= 3 ? 'warn' : '' ?>">
                    <?= function_exists('jdate') ? jdate($user['plan_expiry']) : date('Y/m/d', strtotime($user['plan_expiry'])) ?>
                </div>
            </div>
            <div>
                <div class="pl-status-item-label">💰 اعتبار</div>
                <div class="pl-status-item-val"><?= number_format($user['credit'] ?? 0) ?> ت</div>
            </div>
        </div>
    </div>
</div>

<!-- قیمت‌ها -->
<div class="pl-section-title">📋 تعرفه‌ها</div>

<div class="pl-table-card">
    <table class="pl-table">
        <thead>
            <tr>
                <th>شرح</th>
                <th class="center">ماهانه</th>
                <th class="center">سالانه</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($plans as $plan):
                $is_base = $plan['type'] === 'base';
                $monthly_total = $plan['price_monthly'] * 12;
                $yearly_saving = $monthly_total > 0 ? round((1 - $plan['price_yearly'] / $monthly_total) * 100) : 0;
            ?>
            <tr>
                <td>
                    <div class="pl-plan-name">
                        <span class="pl-plan-icon" style="background:<?= $is_base ? '#FFF1EA' : '#E8F0FE' ?>; color:<?= $is_base ? 'var(--pl-ember-deep)' : 'var(--pl-blue)' ?>">
                            <?= $is_base ? '🏠' : '👤' ?>
                        </span>
                        <?= crm_sanitize($plan['name']) ?>
                    </div>
                </td>
                <td class="pl-price-monthly"><?= number_format($plan['price_monthly']) ?> ت</td>
                <td class="pl-price-yearly">
                    <?= number_format($plan['price_yearly']) ?> ت
                    <?php if ($yearly_saving > 0): ?>
                    <span class="save-tag">صرفه‌جویی <?= $yearly_saving ?>٪</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- خرید -->
<div class="pl-cta">
    <div class="pl-cta-inner">
        <p>🛒 برای خرید یا تمدید اشتراک اقدام کنید</p>
        <a href="index.php?page=plans&action=buy" class="pl-cta-btn">🛒 خرید / تمدید اشتراک</a>
    </div>
</div>

</div><!-- /pl-wrap -->