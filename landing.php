<?php
// landing.php — صفحه اصلی / لندینگ پیج «پیگیر»
// مسیر پیشنهادی: ریشه پروژه یا controllers/LandingController.php + views/landing.php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

// اگر لاگین است، اطلاعات کاربر را برای نمایش دکمه‌های متفاوت نگه دار
$is_logged_in = crm_is_logged_in();
$nav_user = $is_logged_in ? crm_get_current_user() : null;

$pdo = getDB();

// ── خواندن پلن‌ها از دیتابیس ──
$plans_raw = $pdo->query("SELECT * FROM plans ORDER BY price_monthly ASC")->fetchAll(PDO::FETCH_ASSOC);

$base_plan = null;
$per_user_plan = null;
foreach ($plans_raw as $p) {
    if ($p['type'] === 'base' && !$base_plan) $base_plan = $p;
    if ($p['type'] === 'per_user' && !$per_user_plan) $per_user_plan = $p;
}

// fallback اگر دیتابیس خالی بود
$base_plan = $base_plan ?? ['price_monthly' => 0, 'price_yearly' => 0, 'name' => 'پایه'];
$per_user_plan = $per_user_plan ?? ['price_monthly' => 0, 'price_yearly' => 0, 'name' => 'کاربر اضافه'];

function toman($n) {
    return number_format((float)$n);
}

// ── پیام خطای لاگین (اگر از فرم برگشته) ──
$login_error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
$open_login_modal = !empty($login_error) || (($_GET['action'] ?? '') === 'login');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>پیگیریو — نرم‌افزار مدیریت ارتباط با مشتری برای کسب‌وکارهای کوچک</title>
<meta name="description" content="پیگیریو، CRM ساده و فارسی برای تیم‌های فروش کوچک. مشتری بساز، براش فرصت فروش تعریف کن، فعالیت‌ها رو زیرش ثبت کن — هیچ فالوآپی رو از دست نده. ۱۴ روز رایگان.">
<meta name="keywords" content="نرم افزار CRM, مدیریت مشتری, پیگیریوی فروش, نرم افزار فروش, سی آر ام فارسی, مدیریت فرصت فروش">
<link rel="canonical" href="https://paygiro.ir/">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="پیگیریو — هیچ مشتری‌ای رو فراموش نکن">
<meta property="og:description" content="CRM ساده فارسی برای تیم‌های فروش کوچک. ۱۴ روز رایگان، بدون نیاز به کارت بانکی.">
<meta property="og:url" content="https://paygiro.ir/">
<meta property="og:locale" content="fa_IR">
<meta property="og:image" content="https://paygiro.ir/assets/img/og-cover.png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="پیگیریو — هیچ مشتری‌ای رو فراموش نکن">
<meta name="twitter:description" content="CRM ساده فارسی برای تیم‌های فروش کوچک. ۱۴ روز رایگان، بدون نیاز به کارت بانکی.">
<meta name="twitter:image" content="https://paygiro.ir/assets/img/og-cover.png">

<!-- Schema.org -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "پیگیریو",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web",
  "description": "نرم‌افزار مدیریت ارتباط با مشتری (CRM) فارسی برای کسب‌وکارهای کوچک",
  "offers": {
    "@type": "Offer",
    "price": "<?= (float)$base_plan['price_monthly'] ?>",
    "priceCurrency": "IRR"
  }
}
</script>

<!-- Schema.org — FAQPage -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "چرا اول باید مشتری بسازم، بعد فرصت فروش، بعد فعالیت؟",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "چون هر کدوم یه لایه‌ست: مشتری یعنی «این شرکت رو می‌شناسم»، فرصت فروش یعنی «این هدف مشخص رو دنبال می‌کنم» (مثل فروش یه محصول خاص)، و فعالیت یعنی «این کاری بود که همین الان انجام دادم». بدون مشتری، فرصت فروشی وجود نداره؛ بدون فرصت فروش، معلوم نیست فعالیت‌هات به کجا ختم میشن."
      }
    },
    {
      "@type": "Question",
      "name": "آیا واقعاً ۱۴ روز رایگانه؟",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "بله، کاملاً. بدون نیاز به کارت بانکی ثبت‌نام می‌کنید و ۱۴ روز کامل با سقف ۵ کاربر رایگان استفاده می‌کنید."
      }
    },
    {
      "@type": "Question",
      "name": "بعد از اتمام دوره رایگان چی میشه؟",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "داده‌های شما حذف نمی‌شود. می‌توانید اطلاعات‌تان را ببینید، اما برای ادامه کار باید پلن مناسب را فعال کنید."
      }
    },
    {
      "@type": "Question",
      "name": "آیا می‌توانم بعداً کاربر اضافه کنم؟",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "بله، هر زمان می‌توانید از پنل مدیریت کاربر جدید اضافه یا حذف کنید و هزینه به‌صورت نسبی محاسبه می‌شود."
      }
    },
    {
      "@type": "Question",
      "name": "گزارش‌ها چه چیزی رو نشون میدن؟",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "سه سطح گزارش دارید: گزارش شخصی خودتون، گزارش مقایسه‌ای کاربران زیرمجموعه، و گزارش مقایسه تیم‌های مدیران — با نمودار روند روزانه، نرخ تکمیل فرصت فروش و سهم هر نفر از مشتری و فعالیت."
      }
    },
    {
      "@type": "Question",
      "name": "داده‌هایم امن است؟",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "هر کاربر فقط به داده خودش و زیرمجموعه‌هایش دسترسی دارد. رمزها رمزنگاری شده و دسترسی‌ها به‌صورت سلسله‌مراتبی کنترل می‌شود."
      }
    }
  ]
}
</script>

<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
<style>
/* ════════════════════════════════════════
   PAYGIRO LANDING — design tokens
   ════════════════════════════════════════ */
:root{
  --ink:        #14213D;   /* آبی تیره اعتماد — متن اصلی */
  --ink-soft:   #4A5578;
  --paper:      #FAF8F5;   /* زمینه گرم */
  --paper-2:    #F2EEE6;
  --ember:      #FF6B35;   /* نارنجی پیگیری — برند */
  --ember-deep: #E6531E;
  --teal:       #16A085;   /* سبز تایید/CTA موفقیت */
  --teal-deep:  #0E8170;
  --blue:       #1a73e8;
  --line:       #E5DFD3;
  --card:       #FFFFFF;
  --radius:     14px;
  --shadow:     0 8px 30px rgba(20,33,61,.08);
  --shadow-lg:  0 20px 60px rgba(20,33,61,.14);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
  font-family:'Vazirmatn','Segoe UI',Tahoma,sans-serif;
  background:var(--paper);
  color:var(--ink);
  direction:rtl;
  line-height:1.7;
  -webkit-font-smoothing:antialiased;
  overflow-x:hidden;
}
a{color:inherit;text-decoration:none}
img{max-width:100%;display:block}
.container{max-width:1140px;margin:0 auto;padding:0 20px}

/* ── focus visibility ── */
a:focus-visible,button:focus-visible,input:focus-visible{
  outline:2px solid var(--ember);outline-offset:2px;
}
@media(prefers-reduced-motion:reduce){
  *{animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important;scroll-behavior:auto!important}
}

/* ════════ NAVBAR ════════ */
.nav{
  position:sticky;top:0;z-index:300;
  background:rgba(250,248,245,.85);
  backdrop-filter:blur(10px);
  border-bottom:1px solid var(--line);
}
.nav-inner{display:flex;align-items:center;justify-content:space-between;height:64px}
.nav-logo{display:flex;align-items:center;gap:8px;font-weight:800;font-size:19px;color:var(--ink)}
.nav-logo-mark{
  width:30px;height:30px;border-radius:9px;
  background:linear-gradient(135deg,var(--ember),var(--ember-deep));
  display:flex;align-items:center;justify-content:center;color:#fff;font-size:15px;font-weight:900;
  flex-shrink:0;
}
.nav-links{display:none;gap:26px;font-size:14px;color:var(--ink-soft);font-weight:500}
.nav-links a:hover{color:var(--ink)}
@media(min-width:900px){.nav-links{display:flex}}
.nav-actions{display:flex;align-items:center;gap:10px}
.btn{
  display:inline-flex;align-items:center;justify-content:center;gap:6px;
  padding:10px 20px;border-radius:10px;font-size:14px;font-weight:700;
  border:none;cursor:pointer;transition:transform .15s,box-shadow .15s,background .15s;
  white-space:nowrap;
}
.btn-ghost{background:transparent;color:var(--ink);border:1.5px solid var(--line)}
.btn-ghost:hover{border-color:var(--ink);background:var(--paper-2)}
.btn-primary{background:var(--ember);color:#fff;box-shadow:0 4px 14px rgba(255,107,53,.32)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(255,107,53,.4);background:var(--ember-deep)}
.btn-lg{padding:14px 28px;font-size:15px;border-radius:12px}
.btn-block{width:100%}

/* ════════ HERO ════════ */
.hero{position:relative;padding:72px 0 60px;overflow:hidden}
.hero-grid{display:grid;grid-template-columns:1fr;gap:40px;align-items:center}
@media(min-width:960px){.hero-grid{grid-template-columns:1.05fr .95fr;gap:24px}}

.eyebrow{
  display:inline-flex;align-items:center;gap:7px;
  background:#FFF1EA;color:var(--ember-deep);
  border:1px solid #FFD9C2;
  padding:6px 14px;border-radius:30px;font-size:12.5px;font-weight:700;
  margin-bottom:20px;
}
.eyebrow-dot{width:7px;height:7px;border-radius:50%;background:var(--ember);animation:pulse-dot 2s infinite}
@keyframes pulse-dot{0%,100%{opacity:1}50%{opacity:.35}}

.hero h1{
  font-size:clamp(30px,5.4vw,48px);
  font-weight:800;line-height:1.28;letter-spacing:-.02em;
  color:var(--ink);margin-bottom:18px;
}
.hero h1 em{
  font-style:normal;color:var(--ember);position:relative;
}
.hero-sub{font-size:17px;color:var(--ink-soft);max-width:480px;margin-bottom:30px}
.hero-ctas{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:22px}
.hero-trust{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--ink-soft)}
.hero-trust b{color:var(--teal-deep)}

/* ── Signature visual: پیگیری تایم‌لاین ── */
.hero-visual{position:relative}
.timeline-card{
  background:var(--card);border:1px solid var(--line);border-radius:18px;
  box-shadow:var(--shadow-lg);padding:24px 20px;
  position:relative;
}
.tl-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
.tl-header-title{font-size:13px;font-weight:700;color:var(--ink-soft)}
.tl-header-badge{
  font-size:11px;font-weight:700;color:var(--teal-deep);background:#E7F7F3;
  padding:3px 10px;border-radius:20px;
}
.tl-row{display:flex;align-items:flex-start;gap:12px;position:relative;padding-bottom:22px}
.tl-row:last-child{padding-bottom:0}
.tl-row::before{
  content:'';position:absolute;right:13px;top:28px;bottom:0;width:2px;
  background:var(--line);
}
.tl-row:last-child::before{display:none}
.tl-dot{
  width:28px;height:28px;border-radius:50%;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;font-size:13px;
  border:2px solid var(--card);position:relative;z-index:1;
}
.tl-row[data-stage="call"] .tl-dot{background:#E8F0FE;color:#1a73e8}
.tl-row[data-stage="meeting"] .tl-dot{background:#FFF3DD;color:#E6951E}
.tl-row[data-stage="task"] .tl-dot{background:#FFF1EA;color:var(--ember-deep)}
.tl-row[data-stage="won"] .tl-dot{background:#E7F7F3;color:var(--teal-deep)}
.tl-content{flex:1;padding-top:2px}
.tl-title{font-size:13.5px;font-weight:700;color:var(--ink)}
.tl-meta{font-size:11.5px;color:var(--ink-soft);margin-top:2px}
.tl-time{font-size:10.5px;color:#A8A295;margin-right:auto;white-space:nowrap}
.tl-row-inner{display:flex;justify-content:space-between;width:100%;gap:8px}

.float-badge{
  position:absolute;background:var(--card);border:1px solid var(--line);
  border-radius:12px;box-shadow:var(--shadow);padding:10px 14px;
  font-size:12px;font-weight:700;display:flex;align-items:center;gap:8px;
}
.float-badge-1{top:-14px;left:-10px;color:var(--teal-deep)}
.float-badge-2{bottom:-16px;right:-12px;color:var(--ember-deep)}

@media(max-width:540px){
  .float-badge{display:none}
}

/* ════════ LOGO STRIP ════════ */
.strip{padding:26px 0;border-top:1px solid var(--line);border-bottom:1px solid var(--line)}
.strip-inner{display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap;font-size:13px;color:var(--ink-soft)}
.strip-tags{display:flex;gap:10px;flex-wrap:wrap;justify-content:center}
.strip-tag{background:var(--paper-2);padding:6px 14px;border-radius:20px;font-weight:600}

/* ════════ SECTION shared ════════ */
.section{padding:80px 0}
.section-head{max-width:640px;margin:0 auto 48px;text-align:center}
.section-eyebrow{font-size:12.5px;font-weight:800;color:var(--ember-deep);letter-spacing:.04em;margin-bottom:10px;text-transform:uppercase}
.section-title{font-size:clamp(24px,3.6vw,34px);font-weight:800;color:var(--ink);letter-spacing:-.01em;margin-bottom:12px}
.section-desc{font-size:15.5px;color:var(--ink-soft)}

/* ════════ WORKFLOW MINI (مشتری → فرصت فروش → فعالیت) ════════ */
.flow-section{background:var(--paper-2)}
.flow-mini{
  display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap;
  max-width:640px;margin:0 auto;
}
.flow-mini-item{display:flex;flex-direction:column;align-items:center;gap:11px;min-width:108px}
.flow-mini-icon{
  width:68px;height:68px;border-radius:20px;display:flex;align-items:center;justify-content:center;
  font-size:30px;box-shadow:var(--shadow);
}
.flow-mini-item span{font-size:13.5px;font-weight:700;color:var(--ink)}
.flow-mini-arrow{font-size:22px;color:var(--ink-soft);flex-shrink:0}
@media(max-width:560px){.flow-mini-arrow{transform:rotate(90deg)}}
.flow-mini-cta{text-align:center;margin-top:30px}

/* ════════ VS EXCEL/PAPER — BANNER ════════ */
.vs-banner{
  display:flex;align-items:center;gap:20px;flex-wrap:wrap;
  background:var(--card);border:1px solid var(--line);border-radius:18px;
  padding:22px 26px;max-width:900px;margin:0 auto;
}
.vs-banner-icon{font-size:32px;flex-shrink:0}
.vs-banner-text{flex:1;min-width:220px}
.vs-banner-text h3{font-size:15.5px;font-weight:800;color:var(--ink);margin-bottom:3px}
.vs-banner-text p{font-size:12.5px;color:var(--ink-soft)}

/* ════════ FEATURES ════════ */
.feat-grid{display:grid;grid-template-columns:1fr;gap:16px}
@media(min-width:700px){.feat-grid{grid-template-columns:repeat(2,1fr)}}
@media(min-width:1020px){.feat-grid{grid-template-columns:repeat(3,1fr)}}
.feat-card{
  background:var(--card);border:1px solid var(--line);border-radius:var(--radius);
  padding:26px 22px;transition:transform .2s,box-shadow .2s,border-color .2s;
}
.feat-card:hover{transform:translateY(-4px);box-shadow:var(--shadow);border-color:#D8CFC0}
.feat-icon{
  width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;
  font-size:19px;margin-bottom:14px;
}
.feat-card h3{font-size:16px;font-weight:700;margin-bottom:7px;color:var(--ink)}
.feat-card p{font-size:13.5px;color:var(--ink-soft)}

/* ════════ TEAM & REPORTS (فشرده) ════════ */
.tr-grid{display:grid;grid-template-columns:1fr;gap:22px}
@media(min-width:900px){.tr-grid{grid-template-columns:1fr 1fr}}
.tr-card{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:26px 24px;box-shadow:var(--shadow)}
.tr-card-head{display:flex;align-items:center;gap:11px;margin-bottom:16px}
.tr-card-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.tr-card-head h3{font-size:16.5px;font-weight:800;color:var(--ink)}
.tr-caption{font-size:12.5px;color:var(--ink-soft);margin-top:14px;text-align:center}

/* org chart mock */
.org-chart{display:flex;flex-direction:column;align-items:center;gap:0;padding:12px 0 4px}
.org-node{
  background:var(--paper);border:1.5px solid var(--line);border-radius:10px;
  padding:9px 16px;font-size:12.5px;font-weight:700;color:var(--ink);
  display:flex;align-items:center;gap:7px;
}
.org-node.admin{background:#E8F0FE;border-color:#C9DCFB;color:var(--blue)}
.org-node.manager{background:#FFF1EA;border-color:#FFD9C2;color:var(--ember-deep)}
.org-node.agent{background:#E7F7F3;border-color:#BEE7DC;color:var(--teal-deep);font-weight:600}
.org-connector{width:2px;height:18px;background:var(--line)}
.org-row{display:flex;gap:14px;flex-wrap:wrap;justify-content:center}
.org-branch{display:flex;flex-direction:column;align-items:center;gap:0}
.org-agents{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;justify-content:center}

/* report mock */
.rpt-mock-bars{display:flex;align-items:flex-end;gap:7px;height:88px;padding:0 4px;margin-bottom:14px}
.rpt-mock-bar{flex:1;border-radius:5px 5px 2px 2px;background:var(--ember);opacity:.85}
.rpt-mock-legend{display:flex;gap:14px;flex-wrap:wrap;font-size:11px;color:var(--ink-soft);margin-bottom:16px}
.rpt-mock-legend span{display:flex;align-items:center;gap:5px}
.rpt-mock-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}

/* ════════ PRICING ════════ */
.pricing-toggle{
  display:grid;grid-template-columns:1fr 1fr;align-items:stretch;background:var(--paper-2);border-radius:30px;
  padding:4px;margin:0 auto 36px;position:relative;width:100%;max-width:340px;
}
.pt-btn{
  display:flex;align-items:center;justify-content:center;gap:6px;
  padding:9px 14px;border-radius:26px;font-size:13.5px;font-weight:700;
  border:none;background:transparent;color:var(--ink-soft);cursor:pointer;
  position:relative;z-index:1;transition:color .2s;white-space:nowrap;
}
.pt-btn.active{color:#fff}
.pt-slider{
  position:absolute;top:4px;bottom:4px;right:4px;width:calc(50% - 4px);
  background:var(--ink);border-radius:26px;transition:transform .25s cubic-bezier(.4,0,.2,1);
  z-index:0;
}
.pt-slider.yearly{transform:translateX(calc(-100% - 0px))}
.pt-save{
  font-size:10px;background:var(--teal);color:#fff;padding:2px 6px;border-radius:8px;
  white-space:nowrap;flex-shrink:0;
}
@media(max-width:380px){
  .pricing-toggle{max-width:300px}
  .pt-btn{padding:9px 8px;font-size:12.5px;gap:4px}
  .pt-save{font-size:9px;padding:2px 5px}
}

.price-grid{display:grid;grid-template-columns:1fr;gap:18px;max-width:800px;margin:0 auto}
@media(min-width:760px){.price-grid{grid-template-columns:1fr 1fr}}
.price-card{
  background:var(--card);border:1.5px solid var(--line);border-radius:18px;
  padding:32px 28px;position:relative;
}
.price-card.highlight{
  border-color:var(--ember);box-shadow:0 10px 40px rgba(255,107,53,.16);
}
.price-tag{
  position:absolute;top:-12px;right:28px;background:var(--ember);color:#fff;
  font-size:11px;font-weight:800;padding:4px 14px;border-radius:20px;
}
.price-card h3{font-size:16px;font-weight:700;margin-bottom:6px}
.price-card .price-desc{font-size:13px;color:var(--ink-soft);margin-bottom:20px}
.price-amount{display:flex;align-items:baseline;gap:6px;margin-bottom:4px}
.price-num{font-size:36px;font-weight:800;color:var(--ink);font-variant-numeric:tabular-nums}
.price-unit{font-size:13px;color:var(--ink-soft)}
.price-period{font-size:12.5px;color:var(--ink-soft);margin-bottom:22px}
.price-feats{list-style:none;margin-bottom:24px}
.price-feats li{display:flex;align-items:flex-start;gap:9px;font-size:13.5px;color:var(--ink-soft);padding:7px 0}
.price-feats li::before{content:'✓';color:var(--teal-deep);font-weight:800;flex-shrink:0}

/* ════════ FAQ ════════ */
.faq-list{max-width:680px;margin:0 auto}
.faq-item{border-bottom:1px solid var(--line)}
.faq-q{
  display:flex;justify-content:space-between;align-items:center;gap:14px;
  padding:18px 4px;cursor:pointer;font-size:15px;font-weight:600;color:var(--ink);
  -webkit-tap-highlight-color:transparent;
}
.faq-icon{flex-shrink:0;width:22px;height:22px;display:flex;align-items:center;justify-content:center;
  color:var(--ember);transition:transform .25s;font-size:18px;font-weight:300}
.faq-item.open .faq-icon{transform:rotate(45deg)}
.faq-a{
  max-height:0;overflow:hidden;transition:max-height .3s ease, padding .3s ease;
  font-size:13.5px;color:var(--ink-soft);padding:0 4px;
}
.faq-item.open .faq-a{max-height:260px;padding-bottom:18px}

/* ════════ FINAL CTA ════════ */
.cta-band{
  background:linear-gradient(135deg,var(--ink) 0%,#1C2D52 100%);
  border-radius:24px;padding:56px 32px;text-align:center;position:relative;overflow:hidden;
}
.cta-band::before{
  content:'';position:absolute;inset:0;
  background:radial-gradient(circle at 30% 20%,rgba(255,107,53,.25),transparent 50%);
}
.cta-band h2{position:relative;font-size:clamp(22px,3.4vw,30px);font-weight:800;color:#fff;margin-bottom:12px}
.cta-band p{position:relative;color:#C7CEE0;font-size:14.5px;margin-bottom:26px}
.cta-band-actions{position:relative;display:flex;gap:12px;justify-content:center;flex-wrap:wrap}

/* ════════ FOOTER ════════ */
.foot{padding:40px 0 28px;border-top:1px solid var(--line)}
.foot-inner{display:flex;flex-direction:column;gap:18px;align-items:center;text-align:center}
@media(min-width:700px){.foot-inner{flex-direction:row;justify-content:space-between;text-align:right}}
.foot-logo{display:flex;align-items:center;gap:8px;font-weight:800}
.foot-links{display:flex;gap:18px;font-size:13px;color:var(--ink-soft);flex-wrap:wrap;justify-content:center}
.foot-copy{font-size:12px;color:#A8A295}

/* ════════ MODAL ════════ */
.modal-overlay{
  position:fixed;inset:0;background:rgba(20,33,61,.55);backdrop-filter:blur(3px);
  z-index:1000;display:none;align-items:center;justify-content:center;padding:20px;
}
.modal-overlay.show{display:flex;animation:fadeIn .2s ease}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.modal-box{
  background:var(--card);border-radius:18px;max-width:380px;width:100%;
  padding:30px 26px;position:relative;box-shadow:0 30px 80px rgba(0,0,0,.3);
  animation:modalUp .25s cubic-bezier(.2,.8,.2,1);
}
@keyframes modalUp{from{opacity:0;transform:translateY(16px) scale(.98)}to{opacity:1;transform:none}}
.modal-close{
  position:absolute;top:14px;left:14px;width:30px;height:30px;border-radius:50%;
  border:none;background:var(--paper-2);cursor:pointer;font-size:15px;color:var(--ink-soft);
  display:flex;align-items:center;justify-content:center;
}
.modal-close:hover{background:var(--line)}
.modal-logo{
  width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,var(--ember),var(--ember-deep));
  display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:20px;
  margin-bottom:16px;
}
.modal-box h2{font-size:19px;font-weight:800;margin-bottom:4px}
.modal-box .modal-sub{font-size:13px;color:var(--ink-soft);margin-bottom:22px}
.form-group{margin-bottom:14px}
.form-group label{font-size:12.5px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:6px}
.form-group input{
  width:100%;padding:11px 14px;border:1.5px solid var(--line);border-radius:10px;
  font-size:14px;font-family:inherit;background:var(--paper);color:var(--ink);
  transition:border-color .15s;
}
.form-group input:focus{outline:none;border-color:var(--ember);background:#fff}
.modal-error{
  background:#FCE8E6;color:#C0392B;border:1px solid #F5C6CB;border-radius:9px;
  padding:10px 14px;font-size:13px;margin-bottom:14px;
}
.modal-foot{margin-top:18px;text-align:center;font-size:13px;color:var(--ink-soft)}
.modal-foot a{color:var(--ember-deep);font-weight:700}

/* ════════ reveal on scroll ════════ */
.reveal{opacity:0;transform:translateY(16px);transition:opacity .5s ease,transform .5s ease}
.reveal.in{opacity:1;transform:none}
</style>
</head>
<body>

<!-- ═══════════ NAVBAR ═══════════ -->
<header class="nav">
  <div class="container nav-inner">
    <a href="#" class="nav-logo">
      <span class="nav-logo-mark">پ</span>
      پیگیریو
    </a>
    <nav class="nav-links">
      <a href="#flow">چطور کار می‌کنه</a>
      <a href="#features">ویژگی‌ها</a>
      <a href="#pricing">قیمت‌گذاری</a>
      <a href="#faq">سوالات متداول</a>
      <a href="guide.php">راهنمای کامل</a>
    </nav>
    <div class="nav-actions">
        <?php if ($is_logged_in): ?>
            <a href="index.php?page=dashboard" class="btn btn-primary">رفتن به داشبورد</a>
        <?php else: ?>
            <button class="btn btn-ghost" onclick="openLoginModal()">ورود</button>
            <a href="index.php?page=auth&mode=register" class="btn btn-primary">شروع رایگان</a>
        <?php endif; ?>
    </div>
  </div>
</header>

<!-- ═══════════ HERO ═══════════ -->
<section class="hero">
  <div class="container hero-grid">
    <div>
      <div class="eyebrow"><span class="eyebrow-dot"></span> ۱۴ روز رایگان، بدون نیاز به کارت بانکی</div>
      <h1>مشتری بساز، فرصت فروش بذار،<br><em>فعالیت رو پیگیری کن</em></h1>
      <p class="hero-sub">پیگیریو، CRM ساده فارسیه که یک منطق روشن داره: اول مشتری رو ثبت می‌کنی، براش یک فرصت فروش (هدف مشخص) تعریف می‌کنی، و هر تماس یا جلسه رو به‌عنوان فعالیت زیر همون فرصت فروش می‌نویسی — تا وقتی به نتیجه برسه.</p>
      <div class="hero-ctas">
        <?php if ($is_logged_in): ?>
            <a href="index.php?page=dashboard" class="btn btn-primary btn-lg">رفتن به داشبورد</a>
        <?php else: ?>
            <a href="index.php?page=auth&mode=register" class="btn btn-primary btn-lg">شروع رایگان ۱۴ روزه</a>
        <?php endif; ?>
            <a href="#flow" class="btn btn-ghost btn-lg">ببین چطور کار می‌کنه</a>
        </div>
      <div class="hero-trust">⭐️⭐️⭐️⭐️⭐️ <b>+200 تیم فروش</b> همین الان از پیگیریو استفاده می‌کنن</div>
    </div>

    <div class="hero-visual reveal">
      <div class="float-badge float-badge-1">📈 +۳۲٪ نرخ پیگیری</div>
      <div class="timeline-card">
        <div class="tl-header">
          <span class="tl-header-title">تایم‌لاین مشتری — شرکت آرمان</span>
          <span class="tl-header-badge">فعال</span>
        </div>

        <div class="tl-row" data-stage="call">
          <div class="tl-dot">📞</div>
          <div class="tl-content">
            <div class="tl-row-inner">
              <span class="tl-title">تماس اولیه گرفته شد</span>
              <span class="tl-time">دوشنبه</span>
            </div>
            <div class="tl-meta">علاقه‌مند به دمو محصول</div>
          </div>
        </div>

        <div class="tl-row" data-stage="meeting">
          <div class="tl-dot">🤝</div>
          <div class="tl-content">
            <div class="tl-row-inner">
              <span class="tl-title">جلسه دمو برگزار شد</span>
              <span class="tl-time">چهارشنبه</span>
            </div>
            <div class="tl-meta">با مدیر فروش شرکت آرمان</div>
          </div>
        </div>

        <div class="tl-row" data-stage="task">
          <div class="tl-dot">⏰</div>
          <div class="tl-content">
            <div class="tl-row-inner">
              <span class="tl-title">پیگیری بعدی برنامه‌ریزی شد</span>
              <span class="tl-time">یادآوری</span>
            </div>
            <div class="tl-meta">ارسال پیش‌فاکتور تا فردا</div>
          </div>
        </div>

        <div class="tl-row" data-stage="won">
          <div class="tl-dot">💰</div>
          <div class="tl-content">
            <div class="tl-row-inner">
              <span class="tl-title">منجر به فروش شد</span>
              <span class="tl-time">جمعه</span>
            </div>
            <div class="tl-meta">قرارداد نهایی شد ✓</div>
          </div>
        </div>
      </div>
      <div class="float-badge float-badge-2">✅ هیچی از قلم نمی‌افته</div>
    </div>
  </div>
</section>

<!-- ═══════════ STRIP ═══════════ -->
<div class="strip">
  <div class="container strip-inner">
    <span>مناسب برای:</span>
    <div class="strip-tags">
      <span class="strip-tag">تیم‌های فروش SaaS</span>
      <span class="strip-tag">آژانس‌های دیجیتال</span>
      <span class="strip-tag">فروشندگان B2B</span>
      <span class="strip-tag">کسب‌وکارهای رو به رشد</span>
    </div>
  </div>
</div>

<!-- ═══════════ WORKFLOW MINI (مشتری → فرصت فروش → فعالیت) ═══════════ -->
<section class="section flow-section" id="flow">
  <div class="container">
    <div class="section-head reveal">
      <div class="section-eyebrow">منطق کار با پیگیریو</div>
      <h2 class="section-title">مشتری ← فرصت فروش ← فعالیت</h2>
      <p class="section-desc">یک مسیر ساده و همیشه یکسان — همینه که پیگیریو رو ساده نگه می‌داره.</p>
    </div>

    <div class="flow-mini reveal">
      <div class="flow-mini-item">
        <div class="flow-mini-icon" style="background:#E8F0FE">🏢</div>
        <span>مشتری بساز</span>
      </div>
      <div class="flow-mini-arrow" aria-hidden="true">←</div>
      <div class="flow-mini-item">
        <div class="flow-mini-icon" style="background:#FFF1EA">✅</div>
        <span>فرصت فروش تعریف کن</span>
      </div>
      <div class="flow-mini-arrow" aria-hidden="true">←</div>
      <div class="flow-mini-item">
        <div class="flow-mini-icon" style="background:#E7F7F3">📞</div>
        <span>فعالیت ثبت کن</span>
      </div>
    </div>

    <div class="flow-mini-cta reveal">
      <a href="guide.php" class="btn btn-ghost">راهنمای کامل قدم‌به‌قدم ←</a>
    </div>
  </div>
</section>

<!-- ═══════════ VS EXCEL / PAPER — BANNER ═══════════ -->
<section class="section" id="vs-excel" style="padding:44px 0;background:var(--paper-2)">
  <div class="container">
    <div class="vs-banner reveal">
      <div class="vs-banner-icon" aria-hidden="true">📤</div>
      <div class="vs-banner-text">
        <h3>خداحافظی با اکسل و دفترچه پراکنده</h3>
        <p>یک منبع واحد برای کل تیم، با یادآوری خودکار — و هر وقت خواستی، خروجی اکسل هم داری.</p>
      </div>
      <a href="guide.php#vs-excel" class="btn btn-ghost">بیشتر بخون</a>
    </div>
  </div>
</section>

<!-- ═══════════ FEATURES ═══════════ -->
<section class="section" id="features">
  <div class="container">
    <div class="section-head reveal">
      <div class="section-eyebrow">ویژگی‌ها</div>
      <h2 class="section-title">همه‌چی که برای پیگیری مشتری لازم داری</h2>
      <p class="section-desc">بدون پیچیدگی، بدون آموزش چندروزه.</p>
    </div>

    <div class="feat-grid">
      <div class="feat-card reveal">
        <div class="feat-icon" style="background:#E8F0FE">🏢</div>
        <h3>مدیریت مشتریان</h3>
        <p>شرکت، صنعت، مخاطب و وضعیت — یک‌جا، با جستجوی سریع.</p>
      </div>
      <div class="feat-card reveal">
        <div class="feat-icon" style="background:#FFF1EA">✅</div>
        <h3>فرصت فروش با هدف مشخص</h3>
        <p>هر فرصت فروش تا نتیجه (تکمیل، فروش یا لغو) توی داشبورد دیده می‌شه.</p>
      </div>
      <div class="feat-card reveal">
        <div class="feat-icon" style="background:#E7F7F3">📞</div>
        <h3>فعالیت زیر هر فرصت فروش</h3>
        <p>تماس، جلسه، ایمیل یا یادداشت — تاریخچه کامل هر معامله.</p>
      </div>
      <div class="feat-card reveal">
        <div class="feat-icon" style="background:#FFF3DD">⏰</div>
        <h3>یادآوری پیگیری</h3>
        <p>تاریخ پیگیری بعدی رو بذار، پیگیر خودش یادت میاره.</p>
      </div>
      <div class="feat-card reveal">
        <div class="feat-icon" style="background:#F3E8FD">👥</div>
        <h3>تیم و دسترسی</h3>
        <p>مدیر فروش و کارشناس اضافه کن؛ هرکس فقط داده خودشو می‌بینه.</p>
      </div>
      <div class="feat-card reveal">
        <div class="feat-icon" style="background:#FCE8E6">📊</div>
        <h3>گزارش‌گیری زنده</h3>
        <p>عملکرد خودت یا کل تیم رو با نمودار مقایسه‌ای ببین.</p>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════ TEAM & REPORTS (فشرده) ═══════════ -->
<section class="section" id="team-reports" style="background:var(--paper-2)">
  <div class="container">
    <div class="section-head reveal">
      <div class="section-eyebrow">وقتی تیم بزرگ‌تر می‌شه</div>
      <h2 class="section-title">تیم اضافه کن، عملکرد رو مقایسه کن</h2>
    </div>

    <div class="tr-grid">
      <div class="tr-card reveal">
        <div class="tr-card-head">
          <div class="tr-card-icon" style="background:#E8F0FE">👥</div>
          <div><h3>دسترسی سلسله‌مراتبی</h3></div>
        </div>
        <div class="org-chart">
          <div class="org-node admin">🛡️ مدیر</div>
          <div class="org-connector"></div>
          <div class="org-row">
            <div class="org-branch">
              <div class="org-node manager">👔 مدیر فروش</div>
              <div class="org-connector"></div>
              <div class="org-agents">
                <div class="org-node agent">📞 کارشناس</div>
                <div class="org-node agent">📞 کارشناس</div>
              </div>
            </div>
            <div class="org-branch">
              <div class="org-node manager">👔 مدیر فروش</div>
              <div class="org-connector"></div>
              <div class="org-agents">
                <div class="org-node agent">📞 کارشناس</div>
              </div>
            </div>
          </div>
        </div>
        <p class="tr-caption">هرکس فقط داده خودش و زیرمجموعه‌هاش رو می‌بینه.</p>
      </div>

      <div class="tr-card reveal">
        <div class="tr-card-head">
          <div class="tr-card-icon" style="background:#FCE8E6">📊</div>
          <div><h3>سه سطح گزارش</h3></div>
        </div>
        <div class="rpt-mock-bars" aria-hidden="true">
          <div class="rpt-mock-bar" style="height:38%"></div>
          <div class="rpt-mock-bar" style="height:62%"></div>
          <div class="rpt-mock-bar" style="height:45%"></div>
          <div class="rpt-mock-bar" style="height:80%"></div>
          <div class="rpt-mock-bar" style="height:55%"></div>
          <div class="rpt-mock-bar" style="height:70%"></div>
          <div class="rpt-mock-bar" style="height:40%"></div>
        </div>
        <div class="rpt-mock-legend" aria-hidden="true">
          <span><span class="rpt-mock-dot" style="background:#1a73e8"></span>تماس</span>
          <span><span class="rpt-mock-dot" style="background:#E6951E"></span>جلسه</span>
          <span><span class="rpt-mock-dot" style="background:#16A085"></span>ایمیل</span>
        </div>
        <p class="tr-caption">شخصی، کاربران و مقایسه تیم‌های مدیران.</p>
      </div>
    </div>

    <div class="flow-mini-cta reveal">
      <a href="guide.php#team" class="btn btn-ghost">راهنمای کامل تیم و گزارش‌گیری ←</a>
    </div>
  </div>
</section>

<!-- ═══════════ PRICING ═══════════ -->
<section class="section" id="pricing" style="background:var(--paper-2)">
  <div class="container">
    <div class="section-head reveal">
      <div class="section-eyebrow">قیمت‌گذاری</div>
      <h2 class="section-title">قیمتی شفاف، بدون هزینه پنهان</h2>
      <p class="section-desc">یک حساب ادمین + هر تعداد کاربر که نیاز داری.</p>
    </div>

    <div style="display:flex;justify-content:center">
      <div class="pricing-toggle" id="priceToggle">
        <div class="pt-slider" id="ptSlider"></div>
        <button class="pt-btn active" id="btnMonthly" onclick="setBilling('monthly')">ماهانه</button>
        <button class="pt-btn" id="btnYearly" onclick="setBilling('yearly')">سالانه <span class="pt-save">۲ ماه رایگان</span></button>
      </div>
    </div>

    <div class="price-grid reveal">
      <div class="price-card highlight">
        <span class="price-tag">پرکاربردترین</span>
        <h3>🏢 حساب ادمین</h3>
        <p class="price-desc">مدیریت کامل شرکت، مشتریان و گزارش‌ها</p>
        <div class="price-amount">
          <span class="price-num" data-monthly="<?= toman($base_plan['price_monthly']) ?>" data-yearly="<?= toman(round($base_plan['price_yearly']/12)) ?>">
            <?= toman($base_plan['price_monthly']) ?>
          </span>
          <span class="price-unit">تومان / ماه</span>
        </div>
        <div class="price-period" id="periodNoteBase">پرداخت ماهانه</div>
        <ul class="price-feats">
          <li>دسترسی کامل ادمین</li>
          <li>مدیریت نامحدود مشتری، فرصت فروش و فعالیت</li>
          <li>گزارش‌گیری کامل تیم</li>
          <li>پشتیبان‌گیری اکسل و SQL</li>
          <li>۱۴ روز اول رایگان</li>
        </ul>
        <a href="<?= $is_logged_in ? 'index.php?page=dashboard' : 'index.php?page=auth&mode=register' ?>" class="btn btn-primary btn-block"><?= $is_logged_in ? 'رفتن به داشبورد' : 'شروع رایگان' ?></a>      </div>

      <div class="price-card">
        <h3>👤 هر کاربر اضافه</h3>
        <p class="price-desc">برای هر کارشناس یا مدیر فروش اضافه</p>
        <div class="price-amount">
          <span class="price-num" data-monthly="<?= toman($per_user_plan['price_monthly']) ?>" data-yearly="<?= toman(round($per_user_plan['price_yearly']/12)) ?>">
            <?= toman($per_user_plan['price_monthly']) ?>
          </span>
          <span class="price-unit">تومان / ماه</span>
        </div>
        <div class="price-period" id="periodNoteUser">پرداخت ماهانه</div>
        <ul class="price-feats">
          <li>دسترسی کامل کارشناس</li>
          <li>ثبت فعالیت و پیگیری شخصی</li>
          <li>گزارش عملکرد فردی</li>
          <li>افزودن/حذف هر زمان</li>
        </ul>
        <a href="<?= $is_logged_in ? 'index.php?page=users' : 'index.php?page=auth&mode=register' ?>" class="btn btn-ghost btn-block"><?= $is_logged_in ? 'مدیریت کاربران' : 'افزودن به تیم' ?></a>      </div>
    </div>
  </div>
</section>

<!-- ═══════════ FAQ ═══════════ -->
<section class="section" id="faq">
  <div class="container">
    <div class="section-head reveal">
      <div class="section-eyebrow">سوالات متداول</div>
      <h2 class="section-title">قبل از شروع بدون</h2>
    </div>

    <div class="faq-list reveal">
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">چرا اول باید مشتری بسازم، بعد فرصت فروش، بعد فعالیت؟ <span class="faq-icon">+</span></div>
        <div class="faq-a">چون هر کدوم یه لایه‌ست: مشتری یعنی «این شرکت رو می‌شناسم»، فرصت فروش یعنی «این هدف مشخص رو دنبال می‌کنم»، و فعالیت یعنی «این کاری بود که همین الان انجام دادم». این ترتیب باعث میشه هیچ‌وقت گم نشی که کدوم پیگیری به کجا رسیده.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">آیا واقعاً ۱۴ روز رایگانه؟ <span class="faq-icon">+</span></div>
        <div class="faq-a">بله، کاملاً. بدون نیاز به کارت بانکی ثبت‌نام می‌کنی و ۱۴ روز کامل با سقف ۵ کاربر رایگان استفاده می‌کنی.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">بعد از اتمام دوره رایگان چی میشه؟ <span class="faq-icon">+</span></div>
        <div class="faq-a">داده‌هات حذف نمیشه. می‌تونی اطلاعاتت رو ببینی ولی برای ادامه کار باید پلن مناسب رو فعال کنی.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">آیا می‌تونم بعداً کاربر اضافه کنم؟ <span class="faq-icon">+</span></div>
        <div class="faq-a">بله، هر زمان می‌تونی از پنل مدیریت، کاربر جدید اضافه یا حذف کنی و هزینه به‌صورت نسبی محاسبه میشه.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">گزارش‌ها چه چیزی رو نشون میدن؟ <span class="faq-icon">+</span></div>
        <div class="faq-a">سه سطح گزارش داری: گزارش شخصی خودت، مقایسه کاربران زیرمجموعه، و مقایسه تیم‌های مدیران — با نمودار روند روزانه و نرخ تکمیل فرصت فروش.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">داده‌هام امنه؟ <span class="faq-icon">+</span></div>
        <div class="faq-a">هر کاربر فقط به داده خودش و زیرمجموعه‌هاش دسترسی داره. رمزها رمزنگاری شده و دسترسی‌ها به‌صورت سلسله‌مراتبی کنترل میشه.</div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════ FINAL CTA ═══════════ -->
<section class="section">
  <div class="container">
    <div class="cta-band reveal">
      <h2>همین امروز شروع کن</h2>
      <p>۱۴ روز رایگان، بدون کارت بانکی، بدون تعهد.</p>
      <div class="cta-band-actions">
    <?php if ($is_logged_in): ?>
        <a href="index.php?page=dashboard" class="btn btn-primary btn-lg">رفتن به داشبورد</a>
    <?php else: ?>
        <a href="index.php?page=auth&mode=register" class="btn btn-primary btn-lg">شروع رایگان</a>
        <button class="btn btn-ghost btn-lg" style="background:transparent;color:#fff;border-color:rgba(255,255,255,.3)" onclick="openLoginModal()">من قبلاً ثبت‌نام کردم</button>
    <?php endif; ?>
    </div>
    </div>
  </div>
</section>

<!-- ═══════════ FOOTER ═══════════ -->
<footer class="foot">
  <div class="container foot-inner">
    <div class="foot-logo">
      <span class="nav-logo-mark">پ</span> پیگیریو
    </div>
    <div class="foot-links">
        <a href="#flow">چطور کار می‌کنه</a>
        <a href="#features">ویژگی‌ها</a>
        <a href="#pricing">قیمت‌گذاری</a>
        <a href="#faq">سوالات متداول</a>
        <a href="guide.php">راهنمای کامل</a>
        <?php if ($is_logged_in): ?>
            <a href="index.php?page=dashboard">داشبورد</a>
        <?php else: ?>
            <a href="index.php?page=auth&mode=login">ورود</a>
        <?php endif; ?>
    </div>
    <div class="foot-copy">© <?= date('Y') ?> پیگیریو — تمامی حقوق محفوظ است</div>
  </div>
</footer>

<!-- ═══════════ LOGIN MODAL ═══════════ -->
<div class="modal-overlay" id="loginModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeLoginModal()">✕</button>
    <div class="modal-logo">پ</div>
    <h2>ورود به پیگیر</h2>
    <p class="modal-sub">با شماره موبایل و رمز عبورت وارد شو</p>

    <?php if ($login_error): ?>
    <div class="modal-error"><?= crm_sanitize($login_error) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=auth&mode=login">
      <div class="form-group">
        <label>📱 شماره موبایل</label>
        <input type="tel" name="mobile" required placeholder="۰۹۱۲۳۴۵۶۷۸۹" pattern="09[0-9]{9}" maxlength="11">
      </div>
      <div class="form-group">
        <label>🔒 رمز عبور</label>
        <input type="password" name="password" required placeholder="رمز عبور" minlength="6">
      </div>
      <?php include __DIR__ . '/includes/csrf_field.php'; ?>
      <button type="submit" class="btn btn-primary btn-block">ورود</button>
    </form>

    <div class="modal-foot">
      حساب نداری؟ <a href="index.php?page=auth&mode=register">ثبت‌نام رایگان</a>
    </div>
  </div>
</div>

<script>
// ── Login Modal ──
function openLoginModal(){
  document.getElementById('loginModal').classList.add('show');
  document.body.style.overflow = 'hidden';
}
function closeLoginModal(){
  document.getElementById('loginModal').classList.remove('show');
  document.body.style.overflow = '';
}
document.getElementById('loginModal').addEventListener('click', function(e){
  if (e.target === this) closeLoginModal();
});
document.addEventListener('keydown', function(e){
  if (e.key === 'Escape') closeLoginModal();
});
<?php if ($open_login_modal): ?>
window.addEventListener('DOMContentLoaded', openLoginModal);
<?php endif; ?>

// ── FAQ accordion ──
function toggleFaq(el){
  var item = el.parentElement;
  var wasOpen = item.classList.contains('open');
  document.querySelectorAll('.faq-item.open').forEach(function(i){ i.classList.remove('open'); });
  if (!wasOpen) item.classList.add('open');
}

// ── Pricing toggle ──
function setBilling(mode){
  var slider = document.getElementById('ptSlider');
  var btnM = document.getElementById('btnMonthly');
  var btnY = document.getElementById('btnYearly');
  var nums = document.querySelectorAll('.price-num');
  var noteBase = document.getElementById('periodNoteBase');
  var noteUser = document.getElementById('periodNoteUser');

  if (mode === 'yearly') {
    slider.classList.add('yearly');
    btnY.classList.add('active'); btnM.classList.remove('active');
    nums.forEach(function(n){ n.textContent = n.getAttribute('data-yearly'); });
    noteBase.textContent = 'پرداخت سالانه (معادل ماهانه)';
    noteUser.textContent = 'پرداخت سالانه (معادل ماهانه)';
  } else {
    slider.classList.remove('yearly');
    btnM.classList.add('active'); btnY.classList.remove('active');
    nums.forEach(function(n){ n.textContent = n.getAttribute('data-monthly'); });
    noteBase.textContent = 'پرداخت ماهانه';
    noteUser.textContent = 'پرداخت ماهانه';
  }
}

// ── Scroll reveal ──
(function(){
  var els = document.querySelectorAll('.reveal');
  var obs = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if (e.isIntersecting) { e.target.classList.add('in'); obs.unobserve(e.target); }
    });
  }, { threshold: 0.12 });
  els.forEach(function(el){ obs.observe(el); });
})();
</script>
</body>
</html>