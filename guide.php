<?php
// guide.php — راهنمای کامل استفاده از «پیگیر»
// صفحه‌ی آموزشی جدا از لندینگ: اینجا دقیق توضیح می‌دیم که کاربر باید چیکار کنه.
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$is_logged_in = crm_is_logged_in();

$login_error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
$open_login_modal = !empty($login_error);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>راهنمای کامل پیگیر — از ثبت‌نام تا گزارش‌گیری</title>
<meta name="description" content="راهنمای قدم‌به‌قدم پیگیر: چطور مشتری بسازی، تسک تعریف کنی، فعالیت ثبت کنی، تیم بسازی و گزارش بگیری.">
<meta name="robots" content="index, follow">
<link rel="canonical" href="https://paygiro.ir/guide.php">

<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
<style>
:root{
  --ink:#14213D; --ink-soft:#4A5578; --paper:#FAF8F5; --paper-2:#F2EEE6;
  --ember:#FF6B35; --ember-deep:#E6531E; --teal:#16A085; --teal-deep:#0E8170;
  --blue:#1a73e8; --line:#E5DFD3; --card:#FFFFFF; --radius:14px;
  --shadow:0 8px 30px rgba(20,33,61,.08); --shadow-lg:0 20px 60px rgba(20,33,61,.14);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
  font-family:'Vazirmatn','Segoe UI',Tahoma,sans-serif;background:var(--paper);color:var(--ink);
  direction:rtl;line-height:1.7;-webkit-font-smoothing:antialiased;overflow-x:hidden;
}
a{color:inherit;text-decoration:none}
.container{max-width:980px;margin:0 auto;padding:0 20px}
a:focus-visible,button:focus-visible{outline:2px solid var(--ember);outline-offset:2px}
@media(prefers-reduced-motion:reduce){*{animation-duration:.01ms!important;transition-duration:.01ms!important;scroll-behavior:auto!important}}

.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 20px;border-radius:10px;font-size:14px;font-weight:700;border:none;cursor:pointer;transition:transform .15s,box-shadow .15s,background .15s;white-space:nowrap}
.btn-ghost{background:transparent;color:var(--ink);border:1.5px solid var(--line)}
.btn-ghost:hover{border-color:var(--ink);background:var(--paper-2)}
.btn-primary{background:var(--ember);color:#fff;box-shadow:0 4px 14px rgba(255,107,53,.32)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(255,107,53,.4);background:var(--ember-deep)}
.btn-lg{padding:14px 28px;font-size:15px;border-radius:12px}

/* NAV */
.nav{position:sticky;top:0;z-index:300;background:rgba(250,248,245,.9);backdrop-filter:blur(10px);border-bottom:1px solid var(--line)}
.nav-inner{max-width:1140px;margin:0 auto;padding:0 20px;display:flex;align-items:center;justify-content:space-between;height:64px}
.nav-logo{display:flex;align-items:center;gap:8px;font-weight:800;font-size:19px;color:var(--ink)}
.nav-logo-mark{width:30px;height:30px;border-radius:9px;background:linear-gradient(135deg,var(--ember),var(--ember-deep));display:flex;align-items:center;justify-content:center;color:#fff;font-size:15px;font-weight:900;flex-shrink:0}
.nav-links{display:none;gap:24px;font-size:13.5px;color:var(--ink-soft);font-weight:500}
.nav-links a:hover{color:var(--ink)}
@media(min-width:820px){.nav-links{display:flex}}
.nav-actions{display:flex;align-items:center;gap:10px}

/* PAGE HEADER */
.gd-hero{padding:56px 0 40px;text-align:center;background:linear-gradient(180deg,var(--paper-2),var(--paper))}
.gd-back{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--ink-soft);margin-bottom:18px}
.gd-back:hover{color:var(--ink)}
.gd-hero h1{font-size:clamp(26px,4.4vw,38px);font-weight:800;letter-spacing:-.02em;margin-bottom:14px}
.gd-hero p{font-size:15.5px;color:var(--ink-soft);max-width:560px;margin:0 auto}

/* CHECKLIST / TOC */
.gd-checklist{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;max-width:760px;margin:36px auto 0}
@media(min-width:700px){.gd-checklist{grid-template-columns:repeat(3,1fr)}}
.gd-check-item{
  display:flex;align-items:center;gap:10px;background:var(--card);border:1px solid var(--line);
  border-radius:12px;padding:12px 14px;font-size:12.5px;font-weight:700;color:var(--ink);
  transition:border-color .15s,transform .15s;
}
.gd-check-item:hover{border-color:var(--ember);transform:translateY(-2px)}
.gd-check-num{
  width:24px;height:24px;border-radius:7px;background:var(--ink);color:#fff;font-size:11px;font-weight:800;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}

/* SECTION shared */
.section{padding:64px 0}
.section-head{max-width:640px;margin:0 auto 40px;text-align:center}
.section-eyebrow{font-size:12px;font-weight:800;color:var(--ember-deep);letter-spacing:.04em;margin-bottom:10px;text-transform:uppercase}
.section-title{font-size:clamp(22px,3.2vw,30px);font-weight:800;color:var(--ink);letter-spacing:-.01em;margin-bottom:10px}
.section-desc{font-size:14.5px;color:var(--ink-soft)}
.anchor-offset{scroll-margin-top:80px}

/* FLOW detailed cards */
.flow-grid{display:grid;grid-template-columns:1fr;gap:0;max-width:980px;margin:0 auto;counter-reset:flow}
@media(min-width:880px){.flow-grid{grid-template-columns:1fr auto 1fr auto 1fr;align-items:stretch}}
.flow-card{background:var(--card);border:1.5px solid var(--line);border-radius:18px;padding:26px 24px;position:relative;counter-increment:flow}
.flow-card::before{content:counter(flow);position:absolute;top:-14px;right:24px;width:30px;height:30px;border-radius:9px;background:var(--ink);color:#fff;font-size:13px;font-weight:800;display:flex;align-items:center;justify-content:center}
.flow-card.step-1{border-color:#D7E4FB}
.flow-card.step-2{border-color:#FFD9C2}
.flow-card.step-3{border-color:#BEE7DC}
.flow-icon{width:46px;height:46px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:21px;margin-bottom:14px}
.flow-card.step-1 .flow-icon{background:#E8F0FE}
.flow-card.step-2 .flow-icon{background:#FFF1EA}
.flow-card.step-3 .flow-icon{background:#E7F7F3}
.flow-tag{font-size:11px;font-weight:800;letter-spacing:.03em;text-transform:uppercase;margin-bottom:6px}
.flow-card.step-1 .flow-tag{color:var(--blue)}
.flow-card.step-2 .flow-tag{color:var(--ember-deep)}
.flow-card.step-3 .flow-tag{color:var(--teal-deep)}
.flow-card h3{font-size:17px;font-weight:800;color:var(--ink);margin-bottom:8px}
.flow-card p{font-size:13.5px;color:var(--ink-soft);margin-bottom:14px}
.flow-example{background:var(--paper-2);border-radius:10px;padding:10px 12px;font-size:12px;color:var(--ink-soft)}
.flow-card.step-1 .flow-example{background:#F1F6FE}
.flow-card.step-2 .flow-example{background:#FFF6F0}
.flow-card.step-3 .flow-example{background:#F0FAF7}
.flow-example b{color:var(--ink);font-weight:700}
.flow-arrow{display:none;align-items:center;justify-content:center;color:var(--ink-soft);font-size:22px;padding:0 6px}
@media(min-width:880px){.flow-arrow{display:flex}}
.flow-arrow-mobile{display:flex;align-items:center;justify-content:center;color:var(--ink-soft);font-size:20px;padding:6px 0;transform:rotate(90deg)}
@media(min-width:880px){.flow-arrow-mobile{display:none}}
.flow-note{max-width:780px;margin:32px auto 0;background:var(--card);border:1px dashed var(--line);border-radius:14px;padding:16px 20px;font-size:13px;color:var(--ink-soft);text-align:center;line-height:1.8}
.flow-note b{color:var(--ink)}

/* VS EXCEL detailed */
.vs-grid{display:grid;grid-template-columns:1fr;gap:16px;max-width:900px;margin:0 auto}
@media(min-width:760px){.vs-grid{grid-template-columns:1fr 1fr}}
.vs-col{background:var(--card);border-radius:18px;padding:26px 24px;border:1.5px solid var(--line)}
.vs-col-bad{border-color:#F5C6CB}
.vs-col-good{border-color:#BEE7DC}
.vs-col-head{display:flex;align-items:center;gap:10px;margin-bottom:16px}
.vs-col-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.vs-col-bad .vs-col-icon{background:#FCE8E6}
.vs-col-good .vs-col-icon{background:#E7F7F3}
.vs-col-head h3{font-size:16px;font-weight:800;color:var(--ink)}
.vs-list{list-style:none}
.vs-list li{display:flex;align-items:flex-start;gap:10px;font-size:13.5px;color:var(--ink-soft);padding:8px 0;border-bottom:1px solid var(--paper-2)}
.vs-list li:last-child{border-bottom:none}
.vs-x{color:#EA4335;font-weight:800;flex-shrink:0}
.vs-check{color:var(--teal-deep);font-weight:800;flex-shrink:0}
.vs-note{max-width:820px;margin:30px auto 0;background:var(--card);border:1px solid var(--line);border-radius:14px;padding:18px 20px;display:flex;gap:14px;align-items:flex-start;font-size:13px;color:var(--ink-soft);line-height:1.8}
.vs-note-icon{font-size:22px;flex-shrink:0}
.vs-note b{color:var(--ink)}

/* TEAM detailed */
.tr-grid{display:grid;grid-template-columns:1fr;gap:22px}
@media(min-width:900px){.tr-grid{grid-template-columns:1fr 1fr}}
.tr-card{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:26px 24px;box-shadow:var(--shadow)}
.tr-card-head{display:flex;align-items:center;gap:11px;margin-bottom:16px}
.tr-card-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.tr-card-head h3{font-size:16.5px;font-weight:800;color:var(--ink)}
.tr-card-head p{font-size:12px;color:var(--ink-soft);margin-top:2px}
.org-chart{display:flex;flex-direction:column;align-items:center;gap:0;padding:12px 0 4px}
.org-node{background:var(--paper);border:1.5px solid var(--line);border-radius:10px;padding:9px 16px;font-size:12.5px;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:7px}
.org-node.admin{background:#E8F0FE;border-color:#C9DCFB;color:var(--blue)}
.org-node.manager{background:#FFF1EA;border-color:#FFD9C2;color:var(--ember-deep)}
.org-node.agent{background:#E7F7F3;border-color:#BEE7DC;color:var(--teal-deep);font-weight:600}
.org-connector{width:2px;height:18px;background:var(--line)}
.org-row{display:flex;gap:14px;flex-wrap:wrap;justify-content:center}
.org-branch{display:flex;flex-direction:column;align-items:center;gap:0}
.org-agents{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;justify-content:center}
.tr-list{list-style:none;margin-top:16px}
.tr-list li{display:flex;gap:9px;font-size:12.5px;color:var(--ink-soft);padding:6px 0}
.tr-list li::before{content:'✓';color:var(--teal-deep);font-weight:800;flex-shrink:0}
.rpt-mock-bars{display:flex;align-items:flex-end;gap:7px;height:88px;padding:0 4px;margin-bottom:14px}
.rpt-mock-bar{flex:1;border-radius:5px 5px 2px 2px;background:var(--ember);opacity:.85}
.rpt-mock-legend{display:flex;gap:14px;flex-wrap:wrap;font-size:11px;color:var(--ink-soft);margin-bottom:16px}
.rpt-mock-legend span{display:flex;align-items:center;gap:5px}
.rpt-mock-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}

/* BACKUP section */
.bk-grid{display:grid;grid-template-columns:1fr;gap:16px;max-width:900px;margin:0 auto}
@media(min-width:700px){.bk-grid{grid-template-columns:1fr 1fr}}
.bk-card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px}
.bk-card-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:12px}
.bk-card h3{font-size:15px;font-weight:800;margin-bottom:7px}
.bk-card p{font-size:13px;color:var(--ink-soft)}

/* CTA */
.cta-band{background:linear-gradient(135deg,var(--ink) 0%,#1C2D52 100%);border-radius:24px;padding:52px 32px;text-align:center;position:relative;overflow:hidden}
.cta-band::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% 20%,rgba(255,107,53,.25),transparent 50%)}
.cta-band h2{position:relative;font-size:clamp(20px,3vw,26px);font-weight:800;color:#fff;margin-bottom:10px}
.cta-band p{position:relative;color:#C7CEE0;font-size:13.5px;margin-bottom:22px}
.cta-band-actions{position:relative;display:flex;gap:12px;justify-content:center;flex-wrap:wrap}

/* FOOTER */
.foot{padding:36px 0 26px;border-top:1px solid var(--line)}
.foot-inner{max-width:1140px;margin:0 auto;padding:0 20px;display:flex;flex-direction:column;gap:16px;align-items:center;text-align:center}
@media(min-width:700px){.foot-inner{flex-direction:row;justify-content:space-between;text-align:right}}
.foot-logo{display:flex;align-items:center;gap:8px;font-weight:800}
.foot-links{display:flex;gap:16px;font-size:12.5px;color:var(--ink-soft);flex-wrap:wrap;justify-content:center}
.foot-copy{font-size:12px;color:#A8A295}

/* MODAL (همون مودال ورود لندینگ) */
.modal-overlay{position:fixed;inset:0;background:rgba(20,33,61,.55);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center;padding:20px}
.modal-overlay.show{display:flex;animation:fadeIn .2s ease}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.modal-box{background:var(--card);border-radius:18px;max-width:380px;width:100%;padding:30px 26px;position:relative;box-shadow:0 30px 80px rgba(0,0,0,.3);animation:modalUp .25s cubic-bezier(.2,.8,.2,1)}
@keyframes modalUp{from{opacity:0;transform:translateY(16px) scale(.98)}to{opacity:1;transform:none}}
.modal-close{position:absolute;top:14px;left:14px;width:30px;height:30px;border-radius:50%;border:none;background:var(--paper-2);cursor:pointer;font-size:15px;color:var(--ink-soft);display:flex;align-items:center;justify-content:center}
.modal-close:hover{background:var(--line)}
.modal-logo{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,var(--ember),var(--ember-deep));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:20px;margin-bottom:16px}
.modal-box h2{font-size:19px;font-weight:800;margin-bottom:4px}
.modal-box .modal-sub{font-size:13px;color:var(--ink-soft);margin-bottom:22px}
.form-group{margin-bottom:14px}
.form-group label{font-size:12.5px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:6px}
.form-group input{width:100%;padding:11px 14px;border:1.5px solid var(--line);border-radius:10px;font-size:14px;font-family:inherit;background:var(--paper);color:var(--ink);transition:border-color .15s}
.form-group input:focus{outline:none;border-color:var(--ember);background:#fff}
.modal-error{background:#FCE8E6;color:#C0392B;border:1px solid #F5C6CB;border-radius:9px;padding:10px 14px;font-size:13px;margin-bottom:14px}
.modal-foot{margin-top:18px;text-align:center;font-size:13px;color:var(--ink-soft)}
.modal-foot a{color:var(--ember-deep);font-weight:700}
.btn-block{width:100%}

.reveal{opacity:0;transform:translateY(14px);transition:opacity .5s ease,transform .5s ease}
.reveal.in{opacity:1;transform:none}
</style>
</head>
<body>

<!-- NAV -->
<header class="nav">
  <div class="nav-inner">
    <a href="landing.php" class="nav-logo">
      <span class="nav-logo-mark">پ</span>
      پیگیر
    </a>
    <nav class="nav-links">
      <a href="#start">شروع</a>
      <a href="#flow">مشتری→تسک→فعالیت</a>
      <a href="#team">تیم و دسترسی</a>
      <a href="#reports">گزارش‌گیری</a>
      <a href="#backup">پشتیبان‌گیری</a>
    </nav>
    <div class="nav-actions">
        <?php if ($is_logged_in): ?>
            <a href="index.php?page=dashboard" class="btn btn-primary">داشبورد</a>
        <?php else: ?>
            <a href="index.php?page=auth&mode=register" class="btn btn-primary">شروع رایگان</a>
        <?php endif; ?>
    </div>
  </div>
</header>

<!-- HEADER -->
<section class="gd-hero">
  <div class="container">
    <a href="landing.php" class="gd-back">→ بازگشت به صفحه اصلی</a>
    <h1>راهنمای کامل استفاده از پیگیر</h1>
    <p>این صفحه دقیق بهت می‌گه از لحظه ثبت‌نام تا گزارش گرفتن، باید چیکار کنی — قدم به قدم.</p>

    <div class="gd-checklist reveal">
      <a href="#start" class="gd-check-item"><span class="gd-check-num">۱</span>ثبت‌نام کن</a>
      <a href="#flow" class="gd-check-item"><span class="gd-check-num">۲</span>مشتری بساز</a>
      <a href="#flow" class="gd-check-item"><span class="gd-check-num">۳</span>تسک تعریف کن</a>
      <a href="#flow" class="gd-check-item"><span class="gd-check-num">۴</span>فعالیت ثبت کن</a>
      <a href="#team" class="gd-check-item"><span class="gd-check-num">۵</span>تیم بساز</a>
      <a href="#reports" class="gd-check-item"><span class="gd-check-num">۶</span>گزارش بگیر</a>
    </div>
  </div>
</section>

<!-- START -->
<section class="section anchor-offset" id="start">
  <div class="container">
    <div class="section-head reveal">
      <div class="section-eyebrow">قدم صفر</div>
      <h2 class="section-title">یک حساب بساز</h2>
      <p class="section-desc">فقط با شماره موبایل ثبت‌نام کن — بدون کارت بانکی، بدون تعهد. ۱۴ روز کامل با سقف ۵ کاربر رایگانه. اگر نام شرکت رو خالی بذاری، اسم خودت به‌عنوان شرکت ثبت می‌شه و بعداً هم می‌تونی عوضش کنی.</p>
    </div>
    <div style="text-align:center">
      <a href="index.php?page=auth&mode=register" class="btn btn-primary btn-lg">شروع رایگان ۱۴ روزه</a>
    </div>
  </div>
</section>

<!-- FLOW: مشتری → تسک → فعالیت -->
<section class="section anchor-offset" id="flow" style="background:var(--paper-2)">
  <div class="container">
    <div class="section-head reveal">
      <div class="section-eyebrow">منطق اصلی کار</div>
      <h2 class="section-title">مشتری → تسک → فعالیت</h2>
      <p class="section-desc">هر فروش موفق همین سه قدم رو داره. پیگیر همین ترتیب رو توی رابط کاربری هم اجرا می‌کنه، تا هیچ‌وقت گیج نشی که «الان باید چیکار کنم».</p>
    </div>

    <div class="flow-grid reveal">
      <div class="flow-card step-1">
        <div class="flow-tag">قدم اول</div>
        <div class="flow-icon" aria-hidden="true">🏢</div>
        <h3>مشتری رو بساز</h3>
        <p>از منوی «مشتریان» → «مشتری جدید». شرکت، صنعت فعالیت، شماره تماس و مخاطب اصلی رو ثبت کن. این پایه‌ی همه‌چیز بعدیه.</p>
        <div class="flow-example"><b>مثال:</b> «شرکت آرمان صنعت» — صنعت: فولاد — مخاطب: آقای رضایی</div>
      </div>

      <div class="flow-arrow" aria-hidden="true">←</div>
      <div class="flow-arrow-mobile" aria-hidden="true">←</div>

      <div class="flow-card step-2">
        <div class="flow-tag">قدم دوم</div>
        <div class="flow-icon" aria-hidden="true">✅</div>
        <h3>براش یک تسک بذار</h3>
        <p>از داخل صفحه مشتری → «تسک جدید». تسک یعنی یک هدف مشخص که دنبالش می‌کنی — نه یک کار روزمره. برای هر مشتری می‌تونی چند تسک همزمان داشته باشی.</p>
        <div class="flow-example"><b>مثال:</b> «فروش دیزل ژنراتور ۵۰۰ کاوا» — پیگیری بعدی: سه‌شنبه</div>
      </div>

      <div class="flow-arrow" aria-hidden="true">←</div>
      <div class="flow-arrow-mobile" aria-hidden="true">←</div>

      <div class="flow-card step-3">
        <div class="flow-tag">قدم سوم</div>
        <div class="flow-icon" aria-hidden="true">📞</div>
        <h3>فعالیت‌ها رو زیرش ثبت کن</h3>
        <p>از داخل صفحه تسک → «افزودن فعالیت». هر تماس، جلسه، ایمیل یا یادداشت رو زیر همون تسک بنویس — تا وقتی تسک به نتیجه برسه.</p>
        <div class="flow-example"><b>مثال:</b> «تماس گرفتم، قیمت رو خواستن» — نوع: تماس</div>
      </div>
    </div>

    <div class="flow-note reveal">
      💡 <b>چرا این ترتیب مهمه؟</b> بدون مشتری، تسکی وجود نداره. بدون تسک، فعالیت‌هات پراکنده می‌مونن و معلوم نیست به کجا ختم میشن.
      وقتی تسک با نتیجه «✅ تکمیل شد»، «💰 منجر به فروش شد» یا «❌ کنسل شد» بسته میشه، از لیست تسک‌های فعال (و از داشبورد) خارج می‌شه — ولی همه فعالیت‌های زیرش برای گزارش‌گیری باقی می‌مونه.
    </div>
  </div>
</section>

<!-- VS EXCEL / PAPER -->
<section class="section anchor-offset" id="vs-excel">
  <div class="container">
    <div class="section-head reveal">
      <div class="section-eyebrow">جایگزین اکسل و دفترچه</div>
      <h2 class="section-title">همون کاری که با اکسل و دفترچه انجام می‌دی، بدون گم‌شدنش</h2>
      <p class="section-desc">خیلی از تیم‌های فروش هنوز مشتری‌هاشون رو توی یه فایل اکسل مشترک یا دفترچه یادداشت می‌نویسن. پیگیر همون منطق ساده رو داره — ولی هوشمندتر، امن‌تر و قابل دیدن برای کل تیم.</p>
    </div>

    <div class="vs-grid reveal">
      <div class="vs-col vs-col-bad">
        <div class="vs-col-head">
          <span class="vs-col-icon" aria-hidden="true">📄</span>
          <h3>اکسل و دفترچه</h3>
        </div>
        <ul class="vs-list">
          <li><span class="vs-x">✕</span>هر کارشناس یه نسخه جدا داره، هیچ‌کس نمی‌دونه کدوم آخرین نسخه‌ست</li>
          <li><span class="vs-x">✕</span>یادآوری پیگیری نداره — یا یادت میره یا باید خودت مرتب چک کنی</li>
          <li><span class="vs-x">✕</span>وقتی یه نفر از تیم میره، سابقه مشتری‌هاش هم باهاش میره</li>
          <li><span class="vs-x">✕</span>فرمول‌ها خراب میشن، ردیف‌ها جابجا میشن، رمز و دسترسی نداره</li>
          <li><span class="vs-x">✕</span>مدیر باید تک‌تک از هرکس بپرسه «امروز چند تا تماس گرفتی؟»</li>
        </ul>
      </div>

      <div class="vs-col vs-col-good">
        <div class="vs-col-head">
          <span class="vs-col-icon" aria-hidden="true">🧭</span>
          <h3>پیگیر</h3>
        </div>
        <ul class="vs-list">
          <li><span class="vs-check">✓</span>یک منبع واحد برای کل تیم — همه همیشه آخرین وضعیت رو می‌بینن</li>
          <li><span class="vs-check">✓</span>یادآوری پیگیری توی داشبورد، خودکار و بدون فراموشی</li>
          <li><span class="vs-check">✓</span>سابقه مشتری متعلق به شرکته، نه به فایل روی لپ‌تاپ یک نفر</li>
          <li><span class="vs-check">✓</span>دسترسی رمزدار و سلسله‌مراتبی — هرکس فقط داده خودشو می‌بینه</li>
          <li><span class="vs-check">✓</span>گزارش لحظه‌ای — بدون این‌که از کسی چیزی بپرسی</li>
        </ul>
      </div>
    </div>

    <div class="vs-note reveal">
      <span class="vs-note-icon" aria-hidden="true">📤</span>
      <div>
        <b>نگران از دست دادن اکسل نباش.</b>
        هر وقت خواستی، از بخش «پشتیبان‌گیری» یک خروجی اکسل کامل می‌گیری — دقیقاً همون فرمتی که بهش عادت داری. جزئیات کامل رو
        <a href="#backup" style="color:var(--ember-deep);font-weight:700">اینجا</a> ببین.
      </div>
    </div>
  </div>
</section>

<!-- TEAM -->
<section class="section anchor-offset" id="team" style="background:var(--paper-2)">
  <div class="container">
    <div class="section-head reveal">
      <div class="section-eyebrow">وقتی تیم بزرگ‌تر می‌شه</div>
      <h2 class="section-title">کاربر اضافه کن، دسترسی رو بسپار</h2>
      <p class="section-desc">پیگیر فقط برای یک نفر نیست. از منوی «کاربران» → «کاربر جدید»، برای هر کارشناس یا مدیر فروش یک حساب جدا بساز.</p>
    </div>

    <div class="tr-grid">
      <div class="tr-card reveal">
        <div class="tr-card-head">
          <div class="tr-card-icon" style="background:#E8F0FE">👥</div>
          <div>
            <h3>سلسله‌مراتب دسترسی</h3>
            <p>هر نقش فقط چیزی رو می‌بینه که بهش مربوطه</p>
          </div>
        </div>

        <div class="org-chart">
          <div class="org-node admin">🛡️ مدیر (ادمین شرکت)</div>
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

        <ul class="tr-list">
          <li>مدیر همه‌چیز شرکت رو می‌بینه، مدیر فروش فقط تیم خودش، کارشناس فقط کار خودش</li>
          <li>هر زمان از پنل «کاربران» می‌تونی نفر جدید اضافه یا غیرفعال کنی</li>
          <li>سقف کاربران فعال با پلن شما هماهنگه — کاربر اضافه، هزینه اضافه</li>
          <li>وقتی کاربری غیرفعال میشه، مشتری‌ها و تسک‌هاش پیش شرکت می‌مونن، فقط خودش دیگه وارد نمی‌شه</li>
        </ul>
      </div>

      <div class="tr-card reveal">
        <div class="tr-card-head">
          <div class="tr-card-icon" style="background:#FFF1EA">➕</div>
          <div>
            <h3>افزودن کاربر، قدم به قدم</h3>
          </div>
        </div>
        <ul class="tr-list">
          <li>وارد «کاربران» → «کاربر جدید» شو</li>
          <li>شماره موبایل، رمز عبور و نام کاربر رو وارد کن</li>
          <li>نقش رو انتخاب کن: مدیر فروش یا کارشناس</li>
          <li>اگه لازم شد، «مدیر بالادستی»‌شو مشخص کن تا زیرمجموعه درست تعریف بشه</li>
          <li>کاربر جدید همون لحظه می‌تونه با موبایل و رمزش وارد بشه</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- REPORTS -->
<section class="section anchor-offset" id="reports">
  <div class="container">
    <div class="section-head reveal">
      <div class="section-eyebrow">اندازه‌گیری عملکرد</div>
      <h2 class="section-title">سه سطح گزارش</h2>
      <p class="section-desc">از منوی «گزارش‌ها» به هر سه سطح دسترسی داری (بسته به نقشت).</p>
    </div>

    <div class="tr-grid">
      <div class="tr-card reveal">
        <div class="tr-card-head">
          <div class="tr-card-icon" style="background:#E8F0FE">👤</div>
          <div><h3>گزارش شخصی</h3></div>
        </div>
        <div class="rpt-mock-bars" aria-hidden="true">
          <div class="rpt-mock-bar" style="height:38%;background:#1a73e8"></div>
          <div class="rpt-mock-bar" style="height:62%;background:#1a73e8"></div>
          <div class="rpt-mock-bar" style="height:45%;background:#1a73e8"></div>
          <div class="rpt-mock-bar" style="height:80%;background:#1a73e8"></div>
          <div class="rpt-mock-bar" style="height:55%;background:#1a73e8"></div>
        </div>
        <ul class="tr-list">
          <li>تعداد تماس، جلسه، ایمیل و یادداشت خودت</li>
          <li>روند روزانه فعالیت با فیلتر بازه تاریخ</li>
          <li>نرخ تکمیل تسک‌ها و تعداد فروش‌های موفق</li>
        </ul>
      </div>

      <div class="tr-card reveal">
        <div class="tr-card-head">
          <div class="tr-card-icon" style="background:#FFF1EA">👥</div>
          <div><h3>گزارش کاربران (مدیر/مدیر فروش)</h3></div>
        </div>
        <div class="rpt-mock-bars" aria-hidden="true">
          <div class="rpt-mock-bar" style="height:50%;background:var(--ember)"></div>
          <div class="rpt-mock-bar" style="height:75%;background:var(--ember)"></div>
          <div class="rpt-mock-bar" style="height:40%;background:var(--ember)"></div>
          <div class="rpt-mock-bar" style="height:65%;background:var(--ember)"></div>
          <div class="rpt-mock-bar" style="height:30%;background:var(--ember)"></div>
        </div>
        <ul class="tr-list">
          <li>مقایسه تک‌تک کارشناس‌های زیرمجموعه با هم</li>
          <li>کلیک روی هر نفر برای دیدن جزئیات روزانه‌ش</li>
          <li>سهم هر نفر از مشتری‌ها و مخاطبین جدید</li>
        </ul>
      </div>

      <div class="tr-card reveal">
        <div class="tr-card-head">
          <div class="tr-card-icon" style="background:#E7F7F3">🏢</div>
          <div><h3>گزارش مدیران (ادمین)</h3></div>
        </div>
        <div class="rpt-mock-bars" aria-hidden="true">
          <div class="rpt-mock-bar" style="height:70%;background:var(--teal)"></div>
          <div class="rpt-mock-bar" style="height:45%;background:var(--teal)"></div>
          <div class="rpt-mock-bar" style="height:85%;background:var(--teal)"></div>
        </div>
        <ul class="tr-list">
          <li>مقایسه کل تیم‌های زیرمجموعه هر مدیر فروش با هم</li>
          <li>جمع فعالیت، مشتری و نرخ تکمیل هر تیم</li>
          <li>مناسب برای تصمیم‌گیری در سطح شرکت</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- BACKUP -->
<section class="section anchor-offset" id="backup" style="background:var(--paper-2)">
  <div class="container">
    <div class="section-head reveal">
      <div class="section-eyebrow">پشتیبان‌گیری</div>
      <h2 class="section-title">داده‌هات همیشه قابل خروج گرفتنه</h2>
      <p class="section-desc">از منوی «پشتیبان‌گیری» (فقط برای مدیر و سوپر ادمین).</p>
    </div>

    <div class="bk-grid reveal">
      <div class="bk-card">
        <div class="bk-card-icon" style="background:#E7F7F3">📊</div>
        <h3>خروجی اکسل</h3>
        <p>یک فایل با ۶ شیت جدا: کاربران، مشتریان، مخاطبین، فعالیت‌ها، تسک‌ها و صنایع. محدود به داده‌های شرکت خودت — برای آرشیو یا ارائه به بیرون از سیستم.</p>
      </div>
      <div class="bk-card">
        <div class="bk-card-icon" style="background:#E8F0FE">🗄️</div>
        <h3>خروجی کامل SQL</h3>
        <p>پشتیبان کامل از کل دیتابیس (فقط سوپر ادمین) — شامل ساختار جداول و همه داده‌ها، برای بازگردانی کامل سیستم در صورت نیاز.</p>
      </div>
    </div>
  </div>
</section>

<!-- FINAL CTA -->
<section class="section">
  <div class="container">
    <div class="cta-band reveal">
      <h2>همین الان شروع کن</h2>
      <p>۱۴ روز رایگان، بدون کارت بانکی، بدون تعهد.</p>
      <div class="cta-band-actions">
        <?php if ($is_logged_in): ?>
        <a href="index.php?page=dashboard" class="btn btn-primary btn-lg">رفتن به داشبورد</a>
        <?php else: ?>
        <a href="index.php?page=auth&mode=register" class="btn btn-primary btn-lg">شروع رایگان</a>
        <a href="landing.php" class="btn btn-ghost btn-lg" style="background:transparent;color:#fff;border-color:rgba(255,255,255,.3)">بازگشت به صفحه اصلی</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="foot">
  <div class="foot-inner">
    <div class="foot-logo">
      <span class="nav-logo-mark">پ</span> پیگیر
    </div>
    <div class="foot-links">
        <a href="landing.php">صفحه اصلی</a>
        <a href="#start">شروع</a>
        <a href="#flow">مشتری→تسک→فعالیت</a>
        <a href="#team">تیم</a>
        <a href="#reports">گزارش</a>
        <a href="#backup">پشتیبان‌گیری</a>
    </div>
    <div class="foot-copy">© <?= date('Y') ?> پیگیر — تمامی حقوق محفوظ است</div>
  </div>
</footer>

<?php if (!$is_logged_in): ?>
<!-- LOGIN MODAL -->
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
<?php endif; ?>

<script>
function openLoginModal(){
  var m = document.getElementById('loginModal');
  if (!m) return;
  m.classList.add('show');
  document.body.style.overflow = 'hidden';
}
function closeLoginModal(){
  var m = document.getElementById('loginModal');
  if (!m) return;
  m.classList.remove('show');
  document.body.style.overflow = '';
}
<?php if ($open_login_modal): ?>
window.addEventListener('DOMContentLoaded', openLoginModal);
<?php endif; ?>

(function(){
  var overlay = document.getElementById('loginModal');
  if (overlay) {
    overlay.addEventListener('click', function(e){ if (e.target === this) closeLoginModal(); });
  }
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeLoginModal(); });
})();

// Scroll reveal
(function(){
  var els = document.querySelectorAll('.reveal');
  var obs = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if (e.isIntersecting) { e.target.classList.add('in'); obs.unobserve(e.target); }
    });
  }, { threshold: 0.1 });
  els.forEach(function(el){ obs.observe(el); });
})();
</script>
</body>
</html>