<?php
/**
 * ANTIGRAVITY LMS — Email Logger
 * File: emails/EmailLogger.php
 *
 * Singleton-style helper that:
 *   1. Writes every email attempt to the Email_Log table.
 *   2. Uses prepared statements (SQL-injection safe).
 *   3. Never throws — failures are silently error_log()'ed so
 *      the calling code never crashes because of logging.
 */

class EmailLogger
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
        $this->ensureTableExists();
    }

    // ── Public API ────────────────────────────────────────────

    /**
     * Log a successful email delivery.
     *
     * @param int|null $member_id       Member's PK (null for non-member emails)
     * @param string   $email_type      BORROW_CONFIRMATION | REMINDER | OVERDUE
     * @param string   $recipient_email
     * @param string   $subject
     */
    public function success(
        ?int   $member_id,
        string $email_type,
        string $recipient_email,
        string $subject
    ): void {
        $this->insert($member_id, $email_type, $recipient_email, $subject, 'SUCCESS', null);
    }

    /**
     * Log a failed email delivery.
     *
     * @param int|null $member_id
     * @param string   $email_type
     * @param string   $recipient_email
     * @param string   $subject
     * @param string   $error_message   PHPMailer ErrorInfo string
     */
    public function failure(
        ?int   $member_id,
        string $email_type,
        string $recipient_email,
        string $subject,
        string $error_message
    ): void {
        $this->insert($member_id, $email_type, $recipient_email, $subject, 'FAILED', $error_message);
    }

    // ── Private helpers ───────────────────────────────────────

    /**
     * Core INSERT using a prepared statement.
     * Errors here are non-fatal — silently logged to PHP error log.
     */
    private function insert(
        ?int    $member_id,
        string  $email_type,
        string  $recipient_email,
        string  $subject,
        string  $status,
        ?string $error_message
    ): void {
        try {
            $sql = "
                INSERT INTO email_log
                    (member_id, email_type, recipient_email, subject, status, error_message)
                VALUES
                    (?, ?, ?, ?, ?, ?)
            ";

            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new RuntimeException('Prepare failed: ' . $this->conn->error);
            }

            // member_id is nullable → use 'i' but bind null directly
            $stmt->bind_param(
                'isssss',
                $member_id,
                $email_type,
                $recipient_email,
                $subject,
                $status,
                $error_message
            );

            $stmt->execute();
            $stmt->close();

        } catch (Throwable $e) {
            // Logging must NEVER crash the application
            error_log('[EmailLogger] ' . $e->getMessage());
        }
    }

    /**
     * Create the table if it doesn't exist yet.
     * Allows zero-downtime deploy without running the migration manually.
     */
    private function ensureTableExists(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `email_log` (
                `id`              INT           NOT NULL AUTO_INCREMENT,
                `member_id`       INT           NULL,
                `email_type`      ENUM('BORROW_CONFIRMATION','REMINDER','OVERDUE') NOT NULL,
                `recipient_email` VARCHAR(255)  NOT NULL,
                `subject`         VARCHAR(500)  NOT NULL,
                `status`          ENUM('SUCCESS','FAILED') NOT NULL,
                `error_message`   TEXT          NULL,
                `sent_at`         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_sent_at`    (`sent_at`),
                INDEX `idx_member_id`  (`member_id`),
                INDEX `idx_status`     (`status`),
                INDEX `idx_email_type` (`email_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        // Non-fatal — table may already exist
        $this->conn->query($sql);
    }
}
