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
$check = $conn->prepare("SELECT Status FROM Book WHERE Book_ID = ?");
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
    SELECT Issue_ID FROM Issue
    WHERE Book_ID = ? AND Member_ID = ?
      AND Issue_ID NOT IN (SELECT Issue_ID FROM Return_Book)
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
    $stmt = $conn->prepare("INSERT INTO Issue (Book_ID, Member_ID, Librarian_ID, Issue_Date, Due_Date) VALUES (?, ?, 1, ?, ?)");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("iiss", $book_id, $user_id, $issue_date, $due_date);
    $stmt->execute();
    $stmt->close();

    $upd = $conn->prepare("UPDATE Book SET Status='Issued' WHERE Book_ID=?");
    $upd->bind_param("i", $book_id);
    $upd->execute();
    $upd->close();

    $conn->commit();

    $response['success']  = true;
    $response['message']  = 'Book borrowed successfully! Please return by ' . date('M d, Y', strtotime($due_date));
    $response['due_date'] = date('M d, Y', strtotime($due_date));

} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = 'Failed to borrow book: ' . $e->getMessage();
}

echo json_encode($response);
exit;
?>
