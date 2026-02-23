<?php
session_start();
session_destroy();
require_once "../config/app.php";
header("Location: " . BASE_URL . "/auth/login.php");
exit;