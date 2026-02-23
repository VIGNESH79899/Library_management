-- ============================================================
-- ANTIGRAVITY LMS — Email Log Table Migration
-- File: database/migrations/create_email_logs_table.sql
-- Run once against your LibraryManagementSystem database.
-- ============================================================

CREATE TABLE IF NOT EXISTS `Email_Log` (
    `id`              INT            NOT NULL AUTO_INCREMENT,
    `member_id`       INT            NULL COMMENT 'FK → Member/UserAccounts (nullable for system emails)',
    `email_type`      ENUM(
                          'BORROW_CONFIRMATION',
                          'REMINDER',
                          'OVERDUE'
                      )              NOT NULL,
    `recipient_email` VARCHAR(255)   NOT NULL,
    `subject`         VARCHAR(500)   NOT NULL,
    `status`          ENUM(
                          'SUCCESS',
                          'FAILED'
                      )              NOT NULL,
    `error_message`   TEXT           NULL     COMMENT 'PHPMailer ErrorInfo on failure',
    `sent_at`         TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),

    -- Index for fast admin queries (sort by latest, filter by type/status)
    INDEX `idx_sent_at`    (`sent_at` DESC),
    INDEX `idx_member_id`  (`member_id`),
    INDEX `idx_status`     (`status`),
    INDEX `idx_email_type` (`email_type`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Logs every outbound email attempt made by the LMS';
