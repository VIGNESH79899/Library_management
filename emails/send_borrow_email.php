<?php
/**
 * ============================================================
 *  ANTIGRAVITY LMS — Send Borrow Confirmation Email
 *  File: emails/send_borrow_email.php
 * ============================================================
 */

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── Resolve project root ─────────────────────────────────────
$_EMAIL_ROOT = dirname(__DIR__);

require_once $_EMAIL_ROOT . '/vendor/autoload.php';
require_once $_EMAIL_ROOT . '/config/mail_config.php';
require_once $_EMAIL_ROOT . '/emails/EmailLogger.php';

/**
 * Send Borrow Confirmation Email
 */
function sendBorrowEmail(
    mysqli  $conn,
    ?int    $member_db_id,
    string  $student_name,
    string  $student_email,
    string  $book_title,
    string  $due_date,
    string  $issue_date = '',
    string  $member_id  = ''
): array {

    /* =========================================================
       1️⃣ Load SMTP Config
    ========================================================= */
    try {
        $cfg = MailConfig::load();
    } catch (RuntimeException $e) {
        error_log('[sendBorrowEmail] Config error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Mail system misconfigured: ' . $e->getMessage()
        ];
    }

    /* =========================================================
       2️⃣ Validate Email
    ========================================================= */
    if (!filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'message' => "Invalid recipient email: {$student_email}"
        ];
    }

    $logger        = new EmailLogger($conn);
    $contact_email = $cfg->contactEmail;
    $subject       = "📚 Book Issued – {$book_title} | Antigravity Library";

    /* =========================================================
       3️⃣ Build Email Body
    ========================================================= */

    // HTML Template Variables available inside template:
    // $student_name, $book_title, $due_date, $issue_date, $member_id, $contact_email
    ob_start();
    include __DIR__ . '/borrow_confirmation.php';
    $html_body = ob_get_clean();

    $text_body =
        "Hi {$student_name},\n\n" .
        "Your book \"{$book_title}\" has been issued successfully.\n\n" .
        "Issue Date : {$issue_date}\n" .
        "Due Date   : {$due_date}\n" .
        "Member ID  : {$member_id}\n\n" .
        "Please return before the due date to avoid fines (₹10/day).\n\n" .
        "— Antigravity Library\n{$contact_email}";

    /* =========================================================
       4️⃣ Send via PHPMailer
    ========================================================= */

    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) {
        error_log("SMTP DEBUG: $str");
    };

    try {

        $mail->isSMTP();
        $mail->Timeout = 15;      // stop waiting after 15 seconds
$mail->SMTPKeepAlive = false;
        $mail->Host       = $cfg->host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $cfg->user;
        $mail->Password   = $cfg->pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $cfg->port;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($cfg->from, $cfg->fromName);
        $mail->addAddress($student_email, $student_name);
        $mail->addReplyTo($cfg->from, $cfg->fromName);

        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body    = $html_body;
        $mail->AltBody = $text_body;

        $mail->send();

        // Log success
        $logger->success(
            $member_db_id,
            'BORROW_CONFIRMATION',
            $student_email,
            $subject
        );

        return [
            'success' => true,
            'message' => 'Email sent successfully.'
        ];

    } catch (Exception $e) {

        $error = $mail->ErrorInfo;

        // Log failure
        $logger->failure(
            $member_db_id,
            'BORROW_CONFIRMATION',
            $student_email,
            $subject,
            $error
        );

        error_log("[sendBorrowEmail] Failed for {$student_email}: {$error}");

        return [
            'success' => false,
            'message' => 'Email delivery failed: ' . $error
        ];
    }
}