<?php
require_once __DIR__ . '/app.php';

$host = "gateway01.ap-southeast-1.prod.aws.tidbcloud.com";
$user = "3PsojpazoRe8fKN.root";
$password = "1VGO8nDShvxzrmE2";
$database = "test";
$port = 4000;

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_real_connect($conn, $host, $user, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
