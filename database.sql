-- database.sql
CREATE DATABASE IF NOT EXISTS crm_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crm_saas;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `mobile` VARCHAR(15) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('admin','manager','agent') NOT NULL DEFAULT 'agent',
  `parent_id` INT DEFAULT NULL,
  `plan_type` ENUM('trial','monthly','yearly') NOT NULL DEFAULT 'trial',
  `plan_expiry` DATETIME NOT NULL DEFAULT (CURRENT_TIMESTAMP + INTERVAL 14 DAY),
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `industries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- مقادیر پیش‌فرض صنایع
INSERT INTO `industries` (`title`) VALUES 
('فولاد'), ('سیمان'), ('ساختمانی'), ('پتروشیمی'), 
('نفت و گاز'), ('خودروسازی'), ('فناوری اطلاعات'), ('مخابرات'),
('داروسازی'), ('غذایی'), ('نساجی'), ('معدن');

CREATE TABLE `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `industry_id` INT DEFAULT NULL,
  `company_name` VARCHAR(150) NOT NULL,
  `contact_person` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`industry_id`) REFERENCES `industries`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `contacts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `position` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `activities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `contact_id` INT DEFAULT NULL,
  `type` ENUM('call','meeting','email','note') NOT NULL DEFAULT 'call',
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `task_id` INT DEFAULT NULL AFTER `customer_id`,

  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `subscriptions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `plan` VARCHAR(20) NOT NULL,
  `amount` DECIMAL(10,0) DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `status` ENUM('active','completed','cancelled') DEFAULT 'active',
  `next_followup_date` DATETIME DEFAULT NULL,
  `next_followup_topic` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE `users` 
ADD COLUMN `company_name` VARCHAR(150) DEFAULT NULL AFTER `full_name`,
ADD COLUMN `position_title` VARCHAR(100) DEFAULT NULL AFTER `company_name`,
ADD COLUMN `phone` VARCHAR(20) DEFAULT NULL AFTER `position_title`;

ALTER TABLE `users` MODIFY COLUMN `role` ENUM('super_admin','admin','manager','agent') NOT NULL DEFAULT 'agent';

-- اضافه کردن ستون status به customers
ALTER TABLE `customers` 
ADD COLUMN `status` ENUM('active','inactive') NOT NULL DEFAULT 'active' AFTER `notes`;

-- اضافه کردن ستون status به contacts
ALTER TABLE `contacts` 
ADD COLUMN `status` ENUM('active','inactive') NOT NULL DEFAULT 'active' AFTER `is_primary`;

-- آپدیت رکوردهای موجود
UPDATE `customers` SET `status` = 'active';
UPDATE `contacts` SET `status` = 'active';

-- جدول پلن‌ها
CREATE TABLE IF NOT EXISTS `plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('individual','company') NOT NULL,
  `max_users` INT NOT NULL DEFAULT 1,
  `price_monthly` DECIMAL(10,0) NOT NULL DEFAULT 0,
  `price_yearly` DECIMAL(10,0) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- پلن‌های پیش‌فرض
INSERT INTO `plans` (`name`, `type`, `max_users`, `price_monthly`, `price_yearly`) VALUES
('کاربر عادی', 'individual', 1, 100000, 1000000),
('شرکت ۳ کاربره', 'company', 3, 500000, 5000000),
('شرکت ۵ کاربره', 'company', 5, 700000, 7000000),
('شرکت ۱۰ کاربره', 'company', 10, 1200000, 12000000);

-- اضافه کردن فیلدهای جدید به users
ALTER TABLE `users` 
ADD COLUMN `credit` DECIMAL(10,0) DEFAULT 0 AFTER `plan_expiry`,
ADD COLUMN `max_users_limit` INT DEFAULT 1 AFTER `credit`,
ADD COLUMN `plan_id` INT DEFAULT NULL AFTER `max_users_limit`,
ADD FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE SET NULL;

ALTER TABLE `users` 
ADD COLUMN `status` ENUM('active','inactive') NOT NULL DEFAULT 'active' AFTER `role`;

-- ۱. حذف FK از users
ALTER TABLE `users` DROP FOREIGN KEY `users_ibfk_3`;

-- ۲. حالا میتونی plans رو drop کنی
DROP TABLE IF EXISTS `plans`;

-- ۳. ساخت جدول جدید
CREATE TABLE `plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('base','per_user') NOT NULL,
  `price_monthly` DECIMAL(10,0) NOT NULL DEFAULT 0,
  `price_yearly` DECIMAL(10,0) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ۴. درج دو سطر
INSERT INTO `plans` (`name`, `type`, `price_monthly`, `price_yearly`) VALUES
('هزینه پایه (اکانت اصلی)', 'base', 100000, 1000000),
('هزینه هر کاربر اضافه', 'per_user', 100000, 1000000);

-- ۵. دوباره FK رو اضافه کن
ALTER TABLE `users` ADD FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE SET NULL;

ALTER TABLE tasks MODIFY status ENUM('active','completed','cancelled','sold') NOT NULL DEFAULT 'active';