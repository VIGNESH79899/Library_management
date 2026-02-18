<?php
include "config/db.php";

$sql = "CREATE TABLE IF NOT EXISTS Book_Likes (
    Like_ID INT AUTO_INCREMENT PRIMARY KEY,
    Member_ID INT NOT NULL,
    Book_ID INT NOT NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Member_ID) REFERENCES Member(Member_ID) ON DELETE CASCADE,
    FOREIGN KEY (Book_ID) REFERENCES Book(Book_ID) ON DELETE CASCADE,
    UNIQUE KEY (Member_ID, Book_ID)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table Book_Likes created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}
?>
