#!/usr/bin/env php
<?php
/**
 * ============================================================
 *  ANTIGRAVITY LMS — Automated Due-Date Reminder Cron Script
 *  File: cron/send_due_reminders.php
 *
 *  Purpose:
 *    Send a reminder email to every member whose book is due
 *    exactly 2 days from now, but hasn't been reminded yet.
 *    Marks Reminder_Sent = 1 after each successful send to
 *    guarantee one email per loan, no matter how often the
 *    script runs.
 *
 *  Scheduling:
 *    Linux  : 0 7 * * * /usr/bin/php /path/to/cron/send_due_reminders.php >> /var/log/lms_reminders.log 2>&1
 *    Windows: See WINDOWS_TASK_SCHEDULER.md in this directory.
 *
 *  Dependencies:
 *    - composer require phpmailer/phpmailer
 *    - Issue table must have Reminder_Sent column (run migration first)
 * ============================================================
 */

declare(strict_types=1);

// ── Guard: CLI only ──────────────────────────────────────────
// Prevent this script from being triggered via a browser URL
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die("Access denied. Run via CLI only.\n");
}

// ── Bootstrap ────────────────────────────────────────────────
define('ROOT_DIR', dirname(__DIR__));
define('LOG_FILE', ROOT_DIR . '/logs/reminders.log');

// Ensure log directory exists
if (!is_dir(ROOT_DIR . '/logs')) {
    mkdir(ROOT_DIR . '/logs', 0755, true);
}

require_once ROOT_DIR . '/config/db.php';           // provides $conn (mysqli)
require_once ROOT_DIR . '/vendor/autoload.php';     // PHPMailer via Composer
require_once ROOT_DIR . '/config/mail_config.php';  // Env + MailConfig (no hardcoded creds)
require_once ROOT_DIR . '/emails/EmailLogger.php';  // Email_Log table helper

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── Logger ───────────────────────────────────────────────────
$emailLogger = new EmailLogger($conn);

/**
 * Write a timestamped line to the log file AND stdout.
 */
function cronLog(string $level, string $message): void
{
    $line = sprintf("[%s] [%s] %s\n", date('Y-m-d H:i:s'), strtoupper($level), $message);
    file_put_contents(LOG_FILE, $line, FILE_APPEND | LOCK_EX);
    echo $line;
}

// ── Load & validate SMTP config from .env ────────────────────
try {
    $mailCfg = MailConfig::load();
} catch (RuntimeException $e) {
    cronLog('error', 'Mail config error: ' . $e->getMessage());
    cronLog('error', 'Ensure .env contains SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_FROM.');
    exit(2);
}

// ── Script start ──────────────────────────────────────────────
cronLog('info', '════════════════════════════════════════');
cronLog('info', 'Due-date reminder job started.');
cronLog('info', 'Target date: ' . date('Y-m-d', strtotime('+2 days')));

// ── Step 1: Ensure Reminder_Sent column exists ────────────────
// Idempotent — safe to run every time, adds column only if missing
$conn->query("
    ALTER TABLE `Issue`
    ADD COLUMN IF NOT EXISTS `Reminder_Sent` TINYINT(1) NOT NULL DEFAULT 0
");

// ── Step 2: Fetch eligible issues ────────────────────────────
//
// Conditions:
//   a) Due date is exactly TODAY + 2 days (morning-of cron window)
//   b) Reminder_Sent = 0 (not yet emailed)
//   c) Book has NOT been returned (not in Return_Book)
//   d) Member has a valid email address
//
// Uses prepared statement — no interpolation.
//
$fetch_sql = "
    SELECT
        I.Issue_ID,
        I.Member_ID,
        I.Due_Date,
        B.Title            AS Book_Title,
        M.Member_Name,
        M.Email            AS Member_Email
    FROM  Issue I
    JOIN  Book   B  ON I.Book_ID   = B.Book_ID
    JOIN  Member M  ON I.Member_ID = M.Member_ID
    WHERE I.Due_Date     = DATE_ADD(CURDATE(), INTERVAL 2 DAY)
      AND I.Reminder_Sent = 0
      AND M.Email IS NOT NULL
      AND M.Email != ''
      AND I.Issue_ID NOT IN (SELECT Issue_ID FROM Return_Book)
    ORDER BY I.Issue_ID ASC
";

$fetch_stmt = $conn->prepare($fetch_sql);
if (!$fetch_stmt) {
    cronLog('error', 'DB prepare failed: ' . $conn->error);
    exit(1);
}

$fetch_stmt->execute();
$result = $fetch_stmt->get_result();
$total  = $result->num_rows;

cronLog('info', "Found $total issue(s) due for reminders.");

if ($total === 0) {
    cronLog('info', 'Nothing to send. Job complete.');
    exit(0);
}

// ── Step 3: Prepare the UPDATE statement (reused in loop) ────
$update_sql  = "UPDATE `Issue` SET `Reminder_Sent` = 1 WHERE `Issue_ID` = ?";
$update_stmt = $conn->prepare($update_sql);
if (!$update_stmt) {
    cronLog('error', 'DB update prepare failed: ' . $conn->error);
    exit(1);
}

// ── Step 4: Process each issue ───────────────────────────────
$sent_count   = 0;
$failed_count = 0;

while ($row = $result->fetch_assoc()) {

    $issue_id     = (int)   $row['Issue_ID'];
    $member_id    = (int)   $row['Member_ID'];
    $member_name  = (string)$row['Member_Name'];
    $member_email = (string)$row['Member_Email'];
    $book_title   = (string)$row['Book_Title'];
    $due_date_raw = (string)$row['Due_Date'];
    $due_date     = date('M d, Y', strtotime($due_date_raw));
    $member_id_str = sprintf('ARI-%04d', $member_id);
    $days_left    = 2;
    $contact_email = $mailCfg->contactEmail;

    $subject = "⏰ Return Reminder: \"{$book_title}\" due in 2 days | Antigravity Library";

    cronLog('info', "Processing Issue #{$issue_id} — {$member_name} <{$member_email}> — \"{$book_title}\"");

    // ── Build HTML body from template ────────────────────────
    ob_start();
    include ROOT_DIR . '/emails/reminder_template.php';
    $html_body = ob_get_clean();

    $text_body =
        "Hi {$member_name},\n\n" .
        "This is a reminder that your borrowed book is due in {$days_left} day(s).\n\n" .
        "Book     : {$book_title}\n" .
        "Due Date : {$due_date}\n" .
        "Member ID: {$member_id_str}\n\n" .
        "Please return the book before the due date to avoid late fines (₹10/day).\n\n" .
        "— Antigravity Library\n" . $mailCfg->contactEmail;

    // ── Send email ───────────────────────────────────────────
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $mailCfg->host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailCfg->user;
        $mail->Password   = $mailCfg->pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $mailCfg->port;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($mailCfg->from, $mailCfg->fromName);
        $mail->addAddress($member_email, $member_name);
        $mail->addReplyTo($mailCfg->from, $mailCfg->fromName);

        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body    = $html_body;
        $mail->AltBody = $text_body;

        $mail->send();

        // ✅ Email delivered — log success
        $emailLogger->success($member_id, 'REMINDER', $member_email, $subject);

        // ✅ Mark reminder sent (prevents duplicates)
        $update_stmt->bind_param('i', $issue_id);
        $update_stmt->execute();

        cronLog('info', "  ✓ Sent & marked — Issue #{$issue_id}");
        $sent_count++;

    } catch (Exception $e) {
        $error = $mail->ErrorInfo;

        // ❌ Log failure — do NOT mark Reminder_Sent so it retries
        $emailLogger->failure($member_id, 'REMINDER', $member_email, $subject, $error);

        cronLog('error', "  ✗ FAILED Issue #{$issue_id}: {$error}");
        $failed_count++;
    }

    // Throttle — avoid SMTP rate-limit on bulk sends
    usleep(300_000); // 300 ms between emails
}

// ── Step 5: Summary ──────────────────────────────────────────
$update_stmt->close();
$fetch_stmt->close();
$conn->close();

cronLog('info', '────────────────────────────────────────');
cronLog('info', "Job complete. Sent: {$sent_count} | Failed: {$failed_count} | Total: {$total}");
cronLog('info', '════════════════════════════════════════');

exit($failed_count > 0 ? 1 : 0); // Non-zero exit code on any failure
