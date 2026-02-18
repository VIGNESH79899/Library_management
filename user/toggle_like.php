<?php
session_start();
include "../config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$book_id = isset($data['book_id']) ? intval($data['book_id']) : 0;
$member_id = $_SESSION['user_id'];

if ($book_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Book ID']);
    exit;
}

// Check if already liked
$check = $conn->prepare("SELECT Like_ID FROM Book_Likes WHERE Member_ID = ? AND Book_ID = ?");
$check->bind_param("ii", $member_id, $book_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Unlike
    $delete = $conn->prepare("DELETE FROM Book_Likes WHERE Member_ID = ? AND Book_ID = ?");
    $delete->bind_param("ii", $member_id, $book_id);
    if ($delete->execute()) {
        echo json_encode(['status' => 'unliked']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to unlike']);
    }
} else {
    // Like
    $insert = $conn->prepare("INSERT INTO Book_Likes (Member_ID, Book_ID) VALUES (?, ?)");
    $insert->bind_param("ii", $member_id, $book_id);
    if ($insert->execute()) {
        echo json_encode(['status' => 'liked']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to like']);
    }
}
?>
