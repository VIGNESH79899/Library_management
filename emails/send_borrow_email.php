<?php
/**
 * ============================================================
 *  ANTIGRAVITY LMS — Send Borrow Confirmation Email (SendGrid)
 *  File: emails/send_borrow_email.php
 * ============================================================
 */

declare(strict_types=1);

// ── Resolve project root ─────────────────────────────────────
$_EMAIL_ROOT = dirname(__DIR__);

require_once $_EMAIL_ROOT . '/config/env.php';
require_once $_EMAIL_ROOT . '/emails/EmailLogger.php';

/**
 * Send Borrow Confirmation Email using SendGrid API
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

    // Load environment
    Env::load(dirname(__DIR__) . '/.env');

    $apiKey = getenv('SENDGRID_API_KEY');

    if (!$apiKey) {
        error_log('[sendBorrowEmail] SENDGRID_API_KEY not set.');
        return [
            'success' => false,
            'message' => 'Mail system misconfigured.'
        ];
    }

    // Validate recipient email
    if (!filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'message' => "Invalid recipient email: {$student_email}"
        ];
    }

    $logger  = new EmailLogger($conn);
    $subject = "📚 Book Issued – {$book_title} | Antigravity Library";

    /* =========================================================
       Build Email Body (HTML Template)
    ========================================================= */

    $contact_email = "aadhevignesh65@gmail.com"; // Must be verified sender in SendGrid

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
       Send via SendGrid API (HTTPS - NOT SMTP)
    ========================================================= */

    $payload = [
        "personalizations" => [[
            "to" => [[
                "email" => $student_email,
                "name"  => $student_name
            ]],
            "subject" => $subject
        ]],
        "from" => [
            "email" => $contact_email,
            "name"  => "Antigravity Library"
        ],
        "content" => [
            [
                "type"  => "text/plain",
                "value" => $text_body
            ],
            [
                "type"  => "text/html",
                "value" => $html_body
            ]
        ]
    ];

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL            => "https://api.sendgrid.com/v3/mail/send",
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer {$apiKey}",
            "Content-Type: application/json"
        ],
        CURLOPT_TIMEOUT        => 15
    ]);

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);

    curl_close($ch);

    if ($error) {
        error_log("[sendBorrowEmail] cURL Error: {$error}");
    }

    if ($status >= 200 && $status < 300) {

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

    } else {

        // Log failure
        $logger->failure(
            $member_db_id,
            'BORROW_CONFIRMATION',
            $student_email,
            $subject,
            $response ?: $error
        );

        error_log("[sendBorrowEmail] SendGrid failed. Status: {$status} Response: {$response}");

        return [
            'success' => false,
            'message' => 'Email delivery failed.'
        ];
    }
}