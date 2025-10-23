

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login OTP table
CREATE TABLE IF NOT EXISTS `login_otp` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `otp` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME NOT NULL,
  `is_used` TINYINT(1) NOT NULL DEFAULT 0,
  `ip_address` VARCHAR(45) NULL,
  `attempt_count` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_user_email` (`user_id`, `email`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_login_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Account lockout table
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `user_id` INT UNSIGNED NOT NULL,
  `failed_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `last_failed_at` DATETIME NULL,
  `locked_until` DATETIME NULL,
  PRIMARY KEY (`user_id`),
  KEY `idx_locked_until` (`locked_until`),
  CONSTRAINT `fk_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Server-side session storage
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(128) NOT NULL,
  `user_id` INT UNSIGNED NULL,
  `data` LONGBLOB NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `last_activity` DATETIME NULL,
  `expires_at` DATETIME NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Persistent login tokens (Remember Me)
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `selector` CHAR(16) NOT NULL,
  `validator_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` DATETIME NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_selector` (`selector`),
  CONSTRAINT `fk_remember_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;