<?php
include "config/db.php";
$conn->query("ALTER TABLE member ADD COLUMN Address TEXT AFTER Email");
echo "Address column added.";
?>
