<?php
include "config/db.php";
$conn->query("ALTER TABLE book ADD Quantity INT NOT NULL DEFAULT 1, ADD Available_Quantity INT NOT NULL DEFAULT 1");
echo $conn->error;
echo "Done";
?>



