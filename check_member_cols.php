<?php
include "config/db.php";
$result = $conn->query("SHOW COLUMNS FROM member");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
