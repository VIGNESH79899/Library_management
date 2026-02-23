<?php
include "config/db.php";
$result = $conn->query("SHOW COLUMNS FROM category");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
} else {
    echo "Error: " . $conn->error;
}
?>
