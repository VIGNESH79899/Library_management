<?php
include "config/db.php";

// Check if columns exist
$result = $conn->query("SHOW COLUMNS FROM admin LIKE 'Full_Name'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE admin ADD COLUMN Full_Name VARCHAR(100) AFTER username";
    if ($conn->query($sql) === TRUE) {
        echo "Column Full_Name added successfully\n";
    } else {
        echo "Error adding column Full_Name: " . $conn->error . "\n";
    }
} else {
    echo "Column Full_Name already exists\n";
}

$result = $conn->query("SHOW COLUMNS FROM admin LIKE 'Email'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE admin ADD COLUMN Email VARCHAR(100) AFTER Full_Name";
    if ($conn->query($sql) === TRUE) {
        echo "Column Email added successfully\n";
    } else {
        echo "Error adding column Email: " . $conn->error . "\n";
    }
} else {
    echo "Column Email already exists\n";
}

// Update existing admin with default values if null
$conn->query("UPDATE admin SET Full_Name = 'System Administrator', Email = 'admin@library.com' WHERE Full_Name IS NULL");

echo "Migration complete.";
?>
