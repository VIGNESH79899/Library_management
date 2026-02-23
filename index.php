<?php
require_once "config/app.php";

session_start();
// Redirect if already logged in
if (isset($_SESSION['admin'])) {
    header("Location: " . BASE_URL . "/dashboard/dashboard.php");
    exit;
}
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/user/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Library Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/favicon.png">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .hero-pattern {
            background-color: #ffffff;
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 20px 20px;
        }
    </style>
</head>
<body class="bg-gray-50 h-screen w-full overflow-hidden hero-pattern">

    <div class="container mx-auto h-full flex flex-col items-center justify-center p-4">
        
        <div class="text-center mb-12 animate-fade-in-up">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-indigo-600 rounded-2xl mb-6 shadow-xl shadow-indigo-500/30 transform -rotate-3 hover:rotate-0 transition-transform duration-500">
                <i class="fas fa-book-reader text-4xl text-white"></i>
            </div>
            <h1 class="text-5xl font-bold text-slate-900 mb-4 tracking-tight">Aurora <span class="text-indigo-600">Library</span></h1>
            <p class="text-xl text-slate-500 max-w-lg mx-auto">Welcome to the digital gateway of knowledge. Access thousands of books and resources.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full max-w-4xl px-4 animate-fade-in-up" style="animation-delay: 0.1s;">
            
            <!-- Student Portal Card -->
            <a href="<?= BASE_URL ?>/auth/login_user.php" class="group relative bg-white border border-gray-100 rounded-3xl p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:shadow-blue-500/20 hover:-translate-y-2 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-10 -mt-10 transition-transform group-hover:scale-150 duration-500"></div>
                <div class="relative z-10 text-center">
                    <div class="w-16 h-16 mx-auto bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800 mb-2">Student Portal</h2>
                    <p class="text-slate-500 text-sm mb-6">Access for students and faculty. Borrow books, check due dates, and manage your account.</p>
                    <span class="inline-flex items-center text-blue-600 font-bold group-hover:gap-2 transition-all">
                        Enter Portal <i class="fas fa-arrow-right ml-2"></i>
                    </span>
                </div>
            </a>

            <!-- Librarian Portal Card -->
            <a href="<?= BASE_URL ?>/auth/login.php" class="group relative bg-white border border-gray-100 rounded-3xl p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:shadow-indigo-500/20 hover:-translate-y-2 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-full -mr-10 -mt-10 transition-transform group-hover:scale-150 duration-500"></div>
                <div class="relative z-10 text-center">
                    <div class="w-16 h-16 mx-auto bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800 mb-2">Librarian Portal</h2>
                    <p class="text-slate-500 text-sm mb-6">Administrative access for library staff. Manage books, members, issues, and returns.</p>
                    <span class="inline-flex items-center text-indigo-600 font-bold group-hover:gap-2 transition-all">
                        Admin Login <i class="fas fa-arrow-right ml-2"></i>
                    </span>
                </div>
            </a>

        </div>

        <div class="mt-12 text-slate-400 text-sm">
            &copy; <?= date('Y') ?> Aurora Library Management System. All rights reserved.
        </div>

    </div>

    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }
    </style>
</body>
</html>