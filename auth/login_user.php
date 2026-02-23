<?php
session_start();
include "../config/db.php";

$error = "";
$success = "";

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT Member_ID, Member_Name, Email, Password FROM Member WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['Password'])) {
            $_SESSION['user_id']    = $row['Member_ID'];
            $_SESSION['user_name']  = $row['Member_Name'];
            $_SESSION['user_email'] = $row['Email'];       // ← stored for email sends
            header("Location: ../user/dashboard.php");
            exit;

        } else {
            $error = "Invalid Password.";
        }
    } else {
        $error = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS | Student Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 h-screen w-full flex items-center justify-center p-4">

<div class="w-full max-w-sm animate-fade-in-up">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        
        <div class="p-8 text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-600 text-2xl">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Student Portal</h1>
            <p class="text-gray-500 text-sm mt-1">Access your library account</p>
        </div>

        <div class="px-8 pb-8">
            <?php if ($success): ?>
                <div class="bg-green-50 text-green-600 p-3 rounded-lg mb-4 text-sm flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-sm flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-3.5 text-gray-400 text-sm"></i>
                        <input type="email" name="email" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all" placeholder="user@aurora.edu.in">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-3.5 text-gray-400 text-sm"></i>
                        <input type="password" name="password" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all" placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-gray-600 cursor-pointer">
                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"> Remember me
                    </label>
                    <a href="#" class="text-blue-600 hover:underline font-medium">Forgot Password?</a>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-lg shadow-lg shadow-blue-500/30 transition-all transform active:scale-[0.98] flex items-center justify-center gap-2">
                    <span>Log In</span>
                    <i class="fas fa-sign-in-alt"></i>
                </button>

                <div class="text-center mt-6 text-sm text-gray-500">
                    New student? <a href="register_user.php" class="text-blue-600 font-bold hover:underline">Create Account</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
