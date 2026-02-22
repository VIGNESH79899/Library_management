<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Clear all student session data
$_SESSION = [];
session_destroy();

// Redirect to the student login page (absolute path)
header("Location: /Library-management/auth/login_user.php");
exit;
?>
