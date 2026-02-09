<?php
session_start();
include "../config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM admin WHERE username=? AND password=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $_SESSION['admin'] = $username;
        header("Location: ../dashboard/dashboard.php");
        exit;
    } else {
        $error = "Invalid Username or Password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>LMS Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

<div class="bg-white p-8 rounded shadow-md w-96">
    <h2 class="text-2xl font-bold mb-4 text-center">Admin Login</h2>

    <?php if ($error): ?>
        <p class="text-red-500 mb-3"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username"
               class="w-full p-2 mb-3 border rounded" required>

        <input type="password" name="password" placeholder="Password"
               class="w-full p-2 mb-3 border rounded" required>

        <button class="w-full bg-blue-600 text-white p-2 rounded">
            Login
        </button>
    </form>
</div>

</body>
</html>