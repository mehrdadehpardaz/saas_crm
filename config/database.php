<?php
// config/database.php

// ── تنظیم تایم‌زون — باید همیشه اولین کار این فایل باشد ──
// اگر این خط نباشد، PHP به‌صورت پیش‌فرض (در اکثر هاست‌ها) از UTC استفاده
// می‌کند، نه وقت تهران (UTC+3:30). این تابع باید قبل از هر date()/time()
// دیگری در کل پروژه اجرا شود — این فایل چون توسط هر نقطه ورودی (index.php
// و فایل‌های مستقل api/*.php) require می‌شود، امن‌ترین و مرکزی‌ترین محل است.
date_default_timezone_set('Asia/Tehran');

// ── مشخصات اتصال به دیتابیس — هاست اشتراکی ──
define('DB_HOST', 'localhost');
define('DB_NAME', 'crm_saas');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            // هماهنگ‌سازی تایم‌زون خود MySQL با تهران. روی برخی هاست‌های
            // اشتراکی محدود ممکن است این دستور با خطای دسترسی مواجه شود؛
            // در آن صورت کافی است خط زیر را موقتاً کامنت کنید — تنظیم
            // date_default_timezone_set بالا به‌تنهایی هم مشکل اصلی را حل می‌کند.
            $pdo->exec("SET time_zone = '+03:30'");
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}