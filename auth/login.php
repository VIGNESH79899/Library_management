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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS | Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 h-screen w-full overflow-hidden">

<div class="flex h-full w-full">
    <!-- Left Side - Branding -->
    <div class="hidden lg:flex w-1/2 bg-slate-900 relative items-center justify-center overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-purple-600/20 z-10"></div>
        <!-- Decorative blobs -->
        <div class="absolute top-0 left-0 w-full h-full opacity-30">
             <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
             <div class="absolute top-[20%] right-[-10%] w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000"></div>
             <div class="absolute bottom-[-20%] left-[20%] w-96 h-96 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-4000"></div>
        </div>
        
        <div class="relative z-20 text-center p-12">
            <div class="h-20 w-20 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-3xl shadow-xl mx-auto mb-8 transform rotate-3 hover:rotate-6 transition-transform">
                <i class="fas fa-book-reader"></i>
            </div>
            <h1 class="text-5xl font-bold text-white mb-6">Library<br/>Management</h1>
            <p class="text-indigo-200 text-lg max-w-md mx-auto leading-relaxed">
                Empower your library with a seamless, high-performance management system designed for the future.
            </p>
        </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white relative">
        <div class="w-full max-w-md">
            <div class="mb-10">
                <h2 class="text-3xl font-bold text-slate-900 mb-2">Welcome Back!</h2>
                <p class="text-slate-500">Please enter your credentials to access the admin panel.</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r" role="alert">
                    <p class="font-bold">Error</p>
                    <p><?= $error ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-slate-400"></i>
                        </div>
                        <input type="text" name="username" 
                               class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all bg-gray-50 focus:bg-white" 
                               placeholder="Enter your username" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-slate-400"></i>
                        </div>
                        <input type="password" name="password" 
                               class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all bg-gray-50 focus:bg-white" 
                               placeholder="••••••••" required>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-slate-600">Remember me</label>
                    </div>
                    <div class="text-sm">
                        <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">Forgot password?</a>
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-lg shadow-lg shadow-indigo-500/30 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                    <span>Sign In</span>
                    <i class="fas fa-arrow-right text-sm"></i>
                </button>
            </form>
            
            <p class="mt-8 text-center text-sm text-slate-500">
                Don't have an account? <span class="text-indigo-600 font-medium cursor-pointer">Contact Administrator</span>
            </p>
        </div>
    </div>
</div>

<style>
/* Blob animations for the left side */
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}
</style>

</body>
</html>