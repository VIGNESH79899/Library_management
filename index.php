<?php
session_start();

if (isset($_SESSION['admin'])) {
    header("Location: dashboard/dashboard.php");
} else {
    header("Location: auth/login.php");
}
exit;