<?php
session_start();
include "../config/db.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone']; // Adding phone as it's in Member table
    $password = $_POST['password'];
    $address = $_POST['address']; // Optional in Member table? Adding for completeness

    // Validation: Email must end with @aurora.edu.in
    if (!str_ends_with($email, '@aurora.edu.in')) {
        $error = "Email must end with @aurora.edu.in";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT Member_ID FROM member WHERE Email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Register User
            // Password needs hashing (using password_hash for security)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO member (Member_Name, Email, Phone_Number, Address, Password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $address, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: " . BASE_URL . "/auth/login_user.php");
                exit;
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS | Student Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/favicon.png">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md animate-fade-in-up">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-8 text-center text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            <h1 class="text-3xl font-bold mb-2">Create Account</h1>
            <p class="text-blue-100">Join the Aurora Library Portal</p>
        </div>

        <div class="p-8">
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 text-sm flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-lg"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-3.5 text-gray-400 text-sm"></i>
                        <input type="text" name="name" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all" placeholder="John Doe">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">College Email</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-3.5 text-gray-400 text-sm"></i>
                        <input type="email" name="email" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all" placeholder="user@aurora.edu.in">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Must use @aurora.edu.in email address</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Phone Number</label>
                    <div class="relative">
                        <i class="fas fa-phone absolute left-3 top-3.5 text-gray-400 text-sm"></i>
                        <input type="tel" name="phone" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all" placeholder="(555) 123-4567">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Address</label>
                    <div class="relative">
                        <i class="fas fa-map-marker-alt absolute left-3 top-3.5 text-gray-400 text-sm"></i>
                        <input type="text" name="address" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all" placeholder="Your Address">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-3.5 text-gray-400 text-sm"></i>
                        <input type="password" name="password" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition-all" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-lg shadow-lg shadow-blue-500/30 transition-all transform active:scale-[0.98] flex items-center justify-center gap-2 mt-2">
                    <span>Create Account</span>
                    <i class="fas fa-arrow-right"></i>
                </button>

                <div class="text-center mt-6 text-sm text-gray-500">
                    Already have an account? <a href="<?= BASE_URL ?>/auth/login_user.php" class="text-blue-600 font-bold hover:underline">Log In</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
