<?php
/**
 * return_book.php  — Student early-return AJAX handler
 * Outputs ONLY clean JSON. No HTML. No stray output.
 */
ob_start(); // Buffer any accidental output so JSON is never corrupted

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kill any buffered output and set JSON header
ob_end_clean();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'fine' => 0, 'days_late' => 0, 'on_time' => true];

// Auth check
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Session expired. Please log in again.';
    echo json_encode($response);
    exit;
}

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

include "../config/db.php";

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

// ── Check Fine table has Return_ID (do OUTSIDE transaction) ─────────────────
$hasReturnID = $conn->query("SHOW COLUMNS FROM `fine` LIKE 'Return_ID'")->num_rows > 0;

// ── Verify issue belongs to THIS student and is still active ─────────────────
$chk = $conn->prepare("
    SELECT I.Issue_ID, I.Book_ID, I.Member_ID, I.Due_Date, B.Title
    FROM issue I
    JOIN book B ON I.Book_ID = B.Book_ID
    WHERE I.Issue_ID = ?
      AND I.Member_ID = ?
      AND B.Status = 'Issued'
      AND I.Issue_ID NOT IN (SELECT Issue_ID FROM return_book)
");

if (!$chk) {
    $response['message'] = 'DB prepare error: ' . $conn->error;
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

// ── Fine calculation ─────────────────────────────────────────────────────────
$due_ts    = strtotime($due_date);
$today_ts  = strtotime($return_date);
$days_late = ($today_ts > $due_ts) ? (int)floor(($today_ts - $due_ts) / 86400) : 0;
$fine      = (float)($days_late * $fine_rate);

// ── Database Transaction ─────────────────────────────────────────────────────
$conn->begin_transaction();
try {
    // 1. Insert into Return_Book
    $s1 = $conn->prepare("INSERT INTO return_book (Issue_ID, Return_Date, Fine_Amount) VALUES (?, ?, ?)");
    if (!$s1) throw new Exception("s1: " . $conn->error);
    $s1->bind_param("isd", $issue_id, $return_date, $fine);
    $s1->execute();
    $return_id = (int)$conn->insert_id;
    $s1->close();

    // 2. Insert fine record if overdue
    if ($days_late > 0) {
        if ($hasReturnID) {
            // Fine table has Return_ID column
            $s2 = $conn->prepare("
                INSERT INTO fine
                    (Issue_ID, Return_ID, Member_ID, Due_Date, Return_Date, Days_Late, Fine_Rate, Fine_Amount)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if (!$s2) throw new Exception("s2: " . $conn->error);
            $s2->bind_param("iiiisidd",
                $issue_id, $return_id, $member_id,
                $due_date, $return_date,
                $days_late, $fine_rate, $fine
            );
        } else {
            // Fine table without Return_ID
            $s2 = $conn->prepare("
                INSERT INTO fine
                    (Issue_ID, Member_ID, Due_Date, Return_Date, Days_Late, Fine_Rate, Fine_Amount)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if (!$s2) throw new Exception("s2-no-rid: " . $conn->error);
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
    $s3 = $conn->prepare("UPDATE book SET Status='Available' WHERE Book_ID=?");
    if (!$s3) throw new Exception("s3: " . $conn->error);
    $s3->bind_param("i", $book_id);
    $s3->execute();
    $s3->close();

    $conn->commit();

    $response['success']   = true;
    $response['fine']      = $fine;
    $response['days_late'] = $days_late;
    $response['on_time']   = ($days_late === 0);
    $response['title']     = $title;

    $response['message'] = $days_late > 0
        ? '"' . $title . '" returned. Fine of ₹' . number_format($fine, 2) . ' for ' . $days_late . ' overdue day(s).'
        : '"' . $title . '" returned successfully — no fine. 🎉';

} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = 'Return failed: ' . $e->getMessage();
}

echo json_encode($response);
exit;
?>
