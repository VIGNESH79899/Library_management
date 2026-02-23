<?php
session_start();

$_SESSION = [];
session_destroy();

require_once "../config/app.php";
// 🔥 FIXED REDIRECT
header("Location: " . BASE_URL . "/auth/login_user.php");
exit;