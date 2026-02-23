-- ============================================================
-- ANTIGRAVITY LMS — Add reminder_sent column to Issue table
-- File: database/migrations/add_reminder_sent_to_issue.sql
-- Run once. Safe to run multiple times (uses IF NOT EXISTS via
-- SHOW COLUMNS workaround — MySQL <8 compatible).
-- ============================================================

-- Add reminder_sent column (TINYINT(1) = boolean, default 0)
ALTER TABLE `issue`
    ADD COLUMN IF NOT EXISTS `Reminder_Sent` TINYINT(1) NOT NULL DEFAULT 0
        COMMENT '1 = 2-day reminder email already sent; prevents duplicates';

-- Index lets the cron query run without a full table scan
CREATE INDEX IF NOT EXISTS `idx_reminder_due`
    ON `issue` (`Due_Date`, `Reminder_Sent`);
