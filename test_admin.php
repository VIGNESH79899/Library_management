<?php
include "config/db.php";
$stmt = $conn->query("SELECT Full_Name FROM admin LIMIT 1");
if ($stmt) {
    echo "Found Full_Name. Content: ";
    $row = $stmt->fetch_assoc();
    print_r($row);
} else {
    echo "Column Full_Name missing!";
}
?>
