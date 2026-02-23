<?php
session_start();

$_SESSION = [];
session_destroy();

// 🔥 FIXED REDIRECT
header("Location: /auth/login_user.php");
exit;