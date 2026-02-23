<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit;
}
include "../config/db.php";

$message = "";
$editData = null;

/* Handle Delete Request */
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    
    // Check if member has issued books (active or past history)
    $check = $conn->prepare("SELECT Count(*) as count FROM issue WHERE Member_ID = ?");
    $check->bind_param("i", $delete_id);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();
    
    if ($result['count'] > 0) {
        $_SESSION['error'] = "Cannot delete member. They have borrowing history.";
    } else {
        $stmt = $conn->prepare("DELETE FROM member WHERE Member_ID=?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
             $_SESSION['message'] = "Member deleted successfully!";
        } else {
             $_SESSION['error'] = "Failed to delete member.";
        }
    }
    header("Location: members.php");
    exit;
}


/* Handle Form Submission (Add or Update) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    if (isset($_POST['update_id']) && !empty($_POST['update_id'])) {
        // Update Existing Member
        $id = $_POST['update_id'];
        $stmt = $conn->prepare("UPDATE member SET Member_Name=?, Phone_Number=?, Email=?, Address=? WHERE Member_ID=?");
        $stmt->bind_param("ssssi", $name, $phone, $email, $address, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Member updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update member.";
        }
    }
    header("Location: members.php");
    exit;
}

/* Check for Session Messages */
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
} else {
    $error = "";
}


/* Handle Edit Request */
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM member WHERE Member_ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}

/* Fetch Members with issue count */
$members = $conn->query("
    SELECT M.Member_ID, M.Member_Name, M.Phone_Number, M.Email, M.Address,
           COUNT(I.Issue_ID) AS Issued_Count
    FROM member M
    LEFT JOIN issue I ON M.Member_ID = I.Member_ID
    GROUP BY M.Member_ID
    ORDER BY M.Member_ID DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LMS | Members Management</title>
    <?php include "../includers/headers.php"; ?>
</head>
<body class="bg-gray-50 text-slate-800 font-sans antialiased">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include "../includers/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="flex-1 ml-64 flex flex-col relative z-0 transition-all duration-300">
        <!-- Top Navigation -->
        <?php include "../includers/navbar.php"; ?>
        
        <main class="p-8 space-y-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-fade-in">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Members Management</h1>
                    <p class="text-slate-500 mt-1">Manage registered library members.</p>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-start gap-3 animate-fade-in" role="alert">
                    <i class="fas fa-check-circle mt-0.5 text-green-500"></i>
                    <div>
                        <p class="font-bold text-sm">Success</p>
                        <p class="text-sm"><?= $message ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-start gap-3 animate-fade-in" role="alert">
                    <i class="fas fa-exclamation-circle mt-0.5 text-red-500"></i>
                    <div>
                        <p class="font-bold text-sm">Error</p>
                        <p class="text-sm"><?= $error ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <?php if ($editData): ?>
                <!-- Form Card (Only visible when editing) -->
                <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-gray-100 overflow-hidden animate-fade-in-up md:sticky md:top-24">
                    <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="font-bold text-slate-700">Edit Member</h2>
                        <a href="members.php" class="text-xs text-red-500 hover:text-red-700 font-medium">Cancel Edit</a>
                    </div>
                    
                    <form method="POST" class="p-6 space-y-5">
                        <input type="hidden" name="update_id" value="<?= $editData['Member_ID'] ?>">
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Full Name</label>
                            <div class="relative">
                                <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" name="name" required 
                                       value="<?= $editData['Member_Name'] ?>"
                                       class="w-full pl-8 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all text-sm"
                                       placeholder="John Doe">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Phone Number</label>
                            <div class="relative">
                                <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" name="phone" required
                                       value="<?= $editData['Phone_Number'] ?>"
                                       class="w-full pl-8 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all text-sm"
                                       placeholder="(555) 123-4567">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="email" name="email" required
                                       value="<?= $editData['Email'] ?>"
                                       class="w-full pl-8 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all text-sm"
                                       placeholder="john@example.com">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Address / Location</label>
                            <div class="relative">
                                <i class="fas fa-map-marker-alt absolute left-3 top-3 text-slate-400 text-xs"></i>
                                <textarea name="address" rows="2"
                                       class="w-full pl-8 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all text-sm resize-none"
                                       placeholder="123 Library St, Booktown"><?= $editData['Address'] ?></textarea>
                            </div>
                        </div>

                        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg font-bold shadow-lg shadow-indigo-500/30 transition-all transform active:scale-95 flex items-center justify-center gap-2 text-sm">
                            <i class="fas fa-save"></i>
                            <span>Update Member</span>
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- List Card -->
                <div class="<?= $editData ? 'lg:col-span-2' : 'lg:col-span-3' ?> bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-gray-100 overflow-hidden animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100">
                        <h2 class="font-bold text-slate-700 text-lg">Registered Members</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-base text-gray-600">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="p-4 font-semibold text-sm text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="p-4 font-semibold text-sm text-gray-500 uppercase tracking-wider">Details</th>
                                    <th class="p-4 font-semibold text-sm text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="p-4 font-semibold text-sm text-gray-500 uppercase tracking-wider text-center">Issues</th>
                                    <th class="p-4 font-semibold text-sm text-gray-500 uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php $cnt = 1; while ($row = $members->fetch_assoc()) { ?>
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="p-4 font-mono text-sm text-slate-400" title="Database ID: <?= $row['Member_ID'] ?>">#<?= $cnt++ ?></td>
                                    <td class="p-4">
                                        <div class="font-bold text-slate-800 text-lg"><?= $row['Member_Name'] ?></div>
                                        <div class="text-sm text-slate-500 mt-1 flex items-start gap-1">
                                            <i class="fas fa-map-marker-alt mt-1 text-slate-400"></i>
                                            <?= !empty($row['Address']) ? substr($row['Address'], 0, 40) . (strlen($row['Address']) > 40 ? '...' : '') : 'No location set' ?>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col gap-1.5">
                                            <span class="text-sm"><i class="fas fa-envelope w-5 text-slate-400"></i> <?= $row['Email'] ?></span>
                                            <span class="text-sm"><i class="fas fa-phone w-5 text-slate-400"></i> <?= $row['Phone_Number'] ?></span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full text-sm font-bold bg-indigo-100 text-indigo-700">
                                            <?= $row['Issued_Count'] ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="view_member_history.php?id=<?= $row['Member_ID'] ?>" 
                                               class="h-9 w-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm" 
                                               title="View Borrowing History">
                                                <i class="fas fa-history text-sm"></i>
                                            </a>
                                            <a href="members.php?edit=<?= $row['Member_ID'] ?>" 
                                               class="h-9 w-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all shadow-sm" 
                                               title="Edit">
                                                <i class="fas fa-pen text-sm"></i>
                                            </a>
                                            <a href="members.php?delete=<?= $row['Member_ID'] ?>" 
                                               class="h-9 w-9 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all shadow-sm" 
                                               title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this member?');">
                                                <i class="fas fa-trash text-sm"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php if($members->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-slate-400 italic text-base">No registered members found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

</body>
</html>
