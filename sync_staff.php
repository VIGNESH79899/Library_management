<?php
require 'c:/xampp/htdocs/Library-management/config/db.php';

$res = $conn->query("SELECT * FROM librarian");
while ($row = $res->fetch_assoc()) {
    $email = $row['Email'];
    $name = $row['Librarian_Name'];
    $parts = explode('@', $email);
    $username = $parts[0];
    
    // Check if exists
    $check = $conn->prepare("SELECT admin_id FROM admin WHERE username=?");
    $check->bind_param("s", $username);
    $check->execute();
    if ($check->get_result()->num_rows == 0) {
        $pwd = md5("staff123");
        $role = "staff";
        $ins = $conn->prepare("INSERT INTO admin (username, Full_Name, Email, password, role) VALUES (?, ?, ?, ?, ?)");
        $ins->bind_param("sssss", $username, $name, $email, $pwd, $role);
        $ins->execute();
        echo "Created staff account for $name (username: $username, password: staff123)\n";
    } else {
        echo "Account already exists for $username.\n";
    }
}
?>
