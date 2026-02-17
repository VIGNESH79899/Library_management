<?php
include "config/db.php";
$conn->query("ALTER TABLE Member ADD COLUMN Address TEXT AFTER Email");
echo "Address column added.";
?>
