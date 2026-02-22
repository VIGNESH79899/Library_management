<?php
/**
 * return_book.php  — Student early-return AJAX handler
 * Returns pure JSON. Must NOT output any HTML.
 */

// Start session manually (do NOT include header.php — that outputs HTML!)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect non-logged-in users gracefully for JSON context
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
    exit;
}

include "../config/db.php";

header('Content-Type: application/json');
$response = ['success' => false, 'message' => '', 'fine' => 0, 'days_late' => 0, 'on_time' => true];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$issue_id = (int)($_POST['issue_id'] ?? 0);
$user_id  = (int)$_SESSION['user_id'];

if ($issue_id <= 0) {
    $response['message'] = 'Invalid issue ID.';
    echo json_encode($response);
    exit;
}

define('FINE_RATE_PER_DAY', 10.00);
$fine_rate   = (float)FINE_RATE_PER_DAY;
$return_date = date('Y-m-d');

// ── Security: verify issue belongs to THIS student and is still active ──────
$chk = $conn->prepare("
    SELECT I.Issue_ID, I.Book_ID, I.Member_ID, I.Due_Date, B.Title
    FROM Issue I
    JOIN Book B ON I.Book_ID = B.Book_ID
    WHERE I.Issue_ID = ?
      AND I.Member_ID = ?
      AND B.Status = 'Issued'
      AND I.Issue_ID NOT IN (SELECT Issue_ID FROM Return_Book)
");

if (!$chk) {
    $response['message'] = 'Database error: ' . $conn->error;
    echo json_encode($response);
    exit;
}

$chk->bind_param("ii", $issue_id, $user_id);
$chk->execute();
$row = $chk->get_result()->fetch_assoc();
$chk->close();

if (!$row) {
    $response['message'] = 'Book not found or has already been returned.';
    echo json_encode($response);
    exit;
}

$book_id   = (int)$row['Book_ID'];
$member_id = (int)$row['Member_ID'];
$due_date  = $row['Due_Date'];
$title     = $row['Title'];

// ── Fine calculation (0 if returned on time or early) ───────────────────────
$due_ts    = strtotime($due_date);
$today_ts  = strtotime($return_date);
$days_late = ($today_ts > $due_ts) ? (int)floor(($today_ts - $due_ts) / 86400) : 0;
$fine      = (float)($days_late * $fine_rate);

// ── Transaction ──────────────────────────────────────────────────────────────
$conn->begin_transaction();
try {
    // 1. Record the return
    $s1 = $conn->prepare("INSERT INTO Return_Book (Issue_ID, Return_Date, Fine_Amount) VALUES (?, ?, ?)");
    if (!$s1) throw new Exception("Prepare s1 failed: " . $conn->error);
    $s1->bind_param("isd", $issue_id, $return_date, $fine);
    $s1->execute();
    $return_id = (int)$conn->insert_id;
    $s1->close();

    // 2. Insert fine record only if overdue
    if ($days_late > 0) {
        // Check if Fine table has Return_ID column
        $hasCols = $conn->query("SHOW COLUMNS FROM Fine LIKE 'Return_ID'")->num_rows > 0;

        if ($hasCols) {
            $s2 = $conn->prepare("
                INSERT INTO Fine (Issue_ID, Return_ID, Member_ID, Due_Date, Return_Date, Days_Late, Fine_Rate, Fine_Amount)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if (!$s2) throw new Exception("Prepare s2 failed: " . $conn->error);
            $s2->bind_param("iiiisidd",
                $issue_id, $return_id, $member_id,
                $due_date, $return_date,
                $days_late, $fine_rate, $fine
            );
        } else {
            $s2 = $conn->prepare("
                INSERT INTO Fine (Issue_ID, Member_ID, Due_Date, Return_Date, Days_Late, Fine_Rate, Fine_Amount)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if (!$s2) throw new Exception("Prepare s2 (no Return_ID) failed: " . $conn->error);
            $s2->bind_param("iissidd",
                $issue_id, $member_id,
                $due_date, $return_date,
                $days_late, $fine_rate, $fine
            );
        }
        $s2->execute();
        $s2->close();
    }

    // 3. Mark book as Available
    $s3 = $conn->prepare("UPDATE Book SET Status='Available' WHERE Book_ID=?");
    if (!$s3) throw new Exception("Prepare s3 failed: " . $conn->error);
    $s3->bind_param("i", $book_id);
    $s3->execute();
    $s3->close();

    $conn->commit();

    $response['success']   = true;
    $response['fine']      = $fine;
    $response['days_late'] = $days_late;
    $response['on_time']   = ($days_late === 0);
    $response['title']     = $title;

    if ($days_late > 0) {
        $response['message'] = '"' . $title . '" returned. Fine of ₹' . number_format($fine, 2) . ' charged for ' . $days_late . ' overdue day(s).';
    } else {
        $response['message'] = '"' . $title . '" returned successfully — no fine. Great job returning early! 🎉';
    }

} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = 'Return failed: ' . $e->getMessage();
}

echo json_encode($response);
exit;
?>
