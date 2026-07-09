-- database_fixed.sql
-- آماده‌سازی شده برای cPanel - نام دیتابیس را از cPanel وارد کنید

SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- =============================================
-- جدول users
-- =============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `mobile` VARCHAR(15) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `company_name` VARCHAR(150) DEFAULT NULL,
  `position_title` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('super_admin','admin','manager','agent') NOT NULL DEFAULT 'agent',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `parent_id` INT DEFAULT NULL,
  `plan_type` ENUM('trial','monthly','yearly') NOT NULL DEFAULT 'trial',
  `plan_expiry` DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP + INTERVAL 14 DAY),
  `credit` DECIMAL(10,0) DEFAULT 0,
  `max_users_limit` INT DEFAULT 1,
  `plan_id` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول industries
-- =============================================
CREATE TABLE IF NOT EXISTS `industries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `industries` (`title`) VALUES 
('فولاد'), ('سیمان'), ('ساختمانی'), ('پتروشیمی'), 
('نفت و گاز'), ('خودروسازی'), ('فناوری اطلاعات'), ('مخابرات'),
('داروسازی'), ('غذایی'), ('نساجی'), ('معدن');

-- =============================================
-- جدول plans
-- =============================================
CREATE TABLE IF NOT EXISTS `plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('base','per_user') NOT NULL,
  `price_monthly` DECIMAL(10,0) NOT NULL DEFAULT 0,
  `price_yearly` DECIMAL(10,0) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `plans` (`name`, `type`, `price_monthly`, `price_yearly`) VALUES
('هزینه پایه (اکانت اصلی)', 'base', 100000, 1000000),
('هزینه هر کاربر اضافه', 'per_user', 100000, 1000000);

-- اضافه کردن FK پلن به users
ALTER TABLE `users` ADD CONSTRAINT `fk_users_plan` 
  FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE SET NULL;

-- =============================================
-- جدول customers
-- =============================================
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `industry_id` INT DEFAULT NULL,
  `company_name` VARCHAR(150) NOT NULL,
  `contact_person` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`industry_id`) REFERENCES `industries`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول contacts
-- =============================================
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `position` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول tasks (قبل از activities باید ساخته شود)
-- =============================================
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `status` ENUM('active','completed','cancelled','sold') NOT NULL DEFAULT 'active',
  `next_followup_date` DATETIME DEFAULT NULL,
  `next_followup_topic` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول activities (بعد از tasks)
-- =============================================
CREATE TABLE IF NOT EXISTS `activities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `task_id` INT DEFAULT NULL,
  `contact_id` INT DEFAULT NULL,
  `type` ENUM('call','meeting','email','note') NOT NULL DEFAULT 'call',
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول subscriptions
-- =============================================
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `plan` VARCHAR(20) NOT NULL,
  `amount` DECIMAL(10,0) DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;

-- پایان


-- migration_companies.sql
-- تغییر رویه‌ای: جدول مستقل «شرکت‌ها» + برچسب company_id روی هر رکورد
-- (کاربر، مشتری، مخاطب، تسک، فعالیت) علاوه بر «سازنده»‌اش (user_id).
--
-- تا قبل از این، تنها راه تشخیص «این رکورد مال کدوم شرکته» رشته‌ی
-- users.company_name بود که باید هر بار از طریق user_id دنبال می‌شد.
-- این مهاجرت یک منبع واحد (companies) می‌سازه و company_id رو مستقیم
-- روی خودِ هر جدول کپی می‌کنه — هم برای فیلتر/گزارش سریع‌تر، هم برای
-- این‌که اگه بعداً یوزری بین شرکت‌ها جابه‌جا شد، رکوردهای قدیمی‌اش
-- برچسب شرکتِ زمانِ ساختشون رو نگه دارن.

USE crm_saas;

-- ── ۱) جدول شرکت‌ها ──
CREATE TABLE IF NOT EXISTS `companies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL UNIQUE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ── ۳) اضافه کردن company_id به users و پر کردنش ──
ALTER TABLE `users` ADD COLUMN `company_id` INT DEFAULT NULL AFTER `company_name`;


ALTER TABLE `users`
  ADD FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL;

-- ── ۴) customers: company_id از روی صاحب مشتری (user_id) ──
ALTER TABLE `customers` ADD COLUMN `company_id` INT DEFAULT NULL AFTER `user_id`;


ALTER TABLE `customers`
  ADD FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL;

-- ── ۵) contacts: company_id از روی مشتریِ صاحبِ مخاطب ──
ALTER TABLE `contacts` ADD COLUMN `company_id` INT DEFAULT NULL AFTER `customer_id`;


ALTER TABLE `contacts`
  ADD FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL;

-- ── ۶) tasks: company_id از روی مشتریِ همان تسک ──
ALTER TABLE `tasks` ADD COLUMN `company_id` INT DEFAULT NULL AFTER `customer_id`;


ALTER TABLE `tasks`
  ADD FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL;

-- ── ۷) activities: company_id از روی مشتریِ همان فعالیت ──
ALTER TABLE `activities` ADD COLUMN `company_id` INT DEFAULT NULL AFTER `customer_id`;


ALTER TABLE `activities`
  ADD FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL;

  -- migration_contacts_user_id.sql
-- اضافه کردن user_id به جدول contacts، تا مشخص باشه دقیقاً کدوم کاربر
-- این مخاطب رو ساخته — جدا از customer_id (مشتری صاحب مخاطب) و
-- company_id (سازمان). تا الان تنها راه حدس زدن «سازنده»، صاحبِ خودِ
-- مشتری (customers.user_id) بود که همیشه درست نیست (مثلاً وقتی مدیر یا
-- مدیرفروش یک مخاطب رو برای مشتریِ یک کارشناسِ دیگه ثبت می‌کنه).

ALTER TABLE `contacts` ADD COLUMN `user_id` INT DEFAULT NULL AFTER `customer_id`;

ALTER TABLE `contacts`
  ADD FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- migration_manual_deactivation_flag.sql
-- رفع تناقض: وقتی ادمین یک کاربر رو دستی غیرفعال می‌کنه، ولی سقف پلن
-- هنوز جا داره (مثلاً سقف ۵ نفر و فقط ۳ کاربر فعال)، منطق خودکارِ
-- «پر کردن ظرفیت خالی» (که هر بار توی index.php و بعد از خرید/تمدید پلن
-- اجرا می‌شه) همون کاربر رو بدون توجه به تصمیم دستی ادمین دوباره فعال
-- می‌کرد. علتش این بود که سیستم هیچ فرقی بین «غیرفعال چون سقف پر شده
-- بود» و «غیرفعال چون ادمین خودش خواسته» نمی‌ذاشت.

ALTER TABLE `users`
  ADD COLUMN `deactivated_manually` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`;