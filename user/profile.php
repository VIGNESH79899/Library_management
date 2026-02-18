<?php
include "includes/header.php";
include "includes/navbar.php";
include "../config/db.php";

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

/* Update Profile Handler */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = $_POST['password'];

    if (!empty($password)) {
        // Update with password
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE Member SET Phone_Number=?, Address=?, Password=? WHERE Member_ID=?");
        $stmt->bind_param("sssi", $phone, $address, $hashed, $user_id);
    } else {
        // Update without password
        $stmt = $conn->prepare("UPDATE Member SET Phone_Number=?, Address=? WHERE Member_ID=?");
        $stmt->bind_param("ssi", $phone, $address, $user_id);
    }

    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
    } else {
        $error = "Failed to update profile.";
    }
}

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM Member WHERE Member_ID=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
?>

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12 animate-fade-in-up">

    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-gray-100 overflow-hidden">
        
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-8 py-8 text-white">
            <h1 class="text-2xl font-bold">My Profile</h1>
            <p class="text-slate-400 text-sm">Manage your personal information</p>
        </div>

        <div class="p-8">
            <?php if ($success): ?>
                <div class="bg-green-50 text-green-600 p-3 rounded-lg mb-6 text-sm flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <!-- Read Only Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Full Name</label>
                        <input type="text" value="<?= $data['Member_Name'] ?>" readonly class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 text-slate-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Email</label>
                        <input type="text" value="<?= $data['Email'] ?>" readonly class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 text-slate-500 cursor-not-allowed">
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- Editable Fields -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" value="<?= $data['Phone_Number'] ?>" required class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Address</label>
                    <textarea name="address" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-all resize-none"><?= $data['Address'] ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">New Password <span class="text-slate-400 font-normal text-xs">(Leave blank to keep current)</span></label>
                    <input type="password" name="password" placeholder="••••••••" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-all">
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-lg shadow-lg shadow-brand-500/20 transition-transform active:scale-95">
                        Save Changes
                    </button>
                    <a href="dashboard.php" class="block text-center mt-4 text-sm text-slate-500 hover:text-slate-800">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
