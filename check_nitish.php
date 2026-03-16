<?php
require 'c:/xampp/htdocs/Library-management/config/db.php';
$res = $conn->query("SELECT * FROM librarian WHERE Email='nitish@library.com'");
while ($r = $res->fetch_assoc()) print_r($r);
?>
