<?php
include "config/db.php";
$result = $conn->query("SHOW CREATE TABLE member");
$row = $result->fetch_row();
echo $row[1];
?>
