-- SQL Schema for M CLUB Website (Hostinger/MySQL)
-- Import this into your Hostinger phpMyAdmin

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `full_name` VARCHAR(255) NOT NULL,
    `middle_name` VARCHAR(100) DEFAULT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `phone` VARCHAR(50),
    `shipping_address` TEXT DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `payment_receipt` VARCHAR(255),
    `profile_pic` VARCHAR(255) DEFAULT NULL,
    `gcash_number` VARCHAR(50) DEFAULT NULL,
    `bank_name` VARCHAR(100) DEFAULT NULL,
    `bank_number` VARCHAR(100) DEFAULT NULL,
    `referrer_id` INT(11) DEFAULT NULL,
    `password_reset_token` VARCHAR(64) DEFAULT NULL,
    `password_reset_expires` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- If your database was created before referrer tracking, run:
-- ALTER TABLE `users` ADD COLUMN `referrer_id` INT(11) DEFAULT NULL AFTER `bank_number`;

-- Password reset (forgot password). Run once if columns are missing:
-- ALTER TABLE `users` ADD COLUMN `password_reset_token` VARCHAR(64) DEFAULT NULL AFTER `referrer_id`;
-- ALTER TABLE `users` ADD COLUMN `password_reset_expires` DATETIME DEFAULT NULL AFTER `password_reset_token`;

-- Site-wide settings (e.g. business plan YouTube embeds). Table is also auto-created by the app.
CREATE TABLE IF NOT EXISTS `site_settings` (
    `setting_key` VARCHAR(64) NOT NULL PRIMARY KEY,
    `setting_value` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `leads` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50),
    `referrer_id` INT(11),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`referrer_id`) REFERENCES `users`(`id`)
);