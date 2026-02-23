<?php
// Prevent any output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
include "../config/db.php";

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access. Please login.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data.');
    }

    $book_id = isset($data['book_id']) ? intval($data['book_id']) : 0;
    $member_id = $_SESSION['user_id'];

    if ($book_id <= 0) {
        throw new Exception('Invalid Book ID provied.');
    }

    // Check if already liked
    $check = $conn->prepare("SELECT Like_ID FROM book_likes WHERE Member_ID = ? AND Book_ID = ?");
    if (!$check) {
        throw new Exception("Database error: " . $conn->error);
    }
    $check->bind_param("ii", $member_id, $book_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Unlike
        $delete = $conn->prepare("DELETE FROM book_likes WHERE Member_ID = ? AND Book_ID = ?");
        if (!$delete) {
            throw new Exception("Database prepare error: " . $conn->error);
        }
        $delete->bind_param("ii", $member_id, $book_id);
        if ($delete->execute()) {
            echo json_encode(['status' => 'unliked', 'message' => 'Removed from favorites']);
        } else {
            throw new Exception("Failed to delete like: " . $conn->error);
        }
    } else {
        // Like
        $insert = $conn->prepare("INSERT INTO book_likes (Member_ID, Book_ID) VALUES (?, ?)");
        if (!$insert) {
            throw new Exception("Database prepare error: " . $conn->error);
        }
        $insert->bind_param("ii", $member_id, $book_id);
        if ($insert->execute()) {
            echo json_encode(['status' => 'liked', 'message' => 'Added to favorites']);
        } else {
            throw new Exception("Failed to insert like: " . $conn->error);
        }
    }

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error for client side handling
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
