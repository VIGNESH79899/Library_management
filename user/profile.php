<?php
include "includes/header.php";
include "includes/navbar.php";
include "../config/db.php";

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

/* Update Profile Handler */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['member_name'] ?? '');
    $phone    = trim($_POST['phone']       ?? '');
    $address  = trim($_POST['address']     ?? '');
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // ── Validate name ────────────────────────────────────────
    if (empty($new_name)) {
        $error = 'Full name cannot be empty.';
    } elseif (strlen($new_name) < 2 || strlen($new_name) > 80) {
        $error = 'Name must be between 2 and 80 characters.';
    } elseif (preg_match('/[0-9]/', $new_name)) {
        $error = 'Name cannot contain numbers.';
    }

    if (empty($error)) {
        // Fetch current password hash for verification
        $stmt = $conn->prepare("SELECT Password FROM member WHERE Member_ID=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $current_db_password = $stmt->get_result()->fetch_assoc()['Password'];

        // Build UPDATE — always include name, phone, address
        $update_sql = "UPDATE member SET Member_Name=?, Phone_Number=?, Address=?";
        $types      = "sss";
        $params     = [$new_name, $phone, $address];

        $password_error = false;

        // Optional password change
        if (!empty($new_password)) {
            if (empty($current_password)) {
                $error = "Please enter your current password to set a new one.";
                $password_error = true;
            } elseif (!password_verify($current_password, $current_db_password)) {
                $error = "Incorrect current password.";
                $password_error = true;
            } elseif ($new_password !== $confirm_password) {
                $error = "New passwords do not match.";
                $password_error = true;
            } else {
                $update_sql .= ", Password=?";
                $types      .= "s";
                $params[]    = password_hash($new_password, PASSWORD_DEFAULT);
            }
        }

        if (!$password_error) {
            $update_sql .= " WHERE Member_ID=?";
            $types      .= "i";
            $params[]    = $user_id;

            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                // ✅ Sync session so navbar reflects new name immediately
                $_SESSION['user_name'] = $new_name;
                $success = "Profile updated successfully!";
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
}

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM member WHERE Member_ID=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 animate-fade-in-up">

    <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl shadow-slate-200/50 border border-white/50 overflow-hidden">
        
        <div class="relative bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-10 overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2 blur-2xl"></div>
            
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white tracking-tight">My Profile</h1>
                    <p class="text-blue-100 mt-2">Manage your personal information and security settings</p>
                </div>
                <div class="hidden md:block">
                     <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-white text-2xl shadow-inner border border-white/30">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-8 md:p-10">
            <?php if ($success): ?>
                <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 p-4 rounded-xl mb-8 flex items-center gap-3 animate-fade-in shadow-sm">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check text-sm"></i>
                    </div>
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-100 text-red-600 p-4 rounded-xl mb-8 flex items-center gap-3 animate-fade-in shadow-sm">
                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation text-sm"></i>
                    </div>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-8">
                <!-- Personal Info Section -->
                <section>
                    <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-id-card text-blue-500"></i> Personal Information
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="group">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Full Name</label>
                            <div class="relative">
                                <input type="text" name="member_name"
                                       value="<?= htmlspecialchars($data['Member_Name']) ?>"
                                       maxlength="80"
                                       placeholder="Your full name"
                                       class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all pl-10"
                                       required>
                                <i class="fas fa-user absolute left-3.5 top-3.5 text-slate-400"></i>
                            </div>
                            <p class="text-xs text-slate-400 mt-1.5 ml-1">Your display name across AuroraLib.</p>
                        </div>
                        <div class="group">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
                            <div class="relative">
                                <input type="text" value="<?= $data['Email'] ?>" readonly class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-500 cursor-not-allowed pl-10">
                                <i class="fas fa-envelope absolute left-3.5 top-3.5 text-slate-400"></i>
                            </div>
                        </div>
                        <div class="group">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Phone Number</label>
                            <div class="relative">
                                <input type="text" name="phone" value="<?= $data['Phone_Number'] ?>" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all pl-10">
                                <i class="fas fa-phone absolute left-3.5 top-3.5 text-slate-400"></i>
                            </div>
                        </div>
                        <div class="group md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Address</label>
                            <div class="relative">
                                <textarea name="address" rows="2" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all pl-10 resize-none"><?= $data['Address'] ?></textarea>
                                <i class="fas fa-map-marker-alt absolute left-3.5 top-3.5 text-slate-400"></i>
                            </div>
                        </div>
                    </div>
                </section>

                <hr class="border-slate-100">

                <!-- Security Section -->
                <section>
                    <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-lock text-blue-500"></i> Security
                    </h2>

                    <div class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                         <div class="md:col-span-2">
                             <p class="text-sm text-slate-500 mb-4 bg-blue-50 p-3 rounded-lg border border-blue-100 inline-block">
                                <i class="fas fa-info-circle mr-2 text-blue-500"></i> Only fill these fields if you want to change your password.
                             </p>
                         </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Current Password</label>
                            <div class="relative">
                                <input type="password" name="current_password" placeholder="••••••••" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all pl-10">
                                <i class="fas fa-key absolute left-3.5 top-3.5 text-slate-400"></i>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:col-span-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">New Password</label>
                                <div class="relative">
                                    <input type="password" name="new_password" placeholder="••••••••" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all pl-10">
                                    <i class="fas fa-lock absolute left-3.5 top-3.5 text-slate-400"></i>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Confirm New Password</label>
                                <div class="relative">
                                    <input type="password" name="confirm_password" placeholder="••••••••" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all pl-10">
                                    <i class="fas fa-check-circle absolute left-3.5 top-3.5 text-slate-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="pt-4 flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-0.5 active:scale-95 flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="dashboard.php" class="sm:w-32 block text-center py-3.5 rounded-xl border border-slate-200 text-slate-600 font-semibold hover:bg-slate-50 transition-colors">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
