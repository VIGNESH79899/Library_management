<?php
/**
 * borrow_book.php — Student borrow AJAX handler
 * Outputs ONLY clean JSON. No HTML. No stray output.
 */
ob_start(); // Capture any stray output (notices, whitespace)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_end_clean();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Session expired. Please log in again.';
    echo json_encode($response);
    exit;
}

include "../config/db.php";

// Email helper — loaded once, never sends until explicitly called
require_once __DIR__ . '/../emails/send_borrow_email.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$book_id     = (int)($_POST['book_id'] ?? 0);
$user_id     = (int)$_SESSION['user_id'];
$return_date = trim($_POST['return_date'] ?? '');

// Validate book_id
if ($book_id <= 0) {
    $response['message'] = 'Invalid book selected.';
    echo json_encode($response);
    exit;
}

// Validate return_date
$today    = date('Y-m-d');
$max_date = date('Y-m-d', strtotime('+60 days'));

if (empty($return_date)) {
    $response['message'] = 'Please select a return date.';
    echo json_encode($response);
    exit;
}
if ($return_date <= $today) {
    $response['message'] = 'Return date must be after today.';
    echo json_encode($response);
    exit;
}
if ($return_date > $max_date) {
    $response['message'] = 'Return date cannot exceed 60 days from today.';
    echo json_encode($response);
    exit;
}

// Check if book is available
$check = $conn->prepare("SELECT Status FROM book WHERE Book_ID = ?");
if (!$check) {
    $response['message'] = 'Database error: ' . $conn->error;
    echo json_encode($response);
    exit;
}
$check->bind_param("i", $book_id);
$check->execute();
$checkRes = $check->get_result()->fetch_assoc();
$check->close();

if (!$checkRes || $checkRes['Status'] !== 'Available') {
    $response['message'] = 'This book is no longer available.';
    echo json_encode($response);
    exit;
}

// Check if user already has this book currently
$dup = $conn->prepare("
    SELECT Issue_ID FROM issue
    WHERE Book_ID = ? AND Member_ID = ?
      AND Issue_ID NOT IN (SELECT Issue_ID FROM return_book)
");
$dup->bind_param("ii", $book_id, $user_id);
$dup->execute();
if ($dup->get_result()->num_rows > 0) {
    $response['message'] = 'You have already borrowed this book.';
    echo json_encode($response);
    exit;
}
$dup->close();

$issue_date = $today;
$due_date   = $return_date;

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO issue (Book_ID, Member_ID, Librarian_ID, Issue_Date, Due_Date) VALUES (?, ?, 1, ?, ?)");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("iiss", $book_id, $user_id, $issue_date, $due_date);
    $stmt->execute();
    $stmt->close();

    $upd = $conn->prepare("UPDATE book SET Status='Issued' WHERE Book_ID=?");
    $upd->bind_param("i", $book_id);
    $upd->execute();
    $upd->close();

    $conn->commit();

    // ──────────────────────────────────────────────────────────
    // ✉  Send borrow confirmation email
    //    This block is intentionally isolated from the borrow
    //    transaction — an email failure NEVER rolls back the
    //    issue record or changes the HTTP success response.
    // ──────────────────────────────────────────────────────────
    $emailNote = '';
    try {
        // Prefer session-cached email (set at login).
        // Fall back to a DB query for sessions that pre-date this change.
        $student_name  = $_SESSION['user_name']  ?? '';
        $student_email = $_SESSION['user_email'] ?? '';

        if (empty($student_email)) {
            // Legacy session: fetch from DB
            $memStmt = $conn->prepare(
                'SELECT Member_Name, Email FROM member WHERE Member_ID = ? LIMIT 1'
            );
            if ($memStmt) {
                $memStmt->bind_param('i', $user_id);
                $memStmt->execute();
                $memRow = $memStmt->get_result()->fetch_assoc();
                $memStmt->close();
                $student_name  = $memRow['Member_Name'] ?? $student_name;
                $student_email = $memRow['Email']        ?? '';
            }
        }

        if (!empty($student_email)) {
            // Fetch book title
            $bkStmt = $conn->prepare(
                'SELECT Title FROM book WHERE Book_ID = ? LIMIT 1'
            );
            $book_title = 'Your Book';
            if ($bkStmt) {
                $bkStmt->bind_param('i', $book_id);
                $bkStmt->execute();
                $bkRow = $bkStmt->get_result()->fetch_assoc();
                $bkStmt->close();
                $book_title = $bkRow['Title'] ?? 'Your Book';
            }

            $emailResult = sendBorrowEmail(
                conn:         $conn,
                member_db_id: $user_id,
                student_name: $student_name,
                student_email:$student_email,
                book_title:   $book_title,
                due_date:     date('M d, Y', strtotime($due_date)),
                issue_date:   date('M d, Y'),
                member_id:    'ARI-' . sprintf('%04d', $user_id)
            );

            if (!$emailResult['success']) {
                error_log('[borrow_book] Email failed for user ' . $user_id . ': ' . $emailResult['message']);
                $emailNote = ' (Email notification could not be sent.)';
            }
        }
    } catch (Throwable $emailEx) {
        error_log('[borrow_book] Unexpected email error: ' . $emailEx->getMessage());
    }
    // ── End email block ───────────────────────────────────────


    $response['success']  = true;
    $response['message']  = 'Book borrowed successfully! Please return by '
                          . date('M d, Y', strtotime($due_date))
                          . $emailNote;
    $response['due_date'] = date('M d, Y', strtotime($due_date));


} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = 'Failed to borrow book: ' . $e->getMessage();
}

echo json_encode($response);
exit;
?>
