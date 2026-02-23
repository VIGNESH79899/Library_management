<?php
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy session
session_destroy();

// Redirect to login page (NO library-management prefix)
header("Location: /auth/login_user.php");
exit;