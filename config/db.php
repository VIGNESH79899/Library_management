<?php
require_once __DIR__ . '/app.php';

$host = "mainline.proxy.rlwy.net";
$user = "root";
$password = "tqwXGAjLuYJOFfXmRudQyqESSRlhRGmu";
$database = "railway";
$port = 32459;

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>