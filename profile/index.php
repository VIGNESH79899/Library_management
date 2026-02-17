<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/db.php";

$success = "";
$error = "";

// Fetch current details first to get ID
$stmt = $conn->prepare("SELECT * FROM admin WHERE username=?");
$stmt->bind_param("s", $_SESSION['admin']);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();

if (!$current_user) {
    // Should not happen
    header("Location: ../auth/login.php");
    exit;
}

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Check if username already exists (and not current user)
    $stmt_check = $conn->prepare("SELECT admin_id FROM admin WHERE username=? AND admin_id!=?");
    $stmt_check->bind_param("si", $username, $current_user['admin_id']);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $error = "Username already taken!";
    } else {
        $stmt_update = $conn->prepare("UPDATE admin SET Full_Name=?, Email=?, username=? WHERE admin_id=?");
        $stmt_update->bind_param("sssi", $full_name, $email, $username, $current_user['admin_id']);
        
        if ($stmt_update->execute()) {
            $_SESSION['admin'] = $username; // Update session
            $success = "Profile updated successfully!";
            // Refresh current user data
            $current_user['Full_Name'] = $full_name;
            $current_user['Email'] = $email;
            $current_user['username'] = $username;
        } else {
            $error = "Error updating profile! " . $conn->error;
        }
    }
}

// Handle Password Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current_pass = md5($_POST['current_password']);
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    
    // Check current password
    if ($current_user['password'] !== $current_pass && $current_pass !== $current_user['password']) { 
        // Note: Logic allows for mismatched hash if logic changed, but strict check is better.
        // Wait, current_user['password'] is from DB (hashed). $current_pass is hashed input.
        // So strict comparison.
        $error = "Incorrect current password!";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "New passwords do not match!";
    } elseif (strlen($new_pass) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        $new_pass_hash = md5($new_pass);
        $stmt_pass = $conn->prepare("UPDATE admin SET password=? WHERE admin_id=?");
        $stmt_pass->bind_param("si", $new_pass_hash, $current_user['admin_id']);
        
        if ($stmt_pass->execute()) {
            $success = "Password updated successfully!";
            $current_user['password'] = $new_pass_hash;
        } else {
            $error = "Error updating password!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | My Profile</title>
    <?php include "../includers/headers.php"; ?>
</head>
<body class="bg-gray-50 text-slate-800 font-sans antialiased">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include "../includers/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="flex-1 ml-64 flex flex-col relative z-0">
        <!-- Top Navigation -->
        <?php include "../includers/navbar.php"; ?>
        
        <main class="p-8 space-y-8 max-w-7xl mx-auto w-full">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 animate-enter">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight font-inter">My Profile</h1>
                    <p class="text-slate-500 mt-2 font-medium">Manage your account settings and preferences.</p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm animate-enter" role="alert">
                    <div class="flex">
                        <div class="py-1"><i class="fas fa-check-circle mr-3"></i></div>
                        <div><p class="font-bold">Success</p><p><?= $success ?></p></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm animate-enter" role="alert">
                    <div class="flex">
                        <div class="py-1"><i class="fas fa-exclamation-circle mr-3"></i></div>
                        <div><p class="font-bold">Error</p><p><?= $error ?></p></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Profile Card -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-6 text-center relative overflow-hidden group hover-card animate-enter delay-100">
                        <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                        <div class="relative mt-12 mb-4">
                            <div class="w-24 h-24 mx-auto rounded-2xl bg-white p-1 shadow-lg transform group-hover:scale-105 transition-transform duration-300">
                                <div class="w-full h-full rounded-xl bg-slate-900 flex items-center justify-center text-white text-3xl font-bold">
                                    <?php
                                        $name = $current_user['Full_Name'] ?? 'Admin User';
                                        $parts = explode(' ', $name);
                                        echo strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                                    ?>
                                </div>
                            </div>
                        </div>
                        <h2 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($current_user['Full_Name'] ?? 'Admin User') ?></h2>
                        <p class="text-indigo-600 font-medium text-sm mb-4">System Administrator</p>
                        
                        <div class="border-t border-slate-100 pt-4 text-left space-y-3">
                            <div class="flex items-center gap-3 text-sm text-slate-600">
                                <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <span class="truncate"><?= htmlspecialchars($current_user['Email'] ?? 'admin@library.com') ?></span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-slate-600">
                                <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <span><?= htmlspecialchars($current_user['username']) ?></span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-slate-600">
                                <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <span>Joined recently</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Forms -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Edit Details Form -->
                    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-8 animate-enter delay-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                                <i class="fas fa-user-edit text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">Edit Profile Details</h3>
                        </div>
                        
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="update_profile" value="1">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Full Name</label>
                                    <input type="text" name="full_name" value="<?= htmlspecialchars($current_user['Full_Name'] ?? '') ?>" 
                                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all text-sm font-medium text-slate-700" required>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Username</label>
                                    <input type="text" name="username" value="<?= htmlspecialchars($current_user['username']) ?>" 
                                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all text-sm font-medium text-slate-700" required>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700">Email Address</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($current_user['Email'] ?? '') ?>" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all text-sm font-medium text-slate-700" required>
                            </div>
                            
                            <div class="pt-4 flex justify-end">
                                <button type="submit" class="btn-primary px-6 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Change Password Form -->
                    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-8 animate-enter delay-300">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                                <i class="fas fa-key text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">Change Password</h3>
                        </div>
                        
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="update_password" value="1">
                            
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700">Current Password</label>
                                <input type="password" name="current_password" placeholder="••••••••"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all text-sm font-medium text-slate-700" required>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">New Password</label>
                                    <input type="password" name="new_password" placeholder="••••••••"
                                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all text-sm font-medium text-slate-700" required>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700">Confirm New Password</label>
                                    <input type="password" name="confirm_password" placeholder="••••••••"
                                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all text-sm font-medium text-slate-700" required>
                                </div>
                            </div>
                            
                            <div class="pt-4 flex justify-end">
                                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-6 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 shadow-lg shadow-slate-900/20 hover:shadow-slate-900/40 transition-all">
                                    <i class="fas fa-lock"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

        </main>
    </div>
</div>

</body>
</html>
