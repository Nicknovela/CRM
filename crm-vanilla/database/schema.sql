-- CRM Vanilla PHP — Database Schema
-- MySQL 8.0+ / MariaDB 10.5+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─── Users ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`                     INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`                   VARCHAR(191)     NOT NULL,
  `email`                  VARCHAR(191)     NOT NULL UNIQUE,
  `password`               VARCHAR(255)     NOT NULL,
  `role`                   ENUM('admin','manager','vendedor','viewer') NOT NULL DEFAULT 'vendedor',
  `is_active`              TINYINT(1)       NOT NULL DEFAULT 1,
  `timezone`               VARCHAR(100)     NOT NULL DEFAULT 'America/La_Paz',
  `created_at`             DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`             DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Verticals (pipelines) ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `verticals` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`             VARCHAR(191) NOT NULL,
  `slug`             VARCHAR(191) NOT NULL UNIQUE,
  `description`      TEXT         NULL,
  `color`            VARCHAR(7)   NOT NULL DEFAULT '#3B82F6',
  `is_active`        TINYINT(1)   NOT NULL DEFAULT 1,
  `track_commission` TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Stages ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `stages` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `vertical_id` INT UNSIGNED NOT NULL,
  `name`        VARCHAR(191) NOT NULL,
  `color`       VARCHAR(7)   NOT NULL DEFAULT '#6B7280',
  `position`    INT UNSIGNED NOT NULL DEFAULT 0,
  `is_won`      TINYINT(1)   NOT NULL DEFAULT 0,
  `is_lost`     TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_stages_vertical` FOREIGN KEY (`vertical_id`) REFERENCES `verticals` (`id`) ON DELETE CASCADE,
  INDEX `idx_stages_vertical_position` (`vertical_id`, `position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Clients ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `clients` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(191) NOT NULL,
  `company_name` VARCHAR(191) NULL,
  `email`        VARCHAR(191) NULL,
  `phone`        VARCHAR(50)  NULL,
  `industry`     VARCHAR(100) NULL,
  `website`      VARCHAR(255) NULL,
  `address`      TEXT         NULL,
  `notes`        TEXT         NULL,
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_clients_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Deals ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `deals` (
  `id`                   INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `title`                VARCHAR(255)     NOT NULL,
  `client_id`            INT UNSIGNED     NOT NULL,
  `vertical_id`          INT UNSIGNED     NOT NULL,
  `stage_id`             INT UNSIGNED     NOT NULL,
  `assigned_to`          INT UNSIGNED     NULL,
  `amount`               DECIMAL(15,2)    NOT NULL DEFAULT 0.00,
  `currency`             ENUM('BOB','USD','COP') NOT NULL DEFAULT 'BOB',
  `probability`          TINYINT UNSIGNED NOT NULL DEFAULT 50,
  `commission_rate`      DECIMAL(5,2)     NULL,
  `expected_close_date`  DATE             NULL,
  `actual_close_date`    DATE             NULL,
  `notes`                TEXT             NULL,
  `lost_reason`          TEXT             NULL,
  `deleted_at`           DATETIME         NULL,
  `created_at`           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_deals_client`   FOREIGN KEY (`client_id`)   REFERENCES `clients`   (`id`),
  CONSTRAINT `fk_deals_vertical` FOREIGN KEY (`vertical_id`) REFERENCES `verticals` (`id`),
  CONSTRAINT `fk_deals_stage`    FOREIGN KEY (`stage_id`)    REFERENCES `stages`    (`id`),
  CONSTRAINT `fk_deals_user`     FOREIGN KEY (`assigned_to`) REFERENCES `users`     (`id`) ON DELETE SET NULL,
  INDEX `idx_deals_stage`       (`stage_id`),
  INDEX `idx_deals_vertical`    (`vertical_id`),
  INDEX `idx_deals_assigned`    (`assigned_to`),
  INDEX `idx_deals_deleted`     (`deleted_at`),
  INDEX `idx_deals_close_date`  (`expected_close_date`),
  INDEX `idx_deals_client_active` (`client_id`, `deleted_at`, `amount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Activities ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `activities` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `deal_id`      INT UNSIGNED NOT NULL,
  `user_id`      INT UNSIGNED NOT NULL,
  `type`         ENUM('note','stage_change','call','email','meeting','other') NOT NULL DEFAULT 'note',
  `description`  TEXT         NOT NULL,
  `old_stage_id` INT UNSIGNED NULL,
  `new_stage_id` INT UNSIGNED NULL,
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_activities_deal`      FOREIGN KEY (`deal_id`)      REFERENCES `deals`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_activities_user`      FOREIGN KEY (`user_id`)      REFERENCES `users`  (`id`),
  CONSTRAINT `fk_activities_old_stage` FOREIGN KEY (`old_stage_id`) REFERENCES `stages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_activities_new_stage` FOREIGN KEY (`new_stage_id`) REFERENCES `stages` (`id`) ON DELETE SET NULL,
  INDEX `idx_activities_deal` (`deal_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
