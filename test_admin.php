<?php
require 'c:/xampp/htdocs/Library-management/config/db.php';
$conn->query("ALTER TABLE admin ADD COLUMN role VARCHAR(50) DEFAULT 'admin'");
echo $conn->error;
?>
