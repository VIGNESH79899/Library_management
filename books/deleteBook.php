<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/db.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Check if book is issued before deleting
    $check = $conn->query("SELECT Status FROM Book WHERE Book_ID=$id")->fetch_assoc();
    
    if ($check['Status'] == 'Issued') {
        echo "<script>alert('Cannot delete an issued book!'); window.location.href='books.php';</script>";
        exit;
    }

    $conn->query("DELETE FROM Book WHERE Book_ID=$id");
}

header("Location: books.php");
exit;
?>
