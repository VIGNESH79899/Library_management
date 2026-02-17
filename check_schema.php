<?php
include "config/db.php";
$result = $conn->query("SHOW CREATE TABLE Member");
$row = $result->fetch_row();
echo $row[1];
?>
